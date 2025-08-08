<?php
// File: app/Modules/FeeManagement/Views/invoices/view.php
// Included by layout_admin.php
?>

<?php if (isset($invoice) && $invoice): ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
         <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Invoice Details'; ?></h1>
         <button class="btn btn-secondary print-button" onclick="window.print();"><i class="bi bi-printer"></i> Print Invoice</button>
    </div>

    <?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
    <div class="invoice-container card shadow-sm">
        <div class="card-body">
            <div class="header-section row mb-4">
                <div class="col-md-6">
                    <h4>Invoice</h4>
                </div>
                <div class="col-md-6 text-md-end invoice-meta">
                    <strong>Invoice #:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?><br>
                    <strong>Issue Date:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($invoice['issue_date']))); ?><br>
                    <strong>Due Date:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($invoice['due_date']))); ?><br>
                    <?php // Inside the .invoice-meta div in invoices/view.php
                        $invoiceStatus = $invoice['status'];
                        $statusClass = ''; // Default empty
                    
                        // First check if overdue
                        if (in_array($invoiceStatus, ['unpaid', 'partially_paid', 'overdue']) && isset($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d')) {
                            $statusClass = 'bg-danger'; // Overdue uses danger background
                            $invoiceStatusForDisplay = 'Overdue'; // Special display text
                        } else {
                            // Apply classes based on original status
                            switch ($invoiceStatus) {
                                case 'paid':
                                    $statusClass = 'bg-success';
                                    break;
                                case 'partially_paid':
                                    $statusClass = 'bg-warning text-dark';
                                    break; // text-dark added
                                case 'unpaid':
                                    $statusClass = 'bg-danger';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-secondary';
                                    break;
                                default:
                                    $statusClass = 'bg-light text-dark';
                                    break;
                            }
                            $invoiceStatusForDisplay = ucfirst(str_replace('_', ' ', $invoiceStatus));
                        }
                        ?>
                    <strong>Status:</strong>
                    <span class="badge <?php echo $statusClass; ?> fs-6">
                        <?php echo htmlspecialchars($invoiceStatusForDisplay); // Display formatted status ?>
                    </span>
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
                        <thead class="table-light">
                            <tr><th>#</th><th>Description</th><th>Category</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): $itemNumber = 1; foreach ($items as $item): ?>
                                <tr><td><?php echo $itemNumber++; ?></td><td><?php echo htmlspecialchars($item['description']); ?></td><td><?php echo htmlspecialchars($item['category_name']); ?></td><td class="text-end"><?php echo htmlspecialchars(number_format((float)$item['amount'], 2)); ?></td></tr>
                            <?php endforeach; else: ?><tr><td colspan="4">No items found for this invoice.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

             <div class="summary-section mb-4">
                 <div class="row justify-content-end">
                    <div class="col-md-5">
                         <table class="table table-sm table-borderless">
                             <tr><th class="text-end">Subtotal:</th><td class="text-end"><?php echo htmlspecialchars(number_format((float)($invoice['total_amount'] ?? 0.00), 2)); ?></td></tr>
                             <tr><th class="text-end">Total Discount:</th><td class="text-end text-danger">-<?php echo htmlspecialchars(number_format((float)($invoice['total_discount'] ?? 0.00), 2)); ?></td></tr>
                             <tr><th class="text-end border-top pt-2">Total Payable:</th><td class="text-end border-top pt-2"><?php echo htmlspecialchars(number_format((float)$invoice['total_payable'], 2)); ?></td></tr>
                             <tr><th class="text-end">Total Paid:</th><td class="text-end"><?php echo htmlspecialchars(number_format((float)$invoice['total_paid'], 2)); ?></td></tr>
                             <tr><th class="text-end fs-5">Balance Due:</th><td class="text-end fw-bold fs-5"><?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></td></tr>
                         </table>
                    </div>
                 </div>
             </div>

             <div class="discount-section mb-4 border rounded p-3">
                 <h5 class="mb-3">Applied Discounts / Concessions</h5>
                 <div class="applied-discounts mb-3">
                     <?php if (!empty($appliedDiscounts)): ?>
                         <ul class="list-group list-group-flush">
                             <?php foreach($appliedDiscounts as $discount): ?>
                                 <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0">
                                     <span>
                                         <strong><?php echo htmlspecialchars($discount['discount_name']); ?>:</strong>
                                         -<?php echo htmlspecialchars(number_format((float)$discount['applied_amount'], 2)); ?>
                                         <small class="text-muted ms-2">(<?php echo htmlspecialchars($discount['notes'] ?: 'No notes'); ?>)</small>
                                     </span>
                                     <form action="/sfms_project/public/admin/invoices/discounts/remove" method="POST" class="ms-2">
                                         <?php //echo $this-> csrfInput(); ?>
                                         <input type="hidden" name="invoice_discount_id" value="<?php echo $discount['invoice_discount_id']; ?>">
                                         <input type="hidden" name="invoice_id" value="<?php echo $invoice['invoice_id']; ?>">
                                         <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this discount? Invoice totals will be recalculated.');" title="Remove Discount"><i class="bi bi-x-lg"></i></button>
                                     </form>
                                 </li>
                             <?php endforeach; ?>
                         </ul>
                     <?php else: ?>
                         <p class="text-muted">No discounts applied yet.</p>
                     <?php endif; ?>
                 </div>

                 <div class="add-discount-form ">
                     <h6>Apply New Discount</h6>
                     <?php if (!empty($availableDiscounts)): ?>
                         <form action="/sfms_project/public/admin/invoices/<?php echo $invoice['invoice_id']; ?>/discounts/add" method="POST">
                             <?php //echo $this->csrfInput(); ?>
                             <div class="row g-2 align-items-end">
                                 <div class="col-md-5">
                                     <label for="discount_type_id" class="form-label">Discount Type:</label>
                                     <select id="discount_type_id" name="discount_type_id" required class="form-select form-select-sm">
                                         <option value="">-- Select --</option>
                                         <?php foreach ($availableDiscounts as $availDiscount): ?>
                                             <option value="<?php echo $availDiscount['discount_type_id']; ?>"><?php echo htmlspecialchars($availDiscount['name']); ?> (<?php echo $availDiscount['type'] == 'percentage' ? $availDiscount['value'].'%' : 'Fixed: '.number_format($availDiscount['value'], 2); ?>)</option>
                                         <?php endforeach; ?>
                                     </select>
                                 </div>
                                 <div class="col-md-3">
                                      <label for="applied_amount" class="form-label">Amount (Override):</label>
                                      <input type="number" id="applied_amount" name="applied_amount" step="0.01" min="0" class="form-control form-control-sm" placeholder="Default">
                                 </div>
                                 <div class="col-md-4">
                                      <label for="notes" class="form-label">Notes:</label>
                                      <input type="text" id="notes" name="notes" class="form-control form-control-sm">
                                 </div>
                                 <div class="col-12 mt-2">
                                     <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Apply Discount</button>
                                 </div>
                             </div>
                             <small class="form-text text-muted">Override amount or leave blank to use default/percentage.</small>
                         </form>
                     <?php else: ?>
                          <p class="text-muted small">No further discount types available to apply.</p>
                     <?php endif; ?>
                 </div>
             </div>
             <div class="payments-section">
                 <h3>Payments Received</h3>
                 <?php if (!empty($payments)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Date</th><th>Method</th><th>Receipt #</th><th>Reference</th><th class="text-end">Amount Paid</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                         <td><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($payment['payment_date']))); ?></td>
                                         <td><?php echo htmlspecialchars($payment['method_name']); ?></td>
                                         <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                                         <td><?php echo htmlspecialchars($payment['reference_number'] ?? ''); ?></td>
                                         <td class="text-end"><?php echo htmlspecialchars(number_format((float)$payment['amount_paid'], 2)); ?></td>
                                         <td class="actions text-nowrap"> <?php $isCompleted = isset($payment['payment_status']) && $payment['payment_status'] === 'completed'; $alreadyRefunded = isset($payment['refunded_amount']) && $payment['refunded_amount'] !== null && (float)$payment['refunded_amount'] >= (float)$payment['amount_paid']; if ($isCompleted && !$alreadyRefunded): ?> <a href="/sfms_project/public/admin/payments/<?php echo $payment['payment_id']; ?>/refund" class="btn btn-sm btn-warning" title="Record Refund"><i class="bi bi-arrow-counterclockwise"></i></a> <?php elseif (isset($payment['refunded_amount']) && $payment['refunded_amount'] > 0): ?> <span class="badge bg-info text-dark"><?php echo ($payment['payment_status'] == 'refunded') ? 'Refunded' : 'Partially Refunded'; ?> (<?php echo htmlspecialchars(number_format((float)$payment['refunded_amount'], 2)); ?>)</span> <?php else: ?> <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($payment['payment_status'] ?? 'Unknown')); ?></span> <?php endif; ?> </td>
                                     </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                 <?php else: ?>
                    <p>No payments recorded for this invoice yet.</p>
                 <?php endif; ?>
             </div>

        </div> <?php else: ?>
    <div class="alert alert-danger">Invoice details could not be loaded or invoice not found.</div>
    <a href="/sfms_project/public/admin/fees/invoices" class="btn btn-secondary">Back to List</a>
<?php endif; ?>