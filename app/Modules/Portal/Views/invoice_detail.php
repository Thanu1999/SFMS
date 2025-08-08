<?php
// File: app/Modules/Portal/Views/invoice_detail.php
// Included by layout_portal.php

// Variables passed: $pageTitle, $invoice, $items, $payments, $viewError
// Global flash messages handled by layout header
?>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>

<?php if (isset($invoice) && $invoice): ?>
    <div class="invoice-container card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Invoice Details'; ?></h4>
             <button class="btn btn-sm btn-outline-secondary print-button" onclick="window.print();"><i class="bi bi-printer"></i> Print</button>
        </div>
        <div class="card-body">
            <div class="header-section row mb-4">
                <div class="col-md-6">
                    <h5>Invoice</h5>
                </div>
                <div class="col-md-6 text-md-end invoice-meta">
                    <strong>Invoice #:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?><br>
                    <strong>Issue Date:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($invoice['issue_date']))); ?><br>
                    <strong>Due Date:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($invoice['due_date']))); ?><br>
                    <?php /* Status calculation */ $statusClass = ''; $displayStatus = ucfirst(str_replace('_', ' ', $invoice['status'])); if (in_array($invoice['status'], ['unpaid', 'partially_paid', 'overdue']) && isset($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d')) { $statusClass = 'bg-danger'; $displayStatus = 'Overdue'; } else { switch ($invoice['status']) { case 'paid': $statusClass = 'bg-success'; break; case 'partially_paid': $statusClass = 'bg-warning text-dark'; break; case 'unpaid': $statusClass = 'bg-danger'; break; case 'cancelled': $statusClass = 'bg-secondary'; break; default: $statusClass = 'bg-light text-dark'; break; } } ?>
                    <strong>Status:</strong> <span class="badge <?php echo $statusClass; ?> fs-6"><?php echo htmlspecialchars($displayStatus); ?></span>
                </div>
            </div>

            <div class="student-section mb-4">
                <h5>Bill To:</h5>
                <strong><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></strong><br>
                Class: <?php echo htmlspecialchars($invoice['class_name'] ?? 'N/A'); ?><br>
                Session: <?php echo htmlspecialchars($invoice['session_name']); ?><br>
            </div>

            <div class="items-section mb-4">
                <h5>Invoice Items</h5>
                 <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th>#</th><th>Description</th><th>Category</th><th class="text-end">Amount</th></tr></thead>
                        <tbody>
                            <?php if (!empty($items)): $itemNumber = 1; foreach ($items as $item): ?>
                            <tr><td><?php echo $itemNumber++; ?></td><td><?php echo htmlspecialchars($item['description']); ?></td><td><?php echo htmlspecialchars($item['category_name']); ?></td><td class="text-end"><?php echo htmlspecialchars(number_format((float)$item['amount'], 2)); ?></td></tr>
                            <?php endforeach; else: ?><tr><td colspan="4" class="text-center text-muted">No items found for this invoice.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="discount-section mb-4">
                 <h5>Applied Discounts</h5>
                 <?php // NOTE: $appliedDiscounts needs to be fetched and passed by PortalInvoiceController::view() ?>
                 <?php if (!empty($appliedDiscounts)): // Check if the variable exists and is not empty ?>
                     <ul class="list-group list-group-flush">
                         <?php foreach($appliedDiscounts as $discount): ?>
                             <li class="list-group-item ps-0 pe-0">
                                 <strong><?php echo htmlspecialchars($discount['discount_name']); ?>:</strong>
                                 <span class="text-danger float-end">-<?php echo htmlspecialchars(number_format((float)$discount['applied_amount'], 2)); ?></span>
                                 <?php if ($discount['notes']): ?><br><small class="text-muted ms-2"><em>(<?php echo htmlspecialchars($discount['notes']); ?>)</em></small><?php endif; ?>
                             </li>
                         <?php endforeach; ?>
                     </ul>
                 <?php else: ?>
                     <p class="text-muted">No discounts applied to this invoice.</p>
                 <?php endif; ?>
             </div>


             <div class="summary-section mb-4">
                  <div class="row justify-content-end">
                    <div class="col-md-6 col-lg-5">
                         <table class="table table-sm table-borderless mb-0">
                             <tr><th class="text-end">Subtotal:</th><td class="text-end"><?php echo htmlspecialchars(number_format((float)($invoice['total_amount'] ?? 0.00), 2)); ?></td></tr>
                             <tr><th class="text-end">Total Discount:</th><td class="text-end text-danger">-<?php echo htmlspecialchars(number_format((float)($invoice['total_discount'] ?? 0.00), 2)); ?></td></tr>
                             <tr><th class="text-end border-top pt-2">Total Payable:</th><td class="text-end border-top pt-2"><?php echo htmlspecialchars(number_format((float)$invoice['total_payable'], 2)); ?></td></tr>
                             <tr><th class="text-end">Total Paid:</th><td class="text-end"><?php echo htmlspecialchars(number_format((float)$invoice['total_paid'], 2)); ?></td></tr>
                             <tr><th class="text-end fs-5">Balance Due:</th><td class="text-end fw-bold fs-5"><?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></td></tr>
                         </table>
                     </div>
                 </div>
             </div>

             <div class="payments-section">
                 <h5>Payments Received</h5>
                 <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Payment Date</th><th>Method</th><th>Receipt #</th><th>Reference</th><th class="text-end">Amount Paid</th></tr></thead>
                             <tbody>
                                 <?php foreach ($payments as $payment): ?>
                                    <tr><td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($payment['payment_date']))); ?></td><td><?php echo htmlspecialchars($payment['method_name']); ?></td><td><?php echo htmlspecialchars($payment['receipt_number']); ?></td><td><?php echo htmlspecialchars($payment['reference_number'] ?? ''); ?></td><td class="text-end"><?php echo htmlspecialchars(number_format((float)$payment['amount_paid'], 2)); ?></td></tr>
                                 <?php endforeach; ?>
                             </tbody>
                        </table>
                    </div>
                 <?php else: ?>
                    <p class="text-muted">No payments recorded for this invoice yet.</p>
                 <?php endif; ?>
             </div>

        </div> </div> <?php else: ?>
    <div class="alert alert-danger">Invoice details could not be loaded or you are not authorized to view this invoice.</div>
    <a href="/sfms_project/public/portal/fees" class="btn btn-secondary">Back to My Fees</a>
<?php endif; ?>