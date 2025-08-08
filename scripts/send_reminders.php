<?php
// scripts/send_reminders.php

// Basic setup - Adjust path if your script location or vendor dir differs
require_once __DIR__ . '/../vendor/autoload.php'; // Composer Autoloader
require_once __DIR__ . '/../app/Core/Database/DbConnection.php'; // DB Connection Class
require_once __DIR__ . '/../app/Core/Services/NotificationService.php'; // Notification Service

use App\Core\Database\DbConnection;
use App\Core\Services\NotificationService;

// --- Configuration ---
$daysBeforeDueDate = 7; // Send 'Due Soon' reminder X days before due date
$daysAfterDueDate = 3; // Send first 'Overdue' reminder Y days after due date
// Add more overdue intervals later if needed (e.g., 15 days, 30 days)
$currencySymbol = 'Rs.'; // Or load from config
$schoolName = 'Your School Name'; // Or load from config
// --- End Configuration ---


echo "-------------------------------------------\n";
echo "Starting Fee Reminder Script: " . date('Y-m-d H:i:s') . "\n";
echo "-------------------------------------------\n";

$pdo = DbConnection::getInstance();
if (!$pdo) {
    echo "Error: Database connection failed. Cannot proceed.\n";
    exit(1); // Exit with error code
}

$notificationService = new NotificationService();
$remindersSent = 0;
$errorsOccurred = 0;

// --- Logic for DUE SOON Reminders ---
try {
    $targetDueDate = date('Y-m-d', strtotime("+{$daysBeforeDueDate} days"));
    echo "Checking for invoices due soon on: {$targetDueDate}...\n";

    $sqlDueSoon = "SELECT fi.invoice_id, fi.invoice_number, fi.due_date, fi.total_payable, fi.total_paid,
                          s.student_id, s.first_name, s.last_name, s.user_id AS student_user_id,
                          (SELECT GROUP_CONCAT(sgl.user_id) FROM student_guardian_links sgl WHERE sgl.student_id = fi.student_id) AS guardian_user_ids,
                          u.email AS user_email, stu_u.email AS student_user_email -- Fetch emails for both potential recipients
                   FROM fee_invoices fi
                   JOIN students s ON fi.student_id = s.student_id
                   LEFT JOIN users u ON FIND_IN_SET(u.user_id, (SELECT GROUP_CONCAT(sgl.user_id) FROM student_guardian_links sgl WHERE sgl.student_id = fi.student_id)) -- Find Parent User Email
                   LEFT JOIN users stu_u ON s.user_id = stu_u.user_id -- Find Student User Email
                   WHERE fi.status IN ('unpaid', 'partially_paid')
                     AND fi.due_date = :target_due_date";
                     // Optional: Add check here to prevent sending if already sent recently

    $stmtDueSoon = $pdo->prepare($sqlDueSoon);
    $stmtDueSoon->execute([':target_due_date' => $targetDueDate]);

    while ($invoice = $stmtDueSoon->fetch(PDO::FETCH_ASSOC)) {
        echo "  Found Due Soon Invoice: #{$invoice['invoice_number']} for Student ID: {$invoice['student_id']}...\n";
        $balanceDue = (float)$invoice['total_payable'] - (float)$invoice['total_paid'];

        // Determine recipient (Prioritize parent, then student user, then student direct email?) - Adjust logic as needed
        $recipientUserId = $invoice['guardian_user_ids'] ? explode(',', $invoice['guardian_user_ids'])[0] : $invoice['student_user_id']; // Simplified: first guardian or student user ID
        $recipientEmail = $invoice['user_email'] ?: $invoice['student_user_email'] ?: null; // Parent email, then student user email
        $recipientDetail = (!$recipientUserId && $recipientEmail) ? $recipientEmail : null;

         if (!$recipientUserId && !$recipientEmail) {
             echo "    Skipping: No user ID or email found for notification.\n";
             continue;
         }

        $data = [
            'student_name' => $invoice['first_name'] . ' ' . $invoice['last_name'],
            'invoice_number' => $invoice['invoice_number'],
            'due_date' => date('M d, Y', strtotime($invoice['due_date'])),
            'balance_due' => number_format($balanceDue, 2),
            'currency_symbol' => $currencySymbol,
            'school_name' => $schoolName
        ];

        if ($notificationService->sendNotification($recipientUserId, 'FEE_DUE_SOON', $data, $recipientDetail, $invoice['invoice_id'], 'invoice')) {
            echo "    Notification logged/sent for Invoice #{$invoice['invoice_number']}.\n";
            $remindersSent++;
        } else {
            echo "    ERROR logging/sending notification for Invoice #{$invoice['invoice_number']}.\n";
            $errorsOccurred++;
        }
    }
     echo "Due Soon Check Complete.\n";

} catch (\PDOException $e) {
     echo "ERROR checking due soon invoices: " . $e->getMessage() . "\n";
     $errorsOccurred++;
}
// --- End DUE SOON Logic ---


// --- Logic for OVERDUE Reminders ---
try {
    $targetOverdueDate = date('Y-m-d', strtotime("-{$daysAfterDueDate} days"));
    echo "Checking for invoices overdue since: {$targetOverdueDate}...\n";

    // Similar query, but check for due_date = targetOverdueDate
    $sqlOverdue = "SELECT fi.invoice_id, fi.invoice_number, fi.due_date, fi.total_payable, fi.total_paid,
                          s.student_id, s.first_name, s.last_name, s.user_id AS student_user_id,
                          (SELECT GROUP_CONCAT(sgl.user_id) FROM student_guardian_links sgl WHERE sgl.student_id = fi.student_id) AS guardian_user_ids,
                          u.email AS user_email, stu_u.email AS student_user_email
                   FROM fee_invoices fi
                   JOIN students s ON fi.student_id = s.student_id
                   LEFT JOIN users u ON FIND_IN_SET(u.user_id, (SELECT GROUP_CONCAT(sgl.user_id) FROM student_guardian_links sgl WHERE sgl.student_id = fi.student_id))
                   LEFT JOIN users stu_u ON s.user_id = stu_u.user_id
                   WHERE fi.status IN ('unpaid', 'partially_paid', 'overdue')
                     AND fi.due_date = :target_overdue_date";
                    // Optional: Add check here to prevent sending if already sent recently


    $stmtOverdue = $pdo->prepare($sqlOverdue);
    $stmtOverdue->execute([':target_overdue_date' => $targetOverdueDate]);

     while ($invoice = $stmtOverdue->fetch(PDO::FETCH_ASSOC)) {
        echo "  Found Overdue Invoice: #{$invoice['invoice_number']} for Student ID: {$invoice['student_id']}...\n";
        $balanceDue = (float)$invoice['total_payable'] - (float)$invoice['total_paid'];

        // Determine recipient (same logic as above)
        $recipientUserId = $invoice['guardian_user_ids'] ? explode(',', $invoice['guardian_user_ids'])[0] : $invoice['student_user_id'];
        $recipientEmail = $invoice['user_email'] ?: $invoice['student_user_email'] ?: null;
        $recipientDetail = (!$recipientUserId && $recipientEmail) ? $recipientEmail : null;

         if (!$recipientUserId && !$recipientEmail) {
             echo "    Skipping: No user ID or email found for notification.\n";
             continue;
         }

        $data = [ /* ... same data preparation as above ... */
            'student_name' => $invoice['first_name'] . ' ' . $invoice['last_name'],
            'invoice_number' => $invoice['invoice_number'],
            'due_date' => date('M d, Y', strtotime($invoice['due_date'])),
            'balance_due' => number_format($balanceDue, 2),
            'currency_symbol' => $currencySymbol,
            'school_name' => $schoolName
        ];

        if ($notificationService->sendNotification($recipientUserId, 'FEE_OVERDUE', $data, $recipientDetail, $invoice['invoice_id'], 'invoice')) {
            echo "    Notification logged/sent for Invoice #{$invoice['invoice_number']}.\n";
            $remindersSent++;
        } else {
            echo "    ERROR logging/sending notification for Invoice #{$invoice['invoice_number']}.\n";
            $errorsOccurred++;
        }
     }
     echo "Overdue Check Complete.\n";

} catch (\PDOException $e) {
     echo "ERROR checking overdue invoices: " . $e->getMessage() . "\n";
     $errorsOccurred++;
}
// --- End OVERDUE Logic ---

echo "-------------------------------------------\n";
echo "Fee Reminder Script Finished: " . date('Y-m-d H:i:s') . "\n";
echo "Reminders Processed/Sent: {$remindersSent}\n";
echo "Errors: {$errorsOccurred}\n";
echo "-------------------------------------------\n";
exit(0); // Exit successfully
?>