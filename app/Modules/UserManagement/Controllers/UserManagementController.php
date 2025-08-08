<?php
namespace App\Modules\UserManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection; // <-- Import DbConnection
use PDO; // <-- Import PDO

class UserManagementController extends BaseController {

    public function index(): void {
        // --- Explicit Auth Check ---
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }
        // --- End Auth Check ---

        // --- Role Check ---
        if (!$this->hasRole('Admin')) {
             http_response_code(403);
             echo "<h1>Access Denied</h1><p>Only Admins can manage users.</p>";
             exit;
        }
        // --- End Role Check ---

        // --- Fetch Users from Database ---
        $users = []; // Default to empty array
        $viewError = null;
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
            // Log this error properly in a real app
        } else {
            try {
                // Fetch necessary user details
                $stmt = $pdo->query("SELECT user_id, username, email, full_name, status, created_at FROM users ORDER BY user_id ASC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("User List Fetch Error: " . $e->getMessage());
            }
        }
        // --- End Fetch Users ---


        // Use layout_admin, pass users and specific view error
        $this->loadView('UserManagement/Views/index', [
            'users' => $users,
            'pageTitle' => 'User Management',
            'viewError' => $viewError // Pass specific error
            // Flash messages handled by layout
        ], 'layout_admin');
        
    }

    /**
     * Show the form for creating a new user. (Handles GET /admin/users/create)
     */
    public function create(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin')) {
            $_SESSION['flash_error'] = "Access Denied: Only Admins can create users.";
            $this->redirect('/admin/users');
            return;
        }
        // --- End Access Control ---
        $csrfToken = $this->generateCsrfToken(); // Generate/get token

        // Fetch roles for the dropdown
        $roles = $this->getRoles(); // Assuming this helper exists from previous steps
        $viewError = empty($roles) ? "Could not load roles for form." : null; // Example specific error


        $this->loadView('UserManagement/Views/create', [
            'pageTitle' => 'Create New User',
            'roles' => $roles,
            '_csrf_token' => $csrfToken, // Pass token to view
            'viewError' => $viewError,
            'errors' => $_SESSION['form_errors'] ?? [], // For validation feedback
            'oldInput' => $_SESSION['old_input'] ?? []  // For repopulation
        ], 'layout_admin');
        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Store a newly created user in storage. (Handles POST /admin/users)
     */
    public function store(): void {
        // --- ADD CSRF Check FIRST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken($_POST['_csrf_token'] ?? null)) {
                // CSRF token is invalid or missing
                $_SESSION['flash_error'] = 'Invalid security token. Please try submitting the form again.';
                // Redirect back to the form or a safe page
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/dashboard'); // Redirect back if possible
                return;
            }
        } else {
            $this->redirect('/admin/fees/discount-types'); // Redirect if not POST
            return;
        }
        // --- END CSRF Check ---
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/users/create'); return; }
        // --- End Checks ---

        // --- Data Validation ---
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $status = $_POST['status'] ?? 'inactive'; // Default to inactive? Or active?
        $assignedRoles = $_POST['roles'] ?? []; // Expects an array of role_ids

        $errors = [];
        if (empty($username)) $errors[] = "Username is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email is required.";
        if (empty($password)) $errors[] = "Password is required for new users.";
        // Add more validation: password complexity, username format, check roles exist
        if (!in_array($status, ['active', 'inactive', 'pending_verification', 'locked'])) $status = 'inactive';
        // --- End Validation ---

        if (!empty($errors)) {
            // Redirect back to create form with errors (using session flash)
            $_SESSION['flash_error'] = implode('<br>', $errors);
            // Consider also storing old input in session to repopulate form
            $this->redirect('/admin/users/create');
            return;
        }

        // --- Database Insertion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/users'); return; }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Use transaction for multi-step operation (users + user_roles)
            $pdo->beginTransaction();

            // 1. Insert into users table
            $sqlUser = "INSERT INTO users (username, email, password_hash, full_name, contact_number, status, created_at, updated_at)
                        VALUES (:username, :email, :password_hash, :full_name, :contact, :status, NOW(), NOW())";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':full_name' => $fullName ?: null, // Use null if empty
                ':contact' => $contact ?: null,
                ':status' => $status
            ]);

            $userId = $pdo->lastInsertId(); // Get the ID of the newly created user

            // 2. Insert into user_roles table
            if (!empty($assignedRoles) && $userId) {
                $sqlRoles = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
                $stmtRoles = $pdo->prepare($sqlRoles);
                foreach ($assignedRoles as $roleId) {
                    // Optional: Verify roleId exists in roles table first
                    $stmtRoles->execute([':user_id' => $userId, ':role_id' => (int)$roleId]);
                }
            }

            $pdo->commit(); // Commit transaction if all steps succeeded
            $_SESSION['flash_success'] = "User created successfully!";

        } catch (\PDOException $e) {
            $pdo->rollBack(); // Roll back changes on error
            if ($e->getCode() == 23000) { // Duplicate entry
                $this->handleDbErrorAndRedirect("Username or Email already exists.", '/admin/users/create');
            } else {
                error_log("User Store Error: " . $e->getMessage());
                $this->handleDbErrorAndRedirect("Database error creating user.", '/admin/users');
            }
            return; // Stop execution after handling error
        }
        // --- End Database Insertion ---

        $this->redirect('/admin/users');
    }

    /**
     * Show the form for editing the specified user. (Handles GET /admin/users/edit/{id})
     */
    public function edit(array $vars): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin')) { $this->redirect('/dashboard'); return; }
        // --- End Access Control ---
        $csrfToken = $this->generateCsrfToken(); // Generate/get token

        $userId = $vars['id'] ?? null;
        if (!$userId) { $this->handleDbErrorAndRedirect("Invalid User ID.", '/admin/users'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/users'); return; }
        $user = null;
        $currentUserRoles = [];
        $allRoles = [];
        $viewError = null;

        try {
            // Fetch user data
            $stmtUser = $pdo->prepare("SELECT user_id, username, email, full_name, contact_number, status FROM users WHERE user_id = :id");
            $stmtUser->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmtUser->execute();
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$user) { $this->handleDbErrorAndRedirect("User not found.", '/admin/users'); return; }

            // Fetch user's current roles
            $stmtUserRoles = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = :id");
            $stmtUserRoles->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmtUserRoles->execute();
            $currentUserRoles = $stmtUserRoles->fetchAll(PDO::FETCH_COLUMN);

            // Fetch all available roles for the dropdown/checkboxes
            $allRoles = $this->getRoles();

        } catch (\PDOException $e) {
            error_log("User Edit Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching user edit data.";
        }

        $this->loadView('UserManagement/Views/edit', [
            'pageTitle' => 'Edit User: ' . htmlspecialchars($user['username']),
            'user' => $user,
            'currentUserRoles' => $currentUserRoles, // Array of role IDs user currently has
            'allRoles' => $allRoles,
            '_csrf_token' => $csrfToken, // Pass token to view
            'viewError' => $viewError,
            'errors' => $_SESSION['form_errors'] ?? [], // For validation feedback
            'oldInput' => $_SESSION['old_input'] ?? []  // For repopulation
        ], 'layout_admin');
        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Update the specified user in storage. (Handles POST /admin/users/update/{id})
     */
    public function update(array $vars): void {
        // --- ADD CSRF Check FIRST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken($_POST['_csrf_token'] ?? null)) {
                // CSRF token is invalid or missing
                $_SESSION['flash_error'] = 'Invalid security token. Please try submitting the form again.';
                // Redirect back to the form or a safe page
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/dashboard'); // Redirect back if possible
                return;
            }
        } else {
            $this->redirect('/admin/fees/discount-types'); // Redirect if not POST
            return;
        }
        // --- END CSRF Check ---
         // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/users'); return; }
        // --- End Checks ---

        $userId = $vars['id'] ?? null;
        if (!$userId) { $this->handleDbErrorAndRedirect("Invalid User ID for update.", '/admin/users'); return; }

        // --- Data Validation ---
        $username = trim($_POST['username'] ?? ''); // Should username be editable? Maybe not.
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Optional: only update if provided
        $fullName = trim($_POST['full_name'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');
        $status = $_POST['status'] ?? 'inactive';
        $assignedRoles = $_POST['roles'] ?? []; // Array of role_ids

        $errors = [];
        if (empty($username)) $errors[] = "Username is required."; // If editable
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email is required.";
         // Add more validation
        if (!in_array($status, ['active', 'inactive', 'pending_verification', 'locked'])) $status = 'inactive';
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            // Redirect back to edit form
            $this->redirect('/admin/users/edit/' . $userId);
            return;
        }

        // --- Database Update ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/users'); return; }

        try {
            $pdo->beginTransaction();

            // 1. Update users table
            $sqlUser = "UPDATE users SET email = :email, full_name = :full_name, contact_number = :contact, status = :status";
            $paramsUser = [
                ':email' => $email,
                ':full_name' => $fullName ?: null,
                ':contact' => $contact ?: null,
                ':status' => $status,
                ':user_id' => $userId
            ];

            // Only include password update if a new password was provided
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sqlUser .= ", password_hash = :password_hash";
                $paramsUser[':password_hash'] = $passwordHash;
            }

            $sqlUser .= " WHERE user_id = :user_id";
            $stmtUser = $pdo->prepare($sqlUser);
            $stmtUser->execute($paramsUser);

            // 2. Update user_roles table (delete old, insert new)
            // Delete existing roles for this user
            $stmtDeleteRoles = $pdo->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
            $stmtDeleteRoles->execute([':user_id' => $userId]);

            // Insert new roles
            if (!empty($assignedRoles)) {
                $sqlRoles = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
                $stmtRoles = $pdo->prepare($sqlRoles);
                foreach ($assignedRoles as $roleId) {
                    // Optional: Verify roleId exists
                    $stmtRoles->execute([':user_id' => $userId, ':role_id' => (int)$roleId]);
                }
            }

            $pdo->commit();
            $_SESSION['flash_success'] = "User updated successfully!";

        } catch (\PDOException $e) {
             $pdo->rollBack();
             if ($e->getCode() == 23000) { // Duplicate entry (e.g., email)
                $this->handleDbErrorAndRedirect("Update failed: Email already exists for another user.", '/admin/users/edit/' . $userId);
             } else {
                error_log("User Update Error: " . $e->getMessage());
                $this->handleDbErrorAndRedirect("Database error updating user.", '/admin/users');
             }
             return;
        }
        // --- End Database Update ---

        $this->redirect('/admin/users');
    }


    /** Helper to fetch all roles */
    private function getRoles(): array {
        $pdo = DbConnection::getInstance();
        if (!$pdo) return [];
        try {
            $stmt = $pdo->query("SELECT role_id, role_name FROM roles ORDER BY role_name ASC");
            // Fetch as key-value pairs (id => name) for easy use in forms
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            error_log("Get Roles Error: " . $e->getMessage());
            return [];
        }
    }

     /** Helper for DB error redirects */
     private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
     }

     public function delete(array $vars): void {
        // --- ADD CSRF Check FIRST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken($_POST['_csrf_token'] ?? null)) {
                // CSRF token is invalid or missing
                $_SESSION['flash_error'] = 'Invalid security token. Please try submitting the form again.';
                // Redirect back to the form or a safe page
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/dashboard'); // Redirect back if possible
                return;
            }
        } else {
            $this->redirect('/admin/fees/discount-types'); // Redirect if not POST
            return;
        }
    // --- END CSRF Check ---
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/users'); return; } // Ensure POST method
        // --- End Checks ---

        $userId = $vars['id'] ?? null;
        if (!$userId) { $this->handleDbErrorAndRedirect("Invalid User ID for deletion.", '/admin/users'); return; }

        // --- Prevent Self-Deletion ---
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $this->handleDbErrorAndRedirect("You cannot delete your own account.", '/admin/users');
            return;
        }
        // --- End Self-Deletion Check ---

        // --- Database Deletion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/users'); return; }

        try {
            // Important: Use a transaction because we need to delete from multiple tables (users, user_roles)
            // And consider related data (e.g., audit logs might reference user_id - SET NULL ON DELETE helps here)
            $pdo->beginTransaction();

            // 1. Delete from user_roles first
            $stmtRoles = $pdo->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
            $stmtRoles->execute([':user_id' => $userId]);

            // 2. Delete from users table
            // Note: Consider soft delete (setting status='inactive' or adding an is_deleted flag)
            // instead of hard delete, especially if other tables have foreign keys pointing here.
            // Hard delete example:
            $stmtUser = $pdo->prepare("DELETE FROM users WHERE user_id = :user_id");
            $stmtUser->execute([':user_id' => $userId]);

            // Check if any rows were affected (user actually existed)
            if ($stmtUser->rowCount() > 0) {
                $pdo->commit();
                $_SESSION['flash_success'] = "User deleted successfully!";
            } else {
                // User might have been deleted already between page load and click
                $pdo->rollBack();
                $_SESSION['flash_error'] = "User not found or already deleted.";
            }

        } catch (\PDOException $e) {
             $pdo->rollBack();
             error_log("User Delete Error: " . $e->getMessage());
             // Check for foreign key constraint errors if not using ON DELETE SET NULL/CASCADE appropriately
             $this->handleDbErrorAndRedirect("Database error deleting user. Check related records.", '/admin/users');
             return;
        }
        // --- End Database Deletion ---

        $this->redirect('/admin/users');
    }
}

