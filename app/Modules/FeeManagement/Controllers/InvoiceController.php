<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;

class InvoiceController extends BaseController {

    /**
     * Display a list of generated fee invoices. (Handles GET /admin/fees/invoices)
     */
    public function index(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot view invoices.";
            $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        $invoices = [];
        $viewError = null; // Specific error variable
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                // Fetch invoices joining necessary tables for display
                // Calculate balance due directly in the query
                $sql = "SELECT
                            fi.invoice_id, fi.invoice_number, fi.issue_date, fi.due_date,
                            fi.total_payable, fi.total_paid, fi.status,
                            (fi.total_payable - fi.total_paid) AS balance_due, -- Calculate balance
                            s.student_id, s.first_name, s.last_name,
                            c.class_name,
                            acs.session_name
                        FROM fee_invoices fi
                        JOIN students s ON fi.student_id = s.student_id
                        JOIN academic_sessions acs ON fi.session_id = acs.session_id
                        LEFT JOIN classes c ON s.current_class_id = c.class_id -- Use student's current class
                        ORDER BY fi.issue_date DESC, fi.invoice_id DESC";
                        // Add LIMIT/OFFSET later for pagination

                $stmt = $pdo->query($sql);
                $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Invoice List Fetch Error: " . $e->getMessage());
            }
        }

         $this->loadView('FeeManagement/Views/invoices/index', [
            'invoices' => $invoices,
            'pageTitle' => 'Fee Invoices',
            'viewError' => $viewError // Pass specific error
            // Global flash handled by layout
        ], 'layout_admin');
    }

    /**
     * Display the details of a specific fee invoice. (Handles GET /admin/fees/invoices/view/{id})
     */
    /**
     * Display the details of a specific fee invoice, including discount info.
     * Handles GET /admin/fees/invoices/view/{id}
     */
    public function view(array $vars): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot view invoice details.";
            $this->redirect('/dashboard');
            return;
        }
        // --- End Access Control ---

        $invoiceId = $vars['id'] ?? null;
        if (!$invoiceId) {
            // Assuming handleDbErrorAndRedirect exists and calls exit/return
            $this->handleDbErrorAndRedirect("Invalid Invoice ID.", '/admin/fees/invoices');
            return;
        }

        $pdo = DbConnection::getInstance();
        if (!$pdo) {
             // Assuming handleDbErrorAndRedirect exists and calls exit/return
            $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/invoices');
            return;
        }

        $invoice = null;
        $items = [];
        $payments = [];
        $appliedDiscounts = []; // <--- Initialize
        $availableDiscounts = []; // <--- Initialize
        $viewError = null;

        try {
            // 1. Fetch main invoice data (includes balance_due calculation)
            $sqlInvoice = "SELECT fi.*, s.first_name, s.last_name, c.class_name, acs.session_name,
                                  (fi.total_payable - fi.total_paid) AS balance_due
                           FROM fee_invoices fi
                           JOIN students s ON fi.student_id = s.student_id
                           JOIN academic_sessions acs ON fi.session_id = acs.session_id
                           LEFT JOIN classes c ON s.current_class_id = c.class_id
                           WHERE fi.invoice_id = :id";
            $stmtInvoice = $pdo->prepare($sqlInvoice);
            $stmtInvoice->bindParam(':id', $invoiceId, PDO::PARAM_INT); // Use bindParam or pass array to execute
            $stmtInvoice->execute();
            $invoice = $stmtInvoice->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) {
                $this->handleDbErrorAndRedirect("Invoice not found.", '/admin/fees/invoices');
                return;
            }

            // 2. Fetch invoice items
            $sqlItems = "SELECT fii.*, fc.category_name
                         FROM fee_invoice_items fii
                         JOIN fee_categories fc ON fii.category_id = fc.category_id
                         WHERE fii.invoice_id = :id ORDER BY fii.item_id ASC";
            $stmtItems = $pdo->prepare($sqlItems);
            $stmtItems->execute([':id' => $invoiceId]); // Pass param array to execute
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // 3. Fetch allocated payments for this invoice
            $sqlPayments = "SELECT p.payment_id, p.payment_date, p.amount_paid, p.payment_status, p.refunded_amount, p.reference_number, p.receipt_number, pm.method_name
                            FROM payment_allocations pa
                            JOIN payments p ON pa.payment_id = p.payment_id
                            JOIN payment_methods pm ON p.method_id = pm.method_id
                            WHERE pa.invoice_id = :id
                            ORDER BY p.payment_date ASC";
            $stmtPayments = $pdo->prepare($sqlPayments);
            $stmtPayments->execute([':id' => $invoiceId]);
            $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

            // --- 4. Fetch Applied Discounts --- ADDED ---
            $sqlApplied = "SELECT id.invoice_discount_id, id.applied_amount, id.notes,
                                  dt.name as discount_name, dt.type as discount_type, dt.value as discount_value
                           FROM invoice_discounts id
                           JOIN discount_types dt ON id.discount_type_id = dt.discount_type_id
                           WHERE id.invoice_id = :id ORDER BY id.applied_at ASC";
            $stmtApplied = $pdo->prepare($sqlApplied);
            $stmtApplied->execute([':id' => $invoiceId]);
            $appliedDiscounts = $stmtApplied->fetchAll(PDO::FETCH_ASSOC);
            // --- End Fetch Applied ---

            // --- 5. Fetch Available Active Discount Types --- ADDED ---
            // Fetches types not already applied to THIS invoice
             $sqlAvailable = "SELECT discount_type_id, name, type, value
                             FROM discount_types
                             WHERE is_active = TRUE
                               AND discount_type_id NOT IN (SELECT discount_type_id FROM invoice_discounts WHERE invoice_id = :id)
                             ORDER BY name ASC";
             $stmtAvailable = $pdo->prepare($sqlAvailable);
             $stmtAvailable->execute([':id' => $invoiceId]);
             $availableDiscounts = $stmtAvailable->fetchAll(PDO::FETCH_ASSOC);
            // --- End Fetch Available ---


        } catch (PDOException $e) {
            error_log("Invoice View Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching invoice details.";
        }

        // Pass all data arrays to the view
        $this->loadView('FeeManagement/Views/invoices/view', [
            'pageTitle' => 'Invoice Details #' . htmlspecialchars($invoice['invoice_number'] ?? $invoiceId),
            'invoice' => $invoice, // Might be null if error occurred before fetch
            'items' => $items,
            'payments' => $payments,
            'appliedDiscounts' => $appliedDiscounts,       // <-- Pass applied
            'availableDiscounts' => $availableDiscounts,   // <-- Pass available
            'viewError' => $viewError // Pass specific errors
            // Global flash handled by layout
        ], 'layout_admin');

    }

    // --- handleDbErrorAndRedirect method (should be here or in BaseController) ---
    private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
        $_SESSION['flash_error'] = $message;
        $this->redirect($redirectTo);
        exit; // Ensure exit after redirect
    }

}