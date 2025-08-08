<?php
// File: app/Modules/FeeManagement/Views/structures/edit.php
// Included by layout_admin.php

// $pageTitle, $structure, $sessions, $categories, $classes, $errors, $oldInput, $viewError passed
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Edit Fee Structure'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
         <strong>Please correct the errors below:</strong>
         <ul><?php foreach ($errors as $field => $error): ?><li><?php echo htmlspecialchars($field); ?>: <?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if (isset($structure)): ?>
    <?php
         // Use $oldInput if errors exist, otherwise use $structure data
         $formData = !empty($errors) ? $oldInput : $structure;
    ?>
    <form action="/sfms_project/public/admin/fees/structures/update/<?php echo $structure['structure_id']; ?>" method="POST">
        <?php  // CSRF Token ?>

         <fieldset class="mb-3 border p-3 rounded">
            <legend class="w-auto px-2">Basic Information</legend>
             <div class="row g-3">
                 <div class="col-md-6 mb-3">
                    <label for="session_id" class="form-label">Academic Session:</label>
                    <select id="session_id" name="session_id" required class="form-select <?php echo isset($errors['session_id']) ? 'is-invalid' : ''; ?>">
                        <option value="">-- Select Session --</option>
                        <?php foreach ($sessions ?? [] as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($formData['session_id']) && $formData['session_id'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                     <?php if(isset($errors['session_id'])): ?><div class="invalid-feedback"><?php echo $errors['session_id']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Fee Category:</label>
                    <select id="category_id" name="category_id" required class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>">
                         <option value="">-- Select Category --</option>
                         <?php foreach ($categories ?? [] as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($formData['category_id']) && $formData['category_id'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                         <?php endforeach; ?>
                    </select>
                     <?php if(isset($errors['category_id'])): ?><div class="invalid-feedback"><?php echo $errors['category_id']; ?></div><?php endif; ?>
                </div>
                 <div class="col-md-12 mb-3">
                    <label for="structure_name" class="form-label">Structure Name:</label>
                    <input type="text" id="structure_name" name="structure_name" required maxlength="150"
                           value="<?php echo htmlspecialchars($formData['structure_name'] ?? ''); ?>"
                           class="form-control <?php echo isset($errors['structure_name']) ? 'is-invalid' : ''; ?>">
                    <?php if(isset($errors['structure_name'])): ?><div class="invalid-feedback"><?php echo $errors['structure_name']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea id="description" name="description" rows="2"
                              class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                    <?php if(isset($errors['description'])): ?><div class="invalid-feedback"><?php echo $errors['description']; ?></div><?php endif; ?>
                </div>
             </div>
         </fieldset>

         <fieldset class="mb-3 border p-3 rounded">
             <legend class="w-auto px-2">Applicability & Amount</legend>
             <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="applicable_class_id" class="form-label">Applicable Class:</label>
                     <select id="applicable_class_id" name="applicable_class_id" class="form-select <?php echo isset($errors['applicable_class_id']) ? 'is-invalid' : ''; ?>">
                        <option value="">-- All Classes --</option>
                         <?php foreach ($classes ?? [] as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($formData['applicable_class_id']) && $formData['applicable_class_id'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(isset($errors['applicable_class_id'])): ?><div class="invalid-feedback"><?php echo $errors['applicable_class_id']; ?></div><?php endif; ?>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Amount:</label>
                    <input type="number" id="amount" name="amount" required step="0.01" min="0"
                           value="<?php echo htmlspecialchars($formData['amount'] ?? ''); ?>"
                           class="form-control <?php echo isset($errors['amount']) ? 'is-invalid' : ''; ?>">
                    <?php if(isset($errors['amount'])): ?><div class="invalid-feedback"><?php echo $errors['amount']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="frequency" class="form-label">Frequency:</label>
                     <select id="frequency" name="frequency" required class="form-select <?php echo isset($errors['frequency']) ? 'is-invalid' : ''; ?>">
                         <option value="one-time" <?php echo (isset($formData['frequency']) && $formData['frequency'] === 'one-time') ? 'selected' : ''; ?>>One-time</option>
                         <option value="monthly" <?php echo (isset($formData['frequency']) && $formData['frequency'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                         <option value="quarterly" <?php echo (isset($formData['frequency']) && $formData['frequency'] === 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                         <option value="semi-annual" <?php echo (isset($formData['frequency']) && $formData['frequency'] === 'semi-annual') ? 'selected' : ''; ?>>Semi-Annual</option>
                         <option value="annual" <?php echo (isset($formData['frequency']) && $formData['frequency'] === 'annual') ? 'selected' : ''; ?>>Annual</option>
                         <option value="per_term" <?php echo (isset($formData['frequency']) && $formData['frequency'] === 'per_term') ? 'selected' : ''; ?>>Per Term</option>
                    </select>
                    <?php if(isset($errors['frequency'])): ?><div class="invalid-feedback"><?php echo $errors['frequency']; ?></div><?php endif; ?>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="due_day" class="form-label">Due Day (for recurring fees):</label>
                    <input type="number" id="due_day" name="due_day" min="1" max="31" placeholder="e.g., 10"
                           value="<?php echo htmlspecialchars($formData['due_day'] ?? ''); ?>"
                           class="form-control <?php echo isset($errors['due_day']) ? 'is-invalid' : ''; ?>">
                    <div class="form-text">Day of the month/term/etc. the fee is due. Leave blank if not applicable.</div>
                     <?php if(isset($errors['due_day'])): ?><div class="invalid-feedback"><?php echo $errors['due_day']; ?></div><?php endif; ?>
                </div>
             </div>
         </fieldset>

        <fieldset class="mb-3 border p-3 rounded">
            <legend class="w-auto px-2">Late Fee Policy</legend>
             <div class="row g-3">
                 <div class="col-md-6 mb-3">
                    <label for="late_fee_type" class="form-label">Late Fee Type:</label>
                    <select id="late_fee_type" name="late_fee_type" class="form-select <?php echo isset($errors['late_fee_type']) ? 'is-invalid' : ''; ?>">
                        <option value="none" <?php echo (isset($formData['late_fee_type']) && $formData['late_fee_type'] === 'none') ? 'selected' : ''; ?>>None</option>
                        <option value="fixed" <?php echo (isset($formData['late_fee_type']) && $formData['late_fee_type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                        <option value="percentage_per_day" <?php echo (isset($formData['late_fee_type']) && $formData['late_fee_type'] === 'percentage_per_day') ? 'selected' : ''; ?>>Percentage Per Day</option>
                        <option value="fixed_after_days" <?php echo (isset($formData['late_fee_type']) && $formData['late_fee_type'] === 'fixed_after_days') ? 'selected' : ''; ?>>Fixed Amount After X Days</option>
                    </select>
                    <?php if(isset($errors['late_fee_type'])): ?><div class="invalid-feedback"><?php echo $errors['late_fee_type']; ?></div><?php endif; ?>
                 </div>
                 <div class="col-md-6 mb-3">
                     <label for="late_fee_amount" class="form-label">Late Fee Amount / Percentage:</label>
                     <input type="number" id="late_fee_amount" name="late_fee_amount" step="0.01" min="0"
                            value="<?php echo htmlspecialchars($formData['late_fee_amount'] ?? '0.00'); ?>"
                            class="form-control <?php echo isset($errors['late_fee_amount']) ? 'is-invalid' : ''; ?>">
                     <div class="form-text">Amount if Fixed, Percentage (e.g., 0.5 for 0.5%) if Percentage.</div>
                     <?php if(isset($errors['late_fee_amount'])): ?><div class="invalid-feedback"><?php echo $errors['late_fee_amount']; ?></div><?php endif; ?>
                 </div>
                <div class="col-md-6 mb-3">
                     <label for="late_fee_calculation_basis" class="form-label">Late Fee Basis (Days):</label>
                     <input type="number" id="late_fee_calculation_basis" name="late_fee_calculation_basis" min="0"
                            value="<?php echo htmlspecialchars($formData['late_fee_calculation_basis'] ?? '0'); ?>"
                            class="form-control <?php echo isset($errors['late_fee_calculation_basis']) ? 'is-invalid' : ''; ?>">
                     <div class="form-text">Number of days after due date for 'Fixed After Days' type.</div>
                     <?php if(isset($errors['late_fee_calculation_basis'])): ?><div class="invalid-feedback"><?php echo $errors['late_fee_calculation_basis']; ?></div><?php endif; ?>
                </div>
            </div>
        </fieldset>

         <div class="mt-3">
             <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Structure</button>
             <a href="/sfms_project/public/admin/fees/structures" class="btn btn-secondary">Cancel</a>
         </div>
    </form>
<?php else: ?>
    <div class="alert alert-danger">Fee Structure not found.</div>
    <a href="/sfms_project/public/admin/fees/structures" class="btn btn-secondary">Back to List</a>
<?php endif; ?>