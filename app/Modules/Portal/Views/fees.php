<?php
// File: app/Modules/Portal/Views/fees.php
// Included by layout_portal.php

// Variables passed: $pageTitle, $student, $linkedStudents, $selectedStudentId, $invoices, $payments, $viewError, $flash_error, $flash_success
// Global flash handled by layout header - local flash vars $flash_error, $flash_success are passed for specific messages AFTER redirect TO this page.
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'My Fees'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<?php if (isset($flash_error) && $flash_error): ?>
    <div class="alert alert-warning"><?php echo $flash_error; ?></div>
<?php endif; ?>
<?php if (isset($linkedStudents) && count($linkedStudents) > 1 && (isset($_SESSION['roles']) && in_array('Parent', $_SESSION['roles']))): ?>
    <div class="card bg-light mb-4 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-start flex-wrap gap-2">
            <form action="/sfms_project/public/portal/fees" method="GET" class="d-flex align-items-center mb-0" id="studentSelectorForm">
                 <label for="view_student_id" class="form-label fw-bold me-2 mb-0">Viewing Child:</label>
                 <select id="view_student_id" name="view_student_id" class="form-select form-select-sm select2-enable" onchange="this.form.submit()" style="width: auto; min-width: 250px;">
                    <?php foreach ($linkedStudents as $child): ?>
                        <option value="<?php echo $child['student_id']; ?>" <?php echo (isset($selectedStudentId) && $child['student_id'] == $selectedStudentId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name'] . ' (' . $child['admission_number'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </form>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($student) && $student): ?>
     <h3 class="mb-3">Fee Invoices for <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
     <div class="table-responsive mb-4">
         <table class="table table-striped table-hover table-bordered table-sm">
             <thead class="table-light">
                 <tr>
                     <th>Inv #</th>
                     <th>Description</th>
                     <th>Issue Date</th>
                     <th>Due Date</th>
                     <th class="text-end">Amount</th>
                     <th class="text-end">Paid</th>
                     <th class="text-end">Balance</th>
                     <th>Status</th>
                     <th>Actions</th>
                 </tr>
             </thead>
             <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach($invoices as $invoice): ?>
                        <?php
                            $statusClass = ''; $displayStatus = ucfirst(str_replace('_', ' ', $invoice['status']));
                            if (in_array($invoice['status'], ['unpaid', 'partially_paid', 'overdue']) && isset($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d')) { $statusClass = 'bg-danger'; $displayStatus = 'Overdue';
                            } else { switch ($invoice['status']) { case 'paid': $statusClass = 'bg-success'; break; case 'partially_paid': $statusClass = 'bg-warning text-dark'; break; case 'unpaid': $statusClass = 'bg-danger'; break; case 'cancelled': $statusClass = 'bg-secondary'; break; default: $statusClass = 'bg-light text-dark'; break; } }
                        ?>
                         <tr>
                             <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                             <td><?php echo htmlspecialchars($invoice['description']); ?></td>
                             <td><?php echo date('Y-m-d', strtotime($invoice['issue_date'])); ?></td>
                             <td><?php echo date('Y-m-d', strtotime($invoice['due_date'])); ?></td>
                             <td class="text-end"><?php echo number_format((float)$invoice['total_payable'], 2); ?></td>
                             <td class="text-end"><?php echo number_format((float)$invoice['total_paid'], 2); ?></td>
                             <td class="text-end fw-bold"><?php echo number_format((float)$invoice['balance_due'], 2); ?></td>
                             <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($displayStatus); ?></span></td>
                             <td class="actions text-nowrap">
                                 <a href="/sfms_project/public/portal/invoices/view/<?php echo $invoice['invoice_id']; ?>?view_student_id=<?php echo $selectedStudentId; ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="bi bi-eye"></i> View</a>
                                 <?php if ($invoice['status'] != 'paid' && $invoice['status'] != 'cancelled'): ?>
                                     <a href="/sfms_project/public/portal/payments/offline/<?php echo $invoice['invoice_id']; ?>?view_student_id=<?php echo $selectedStudentId; ?>" class="btn btn-sm btn-outline-success ms-1" title="Pay Offline / Upload Proof"><i class="bi bi-upload"></i> Upload Proof</a>
                                     <?php endif; ?>
                             </td>
                         </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center text-muted">No invoices found for this student.</td></tr>
                <?php endif; ?>
             </tbody>
         </table>
     </div>

     <h3 class="mb-3">Payment History</h3>
     <div class="table-responsive">
         <table class="table table-striped table-hover table-bordered table-sm">
              <thead class="table-light">
                 <tr>
                     <th>Date</th> <th>Receipt #</th> <th>Method</th> <th>Reference</th> <th>Notes</th> <th class="text-end">Amount Paid</th> <th>For Invoice</th>
                 </tr>
             </thead>
             <tbody>
                <?php if (!empty($payments)): ?>
                     <?php foreach($payments as $payment): ?>
                         <tr>
                             <td><?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                             <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                             <td><?php echo htmlspecialchars($payment['method_name']); ?></td>
                             <td><?php echo htmlspecialchars($payment['reference_number'] ?? ''); ?></td>
                             <td><?php echo nl2br(htmlspecialchars($payment['notes'] ?? '')); ?></td>
                             <td class="text-end"><?php echo number_format((float)$payment['amount_paid'], 2); ?></td>
                             <td><?php echo htmlspecialchars($payment['invoice_number'] ?? 'N/A'); ?></td>
                         </tr>
                     <?php endforeach; ?>
                 <?php else: ?>
                     <tr><td colspan="7" class="text-center text-muted">No payment history found for this student.</td></tr>
                 <?php endif; ?>
             </tbody>
         </table>
     </div>

<?php elseif (!isset($viewError)): // No student selected and no DB error ?>
     <div class="alert alert-info">Please select a student<?php echo (isset($linkedStudents) && count($linkedStudents) > 1) ? ' from the dropdown above' : ''; ?> to view fee details.</div>
<?php endif; ?>