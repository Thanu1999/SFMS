<?php
// File: app/Modules/Admin/Views/settings_form.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $settings, $viewError, $_csrf_token
// Global flash messages handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Settings'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<p class="lead">Manage system-wide configurations. Sensitive credentials (Database password, Mail password/key) must be set in the <code>.env</code> file.</p>

<form action="/sfms_project/public/admin/settings" method="POST">
    <?php echo $this->csrfInput(); // CSRF Token ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h4><i class="bi bi-gear-fill me-2"></i>General Settings</h4></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="school_name" class="form-label">School Name:</label>
                        <input type="text" id="school_name" name="school_name" class="form-control" value="<?php echo htmlspecialchars($settings['school_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="school_address" class="form-label">School Address:</label>
                        <textarea id="school_address" name="school_address" rows="3" class="form-control"><?php echo htmlspecialchars($settings['school_address'] ?? ''); ?></textarea>
                    </div>
                     <div class="mb-3">
                        <label for="school_contact" class="form-label">School Contact (Phone/Email):</label>
                        <input type="text" id="school_contact" name="school_contact" class="form-control" value="<?php echo htmlspecialchars($settings['school_contact'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="currency_symbol" class="form-label">Currency Symbol:</label>
                        <input type="text" id="currency_symbol" name="currency_symbol" class="form-control" style="width: 100px;" value="<?php echo htmlspecialchars($settings['currency_symbol'] ?? ''); ?>">
                    </div>
                </div>
            </div>

             <div class="card shadow-sm mb-4">
                 <div class="card-header"><h4><i class="bi bi-envelope-at me-2"></i>Notification Settings</h4></div>
                 <div class="card-body">
                      <div class="mb-3">
                         <label for="mail_from_name" class="form-label">Email From Name:</label>
                         <input type="text" id="mail_from_name" name="mail_from_name" class="form-control" value="<?php echo htmlspecialchars($settings['mail_from_name'] ?? ''); ?>">
                         <div class="form-text">The name emails appear to be sent from. Address & credentials are set in <code>.env</code> file.</div>
                     </div>
                     <hr>
                      <p><strong>Fee Reminders:</strong></p>
                      <div class="mb-3">
                         <label for="reminder_days_before_due" class="form-label">Send 'Due Soon' Reminder (Days Before):</label>
                         <input type="number" id="reminder_days_before_due" name="reminder_days_before_due" class="form-control" style="width: 100px;" min="0" value="<?php echo htmlspecialchars($settings['reminder_days_before_due'] ?? ''); ?>">
                     </div>
                     <div class="mb-3">
                         <label for="reminder_days_after_due" class="form-label">Send 'Overdue' Reminder (Days After):</label>
                         <input type="number" id="reminder_days_after_due" name="reminder_days_after_due" class="form-control" style="width: 100px;" min="0" value="<?php echo htmlspecialchars($settings['reminder_days_after_due'] ?? ''); ?>">
                     </div>
                      <div class="mb-3">
                         <label for="reminder_cooldown_days" class="form-label">Reminder Cooldown (Days):</label>
                         <input type="number" id="reminder_cooldown_days" name="reminder_cooldown_days" class="form-control" style="width: 100px;" min="1" value="<?php echo htmlspecialchars($settings['reminder_cooldown_days'] ?? ''); ?>">
                         <div class="form-text">Min days between sending reminders for the same invoice.</div>
                     </div>
                 </div>
            </div>

        </div><div class="col-lg-6">
             <div class="card shadow-sm mb-4">
                 <div class="card-header"><h4><i class="bi bi-bank me-2"></i>Bank Details (for Offline Payments)</h4></div>
                 <div class="card-body">
                     <div class="mb-3">
                         <label for="bank_account_name" class="form-label">Account Name:</label>
                         <input type="text" id="bank_account_name" name="bank_account_name" class="form-control" value="<?php echo htmlspecialchars($settings['bank_account_name'] ?? ''); ?>">
                     </div>
                     <div class="mb-3">
                          <label for="bank_account_number" class="form-label">Account Number:</label>
                          <input type="text" id="bank_account_number" name="bank_account_number" class="form-control" value="<?php echo htmlspecialchars($settings['bank_account_number'] ?? ''); ?>">
                     </div>
                     <div class="mb-3">
                          <label for="bank_name" class="form-label">Bank Name:</label>
                          <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?php echo htmlspecialchars($settings['bank_name'] ?? ''); ?>">
                     </div>
                      <div class="mb-3">
                          <label for="bank_branch" class="form-label">Branch Name:</label>
                          <input type="text" id="bank_branch" name="bank_branch" class="form-control" value="<?php echo htmlspecialchars($settings['bank_branch'] ?? ''); ?>">
                     </div>
                      <div class="mb-3">
                          <label for="bank_reference_info" class="form-label">Reference Info (Instructions for Payer):</label>
                          <textarea id="bank_reference_info" name="bank_reference_info" rows="3" class="form-control"><?php echo htmlspecialchars($settings['bank_reference_info'] ?? ''); ?></textarea>
                     </div>
                 </div>
             </div>

              </div></div><div class="mt-3">
        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-save"></i> Save All Settings</button>
    </div>
</form>