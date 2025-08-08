<?php
namespace App\Modules\Portal\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use App\Core\Services\AuditLogService; // For logging password changes etc.
use PDO;

class PortalProfileController extends BaseController {

    private AuditLogService $auditLogService;

    public function __construct() {
        $this->auditLogService = new AuditLogService();
    }

    /**
     * Show the user's profile form.
     * Handles GET /portal/profile
     */
    public function show(): void {
        // --- Auth Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        $userId = $_SESSION['user_id'];
        // Allow any logged-in user (Student/Parent) to see their own profile
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) {
             $_SESSION['flash_error'] = "Access Denied.";
             $this->redirect('/logout'); return;
        }
        // --- End Checks ---

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/portal/dashboard'); return; }

        $user = null;
        $viewError = null;

        try {
             $stmt = $pdo->prepare("SELECT user_id, username, email, full_name, contact_number FROM users WHERE user_id = :id");
             $stmt->execute([':id' => $userId]);
             $user = $stmt->fetch(PDO::FETCH_ASSOC);

             if (!$user) { $this->handleDbErrorAndRedirect("User profile not found.", '/portal/dashboard'); return; }

        } catch (\PDOException $e) {
            $viewError = "Database error fetching profile.";
            error_log("Portal Profile Fetch Error: " . $e->getMessage());
        }

        // Use layout_portal
        $this->loadView('Portal/Views/profile', [
            'pageTitle' => 'My Profile',
            'user' => $user,
            'viewError' => $viewError, // Pass specific error
            // Errors/Old Input for validation need to be passed from update methods if redirecting back here
            'errors' => $_SESSION['form_errors'] ?? [],
            'oldInput' => $_SESSION['old_input'] ?? []
            // Global flash handled by layout
        ], 'layout_portal'); // <-- SPECIFY PORTAL LAYOUT

        unset($_SESSION['form_errors']); // Unset session vars after passing
        unset($_SESSION['old_input']);
    }

    /**
     * Update user's basic details (name, contact).
     * Handles POST /portal/profile/update
     */
    public function updateDetails(): void {
        // --- Auth & Method Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        $userId = $_SESSION['user_id'];
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) { $this->redirect('/logout'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/portal/profile'); return; }
        // --- End Checks ---

        // --- Validation & Retrieval ---
        $fullName = trim($_POST['full_name'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        // Note: Username and Email are typically NOT updated here for security/simplicity
        // --- End Validation ---

        // --- ADD Basic Validation Example ---
        $errors = [];
        if (strlen($fullName) > 100)
            $errors['full_name'] = "Full Name is too long.";
        if (!empty($contactNumber) && strlen($contactNumber) > 20)
            $errors['contact_number'] = "Contact number is too long.";
        // Add more validation if needed

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $_POST; // Store submitted data
            $this->redirect('/portal/profile'); // Redirect back to profile page
            return;
        }
        // --- End Validation ---


        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/portal/profile'); return; }

        try {
             $sql = "UPDATE users SET full_name = :fname, contact_number = :contact, updated_at = NOW() WHERE user_id = :id";
             $stmt = $pdo->prepare($sql);
             $success = $stmt->execute([
                 ':fname' => $fullName ?: null,
                 ':contact' => $contactNumber ?: null,
                 ':id' => $userId
             ]);

             if ($success) {
                 // Log audit event
                 $this->auditLogService->log($userId, 'USER_PROFILE_UPDATE', 'users', $userId, ['updated_fields' => ['full_name', 'contact_number']]);
                 $_SESSION['flash_success'] = "Profile details updated successfully.";
             } else {
                  $_SESSION['flash_error'] = "Failed to update profile details.";
             }
        } catch (\PDOException $e) {
             error_log("Portal Profile Update Error: " . $e->getMessage());
             $_SESSION['flash_error'] = "Database error updating profile.";
        }

        $this->redirect('/portal/profile');
    }

    /**
     * Update user's password.
     * Handles POST /portal/profile/change-password
     */
    public function updatePassword(): void {
        // --- Auth & Method Check ---
         if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
         $userId = $_SESSION['user_id'];
         if (!$this->hasRole('Student') && !$this->hasRole('Parent')) { $this->redirect('/logout'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/portal/profile'); return; }
        // --- End Checks ---

         // --- Validation & Retrieval ---
         $currentPassword = $_POST['current_password'] ?? '';
         $newPassword = $_POST['new_password'] ?? '';
         $confirmPassword = $_POST['confirm_password'] ?? '';

         $errors = [];
         if (empty($currentPassword)) $errors[] = "Current Password is required.";
         if (empty($newPassword)) $errors[] = "New Password is required.";
         // Add complexity rules for new password if desired
         if ($newPassword !== $confirmPassword) $errors[] = "New Password and Confirm Password do not match.";
         if (strlen($newPassword) < 6 && !empty($newPassword)) $errors[] = "New Password must be at least 6 characters long."; // Example minimum length

         if (!empty($errors)) {
             $_SESSION['flash_error'] = implode('<br>', $errors);
             $this->redirect('/portal/profile');
             return;
         }
         // --- End Validation ---


         $pdo = DbConnection::getInstance();
         if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/portal/profile'); return; }

         try {
            // 1. Fetch current password hash
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = :id");
            $stmt->execute([':id' => $userId]);
            $currentHash = $stmt->fetchColumn();

            if (!$currentHash) { throw new \Exception("Could not find user record."); } // Should not happen if logged in

            // 2. Verify current password
            if (!password_verify($currentPassword, $currentHash)) {
                $_SESSION['flash_error'] = "Incorrect Current Password.";
                $this->redirect('/portal/profile');
                return;
            }

            // 3. Hash new password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

            // 4. Update database
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :new_hash, updated_at = NOW() WHERE user_id = :id");
            $success = $updateStmt->execute([
                ':new_hash' => $newPasswordHash,
                ':id' => $userId
            ]);

            if ($success) {
                 // Log audit event
                 $this->auditLogService->log($userId, 'USER_PASSWORD_CHANGE', 'users', $userId);
                 $_SESSION['flash_success'] = "Password updated successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to update password.";
            }

         } catch (\Exception $e) { // Catch PDO or other exceptions
              error_log("Portal Password Update Error: " . $e->getMessage());
              $_SESSION['flash_error'] = "An error occurred changing password.";
         }

        $this->redirect('/portal/profile');
    }


    /** Helper for DB error redirects (could be in BaseController) */
    private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
         $_SESSION['flash_error'] = $message;
         $this->redirect($redirectTo);
    }
}