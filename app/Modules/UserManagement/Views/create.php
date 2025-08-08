<?php
// File: app/Modules/UserManagement/Views/create.php
// Included by layout_admin.php

// $pageTitle, $roles, $errors, $oldInput, $viewError passed from controller
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Create User'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
         <strong>Please correct the errors below:</strong>
         <ul><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form action="/sfms_project/public/admin/users" method="POST">
    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token ?? ''); ?>">
    <div class="row g-3">
        <div class="col-md-6 mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" id="username" name="username" required maxlength="50"
                   value="<?php echo htmlspecialchars($oldInput['username'] ?? ''); ?>"
                   class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>">
             <?php if(isset($errors['username'])): ?><div class="invalid-feedback"><?php echo $errors['username']; ?></div><?php endif; ?>
        </div>
         <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" required maxlength="100"
                   value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>"
                   class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
             <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
        </div>
         <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" required minlength="6"
                   class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
            <div class="form-text">Minimum 6 characters.</div>
             <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
        </div>
         <div class="col-md-6 mb-3">
            <label for="confirm_password" class="form-label">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                    class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
             <?php if(isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
        </div>
         <div class="col-md-6 mb-3">
            <label for="full_name" class="form-label">Full Name:</label>
            <input type="text" id="full_name" name="full_name" maxlength="100"
                   value="<?php echo htmlspecialchars($oldInput['full_name'] ?? ''); ?>"
                   class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>">
              <?php if(isset($errors['full_name'])): ?><div class="invalid-feedback"><?php echo $errors['full_name']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="contact_number" class="form-label">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" maxlength="20"
                   value="<?php echo htmlspecialchars($oldInput['contact_number'] ?? ''); ?>"
                   class="form-control <?php echo isset($errors['contact_number']) ? 'is-invalid' : ''; ?>">
            <?php if(isset($errors['contact_number'])): ?><div class="invalid-feedback"><?php echo $errors['contact_number']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status:</label>
            <select id="status" name="status" required class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                <option value="active" <?php echo (isset($oldInput['status']) && $oldInput['status'] === 'active') ? 'selected' : 'selected'; ?>>Active</option>
                <option value="inactive" <?php echo (isset($oldInput['status']) && $oldInput['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                <option value="pending_verification" <?php echo (isset($oldInput['status']) && $oldInput['status'] === 'pending_verification') ? 'selected' : ''; ?>>Pending Verification</option>
             </select>
             <?php if(isset($errors['status'])): ?><div class="invalid-feedback"><?php echo $errors['status']; ?></div><?php endif; ?>
        </div>
        <div class="col-12 mb-3">
             <label class="form-label">Assign Roles:</label>
             <div class="border p-2 rounded <?php echo isset($errors['roles']) ? 'is-invalid' : ''; ?>">
                 <?php if (isset($roles) && !empty($roles)): ?>
                     <?php foreach ($roles as $id => $name): ?>
                         <div class="form-check form-check-inline">
                             <input class="form-check-input" type="checkbox" id="role_<?php echo $id; ?>" name="roles[]" value="<?php echo $id; ?>"
                                    <?php echo (isset($oldInput['roles']) && is_array($oldInput['roles']) && in_array($id, $oldInput['roles'])) ? 'checked' : ''; ?>>
                             <label class="form-check-label" for="role_<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></label>
                         </div>
                     <?php endforeach; ?>
                 <?php else: ?>
                     <p class="text-muted">No roles found in system.</p>
                 <?php endif; ?>
             </div>
             <?php if(isset($errors['roles'])): ?><div class="invalid-feedback d-block"><?php echo $errors['roles']; ?></div><?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Create User</button>
        <a href="/sfms_project/public/admin/users" class="btn btn-secondary">Cancel</a>
    </div>
</form>