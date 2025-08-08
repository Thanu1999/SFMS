<?php
// File: app/Modules/FeeManagement/Views/payments/refund_form.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $payment, $invoiceId, $dbError, $errors, $oldInput
// Global flash messages handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Record Refund'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
         <strong>Please correct the errors below:</strong>
         <ul><?php foreach ($errors as $field => $error): ?><li><?php echo htmlspecialchars($field); ?>: <?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if (isset($payment) && $payment): ?>
    <?php
        // Use $oldInput if errors exist, otherwise calculate defaults
        $formData = !empty($errors) ? $oldInput : [];
        $refundableAmount = max(0, (float)($payment['amount_paid'] ?? 0) - (float)($payment['refunded_amount'] ?? 0));
        $amountValue = $formData['refund_amount'] ?? number_format($refundableAmount, 2, '.', '');
        $dateValue = $formData['refund_date'] ?? date('Y-m-d');
        $reasonValue = $formData['refund_reason'] ?? '';
    ?>
    <div class="card mb-4">
         <div class="card-header"><h3>Original Payment Details</h3></div>
         <div class="card-body row">
             <p class="col-md-6"><strong>Payment ID:</strong> <?php echo htmlspecialchars($payment['payment_id']); ?></p>
             <p class="col-md-6"><strong>Student:</strong> <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></p>
             <p class="col-md-6"><strong>Payment Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($payment['payment_date']))); ?></p>
             <p class="col-md-6"><strong>Receipt #:</strong> <?php echo htmlspecialchars($payment['receipt_number']); ?></p>
             <p class="col-md-6"><strong>Method:</strong> <?php echo htmlspecialchars($payment['method_name']); ?></p>
             <p class="col-md-6"><strong>Amount Paid:</strong> <?php echo htmlspecialchars(number_format((float)$payment['amount_paid'], 2)); ?></p>
             <?php if (isset($payment['refunded_amount']) && $payment['refunded_amount'] > 0): ?>
                 <p class="col-12"><strong>Already Refunded:</strong> <span class="badge bg-warning text-dark"><?php echo htmlspecialchars(number_format((float)$payment['refunded_amount'], 2)); ?></span></p>
             <?php endif; ?>
             <p class="col-12"><strong>Max Refundable:</strong> <strong class="text-primary fs-5"><?php echo htmlspecialchars(number_format($refundableAmount, 2)); ?></strong></p>
        </div>
    </div>
    <?php if ($refundableAmount > 0.001): // Only show form if amount is refundable ?>
        <form action="/sfms_project/public/admin/payments/refund" method="POST">
            <?php //echo $this->csrfInput(); // CSRF Token ?>
            <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($payment['payment_id']); ?>">

             <fieldset class="mb-3 border p-3 rounded">
                <legend class="w-auto px-2">Refund Details</legend>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="refund_amount" class="form-label">Amount to Refund:</label>
                        <input type="number" id="refund_amount" name="refund_amount" required step="0.01" min="0.01"
                               max="<?php echo htmlspecialchars(number_format($refundableAmount, 2, '.', '')); ?>"
                               value="<?php echo htmlspecialchars($amountValue); ?>"
                               class="form-control <?php echo isset($errors['refund_amount']) ? 'is-invalid' : ''; ?>">
                        <?php if(isset($errors['refund_amount'])): ?><div class="invalid-feedback"><?php echo $errors['refund_amount']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="refund_date" class="form-label">Refund Date:</label>
                        <input type="date" id="refund_date" name="refund_date" required
                               value="<?php echo htmlspecialchars($dateValue); ?>"
                               class="form-control <?php echo isset($errors['refund_date']) ? 'is-invalid' : ''; ?>">
                        <?php if(isset($errors['refund_date'])): ?><div class="invalid-feedback"><?php echo $errors['refund_date']; ?></div><?php endif; ?>
                    </div>
                     <div class="col-12 mb-3">
                        <label for="refund_reason" class="form-label">Reason for Refund (Optional):</label>
                        <textarea id="refund_reason" name="refund_reason" rows="3"
                                  class="form-control <?php echo isset($errors['refund_reason']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($reasonValue); ?></textarea>
                         <?php if(isset($errors['refund_reason'])): ?><div class="invalid-feedback"><?php echo $errors['refund_reason']; ?></div><?php endif; ?>
                    </div>
                </div>
             </fieldset>

            <div class="mt-3">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to process this refund? This action cannot be easily undone.');"><i class="bi bi-arrow-counterclockwise"></i> Process Refund</button>
                 <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $payment['invoice_id'] ?? ''; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info">This payment has already been fully refunded or has no refundable amount.</div>
         <a href="/sfms_project/public/admin/fees/invoices/view/<?php echo $payment['invoice_id'] ?? ''; ?>" class="btn btn-secondary">Back to Invoice</a>
    <?php endif; ?>

<?php elseif (!isset($viewError)): // Payment details not found (and no DB error) ?>
    <div class="alert alert-danger">Original payment details could not be loaded.</div>
     <a href="/sfms_project/public/admin/fees/invoices" class="btn btn-secondary">Back to Invoices</a>
<?php endif; ?>