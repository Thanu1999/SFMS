<?php
// File: app/Modules/Reporting/Views/fee_collection_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $payments, $totalCollected, $startDate, $endDate, $viewError
// Global flash handled by layout
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Fee Collection Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php $flash_error_validation = $_SESSION['flash_error'] ?? null; unset($_SESSION['flash_error']); // Handle specific validation errors if passed back ?>
<?php if ($flash_error_validation): ?><div class="alert alert-warning"><?php echo $flash_error_validation; ?></div><?php endif; ?>


<div class="card bg-light mb-3">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/fee-collection" method="GET" class="row gx-3 gy-2 align-items-end">
            <div class="col-auto">
                <label for="start_date" class="form-label">From:</label>
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($startDate ?? ''); ?>" required>
            </div>
             <div class="col-auto">
                <label for="end_date" class="form-label">To:</label>
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($endDate ?? ''); ?>" required>
             </div>
              <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>


<h2>Report Period: <?php echo htmlspecialchars($startDate ?? ''); ?> to <?php echo htmlspecialchars($endDate ?? ''); ?></h2>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Pay ID</th>
                <th>Payment Date</th>
                <th>Receipt #</th>
                <th>Student Name</th>
                <th>Invoice #</th>
                <th>Method</th>
                <th class="text-end">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($payments) && !empty($payments)): ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($payment['payment_date']))); ?></td>
                        <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                        <td><?php echo htmlspecialchars($payment['last_name'] . ', ' . $payment['first_name']); ?></td>
                        <td>
                            <?php if (!empty($payment['invoice_number'])): ?>
                            <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $payment['invoice_id'] ?? ''; // Need invoice_id from join ?>"><?php echo htmlspecialchars($payment['invoice_number']); ?></a>
                            <?php else: echo 'N/A'; endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($payment['method_name']); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$payment['amount_paid'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr> <td colspan="7" class="text-center">No payments found for the selected period.</td> </tr>
            <?php endif; ?>
        </tbody>
         <tfoot class="table-group-divider">
            <tr>
                <th colspan="6" class="text-end">Total Collected:</th>
                <th class="text-end"><?php echo htmlspecialchars(number_format($totalCollected ?? 0.00, 2)); ?></th>
            </tr>
         </tfoot>
    </table>
</div>