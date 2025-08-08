<?php
namespace App\Modules\Portal\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;

class PortalFeeController extends BaseController {

    /**
     * Show the fee and payment history page for the logged-in student/parent.
     */
    public function index(): void {


        // --- Auth & Role Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        $userId = $_SESSION['user_id'];
        $userRoles = $_SESSION['roles'] ?? [];
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) {
             $_SESSION['flash_error'] = "Access Denied.";
             $this->redirect('/logout'); return;
        }
        // --- End Checks ---

        // Read flash messages into local variables
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error = $_SESSION['flash_error'] ?? null;



        // Unset session variables AFTER reading them into local variables
        unset($_SESSION['flash_success']);
        unset($_SESSION['flash_error']);


        $pdo = DbConnection::getInstance();
        $studentInfo = null;
        $linkedStudents = [];
        $selectedStudentId = null;
        $invoices = [];
        $payments = [];
        $viewError = null; // Specific view error


        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                 // --- Determine linked students and selected student ---
                 // (This logic is identical to PortalDashboardController)
                if (in_array('Student', $userRoles)) {
                    // ... (fetch student for Student role) ...
                     $stmt = $pdo->prepare("SELECT student_id, first_name, last_name FROM students WHERE user_id = :userId LIMIT 1");
                     $stmt->execute([':userId' => $userId]);
                     $studentLink = $stmt->fetch(PDO::FETCH_ASSOC);
                     if ($studentLink) {
                         $linkedStudents[] = $studentLink;
                         $selectedStudentId = $studentLink['student_id'];
                     }
                } elseif (in_array('Parent', $userRoles)) {
                    // ... (fetch linked students for Parent role) ...
                     $stmt = $pdo->prepare("SELECT s.student_id, s.first_name, s.last_name, s.admission_number FROM student_guardian_links sgl JOIN students s ON sgl.student_id = s.student_id WHERE sgl.user_id = :userId AND s.status = 'active' ORDER BY s.last_name, s.first_name");
                     $stmt->execute([':userId' => $userId]);
                     $linkedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                     if (!empty($linkedStudents)) {
                         $requestedStudentId = filter_input(INPUT_GET, 'view_student_id', FILTER_VALIDATE_INT);
                         $isValidSelection = false;
                         if ($requestedStudentId) {
                             foreach ($linkedStudents as $student) { if ($student['student_id'] == $requestedStudentId) { $selectedStudentId = $requestedStudentId; $isValidSelection = true; break; } }
                         }
                         if (!$isValidSelection) { $selectedStudentId = $linkedStudents[0]['student_id']; }
                     }
                }
                 // --- End determining linked/selected students ---

                // --- Fetch details ONLY for the SELECTED student ---
                if ($selectedStudentId) {
                    // ... (fetch studentInfo for selected student) ...
                     $stmtStudent = $pdo->prepare("SELECT student_id, first_name, last_name, admission_number FROM students WHERE student_id = ?");
                     $stmtStudent->execute([$selectedStudentId]);
                     $studentInfo = $stmtStudent->fetch(PDO::FETCH_ASSOC);


                    if ($studentInfo) {
                        // ... (Fetch Invoices) ...
                        $sqlInvoices = "SELECT fi.*, (fi.total_payable - fi.total_paid) AS balance_due FROM fee_invoices fi WHERE fi.student_id = :studentId AND status != 'cancelled' ORDER BY fi.issue_date DESC, fi.invoice_id DESC";
                        $stmtInvoices = $pdo->prepare($sqlInvoices);
                        $stmtInvoices->execute([':studentId' => $selectedStudentId]);
                        $invoices = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

                        // ... (Fetch Payments) ...
                        $sqlPayments = "SELECT p.*, pm.method_name, pa.invoice_id AS allocated_invoice_id, fi.invoice_number FROM payments p JOIN payment_methods pm ON p.method_id = pm.method_id LEFT JOIN payment_allocations pa ON p.payment_id = pa.payment_id LEFT JOIN fee_invoices fi ON pa.invoice_id = fi.invoice_id WHERE p.student_id = :studentId AND p.payment_status = 'completed' ORDER BY p.payment_date DESC, p.payment_id DESC";
                        $stmtPayments = $pdo->prepare($sqlPayments);
                        $stmtPayments->execute([':studentId' => $selectedStudentId]);
                        $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

                    } else {
                        $viewError = "Could not load details for selected student.";
                        $selectedStudentId = null;
                    }
                }
                // Use flash_error if set previously (e.g., by redirect from dashboard with error)
                if (empty($linkedStudents) && (in_array('Student', $userRoles) || in_array('Parent', $userRoles)) && !$flash_error) {
                    $flash_error = "No associated student record found for this user account.";
                }

            } catch (\PDOException $e) {
                $viewError = "Database Error: " . $e->getMessage();
                error_log("Portal Fees Error: " . $e->getMessage());
            }
        } // End if $pdo

        // Prepare data array for the view
        $viewData = [
            'pageTitle' => 'My Fees & Payments',
            'student' => $studentInfo,
            'linkedStudents' => $linkedStudents,
            'selectedStudentId' => $selectedStudentId,
            'invoices' => $invoices,
            'payments' => $payments,
            'viewError' => $viewError, // Pass specific view error
            'flash_success' => $flash_success, // Pass local flash variable
            'flash_error' => $flash_error    // Pass local flash variable
        ];

        // Use layout_portal
        $this->loadView('Portal/Views/fees', $viewData, 'layout_portal');
        // Unsetting session variables is done earlier now
    }

     // Inherited methods like redirect(), hasRole(), loadView() are assumed from BaseController
}