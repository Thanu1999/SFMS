<?php
// File: app/Modules/Reporting/Views/outstanding_dues_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $invoices, $totalOutstanding, $sessions, $classes, $filterSessionId, $filterClassId, $dbError, $flash_error
// Global flash messages handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($dbError) && $dbError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($dbError); ?></div><?php endif; ?>
<?php if (isset($flash_error) && $flash_error): ?><div class="alert alert-warning"><?php echo $flash_error; ?></div><?php endif; ?>


<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/outstanding-dues" method="GET" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-5 col-lg-4">
                <label for="session_id" class="form-label">Filter by Session:</label>
                <select id="session_id" name="session_id" class="form-select form-select-sm">
                    <option value="">-- All Sessions --</option>
                     <?php foreach ($sessions ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($filterSessionId) && $id == $filterSessionId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 col-lg-4">
                <label for="class_id" class="form-label">Filter by Class:</label>
                 <select id="class_id" name="class_id" class="form-select form-select-sm">
                    <option value="">-- All Classes --</option>
                     <?php foreach ($classes ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($filterClassId) && $id == $filterClassId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-auto">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<h2>Outstanding Dues <?php echo isset($filterSessionId) ? ' for ' . htmlspecialchars($sessions[$filterSessionId] ?? 'Selected Session') : ''; ?> <?php echo isset($filterClassId) ? ' in ' . htmlspecialchars($classes[$filterClassId] ?? 'Selected Class') : ''; ?></h2>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Inv #</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Session</th>
                <th>Due Date</th>
                <th class="text-end">Total Payable</th>
                <th class="text-end">Amount Paid</th>
                <th class="text-end">Balance Due</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($invoices) && !empty($invoices)): ?>
                <?php foreach ($invoices as $invoice): ?>
                     <?php
                        // Determine status class
                        $statusClass = '';
                        $displayStatus = ucfirst(str_replace('_', ' ', $invoice['status']));
                        if (in_array($invoice['status'], ['unpaid', 'partially_paid', 'overdue']) && isset($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d')) {
                            $statusClass = 'bg-danger'; // Overdue uses danger background
                            $displayStatus = 'Overdue';
                        } else {
                            switch ($invoice['status']) {
                                case 'partially_paid': $statusClass = 'bg-warning text-dark'; break;
                                case 'unpaid': $statusClass = 'bg-danger'; break;
                                // Add other cases if needed (e.g., cancelled)
                                default: $statusClass = 'bg-light text-dark'; break;
                            }
                        }
                     ?>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['last_name'] . ', ' . $invoice['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['class_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['session_name']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($invoice['due_date']))); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$invoice['total_payable'], 2)); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$invoice['total_paid'], 2)); ?></td>
                        <td class="text-end fw-bold"><?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($displayStatus); ?></span></td>
                        <td class="actions text-nowrap">
                            <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $invoice['invoice_id']; ?>" class="btn btn-sm btn-info me-1" title="View Details"><i class="bi bi-eye-fill"></i></a>
                            <a href="/sfms_project/public/admin/payments/record/<?php echo $invoice['invoice_id']; ?>" class="btn btn-sm btn-success" title="Record Payment"><i class="bi bi-currency-dollar"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr> <td colspan="10" class="text-center">No outstanding dues found matching the criteria.</td> </tr> <?php endif; ?>
        </tbody>
        <tfoot class="table-group-divider fw-bold">
            <tr>
                <th colspan="7" class="text-end">Total Outstanding:</th>
                <th class="text-end"><?php echo htmlspecialchars(number_format($totalOutstanding ?? 0.00, 2)); ?></th>
                <th colspan="2"></th> </tr>
         </tfoot>
    </table>
</div>