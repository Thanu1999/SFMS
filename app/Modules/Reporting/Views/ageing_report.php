<?php
// File: app/Modules/Reporting/Views/ageing_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $ageingSummary, $totalOutstanding, $asOfDate, $viewError, $flash_error
// Global flash handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (isset($flash_error) && $flash_error): ?><div class="alert alert-warning"><?php echo $flash_error; ?></div><?php endif; ?>
<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/ageing" method="GET" class="row gx-3 gy-2 align-items-end">
            <div class="col-auto">
                <label for="as_of_date" class="form-label">Report As Of Date:</label>
                <input type="date" id="as_of_date" name="as_of_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($asOfDate ?? date('Y-m-d')); ?>" required>
            </div>
             <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Generate Report</button>
            </div>
        </form>
    </div>
</div>

<h2>Outstanding Fees Ageing as of <?php echo htmlspecialchars($asOfDate ?? ''); ?></h2>

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-6">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm summary-table">
                <thead class="table-light">
                    <tr>
                        <th>Ageing Bucket</th>
                        <th class="text-end">Total Outstanding Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($ageingSummary)): ?>
                        <tr>
                            <td>Current (Not Yet Due)</td>
                            <td class="text-end"><?php echo htmlspecialchars(number_format($ageingSummary['current'], 2)); ?></td>
                        </tr>
                         <tr>
                            <td>1 - 30 Days Overdue</td>
                            <td class="text-end"><?php echo htmlspecialchars(number_format($ageingSummary['1-30'], 2)); ?></td>
                        </tr>
                         <tr>
                            <td>31 - 60 Days Overdue</td>
                            <td class="text-end"><?php echo htmlspecialchars(number_format($ageingSummary['31-60'], 2)); ?></td>
                        </tr>
                         <tr>
                            <td>61 - 90 Days Overdue</td>
                            <td class="text-end"><?php echo htmlspecialchars(number_format($ageingSummary['61-90'], 2)); ?></td>
                        </tr>
                         <tr>
                            <td>Over 90 Days Overdue</td>
                            <td class="text-end"><?php echo htmlspecialchars(number_format($ageingSummary['90+'], 2)); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr> <td colspan="2" class="text-center">Could not generate ageing summary.</td> </tr>
                    <?php endif; ?>
                </tbody>
                 <tfoot class="table-group-divider fw-bold">
                    <tr>
                        <td class="text-end">Grand Total Outstanding:</td>
                        <td class="text-end fs-5"><?php echo htmlspecialchars(number_format($totalOutstanding ?? 0.00, 2)); ?></td>
                    </tr>
                 </tfoot>
            </table>
        </div>
    </div>
</div>