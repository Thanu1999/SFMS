<?php
// File: app/Modules/Portal/Views/offline_payment.php
// Included by layout_portal.php

// Variables passed: $pageTitle, $invoice, $bankDetails, $viewError, $flash_error
// Global flash handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Offline Payment Instructions'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (isset($flash_error) && $flash_error): ?><div class="alert alert-danger"><?php echo $flash_error; ?></div><?php endif; ?>
<?php if (isset($invoice) && $invoice): ?>
    <div class="alert alert-info" role="alert">
      <h4 class="alert-heading">Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h4>
      <p>Student: <?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></p>
      <hr>
      <p class="mb-0">Amount Due: <strong class="fs-5">Rs. <?php echo htmlspecialchars(number_format((float)$invoice['balance_due'], 2)); ?></strong></p>
    </div>


     <div class="card mb-4">
        <div class="card-header"><h3><i class="bi bi-bank me-2"></i>Bank Transfer / Deposit Details</h3></div>
        <div class="card-body">
             <p>Please make your payment to the following account:</p>
             <ul class="list-unstyled">
                <li><strong>Account Name:</strong> <?php echo htmlspecialchars($bankDetails['account_name'] ?? 'N/A'); ?></li>
                <li><strong>Account Number:</strong> <?php echo htmlspecialchars($bankDetails['account_number'] ?? 'N/A'); ?></li>
                <li><strong>Bank Name:</strong> <?php echo htmlspecialchars($bankDetails['bank_name'] ?? 'N/A'); ?></li>
                <li><strong>Branch Name:</strong> <?php echo htmlspecialchars($bankDetails['branch_name'] ?? 'N/A'); ?></li>
             </ul>
             <p class="mt-3"><strong>Reference:</strong> <br><span class="text-danger fw-bold"><?php echo htmlspecialchars($bankDetails['reference_info'] ?? 'Please include Student Admission Number or Invoice Number'); ?></span></p>
         </div>
     </div>

     <div class="card">
        <div class="card-header"><h3><i class="bi bi-upload me-2"></i>Upload Payment Proof</h3></div>
        <div class="card-body">
            <p>After making the payment, please upload a clear image (JPG, PNG) or PDF of your bank transfer receipt or deposit slip below.</p>
            <form action="/sfms_project/public/portal/payments/upload-proof" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token ?? ''); ?>">
                <input type="hidden" name="invoice_id" value="<?php echo htmlspecialchars($invoice['invoice_id']); ?>">
                 <div class="mb-3">
                    <label for="payment_proof" class="form-label">Select Proof File:</label>
                    <input class="form-control" type="file" id="payment_proof" name="payment_proof" required accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-text">(Allowed types: JPG, PNG, PDF. Max size: 5MB)</div>
                    <?php // Display file-specific errors if validation logic passes them back ?>
                    <?php if(isset($errors['payment_proof'])): ?><div class="invalid-feedback d-block"><?php echo $errors['payment_proof']; ?></div><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up-fill"></i> Upload Proof</button>
                <a href="/sfms_project/public/portal/fees?view_student_id=<?php echo $invoice['student_id']; // Maintain student context ?>" class="btn btn-secondary">Cancel</a>
            </form>
         </div>
     </div>

<?php elseif (!isset($viewError)): // Only show if not a DB error initially ?>
     <div class="alert alert-warning">Could not load invoice details for payment instructions.</div>
<?php endif; ?>