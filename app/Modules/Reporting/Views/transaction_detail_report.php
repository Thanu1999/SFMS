<?php
// File: app/Modules/Reporting/Views/transaction_detail_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $transactions, $filterStartDate, $filterEndDate, $currentPage, $totalPages, $totalRecords, $viewError
// Global flash handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>

<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/transaction-detail" method="GET" class="row gx-3 gy-2 align-items-end">
            <div class="col-auto">
                <label for="start_date" class="form-label">From:</label>
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterStartDate ?? ''); ?>" required>
            </div>
             <div class="col-auto">
                <label for="end_date" class="form-label">To:</label>
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filterEndDate ?? ''); ?>" required>
             </div>
              <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<h2>Payments from <?php echo htmlspecialchars($filterStartDate ?? ''); ?> to <?php echo htmlspecialchars($filterEndDate ?? ''); ?></h2>
<p>Total Records Found: <?php echo $totalRecords ?? 0; ?></p>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Pay ID</th>
                <th>Date/Time</th>
                <th>Receipt #</th>
                <th>Student</th>
                <th>Adm No.</th>
                <th>Invoice #</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="text-end">Amount Paid</th>
                <th>Processed By</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($transactions) && !empty($transactions)): ?>
                <?php foreach ($transactions as $txn): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($txn['payment_id']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($txn['payment_date']))); ?></td>
                        <td><?php echo htmlspecialchars($txn['receipt_number']); ?></td>
                        <td><?php echo htmlspecialchars($txn['last_name'] . ', ' . $txn['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($txn['admission_number']); ?></td>
                        <td>
                            <?php // Link to invoice detail if invoice_id exists ?>
                            <?php if (!empty($txn['invoice_id']) && !empty($txn['invoice_number'])): ?>
                                <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $txn['invoice_id']; ?>" title="View Invoice Details">
                                    <?php echo htmlspecialchars($txn['invoice_number']); ?>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($txn['method_name']); ?></td>
                        <td><?php echo htmlspecialchars($txn['reference_number'] ?? ''); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$txn['amount_paid'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($txn['processed_by_username'] ?? 'N/A'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($txn['notes'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr> <td colspan="11" class="text-center">No payment transactions found for the selected criteria.</td> </tr> <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($totalPages) && $totalPages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php
             $queryParams = $_GET; // Get current filters
             unset($queryParams['page']);
             $baseUrl = "/sfms_project/public/admin/reports/transaction-detail?" . http_build_query($queryParams);
             $baseUrl .= (empty($queryParams) ? '' : '&') . 'page='; // Add base page param

             // Previous Button
             $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
             echo "<li class='page-item {$prevDisabled}'><a class='page-link' href='{$baseUrl}" . ($currentPage - 1) . "'>&laquo; Prev</a></li>";

             // Page Numbers (simplified - potentially add logic for ellipsis '...' if many pages)
             for ($i = 1; $i <= $totalPages; $i++):
                 $activeClass = ($i == $currentPage) ? 'active' : '';
                 echo "<li class='page-item {$activeClass}'><a class='page-link' href='{$baseUrl}{$i}'>{$i}</a></li>";
             endfor;

             // Next Button
              $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
              echo "<li class='page-item {$nextDisabled}'><a class='page-link' href='{$baseUrl}" . ($currentPage + 1) . "'>Next &raquo;</a></li>";
        ?>
    </ul>
</nav>
<?php endif; ?>