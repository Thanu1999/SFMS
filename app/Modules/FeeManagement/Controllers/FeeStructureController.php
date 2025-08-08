<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;

use DateTime; // Keep if used elsewhere or for future date logic

class FeeStructureController extends BaseController {

    /**
     * Display a list of fee structures. (Handles GET /admin/fees/structures)
     */
    public function index(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot manage fee structures.";
            $this->redirect('/dashboard'); return;
        }
        // --- End Access Control ---

        $structures = [];
        $viewError = null; // Use specific variable name
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                // Join with related tables for better display
                $sql = "SELECT fs.*, fc.category_name, acs.session_name, cl.class_name
                        FROM fee_structures fs
                        JOIN fee_categories fc ON fs.category_id = fc.category_id
                        JOIN academic_sessions acs ON fs.session_id = acs.session_id
                        LEFT JOIN classes cl ON fs.applicable_class_id = cl.class_id
                        ORDER BY acs.session_name DESC, fc.category_name ASC, fs.structure_name ASC";
                $stmt = $pdo->query($sql);
                $structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Fee Structure Fetch Error: " . $e->getMessage());
            }
        }

        $this->loadView('FeeManagement/Views/structures/index', [
            'structures' => $structures,
            'pageTitle' => 'Fee Structures',
            'viewError' => $viewError // Pass specific error
            // Global flash messages handled by layout header
        ], 'layout_admin');
    }

    /**
     * Show the form for creating a new fee structure. (Handles GET /admin/fees/structures/create)
     */
    public function create(): void {
         // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied.";
             $this->redirect('/admin/fees/structures'); return;
         }
        // --- End Access Control ---

        // Fetch data needed for form dropdowns
        $formData = $this->getFormData();

        $this->loadView('FeeManagement/Views/structures/create', [
            'pageTitle' => 'Create Fee Structure',
            'sessions' => $formData['sessions'] ?? [],
            'categories' => $formData['categories'] ?? [],
            'classes' => $formData['classes'] ?? [],
            'viewError' => $formData['error'] ?? null, // Pass DB errors from fetching form data
            'errors' => $_SESSION['form_errors'] ?? [], // For validation feedback
            'oldInput' => $_SESSION['old_input'] ?? []  // For repopulation
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Store a newly created fee structure in storage. (Handles POST /admin/fees/structures)
     */
    public function store(): void {
         // --- Access Control & Method Check ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/structures/create'); return; }
        // --- End Checks ---

        // --- Data Validation & Retrieval ---
        // Basic retrieval - ADD ROBUST VALIDATION!
        $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $structureName = trim($_POST['structure_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $applicableClassId = filter_input(INPUT_POST, 'applicable_class_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $frequency = $_POST['frequency'] ?? null;
        $dueDay = filter_input(INPUT_POST, 'due_day', FILTER_VALIDATE_INT);
        $lateFeeType = $_POST['late_fee_type'] ?? 'none';
        $lateFeeAmount = filter_input(INPUT_POST, 'late_fee_amount', FILTER_VALIDATE_FLOAT) ?: 0.00;
        $lateFeeBasis = filter_input(INPUT_POST, 'late_fee_calculation_basis', FILTER_VALIDATE_FLOAT) ?: 0.00; // e.g., percentage or days

        $errors = [];
        if (empty($sessionId)) $errors[] = "Academic Session is required.";
        if (empty($categoryId)) $errors[] = "Fee Category is required.";
        if (empty($structureName)) $errors[] = "Structure Name is required.";
        if ($amount === false || $amount <= 0) $errors[] = "Valid Amount is required.";
        // Validate frequency, late fee type against ENUM values if possible
        // Validate class ID exists or is empty/null
        // Validate dueDay makes sense for frequency
        // --- End Basic Validation ---

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            // TODO: Store old input in session to repopulate form
            $this->redirect('/admin/fees/structures/create');
            return;
        }

        // --- Database Insertion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/structures'); return; }

        try {
            $sql = "INSERT INTO fee_structures (session_id, category_id, structure_name, description, applicable_class_id, amount, frequency, due_day, late_fee_type, late_fee_amount, late_fee_calculation_basis, created_by_user_id, created_at, updated_at)
                    VALUES (:session_id, :category_id, :structure_name, :description, :applicable_class_id, :amount, :frequency, :due_day, :late_fee_type, :late_fee_amount, :late_fee_basis, :user_id, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);

            $params = [
                ':session_id' => $sessionId,
                ':category_id' => $categoryId,
                ':structure_name' => $structureName,
                ':description' => $description ?: null,
                ':applicable_class_id' => $applicableClassId ?: null, // Allow NULL if 'All Classes' selected
                ':amount' => $amount,
                ':frequency' => $frequency,
                ':due_day' => $dueDay ?: null,
                ':late_fee_type' => $lateFeeType,
                ':late_fee_amount' => ($lateFeeType !== 'none') ? $lateFeeAmount : 0.00,
                ':late_fee_basis' => ($lateFeeType !== 'none') ? $lateFeeBasis : 0.00,
                ':user_id' => $_SESSION['user_id'] ?? null // Store who created it
            ];

            if ($stmt->execute($params)) {
                $_SESSION['flash_success'] = "Fee structure created successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to create fee structure.";
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry based on UNIQUE KEY uk_fee_structure
                $this->handleDbErrorAndRedirect("Creation failed: A fee structure with the same Session, Category, Name, and Class might already exist.", '/admin/fees/structures/create');
            } else {
                error_log("Fee Structure Store Error: " . $e->getMessage());
                $this->handleDbErrorAndRedirect("Database error creating fee structure.", '/admin/fees/structures');
            }
             return;
        }
        // --- End Database Insertion ---

        $this->redirect('/admin/fees/structures');
    }

    /**
     * Show the form for editing the specified fee structure. (Handles GET /admin/fees/structures/edit/{id})
     */
    public function edit(array $vars): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
           $_SESSION['flash_error'] = "Access Denied: Cannot edit fee structures.";
           $this->redirect('/admin/fees/structures'); return;
        }
       // --- End Access Control ---

       $structureId = $vars['id'] ?? null;
       if (!$structureId) { $this->handleDbErrorAndRedirect("Invalid Fee Structure ID.", '/admin/fees/structures'); return; }

       $pdo = DbConnection::getInstance();
       if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/structures'); return; }

       $structure = null;
       $formData = ['sessions' => [], 'categories' => [], 'classes' => [], 'error' => null];
       $viewError = null;
       

       try {
           // Fetch structure data
           $stmt = $pdo->prepare("SELECT * FROM fee_structures WHERE structure_id = :id");
           $stmt->bindParam(':id', $structureId, PDO::PARAM_INT);
           $stmt->execute();
           $structure = $stmt->fetch(PDO::FETCH_ASSOC);

           if (!$structure) { $this->handleDbErrorAndRedirect("Fee Structure not found.", '/admin/fees/structures'); return; }

           // Fetch data needed for form dropdowns
           $formData = $this->getFormData(); // Fetches sessions, categories, classes
           $viewError = $formData['error']; // Assign potential error

        } catch (PDOException $e) {
            error_log("Fee Structure Edit Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching structure data.";
        }

       $this->loadView('FeeManagement/Views/structures/edit', [
           'pageTitle' => 'Edit Fee Structure: ' . htmlspecialchars($structure['structure_name']),
           'structure' => $structure, // Pass current structure data
           'sessions' => $formData['sessions'] ?? [],
           'categories' => $formData['categories'] ?? [],
           'classes' => $formData['classes'] ?? [],
           'viewError' => $viewError,
            'errors' => $_SESSION['form_errors'] ?? [], // For validation feedback
            'oldInput' => $_SESSION['old_input'] ?? []  // For repopulation
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
   }

   /**
    * Update the specified fee structure in storage. (Handles POST /admin/fees/structures/update/{id})
    */
   public function update(array $vars): void {
       // --- Access Control & Method Check ---
       if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
       if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/structures'); return; }
       // --- End Checks ---

       $structureId = $vars['id'] ?? null;
       if (!$structureId) { $this->handleDbErrorAndRedirect("Invalid Fee Structure ID for update.", '/admin/fees/structures'); return; }

       // --- Data Validation & Retrieval ---
       // Basic retrieval - ADD ROBUST VALIDATION!
       $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
       $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
       $structureName = trim($_POST['structure_name'] ?? '');
       $description = trim($_POST['description'] ?? '');
       $applicableClassId = filter_input(INPUT_POST, 'applicable_class_id', FILTER_VALIDATE_INT);
           if ($applicableClassId === 0) $applicableClassId = null; // Handle "All Classes" value if it's 0 or empty string
       $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
       $frequency = $_POST['frequency'] ?? null;
       $dueDay = filter_input(INPUT_POST, 'due_day', FILTER_VALIDATE_INT);
            if ($dueDay === 0 || $dueDay === false) $dueDay = null;
       $lateFeeType = $_POST['late_fee_type'] ?? 'none';
       $lateFeeAmount = filter_input(INPUT_POST, 'late_fee_amount', FILTER_VALIDATE_FLOAT) ?: 0.00;
       $lateFeeBasis = filter_input(INPUT_POST, 'late_fee_calculation_basis', FILTER_VALIDATE_FLOAT) ?: 0.00;

       $errors = [];
       if (empty($sessionId)) $errors[] = "Academic Session is required.";
       if (empty($categoryId)) $errors[] = "Fee Category is required.";
       if (empty($structureName)) $errors[] = "Structure Name is required.";
       if ($amount === false || $amount <= 0) $errors[] = "Valid Amount is required.";
       // Add more validation...
       // --- End Basic Validation ---

        if (!empty($errors)) {
           $_SESSION['flash_error'] = implode('<br>', $errors);
           $this->redirect('/admin/fees/structures/edit/' . $structureId);
           return;
        }

        // --- Database Update ---
       $pdo = DbConnection::getInstance();
       if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/structures'); return; }

       try {
           $sql = "UPDATE fee_structures SET
                       session_id = :session_id,
                       category_id = :category_id,
                       structure_name = :structure_name,
                       description = :description,
                       applicable_class_id = :applicable_class_id,
                       amount = :amount,
                       frequency = :frequency,
                       due_day = :due_day,
                       late_fee_type = :late_fee_type,
                       late_fee_amount = :late_fee_amount,
                       late_fee_calculation_basis = :late_fee_basis,
                       updated_at = NOW()
                   WHERE structure_id = :structure_id";

           $stmt = $pdo->prepare($sql);
           $params = [
               ':session_id' => $sessionId,
               ':category_id' => $categoryId,
               ':structure_name' => $structureName,
               ':description' => $description ?: null,
               ':applicable_class_id' => $applicableClassId ?: null,
               ':amount' => $amount,
               ':frequency' => $frequency,
               ':due_day' => $dueDay ?: null,
               ':late_fee_type' => $lateFeeType,
               ':late_fee_amount' => ($lateFeeType !== 'none') ? $lateFeeAmount : 0.00,
               ':late_fee_basis' => ($lateFeeType !== 'none') ? $lateFeeBasis : 0.00,
               ':structure_id' => $structureId
           ];

            if ($stmt->execute($params)) {
                $_SESSION['flash_success'] = "Fee structure updated successfully!";
            } else {
                $_SESSION['flash_error'] = "Failed to update fee structure.";
            }

       } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry on unique key
               $this->handleDbErrorAndRedirect("Update failed: A fee structure with the same Session, Category, Name, and Class might already exist.", '/admin/fees/structures/edit/' . $structureId);
            } else {
               error_log("Fee Structure Update Error: " . $e->getMessage());
               $this->handleDbErrorAndRedirect("Database error updating fee structure.", '/admin/fees/structures');
            }
            return;
       }
       // --- End Database Update ---

       $this->redirect('/admin/fees/structures');
   }

   /**
    * Remove the specified fee structure from storage. (Handles POST /admin/fees/structures/delete/{id})
    */
   public function delete(array $vars): void {
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/structures'); return; }
       // --- End Checks ---

        $structureId = $vars['id'] ?? null;
        if (!$structureId) { $this->handleDbErrorAndRedirect("Invalid Fee Structure ID for deletion.", '/admin/fees/structures'); return; }

       // --- Database Deletion ---
       $pdo = DbConnection::getInstance();
       if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/structures'); return; }

       // **Important Check:** Before deleting, check if any Fee Invoices (or student assignments) use this structure.
       // Our schema uses ON DELETE RESTRICT for fee_invoices.structure_id.
       try {
           $checkSql = "SELECT COUNT(*) FROM fee_invoices WHERE structure_id = :id"; // Check fee_invoices table
           $checkStmt = $pdo->prepare($checkSql);
           $checkStmt->execute([':id' => $structureId]);
           $count = $checkStmt->fetchColumn();

           if ($count > 0) {
               $this->handleDbErrorAndRedirect("Cannot delete structure: It is currently used by {$count} fee invoice(s).", '/admin/fees/structures');
               return;
           }

           // Proceed with deletion if not linked
           $stmt = $pdo->prepare("DELETE FROM fee_structures WHERE structure_id = :id");
           $stmt->execute([':id' => $structureId]);

           if ($stmt->rowCount() > 0) {
               $_SESSION['flash_success'] = "Fee structure deleted successfully!";
           } else {
               $_SESSION['flash_error'] = "Fee structure not found or already deleted.";
           }

       } catch (\PDOException $e) {
            error_log("Fee Structure Delete Error: " . $e->getMessage());
            // Catch potential FK constraint errors
            if (strpos($e->getMessage(), 'constraint fails') !== false) {
                 $this->handleDbErrorAndRedirect("Cannot delete structure due to related records (e.g., Invoices).", '/admin/fees/structures');
            } else {
                 $this->handleDbErrorAndRedirect("Database error deleting fee structure.", '/admin/fees/structures');
            }
            return;
       }
       // --- End Database Deletion ---

       $this->redirect('/admin/fees/structures');
   }

   // ... (keep existing private helper methods) ...


    /** Helper to fetch data needed for forms */
    private function getFormData(): array {
        $pdo = DbConnection::getInstance();
        $data = ['sessions' => [], 'categories' => [], 'classes' => [], 'error' => null];
        if (!$pdo) {
            $data['error'] = "Database connection failed while fetching form data.";
            return $data;
        }
        try {
            // Fetch active sessions? Or all? Fetching all for now.
            $data['sessions'] = $pdo->query("SELECT session_id, session_name FROM academic_sessions ORDER BY start_date DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
            $data['categories'] = $pdo->query("SELECT category_id, category_name FROM fee_categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
            $data['classes'] = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
             error_log("Get Form Data Error: " . $e->getMessage());
             $data['error'] = "Database error fetching form data.";
        }
        return $data;
    }

     /** Helper for DB error redirects */
     private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
     }

    // Add edit(), update(), delete() methods later...
}