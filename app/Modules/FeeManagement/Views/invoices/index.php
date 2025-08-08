<?php
// File: app/Modules/FeeManagement/Views/invoices/index.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Fee Invoices'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<div class="mb-3">
    <a href="/sfms_project/public/admin/fees/invoices/generate" class="btn btn-info"><i class="bi bi-receipt"></i> Generate Invoices</a>
    </div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
         <thead class="table-light">
             <tr>
                 <th>Inv No.</th>
                 <th>Student</th>
                 <th>Class</th>
                 <th>Session</th>
                 <th>Issue Date</th>
                 <th>Due Date</th>
                 <th class="text-end">Payable</th>
                 <th class="text-end">Paid</th>
                 <th class="text-end">Balance</th>
                 <th>Status</th>
                 <th>Actions</th>
             </tr>
         </thead>
         <tbody>
             <?php if (isset($invoices) && !empty($invoices)): ?>
                 <?php foreach ($invoices as $invoice): ?>
                    <?php // Inside the foreach loop in invoices/index.php
                            $invoiceStatus = $invoice['status'];
                            $statusClass = ''; // Default empty
                    
                            // First check if overdue (and not already paid/cancelled)
                            if (in_array($invoiceStatus, ['unpaid', 'partially_paid', 'overdue']) && isset($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d')) {
                                $statusClass = 'bg-danger'; // Overdue uses danger background
                                $invoiceStatus = 'overdue'; // Force status text to overdue for display consistency
                            } else {
                                // Apply classes based on original status
                                switch ($invoiceStatus) {
                                    case 'paid':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'partially_paid':
                                        $statusClass = 'bg-warning text-dark';
                                        break; // Use text-dark with warning
                                    case 'unpaid':
                                        $statusClass = 'bg-danger';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'bg-secondary';
                                        break;
                                    // Add cases for other statuses if needed
                                    default:
                                        $statusClass = 'bg-light text-dark';
                                        break;
                                }
                            }
                    ?>
                    <tr>
                         <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                         <td><?php echo htmlspecialchars($invoice['last_name'] . ', ' . $invoice['first_name']); ?></td>
                         <td><?php echo htmlspecialchars($invoice['class_name'] ?? 'N/A'); ?></td>
                         <td><?php echo htmlspecialchars($invoice['session_name']); ?></td>
                         <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($invoice['issue_date']))); ?></td>
                         <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($invoice['due_date']))); ?></td>
                         <td class="text-end"><?php echo htmlspecialchars(number_format((float)$invoice['total_payable'], 2)); ?></td>
                         <td class="text-end"><?php echo htmlspecialchars(number_format((float)$invoice['total_paid'], 2)); ?></td>
                         <td class="text-end fw-bold"><?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></td>
                         <td>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $invoiceStatus))); // Display potentially modified status ?>
                            </span>
                        </td>
                         <td class="actions text-nowrap">
                             <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $invoice['invoice_id']; ?>" class="btn btn-sm btn-info me-1" title="View Details"><i class="bi bi-eye-fill"></i></a>
                             <?php if ($invoice['status'] !== 'paid' && $invoice['status'] !== 'cancelled'): ?>
                                 <a href="/sfms_project/public/admin/payments/record/<?php echo $invoice['invoice_id']; ?>" class="btn btn-sm btn-success" title="Record Payment"><i class="bi bi-currency-dollar"></i></a>
                             <?php endif; ?>
                         </td>
                     </tr>
                 <?php endforeach; ?>
             <?php else: ?>
                 <tr><td colspan="11" class="text-center">No invoices found. Try generating some.</td></tr> <?php endif; ?>
         </tbody>
    </table>
</div>