<?php
// File: app/Modules/UserManagement/Views/edit.php
// Included by layout_admin.php

// $pageTitle, $user, $currentUserRoles, $allRoles, $errors, $oldInput, $viewError passed from controller
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Edit User'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please correct the errors below:</strong>
        <ul><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
 <?php endif; ?>

<?php if (isset($user)): ?>
    <?php
        // Use $oldInput if errors exist for repopulation, otherwise use $user data
        $formData = !empty($errors) ? $oldInput : $user;
        $currentRoles = !empty($errors) ? ($oldInput['roles'] ?? []) : $currentUserRoles; // Use old input roles if errors exist
    ?>
    <form action="/sfms_project/public/admin/users/update/<?php echo $user['user_id']; ?>" method="POST">
        <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token ?? ''); ?>">
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label for="username" class="form-label">Username: (Read Only)</label>
                <input type="text" id="username" name="username_display" readonly disabled
                       value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                       class="form-control">
                 <input type="hidden" name="username" value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>">
            </div>
             <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" required maxlength="100"
                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
            </div>
             <div class="col-md-6 mb-3">
                <label for="password" class="form-label">New Password: (Leave blank to keep current)</label>
                <input type="password" id="password" name="password" minlength="6"
                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                 <div class="form-text">Minimum 6 characters if changing.</div>
                 <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
            </div>
             <div class="col-md-6 mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
                <?php if(isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
            </div>
             <div class="col-md-6 mb-3">
                <label for="full_name" class="form-label">Full Name:</label>
                <input type="text" id="full_name" name="full_name" maxlength="100"
                       value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                       class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['full_name'])): ?><div class="invalid-feedback"><?php echo $errors['full_name']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="contact_number" class="form-label">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" maxlength="20"
                       value="<?php echo htmlspecialchars($formData['contact_number'] ?? ''); ?>"
                       class="form-control <?php echo isset($errors['contact_number']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['contact_number'])): ?><div class="invalid-feedback"><?php echo $errors['contact_number']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status:</label>
                <select id="status" name="status" required class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                    <option value="active" <?php echo (isset($formData['status']) && $formData['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (isset($formData['status']) && $formData['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="pending_verification" <?php echo (isset($formData['status']) && $formData['status'] === 'pending_verification') ? 'selected' : ''; ?>>Pending Verification</option>
                    <option value="locked" <?php echo (isset($formData['status']) && $formData['status'] === 'locked') ? 'selected' : ''; ?>>Locked</option>
                </select>
                  <?php if(isset($errors['status'])): ?><div class="invalid-feedback"><?php echo $errors['status']; ?></div><?php endif; ?>
            </div>
             <div class="col-12 mb-3">
                 <label class="form-label">Assigned Roles:</label>
                 <div class="border p-2 rounded <?php echo isset($errors['roles']) ? 'is-invalid' : ''; ?>">
                     <?php if (isset($allRoles) && !empty($allRoles)): ?>
                         <?php foreach ($allRoles as $id => $name): ?>
                            <?php $isChecked = in_array($id, $currentRoles); ?>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="checkbox" id="role_<?php echo $id; ?>" name="roles[]" value="<?php echo $id; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                                 <label class="form-check-label" for="role_<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></label>
                             </div>
                         <?php endforeach; ?>
                     <?php else: ?>
                         <p class="text-muted">No roles defined in system.</p>
                     <?php endif; ?>
                 </div>
                  <?php if(isset($errors['roles'])): ?><div class="invalid-feedback d-block"><?php echo $errors['roles']; ?></div><?php endif; ?>
             </div>
        </div>

         <div class="mt-3">
             <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update User</button>
             <a href="/sfms_project/public/admin/users" class="btn btn-secondary">Cancel</a>
         </div>
    </form>

<?php else: // User not found ?>
     <div class="alert alert-danger">User not found.</div>
     <a href="/sfms_project/public/admin/users" class="btn btn-secondary">Back to List</a>
<?php endif; ?>