<?php
// File: app/Modules/FeeManagement/Views/payments/record_form.php
// Included by layout_admin.php

// $pageTitle, $invoice, $paymentMethods, $proofData, $proofId, $viewError, $errors, $oldInput passed
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Record Payment'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please correct the errors below:</strong>
        <ul><?php foreach ($errors as $field => $error): ?><li><?php echo htmlspecialchars($field); ?>: <?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
 <?php if (isset($invoice) && $invoice): ?>
    <?php
        // Use $oldInput if errors exist, otherwise calculate defaults
        $formData = !empty($errors) ? $oldInput : [];
        // Default amount logic needs careful check against balance and potential old input
        $defaultAmount = $invoice['balance_due'] > 0 ? number_format((float)$invoice['balance_due'], 2, '.', '') : '0.01';
        $amountValue = $formData['amount_paid'] ?? $defaultAmount;
        $dateValue = $formData['payment_date'] ?? date('Y-m-d');
        $methodIdValue = $formData['method_id'] ?? null;
        $referenceValue = $formData['reference_number'] ?? '';
        $notesValue = $formData['notes'] ?? (isset($proofData) ? "Verified against uploaded proof #" . htmlspecialchars($proofData['proof_id']) : '');
    ?>
    <div class="card mb-4">
        <div class="card-header"><h3>Invoice Details</h3></div>
        <div class="card-body row">
             <p class="col-md-6"><strong>Invoice #:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
             <p class="col-md-6"><strong>Student:</strong> <?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></p>
             <p class="col-md-6"><strong>Total Payable:</strong> <?php echo htmlspecialchars(number_format((float)$invoice['total_payable'], 2)); ?></p>
             <p class="col-md-6"><strong>Amount Paid:</strong> <?php echo htmlspecialchars(number_format((float)$invoice['total_paid'], 2)); ?></p>
             <p class="col-12"><strong>Balance Due:</strong> <strong style="color: red; font-size: 1.1em;"><?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></strong></p>
        </div>
    </div>
    <?php if(isset($proofData) && $proofData): ?>
        <div class="card mb-4 border-primary">
             <div class="card-header bg-primary text-white"><h3>Verifying Uploaded Proof (#<?php echo htmlspecialchars($proofData['proof_id']); ?>)</h3></div>
            <div class="card-body">
                <p>
                    Uploaded by: <?php echo htmlspecialchars($proofData['uploader_username'] ?? 'Unknown User'); ?> on
                    <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($proofData['uploaded_at']))); ?>
                </p>
                <p> Original Filename: <?php echo htmlspecialchars($proofData['file_name']); ?> </p>
                <p>
                     <a href="/sfms_project/public/admin/payments/proofs/view-file/<?php echo $proofData['proof_id']; ?>" target="_blank" class="btn btn-outline-primary"><i class="bi bi-eye"></i> View Uploaded Proof File</a>
                </p>
                <?php if (in_array(strtolower(pathinfo($proofData['file_name'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                     <img src="/sfms_project/public/admin/payments/proofs/view-file/<?php echo $proofData['proof_id']; ?>" alt="Payment Proof Preview" class="img-fluid border rounded mt-2" style="max-height: 400px;">
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
     <form action="/sfms_project/public/admin/payments" method="POST" class="needs-validation" novalidate>
        <?php //echo $this->csrfInput(); // CSRF Token ?>
        <input type="hidden" name="invoice_id" value="<?php echo htmlspecialchars($invoice['invoice_id']); ?>">
        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($invoice['student_id']); ?>">
        <?php if(isset($proofId) && $proofId): ?>
            <input type="hidden" name="verified_proof_id" value="<?php echo htmlspecialchars($proofId); ?>">
        <?php endif; ?>

         <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label for="amount_paid" class="form-label">Amount Paid:</label>
                <input type="number" id="amount_paid" name="amount_paid" required step="0.01" min="0.01"
                       value="<?php echo htmlspecialchars($amountValue); ?>"
                       class="form-control <?php echo isset($errors['amount_paid']) ? 'is-invalid' : ''; ?>">
                <div class="form-text">Verify amount against proof if applicable. Max possible: <?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></div>
                 <?php if(isset($errors['amount_paid'])): ?><div class="invalid-feedback"><?php echo $errors['amount_paid']; ?></div><?php endif; ?>
                 <?php if(isset($errors['form'])): ?><div class="text-danger small mt-1"><?php echo $errors['form']; ?></div><?php endif; // Display general form errors ?>

            </div>
            <div class="col-md-6 mb-3">
                <label for="payment_date" class="form-label">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" required
                       value="<?php echo htmlspecialchars(substr($dateValue, 0, 10)); // Extract date part ?>"
                       class="form-control <?php echo isset($errors['payment_date']) ? 'is-invalid' : ''; ?>">
                 <div class="form-text">Actual date payment was received/verified.</div>
                 <?php if(isset($errors['payment_date'])): ?><div class="invalid-feedback"><?php echo $errors['payment_date']; ?></div><?php endif; ?>
             </div>
             <div class="col-md-6 mb-3">
                <label for="method_id" class="form-label">Payment Method:</label>
                 <select id="method_id" name="method_id" required class="form-select <?php echo isset($errors['method_id']) ? 'is-invalid' : ''; ?>">
                    <option value="">-- Select Method --</option>
                     <?php foreach ($paymentMethods ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($methodIdValue == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if(isset($errors['method_id'])): ?><div class="invalid-feedback"><?php echo $errors['method_id']; ?></div><?php endif; ?>
             </div>
              <div class="col-md-6 mb-3">
                <label for="reference_number" class="form-label">Reference / Cheque No:</label>
                <input type="text" id="reference_number" name="reference_number" maxlength="100"
                       value="<?php echo htmlspecialchars($referenceValue); ?>"
                       class="form-control <?php echo isset($errors['reference_number']) ? 'is-invalid' : ''; ?>" placeholder="e.g., Bank Txn ID, Cheque #">
                 <?php if(isset($errors['reference_number'])): ?><div class="invalid-feedback"><?php echo $errors['reference_number']; ?></div><?php endif; ?>
             </div>
            <div class="col-12 mb-3">
                <label for="notes" class="form-label">Notes:</label>
                <textarea id="notes" name="notes" rows="3"
                          class="form-control <?php echo isset($errors['notes']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($notesValue); ?></textarea>
                 <?php if(isset($errors['notes'])): ?><div class="invalid-feedback"><?php echo $errors['notes']; ?></div><?php endif; ?>
             </div>
        </div>

        <div class="mt-3">
             <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Record Payment<?php if(isset($proofId) && $proofId) echo " & Verify Proof"; ?></button>
             <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $invoice['invoice_id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    <?php elseif (!isset($viewError)): // Only show if not a DB error initially ?>
    <div class="alert alert-warning">Invoice details could not be loaded.</div>
    <a href="/sfms_project/public/admin/fees/invoices" class="btn btn-secondary">Back to Invoices</a>
<?php endif; ?>