<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use App\Core\Services\AuditLogService; // <-- Import AuditLogService
use PDO;

class AuthController extends BaseController {

    private AuditLogService $auditLogService; // <-- Optional: Inject via constructor later

    public function __construct() {
        // Optional: Instantiate here if used in multiple methods
        // Or instantiate directly in methods where needed if preferred
        $this->auditLogService = new AuditLogService();
    }

    /**
     * Display the login form. Handles GET /login
     */
    public function showLoginForm(): void {
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
    
        // Generate/get the token
        $csrfToken = $this->getCsrfToken(); // Use helper to ensure it's generated and get value
    
        // Load the view, explicitly passing the token variable
        // !!! IMPORTANT: This assumes you are using the loadView method now !!!
        // If you are still using `require`, the view won't automatically get $_csrf_token.
        $this->loadView('Auth/Views/login', [
            'error' => $error,
            '_csrf_token' => $csrfToken // Pass the token with this specific key name
        ]);
    
        // If using require instead of loadView, you'd need to do this:
        // $_csrf_token = $csrfToken; // Make it a local variable
        // require __DIR__ . '/../Views/login.php';
    }

    /**
     * Process the login form submission. Handles POST /login
     */
    public function login(): void {
        // --- Check Method & ADD CSRF Check FIRST ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken($_POST['_csrf_token'] ?? null)) {
                // CSRF token is invalid or missing
                // Log this attempt maybe?
                error_log("CSRF validation failed for login attempt.");
                $_SESSION['flash_error'] = 'Invalid security token. Please try submitting the form again.';
                $this->redirect('/login'); // Redirect back to login form
                return; // Stop execution
            }
        } else {
             // Should not happen for login action
             $this->redirect('/login'); return;
        }
        // --- END CSRF Check ---
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // --- Get IP and User Agent for logging ---
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        // --- End Get IP/UA ---

        if (empty($username) || empty($password)) {
            // Log failed attempt due to missing input
            $logDetails = ['reason' => 'Missing username or password', 'username_attempted' => $username];
            $this->auditLogService->log(null, 'USER_LOGIN_FAILURE', null, null, $logDetails, $ipAddress, $userAgent);

            $_SESSION['login_error'] = 'Username and password are required.';
            $this->redirect('/login');
            return;
        }

        $pdo = DbConnection::getInstance();
        if (!$pdo) {
            // Log critical DB error during login attempt
            $this->auditLogService->log(null, 'USER_LOGIN_DB_ERROR', null, null, 'DB connection failed', $ipAddress, $userAgent);

            $_SESSION['login_error'] = 'Database connection error. Please try again later.';
            $this->redirect('/login');
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, status FROM users WHERE username = :username LIMIT 1");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify user, password, and status
            if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
                // --- Login Successful ---
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Fetch and store roles
                $roles = [];
                $roleStmt = $pdo->prepare("SELECT r.role_name FROM roles r JOIN user_roles ur ON r.role_id = ur.role_id WHERE ur.user_id = :user_id");
                $roleStmt->bindParam(':user_id', $user['user_id'], PDO::PARAM_INT);
                $roleStmt->execute();
                $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);
                $_SESSION['roles'] = $roles;

                // --- Log Success ---
                $this->auditLogService->log($user['user_id'], 'USER_LOGIN_SUCCESS', null, null, null, $ipAddress, $userAgent);
                // --- End Log ---

                $redirectUrl = '/login'; // Default fallback
                 if (!empty($roles)) {
                     if (in_array('Admin', $roles) || in_array('Staff', $roles)) {
                         $redirectUrl = '/admin/dashboard'; // Admin/Staff go to Admin Dashboard
                     } elseif (in_array('Student', $roles) || in_array('Parent', $roles)) {
                          $redirectUrl = '/portal/dashboard'; // Student/Parent go to Portal Dashboard
                     } else {
                          // User has roles, but none are recognized for dashboard access
                           $_SESSION['flash_error'] = "Your user role does not have access to a dashboard.";
                           $this->auditLogService->log($user['user_id'], 'USER_LOGIN_NO_ACCESS', null, null, ['roles' => $roles], $ipAddress, $userAgent);
                           $redirectUrl = '/logout'; // Log them out immediately
                     }
                 } else {
                      // User logged in but has NO roles assigned!
                      $_SESSION['flash_error'] = "Login successful, but no roles assigned. Please contact administrator.";
                      $this->auditLogService->log($user['user_id'], 'USER_LOGIN_NO_ROLES', null, null, null, $ipAddress, $userAgent);
                      $redirectUrl = '/logout'; // Log them out
                 }
                 $this->redirect($redirectUrl); // Perform the determined redirect
                 return; // Stop script after redirect
                 // --- *** END CORRECTED REDIRECT LOGIC *** ---

            } else {
                // --- Login Failed ---
                $reason = 'Invalid credentials';
                if ($user && $user['status'] !== 'active') {
                    $reason = 'Account inactive/locked';
                }
                $logDetails = ['username_attempted' => $username, 'reason' => $reason];
                // Log failure attempt (careful not to log password) - Use user ID if user was found but status/pw failed
                $this->auditLogService->log($user['user_id'] ?? null, 'USER_LOGIN_FAILURE', null, null, $logDetails, $ipAddress, $userAgent);
                // --- End Log ---

                $_SESSION['login_error'] = 'Invalid username or password, or account inactive.';
                $this->redirect('/login');
                return;
            }

        } catch (\PDOException $e) {
            // --- Log DB Error during query/fetch ---
             $this->auditLogService->log(null, 'USER_LOGIN_DB_ERROR', null, null, $e->getMessage(), $ipAddress, $userAgent);
            // --- End Log ---

            error_log("Login Database Error: " . $e->getMessage());
            $_SESSION['login_error'] = 'An error occurred during login. Please try again.';
            $this->redirect('/login');
            return;
        }
    }

    /**
     * Process user logout. Handles GET /logout
     */
    public function logout(): void {
        // --- Get user ID *before* destroying session ---
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        // --- End Get Info ---

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destroy the session data on the server
        session_destroy();

        // --- Log Logout Action ---
        // Use the user ID we captured before destroying the session
        if ($userId) { // Only log if we knew who was logged in
             $this->auditLogService->log($userId, 'USER_LOGOUT', null, null, null, $ipAddress, $userAgent);
        }
        // --- End Log ---

        // Redirect to login page
        $this->redirect('/login');
        // Note: No return needed after redirect helper as it calls exit()
    }

    // No need for the private redirect() method here, it's inherited from BaseController

} // End of AuthController class