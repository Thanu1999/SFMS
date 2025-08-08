<?php
namespace App\Modules\Portal\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use App\Core\Helpers\SettingsHelper;
use PDO;

class PortalPaymentController extends BaseController {

    // Define storage path CONSTANT or load from config
    // IMPORTANT: Ensure this path is OUTSIDE your web root (e.g., not inside 'public')
    // Adjust the path based on your project structure relative to this file
    private const UPLOAD_PATH = __DIR__ . '/../../../../storage/payment_proofs/';


    /**
     * Show offline payment instructions and proof upload form.
     * Handles GET /portal/payments/offline/{invoice_id}
     */
    public function showOfflinePaymentInstructions(array $vars): void {
        // --- Auth & Role Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) { $this->redirect('/logout'); return; }
        $userId = $_SESSION['user_id'];
        // --- End Checks ---

        $invoiceId = $vars['invoice_id'] ?? null;
        if (!$invoiceId) { $this->handleError("Invalid Invoice ID.", '/portal/fees'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleError("Database connection failed.", '/portal/fees'); return; }

        $invoice = null;
        $viewError = null;
        $bankDetails = []; // Load from config/settings

        // --- Read Flash Messages FIRST ---
        $flash_success = $_SESSION['flash_success'] ?? null; // Although unlikely to be set here
        $flash_error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success']);
        unset($_SESSION['flash_error']);
        // --- End Flash Handling ---

        try {
            // Fetch invoice ensuring it belongs to the user or their child
            $sqlInvoice = "SELECT fi.*, s.first_name, s.last_name, s.user_id AS student_user_id,
                                  (fi.total_payable - fi.total_paid) AS balance_due,
                                  (SELECT GROUP_CONCAT(sgl.user_id) FROM student_guardian_links sgl WHERE sgl.student_id = fi.student_id) AS guardian_user_ids
                           FROM fee_invoices fi
                           JOIN students s ON fi.student_id = s.student_id
                           WHERE fi.invoice_id = :invoiceId";
            $stmtInvoice = $pdo->prepare($sqlInvoice);
            $stmtInvoice->execute([':invoiceId' => $invoiceId]);
            $invoice = $stmtInvoice->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) { $this->handleError("Invoice not found.", '/portal/fees'); return; }

            $isAuthorized = false;
            $userRoles = $_SESSION['roles'] ?? []; // Get roles
            if (in_array('Student', $userRoles) && $invoice['student_user_id'] == $userId)
                $isAuthorized = true;
            if (in_array('Parent', $userRoles) && $invoice['guardian_user_ids'] && in_array($userId, explode(',', $invoice['guardian_user_ids'])))
                $isAuthorized = true;
            if (!$isAuthorized) {
                $this->handleError("Access Denied: Not your invoice.", '/portal/dashboard');
                return;
            }

            if (in_array($invoice['status'], ['paid', 'cancelled'])) {
                $this->handleError("This invoice is already '{$invoice['status']}'.", '/portal/fees'); return; }

            // --- Fetch Bank Details (from config or DB settings) ---
            // Example - replace with your actual loading mechanism
            // $settings = load_system_settings(); // Hypothetical function
            // $bankDetails['account_name'] = $settings['bank_account_name'] ?? 'Your School Name';
            // $bankDetails['account_number'] = $settings['bank_account_number'] ?? 'N/A';
            // $bankDetails['bank_name'] = $settings['bank_name'] ?? 'N/A';
            // $bankDetails['branch_name'] = $settings['bank_branch'] ?? 'N/A';
            $bankDetails = [
                'account_name' => SettingsHelper::get('bank_account_name', 'Default School Account Name'),
                'account_number' => SettingsHelper::get('bank_account_number', 'N/A'),
                'bank_name' => SettingsHelper::get('bank_name', 'N/A'),
                'branch_name' => SettingsHelper::get('bank_branch', 'N/A'),
                'reference_info' => SettingsHelper::get('bank_reference_info', 'Please include Student Admission Number or Invoice Number')
            ];


        } catch (PDOException $e) {
            error_log("Offline Payment Form Error: " . $e->getMessage());
            $viewError = "Database error fetching invoice details.";
        }

        $this->loadView('Portal/Views/offline_payment', [
            'pageTitle' => 'Offline Payment for Invoice #' . htmlspecialchars($invoice['invoice_number'] ?? $invoiceId),
            'invoice' => $invoice,
            'bankDetails' => $bankDetails,
            'viewError' => $viewError, // Pass specific error
            'flash_error' => $flash_error // Pass specific flash error (e.g., from failed upload)
            // Global flash handled by layout
        ], 'layout_portal');
    }


    /**
     * Handle the upload of payment proof.
     * Handles POST /portal/payments/upload-proof
     */
    public function handleProofUpload(): void {
         // --- Auth & Method Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        $userId = $_SESSION['user_id']; // Uploader ID
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) { $this->redirect('/logout'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/portal/dashboard'); return; }
        // --- End Checks ---
        // --- CSRF Check --- ADD THIS! ---
        if (!$this->validateCsrfToken($_POST['_csrf_token'] ?? null)) {
            $invoiceId = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
            $this->handleError("Invalid security token. Please try again.", '/portal/payments/offline/' . $invoiceId);
            return;
       }

        $invoiceId = filter_input(INPUT_POST, 'invoice_id', FILTER_VALIDATE_INT);
        $uploadedFile = $_FILES['payment_proof'] ?? null;

        // Basic Validation
        if (empty($invoiceId)) { $this->handleError("Missing invoice reference.", '/portal/dashboard'); return; }
        if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            $this->handleError("File upload error: " . $this->getUploadErrorMessage($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE), '/portal/payments/offline/' . $invoiceId); return;
        }

        // File Validation (Type, Size)
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            $this->handleError("Invalid file type. Allowed types: JPG, PNG, PDF.", '/portal/payments/offline/' . $invoiceId); return;
        }
        if ($uploadedFile['size'] > $maxFileSize) {
             $this->handleError("File is too large. Maximum size: " . ($maxFileSize / 1024 / 1024) . " MB.", '/portal/payments/offline/' . $invoiceId); return;
        }

        // --- Process Upload ---
        $pdo = DbConnection::getInstance();
         if (!$pdo) { $this->handleError("Database connection failed.", '/portal/payments/offline/' . $invoiceId); return; }

        // Ensure upload directory exists and is writable
        $uploadDir = self::UPLOAD_PATH;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) { // Create recursively if not exists
             error_log("Payment Proof Upload: Failed to create upload directory: {$uploadDir}");
             $this->handleError("Server error: Cannot save upload.", '/portal/payments/offline/' . $invoiceId); return;
        }
         if (!is_writable($uploadDir)) {
              error_log("Payment Proof Upload: Upload directory not writable: {$uploadDir}");
              $this->handleError("Server error: Cannot save upload (permission issue).", '/portal/payments/offline/' . $invoiceId); return;
         }


        // Generate unique filename
        $originalFileName = basename($uploadedFile['name']);
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $safeFileName = preg_replace("/[^a-zA-Z0-9._-]/", "_", pathinfo($originalFileName, PATHINFO_FILENAME)); // Sanitize
        $uniqueFileName = $safeFileName . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
        $destinationPath = $uploadDir . $uniqueFileName;
        $relativePathForDB = 'payment_proofs/' . $uniqueFileName; // Store relative path

        // Fetch student ID associated with the invoice (for logging)
        $stmtStudent = $pdo->prepare("SELECT student_id FROM fee_invoices WHERE invoice_id = ?");
        $stmtStudent->execute([$invoiceId]);
        $studentId = $stmtStudent->fetchColumn();

        if (!$studentId) {
             $this->handleError("Invalid invoice reference for upload.", '/portal/fees'); return;
        }

        // Move the file
        if (move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
            // --- Save proof reference to DB ---
            try {
                 $sql = "INSERT INTO payment_proofs (invoice_id, student_id, uploader_user_id, file_name, file_path, uploaded_at, status)
                         VALUES (:inv_id, :stu_id, :up_by, :fname, :fpath, NOW(), 'pending')";
                 $stmt = $pdo->prepare($sql);
                 $params = [
                    ':inv_id' => $invoiceId,
                    ':stu_id' => $studentId,
                    ':up_by' => $userId,
                    ':fname' => $originalFileName,
                    ':fpath' => $relativePathForDB // Store relative path
                 ];
                 if ($stmt->execute($params)) {
                    $_SESSION['flash_success'] = "Payment proof uploaded successfully. Awaiting verification.";
                    echo "<pre style='background:lightyellow; padding:10px; border:1px solid red;'>DEBUG (handleProofUpload): Flash message SET to: ";
                    var_dump($_SESSION['flash_success']);
                    echo "</pre>";
                    $this->redirect('/portal/fees'); // Redirect to fee list
                    return;
                 } else {
                      // Failed to save DB record - try to delete uploaded file?
                      unlink($destinationPath); // Attempt to clean up orphaned file
                      $this->handleError("Database error saving proof record.", '/portal/payments/offline/' . $invoiceId);
                 }
            } catch (\PDOException $e) {
                 unlink($destinationPath); // Attempt to clean up orphaned file
                 error_log("Save Payment Proof DB Error: " . $e->getMessage());
                 $this->handleError("Database error saving proof.", '/portal/payments/offline/' . $invoiceId);
            }

        } else {
            error_log("Payment Proof Upload: move_uploaded_file failed for {$uploadedFile['tmp_name']} to {$destinationPath}");
            $this->handleError("Failed to save uploaded file.", '/portal/payments/offline/' . $invoiceId);
        }
    }

    /** Helper for setting flash error and redirecting */
    private function handleError(string $message, string $redirectTo): void {
         $_SESSION['flash_error'] = $message;
         $this->redirect($redirectTo);
    }

    /** Helper for upload error messages */
    private function getUploadErrorMessage(int $errorCode): string {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE: return "File exceeds server upload size limit.";
            case UPLOAD_ERR_FORM_SIZE: return "File exceeds form upload size limit.";
            case UPLOAD_ERR_PARTIAL: return "File was only partially uploaded.";
            case UPLOAD_ERR_NO_FILE: return "No file was uploaded.";
            case UPLOAD_ERR_NO_TMP_DIR: return "Server missing a temporary folder.";
            case UPLOAD_ERR_CANT_WRITE: return "Failed to write file to disk.";
            case UPLOAD_ERR_EXTENSION: return "A PHP extension stopped the file upload.";
            default: return "Unknown upload error.";
        }
    }

     // Inherited redirect/hasRole assumed from BaseController
}