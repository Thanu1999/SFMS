<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;

class FeeCategoryController extends BaseController {

    /**
     * Display a list of fee categories. (Handles GET /admin/fees/categories)
     */
    public function index(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            // Redirect or show access denied if not Admin or Staff
            // For simplicity, redirecting to dashboard if unauthorized here
            $_SESSION['flash_error'] = "Access Denied: You cannot manage fee categories.";
            $this->redirect('/dashboard');
            return;
        }
        // --- End Access Control ---

        $categories = [];
        $viewError = null; // Specific DB error for this view
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                $stmt = $pdo->query("SELECT * FROM fee_categories ORDER BY category_name ASC");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Fee Category List Error: " . $e->getMessage());
            }
        }

        // Load the view, passing categories and potential errors
        $this->loadView('FeeManagement/Views/categories/index', [
            'categories' => $categories,
            'pageTitle' => 'Fee Categories',
            'viewError' => $viewError
            // Global Flash messages handled by layout header
        ], 'layout_admin');
    }

    /**
     * Show the form for creating a new fee category. (Handles GET /admin/fees/categories/create)
     */
    public function create(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: You cannot create fee categories.";
             $this->redirect('/admin/fees/categories'); // Redirect back to list
             return;
        }
        // --- End Access Control ---

        $this->loadView('FeeManagement/Views/categories/create', [
            'pageTitle' => 'Create Fee Category',
            'errors' => $_SESSION['form_errors'] ?? [],
            'oldInput' => $_SESSION['old_input'] ?? []
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Store a newly created fee category in storage. (Handles POST /admin/fees/categories)
     */
    public function store(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied."; // Keep it simple here
             $this->redirect('/dashboard');
             return;
        }
        // --- End Access Control ---

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/fees/categories/create');
            return;
        }

        // --- Data Validation ---
        $categoryName = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($categoryName)) {
             // Ideally, pass errors back to the create form with old input
             $_SESSION['flash_error'] = "Category Name cannot be empty.";
             $this->redirect('/admin/fees/categories/create');
             return;
        }
        // Add more validation if needed (length, uniqueness check)
        // --- End Validation ---


        // --- Database Insertion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) {
             $_SESSION['flash_error'] = "Database connection error.";
             $this->redirect('/admin/fees/categories'); // Redirect to list view on DB error
             return;
        }

        try {
            $sql = "INSERT INTO fee_categories (category_name, description, created_at, updated_at) VALUES (:name, :desc, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $categoryName, PDO::PARAM_STR);
            $stmt->bindParam(':desc', $description, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['flash_success'] = "Fee category created successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to create fee category.";
            }
        } catch (\PDOException $e) {
            // Check for duplicate entry error (unique constraint on category_name)
            if ($e->getCode() == 23000) { // Integrity constraint violation
                 $_SESSION['flash_error'] = "Failed to create fee category: Name already exists.";
            } else {
                 $_SESSION['flash_error'] = "Database error: " . $e->getMessage();
                 error_log("Fee Category Store Error: " . $e->getMessage());
            }
        }
        // --- End Database Insertion ---

        // Redirect back to the category list page
        $this->redirect('/admin/fees/categories');
    }

    /**
     * Show the form for editing the specified fee category. (Handles GET /admin/fees/categories/edit/{id})
     */
    public function edit(array $vars): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot edit fee categories.";
            $this->redirect('/admin/fees/categories');
            return;
        }
        // --- End Access Control ---

        $categoryId = $vars['id'] ?? null;
        if (!$categoryId) {
            $this->handleDbErrorAndRedirect("Invalid Fee Category ID.", '/admin/fees/categories');
            return;
        }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/categories'); return; }
        $category = null; // Changed variable name for clarity
        $viewError = null;

        try {
            // Fetch category data
            $stmt = $pdo->prepare("SELECT category_id, category_name, description FROM fee_categories WHERE category_id = :id");
            $stmt->bindParam(':id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
            $category = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$category) {
                $this->handleDbErrorAndRedirect("Fee Category not found.", '/admin/fees/categories');
                return;
            }

        } catch (PDOException $e) {
            error_log("Fee Category Edit Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching category details.";
        }

        $this->loadView('FeeManagement/Views/categories/edit', [
            'pageTitle' => 'Edit Fee Category: ' . htmlspecialchars($category['category_name']),
            'category' => $category,
             'viewError' => $viewError,
             'errors' => $_SESSION['form_errors'] ?? [],
             'oldInput' => $_SESSION['old_input'] ?? []
         ], 'layout_admin'); // <-- SPECIFY LAYOUT

         unset($_SESSION['form_errors']);
         unset($_SESSION['old_input']);
    }

    /**
     * Update the specified fee category in storage. (Handles POST /admin/fees/categories/update/{id})
     */
    public function update(array $vars): void {
         // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/categories'); return; }
        // --- End Checks ---

        $categoryId = $vars['id'] ?? null;
        if (!$categoryId) { $this->handleDbErrorAndRedirect("Invalid Fee Category ID for update.", '/admin/fees/categories'); return; }

        // --- Data Validation ---
        $categoryName = trim($_POST['category_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($categoryName)) {
             $_SESSION['flash_error'] = "Category Name cannot be empty.";
             // Redirect back to edit form
             $this->redirect('/admin/fees/categories/edit/' . $categoryId);
             return;
        }
        // Add more validation if needed (length, etc.)
        // --- End Validation ---

        // --- Database Update ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/categories'); return; }

        try {
            $sql = "UPDATE fee_categories SET category_name = :name, description = :desc, updated_at = NOW() WHERE category_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $categoryName,
                ':desc' => $description,
                ':id' => $categoryId
            ]);

             $_SESSION['flash_success'] = "Fee category updated successfully!";

        } catch (\PDOException $e) {
             if ($e->getCode() == 23000) { // Duplicate entry
                $this->handleDbErrorAndRedirect("Update failed: Category Name already exists.", '/admin/fees/categories/edit/' . $categoryId);
             } else {
                error_log("Fee Category Update Error: " . $e->getMessage());
                $this->handleDbErrorAndRedirect("Database error updating category.", '/admin/fees/categories');
             }
             return;
        }
        // --- End Database Update ---

        $this->redirect('/admin/fees/categories');
    }

    /**
     * Remove the specified fee category from storage. (Handles POST /admin/fees/categories/delete/{id})
     */
    public function delete(array $vars): void {
         // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/categories'); return; }
        // --- End Checks ---

         $categoryId = $vars['id'] ?? null;
         if (!$categoryId) { $this->handleDbErrorAndRedirect("Invalid Fee Category ID for deletion.", '/admin/fees/categories'); return; }

        // --- Database Deletion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/categories'); return; }

        // **Important Check:** Before deleting, check if any Fee Structures use this category.
        // Our schema uses ON DELETE RESTRICT for fee_structures.category_id, so the DELETE
        // query below would fail anyway, but checking first provides a better user message.
        try {
            $checkSql = "SELECT COUNT(*) FROM fee_structures WHERE category_id = :id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([':id' => $categoryId]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                $this->handleDbErrorAndRedirect("Cannot delete category: It is currently used by {$count} fee structure(s).", '/admin/fees/categories');
                return;
            }

            // Proceed with deletion if no structures are linked
            $stmt = $pdo->prepare("DELETE FROM fee_categories WHERE category_id = :id");
            $stmt->execute([':id' => $categoryId]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['flash_success'] = "Fee category deleted successfully!";
            } else {
                $_SESSION['flash_error'] = "Fee category not found or already deleted.";
            }

        } catch (\PDOException $e) {
             error_log("Fee Category Delete Error: " . $e->getMessage());
             // Catch potential FK constraint errors if the check above somehow fails or isn't implemented
             if (strpos($e->getMessage(), 'constraint fails') !== false) {
                  $this->handleDbErrorAndRedirect("Cannot delete category due to related records (e.g., Fee Structures).", '/admin/fees/categories');
             } else {
                  $this->handleDbErrorAndRedirect("Database error deleting category.", '/admin/fees/categories');
             }
             return;
        }
        // --- End Database Deletion ---

        $this->redirect('/admin/fees/categories');
    }

    /** Helper for DB error redirects */
    private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
        $_SESSION['flash_error'] = $message;
        $this->redirect($redirectTo);
    }
}
