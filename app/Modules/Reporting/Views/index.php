<?php
// File: app/Modules/Reporting/Views/index.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $isAdmin
// Global flash messages handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Reports'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>

<p class="lead">Select a report to view financial and system activity.</p>

<div class="list-group mb-4">
    <h2 class="h5">Financial Reports</h2>
    <a href="/sfms_project/public/admin/reports/fee-collection" class="list-group-item list-group-item-action">
        <i class="bi bi-cash-stack me-2"></i>Fee Collection Report (Detailed)
    </a>
    <a href="/sfms_project/public/admin/reports/fee-collection-summary" class="list-group-item list-group-item-action">
        <i class="bi bi-bar-chart-line-fill me-2"></i>Fee Collection Summary
    </a>
     <a href="/sfms_project/public/admin/reports/outstanding-dues" class="list-group-item list-group-item-action">
         <i class="bi bi-exclamation-triangle-fill me-2"></i>Outstanding Dues Report
    </a>
     <a href="/sfms_project/public/admin/reports/defaulters" class="list-group-item list-group-item-action">
        <i class="bi bi-person-x-fill me-2"></i>Defaulter List
    </a>
     <a href="/sfms_project/public/admin/reports/ageing" class="list-group-item list-group-item-action">
        <i class="bi bi-hourglass-split me-2"></i>Outstanding Fees Ageing Report
    </a>
    <a href="/sfms_project/public/admin/reports/transaction-detail" class="list-group-item list-group-item-action">
         <i class="bi bi-list-columns-reverse me-2"></i>Payment Transaction Detail
    </a>
     </div>

<div class="list-group mb-4">
    <h2 class="h5">Student Reports</h2>
     <a href="/sfms_project/public/admin/reports/student-ledger" class="list-group-item list-group-item-action">
         <i class="bi bi-journal-text me-2"></i>Student Ledger / Payment History
    </a>
     </div>


<?php if (isset($isAdmin) && $isAdmin): ?>
    <div class="list-group mb-4">
         <h2 class="h5">Administrative Reports</h2>
         <a href="/sfms_project/public/admin/reports/audit-log" class="list-group-item list-group-item-action">
             <i class="bi bi-shield-lock-fill me-2"></i>Audit Log Report
         </a>
    </div>
<?php endif; ?>


<div class="card bg-light">
    <div class="card-header"><h3>Manual Actions</h3></div>
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/trigger-reminders" method="POST" onsubmit="return confirm('Are you sure you want to check for and send fee reminders now? This might take a moment.');">
             <?php //echo $this->csrfInput(); // CSRF Token ?>
             <p>Manually check for invoices due soon or overdue and attempt to send email reminders.</p>
             <button type="submit" class="btn btn-warning"><i class="bi bi-send-exclamation"></i> Send Fee Reminders Now</button>
        </form>
    </div>
</div>