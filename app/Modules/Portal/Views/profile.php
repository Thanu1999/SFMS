<?php
// File: app/Modules/Portal/Views/profile.php
// Included by layout_portal.php

// Variables passed: $pageTitle, $user, $viewError, $errors, $oldInput
// Global flash handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'My Profile'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
         <strong>Please correct the errors below:</strong>
         <ul><?php foreach ($errors as $field => $error): ?><li><?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$field))) // Make key readable ?>: <?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<?php if (isset($user)): ?>
    <?php
        // Use $oldInput if errors exist for repopulation, otherwise use $user data
        // Only apply to fields present in the 'Update Details' form
        $formData = !empty($errors) ? $oldInput : $user;
    ?>
    <div class="card mb-4 shadow-sm">
         <div class="card-header"><h2>Profile Details</h2></div>
         <div class="card-body">
            <form action="/sfms_project/public/portal/profile/update" method="POST" class="needs-validation" novalidate>
                <?php //echo $this->csrfInput(); // CSRF Token ?>
                <div class="mb-3">
                    <label class="form-label">Username:</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                     <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly disabled class="form-control">
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" maxlength="100"
                           value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                           class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>">
                    <?php if(isset($errors['full_name'])): ?><div class="invalid-feedback"><?php echo $errors['full_name']; ?></div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact Number:</label>
                     <input type="text" id="contact_number" name="contact_number" maxlength="20"
                            value="<?php echo htmlspecialchars($formData['contact_number'] ?? ''); ?>"
                            class="form-control <?php echo isset($errors['contact_number']) ? 'is-invalid' : ''; ?>">
                    <?php if(isset($errors['contact_number'])): ?><div class="invalid-feedback"><?php echo $errors['contact_number']; ?></div><?php endif; ?>
                </div>
                 <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Details</button>
            </form>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
         <div class="card-header"><h2>Change Password</h2></div>
         <div class="card-body">
             <form action="/sfms_project/public/portal/profile/change-password" method="POST" class="needs-validation" novalidate>
                  <?php //echo $this->csrfInput(); // CSRF Token ?>
                 <div class="mb-3">
                     <label for="current_password" class="form-label">Current Password:</label>
                     <input type="password" id="current_password" name="current_password" required
                            class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>">
                     <?php if(isset($errors['current_password'])): ?><div class="invalid-feedback"><?php echo $errors['current_password']; ?></div><?php endif; ?>
                 </div>
                 <div class="mb-3">
                     <label for="new_password" class="form-label">New Password:</label>
                     <input type="password" id="new_password" name="new_password" required minlength="6"
                            class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>">
                     <div class="form-text">Minimum 6 characters.</div>
                      <?php if(isset($errors['new_password'])): ?><div class="invalid-feedback"><?php echo $errors['new_password']; ?></div><?php endif; ?>
                 </div>
                 <div class="mb-3">
                      <label for="confirm_password" class="form-label">Confirm New Password:</label>
                      <input type="password" id="confirm_password" name="confirm_password" required
                             class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
                       <?php if(isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
                 </div>
                  <button type="submit" class="btn btn-warning"><i class="bi bi-key-fill"></i> Change Password</button>
             </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger">Could not load user profile.</div>
<?php endif; ?>