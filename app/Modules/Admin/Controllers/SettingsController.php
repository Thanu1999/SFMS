<?php
namespace App\Modules\Admin\Controllers;

use App\Core\Http\BaseController;
use App\Core\Helpers\SettingsHelper; // Use the helper
use App\Core\Services\AuditLogService;
use PDO; // Required if using PDOException
use PDOException; // Required for catch block

class SettingsController extends BaseController {

    private AuditLogService $auditLogService;

    public function __construct() {
         $this->auditLogService = new AuditLogService();
         // Ensure BaseController constructor is called if it exists
         // parent::__construct();
    }

    /**
     * Show settings form and handle updates.
     * Handles GET and POST for /admin/settings
     */
    public function index(): void {
        // --- Access Control - STRICTLY ADMIN ---
        if (!$this->hasRole('Admin')) {
             $_SESSION['flash_error'] = "Access Denied: You cannot manage system settings.";
             $this->redirect('/admin/dashboard'); return;
        }
        // --- End Access Control ---

        // Define keys managed via UI
        $settingsKeys = [
            'school_name', 'school_address', 'school_contact', 'currency_symbol',
            'bank_account_name', 'bank_account_number', 'bank_name', 'bank_branch', 'bank_reference_info',
            'mail_from_name',
            'reminder_days_before_due', 'reminder_days_after_due', 'reminder_cooldown_days'
        ];
        $viewError = null;

        // --- Handle POST Request (Update Settings) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken($_POST['_csrf_token'] ?? null)) {
                 $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
                 $this->redirect('/admin/settings'); return;
            }

            $updatedCount = 0;
            $errorCount = 0;
            $changes = [];
            $currentSettings = SettingsHelper::getMultiple($settingsKeys); // Get current values for audit

            foreach ($settingsKeys as $key) {
                if (isset($_POST[$key])) {
                    $newValue = trim($_POST[$key]);
                    $oldValue = $currentSettings[$key] ?? $this->getDefaultSettingValue($key); // Use default for comparison if not set

                    // Add basic validation based on key if needed
                    if ($key === 'currency_symbol' && strlen($newValue) > 5) {
                         $_SESSION['flash_error'] = ($_SESSION['flash_error'] ?? '') . "Currency Symbol '{$key}' is too long.<br>";
                         $errorCount++; continue; // Skip this key
                    }
                     if (str_contains($key, 'days') && (!is_numeric($newValue) || (int)$newValue < 0)) {
                         $_SESSION['flash_error'] = ($_SESSION['flash_error'] ?? '') . "Value for '{$key}' must be a non-negative number.<br>";
                         $errorCount++; continue; // Skip this key
                     }

                    // Update only if changed
                    if ($oldValue !== $newValue) {
                       if (SettingsHelper::set($key, $newValue, $_SESSION['user_id'])) {
                           $updatedCount++;
                           $changes[$key] = ['old' => $oldValue, 'new' => $newValue];
                       } else {
                           $errorCount++;
                           $_SESSION['flash_error'] = ($_SESSION['flash_error'] ?? '') . "Failed to update setting: {$key}<br>";
                       }
                    }
                }
            }

            if ($errorCount === 0) { // Only set success/info if no validation/DB errors
                if ($updatedCount > 0) {
                     $_SESSION['flash_success'] = "{$updatedCount} settings updated successfully.";
                     // Log changes
                     $this->auditLogService->log($_SESSION['user_id'], 'SETTINGS_UPDATED', 'system_settings', null, $changes, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null);
                } else {
                    $_SESSION['flash_info'] = "No settings were changed."; // Use info flash
                }
            }

             SettingsHelper::clearCache(); // Clear cache after updates
             $this->redirect('/admin/settings'); // Redirect to refresh page
             return;
        }
        // --- End Handle POST ---


        // --- Load View (GET Request) ---
         $settingsForView = [];
         // Fetch current settings using the helper, providing defaults
         $currentSettings = SettingsHelper::getMultiple($settingsKeys);
         foreach ($settingsKeys as $key) {
             $settingsForView[$key] = $currentSettings[$key] ?? $this->getDefaultSettingValue($key);
         }

        // Use layout_admin
        $this->loadView('Admin/Views/settings_form', [ // Assuming view is in Admin module
            'pageTitle' => 'System Settings',
            'settings' => $settingsForView,
            'viewError' => $viewError,
             '_csrf_token' => $this->getCsrfToken() // Pass CSRF for the form
        ], 'layout_admin');
    }

    /** Helper for default settings */
    private function getDefaultSettingValue(string $key): string {
         $defaults = [
             'currency_symbol' => 'Rs.',
             'reminder_days_before_due' => '7',
             'reminder_days_after_due' => '3',
             'reminder_cooldown_days' => '3',
             'school_name' => 'My School',
             'mail_from_name' => 'SFMS Notifications',
             'bank_account_name' => '',
             'bank_account_number' => '',
             'bank_name' => '',
             'bank_branch' => '',
             'bank_reference_info' => 'Include Student Admission No / Invoice No',
             'school_address' => '',
             'school_contact' => '',
         ];
         return $defaults[$key] ?? '';
    }

     /** Helper for DB error redirects (ensure in BaseController) */
    private function handleError(string $message, string $redirectTo): void {
         $_SESSION['flash_error'] = $message;
         $this->redirect($redirectTo);
         exit;
     }
}