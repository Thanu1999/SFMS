<?php
namespace App\Modules\Portal\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;

class PortalDashboardController extends BaseController {

    /**
     * Show the main dashboard for the student/parent portal.
     */
    public function index(): void {
        // --- Auth Check ---
        if (!isset($_SESSION['user_id'])) { $this->redirect('/login'); return; }
        $userId = $_SESSION['user_id'];
        $userRoles = $_SESSION['roles'] ?? [];
        if (!$this->hasRole('Student') && !$this->hasRole('Parent')) {
             $_SESSION['flash_error'] = "Access Denied.";
             $this->redirect('/logout'); return;
        }
        // --- End Checks ---

        $pdo = DbConnection::getInstance();
        $studentInfo = null; // Info for the currently selected student
        $linkedStudents = []; // List of all students linked to the parent/user
        $selectedStudentId = null; // The ID the user wants to view
        $outstandingBalance = 0;
        $dbError = null;

        if (!$pdo) {
            $dbError = "Database connection failed.";
        } else {
            try {
                // --- Determine linked students and selected student ---
                if (in_array('Student', $userRoles)) {
                    // If logged-in user IS a student, they are the only relevant student
                    $stmt = $pdo->prepare("SELECT student_id, first_name, last_name FROM students WHERE user_id = :userId LIMIT 1");
                    $stmt->execute([':userId' => $userId]);
                    $studentLink = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($studentLink) {
                        $linkedStudents[] = $studentLink; // List contains only self
                        $selectedStudentId = $studentLink['student_id']; // Select self
                    }
                } elseif (in_array('Parent', $userRoles)) {
                    // If user is a Parent, find all linked students
                    $stmt = $pdo->prepare("SELECT s.student_id, s.first_name, s.last_name, s.admission_number
                                           FROM student_guardian_links sgl
                                           JOIN students s ON sgl.student_id = s.student_id
                                           WHERE sgl.user_id = :userId AND s.status = 'active'
                                           ORDER BY s.last_name, s.first_name");
                    $stmt->execute([':userId' => $userId]);
                    $linkedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($linkedStudents)) {
                        // Check if a specific student is requested via GET parameter
                        $requestedStudentId = filter_input(INPUT_GET, 'view_student_id', FILTER_VALIDATE_INT);

                        // Validate if the requested ID belongs to this parent
                        $isValidSelection = false;
                        if ($requestedStudentId) {
                            foreach ($linkedStudents as $student) {
                                if ($student['student_id'] == $requestedStudentId) {
                                    $selectedStudentId = $requestedStudentId;
                                    $isValidSelection = true;
                                    break;
                                }
                            }
                        }
                        // If no valid selection requested, default to the first linked student
                        if (!$isValidSelection) {
                            $selectedStudentId = $linkedStudents[0]['student_id'];
                        }
                    }
                }
                // --- End determining linked/selected students ---


                // --- Fetch details for the SELECTED student ---
                if ($selectedStudentId) {
                    $sql = "SELECT s.*, c.class_name
                            FROM students s
                            LEFT JOIN classes c ON s.current_class_id = c.class_id
                            WHERE s.student_id = :studentId LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':studentId' => $selectedStudentId]);
                    $studentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($studentInfo) {
                        // Fetch outstanding balance for this specific student
                        $sqlBalance = "SELECT SUM(total_payable - total_paid) AS total_due
                                       FROM fee_invoices
                                       WHERE student_id = :studentId
                                         AND status NOT IN ('paid', 'cancelled')";
                        $stmtBalance = $pdo->prepare($sqlBalance);
                        $stmtBalance->execute([':studentId' => $studentInfo['student_id']]);
                        $outstandingBalance = $stmtBalance->fetchColumn() ?: 0.00;
                    } else {
                         $dbError = "Could not load details for selected student."; // Should not happen if ID validated
                         $selectedStudentId = null; // Reset selection
                    }
                }
                // Set error message if logged in user SHOULD have links but none were found
                if (empty($linkedStudents) && (in_array('Student', $userRoles) || in_array('Parent', $userRoles))) {
                     $_SESSION['flash_error'] = "No associated student record found for this user account.";
                }


            } catch (\PDOException $e) { /* ... Error handling ... */ }
        }

        $this->loadView('Portal/Views/dashboard', [ // View path relative to Modules/
            'pageTitle' => 'Portal Dashboard',
            'student' => $studentInfo,
            'linkedStudents' => $linkedStudents,
            'selectedStudentId' => $selectedStudentId,
            'outstandingBalance' => $outstandingBalance,
            'viewError' => $dbError, // Pass specific error
            // Global flash handled by layout
        ], 'layout_portal');
    }
}