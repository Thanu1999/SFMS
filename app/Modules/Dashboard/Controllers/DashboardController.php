<?php
namespace App\Modules\Dashboard\Controllers;

use App\Core\Http\BaseController;
use App\Core\Database\DbConnection;
use PDO;
use DateTime;

class DashboardController extends BaseController {

    /**
     * Display the main Admin/Staff dashboard with summary stats.
     * Handles GET /admin/dashboard
     */
    public function index(): void {
        // --- Access Control ---
        if (!$this->hasRole('Admin') && !$this->hasRole('Staff')) {
            // If logged in but wrong role, maybe redirect to portal? Or just deny.
             $_SESSION['flash_error'] = "Access Denied to Admin Dashboard.";
             // Check if they have student/parent role to redirect to portal?
             if ($this->hasRole('Student') || $this->hasRole('Parent')) {
                  $this->redirect('/portal/dashboard'); return;
             } else {
                  $this->redirect('/logout'); return; // Or /login
             }
        }
        // --- End Access Control ---

        $pdo = DbConnection::getInstance();
        if (!$pdo) {
            // Handle DB error - maybe load view with error?
            $this->loadView('Dashboard/Views/admin_dashboard', [
                'pageTitle' => 'Admin Dashboard',
                'dbError' => 'Database connection failed.'
            ]);
            return;
        }

        $stats = [
            'active_students' => 0,
            'total_outstanding' => 0.00,
            'payments_today_count' => 0,
            'payments_today_amount' => 0.00,
            'pending_proofs' => 0,
        ];
        $dbError = null;

        try {
            // Get Active Students Count
            $stmtStudents = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
            $stats['active_students'] = $stmtStudents->fetchColumn();

            // Get Total Outstanding Balance
            $stmtOutstanding = $pdo->query("SELECT SUM(total_payable - total_paid) FROM fee_invoices WHERE status IN ('unpaid', 'partially_paid', 'overdue')");
            $stats['total_outstanding'] = $stmtOutstanding->fetchColumn() ?: 0.00;

            // Get Payments Today
            $todayStart = date('Y-m-d 00:00:00');
            $todayEnd = date('Y-m-d 23:59:59');
            $stmtToday = $pdo->prepare("SELECT COUNT(*) as count, SUM(amount_paid) as amount FROM payments WHERE payment_status = 'completed' AND payment_date BETWEEN :start AND :end");
            $stmtToday->execute([':start' => $todayStart, ':end' => $todayEnd]);
            $todayData = $stmtToday->fetch(PDO::FETCH_ASSOC);
            $stats['payments_today_count'] = $todayData['count'] ?: 0;
            $stats['payments_today_amount'] = $todayData['amount'] ?: 0.00;

             // Get Pending Proofs Count
            $stmtProofs = $pdo->query("SELECT COUNT(*) FROM payment_proofs WHERE status = 'pending'");
            $stats['pending_proofs'] = $stmtProofs->fetchColumn();

        } catch (\PDOException $e) {
             $dbError = "Database error fetching dashboard stats.";
             error_log("Admin Dashboard Error: " . $e->getMessage());
        }

        $this->loadView('Dashboard/Views/admin_dashboard', [ // View path relative to Modules/
            'pageTitle' => 'Admin Dashboard',
            'stats' => $stats,
            'dbError' => $dbError,
        ], 'layout_admin');
        
    }

     // Inherited helpers like hasRole, redirect, loadView assumed
}