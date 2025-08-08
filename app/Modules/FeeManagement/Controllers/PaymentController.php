<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use App\Core\Services\AuditLogService;
use App\Core\Services\NotificationService; // For sending confirmation
use PDO;
use PDOException; // Explicitly use PDOException for catch blocks
use DateTime; // For date handling

class PaymentController extends BaseController {

    // Define storage path CONSTANT or load from config
    // IMPORTANT: Ensure this path is OUTSIDE your web root (e.g., not inside 'public')
    // Adjust the path based on your project structure relative to this file
    private AuditLogService $auditLogService; // Type hint added previously

    private const UPLOAD_PATH = __DIR__ . '/../../../../storage/payment_proofs/';

    private NotificationService $notificationService; // For sending confirmations

    public function __construct() {
        // Instantiate services potentially used by multiple methods
        $this->notificationService = new NotificationService();
        // Note: Consider dependency injection later for better testability
        $this->auditLogService = new AuditLogService(); // Instantiation
    }

    /**
     * List payment proofs awaiting verification.
     * Handles GET /admin/payments/proofs
     */
    public function listPendingProofs(): void {
         // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot verify payments.";
            $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        $proofs = [];
        $viewError = null; // Specific error variable
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
             try {
                // Fetch pending proofs joining necessary info
                $sql = "SELECT pp.*, fi.invoice_number, fi.total_payable, fi.total_paid, (fi.total_payable - fi.total_paid) as balance_due,
                               s.first_name, s.last_name, s.admission_number,
                               u.username AS uploader_username
                        FROM payment_proofs pp
                        JOIN fee_invoices fi ON pp.invoice_id = fi.invoice_id
                        JOIN students s ON pp.student_id = s.student_id
                        LEFT JOIN users u ON pp.uploader_user_id = u.user_id
                        WHERE pp.status = 'pending'
                        ORDER BY pp.uploaded_at ASC";
                $stmt = $pdo->query($sql);
                $proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);
             } catch (PDOException $e) {
                 $viewError = "Database query failed: " . $e->getMessage();
                 error_log("List Pending Proofs Error: " . $e->getMessage());
             }
        }

         $this->loadView('FeeManagement/Views/payments/list_proofs', [
            'pageTitle' => 'Pending Payment Proofs',
            'proofs' => $proofs,
            'viewError' => $viewError // Use viewError instead of dbError for consistency
            // Global flash handled by layout
        ], 'layout_admin');
    }


    /**
     * Show the form for recording a payment, potentially pre-filled from proof verification.
     * Handles GET /admin/payments/record/{invoice_id} (?proof_id=xx)
     */
    public function showRecordForm(array $vars): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot record payments.";
            $this->redirect('/dashboard'); return;
        }
        // --- End Access Control ---

        $invoiceId = $vars['invoice_id'] ?? null;
        $proofId = filter_input(INPUT_GET, 'proof_id', FILTER_VALIDATE_INT); // Get proof_id from GET

        if (!$invoiceId) { $this->handleDbErrorAndRedirect("Invalid Invoice ID.", '/admin/fees/invoices'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/invoices'); return; }

        $invoice = null;
        $paymentMethods = [];
        $proofData = null;
        $viewError = null;

        try {
            // Fetch invoice details
            $sqlInvoice = "SELECT fi.*, s.first_name, s.last_name, s.student_id, -- Make sure student_id is fetched
                                  (fi.total_payable - fi.total_paid) AS balance_due
                           FROM fee_invoices fi
                           JOIN students s ON fi.student_id = s.student_id
                           WHERE fi.invoice_id = :id";
            $stmtInvoice = $pdo->prepare($sqlInvoice);
            $stmtInvoice->execute([':id' => $invoiceId]);
            $invoice = $stmtInvoice->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) { $this->handleDbErrorAndRedirect("Invoice not found.", '/admin/fees/invoices'); return; }

            // Check status only if NOT verifying a proof
             if (in_array($invoice['status'], ['paid', 'cancelled']) && !$proofId) {
                 $this->handleDbErrorAndRedirect("Payment cannot be recorded for an already '{$invoice['status']}' invoice.", '/admin/fees/invoices'); return;
             }

            // Fetch active payment methods
            $stmtMethods = $pdo->query("SELECT method_id, method_name FROM payment_methods WHERE is_active = TRUE ORDER BY method_name ASC");
            $paymentMethods = $stmtMethods->fetchAll(PDO::FETCH_KEY_PAIR);

            // Fetch proof data if verifying
            if ($proofId) {
                 $stmtProof = $pdo->prepare("SELECT pp.*, u.username as uploader_username
                                            FROM payment_proofs pp
                                            LEFT JOIN users u ON pp.uploader_user_id = u.user_id
                                            WHERE pp.proof_id = :pid AND pp.invoice_id = :invid AND pp.status = 'pending'");
                 $stmtProof->execute([':pid' => $proofId, ':invid' => $invoiceId]);
                 $proofData = $stmtProof->fetch(PDO::FETCH_ASSOC);
                 if (!$proofData) {
                     $_SESSION['flash_warning'] = "Could not find matching pending proof record (#{$proofId}) for this invoice.";
                     $proofId = null; // Unset proofId if not found/matched/pending
                 }
            }

        } catch (PDOException $e) {
            error_log("Show Payment Form Error: " . $e->getMessage());
            $viewError = "Database error fetching data for payment form.";
        }

        $this->loadView('FeeManagement/Views/payments/record_form', [
            'pageTitle' => ($proofId ? 'Verify Proof & ' : '') . 'Record Payment for Invoice #' . htmlspecialchars($invoice['invoice_number'] ?? $invoiceId),
            'invoice' => $invoice,
            'paymentMethods' => $paymentMethods,
            'proofData' => $proofData, // Pass proof data if found
            'proofId' => $proofId,     // Pass proof ID if verifying
            'viewError' => $viewError, // Pass specific error
            // Errors/Old Input for validation repopulation (if validation added here)
            'errors' => $_SESSION['form_errors'] ?? [],
            'oldInput' => $_SESSION['old_input'] ?? []
            // Global flash handled by layout
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }


    /**
     * Store a newly recorded payment, potentially verifying a proof.
     * Handles POST /admin/payments
     */
    public function store(): void {
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/invoices'); return; }
        // --- End Checks ---

        // --- Data Validation & Retrieval ---
        $invoiceId = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        $amountPaid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
        $paymentDateStr = $_POST['payment_date'] ?? date('Y-m-d');
        $methodId = filter_input(INPUT_POST, 'method_id', FILTER_VALIDATE_INT);
        $reference = trim($_POST['reference_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $currentUserId = $_SESSION['user_id'] ?? null;
        $verifiedProofId = filter_input(INPUT_POST, 'verified_proof_id', FILTER_VALIDATE_INT); // Get proof ID if present

        $errors = [];
        if (empty($invoiceId)) $errors[] = "Invoice ID is missing.";
        if (empty($studentId)) $errors[] = "Student ID is missing.";
        if ($amountPaid === false || $amountPaid <= 0) $errors[] = "Valid Amount Paid is required.";
        if (empty($paymentDateStr)) $errors[] = "Payment Date is required.";
        if (empty($methodId)) $errors[] = "Payment Method is required.";
        $paymentDate = DateTime::createFromFormat('Y-m-d', $paymentDateStr);
        if (!$paymentDate) { $errors[] = "Invalid Payment Date format (usexRtList-MM-DD)."; }
        else { $paymentDateStr = $paymentDate->format('Y-m-d H:i:s'); } // Format for DB only if valid

        $redirectUrl = '/admin/payments/record/' . $invoiceId . ($verifiedProofId ? '?proof_id=' . $verifiedProofId : ''); // URL to redirect back on error

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $this->redirect($redirectUrl);
            return;
        }
        // --- End Basic Validation ---

        // --- Database Operations ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/invoices'); return; }

        $pdo->beginTransaction(); // Start transaction
        $paymentId = null;
        $receiptNumber = null;
        $invoiceNumber = $invoiceId; // Default if fetch fails

        try {
            // 1. Fetch current invoice details (FOR UPDATE lock)
             $stmtInv = $pdo->prepare("SELECT invoice_number, total_payable, total_paid, status FROM fee_invoices WHERE invoice_id = :id FOR UPDATE");
             $stmtInv->execute([':id' => $invoiceId]);
             $invoice = $stmtInv->fetch(PDO::FETCH_ASSOC);

             if (!$invoice) { throw new \Exception("Invoice #{$invoiceId} not found during payment processing."); }
             $invoiceNumber = $invoice['invoice_number']; // Get actual invoice number
             if (in_array($invoice['status'], ['paid', 'cancelled']) && !$verifiedProofId) { // Allow overriding if verifying proof
                 throw new \Exception("Payment cannot be recorded for an already '{$invoice['status']}' invoice.");
             }

            // 2. Insert into payments table
            $sqlPayment = "INSERT INTO payments (student_id, amount_paid, payment_date, method_id, reference_number, notes, receipt_number, payment_status, processed_by_user_id, created_at) VALUES (:sid, :amount, :pdate, :mid, :ref, :notes, :receipt, 'completed', :uid, NOW())";
            $stmtPayment = $pdo->prepare($sqlPayment);
            $receiptNumber = $this->generateReceiptNumber();
            $paramsPayment = [ ':sid' => $studentId, ':amount' => $amountPaid, ':pdate' => $paymentDateStr, ':mid' => $methodId, ':ref' => $reference ?: null, ':notes' => $notes ?: null, ':receipt' => $receiptNumber, ':uid' => $currentUserId ];
            if(!$stmtPayment->execute($paramsPayment)) { throw new \Exception("Failed to insert payment record."); }
            $paymentId = $pdo->lastInsertId();
            if (!$paymentId) { throw new \Exception("Failed to get payment ID after insert."); }


            // 3. Insert into payment_allocations table
            $sqlAlloc = "INSERT INTO payment_allocations (payment_id, invoice_id, allocated_amount, allocation_date) VALUES (:pid, :invid, :amount, NOW())";
            $stmtAlloc = $pdo->prepare($sqlAlloc);
            if(!$stmtAlloc->execute([':pid' => $paymentId, ':invid' => $invoiceId, ':amount' => $amountPaid])) { throw new \Exception("Failed to insert payment allocation."); }


            // 4. Update fee_invoices table
            $newTotalPaid = (float)$invoice['total_paid'] + $amountPaid;
            $newBalance = (float)$invoice['total_payable'] - $newTotalPaid;
            $newStatus = ($newBalance <= 0.001) ? 'paid' : 'partially_paid'; // Determine new status

            $sqlInvUpdate = "UPDATE fee_invoices SET total_paid = :paid, status = :status, updated_at = NOW() WHERE invoice_id = :id";
            $stmtInvUpdate = $pdo->prepare($sqlInvUpdate);
            if(!$stmtInvUpdate->execute([':paid' => $newTotalPaid, ':status' => $newStatus, ':id' => $invoiceId])) { throw new \Exception("Failed to update invoice status."); }


            // 5. Update Payment Proof Status (if applicable)
            if ($verifiedProofId) {
                $sqlProof = "UPDATE payment_proofs SET status = 'verified', verified_by_user_id = :admin_id, verified_at = NOW(), payment_id = :payment_id WHERE proof_id = :proof_id AND status = 'pending'";
                $stmtProof = $pdo->prepare($sqlProof);
                if(!$stmtProof->execute([':admin_id' => $currentUserId, ':payment_id' => $paymentId, ':proof_id' => $verifiedProofId])) { throw new \Exception("Failed to update payment proof status."); }
                // Check $stmtProof->rowCount() > 0 if needed
            }

            $pdo->commit(); // Commit transaction AFTER all steps succeed

            // --- Trigger Notification (outside transaction) ---
            try {
                // ... (fetch student info like before) ...
                $stmtStudent = $pdo->prepare("SELECT s.first_name, s.last_name, s.email AS student_email, s.user_id, u.email AS user_email FROM students s LEFT JOIN users u ON s.user_id = u.user_id WHERE s.student_id = ?");
                $stmtStudent->execute([$studentId]);
                $studentInfo = $stmtStudent->fetch(PDO::FETCH_ASSOC);

                $targetUserId = $studentInfo['user_id'] ?? null;
                $recipientEmail = $studentInfo['user_email'] ?: ($studentInfo['student_email'] ?? null);
                $recipientDetailForLog = (!$targetUserId && $recipientEmail) ? $recipientEmail : null;

                if (!empty($recipientEmail)) {
                    $notificationData = [ /* ... prepare data ... */
                         'student_name' => ($studentInfo['first_name'] ?? '') . ' ' . ($studentInfo['last_name'] ?? ''),
                         'amount_paid' => number_format($amountPaid, 2),
                         'payment_date' => $paymentDate->format('Y-m-d'), // Use formatted date object
                         'receipt_number' => $receiptNumber,
                         'invoice_number' => $invoiceNumber,
                         'reference_number' => $reference ?: 'N/A',
                         'currency_symbol' => 'Rs.'
                    ];
                    $this->notificationService->sendNotification( $targetUserId, 'PAYMENT_CONFIRMATION', $notificationData, $recipientDetailForLog, $paymentId, 'payment' );
                } else {
                    error_log("NotificationService Warning: No recipient email found for student ID {$studentId} for template PAYMENT_CONFIRMATION.");
                }
            } 
            catch (\Exception $e) {
                // Ensure it's $e->getMessage(), NOT just getMessage()
                error_log("Failed to send/log payment confirmation for Payment ID {$paymentId}: " . $e->getMessage());
                // You don't need to redirect here, just log the notification failure
            }
            // --- End Notification Trigger ---

            $_SESSION['flash_success'] = "Payment recorded successfully. Receipt: " . htmlspecialchars($receiptNumber) . ($verifiedProofId ? " (Proof Verified)" : "");

        } catch (\Exception $e) {
             if ($pdo->inTransaction()) { $pdo->rollBack(); }
             error_log("Payment Store Error: " . $e->getMessage());
             $this->handleDbErrorAndRedirect("Error recording payment: " . $e->getMessage(), $redirectUrl);
             return;
        }

        $this->redirect('/admin/fees/invoices'); // Redirect to invoice list after success
    }


    /**
     * Reject an uploaded payment proof.
     * Handles POST /admin/payments/proofs/reject
     */
    public function rejectProof(): void {
         // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/payments/proofs'); return; }
        // --- End Checks ---

        $proofId = filter_input(INPUT_POST, 'proof_id', FILTER_VALIDATE_INT);
        $notes = trim($_POST['admin_notes'] ?? '');

        if (!$proofId) { $this->handleDbErrorAndRedirect("Invalid Proof ID for rejection.", '/admin/payments/proofs'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/payments/proofs'); return; }

        try {
            $sql = "UPDATE payment_proofs SET status = 'rejected', admin_notes = :notes, verified_by_user_id = :admin_id, verified_at = NOW() WHERE proof_id = :proof_id AND status = 'pending'";
             $stmt = $pdo->prepare($sql);
             $success = $stmt->execute([
                 ':notes' => $notes ?: null,
                 ':admin_id' => $_SESSION['user_id'] ?? null,
                 ':proof_id' => $proofId
             ]);

            if ($success && $stmt->rowCount() > 0) {
                 $_SESSION['flash_success'] = "Payment proof rejected successfully.";
                 // TODO: Optionally notify the uploader about the rejection?
             } else {
                 $_SESSION['flash_error'] = "Failed to reject proof (maybe not pending or not found?).";
             }
        } catch (PDOException $e) {
             error_log("Reject Proof Error: " . $e->getMessage());
             $_SESSION['flash_error'] = "Database error rejecting proof.";
        }
        $this->redirect('/admin/payments/proofs');
    }


    /**
     * Securely outputs an uploaded proof file.
     * Handles GET /admin/payments/proofs/view-file/{proof_id}
     */
    public function viewProofFile(array $vars): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             http_response_code(403); echo "Access Denied."; exit;
         }
        // --- End Access Control ---

        $proofId = $vars['proof_id'] ?? null;
        if (!$proofId) { http_response_code(400); echo "Invalid Proof ID."; exit; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { http_response_code(500); echo "Database error."; exit; }

        try {
            $stmt = $pdo->prepare("SELECT file_name, file_path FROM payment_proofs WHERE proof_id = :id");
            $stmt->execute([':id' => $proofId]);
            $fileInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fileInfo) { http_response_code(404); echo "Proof record not found."; exit; }

            // Construct path using the constant and ensure no directory traversal
            $filePath = realpath(self::UPLOAD_PATH . basename($fileInfo['file_path']));
            $baseUploadPath = realpath(self::UPLOAD_PATH);

             // Security Check: Ensure the resolved path is within the intended upload directory
            if (!$filePath || !$baseUploadPath || strpos($filePath, $baseUploadPath) !== 0) {
                error_log("View Proof File Access Denied: Path mismatch. Base: {$baseUploadPath}, Requested: {$filePath}");
                http_response_code(403); echo "Access Denied."; exit;
            }


            if (!file_exists($filePath) || !is_readable($filePath)) {
                 error_log("View Proof File Error: File not found or not readable at calculated path: {$filePath}");
                 http_response_code(404); echo "Proof file not found on server or not readable."; exit;
            }

            // Determine MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            // Set headers and output file
            header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
            header('Content-Disposition: inline; filename="' . basename($fileInfo['file_name']) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            ob_clean();
            flush();
            readfile($filePath);
            exit;

        } catch (PDOException $e) {
             error_log("View Proof File Error: " . $e->getMessage());
             http_response_code(500); echo "Database error retrieving file information."; exit;
        } catch (\Exception $e) {
             error_log("View Proof File Error: " . $e->getMessage());
             http_response_code(500); echo "Server error retrieving file."; exit;
        }
    }

    /**
     * Show the form for recording a refund against a specific payment.
     * Handles GET /admin/payments/{payment_id}/refund
     */
    public function showRefundForm(array $vars): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot process refunds.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        $paymentId = $vars['payment_id'] ?? null;
        if (!$paymentId) { $this->handleDbErrorAndRedirect("Invalid Payment ID.", '/admin/fees/invoices'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/invoices'); return; }

        $payment = null;
        $viewError = null;
        $invoiceId = null; // To find the invoice for the back button

        try {
             // Fetch payment details, join student and method
             $sql = "SELECT p.*, s.first_name, s.last_name, pm.method_name, pa.invoice_id
                     FROM payments p
                     JOIN students s ON p.student_id = s.student_id
                     JOIN payment_methods pm ON p.method_id = pm.method_id
                     LEFT JOIN payment_allocations pa ON p.payment_id = pa.payment_id -- To get invoice ID
                     WHERE p.payment_id = :id";
             $stmt = $pdo->prepare($sql);
             $stmt->execute([':id' => $paymentId]);
             $payment = $stmt->fetch(PDO::FETCH_ASSOC);

             if (!$payment) { $this->handleDbErrorAndRedirect("Payment record not found.", '/admin/fees/invoices'); return; }

             // Check if payment can be refunded
             if ($payment['payment_status'] === 'refunded') {
                 $this->handleDbErrorAndRedirect("This payment has already been fully refunded.", '/admin/fees/invoices/view/' . $payment['invoice_id']); return;
             }
             if ($payment['payment_status'] !== 'completed' && $payment['payment_status'] !== 'partially_refunded') {
                  $this->handleDbErrorAndRedirect("Only 'completed' or 'partially_refunded' payments can be refunded.", '/admin/fees/invoices/view/' . $payment['invoice_id']); return;
             }
              $invoiceId = $payment['invoice_id']; // Get invoice ID for back button

        } catch (PDOException $e) {
             error_log("Show Refund Form Error: " . $e->getMessage());
             $viewError = "Database error fetching payment details.";
        }

        $this->loadView('FeeManagement/Views/payments/refund_form', [
            'pageTitle' => 'Record Refund for Payment #' . htmlspecialchars($payment['receipt_number'] ?? $paymentId),
            'payment' => $payment, // Pass payment data
            'invoiceId' => $invoiceId, // For back button link
            'viewError' => $viewError,
            'errors' => $_SESSION['form_errors'] ?? [],     // Pass validation errors if any
            'oldInput' => $_SESSION['old_input'] ?? []   // Pass old input if any
            // Global flash handled by layout
        ], 'layout_admin'); // <-- Ensure layout_admin is specified

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }


    /**
     * Process a refund against a specific payment.
     * Handles POST /admin/payments/refund
     */
    public function processRefund(): void {
        // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/invoices'); return; }
        // --- End Checks ---

        // --- Validation & Retrieval ---
        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $refundAmount = filter_input(INPUT_POST, 'refund_amount', FILTER_VALIDATE_FLOAT);
        $refundDateStr = $_POST['refund_date'] ?? date('Y-m-d');
        $refundReason = trim($_POST['refund_reason'] ?? '');
        $currentUserId = $_SESSION['user_id'] ?? null;
        $invoiceId = null; // Will fetch this inside transaction

        $errors = [];
        if (empty($paymentId)) $errors[] = "Original Payment ID is missing.";
        if ($refundAmount === false || $refundAmount <= 0) $errors[] = "Valid Refund Amount (> 0) is required.";
        if (empty($refundDateStr)) $errors[] = "Refund Date is required.";
        $refundDate = DateTime::createFromFormat('Y-m-d', $refundDateStr);
         if (!$refundDate) { $errors[] = "Invalid Refund Date format (usecountrygeocode-MM-DD)."; }
         else { $refundDateStr = $refundDate->format('Y-m-d H:i:s'); } // Format for DB
         // --- End Validation ---

         // Redirect URL in case of validation error depends on getting invoice ID later
         $baseRedirectUrl = '/admin/fees/invoices'; // Fallback redirect

         if (!empty($errors)) {
             $_SESSION['flash_error'] = implode('<br>', $errors);
             // Cannot redirect back to specific refund form easily without payment ID in URL
             $this->redirect($baseRedirectUrl); // Redirect to general invoice list
             return;
         }

        // --- Database Operations ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", $baseRedirectUrl); return; }

        $pdo->beginTransaction();
        try {
            // 1. Fetch original payment and associated invoice FOR UPDATE
            $sqlFetch = "SELECT p.amount_paid, p.refunded_amount, p.student_id, pa.invoice_id,
                               fi.total_paid, fi.total_payable
                        FROM payments p
                        JOIN payment_allocations pa ON p.payment_id = pa.payment_id -- Assumes one allocation
                        JOIN fee_invoices fi ON pa.invoice_id = fi.invoice_id
                        WHERE p.payment_id = :pid FOR UPDATE"; // Lock rows
            $stmtFetch = $pdo->prepare($sqlFetch);
            $stmtFetch->execute([':pid' => $paymentId]);
            $data = $stmtFetch->fetch(PDO::FETCH_ASSOC);

            if (!$data) { throw new \Exception("Could not find payment or associated invoice details."); }
            $invoiceId = $data['invoice_id']; // Get invoice ID for final redirect
            $baseRedirectUrl = '/admin/fees/invoices/view/' . $invoiceId; // Update redirect target

            $originalPaid = (float)$data['amount_paid'];
            $alreadyRefunded = (float)($data['refunded_amount'] ?? 0.00);
            $maxRefundable = $originalPaid - $alreadyRefunded;

            if ($refundAmount > ($maxRefundable + 0.001)) {
                throw new \Exception("Refund amount (".number_format($refundAmount,2).") cannot exceed the remaining refundable amount (".number_format($maxRefundable,2).").");
            }

            // 2. Update Payment Record
            $newRefundedTotal = $alreadyRefunded + $refundAmount;
            $newPaymentStatus = ($newRefundedTotal >= ($originalPaid - 0.001)) ? 'refunded' : 'partially_refunded';

            $sqlPaymentUpdate = "UPDATE payments SET
                                    refunded_amount = :refund_amt,
                                    refund_date = :refund_date,
                                    refund_reason = :reason,
                                    refunded_by_user_id = :admin_id,
                                    payment_status = :status, -- Update status
                                    updated_at = NOW()
                                WHERE payment_id = :pid";
            $stmtPaymentUpdate = $pdo->prepare($sqlPaymentUpdate);
            if (!$stmtPaymentUpdate->execute([
                    ':refund_amt' => $newRefundedTotal,
                    ':refund_date' => $refundDateStr,
                    ':reason' => $refundReason ?: null,
                    ':admin_id' => $currentUserId,
                    ':status' => $newPaymentStatus,
                    ':pid' => $paymentId
                ])) { throw new \Exception("Failed to update payment record for refund."); }


            // 3. Update Invoice Record
            $newInvoiceTotalPaid = (float)$data['total_paid'] - $refundAmount;
            $newInvoiceBalance = (float)$data['total_payable'] - $newInvoiceTotalPaid;
            $newInvoiceStatus = 'unpaid'; // Default if becomes unpaid
            if ($newInvoiceBalance <= 0.001) { $newInvoiceStatus = 'paid'; }
            elseif ($newInvoiceTotalPaid > 0.001) { $newInvoiceStatus = 'partially_paid'; }

            $sqlInvUpdate = "UPDATE fee_invoices SET
                                total_paid = :paid,
                                status = :status,
                                updated_at = NOW()
                             WHERE invoice_id = :id";
            $stmtInvUpdate = $pdo->prepare($sqlInvUpdate);
             if (!$stmtInvUpdate->execute([
                    ':paid' => $newInvoiceTotalPaid,
                    ':status' => $newInvoiceStatus,
                    ':id' => $invoiceId
                ])) { throw new \Exception("Failed to update invoice totals after refund."); }


            // 4. Update Payment Allocation (Simplistic: Reduce allocation amount)
            // More complex logic needed if one payment covers multiple invoices etc.
             $sqlAllocUpdate = "UPDATE payment_allocations SET allocated_amount = allocated_amount - :refund_amt
                               WHERE payment_id = :pid AND invoice_id = :invid";
             $stmtAllocUpdate = $pdo->prepare($sqlAllocUpdate);
             if (!$stmtAllocUpdate->execute([':refund_amt' => $refundAmount, ':pid' => $paymentId, ':invid' => $invoiceId])) {
                  error_log("Warning: Failed to update payment allocation amount for refund on Payment ID {$paymentId}");
                  // Decide if this should be a fatal error / rollback
             }

            $pdo->commit(); // Commit transaction

            // Log Audit Event
             $this->auditLogService->log(
                 $currentUserId,
                 'PAYMENT_REFUNDED',
                 'payments',
                 $paymentId,
                 ['amount' => $refundAmount, 'reason' => $refundReason, 'invoice_id' => $invoiceId],
                 $_SERVER['REMOTE_ADDR'] ?? null,
                 $_SERVER['HTTP_USER_AGENT'] ?? null
             );

            $_SESSION['flash_success'] = "Refund of " . number_format($refundAmount, 2) . " processed successfully for Payment ID {$paymentId}.";

        } catch (\Exception $e) {
             if ($pdo->inTransaction()) { $pdo->rollBack(); }
             error_log("Process Refund Error: " . $e->getMessage());
             $this->handleDbErrorAndRedirect("Error processing refund: " . $e->getMessage(), $baseRedirectUrl);
             return;
        }

         $this->redirect($baseRedirectUrl); // Redirect back to invoice detail page
    }


    /** Helper to generate a unique receipt number (example) */
    private function generateReceiptNumber(): string {
        return 'RCPT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    }

    /** Helper for DB error redirects (could be in BaseController) */
     private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
     }

} // End Class