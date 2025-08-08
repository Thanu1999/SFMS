<?php
// File: app/Modules/FeeManagement/Views/payments/list_proofs.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Pending Payment Proofs'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Proof ID</th>
                <th>Uploaded At</th>
                <th>Student</th>
                <th>Invoice #</th>
                <th class="text-end">Balance Due</th>
                <th>Uploader</th>
                <th>Proof File</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($proofs) && !empty($proofs)): ?>
                <?php foreach ($proofs as $proof): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($proof['proof_id']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($proof['uploaded_at']))); ?></td>
                        <td><?php echo htmlspecialchars($proof['last_name'] . ', ' . $proof['first_name'] . ' (' . $proof['admission_number'] . ')'); ?></td>
                        <td><a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $proof['invoice_id']; ?>" target="_blank"><?php echo htmlspecialchars($proof['invoice_number']); ?></a></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$proof['balance_due'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($proof['uploader_username'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="/sfms_project/public/admin/payments/proofs/view-file/<?php echo $proof['proof_id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View Proof">
                                <i class="bi bi-file-earmark-arrow-down"></i> <?php echo htmlspecialchars($proof['file_name']); ?>
                            </a>
                        </td>
                        <td class="actions text-nowrap">
                            <a href="/sfms_project/public/admin/payments/record/<?php echo $proof['invoice_id']; ?>?proof_id=<?php echo $proof['proof_id']; ?>" class="btn btn-sm btn-success me-1" title="Approve & Record Payment"><i class="bi bi-check-circle-fill"></i> Approve</a>

                            <form action="/sfms_project/public/admin/payments/proofs/reject" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to REJECT this proof?');">
                                <?php //echo $this->csrfInput(); // CSRF Token ?>
                                <input type="hidden" name="proof_id" value="<?php echo $proof['proof_id']; ?>">
                                <div class="input-group input-group-sm d-inline-flex w-auto">
                                    <input type="text" name="admin_notes" class="form-control" placeholder="Reason (optional)" style="max-width: 150px;">
                                    <button type="submit" class="btn btn-danger" title="Reject Proof"><i class="bi bi-x-octagon-fill"></i> Reject</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr> <td colspan="8" class="text-center">No pending payment proofs found.</td> </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>