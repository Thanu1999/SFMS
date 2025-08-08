<?php
// File: app/Modules/StudentManagement/Views/create.php
// Included by layout_admin.php

// $pageTitle is set by controller and used by layout header
// $sessions, $classes, $parentUsers, $studentUsers passed by controller
// $errors, $oldInput passed on validation failure
// $viewError may contain DB error message from loading form data
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Add Student'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please correct the errors below:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; /* Errors might have HTML */ ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>


<form action="/sfms_project/public/admin/students" method="POST">
    <?php // Add CSRF token ?>

    <fieldset class="mb-3 border p-3 rounded">
        <legend class="w-auto px-2">Admission Details</legend>
         <div class="row g-3">
             <div class="col-md-6 mb-3">
                <label for="admission_number" class="form-label">Admission Number:</label>
                <input type="text" id="admission_number" name="admission_number_display" readonly value="[Auto-Generated]" class="form-control" disabled>
                <div class="form-text">Will be generated automatically upon saving.</div>
             </div>
             <div class="col-md-6 mb-3">
                 <label for="admission_date" class="form-label">Admission Date:</label>
                 <input type="date" id="admission_date" name="admission_date" required
                        value="<?php echo htmlspecialchars($oldInput['admission_date'] ?? date('Y-m-d')); ?>"
                        class="form-control <?php echo isset($errors['admission_date']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['admission_date'])): ?><div class="invalid-feedback"><?php echo $errors['admission_date']; ?></div><?php endif; ?>
             </div>
             <div class="col-md-6 mb-3">
                <label for="current_session_id" class="form-label">Academic Session:</label>
                <select id="current_session_id" name="current_session_id" required class="form-select <?php echo isset($errors['current_session_id']) ? 'is-invalid' : ''; ?>">
                    <option value="">-- Select Session --</option>
                    <?php foreach ($sessions ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($oldInput['current_session_id']) && $oldInput['current_session_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                 <?php if(isset($errors['current_session_id'])): ?><div class="invalid-feedback"><?php echo $errors['current_session_id']; ?></div><?php endif; ?>
             </div>
            <div class="col-md-6 mb-3">
                <label for="current_class_id" class="form-label">Class:</label>
                <select id="current_class_id" name="current_class_id" required class="form-select <?php echo isset($errors['current_class_id']) ? 'is-invalid' : ''; ?>">
                    <option value="">-- Select Class --</option>
                     <?php foreach ($classes ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($oldInput['current_class_id']) && $oldInput['current_class_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                 <?php if(isset($errors['current_class_id'])): ?><div class="invalid-feedback"><?php echo $errors['current_class_id']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="section" class="form-label">Section:</label>
                <input type="text" id="section" name="section" maxlength="20" placeholder="e.g., A, Blue"
                       value="<?php echo htmlspecialchars($oldInput['section'] ?? ''); ?>"
                       class="form-control <?php echo isset($errors['section']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['section'])): ?><div class="invalid-feedback"><?php echo $errors['section']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status:</label>
                <select id="status" name="status" required class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                    <option value="active" <?php echo (isset($oldInput['status']) && $oldInput['status'] === 'active') ? 'selected' : 'selected'; // Default active?>>Active</option>
                    <option value="inactive" <?php echo (isset($oldInput['status']) && $oldInput['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
                 <?php if(isset($errors['status'])): ?><div class="invalid-feedback"><?php echo $errors['status']; ?></div><?php endif; ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="mb-3 border p-3 rounded">
        <legend class="w-auto px-2">Personal Details</legend>
         <div class="row g-3">
             <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label">First Name:</label>
                <input type="text" id="first_name" name="first_name" required maxlength="50"
                       value="<?php echo htmlspecialchars($oldInput['first_name'] ?? ''); ?>"
                       class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>">
                <?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required maxlength="50"
                        value="<?php echo htmlspecialchars($oldInput['last_name'] ?? ''); ?>"
                        class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label for="middle_name" class="form-label">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" maxlength="50"
                        value="<?php echo htmlspecialchars($oldInput['middle_name'] ?? ''); ?>"
                        class="form-control <?php echo isset($errors['middle_name']) ? 'is-invalid' : ''; ?>">
                <?php if(isset($errors['middle_name'])): ?><div class="invalid-feedback"><?php echo $errors['middle_name']; ?></div><?php endif; ?>
            </div>
             <div class="col-md-6 mb-3">
                 <label for="date_of_birth" class="form-label">Date of Birth:</label>
                 <input type="date" id="date_of_birth" name="date_of_birth"
                        value="<?php echo htmlspecialchars($oldInput['date_of_birth'] ?? ''); ?>"
                        class="form-control <?php echo isset($errors['date_of_birth']) ? 'is-invalid' : ''; ?>">
                 <?php if(isset($errors['date_of_birth'])): ?><div class="invalid-feedback"><?php echo $errors['date_of_birth']; ?></div><?php endif; ?>
             </div>
             <div class="col-md-6 mb-3">
                 <label for="gender" class="form-label">Gender:</label>
                 <select id="gender" name="gender" class="form-select <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>">
                    <option value="">-- Select --</option>
                    <option value="Male" <?php echo (isset($oldInput['gender']) && $oldInput['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (isset($oldInput['gender']) && $oldInput['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo (isset($oldInput['gender']) && $oldInput['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                 </select>
                  <?php if(isset($errors['gender'])): ?><div class="invalid-feedback"><?php echo $errors['gender']; ?></div><?php endif; ?>
             </div>
             <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address: (Optional)</label>
                <input type="email" id="email" name="email" maxlength="100"
                       value="<?php echo htmlspecialchars($oldInput['email'] ?? ''); ?>"
                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>">
                <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
             </div>
        </div>
    </fieldset>

    <fieldset class="mb-3 border p-3 rounded">
         <legend class="w-auto px-2">Account Links (Optional)</legend>
          <div class="mb-3">
              <label for="user_id" class="form-label">Link Student Login Account:</label>
              <select id="user_id" name="user_id" class="form-select <?php echo isset($errors['user_id']) ? 'is-invalid' : ''; ?>">
                  <option value="">-- None (No Login) --</option>
                  <?php foreach ($studentUsers ?? [] as $id => $display): ?>
                      <option value="<?php echo $id; ?>" <?php echo (isset($oldInput['user_id']) && $oldInput['user_id'] == $id) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($display); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
              <div class="form-text">Link to an existing user account for the student to log in.</div>
              <?php if(isset($errors['user_id'])): ?><div class="invalid-feedback"><?php echo $errors['user_id']; ?></div><?php endif; ?>
          </div>
          </fieldset>

    <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Add Student</button>
        <a href="/sfms_project/public/admin/students" class="btn btn-secondary">Cancel</a>
    </div>
</form>