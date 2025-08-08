<?php
// File: app/Modules/Reporting/Views/defaulter_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $defaulters, $totalOverdueAmount, $sessions, $classes, $filterSessionId, $filterClassId, $filterMinDaysOverdue, $filterMinBalanceDue, $viewError
// Global flash handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>

<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/defaulters" method="GET" class="row gx-3 gy-2 align-items-end">
             <div class="col-md-6 col-lg-3">
                <label for="session_id" class="form-label">Session:</label>
                <select id="session_id" name="session_id" class="form-select form-select-sm">
                    <option value="">-- All Sessions --</option>
                     <?php foreach ($sessions ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($filterSessionId) && $id == $filterSessionId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label for="class_id" class="form-label">Class:</label>
                 <select id="class_id" name="class_id" class="form-select form-select-sm">
                    <option value="">-- All Classes --</option>
                     <?php foreach ($classes ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($filterClassId) && $id == $filterClassId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
             <div class="col-md-4 col-lg-2">
                <label for="min_days_overdue" class="form-label">Min Days Overdue:</label>
                <input type="number" id="min_days_overdue" name="min_days_overdue" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterMinDaysOverdue ?? 1); ?>" min="0">
             </div>
              <div class="col-md-4 col-lg-2">
                <label for="min_balance_due" class="form-label">Min Balance Due:</label>
                <input type="number" id="min_balance_due" name="min_balance_due" class="form-control form-control-sm" value="<?php echo htmlspecialchars(number_format($filterMinBalanceDue ?? 0.01, 2, '.', '')); ?>" min="0.01" step="0.01">
             </div>
            <div class="col-md-4 col-lg-auto">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<h2>Defaulter List <?php echo isset($filterSessionId) ? ' for ' . htmlspecialchars($sessions[$filterSessionId] ?? '') : ''; ?> <?php echo isset($filterClassId) ? ' in ' . htmlspecialchars($classes[$filterClassId] ?? '') : ''; ?></h2>
 <p><small>(Showing invoices overdue by at least <?php echo htmlspecialchars($filterMinDaysOverdue ?? 1);?> day(s) with minimum balance Rs. <?php echo htmlspecialchars(number_format($filterMinBalanceDue ?? 0.01, 2));?>)</small></p>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Adm No.</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Invoice #</th>
                <th>Due Date</th>
                <th class="text-center">Days Overdue</th>
                <th class="text-end">Balance Due</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($defaulters) && !empty($defaulters)): ?>
                <?php foreach ($defaulters as $invoice): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['admission_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['last_name'] . ', ' . $invoice['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['class_name'] ?? 'N/A'); ?></td>
                        <td><a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $invoice['invoice_id']; ?>" title="View Invoice Details"><?php echo htmlspecialchars($invoice['invoice_number']); ?></a></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($invoice['due_date']))); ?></td>
                        <td class="text-center text-danger fw-bold"><?php echo htmlspecialchars($invoice['days_overdue']); ?></td>
                        <td class="text-end fw-bold"><?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></td>
                        <td class="actions text-nowrap">
                             <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $invoice['invoice_id']; ?>" class="btn btn-sm btn-info me-1" title="View Details"><i class="bi bi-eye-fill"></i></a>
                             <a href="/sfms_project/public/admin/payments/record/<?php echo $invoice['invoice_id']; ?>" class="btn btn-sm btn-success" title="Record Payment"><i class="bi bi-currency-dollar"></i></a>
                             </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr> <td colspan="8" class="text-center">No defaulters found matching the criteria.</td> </tr> <?php endif; ?>
        </tbody>
         <tfoot class="table-group-divider fw-bold">
            <tr>
                <th colspan="6" class="text-end">Total Overdue Amount:</th>
                <th class="text-end"><?php echo htmlspecialchars(number_format($totalOverdueAmount ?? 0.00, 2)); ?></th>
                <th></th> </tr>
         </tfoot>
    </table>
</div>