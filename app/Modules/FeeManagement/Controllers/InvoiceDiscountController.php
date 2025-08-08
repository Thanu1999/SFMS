<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;
use PDOException;

class InvoiceDiscountController extends BaseController {

    /**
     * Add a discount to a specific invoice.
     * Handles POST /admin/invoices/{invoice_id}/discounts/add
     */
    public function addDiscount(array $vars): void {
        // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/invoices'); return; }
        // --- End Checks ---

        $invoiceId = $vars['invoice_id'] ?? null;
        $discountTypeId = filter_input(INPUT_POST, 'discount_type_id', FILTER_VALIDATE_INT);
        // Optional: Allow overriding fixed amount or applying specific amount
        $overrideAmount = filter_input(INPUT_POST, 'applied_amount', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
        $notes = trim($_POST['notes'] ?? '');
        $currentUserId = $_SESSION['user_id'] ?? null;

        if (!$invoiceId || !$discountTypeId) {
            $this->handleError("Missing Invoice ID or Discount Type ID.", '/admin/fees/invoices'); return;
        }
        $redirectUrl = '/admin/fees/invoices/view/' . $invoiceId; // Redirect back to invoice view

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleError("Database connection failed.", $redirectUrl); return; }

        $pdo->beginTransaction();
        try {
            // 1. Get Discount Type details
            $stmtDiscType = $pdo->prepare("SELECT * FROM discount_types WHERE discount_type_id = :id AND is_active = TRUE");
            $stmtDiscType->execute([':id' => $discountTypeId]);
            $discountType = $stmtDiscType->fetch(PDO::FETCH_ASSOC);
            if (!$discountType) { throw new \Exception("Active Discount Type not found."); }

            // 2. Get Invoice total amount (needed for percentage calc)
             $stmtInv = $pdo->prepare("SELECT total_amount FROM fee_invoices WHERE invoice_id = :id FOR UPDATE"); // Lock invoice row
             $stmtInv->execute([':id' => $invoiceId]);
             $totalAmount = $stmtInv->fetchColumn();
             if ($totalAmount === false) { throw new \Exception("Invoice not found or cannot fetch total amount."); }
             $totalAmount = (float) $totalAmount;

            // 3. Calculate applied amount
            $appliedAmount = 0.00;
            if ($overrideAmount !== null && $overrideAmount >= 0) {
                // Allow overriding amount if provided and valid (e.g., for flexible fixed amounts)
                $appliedAmount = $overrideAmount;
            } elseif ($discountType['type'] === 'percentage') {
                $appliedAmount = round($totalAmount * ($discountType['value'] / 100.0), 2);
            } elseif ($discountType['type'] === 'fixed_amount') {
                $appliedAmount = (float)$discountType['value'];
            }
            // Ensure applied amount is not negative and not more than total amount
            $appliedAmount = max(0, min($appliedAmount, $totalAmount));


            // 4. Insert into invoice_discounts
            $sqlInsert = "INSERT INTO invoice_discounts (invoice_id, discount_type_id, applied_amount, applied_by_user_id, applied_at, notes)
                          VALUES (:inv_id, :disc_type_id, :amount, :user_id, NOW(), :notes)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $paramsInsert = [
                ':inv_id' => $invoiceId,
                ':disc_type_id' => $discountTypeId,
                ':amount' => $appliedAmount,
                ':user_id' => $currentUserId,
                ':notes' => $notes ?: null
            ];
            if (!$stmtInsert->execute($paramsInsert)) { throw new \Exception("Failed to apply discount link."); }

            // 5. Recalculate totals and Update fee_invoices
            if (!$this->recalculateInvoiceTotals($pdo, $invoiceId, $totalAmount)) { // Pass totalAmount
                throw new \Exception("Failed to update invoice totals after applying discount.");
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Discount applied successfully.";

        } catch (\Exception $e) {
             if ($pdo->inTransaction()) { $pdo->rollBack(); }
             error_log("Add Invoice Discount Error: " . $e->getMessage());
             // Handle duplicate key error specifically
             if ($e instanceof PDOException && $e->getCode() == 23000) {
                  $this->handleError("This discount type has already been applied to this invoice.", $redirectUrl);
             } else {
                  $this->handleError("Error applying discount: " . $e->getMessage(), $redirectUrl);
             }
             return; // Stop execution after handling error
        }

        $this->redirect($redirectUrl);
    }


    /**
     * Remove a discount from a specific invoice.
     * Handles POST /admin/invoices/discounts/remove
     */
    public function removeDiscount(): void {
         // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/invoices'); return; }
        // --- End Checks ---

        $invoiceDiscountId = filter_input(INPUT_POST, 'invoice_discount_id', FILTER_VALIDATE_INT);
        $invoiceId = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT); // Needed for redirect & recalc

        if (!$invoiceDiscountId || !$invoiceId) {
             $this->handleError("Missing required IDs to remove discount.", '/admin/fees/invoices'); return;
        }
        $redirectUrl = '/admin/fees/invoices/view/' . $invoiceId;

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleError("Database connection failed.", $redirectUrl); return; }

        $pdo->beginTransaction();
        try {
            // 1. Get Invoice total amount before deleting discount (needed for recalc)
             $stmtInv = $pdo->prepare("SELECT total_amount FROM fee_invoices WHERE invoice_id = :id FOR UPDATE"); // Lock
             $stmtInv->execute([':id' => $invoiceId]);
             $totalAmount = $stmtInv->fetchColumn();
             if ($totalAmount === false) { throw new \Exception("Invoice not found for recalculation."); }
             $totalAmount = (float) $totalAmount;

            // 2. Delete from invoice_discounts
            $sqlDelete = "DELETE FROM invoice_discounts WHERE invoice_discount_id = :id";
            $stmtDelete = $pdo->prepare($sqlDelete);
            $stmtDelete->execute([':id' => $invoiceDiscountId]);

            if ($stmtDelete->rowCount() === 0) {
                // Link might have already been removed
                throw new \Exception("Discount link not found or already removed.");
            }

            // 3. Recalculate totals and Update fee_invoices
            if (!$this->recalculateInvoiceTotals($pdo, $invoiceId, $totalAmount)) {
                throw new \Exception("Failed to update invoice totals after removing discount.");
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "Discount removed successfully.";

        } catch (\Exception $e) {
             if ($pdo->inTransaction()) { $pdo->rollBack(); }
             error_log("Remove Invoice Discount Error: " . $e->getMessage());
             $this->handleError("Error removing discount: " . $e->getMessage(), $redirectUrl);
             return;
        }

         $this->redirect($redirectUrl);
    }


    /**
     * Helper function to recalculate and update invoice totals based on applied discounts.
     * Should be called within a transaction.
     */
    private function recalculateInvoiceTotals(PDO $pdo, int $invoiceId, float $totalAmount): bool {
         // Sum all applied discounts for this invoice
         $sqlSum = "SELECT SUM(applied_amount) FROM invoice_discounts WHERE invoice_id = :id";
         $stmtSum = $pdo->prepare($sqlSum);
         $stmtSum->execute([':id' => $invoiceId]);
         $totalDiscount = (float)($stmtSum->fetchColumn() ?: 0.00);

         // Calculate new payable amount
         $totalPayable = $totalAmount - $totalDiscount;
         $totalPayable = max(0, $totalPayable); // Ensure payable is not negative

         // Update the fee_invoices table
         $sqlUpdate = "UPDATE fee_invoices SET
                            total_discount = :discount,
                            total_payable = :payable,
                            updated_at = NOW()
                       WHERE invoice_id = :id";
         $stmtUpdate = $pdo->prepare($sqlUpdate);
         return $stmtUpdate->execute([
             ':discount' => $totalDiscount,
             ':payable' => $totalPayable,
             ':id' => $invoiceId
         ]);
    }

    /** Helper for setting flash error and redirecting (should be in BaseController) */
    private function handleError(string $message, string $redirectTo): void {
         $_SESSION['flash_error'] = $message;
         $this->redirect($redirectTo);
         exit; // Ensure exit after redirect
    }
}