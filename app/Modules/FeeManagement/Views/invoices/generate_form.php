<?php
// File: app/Modules/FeeManagement/Views/invoices/generate_form.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Generate Invoices'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please correct the errors below:</strong>
        <ul><?php foreach ($errors as $field => $error): ?><li><?php echo htmlspecialchars($field); ?>: <?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<form action="/sfms_project/public/admin/fees/invoices/generate" method="POST">
    <?php //echo $this->csrfInput(); // CSRF Token ?>

    <p class="lead">Select criteria to generate invoices for active students.</p>
    <div class="row g-3">
        <div class="col-md-6 mb-3">
            <label for="session_id" class="form-label">Academic Session:</label>
            <select id="session_id" name="session_id" required class="form-select <?php echo isset($errors['session_id']) ? 'is-invalid' : ''; ?>">
                <option value="">-- Select Session --</option>
                <?php foreach ($sessions ?? [] as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo (isset($oldInput['session_id']) && $oldInput['session_id'] == $id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
             <?php if(isset($errors['session_id'])): ?><div class="invalid-feedback"><?php echo $errors['session_id']; ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label for="class_id" class="form-label">Class:</label>
            <select id="class_id" name="class_id" required class="form-select <?php echo isset($errors['class_id']) ? 'is-invalid' : ''; ?>">
                <option value="">-- Select Class --</option>
                 <?php foreach ($classes ?? [] as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo (isset($oldInput['class_id']) && $oldInput['class_id'] == $id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Invoices will be generated for all 'active' students in this class for the selected session.</div>
             <?php if(isset($errors['class_id'])): ?><div class="invalid-feedback"><?php echo $errors['class_id']; ?></div><?php endif; ?>
        </div>

         <div class="col-12 mb-3">
             <label class="form-label fw-bold">Select Fee Structure(s) to Generate Invoices For:</label>
             <div class="border p-3 rounded structure-list <?php echo isset($errors['structure_ids']) ? 'is-invalid' : ''; ?>" style="max-height: 300px; overflow-y: auto;">
                 <?php if (isset($structures) && !empty($structures)): ?>
                     <?php foreach ($structures as $structure): ?>
                         <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="structure_<?php echo $structure['structure_id']; ?>" name="structure_ids[]" value="<?php echo $structure['structure_id']; ?>"
                                    <?php echo (isset($oldInput['structure_ids']) && is_array($oldInput['structure_ids']) && in_array($structure['structure_id'], $oldInput['structure_ids'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="structure_<?php echo $structure['structure_id']; ?>">
                                <?php echo htmlspecialchars($structure['structure_name']); ?> (<?php echo htmlspecialchars(number_format((float)$structure['amount'], 2)); ?>)
                            </label>
                         </div>
                     <?php endforeach; ?>
                 <?php else: ?>
                     <p class="text-muted">No fee structures found. Please create fee structures first.</p>
                 <?php endif; ?>
             </div>
              <?php if(isset($errors['structure_ids'])): ?><div class="invalid-feedback d-block"><?php echo $errors['structure_ids']; ?></div><?php endif; ?>
             <div class="form-text">Select one or more structures. Invoices will only be generated for students/structures if they don't already exist for the selected session.</div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Generate invoices based on selection? This might take a moment.');"><i class="bi bi-receipt"></i> Generate Invoices</button>
         <a href="/sfms_project/public/admin/fees/invoices" class="btn btn-secondary">Cancel</a>
    </div>
</form>