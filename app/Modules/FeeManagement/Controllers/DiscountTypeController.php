<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;
use PDOException;

class DiscountTypeController extends BaseController {

    /**
     * Display a list of discount types.
     * Handles GET /admin/fees/discount-types
     */
    public function index(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $this->handleError("Access Denied: Cannot manage discount types.", '/admin/dashboard'); return;
        }
        // --- End Access Control ---

        $discountTypes = [];
        $viewError = null; // Use specific variable name
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                $stmt = $pdo->query("SELECT * FROM discount_types ORDER BY name ASC");
                $discountTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Discount Type List Error: " . $e->getMessage());
            }
        }

        $this->loadView('FeeManagement/Views/discounts/index', [
            'pageTitle' => 'Manage Discount Types',
            'discountTypes' => $discountTypes,
            'viewError' => $viewError
            // Global Flash messages handled by layout header
        ], 'layout_admin');
    }

    /**
     * Show the form for creating a new discount type.
     * Handles GET /admin/fees/discount-types/create
     */
    public function create(): void {
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { /* ... access denied ... */ }
        $csrfToken = $this->generateCsrfToken(); // Generate/get token

        $this->loadView('FeeManagement/Views/discounts/create', [
            'pageTitle' => 'Create Discount Type',
            '_csrf_token' => $csrfToken,
            'errors' => $_SESSION['form_errors'] ?? [],
            'oldInput' => $_SESSION['old_input'] ?? []
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Store a newly created discount type.
     * Handles POST /admin/fees/discount-types
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
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { /* ... */ }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/discount-types'); return; }

        // --- Validation & Retrieval ---
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'fixed_amount'; // Default type
        $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
        $isActive = isset($_POST['is_active']) ? 1 : 0; // Checkbox value

        $errors = [];
        if (empty($name)) $errors[] = "Discount Name is required.";
        if (!in_array($type, ['percentage', 'fixed_amount'])) $errors[] = "Invalid Discount Type selected.";
        if ($value === false || $value < 0) $errors[] = "Valid Value (Amount or Percentage) is required and cannot be negative.";
        if ($type === 'percentage' && $value > 100) $errors[] = "Percentage value cannot exceed 100.";
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            // TODO: Pass back old input data via session
            $this->redirect('/admin/fees/discount-types/create'); return;
        }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleError("Database connection failed.", '/admin/fees/discount-types'); return; }

        try {
             $sql = "INSERT INTO discount_types (name, description, type, value, is_active, created_at, updated_at)
                     VALUES (:name, :desc, :type, :value, :active, NOW(), NOW())";
             $stmt = $pdo->prepare($sql);
             $params = [
                 ':name' => $name,
                 ':desc' => $description ?: null,
                 ':type' => $type,
                 ':value' => $value,
                 ':active' => $isActive
             ];
             if ($stmt->execute($params)) {
                 $_SESSION['flash_success'] = "Discount Type created successfully.";
             } else {
                 $_SESSION['flash_error'] = "Failed to create Discount Type.";
             }
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) { // Duplicate name
                 $this->handleError("Failed: Discount Type name '{$name}' already exists.", '/admin/fees/discount-types/create');
             } else {
                 error_log("Discount Type Store Error: " . $e->getMessage());
                 $this->handleError("Database error creating discount type.", '/admin/fees/discount-types');
             }
             return;
        }
        $this->redirect('/admin/fees/discount-types');
    }

    /**
     * Show the form for editing the specified discount type.
     * Handles GET /admin/fees/discount-types/edit/{id}
     */
    public function edit(array $vars): void {
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { /* ... */ }
         $csrfToken = $this->generateCsrfToken(); // Generate/get token
         $id = $vars['id'] ?? null;
         if (!$id) { $this->handleError("Invalid Discount Type ID.", '/admin/fees/discount-types'); return; }

         $pdo = DbConnection::getInstance();
         if (!$pdo) { /* ... */ }
         $discountType = null;
         $viewError = null;

        try {
            $stmt = $pdo->prepare("SELECT * FROM discount_types WHERE discount_type_id = :id");
            $stmt->execute([':id' => $id]);
            $discountType = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$discountType) {
                $this->handleError("Discount Type not found.", '/admin/fees/discount-types');
                return;
            }
        } catch (PDOException $e) {
            error_log("Discount Type Edit Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching discount type details.";
        }

         $this->loadView('FeeManagement/Views/discounts/edit', [
             'pageTitle' => 'Edit Discount Type: ' . htmlspecialchars($discountType['name'] ?? ''),
             'discountType' => $discountType,
             '_csrf_token' => $csrfToken,
             'viewError' => $viewError,
             'errors' => $_SESSION['form_errors'] ?? [],
             'oldInput' => $_SESSION['old_input'] ?? []
         ], 'layout_admin'); // <-- SPECIFY LAYOUT

         unset($_SESSION['form_errors']);
         unset($_SESSION['old_input']);
    }

    /**
     * Update the specified discount type.
     * Handles POST /admin/fees/discount-types/update/{id}
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
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { /* ... */ }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... */ }
         $id = $vars['id'] ?? null;
         if (!$id) { $this->handleError("Invalid Discount Type ID.", '/admin/fees/discount-types'); return; }

          // --- Validation & Retrieval ---
         $name = trim($_POST['name'] ?? '');
         $description = trim($_POST['description'] ?? '');
         $type = $_POST['type'] ?? 'fixed_amount';
         $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
         $isActive = isset($_POST['is_active']) ? 1 : 0;

         $errors = [];
         if (empty($name)) $errors[] = "Discount Name is required.";
         if (!in_array($type, ['percentage', 'fixed_amount'])) $errors[] = "Invalid Discount Type selected.";
         if ($value === false || $value < 0) $errors[] = "Valid Value is required and cannot be negative.";
         if ($type === 'percentage' && $value > 100) $errors[] = "Percentage value cannot exceed 100.";
         // --- End Validation ---

         if (!empty($errors)) {
             $_SESSION['flash_error'] = implode('<br>', $errors);
             $this->redirect('/admin/fees/discount-types/edit/' . $id); return;
         }

         $pdo = DbConnection::getInstance();
         if (!$pdo) { /* ... */ }

         try {
              $sql = "UPDATE discount_types SET name = :name, description = :desc, type = :type, value = :value, is_active = :active, updated_at = NOW() WHERE discount_type_id = :id";
              $stmt = $pdo->prepare($sql);
              $params = [
                  ':name' => $name,
                  ':desc' => $description ?: null,
                  ':type' => $type,
                  ':value' => $value,
                  ':active' => $isActive,
                  ':id' => $id
              ];
              if ($stmt->execute($params)) {
                  $_SESSION['flash_success'] = "Discount Type updated successfully.";
              } else {
                   $_SESSION['flash_error'] = "Failed to update Discount Type.";
              }
         } catch (PDOException $e) {
              if ($e->getCode() == 23000) {
                 $this->handleError("Update failed: Discount Type name '{$name}' already exists for another record.", '/admin/fees/discount-types/edit/' . $id);
              } else {
                 error_log("Discount Type Update Error: " . $e->getMessage());
                 $this->handleError("Database error updating discount type.", '/admin/fees/discount-types');
              }
              return;
         }
         $this->redirect('/admin/fees/discount-types');
    }


    /**
     * Toggle the active status of a discount type.
     * Handles POST /admin/fees/discount-types/toggle/{id}
     */
    public function toggleStatus(array $vars): void {
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { /* ... */ }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... */ }
         $id = $vars['id'] ?? null;
         if (!$id) { $this->handleError("Invalid Discount Type ID.", '/admin/fees/discount-types'); return; }

         $pdo = DbConnection::getInstance();
         if (!$pdo) { /* ... */ }

         try {
              // Toggle the is_active status
              $sql = "UPDATE discount_types SET is_active = NOT is_active, updated_at = NOW() WHERE discount_type_id = :id";
              $stmt = $pdo->prepare($sql);
              if ($stmt->execute([':id' => $id])) {
                   $_SESSION['flash_success'] = "Discount Type status toggled successfully.";
              } else {
                   $_SESSION['flash_error'] = "Failed to toggle status.";
              }
         } catch (PDOException $e) {
              error_log("Discount Type Toggle Error: " . $e->getMessage());
              $this->handleError("Database error toggling status.", '/admin/fees/discount-types');
              return;
         }
         $this->redirect('/admin/fees/discount-types');
    }


    /** Helper for DB error redirects (should be in BaseController?) */
     private function handleError(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
          // Make sure redirect calls exit()
     }
}