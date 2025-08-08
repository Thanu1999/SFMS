<?php
namespace App\Modules\StudentManagement\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use App\Core\Services\SequenceGenerator; // <-- ADD THIS LINE
use PDO;

class StudentController extends BaseController {

    /**
     * Display a list of students. (Handles GET /admin/students)
     */
    public function index(): void
    {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot view student list.";
            $this->redirect('/dashboard');
            return;
        }
        // --- End Access Control ---

        $students = [];
        $error = null;
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $error = "Database connection failed.";
        } else {
            try {
                // Join with related tables for display
                $sql = "SELECT s.*, c.class_name, acs.session_name
                        FROM students s
                        LEFT JOIN classes c ON s.current_class_id = c.class_id
                        LEFT JOIN academic_sessions acs ON s.current_session_id = acs.session_id
                        ORDER BY s.last_name ASC, s.first_name ASC";
                $stmt = $pdo->query($sql);
                $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                $error = "Database query failed: " . $e->getMessage();
                error_log("Student Fetch Error: " . $e->getMessage());
            }
        }

        $this->loadView('StudentManagement/Views/index', [
            'students' => $students,
            'pageTitle' => 'Student Management',
            'viewError' => $error // Pass specific DB error for this view
            // Flash messages handled by layout
        ], 'layout_admin');
    }

    /**
     * Show the form for creating a new student. (Handles GET /admin/students/create)
     */
    public function create(): void
    {
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { /* ... access denied ... */
        }

        $formData = $this->getFormData(); // Gets sessions, classes
        // No need to fetch parent users here anymore
        $studentUsers = $this->getLinkableStudentUsers(); // Fetch potential student logins

        $this->loadView('StudentManagement/Views/create', [
            'pageTitle' => 'Add New Student',
            'sessions' => $formData['sessions'] ?? [],
            'classes' => $formData['classes'] ?? [],
            'studentUsers' => $studentUsers,
            'viewError' => $formData['error'] ?? null, // Pass DB errors from fetching form data
            // Pass form errors and old input if validation failed on previous POST
            'errors' => $_SESSION['form_errors'] ?? [],
            'oldInput' => $_SESSION['old_input'] ?? []
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        // Unset session variables AFTER passing them
        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Store a newly created student in storage. (Handles POST /admin/students)
     */
    public function store(): void
    {
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $this->redirect('/dashboard');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/students/create');
            return;
        }
        // --- End Checks ---

        // --- Data Validation & Retrieval ---
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? ''); // Retrieve middleName
        $dob = $_POST['date_of_birth'] ?? null;        // Retrieve dob
        $gender = $_POST['gender'] ?? null;            // Retrieve gender
        $email = trim($_POST['email'] ?? '');          // Retrieve email (only once)
        $admissionDate = $_POST['admission_date'] ?? null; // Retrieve admissionDate
        $classId = filter_input(INPUT_POST, 'current_class_id', FILTER_VALIDATE_INT);
        $sessionId = filter_input(INPUT_POST, 'current_session_id', FILTER_VALIDATE_INT);
        $section = trim($_POST['section'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $linkedUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: null;
        // Parent User ID is NOT retrieved here, as it's handled via Edit screen

        $errors = [];
        if (empty($firstName))
            $errors[] = "First Name is required.";
        if (empty($lastName))
            $errors[] = "Last Name is required.";
        if (empty($admissionDate))
            $errors[] = "Admission Date is required.";
        if (empty($classId))
            $errors[] = "Current Class is required.";
        if (empty($sessionId))
            $errors[] = "Current Session is required.";
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please provide a valid Email address (or leave blank).";
        }
        if (!in_array($status, ['active', 'inactive', 'graduated', 'transferred_out']))
            $status = 'active'; // Default to active on creation
        // Add more validation as needed...
        // --- End Validation ---

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            // TODO: Store old input in session to repopulate form
            $this->redirect('/admin/students/create');
            return;
        }

        // --- Database Insertion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) {
            $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/students');
            return;
        }

        // --- Generate Admission Number ---
        $sequenceGenerator = new SequenceGenerator();
        $nextAdmNumberInt = $sequenceGenerator->getNextValue('admission_number');

        if ($nextAdmNumberInt === null) {
            $this->handleDbErrorAndRedirect("Failed to generate admission number. Please try again.", '/admin/students/create');
            return;
        }
        $admissionNumber = (string) $nextAdmNumberInt;
        // --- End Generate ---

        try {
            // CORRECTED SQL: Removed parent_user_id column
            $sql = "INSERT INTO students (user_id, admission_number, first_name, last_name, middle_name, date_of_birth, gender, email, admission_date, current_class_id, current_session_id, section, status, created_at, updated_at)
                 VALUES (:user_id, :adm_no, :fname, :lname, :mname, :dob, :gender, :email, :adm_date, :class_id, :session_id, :section, :status, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);

            // CORRECTED PARAMS: Keys match SQL placeholders EXACTLY
            $params = [
                ':user_id' => $linkedUserId, // OK
                ':adm_no' => $admissionNumber, // OK
                ':fname' => $firstName, // OK
                ':lname' => $lastName, // OK
                ':mname' => $middleName ?: null, // Key matches :mname
                ':dob' => $dob ?: null,           // Key matches :dob
                ':gender' => $gender ?: null,     // Key matches :gender
                ':email' => $email ?: null,       // Key matches :email
                ':adm_date' => $admissionDate,   // Key matches :adm_date
                ':class_id' => $classId, // OK
                ':session_id' => $sessionId, // OK
                ':section' => $section ?: null, // OK
                ':status' => $status // OK
            ];

            if ($stmt->execute($params)) {
                $_SESSION['flash_success'] = "Student added successfully! Admission Number: " . htmlspecialchars($admissionNumber);
                // Maybe redirect to edit page?
                // $newStudentId = $pdo->lastInsertId();
                // $this->redirect('/admin/students/edit/' . $newStudentId); return;
            } else {
                $_SESSION['flash_error'] = "Failed to add student.";
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry (likely admission_number)
                $this->handleDbErrorAndRedirect("Creation failed: Admission Number '{$admissionNumber}' might already exist, or another constraint failed.", '/admin/students/create');
            } else {
                error_log("Student Store Error: " . $e->getMessage());
                $this->handleDbErrorAndRedirect("Database error adding student.", '/admin/students');
            }
            return;
        }
        // --- End Database Insertion ---

        $this->redirect('/admin/students');
    }

    /** Helper to fetch data needed for forms */
    public  function getFormData(): array {
        $pdo = DbConnection::getInstance();
        $data = ['sessions' => [], 'classes' => [], 'error' => null];
        if (!$pdo) {
            $data['error'] = "Database connection failed while fetching form data.";
            return $data;
        }
        try {
            // Fetch only ACTIVE sessions for assigning new students?
            $data['sessions'] = $pdo->query("SELECT session_id, session_name FROM academic_sessions WHERE is_active = TRUE ORDER BY start_date DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
            // If no active session, fetch the latest one perhaps? Add logic as needed.
            $data['classes'] = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
             error_log("Get Student Form Data Error: " . $e->getMessage());
             $data['error'] = "Database error fetching form data.";
        }
        return $data;
    }

     /** Helper for DB error redirects */
     private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
     }

    /**
     * Show the form for editing the specified student. (Handles GET /admin/students/edit/{id})
     */
    public function edit(array $vars): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot edit students.";
            $this->redirect('/admin/students'); return;
        }
        // --- End Access Control ---

        $studentId = $vars['id'] ?? null;
        if (!$studentId) { $this->handleDbErrorAndRedirect("Invalid Student ID.", '/admin/students'); return; }

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/students'); return; }
        $student = null;
        $linkedGuardians = [];
        $potentialGuardians = [];
        $studentUsers = [];
        $formData = ['sessions' => [], 'classes' => [], 'error' => null];
        $viewError = null; // Specific error for this view load

        try {
            // Fetch student
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :id");
            $stmt->execute([':id' => $studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$student) { $this->handleDbErrorAndRedirect("Student not found.", '/admin/students'); return; }

            // Fetch currently linked guardians
            $sqlGuardians = "SELECT sgl.link_id, sgl.relationship_type, u.user_id, u.full_name, u.username, u.email
                     FROM student_guardian_links sgl
                     JOIN users u ON sgl.user_id = u.user_id
                     WHERE sgl.student_id = :student_id
                     ORDER BY u.full_name ASC"; // <-- CORRECTED ORDER BY
            $stmtGuardians = $pdo->prepare($sqlGuardians);
            $stmtGuardians->execute([':student_id' => $studentId]);
            $linkedGuardians = $stmtGuardians->fetchAll(PDO::FETCH_ASSOC);

            // Fetch potential guardians (Users with 'Parent' role NOT already linked to THIS student)
            $sqlPotential = "SELECT u.user_id, CONCAT(u.full_name, ' (', u.username, ')') AS display_name
                            FROM users u
                            JOIN user_roles ur ON u.user_id = ur.user_id
                            JOIN roles r ON ur.role_id = r.role_id
                            WHERE r.role_name = 'Parent' AND u.status = 'active'
                              AND u.user_id NOT IN (SELECT user_id FROM student_guardian_links WHERE student_id = :student_id)
                            ORDER BY display_name ASC";
            $stmtPotential = $pdo->prepare($sqlPotential);
            $stmtPotential->execute([':student_id' => $studentId]);
            $potentialGuardians = $stmtPotential->fetchAll(PDO::FETCH_KEY_PAIR);


            // Fetch other form data (sessions, classes, linkable student users)
            $formData = $this->getFormData();
            $studentUsers = $this->getLinkableStudentUsers($studentId); // Pass current student ID
            $viewError = $formData['error']; // Assign potential error from getFormData

        } catch (PDOException $e) {
            error_log("Student Edit Fetch Error: " . $e->getMessage());
            $viewError = "Database error fetching student edit data.";
            // Let the view load and display the error
       }

        $this->loadView('StudentManagement/Views/edit', [
            'pageTitle' => 'Edit Student: ' . htmlspecialchars($student['first_name'] ?? '' . ' ' . $student['last_name'] ?? ''),
            'student' => $student, // This is the data for the form fields if NO errors occurred on POST
            'sessions' => $formData['sessions'] ?? [],
            'classes' => $formData['classes'] ?? [],
            'studentUsers' => $studentUsers,
            'linkedGuardians' => $linkedGuardians,
            'potentialGuardians' => $potentialGuardians,
            'viewError' => $viewError, // Pass specific DB errors for this view
            'errors' => $_SESSION['form_errors'] ?? [], // Pass validation errors from previous POST attempt
            'oldInput' => $_SESSION['old_input'] ?? [] // Pass old input from previous POST attempt
        ], 'layout_admin'); // <-- SPECIFY LAYOUT

        // Unset session variables AFTER passing them
        unset($_SESSION['form_errors']);
        unset($_SESSION['old_input']);
    }

    /**
     * Update the specified student in storage. (Handles POST /admin/students/update/{id})
     */
    public function update(array $vars): void {
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/students'); return; }
        // --- End Checks ---

        $studentId = $vars['id'] ?? null;
        if (!$studentId) { $this->handleDbErrorAndRedirect("Invalid Student ID for update.", '/admin/students'); return; }

        // --- Data Validation & Retrieval ---
        // Ensure all needed variables from $_POST are retrieved here
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $middleName = trim($_POST['middle_name'] ?? '');
        $dob = $_POST['date_of_birth'] ?? null;
        $gender = $_POST['gender'] ?? null;
        $email = trim($_POST['email'] ?? '');
        $admissionDate = $_POST['admission_date'] ?? null; // If editable
        $classId = filter_input(INPUT_POST, 'current_class_id', FILTER_VALIDATE_INT);
        $sessionId = filter_input(INPUT_POST, 'current_session_id', FILTER_VALIDATE_INT);
        $section = trim($_POST['section'] ?? '');
        $status = $_POST['status'] ?? 'inactive';
        $linkedUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT) ?: null;
        $password = $_POST['password'] ?? ''; // Get password field only if changing

        $errors = [];
        if (empty($firstName))
            $errors[] = "First Name is required.";
        if (empty($lastName))
            $errors[] = "Last Name is required.";
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { // <-- Add email validation
            $errors[] = "Please provide a valid Email address (or leave blank).";
        }
        // if (empty($admissionDate)) $errors[] = "Admission Date is required."; // If editable
        if (empty($classId))
            $errors[] = "Current Class is required.";
        if (empty($sessionId))
            $errors[] = "Current Session is required.";
        if (!in_array($status, ['active', 'inactive', 'graduated', 'transferred_out']))
            $status = 'inactive';
        // Add more validation...
        // --- End Basic Validation ---

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $this->redirect('/admin/students/edit/' . $studentId);
            return;
        }

        // --- Database Update ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) {
            $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/students');
            return;
        }

        try {
            // Build the base SQL UPDATE statement
            // Ensure all columns match the $paramsUser array below (except conditional password)
            $sqlUser = "UPDATE students SET
                        user_id = :user_id,
                        first_name = :fname,
                        last_name = :lname,
                        middle_name = :mname,
                        date_of_birth = :dob,
                        gender = :gender,
                        email = :email,
                        -- admission_date = :adm_date, -- Uncomment if admission_date is editable
                        current_class_id = :class_id,
                        current_session_id = :session_id,
                        section = :section,
                        status = :status,
                        updated_at = NOW()"; // Base update fields

            // Prepare the base parameters array
            // Ensure all keys match the placeholders in the base SQL above
            $paramsUser = [
                ':user_id' => $linkedUserId,
                ':fname' => $firstName,
                ':lname' => $lastName,
                ':mname' => $middleName ?: null,
                ':dob' => $dob ?: null,
                ':gender' => $gender ?: null,
                ':email' => $email ?: null,
                // ':adm_date' => $admissionDate, // Uncomment if admission_date is editable
                ':class_id' => $classId,
                ':session_id' => $sessionId,
                ':section' => $section ?: null,
                ':status' => $status,
                ':student_id' => $studentId // This is used in the WHERE clause
            ];

            // Conditionally add password update to SQL and params
            if (!empty($password)) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sqlUser .= ", password_hash = :password_hash"; // Add to SQL string
                $paramsUser[':password_hash'] = $passwordHash; // Add to parameters array
            }

            // Add the WHERE clause AFTER potential password addition
            $sqlUser .= " WHERE student_id = :student_id";

            // Prepare the final SQL statement
            $stmtUser = $pdo->prepare($sqlUser);

            // Execute with the final parameters array
            if ($stmtUser->execute($paramsUser)) { // Execute uses the $paramsUser array
                 $_SESSION['flash_success'] = "Student details updated successfully!";
             } else {
                 $_SESSION['flash_error'] = "Failed to update student details.";
             }

        } catch (\PDOException $e) {
             error_log("Student Update Error: " . $e->getMessage()); // Log the detailed error
             // Provide a more specific error if possible, otherwise generic
             if ($e->getCode() == '23000') { // Duplicate entry like unique email?
                $this->handleDbErrorAndRedirect("Update failed: Email might already exist for another user.", '/admin/students/edit/' . $studentId);
             } else if ($e->getCode() == 'HY093'){ // Parameter number error
                 $this->handleDbErrorAndRedirect("Update failed: Parameter mismatch error (HY093). Please check code.", '/admin/students/edit/' . $studentId);
             }
             else {
                 $this->handleDbErrorAndRedirect("Database error updating student.", '/admin/students');
             }
             return;
        }
        // --- End Database Update ---

        $this->redirect('/admin/students');
    }

    /**
     * Remove the specified student from storage. (Handles POST /admin/students/delete/{id})
     */
    public function delete(array $vars): void {
        // --- Access Control & Method Check ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; } // Maybe only Admin for delete?
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/students'); return; }
        // --- End Checks ---

        $studentId = $vars['id'] ?? null;
        if (!$studentId) { $this->handleDbErrorAndRedirect("Invalid Student ID for deletion.", '/admin/students'); return; }

        // --- Database Deletion ---
        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/students'); return; }

        // ** CRUCIAL CONSIDERATION: Deleting Students with Financial Records **
        // Our schema uses ON DELETE RESTRICT for fee_invoices.student_id and payments.student_id.
        // This means the database will PREVENT deleting a student if they have associated invoices or payments.
        // This is generally the desired behaviour to maintain financial history integrity.
        // We should try the delete and catch the specific error, or perform checks first.

        try {
            // Attempt hard delete (database FK constraints will prevent if linked records exist)
            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = :id");
            $stmt->execute([':id' => $studentId]);

            if ($stmt->rowCount() > 0) {
                // Optional: Also delete the associated user account if one exists?
                // $userStmt = $pdo->prepare("DELETE FROM users WHERE user_id = (SELECT user_id FROM students WHERE student_id = :id LIMIT 1)");
                // $userStmt->execute([':id' => $studentId]); // Be careful with this!

                $_SESSION['flash_success'] = "Student deleted successfully!";
            } else {
                $_SESSION['flash_error'] = "Student not found or already deleted.";
            }

        } catch (\PDOException $e) {
             error_log("Student Delete Error: " . $e->getMessage());
             // Check for foreign key constraint violation (error code 1451 often indicates this in MySQL)
             if ($e->getCode() == '23000' || strpos(strtolower($e->getMessage()), 'foreign key constraint fails') !== false) {
                  $this->handleDbErrorAndRedirect("Cannot delete student: They have associated financial records (invoices/payments). Consider making the student 'Inactive' instead.", '/admin/students');
             } else {
                  $this->handleDbErrorAndRedirect("Database error deleting student.", '/admin/students');
             }
             return;
        }
        // --- End Database Deletion ---

        $this->redirect('/admin/students');
    }

    private function getUsersByRole(string $roleName): array {
        $pdo = DbConnection::getInstance();
        if (!$pdo) return [];
        try {
            $sql = "SELECT u.user_id, CONCAT(u.full_name, ' (', u.username, ')') AS display_name
                    FROM users u
                    JOIN user_roles ur ON u.user_id = ur.user_id
                    JOIN roles r ON ur.role_id = r.role_id
                    WHERE r.role_name = :roleName AND u.status = 'active'
                    ORDER BY u.full_name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':roleName' => $roleName]);
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            error_log("Get Users by Role Error ({$roleName}): " . $e->getMessage());
            return [];
        }
    }

     /** Helper to fetch users suitable for linking as student login */
    private function getLinkableStudentUsers(?int $currentStudentId = null): array
    {
        $pdo = DbConnection::getInstance();
        if (!$pdo)
            return [];
        try {
            // Select active users with 'Student' role OR no role yet,
            // who are not currently linked to any *other* student
            $sql = "SELECT u.user_id, CONCAT(u.full_name, ' (', u.username, ')') AS display_name
                    FROM users u
                    LEFT JOIN students s ON u.user_id = s.user_id
                    LEFT JOIN user_roles ur ON u.user_id = ur.user_id
                    LEFT JOIN roles r ON ur.role_id = r.role_id
                    WHERE u.status = 'active'
                      AND (s.student_id IS NULL OR s.student_id = :current_student_id) -- Not linked OR linked to current student being edited
                      AND (r.role_name = 'Student' OR r.role_name IS NULL) -- Has Student role or no role assigned yet
                    GROUP BY u.user_id -- Ensure unique users if they somehow have multiple roles listed via bad data
                    ORDER BY u.full_name ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':current_student_id', $currentStudentId, $currentStudentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            error_log("Get Linkable Student Users Error: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Link a guardian/parent user to a student.
     * Handles POST /admin/students/{id}/add-guardian
     */
    public function addGuardianLink(array $vars): void {
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/students'); return; }

        $studentId = $vars['id'] ?? null;
        $guardianUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT); // The parent/guardian user ID to link
        $relationship = trim($_POST['relationship_type'] ?? '');

        if (!$studentId || !$guardianUserId) {
             $this->handleDbErrorAndRedirect("Missing student or guardian ID.", '/admin/students'); // Redirect to list might be better
             return;
        }

         // TODO: Add validation - does student exist? does user exist and have 'Parent' role?

         $pdo = DbConnection::getInstance();
         if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/students/edit/' . $studentId); return; }

         try {
            $sql = "INSERT INTO student_guardian_links (student_id, user_id, relationship_type, created_at)
                    VALUES (:student_id, :user_id, :relationship, NOW())";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([
                ':student_id' => $studentId,
                ':user_id' => $guardianUserId,
                ':relationship' => $relationship ?: null
                ])) {
                 $_SESSION['flash_success'] = "Guardian linked successfully.";
            } else {
                 $_SESSION['flash_error'] = "Failed to link guardian.";
            }
         } catch (\PDOException $e) {
              if ($e->getCode() == 23000) { // Unique constraint violation
                 $_SESSION['flash_error'] = "This guardian is already linked to this student.";
              } else {
                 error_log("Add Guardian Link Error: " . $e->getMessage());
                 $_SESSION['flash_error'] = "Database error linking guardian.";
              }
         }
         $this->redirect('/admin/students/edit/' . $studentId); // Redirect back to student edit page
    }

    /**
     * Remove a guardian/parent link from a student.
     * Handles POST /admin/students/remove-guardian (expects link_id and student_id in POST)
     */
    public function removeGuardianLink(): void {
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) { $this->redirect('/dashboard'); return; }
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/students'); return; }

         $linkId = filter_input(INPUT_POST, 'link_id', FILTER_VALIDATE_INT);
         $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT); // Needed for redirect

         if (!$linkId || !$studentId) {
              $this->handleDbErrorAndRedirect("Invalid link or student ID for removal.", '/admin/students');
              return;
         }

         $pdo = DbConnection::getInstance();
         if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/students/edit/' . $studentId); return; }

         try {
            $sql = "DELETE FROM student_guardian_links WHERE link_id = :link_id";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([':link_id' => $linkId])) {
                if ($stmt->rowCount() > 0) {
                     $_SESSION['flash_success'] = "Guardian link removed successfully.";
                } else {
                     $_SESSION['flash_error'] = "Link not found or already removed.";
                }
            } else {
                 $_SESSION['flash_error'] = "Failed to remove guardian link.";
            }
         } catch (\PDOException $e) {
             error_log("Remove Guardian Link Error: " . $e->getMessage());
             $_SESSION['flash_error'] = "Database error removing link.";
         }
          $this->redirect('/admin/students/edit/' . $studentId); // Redirect back to student edit page
    }

    // ... (keep existing private helper methods) ...
}