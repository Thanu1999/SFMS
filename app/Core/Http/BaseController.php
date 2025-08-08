<?php

namespace App\Core\Http;

class BaseController {

    /**
     * Check if the currently logged-in user has a specific role.
     *
     * @param string $requiredRole The name of the role to check for.
     * @return bool True if the user has the role, false otherwise.
     */
    protected function hasRole(string $requiredRole): bool {
        // Check if user is logged in and roles are set in session
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['roles']) || !is_array($_SESSION['roles'])) {
            return false;
        }
        // Check if the required role exists in the user's roles array
        return in_array($requiredRole, $_SESSION['roles']);
    }

    /**
     * Redirect helper (moved here from AuthController for potential reuse)
     */
    // --- ADD THIS METHOD BACK ---
    protected function redirect(string $url): void {
        // Adjust base path if needed
        $basePath = '/sfms_project/public';
        header('Location: ' . $basePath . $url);
        exit; // Important to stop script execution after redirect
    }
    // --- END OF METHOD TO ADD ---

    /**
     * Loads a view file, optionally within a layout, and passes data to it.
     *
     * @param string $viewName The view name relative to the Module (e.g., 'UserManagement/Views/index').
     * @param array $data Optional array of data to extract for the view & layout.
     * @param string|null $layoutName The name of the layout file in app/Views/layouts/ (e.g., 'layout_admin'). If null, view is echoed directly.
     * @return void
     */
    protected function loadView(string $viewName, array $data = [], ?string $layoutName = null): void
    {
        // Construct the full path to the specific view file within the module
        $viewPath = __DIR__ . '/../../Modules/' . $viewName . '.php';

        if (!file_exists($viewPath)) {
            // Handle view not found error
            http_response_code(500);
            error_log("View file not found at: " . $viewPath);
            echo "Error: View file '" . htmlspecialchars($viewName) . "' not found. Check path in controller.";
            // Consider throwing an exception or loading a dedicated error view
            return; // Stop execution
        }

        // Extract data variables for use in the view and potentially the layout
        extract($data);

        // Start output buffering to capture the view content
        ob_start();
        require $viewPath; // Include the specific view file (e.g., admin_dashboard.php)
        $content = ob_get_clean(); // Get the rendered content of the view into $content variable

        // If a layout name is provided, include the layout file
        if ($layoutName !== null) {
            // Construct path to layout file
            $layoutPath = __DIR__ . '/../../Views/layouts/' . $layoutName . '.php';

            if (file_exists($layoutPath)) {
                // The layout file will require header/footer and echo $content
                // Make $this available to layout/partials if needed (e.g., for csrfInput in forms within content)
                require $layoutPath;
            } else {
                // Handle layout not found error
                http_response_code(500);
                error_log("Layout file not found at: " . $layoutPath);
                echo "Error: Layout file '" . htmlspecialchars($layoutName) . "' not found.";
                // Outputting content directly as fallback? Or show error?
                // echo $content; // Fallback: echo content without layout
            }
        } else {
            // If no layout specified, just echo the content directly (old behavior)
            echo $content;
        }
    }
    /**
     * Generates a CSRF token, stores it in the session, and returns it.
     * @return string The generated CSRF token.
     */
    protected function generateCsrfToken(): string {
        if (empty($_SESSION['_csrf_token'])) { // Generate only if not already set for this request/page load
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Validates a submitted CSRF token against the one in the session.
     * Optionally consumes the token after successful validation.
     *
     * @param string|null $submittedToken The token received from the form POST data.
     * @param bool $consumeToken If true, unsets the session token after validation.
     * @return bool True if valid, false otherwise.
     */
    protected function validateCsrfToken(?string $submittedToken, bool $consumeToken = true): bool {
        if (empty($submittedToken) || empty($_SESSION['_csrf_token'])) {
            error_log("CSRF Validation Failed: Missing submitted or session token.");
            return false;
        }

        $sessionToken = $_SESSION['_csrf_token'];

        // Use hash_equals for timing-attack safe comparison
        $isValid = hash_equals($sessionToken, $submittedToken);

        if ($isValid && $consumeToken) {
            // Invalidate the token after successful use
            unset($_SESSION['_csrf_token']);
        } elseif (!$isValid) {
             error_log("CSRF Validation Failed: Token mismatch.");
             // Optionally log more details like IP, user agent if needed for investigation
        }

        return $isValid;
    }

    /**
     * Gets the current CSRF token from the session (for forms).
     * Generates one if it doesn't exist yet for this request.
     * @return string
     */
    protected function getCsrfToken(): string {
         // Ensure a token exists for the form generation phase
        return $this->generateCsrfToken();
    }

     /**
     * Renders the CSRF hidden input field.
     * @return string HTML input field.
     */
    protected function csrfInput(): string {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($this->getCsrfToken()) . '">';
    }


    // You might add other common methods like loadView here later
}