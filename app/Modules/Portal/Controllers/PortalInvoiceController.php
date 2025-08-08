<?php
namespace App\Modules\Portal\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;

class PortalInvoiceController extends BaseController {

    /**
     * Display the details of a specific fee invoice for the logged-in user.
     * Handles GET /portal/invoices/view/{id}
     */
    public function view(array $vars): void {
        // --- Auth Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        $userId = $_SESSION['user_id'];
        $userRoles = $_SESSION['roles'] ?? [];
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) {
             $_SESSION['flash_error'] = "Access Denied.";
             $this->redirect('/logout'); return;
        }
        // --- End Checks ---

        $invoiceId = $vars['id'] ?? null;
        if (!$invoiceId) { $this->handleDbErrorAndRedirect("Invalid Invoice ID.", '/portal/fees'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/portal/fees'); return; }

        $invoice = null;
        $items = [];
        $payments = [];
        $viewError = null; // Renamed $dbError for consistency
        $isAuthorized = false; // Flag to check if user can view this invoice

        try {
            // 1. Fetch main invoice data AND check authorization in one go
            // Join students and potentially student_guardian_links to verify link to logged-in user
            $sqlInvoice = "SELECT fi.*, s.first_name, s.last_name, s.user_id AS student_user_id,
                                 (SELECT GROUP_CONCAT(sgl.user_id) FROM student_guardian_links sgl WHERE sgl.student_id = fi.student_id) AS guardian_user_ids,
                                 c.class_name, acs.session_name,
                                 (fi.total_payable - fi.total_paid) AS balance_due
                           FROM fee_invoices fi
                           JOIN students s ON fi.student_id = s.student_id
                           JOIN academic_sessions acs ON fi.session_id = acs.session_id
                           LEFT JOIN classes c ON s.current_class_id = c.class_id
                           WHERE fi.invoice_id = :invoiceId";
            $stmtInvoice = $pdo->prepare($sqlInvoice);
            $stmtInvoice->bindParam(':invoiceId', $invoiceId, PDO::PARAM_INT);
            $stmtInvoice->execute();
            $invoice = $stmtInvoice->fetch(PDO::FETCH_ASSOC);

            

            if (!$invoice) { $this->handleDbErrorAndRedirect("Invoice not found.", '/portal/fees'); return; }

            // 2. Authorization Check: Is logged-in user the student OR one of the linked guardians?
            if (in_array('Student', $userRoles) && $invoice['student_user_id'] == $userId) {
                $isAuthorized = true;
            } elseif (in_array('Parent', $userRoles) && $invoice['guardian_user_ids']) {
                $guardianIds = explode(',', $invoice['guardian_user_ids']);
                if (in_array($userId, $guardianIds)) {
                    $isAuthorized = true;
                }
            }

            if (!$isAuthorized) {
                 $_SESSION['flash_error'] = "Access Denied: You cannot view this invoice.";
                 $this->redirect('/portal/dashboard'); return;
            }
            // --- ADDED: Fetch Applied Discounts ---
            $sqlApplied = "SELECT id.*, dt.name as discount_name
                           FROM invoice_discounts id
                           JOIN discount_types dt ON id.discount_type_id = dt.discount_type_id
                           WHERE id.invoice_id = :id ORDER BY id.applied_at ASC";
            $stmtApplied = $pdo->prepare($sqlApplied);
            $stmtApplied->execute([':id' => $invoiceId]);
            $appliedDiscounts = $stmtApplied->fetchAll(PDO::FETCH_ASSOC);
            // --- END Fetch Applied Discounts ---

            // 3. Fetch invoice items (if authorized)
            $sqlItems = "SELECT fii.*, fc.category_name FROM fee_invoice_items fii JOIN fee_categories fc ON fii.category_id = fc.category_id WHERE fii.invoice_id = :id ORDER BY fii.item_id ASC";
            $stmtItems = $pdo->prepare($sqlItems);
            $stmtItems->execute([':id' => $invoiceId]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // 4. Fetch allocated payments for this invoice (if authorized)
            $sqlPayments = "SELECT p.payment_id, p.payment_date, p.amount_paid, p.reference_number, p.receipt_number, pm.method_name FROM payment_allocations pa JOIN payments p ON pa.payment_id = p.payment_id JOIN payment_methods pm ON p.method_id = pm.method_id WHERE pa.invoice_id = :id ORDER BY p.payment_date ASC";
            $stmtPayments = $pdo->prepare($sqlPayments);
            $stmtPayments->execute([':id' => $invoiceId]);
            $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Portal Invoice View Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching invoice details."; // Assign specific error
        }


        // UPDATE the loadView call to pass the discounts
        $this->loadView('Portal/Views/invoice_detail', [
            'pageTitle' => 'Invoice #' . htmlspecialchars($invoice['invoice_number'] ?? $invoiceId),
            'invoice' => $invoice,
            'items' => $items,
            'payments' => $payments,
            'appliedDiscounts' => $appliedDiscounts, // <-- Pass applied discounts
            'viewError' => $viewError
        ], 'layout_portal');
    }

     /** Helper for DB error redirects */
     private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
          exit;
     }
}