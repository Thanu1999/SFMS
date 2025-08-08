<?php
namespace App\Modules\FeeManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;
use DateTime; // Needed for due date calculations

class InvoiceGenerationController extends BaseController {

    /**
     * Show the form for generating fee invoices. (Handles GET /admin/fees/invoices/generate)
     */
    public function showGenerateForm(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied.";
            $this->redirect('/dashboard'); return;
        }
        // --- End Access Control ---

        // Fetch data needed for form dropdowns
        $formData = $this->getFormDataForGenerator();

        $this->loadView('FeeManagement/Views/invoices/generate_form', [
            'pageTitle' => 'Generate Fee Invoices',
            'sessions' => $formData['sessions'] ?? [],
            'classes' => $formData['classes'] ?? [],
            'structures' => $formData['structures'] ?? [], // Structures might depend on selected session/class via JS/AJAX later
            'viewError' => $formData['error'] ?? null, // Pass DB errors fetching form data
            'errors' => $_SESSION['form_errors'] ?? [], // Pass validation errors if any
            'oldInput' => $_SESSION['old_input'] ?? [] // Pass old input
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Process the generation of fee invoices. (Handles POST /admin/fees/invoices/generate)
     */
    public function processGeneration(): void {
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/fees/invoices/generate'); return; }
        // --- End Checks ---

        // --- Data Validation & Retrieval ---
        $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);
        $classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
        $structureIds = $_POST['structure_ids'] ?? []; // Expecting an array of fee structure IDs

        $errors = [];
        if (empty($sessionId)) $errors[] = "Academic Session is required.";
        if (empty($classId)) $errors[] = "Class is required.";
        if (empty($structureIds) || !is_array($structureIds)) $errors[] = "At least one Fee Structure must be selected.";
        // --- End Basic Validation ---

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $this->redirect('/admin/fees/invoices/generate');
            return;
        }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/fees/invoices/generate'); return; }

        // --- Generation Logic ---
        $generatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $currentUserId = $_SESSION['user_id'] ?? null; // User performing the action

        try {
            // 1. Fetch selected Fee Structures details
            $placeholders = implode(',', array_fill(0, count($structureIds), '?'));
            $sqlStructures = "SELECT * FROM fee_structures WHERE structure_id IN ($placeholders)";
            $stmtStructures = $pdo->prepare($sqlStructures);
            $stmtStructures->execute($structureIds);
            $structures = $stmtStructures->fetchAll(PDO::FETCH_ASSOC);
            if (empty($structures)) { throw new \Exception("Selected fee structures not found."); }

            // 2. Fetch active Students for the selected class and session
            $sqlStudents = "SELECT student_id FROM students WHERE current_class_id = ? AND current_session_id = ? AND status = 'active'";
            $stmtStudents = $pdo->prepare($sqlStudents);
            $stmtStudents->execute([$classId, $sessionId]);
            $studentIds = $stmtStudents->fetchAll(PDO::FETCH_COLUMN);
            if (empty($studentIds)) { throw new \Exception("No active students found for the selected class and session."); }

            // 3. Fetch Academic Session details (for due date calculation)
            $stmtSession = $pdo->prepare("SELECT start_date, end_date FROM academic_sessions WHERE session_id = ?");
            $stmtSession->execute([$sessionId]);
            $session = $stmtSession->fetch(PDO::FETCH_ASSOC);
            if (!$session) { throw new \Exception("Academic session details not found."); }


            // 4. Loop and Generate Invoices (use transactions)
            foreach ($studentIds as $studentId) {
                foreach ($structures as $structure) {
                    // Check if invoice already exists for this student/structure/session (prevent duplicates)
                    $sqlCheck = "SELECT COUNT(*) FROM fee_invoices WHERE student_id = ? AND structure_id = ? AND session_id = ?";
                    $stmtCheck = $pdo->prepare($sqlCheck);
                    $stmtCheck->execute([$studentId, $structure['structure_id'], $sessionId]);
                    if ($stmtCheck->fetchColumn() > 0) {
                        $skippedCount++;
                        continue; // Skip if already exists
                    }

                    // Start transaction for each invoice + items
                    $pdo->beginTransaction();

                    try {
                        $invoiceNumber = $this->generateInvoiceNumber(); // Implement this helper
                        $dueDate = $this->calculateDueDate($structure['frequency'], $structure['due_day'], $session['start_date']); // Implement this helper

                        // Insert into fee_invoices
                        $sqlInvoice = "INSERT INTO fee_invoices (student_id, session_id, structure_id, invoice_number, description, total_amount, total_payable, issue_date, due_date, status, created_by_user_id, created_at, updated_at)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, 'unpaid', ?, NOW(), NOW())";
                        $stmtInvoice = $pdo->prepare($sqlInvoice);
                        $totalAmount = (float)$structure['amount']; // For now, assume discount is 0
                        $totalPayable = $totalAmount; // Adjust if discounts apply

                        $stmtInvoice->execute([
                            $studentId,
                            $sessionId,
                            $structure['structure_id'],
                            $invoiceNumber,
                            $structure['structure_name'], // Use structure name as description
                            $totalAmount,
                            $totalPayable,
                            $dueDate,
                            $currentUserId
                        ]);
                        $invoiceId = $pdo->lastInsertId();

                        // Insert into fee_invoice_items (simple case: one item per invoice)
                        $sqlItem = "INSERT INTO fee_invoice_items (invoice_id, category_id, description, amount, payable_amount, created_at)
                                    VALUES (?, ?, ?, ?, ?, NOW())";
                        $stmtItem = $pdo->prepare($sqlItem);
                        $stmtItem->execute([
                            $invoiceId,
                            $structure['category_id'],
                            $structure['structure_name'], // Item description
                            $totalAmount, // Item amount
                            $totalPayable // Item payable amount
                        ]);

                        $pdo->commit();
                        $generatedCount++;

                    } catch (\PDOException $e) {
                        $pdo->rollBack();
                        error_log("Invoice Generation Error (Student: {$studentId}, Structure: {$structure['structure_id']}): " . $e->getMessage());
                        $errorCount++;
                    }
                } // end structure loop
            } // end student loop

            // Set success/info message
            $message = "Invoice generation complete. Generated: {$generatedCount}, Skipped (duplicates): {$skippedCount}";
            if ($errorCount > 0) {
                $_SESSION['flash_error'] = "{$errorCount} errors occurred during generation. Check logs. " . $message;
            } else {
                 $_SESSION['flash_success'] = $message;
            }

        } catch (\Exception $e) { // Catch broader errors like students/structures not found
             error_log("Invoice Generation Process Error: " . $e->getMessage());
             $_SESSION['flash_error'] = "Error during generation process: " . $e->getMessage();
        }
        // --- End Generation Logic ---

        $this->redirect('/admin/fees/invoices/generate'); // Redirect back to form
    }


    /** Helper to fetch data needed for generator form */
    private function getFormDataForGenerator(): array {
        $pdo = DbConnection::getInstance();
        // Reuse StudentController's helper temporarily, or create a dedicated one
        $studentController = new \App\Modules\StudentManagement\Controllers\StudentController(); // Temporary instantiation
        $formData = $studentController->getFormData(); // Use the existing helper for sessions/classes

         if (!$pdo) {
            $formData['error'] = "Database connection failed while fetching form data.";
            return $formData;
        }
        try {
            // Fetch all structures - might need refinement to filter by selected session/class via JS/AJAX
             $formData['structures'] = $pdo->query("SELECT structure_id, structure_name, amount FROM fee_structures ORDER BY structure_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
             error_log("Get Generator Form Data Error: " . $e->getMessage());
             $formData['error'] = ($formData['error'] ? $formData['error'] . '; ' : '') . "Database error fetching structures.";
        }
        return $formData;
    }


    /** Helper to generate a unique invoice number (example) */
    private function generateInvoiceNumber(): string {
        // Simple example: YEARMONTHDAY-SEQUENTIAL (e.g., INV-20250501-001)
        // Needs a proper sequence generator in a real app
        $prefix = "INV-";
        $datePart = date('Ymd');
        // Placeholder for sequence - replace with a real sequence generator call
        $sequence = mt_rand(100, 999); // Replace with reliable sequence
        return $prefix . $datePart . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

     /** Helper to calculate due date (basic example) */
     private function calculateDueDate(string $frequency, ?int $dueDay, string $sessionStartDate): ?string {
         // This needs to be much more robust based on school policy
         // Simple example: 15 days from issue date (today)
         $issueDate = new DateTime();
         if ($frequency === 'one-time') {
             // Maybe due based on admission date or a fixed offset from session start?
             // Example: Due 15 days from session start
             try {
                 $startDate = new DateTime($sessionStartDate);
                 $startDate->modify('+15 days');
                 return $startDate->format('Y-m-d');
             } catch (\Exception $e) { return date('Y-m-d', strtotime('+15 days')); } // Fallback
         } elseif ($frequency === 'monthly' && $dueDay) {
              // Due on the 'dueDay' of the *next* month? Or current month if issued before dueDay?
              // Simplified: Due end of current month
              return date('Y-m-t');
         } else {
             // Default: 15 days from today
             return date('Y-m-d', strtotime('+15 days'));
         }
         // Add logic for quarterly, annual etc. based on term dates / session start/end
     }

     /** Helper for DB error redirects (already exists in BaseController if moved) */
     private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
     }

}