<?php
// File: app/Modules/StudentManagement/Views/edit.php
// Included by layout_admin.php

// $pageTitle is set by controller and used by layout header
// $student, $sessions, $classes, $studentUsers, $linkedGuardians, $potentialGuardians passed by controller
// $errors, $oldInput passed on validation failure
// $viewError may contain DB error message from loading form data
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Edit Student'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<?php
     $errors = $_SESSION['form_errors'] ?? [];
     $oldInput = $_SESSION['old_input'] ?? [];
     unset($_SESSION['form_errors'], $_SESSION['old_input']);
 ?>
 <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
         <strong>Please correct the errors below:</strong>
         <ul><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
 <?php endif; ?>

<?php if (isset($student)): // Check if student data exists ?>
    <?php
        // Use $oldInput if errors exist, otherwise use $student data
        $formData = !empty($errors) ? $oldInput : $student;
    ?>
     <form action="/sfms_project/public/admin/students/update/<?php echo $student['student_id']; ?>" method="POST">
        <?php // Add CSRF token ?>

        <fieldset class="mb-3 border p-3 rounded">
            <legend class="w-auto px-2">Admission Details</legend>
             <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="admission_number" class="form-label">Admission Number:</label>
                    <input type="text" id="admission_number" name="admission_number_display" readonly value="<?php echo htmlspecialchars($formData['admission_number'] ?? ''); ?>" class="form-control" disabled>
                </div>
                 <div class="col-md-6 mb-3">
                     <label for="admission_date" class="form-label">Admission Date:</label>
                     <input type="date" id="admission_date" name="admission_date" required
                            value="<?php echo htmlspecialchars($formData['admission_date'] ?? date('Y-m-d')); ?>"
                            class="form-control <?php echo isset($errors['admission_date']) ? 'is-invalid' : ''; ?>">
                      <?php if(isset($errors['admission_date'])): ?><div class="invalid-feedback"><?php echo $errors['admission_date']; ?></div><?php endif; ?>
                      </div>
                <div class="col-md-6 mb-3">
                    <label for="current_session_id" class="form-label">Academic Session:</label>
                    <select id="current_session_id" name="current_session_id" required class="form-select <?php echo isset($errors['current_session_id']) ? 'is-invalid' : ''; ?>">
                         <option value="">-- Select Session --</option>
                         <?php foreach ($sessions ?? [] as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($formData['current_session_id']) && $formData['current_session_id'] == $id) ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $id; ?>" <?php echo (isset($formData['current_class_id']) && $formData['current_class_id'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                         <?php endforeach; ?>
                    </select>
                    <?php if(isset($errors['current_class_id'])): ?><div class="invalid-feedback"><?php echo $errors['current_class_id']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="section" class="form-label">Section:</label>
                    <input type="text" id="section" name="section" maxlength="20"
                           value="<?php echo htmlspecialchars($formData['section'] ?? ''); ?>"
                           class="form-control <?php echo isset($errors['section']) ? 'is-invalid' : ''; ?>">
                    <?php if(isset($errors['section'])): ?><div class="invalid-feedback"><?php echo $errors['section']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select id="status" name="status" required class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>">
                         <option value="active" <?php echo (isset($formData['status']) && $formData['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                         <option value="inactive" <?php echo (isset($formData['status']) && $formData['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                         <option value="graduated" <?php echo (isset($formData['status']) && $formData['status'] === 'graduated') ? 'selected' : ''; ?>>Graduated</option>
                         <option value="transferred_out" <?php echo (isset($formData['status']) && $formData['status'] === 'transferred_out') ? 'selected' : ''; ?>>Transferred Out</option>
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
                           value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>"
                           class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>">
                    <?php if(isset($errors['first_name'])): ?><div class="invalid-feedback"><?php echo $errors['first_name']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required maxlength="50"
                            value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>"
                            class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>">
                     <?php if(isset($errors['last_name'])): ?><div class="invalid-feedback"><?php echo $errors['last_name']; ?></div><?php endif; ?>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="middle_name" class="form-label">Middle Name:</label>
                    <input type="text" id="middle_name" name="middle_name" maxlength="50"
                            value="<?php echo htmlspecialchars($formData['middle_name'] ?? ''); ?>"
                            class="form-control <?php echo isset($errors['middle_name']) ? 'is-invalid' : ''; ?>">
                     <?php if(isset($errors['middle_name'])): ?><div class="invalid-feedback"><?php echo $errors['middle_name']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                     <label for="date_of_birth" class="form-label">Date of Birth:</label>
                     <input type="date" id="date_of_birth" name="date_of_birth"
                            value="<?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?>"
                            class="form-control <?php echo isset($errors['date_of_birth']) ? 'is-invalid' : ''; ?>">
                     <?php if(isset($errors['date_of_birth'])): ?><div class="invalid-feedback"><?php echo $errors['date_of_birth']; ?></div><?php endif; ?>
                 </div>
                 <div class="col-md-6 mb-3">
                     <label for="gender" class="form-label">Gender:</label>
                     <select id="gender" name="gender" class="form-select <?php echo isset($errors['gender']) ? 'is-invalid' : ''; ?>">
                        <option value="">-- Select --</option>
                        <option value="Male" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (isset($formData['gender']) && $formData['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                     </select>
                     <?php if(isset($errors['gender'])): ?><div class="invalid-feedback"><?php echo $errors['gender']; ?></div><?php endif; ?>
                 </div>
                 <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address: (Optional)</label>
                    <input type="email" id="email" name="email" maxlength="100"
                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
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
                     <?php // Option to keep current link selected ?>
                     <?php if (isset($formData['user_id']) && $formData['user_id']): ?>
                        <?php endif; ?>

                     <?php foreach ($studentUsers ?? [] as $id => $display): ?>
                         <option value="<?php echo $id; ?>" <?php echo (isset($formData['user_id']) && $formData['user_id'] == $id) ? 'selected' : ''; ?>>
                             <?php echo htmlspecialchars($display); ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                 <div class="form-text">Link to an existing user account for the student to log in.</div>
                  <?php if(isset($errors['user_id'])): ?><div class="invalid-feedback"><?php echo $errors['user_id']; ?></div><?php endif; ?>
             </div>
             <?php if (!empty($formData['user_id'])): ?>
                 <div class="mb-3">
                    <label for="password" class="form-label">New Password for Student Account: (Leave blank to keep current)</label>
                    <input type="password" id="password" name="password" minlength="6"
                           class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                     <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                </div>
              <?php else: ?>
                  <p class="text-muted"><small>Password cannot be set as no student login account is linked.</small></p>
              <?php endif; ?>
        </fieldset>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Student Details</button>
            <a href="/sfms_project/public/admin/students" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <div class="guardian-section mt-4 border-top pt-3">
         <h2>Linked Parents / Guardians</h2>
         <div class="guardian-list mb-3">
             <?php if (!empty($linkedGuardians)): ?>
                 <ul class="list-group">
                     <?php foreach($linkedGuardians as $link): ?>
                         <li class="list-group-item d-flex justify-content-between align-items-center">
                             <div>
                                 <strong><?php echo htmlspecialchars($link['full_name'] ?? $link['username']); ?></strong>
                                 (<?php echo htmlspecialchars($link['relationship_type'] ?: 'Guardian'); ?>)
                                 <br><small class="text-muted"><?php echo htmlspecialchars($link['email'] ?? 'No Email'); ?></small>
                             </div>
                             <form action="/sfms_project/public/admin/students/remove-guardian" method="POST" style="display:inline;">
                                 <?php // Add CSRF token ?>
                                 <input type="hidden" name="link_id" value="<?php echo $link['link_id']; ?>">
                                 <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                 <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove this guardian link?');"><i class="bi bi-trash"></i> Remove</button>
                             </form>
                         </li>
                     <?php endforeach; ?>
                 </ul>
             <?php else: ?>
                 <p>No parents or guardians are currently linked to this student.</p>
             <?php endif; ?>
         </div>

         <div class="add-guardian-form card card-body bg-light">
             <h4>Link New Parent/Guardian</h4>
             <?php if (!empty($potentialGuardians)): ?>
                 <form action="/sfms_project/public/admin/students/<?php echo $student['student_id']; ?>/add-guardian" method="POST">
                     <?php // Add CSRF token ?>
                     <div class="mb-3">
                         <label for="guardian_user_id" class="form-label">Select User (Role: Parent):</label>
                         <select id="guardian_user_id" name="user_id" required class="form-select <?php echo isset($errors['guardian_user_id']) ? 'is-invalid' : ''; // Need error handling from addGuardianLink ?>">
                            <option value="">-- Select User --</option>
                            <?php foreach ($potentialGuardians ?? [] as $id => $display): ?>
                                 <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($display); ?></option>
                             <?php endforeach; ?>
                         </select>
                          <?php if(isset($errors['guardian_user_id'])): ?><div class="invalid-feedback"><?php echo $errors['guardian_user_id']; ?></div><?php endif; ?>
                     </div>
                     <div class="mb-3">
                          <label for="relationship_type" class="form-label">Relationship:</label>
                          <input type="text" id="relationship_type" name="relationship_type" placeholder="e.g., Mother, Father, Guardian (Optional)" maxlength="50" class="form-control <?php echo isset($errors['relationship_type']) ? 'is-invalid' : ''; ?>">
                           <?php if(isset($errors['relationship_type'])): ?><div class="invalid-feedback"><?php echo $errors['relationship_type']; ?></div><?php endif; ?>
                     </div>
                     <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-link-45deg"></i> Add Guardian Link</button>
                 </form>
             <?php else: ?>
                  <p class="text-muted">No available Parent accounts found to link.</p>
             <?php endif; ?>
         </div>
    </div>
     <?php else: // Student not found ?>
    <div class="alert alert-danger">Student data not found.</div>
    <a href="/sfms_project/public/admin/students" class="btn btn-secondary">Back to List</a>
<?php endif; ?>