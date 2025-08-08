<?php
// File: app/Modules/Dashboard/Views/admin_dashboard.php
// This view is now included within layout_admin.php
// It receives the $stats array from the controller.
// $pageTitle is used by the layout header.
?>

<?php if (isset($dbError) && $dbError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($dbError); ?></div>
<?php endif; ?>

<h2>Overview</h2>
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
    <div class="col">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title label text-primary"><i class="bi bi-people-fill"></i> Active Students</h5>
                <p class="card-text value fs-2"><?php echo htmlspecialchars($stats['active_students'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center h-100">
             <div class="card-body">
                <h5 class="card-title label text-danger"><i class="bi bi-currency-exchange"></i> Total Outstanding</h5>
                <p class="card-text value fs-2">Rs. <?php echo htmlspecialchars(number_format((float)($stats['total_outstanding'] ?? 0), 2)); ?></p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-center h-100">
             <div class="card-body">
                <h5 class="card-title label text-success"><i class="bi bi-calendar-check"></i> Payments Today</h5>
                 <p class="card-text value fs-2">Rs. <?php echo htmlspecialchars(number_format((float)($stats['payments_today_amount'] ?? 0), 2)); ?></p>
                 <small class="text-muted">(<?php echo htmlspecialchars($stats['payments_today_count'] ?? 0); ?> Payments)</small>
             </div>
        </div>
    </div>
     <div class="col">
         <div class="card text-center h-100">
             <div class="card-body">
                <a href="/sfms_project/public/admin/payments/proofs" class="text-decoration-none text-warning">
                    <h5 class="card-title label"><i class="bi bi-file-earmark-check"></i> Pending Proofs</h5>
                    <p class="card-text value fs-2 pending"><?php echo htmlspecialchars($stats['pending_proofs'] ?? 'N/A'); ?></p>
                </a>
             </div>
         </div>
    </div>
</div>

<div class="quick-links card">
    <div class="card-header"><h2>Quick Actions</h2></div>
    <div class="card-body">
        <a href="/sfms_project/public/admin/students/create" class="btn btn-primary mb-2"><i class="bi bi-person-plus-fill"></i> Add Student</a>
        <a href="/sfms_project/public/admin/users/create" class="btn btn-info mb-2"><i class="bi bi-person-gear"></i> Add User</a>
        <a href="/sfms_project/public/admin/fees/invoices/generate" class="btn btn-secondary mb-2"><i class="bi bi-receipt"></i> Generate Invoices</a>
        <a href="/sfms_project/public/admin/fees/invoices" class="btn btn-secondary mb-2"><i class="bi bi-journal-text"></i> View Invoices</a>
        <a href="/sfms_project/public/admin/payments/proofs" class="btn btn-warning mb-2"><i class="bi bi-check2-circle"></i> Verify Proofs (<?php echo htmlspecialchars($stats['pending_proofs'] ?? 0); ?>)</a>
        <a href="/sfms_project/public/admin/reports" class="btn btn-secondary mb-2"><i class="bi bi-graph-up"></i> View Reports</a>
    </div>
</div>