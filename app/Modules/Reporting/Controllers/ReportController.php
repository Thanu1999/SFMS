<?php
namespace App\Modules\Reporting\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use App\Core\Services\NotificationService;
use App\Core\Helpers\SettingsHelper;
use PDO;
use DateTime; // For date handling

class ReportController extends BaseController {
    
    private NotificationService $notificationService; // <-- Add property
    public function __construct() {
        $this->notificationService = new NotificationService(); // Instantiate service
    }

    /**
     * Display the main reports menu page. (Handles GET /admin/reports)
     */
    public function index(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot view reports.";
            $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Read and Unset Flash Messages ---
        // Read session variables into local variables first
        $flash_success = $_SESSION['flash_success'] ?? null;
        $flash_error = $_SESSION['flash_error'] ?? null;

        // Unset them immediately after reading
        unset($_SESSION['flash_success']);
        unset($_SESSION['flash_error']);
        // --- End Flash Message Handling ---

        // --- Determine if current user is Admin ---
        $isAdmin = $this->hasRole('Admin');
        // --- End Check ---

        // Load the simple view listing available reports
        // Pass the flash messages along with other data
        $this->loadView('Reporting/Views/index', [ // <-- Path to View
            'pageTitle' => 'Reports',
            'isAdmin' => $isAdmin
            // No need to pass global flash messages here
        ], 'layout_admin');
    }

    /**
     * Show/Generate the Fee Collection Report based on date filters.
     * Handles GET /admin/reports/fee-collection
     */
    public function showFeeCollectionReport(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Get and Validate Date Filters ---
        // Default to current month
        $today = new DateTime();
        $defaultStartDate = $today->format('Y-m-01');
        $defaultEndDate = $today->format('Y-m-t'); // 't' gets the last day of the month

        $startDate = trim($_GET['start_date'] ?? $defaultStartDate);
        $endDate = trim($_GET['end_date'] ?? $defaultEndDate);
        $errors = [];

        // Basic validation (could be more robust)
        $startDateTime = DateTime::createFromFormat('Y-m-d', $startDate);
        $endDateTime = DateTime::createFromFormat('Y-m-d', $endDate);

        if (!$startDateTime || $startDateTime->format('Y-m-d') !== $startDate) {
            $errors[] = "Invalid Start Date format (use YYYY-MM-DD). Using default.";
            $startDate = $defaultStartDate;
        }
        if (!$endDateTime || $endDateTime->format('Y-m-d') !== $endDate) {
             $errors[] = "Invalid End Date format (use YYYY-MM-DD). Using default.";
             $endDate = $defaultEndDate;
        }
        if ($startDateTime && $endDateTime && $startDateTime > $endDateTime) {
             $errors[] = "Start Date cannot be after End Date. Using defaults.";
             $startDate = $defaultStartDate;
             $endDate = $defaultEndDate;
        }
        if (!empty($errors)) {
             $_SESSION['flash_error'] = implode('<br>', $errors);
             // Allow proceeding with default dates
        }
        // --- End Date Filters ---

        $payments = [];
        $totalCollected = 0;
        $dbError = null;
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $dbError = "Database connection failed.";
        } else {
            try {
                // Fetch 'completed' payments within the date range, joining relevant details
                // Note: We filter by p.payment_date here
                $sql = "SELECT
                            p.payment_id, p.payment_date, p.amount_paid, p.receipt_number,
                            pm.method_name,
                            s.first_name, s.last_name,
                            fi.invoice_number -- Get associated invoice number via allocation
                        FROM payments p
                        JOIN payment_methods pm ON p.method_id = pm.method_id
                        JOIN students s ON p.student_id = s.student_id
                        LEFT JOIN payment_allocations pa ON p.payment_id = pa.payment_id -- Assuming one allocation per payment for now
                        LEFT JOIN fee_invoices fi ON pa.invoice_id = fi.invoice_id -- To get invoice #
                        WHERE p.payment_status = 'completed'
                          AND DATE(p.payment_date) BETWEEN :start_date AND :end_date
                        ORDER BY p.payment_date DESC, p.payment_id DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':start_date', $startDate);
                $stmt->bindParam(':end_date', $endDate);
                $stmt->execute();
                $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calculate total collected for the period
                $totalCollected = array_sum(array_column($payments, 'amount_paid'));

            } catch (\PDOException $e) {
                 $dbError = "Database query failed: " . $e->getMessage();
                 error_log("Fee Collection Report Error: " . $e->getMessage());
            }
        }

        // Load the view
        $this->loadView('Reporting/Views/fee_collection_report', [
            'pageTitle' => 'Fee Collection Report',
            'payments' => $payments,
            'totalCollected' => $totalCollected,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'viewError' => $dbError, // Use a consistent name like viewError
            'flash_error_validation' => $_SESSION['flash_error'] ?? null // Pass specific validation flash if needed
        ], 'layout_admin'); // <-- SPECIFY LAYOUT
         unset($_SESSION['flash_error']); // Clear flash message
    }

    /**
     * Show/Generate the Outstanding Dues Report based on filters.
     * Handles GET /admin/reports/outstanding-dues
     */
    public function showOutstandingDuesReport(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Get and Validate Filters ---
        $filterSessionId = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);
        $filterClassId = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        // TODO: Add validation logic if needed, maybe default to current active session?

        // Fetch data for filter dropdowns
        $formData = $this->getFilterFormData();

        $outstandingInvoices = [];
        $totalOutstanding = 0;
        $dbError = $formData['error']; // Get potential error from fetching filter data
        $pdo = DbConnection::getInstance();

        if (!$pdo && !$dbError) { // Check for DB error only if not already set
            $dbError = "Database connection failed.";
        } elseif ($pdo) { // Proceed only if DB connection ok and no prior error
            try {
                // Base query to find invoices with a balance > 0
                $sql = "SELECT
                            fi.invoice_id, fi.invoice_number, fi.issue_date, fi.due_date,
                            fi.total_payable, fi.total_paid, fi.status,
                            (fi.total_payable - fi.total_paid) AS balance_due,
                            s.student_id, s.first_name, s.last_name,
                            c.class_name,
                            acs.session_name
                        FROM fee_invoices fi
                        JOIN students s ON fi.student_id = s.student_id
                        JOIN academic_sessions acs ON fi.session_id = acs.session_id
                        LEFT JOIN classes c ON s.current_class_id = c.class_id
                        WHERE (fi.total_payable - fi.total_paid) > 0.001 -- Use tolerance for float
                          AND fi.status NOT IN ('paid', 'cancelled') "; // Exclude paid/cancelled

                $params = [];

                // Apply filters
                if ($filterSessionId) {
                    $sql .= " AND fi.session_id = :session_id ";
                    $params[':session_id'] = $filterSessionId;
                }
                if ($filterClassId) {
                    // Assuming student's *current* class determines filtering
                    $sql .= " AND s.current_class_id = :class_id ";
                    $params[':class_id'] = $filterClassId;
                }

                $sql .= " ORDER BY fi.due_date ASC, s.last_name ASC, s.first_name ASC"; // Order by due date

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $outstandingInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calculate total outstanding for the filtered results
                $totalOutstanding = array_sum(array_column($outstandingInvoices, 'balance_due'));

            } catch (\PDOException $e) {
                 $dbError = "Database query failed: " . $e->getMessage();
                 error_log("Outstanding Dues Report Error: " . $e->getMessage());
            }
        }

         // Load the view
         $this->loadView('Reporting/Views/outstanding_dues_report', [ // <-- Correct Path
            'pageTitle' => 'Outstanding Dues Report',
            'invoices' => $outstandingInvoices,
            'totalOutstanding' => $totalOutstanding,
            'sessions' => $formData['sessions'] ?? [], // Pass filter options
            'classes' => $formData['classes'] ?? [],
            'filterSessionId' => $filterSessionId,     // Pass current filter values back
            'filterClassId' => $filterClassId,
            'dbError' => $dbError, // Pass specific error
            'flash_error' => $_SESSION['flash_error'] ?? null // Pass specific validation flash if needed
        ], 'layout_admin'); // <-- CORRECT: layout_admin is specified
         unset($_SESSION['flash_error']);
    }

    /** Helper to fetch data needed for report filter forms */
    private function getFilterFormData(): array
    {
        $pdo = DbConnection::getInstance();
        $data = ['sessions' => [], 'classes' => [], 'error' => null];
        if (!$pdo) {
            $data['error'] = "Database connection failed while fetching filter data.";
            return $data;
        }
        try {
            // Fetch all sessions
            $data['sessions'] = $pdo->query("SELECT session_id, session_name FROM academic_sessions ORDER BY start_date DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
            // Fetch all classes
            $data['classes'] = $pdo->query("SELECT class_id, class_name FROM classes ORDER BY class_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\PDOException $e) {
            error_log("Get Report Filter Data Error: " . $e->getMessage());
            $data['error'] = "Database error fetching filter data.";
        }
        return $data;
    }

    /**
     * Show Student Ledger form and report.
     * Handles GET /admin/reports/student-ledger
     */
    public function showStudentLedger(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
            $this->redirect('/dashboard'); return;
        }
        // --- End Access Control ---

        $pdo = DbConnection::getInstance();
        if (!$pdo) {
            // Handle error - maybe redirect or load view with error
            $this->loadView('Reporting/Views/student_ledger_report', [
                'pageTitle' => 'Student Ledger Report',
                'viewError' => "Database connection failed."
            ], 'layout_admin');
            return;
        }

        // Fetch Students for Dropdown
        $allStudents = [];
        $viewError = null; // Initialize view-specific error
        try {
            $allStudents = $pdo->query("SELECT student_id, CONCAT(last_name, ', ', first_name, ' (', admission_number, ')') AS full_display FROM students ORDER BY last_name ASC, first_name ASC")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            error_log("Student Ledger - Fetch Students Error: " . $e->getMessage());
            $viewError = "Error fetching student list.";
        }

        // Process Selected Student
        $selectedStudentId = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
        $studentDetails = null;
        $ledgerEntries = [];

        if ($selectedStudentId && !$viewError) {
            try {
                // 1. Fetch Selected Student's Details
                $stmtStudent = $pdo->prepare("SELECT s.*, c.class_name FROM students s LEFT JOIN classes c ON s.current_class_id = c.class_id WHERE s.student_id = :id");
                $stmtStudent->execute([':id' => $selectedStudentId]);
                $studentDetails = $stmtStudent->fetch(PDO::FETCH_ASSOC);

                if (!$studentDetails) {
                    $_SESSION['flash_error'] = "Selected student not found."; // Use flash for action feedback
                    $selectedStudentId = null; // Clear selection if invalid
                } else {
                    // 2. Fetch Invoices (Charges)
                    $stmtInvoices = $pdo->prepare("SELECT invoice_id, invoice_number, issue_date, description, total_payable AS amount, created_at FROM fee_invoices WHERE student_id = :id AND status != 'cancelled' ORDER BY issue_date ASC, created_at ASC");
                    $stmtInvoices->execute([':id' => $selectedStudentId]);
                    $invoices = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

                    // 3. Fetch Payments (Credits)
                    $stmtPayments = $pdo->prepare("SELECT payment_id, payment_date, receipt_number, notes, amount_paid AS amount, pm.method_name, reference_number FROM payments p JOIN payment_methods pm ON p.method_id = pm.method_id WHERE p.student_id = :id AND p.payment_status = 'completed' ORDER BY payment_date ASC, payment_id ASC");
                    $stmtPayments->execute([':id' => $selectedStudentId]);
                    $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);


                    // 4. Combine and Sort
                    $combined = [];
                    // ... (Combine logic remains the same - populate $combined) ...
                    foreach ($invoices as $inv) {
                        $invoiceTimestamp = strtotime($inv['issue_date'] . ' ' . date('H:i:s', strtotime($inv['created_at'])));
                        $combined[] = ['timestamp' => $invoiceTimestamp, 'id' => $inv['invoice_id'], 'date' => $inv['issue_date'], 'type' => 'Invoice', 'ref' => $inv['invoice_number'], 'description' => $inv['description'] ?: ('Invoice #' . $inv['invoice_number']), 'charge' => (float) $inv['amount'], 'credit' => 0.00];
                    }
                    foreach ($payments as $pay) {
                        $paymentTimestamp = strtotime($pay['payment_date']);
                        $combined[] = ['timestamp' => $paymentTimestamp, 'id' => $pay['payment_id'], 'date' => $pay['payment_date'], 'type' => 'Payment', 'ref' => $pay['receipt_number'], 'description' => 'Payment Received (' . $pay['method_name'] . (($pay['reference_number'] ?? '') ? ' Ref:' . htmlspecialchars($pay['reference_number']) : '') . ')', 'charge' => 0.00, 'credit' => (float) $pay['amount']];
                    }
                    usort($combined, function($a, $b) { if ($a['timestamp'] == $b['timestamp']) { return $a['id'] <=> $b['id']; } return $a['timestamp'] <=> $b['timestamp']; });

                    // 5. Calculate Running Balance
                    $balance = 0.00;
                    foreach ($combined as $key => $entry) {
                        $balance += $entry['charge'];
                        $balance -= $entry['credit'];
                        $combined[$key]['balance'] = $balance;
                    }
                    // ** IMPORTANT: Remove the double array_reverse from your uploaded code **
                    // $combined = array_reverse($combined); // REMOVE THIS
                    // $combined = array_reverse($combined); // REMOVE THIS
                    $ledgerEntries = $combined;

                } // end if student found

            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Student Ledger Fetch Error: " . $e->getMessage());
            }
        } // end if selectedStudentId

        // --- Load View ---
        $this->loadView('Reporting/Views/student_ledger_report', [
            'pageTitle' => 'Student Ledger Report',
            'allStudents' => $allStudents,
            'selectedStudentId' => $selectedStudentId,
            'studentDetails' => $studentDetails,
            'ledgerEntries' => $ledgerEntries,
            'viewError' => $viewError // Pass specific errors for this view load
            // Global flash messages handled by layout
        ], 'layout_admin');
    }

    /**
     * Show/Generate the Defaulter List Report based on filters.
     * Handles GET /admin/reports/defaulters
     */
    public function showDefaulterReport(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Get and Validate Filters ---
        $filterSessionId = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT);
        $filterClassId = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
        $filterMinDaysOverdue = filter_input(INPUT_GET, 'min_days_overdue', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 0]]); // Default 1 day overdue
        $filterMinBalanceDue = filter_input(INPUT_GET, 'min_balance_due', FILTER_VALIDATE_FLOAT, ['options' => ['default' => 0.01]]); // Default min balance > 0

        // Fetch data for filter dropdowns
        $formData = $this->getFilterFormData();

        $defaulters = [];
        $totalOverdueAmount = 0;
        $viewError = $formData['error']; // Use viewError for consistency, get potential error from filter data fetch
        $pdo = DbConnection::getInstance();

        if (!$pdo && !$viewError) {
            $viewError = "Database connection failed.";
        } elseif ($pdo) {
            try {
                // Fetch overdue invoices (due date is in the past) with a balance > min_balance_due
                // Calculate days overdue
                $sql = "SELECT
                            fi.invoice_id, fi.invoice_number, fi.due_date,
                            fi.total_payable, fi.total_paid,
                            (fi.total_payable - fi.total_paid) AS balance_due,
                            DATEDIFF(CURDATE(), fi.due_date) AS days_overdue, -- Calculate days overdue
                            s.student_id, s.first_name, s.last_name, s.admission_number,
                            c.class_name,
                            acs.session_name
                        FROM fee_invoices fi
                        JOIN students s ON fi.student_id = s.student_id
                        JOIN academic_sessions acs ON fi.session_id = acs.session_id
                        LEFT JOIN classes c ON s.current_class_id = c.class_id
                        WHERE fi.status IN ('unpaid', 'partially_paid', 'overdue') -- Not paid or cancelled
                          AND fi.due_date < CURDATE() -- Due date is in the past
                          AND (fi.total_payable - fi.total_paid) >= :min_balance -- Balance meets minimum
                          AND DATEDIFF(CURDATE(), fi.due_date) >= :min_days -- Overdue by minimum days
                          ";

                $params = [
                    ':min_balance' => $filterMinBalanceDue,
                    ':min_days' => $filterMinDaysOverdue
                ];

                if ($filterSessionId) {
                    $sql .= " AND fi.session_id = :session_id ";
                    $params[':session_id'] = $filterSessionId;
                }
                if ($filterClassId) {
                    $sql .= " AND s.current_class_id = :class_id ";
                    $params[':class_id'] = $filterClassId;
                }
                $sql .= " ORDER BY days_overdue DESC, s.last_name ASC, s.first_name ASC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $defaulters = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $totalOverdueAmount = array_sum(array_column($defaulters, 'balance_due'));

            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Defaulter Report Error: " . $e->getMessage());
            }
        }

        $this->loadView('Reporting/Views/defaulter_report', [
            'pageTitle' => 'Defaulter List Report',
            'defaulters' => $defaulters,
            'totalOverdueAmount' => $totalOverdueAmount,
            'sessions' => $formData['sessions'] ?? [],
            'classes' => $formData['classes'] ?? [],
            'filterSessionId' => $filterSessionId,
            'filterClassId' => $filterClassId,
            'filterMinDaysOverdue' => $filterMinDaysOverdue,
            'filterMinBalanceDue' => $filterMinBalanceDue,
            'viewError' => $viewError // Pass specific view error
            // Global flash handled by layout
        ], 'layout_admin');

    }
    /**
     * Show/Generate the Fee Collection Summary Report based on filters and grouping.
     * Handles GET /admin/reports/fee-collection-summary
     */
    public function showFeeCollectionSummary(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Get and Validate Filters ---
        $today = new DateTime();
        $defaultStartDate = $today->format('Y-m-01');
        $defaultEndDate = $today->format('Y-m-t');

        $startDate = trim($_GET['start_date'] ?? $defaultStartDate);
        $endDate = trim($_GET['end_date'] ?? $defaultEndDate);
        $groupBy = trim($_GET['group_by'] ?? 'method'); // Default grouping: method, class, category
        $allowedGroupBy = ['method', 'class', 'category'];

        $errors = [];
        $startDateTime = DateTime::createFromFormat('Y-m-d', $startDate);
        $endDateTime = DateTime::createFromFormat('Y-m-d', $endDate);

        if (!$startDateTime || $startDateTime->format('Y-m-d') !== $startDate) { $errors[] = "Invalid Start Date format."; $startDate = $defaultStartDate; }
        if (!$endDateTime || $endDateTime->format('Y-m-d') !== $endDate) { $errors[] = "Invalid End Date format."; $endDate = $defaultEndDate; }
        if ($startDateTime && $endDateTime && $startDateTime > $endDateTime) { $errors[] = "Start Date cannot be after End Date."; $startDate = $defaultStartDate; $endDate = $defaultEndDate; }
        if (!in_array($groupBy, $allowedGroupBy)) { $groupBy = 'method'; } // Default if invalid group_by is passed
        // --- End Filters ---


        $summaryData = [];
        $grandTotal = 0;
        $dbError = null;
        $pdo = DbConnection::getInstance();
        $groupingLabel = "Group"; // Default label

        if (!$pdo) {
            $dbError = "Database connection failed.";
        } else {
            try {
                // --- Build SQL based on Grouping ---
                $selectField = "";
                $joinClause = "";
                $groupByField = "";
                $orderByField = "";

                switch ($groupBy) {
                    case 'class':
                        $selectField = "COALESCE(c.class_name, 'N/A') AS group_name";
                        $joinClause = " JOIN students s ON p.student_id = s.student_id LEFT JOIN classes c ON s.current_class_id = c.class_id ";
                        $groupByField = "group_name"; // Group by the alias
                        $orderByField = "group_name";
                        $groupingLabel = "Class";
                        break;

                    case 'category':
                        // To group by category, we need to sum allocated amounts linked via invoice items
                        $selectField = "COALESCE(fc.category_name, 'Unallocated/Other') AS group_name";
                        // Joining through allocation -> invoice -> invoice_items -> categories
                        $joinClause = " LEFT JOIN payment_allocations pa ON p.payment_id = pa.payment_id
                                        LEFT JOIN fee_invoice_items fii ON pa.invoice_item_id = fii.item_id OR pa.invoice_id = fii.invoice_id -- Simplistic join, might need refinement if multi-items
                                        LEFT JOIN fee_categories fc ON fii.category_id = fc.category_id ";
                        $groupByField = "group_name";
                        $orderByField = "group_name";
                        $groupingLabel = "Fee Category";
                        break;

                    case 'method':
                    default:
                        $selectField = "pm.method_name AS group_name";
                        $joinClause = " JOIN payment_methods pm ON p.method_id = pm.method_id ";
                        $groupByField = "pm.method_name";
                        $orderByField = "pm.method_name";
                        $groupingLabel = "Payment Method";
                        break;
                }

                $sql = "SELECT
                            {$selectField},
                            SUM(p.amount_paid) AS total_amount
                        FROM payments p
                        {$joinClause}
                        WHERE p.payment_status = 'completed'
                          AND DATE(p.payment_date) BETWEEN :start_date AND :end_date
                        GROUP BY {$groupByField}
                        ORDER BY {$orderByField} ASC";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':start_date', $startDate);
                $stmt->bindParam(':end_date', $endDate);
                $stmt->execute();
                $summaryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calculate grand total
                $grandTotal = array_sum(array_column($summaryData, 'total_amount'));

            } catch (\PDOException $e) {
                 $dbError = "Database query failed: " . $e->getMessage();
                 error_log("Fee Collection Summary Report Error: " . $e->getMessage());
            }
        }

        // Pass validation errors (if any) via flash session
        if (!empty($errors)) { $_SESSION['flash_error'] = implode('<br>', $errors); }

         // Load the view
         $this->loadView('Reporting/Views/fee_collection_summary_report', [ // <-- Correct Path
            'pageTitle' => 'Fee Collection Summary',
            'summaryData' => $summaryData,
            'grandTotal' => $grandTotal,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'groupBy' => $groupBy,
            'groupingLabel' => $groupingLabel,
            'dbError' => $dbError, // Use dbError or rename to viewError
            'flash_error' => $_SESSION['flash_error'] ?? null // Pass specific validation errors if any
        ], 'layout_admin'); // <-- CORRECT: layout_admin is specified
         unset($_SESSION['flash_error']);
    }
    /**
     * Show the Audit Log Report based on filters.
     * Handles GET /admin/reports/audit-log
     */
    public function showAuditLogReport(): void
    {
        // --- Access Control - STRICTLY ADMIN ---
        if (!$this->hasRole('Admin')) {
            $_SESSION['flash_error'] = "Access Denied: You do not have permission to view the audit log.";
            // Redirect to admin dashboard if they are staff, otherwise logout
            $this->redirect(($this->hasRole('Staff')) ? '/admin/dashboard' : '/logout');
            return;
        }
        // --- End Access Control ---

        // --- Filters ---
        $today = new DateTime();
        $defaultEndDate = $today->format('Y-m-d');
        $defaultStartDate = $today->modify('-7 days')->format('Y-m-d');

        $filterStartDate = trim($_GET['start_date'] ?? $defaultStartDate);
        $filterEndDate = trim($_GET['end_date'] ?? $defaultEndDate);
        $filterUserId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
        $filterActionType = trim($_GET['action_type'] ?? '');
        // Add date validation...
        $startDateTime = DateTime::createFromFormat('Y-m-d', $filterStartDate);
        $endDateTime = DateTime::createFromFormat('Y-m-d', $filterEndDate);
        if (!$startDateTime || $startDateTime->format('Y-m-d') !== $filterStartDate)
            $filterStartDate = $defaultStartDate;
        if (!$endDateTime || $endDateTime->format('Y-m-d') !== $filterEndDate)
            $filterEndDate = $defaultEndDate;
        if ($startDateTime && $endDateTime && $startDateTime > $endDateTime) {
            $filterStartDate = $defaultStartDate;
            $filterEndDate = $defaultEndDate;
        }
        // --- End Filters ---

        // --- Pagination ---
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 50; // Logs per page
        $offset = ($page - 1) * $limit;
        // --- End Pagination ---

        $logs = [];
        $totalRecords = 0;
        $allUsers = [];
        $viewError = null; // Use viewError
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                // Fetch users for filter dropdown (remains same)
                $allUsers = $pdo->query("SELECT user_id, username FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_KEY_PAIR);

                // Build query with filters (remains same)
                $sqlBase = " FROM audit_logs al LEFT JOIN users u ON al.user_id = u.user_id ";
                $whereClauses = [];
                $params = [];
                $whereClauses[] = "al.created_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filterStartDate . ' 00:00:00';
                $params[':end_date'] = $filterEndDate . ' 23:59:59';
                if ($filterUserId) {
                    $whereClauses[] = "al.user_id = :user_id";
                    $params[':user_id'] = $filterUserId;
                }
                if (!empty($filterActionType)) {
                    $whereClauses[] = "al.action_type LIKE :action_type";
                    $params[':action_type'] = '%' . $filterActionType . '%';
                }
                $sqlWhere = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

                // Get Total Count (remains same)
                $sqlCount = "SELECT COUNT(*) " . $sqlBase . $sqlWhere;
                $stmtCount = $pdo->prepare($sqlCount);
                $stmtCount->execute($params);
                $totalRecords = $stmtCount->fetchColumn();

                // Fetch Paginated Log Data (remains same)
                $sqlSelect = "SELECT al.*, u.username ";
                $sqlOrderLimit = " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset ";
                $sql = $sqlSelect . $sqlBase . $sqlWhere . $sqlOrderLimit;
                $stmt = $pdo->prepare($sql);
                $executeParams = $params;
                $executeParams[':limit'] = $limit;
                $executeParams[':offset'] = $offset;
                foreach ($executeParams as $key => &$val) { /* ... explicit binding ... */
                    $paramType = PDO::PARAM_STR;
                    if ($key === ':limit' || $key === ':offset')
                        $paramType = PDO::PARAM_INT;
                    elseif (is_int($val))
                        $paramType = PDO::PARAM_INT;
                    elseif (is_bool($val))
                        $paramType = PDO::PARAM_BOOL;
                    elseif (is_null($val))
                        $paramType = PDO::PARAM_NULL;
                    $stmt->bindValue($key, $val, $paramType);
                }
                unset($val);
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Audit Log Report Error: " . $e->getMessage());
            }
        }

        // --- Load View --- Use layout_admin
        $this->loadView('Reporting/Views/audit_log_report', [
            'pageTitle' => 'Audit Log Report',
            'logs' => $logs,
            'allUsers' => $allUsers,
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
            'filterUserId' => $filterUserId,
            'filterActionType' => $filterActionType,
            'currentPage' => $page,
            'totalPages' => ceil($totalRecords / $limit),
            'totalRecords' => $totalRecords,
            'viewError' => $viewError // Pass specific view error
            // Global flash messages handled by layout
        ], 'layout_admin'); // <-- SPECIFY LAYOUT
    }
    /**
     * Show/Generate the Accounts Receivable Ageing Report.
     * Handles GET /admin/reports/ageing
     */
    public function showAgeingReport(): void
    {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
            $this->redirect('/dashboard');
            return;
        }
        // --- End Access Control ---

        // --- Get Filters ---
        $asOfDateStr = trim($_GET['as_of_date'] ?? date('Y-m-d'));
        $asOfDate = DateTime::createFromFormat('Y-m-d', $asOfDateStr);
        $flashErrorReport = null; // Specific flash message for this action
        if (!$asOfDate || $asOfDate->format('Y-m-d') !== $asOfDateStr) {
            $asOfDateStr = date('Y-m-d');
            $asOfDate = new DateTime();
            $flashErrorReport = "Invalid 'As Of' date provided, defaulting to today."; // Use local variable for feedback
        }
        // --- End Filters ---


        $ageingSummary = [ /* ... initialize buckets ... */
            'current' => 0.00, '1-30' => 0.00, '31-60' => 0.00, '61-90' => 0.00, '90+' => 0.00,
        ];
        $totalOutstanding = 0;
        $viewError = null; // Specific DB error variable
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                // Fetch all invoices that are not fully paid or cancelled
                $sql = "SELECT
                           fi.due_date,
                           (fi.total_payable - fi.total_paid) AS balance_due
                       FROM fee_invoices fi
                       WHERE fi.status NOT IN ('paid', 'cancelled')
                         AND (fi.total_payable - fi.total_paid) > 0.001 "; // Balance > 0

                // Add Session/Class filters here if implemented later based on fi.session_id or student join

                $stmt = $pdo->prepare($sql);
                // Bind session/class params here if filtering
                $stmt->execute();
                $outstandingInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // --- Categorize into Ageing Buckets ---
                $asOfTimestamp = $asOfDate->getTimestamp();

                foreach ($outstandingInvoices as $invoice) {
                    $balance = (float) $invoice['balance_due'];
                    $totalOutstanding += $balance;
                    $dueDateTimestamp = strtotime($invoice['due_date']);

                    if ($dueDateTimestamp > $asOfTimestamp) {
                        // Due date is in the future relative to 'as of' date
                        $ageingSummary['current'] += $balance;
                    } else {
                        // Due date is past or equal to 'as of' date
                        // Calculate days overdue relative to 'as of' date
                        $daysOverdue = floor(($asOfTimestamp - $dueDateTimestamp) / (60 * 60 * 24));

                        if ($daysOverdue <= 30) {
                            $ageingSummary['1-30'] += $balance;
                        } elseif ($daysOverdue <= 60) {
                            $ageingSummary['31-60'] += $balance;
                        } elseif ($daysOverdue <= 90) {
                            $ageingSummary['61-90'] += $balance;
                        } else {
                            $ageingSummary['90+'] += $balance;
                        }
                    }
                }
                // --- End Categorization ---

            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Ageing Report Error: " . $e->getMessage());
            }
        }

        // Use layout_admin, pass summary data, total, filter values, and errors
        $this->loadView('Reporting/Views/ageing_report', [
            'pageTitle' => 'Outstanding Fees Ageing Report',
            'ageingSummary' => $ageingSummary,
            'totalOutstanding' => $totalOutstanding,
            'asOfDate' => $asOfDateStr,
            'viewError' => $viewError, // Pass specific view error
            'flash_error' => $flashErrorReport // Pass date validation error if any
            // Global flash messages handled by layout
        ], 'layout_admin');
    }

    /**
     * Manually triggers the sending of fee reminders.
     * Handles POST /admin/reports/trigger-reminders
     */
    public function triggerReminders(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot trigger reminders.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Configuration ---
        $daysBeforeDueDate = (int) SettingsHelper::get('reminder_days_before_due', 7);
        $daysAfterDueDate = (int) SettingsHelper::get('reminder_days_after_due', 3);
        $reminderCooldownDays = (int) SettingsHelper::get('reminder_cooldown_days', 3);
        $currencySymbol = SettingsHelper::get('currency_symbol', 'Rs.');
        $schoolName = SettingsHelper::get('school_name', 'Your School Name'); // Use setting
        // --- End Configuration ---

        $pdo = DbConnection::getInstance();
        if (!$pdo) { $this->handleDbErrorAndRedirect("Database connection failed.", '/admin/reports'); return; }

        $remindersSent = 0;
        $remindersFailed = 0;
        $errorsOccurred = 0;
        $logMessages = [];
        $nowTimestamp = date('Y-m-d H:i:s');
        $cooldownDate = date('Y-m-d H:i:s', strtotime("-{$reminderCooldownDays} days"));

        // --- Logic for DUE SOON Reminders ---
        try {
            $targetDueDate = date('Y-m-d', strtotime("+{$daysBeforeDueDate} days"));
            $logMessages[] = "Checking for invoices due soon on: {$targetDueDate}... (Cooldown: >{$reminderCooldownDays} days)";

            // ADDED check for last_reminder_sent_at
            $sqlDueSoon = "SELECT fi.invoice_id, fi.invoice_number, fi.due_date, fi.total_payable, fi.total_paid, s.student_id, s.first_name, s.last_name, s.email AS student_email, s.user_id AS student_user_id, u.email AS parent_email, sgl.user_id AS parent_user_id
                           FROM fee_invoices fi
                           JOIN students s ON fi.student_id = s.student_id
                           LEFT JOIN student_guardian_links sgl ON s.student_id = sgl.student_id
                           LEFT JOIN users u ON sgl.user_id = u.user_id
                           LEFT JOIN users stu_u ON s.user_id = stu_u.user_id
                           WHERE fi.status IN ('unpaid', 'partially_paid', 'overdue')
                             AND fi.due_date = :target_due_date
                             AND (fi.last_reminder_sent_at IS NULL OR fi.last_reminder_sent_at < :cooldown_date) -- Check cooldown
                           GROUP BY fi.invoice_id";
            $stmtDueSoon = $pdo->prepare($sqlDueSoon);
            $stmtDueSoon->execute([
                ':target_due_date' => $targetDueDate,
                ':cooldown_date' => $cooldownDate
            ]);

            while ($invoice = $stmtDueSoon->fetch(PDO::FETCH_ASSOC)) {
                $logMessages[] = "  Found Due Soon Invoice: #{$invoice['invoice_number']} for Student ID: {$invoice['student_id']}. Attempting notification...";
                $balanceDue = (float)$invoice['total_payable'] - (float)$invoice['total_paid'];

                // Refined Recipient Logic (Prioritize Parent > Student User > Student Direct Email)
                $recipientUserId = $invoice['parent_user_id'] ?: $invoice['student_user_id']; // Use Parent ID first if available
                $recipientEmail = $invoice['parent_email'] ?: $invoice['student_user_email'] ?: $invoice['student_email'] ?: null;
                $recipientDetail = (!$recipientUserId && $recipientEmail) ? $recipientEmail : null;

                if (!$recipientEmail) { // Check if we have *any* email
                     $logMessages[] = "    Skipped: No email found (Parent/Student User/Student Record).";
                     continue;
                }

                $data = [ /* ... prepare data array like before ... */
                    'student_name' => $invoice['first_name'] . ' ' . $invoice['last_name'],
                    'invoice_number' => $invoice['invoice_number'],
                    'due_date' => date('M d, Y', strtotime($invoice['due_date'])),
                    'balance_due' => number_format($balanceDue, 2),
                    'currency_symbol' => $currencySymbol,
                    'school_name' => $schoolName
                ];

                // Call the service - it now attempts email sending
                if ($this->notificationService->sendNotification($recipientUserId, 'FEE_DUE_SOON', $data, $recipientDetail, $invoice['invoice_id'], 'invoice')) {
                    // --- UPDATE last_reminder_sent_at on success ---
                    try {
                       $stmtUpdate = $pdo->prepare("UPDATE fee_invoices SET last_reminder_sent_at = :now WHERE invoice_id = :id");
                       $stmtUpdate->execute([':now' => $nowTimestamp, ':id' => $invoice['invoice_id']]);
                       $logMessages[] = "    SUCCESS: Notification logged/sent for Invoice #{$invoice['invoice_number']} to {$recipientEmail}. Updated reminder timestamp.";
                       $remindersSent++;
                    } catch (PDOException $updateE) {
                        error_log("Failed to update last_reminder_sent_at for invoice {$invoice['invoice_id']}: " . $updateE->getMessage());
                        // Still count as sent/logged, but log this failure
                        $logMessages[] = "    WARNING: Notification logged/sent for Invoice #{$invoice['invoice_number']} but FAILED to update reminder timestamp.";
                        $remindersSent++; // Count notification success even if timestamp update failed
                        $errorsOccurred++;
                    }
                    // --- END UPDATE ---
                } else {
                    $logMessages[] = "    FAILURE: Notification logging/sending failed for Invoice #{$invoice['invoice_number']} to {$recipientEmail}. Check logs.";
                    $remindersFailed++; // Increment specific failure count
                }
            }
             $logMessages[] = "Due Soon Check Complete.";

        } catch (\PDOException $e) {
             $logMessages[] = "ERROR checking due soon invoices: " . $e->getMessage();
             error_log("Trigger Reminders (Due Soon) Error: " . $e->getMessage());
             $errorsOccurred++;
        }
        // --- End DUE SOON Logic ---


        // --- Logic for OVERDUE Reminders ---
        try {
            $targetOverdueDate = date('Y-m-d', strtotime("-{$daysAfterDueDate} days"));
             $logMessages[] = "Checking for invoices overdue since: {$targetOverdueDate}... (Cooldown: >{$reminderCooldownDays} days)";

            // ADDED check for last_reminder_sent_at
            $sqlOverdue = "SELECT fi.invoice_id, fi.invoice_number, fi.due_date, fi.total_payable, fi.total_paid, s.student_id, s.first_name, s.last_name, s.email AS student_email, s.user_id AS student_user_id, u.email AS parent_email, sgl.user_id AS parent_user_id
                           FROM fee_invoices fi
                           JOIN students s ON fi.student_id = s.student_id
                           LEFT JOIN student_guardian_links sgl ON s.student_id = sgl.student_id
                           LEFT JOIN users u ON sgl.user_id = u.user_id
                           LEFT JOIN users stu_u ON s.user_id = stu_u.user_id
                           WHERE fi.status IN ('unpaid', 'partially_paid', 'overdue')
                             AND fi.due_date <= :target_overdue_date -- Check if due ON or BEFORE the target past date
                             AND (fi.last_reminder_sent_at IS NULL OR fi.last_reminder_sent_at < :cooldown_date) -- Check cooldown
                           GROUP BY fi.invoice_id";
             $stmtOverdue = $pdo->prepare($sqlOverdue);
             $stmtOverdue->execute([
                 ':target_overdue_date' => $targetOverdueDate, // Maybe adjust date check logic (e.g., >= X days overdue)
                 ':cooldown_date' => $cooldownDate
            ]);

            while ($invoice = $stmtOverdue->fetch(PDO::FETCH_ASSOC)) {
                $logMessages[] = "  Found Overdue Invoice: #{$invoice['invoice_number']} for Student ID: {$invoice['student_id']}. Attempting notification...";
                // ... (Determine recipient logic) ...
                $recipientUserId = $invoice['parent_user_id'] ?: $invoice['student_user_id'];
                $recipientEmail = $invoice['parent_email'] ?: $invoice['student_user_email'] ?: $invoice['student_email'] ?: null;
                $recipientDetail = (!$recipientUserId && $recipientEmail) ? $recipientEmail : null;

                if (!$recipientEmail) {
                     $logMessages[] = "    Skipped: No email found.";
                     continue;
                }

                $data = [ /* ... prepare data array ... */
                    'student_name' => $invoice['first_name'] . ' ' . $invoice['last_name'],
                    'invoice_number' => $invoice['invoice_number'],
                    'due_date' => date('M d, Y', strtotime($invoice['due_date'])),
                    'balance_due' => number_format($balanceDue, 2),
                    'currency_symbol' => $currencySymbol,
                    'school_name' => $schoolName
                ];

                if ($this->notificationService->sendNotification($recipientUserId, 'FEE_OVERDUE', $data, $recipientDetail, $invoice['invoice_id'], 'invoice')) {
                    // --- UPDATE last_reminder_sent_at on success ---
                    try {
                       $stmtUpdate = $pdo->prepare("UPDATE fee_invoices SET last_reminder_sent_at = :now WHERE invoice_id = :id");
                       $stmtUpdate->execute([':now' => $nowTimestamp, ':id' => $invoice['invoice_id']]);
                       $logMessages[] = "    SUCCESS: Notification logged/sent for Invoice #{$invoice['invoice_number']} to {$recipientEmail}. Updated reminder timestamp.";
                       $remindersSent++;
                    } catch (PDOException $updateE) {
                        error_log("Failed to update last_reminder_sent_at for invoice {$invoice['invoice_id']}: " . $updateE->getMessage());
                        $logMessages[] = "    WARNING: Notification logged/sent for Invoice #{$invoice['invoice_number']} but FAILED to update reminder timestamp.";
                        $remindersSent++;
                        $errorsOccurred++;
                    }
                    // --- END UPDATE ---
                } else {
                     $logMessages[] = "    FAILURE: Notification logging/sending failed for Invoice #{$invoice['invoice_number']} to {$recipientEmail}. Check logs.";
                     $remindersFailed++;
                }
             }
             $logMessages[] = "Overdue Check Complete.";

        } catch (\PDOException $e) {
            $logMessages[] = "ERROR checking overdue invoices: " . $e->getMessage();
            error_log("Trigger Reminders (Overdue) Error: " . $e->getMessage());
            $errorsOccurred++;
        }
        // --- End OVERDUE Logic ---

        // --- Set Flash Message based on outcome ---
        $finalMessage = "Reminder process finished.<br>";
        $finalMessage .= "Attempted/Sent: {$remindersSent}<br>";
        $finalMessage .= "Failures: {$remindersFailed}<br>";
        $finalMessage .= "DB Errors: {$errorsOccurred}<br>";
        $finalMessage .= "<br>Details:<br>" . implode("<br>", array_map('htmlspecialchars', $logMessages)); // Show detailed log

        if ($errorsOccurred > 0 || $remindersFailed > 0) {
             $_SESSION['flash_error'] = $finalMessage;
        } else {
             $_SESSION['flash_success'] = $finalMessage;
            
        }
        
        // --- End Flash Message ---

        $this->redirect('/admin/reports'); // Redirect back to reports index
    }

    public function showTransactionDetailReport(): void {
        // --- Access Control ---
         if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
             $_SESSION['flash_error'] = "Access Denied: Cannot view this report.";
             $this->redirect('/dashboard'); return;
         }
        // --- End Access Control ---

        // --- Get and Validate Filters ---
        $today = new DateTime();
        $defaultEndDate = $today->format('Y-m-d');
        $defaultStartDate = $today->modify('-7 days')->format('Y-m-d');
        $filterStartDate = trim($_GET['start_date'] ?? $defaultStartDate);
        $filterEndDate = trim($_GET['end_date'] ?? $defaultEndDate);
        // ... (Add date validation logic) ...
        $startDateTime = DateTime::createFromFormat('Y-m-d', $filterStartDate);
        $endDateTime = DateTime::createFromFormat('Y-m-d', $filterEndDate);
        if (!$startDateTime || $startDateTime->format('Y-m-d') !== $filterStartDate)
            $filterStartDate = $defaultStartDate;
        if (!$endDateTime || $endDateTime->format('Y-m-d') !== $filterEndDate)
            $filterEndDate = $defaultEndDate;
        if ($startDateTime && $endDateTime && $startDateTime > $endDateTime) {
            $filterStartDate = $defaultStartDate;
            $filterEndDate = $defaultEndDate;
        }
        // --- End Filters ---

        // --- Pagination ---
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = 30;
        $offset = ($page - 1) * $limit;
        // --- End Pagination ---

        $transactions = [];
        $totalRecords = 0;
        $viewError = null; // Use viewError
        $pdo = DbConnection::getInstance();

        if (!$pdo) {
            $viewError = "Database connection failed.";
        } else {
            try {
                // Build query with filters
                $sqlBase = " FROM payments p
                             JOIN payment_methods pm ON p.method_id = pm.method_id
                             JOIN students s ON p.student_id = s.student_id
                             LEFT JOIN users staff ON p.processed_by_user_id = staff.user_id
                             LEFT JOIN payment_allocations pa ON p.payment_id = pa.payment_id -- Simple join, assumes one allocation
                             LEFT JOIN fee_invoices fi ON pa.invoice_id = fi.invoice_id ";
                $whereClauses = [];
                $params = [];

                // Date Filter (mandatory) - Filter on payment_date
                $whereClauses[] = "p.payment_date BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $filterStartDate . ' 00:00:00';
                $params[':end_date'] = $filterEndDate . ' 23:59:59';

                // Add other filters here (e.g., for student_id, method_id) if implemented

                $sqlWhere = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

                // Get Total Count for Pagination
                $sqlCount = "SELECT COUNT(DISTINCT p.payment_id) " . $sqlBase . $sqlWhere; // Use DISTINCT if joins cause duplicates
                $stmtCount = $pdo->prepare($sqlCount);
                $stmtCount->execute($params);
                $totalRecords = $stmtCount->fetchColumn();

                // Fetch Paginated Transaction Data
                $sqlSelect = "SELECT p.payment_id, p.payment_date, p.amount_paid, p.receipt_number, p.reference_number, p.notes,
                                     pm.method_name,
                                     s.first_name, s.last_name, s.admission_number,
                                     staff.username AS processed_by_username,
                                     fi.invoice_number ";
                $sqlOrderLimit = " ORDER BY p.payment_date DESC, p.payment_id DESC LIMIT :limit OFFSET :offset ";
                $sql = $sqlSelect . $sqlBase . $sqlWhere . $sqlOrderLimit;

                $stmt = $pdo->prepare($sql);
                // Bind WHERE params (pass $params by value copy)
                $executeParams = $params;
                // Bind LIMIT/OFFSET params
                $executeParams[':limit'] = $limit;
                $executeParams[':offset'] = $offset;

                // Bind explicitly for type safety on limit/offset
                foreach ($executeParams as $key => &$val) {
                     $paramType = PDO::PARAM_STR; // Default
                     if ($key === ':limit' || $key === ':offset') {
                         $paramType = PDO::PARAM_INT;
                     } elseif (is_int($val)) {
                          $paramType = PDO::PARAM_INT;
                     } elseif (is_bool($val)) {
                         $paramType = PDO::PARAM_BOOL;
                     } elseif (is_null($val)) {
                          $paramType = PDO::PARAM_NULL;
                     }
                     $stmt->bindValue($key, $val, $paramType);
                }
                unset($val);

                $stmt->execute();
                $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                $viewError = "Database query failed: " . $e->getMessage();
                error_log("Transaction Detail Report Error: " . $e->getMessage());
            }
        }

        // --- Load View ---
        $this->loadView('Reporting/Views/transaction_detail_report', [
            'pageTitle' => 'Transaction Detail Report',
            'transactions' => $transactions,
            'filterStartDate' => $filterStartDate,
            'filterEndDate' => $filterEndDate,
            'currentPage' => $page,
            'totalPages' => ceil($totalRecords / $limit),
            'totalRecords' => $totalRecords,
            'viewError' => $viewError // Pass specific view error
            // Global flash handled by layout
        ], 'layout_admin');
    }


    // Helper method - already in BaseController if moved there
    // private function redirect(string $url): void { ... }
    // Helper method - already in BaseController if moved there
    // private function hasRole(string $requiredRole): bool { ... }
    // Helper method - already in Fee/Student controllers - consider moving to BaseController or helper
    private function handleDbErrorAndRedirect(string $message, string $redirectTo): void {
          $_SESSION['flash_error'] = $message;
          $this->redirect($redirectTo);
    }
}