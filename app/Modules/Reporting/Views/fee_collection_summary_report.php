<?php
// File: app/Modules/Reporting/Views/fee_collection_summary_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $summaryData, $grandTotal, $startDate, $endDate, $groupBy, $groupingLabel, $viewError (renamed from dbError), $flash_error
// Global flash messages handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<?php if (isset($flash_error) && $flash_error): ?>
    <div class="alert alert-warning"><?php echo $flash_error; /* Errors might have HTML */ ?></div>
<?php endif; ?>


<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/fee-collection-summary" method="GET" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label for="start_date" class="form-label">From:</label>
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($startDate ?? ''); ?>" required>
            </div>
             <div class="col-md-4 col-lg-3">
                <label for="end_date" class="form-label">To:</label>
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($endDate ?? ''); ?>" required>
             </div>
              <div class="col-md-4 col-lg-3">
                 <label for="group_by" class="form-label">Group By:</label>
                 <select id="group_by" name="group_by" class="form-select form-select-sm">
                    <option value="method" <?php echo (isset($groupBy) && $groupBy === 'method') ? 'selected' : ''; ?>>Payment Method</option>
                    <option value="class" <?php echo (isset($groupBy) && $groupBy === 'class') ? 'selected' : ''; ?>>Class</option>
                    <option value="category" <?php echo (isset($groupBy) && $groupBy === 'category') ? 'selected' : ''; ?>>Fee Category</option>
                 </select>
              </div>
            <div class="col-lg-auto">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<h2>Report Period: <?php echo htmlspecialchars($startDate ?? ''); ?> to <?php echo htmlspecialchars($endDate ?? ''); ?></h2>
<h3 class="text-muted fs-5">Grouped By: <?php echo htmlspecialchars($groupingLabel ?? 'N/A'); ?></h3>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th><?php echo htmlspecialchars($groupingLabel ?? 'Group'); ?></th>
                <th class="text-end">Total Collected</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($summaryData) && !empty($summaryData)): ?>
                <?php foreach ($summaryData as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['group_name']); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$row['total_amount'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr> <td colspan="2" class="text-center">No fee collections found for the selected criteria.</td> </tr>
            <?php endif; ?>
        </tbody>
         <tfoot class="table-group-divider fw-bold">
            <tr>
                <td class="text-end">Grand Total:</td>
                <td class="text-end"><?php echo htmlspecialchars(number_format($grandTotal ?? 0.00, 2)); ?></td>
            </tr>
         </tfoot>
    </table>
</div>