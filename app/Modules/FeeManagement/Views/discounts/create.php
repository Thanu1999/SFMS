<?php
// File: app/Modules/FeeManagement/Views/discounts/create.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Create Discount Type'; ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
         <strong>Please correct the errors below:</strong>
         <ul><?php foreach ($errors as $field => $error): ?><li><?php echo htmlspecialchars($field); ?>: <?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<form action="/sfms_project/public/admin/fees/discount-types" method="POST" class="needs-validation" novalidate>
    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token ?? ''); ?>">

    <div class="row g-3">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Discount Name:</label>
            <input type="text" id="name" name="name" required maxlength="100" placeholder="e.g., Sibling Discount, Staff Ward"
                   value="<?php echo htmlspecialchars($oldInput['name'] ?? ''); ?>"
                   class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>">
             <?php if(isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
        </div>

        <div class="col-md-6 mb-3">
             <label for="type" class="form-label">Discount Type:</label>
             <select id="type" name="type" required class="form-select <?php echo isset($errors['type']) ? 'is-invalid' : ''; ?>">
                <option value="fixed_amount" <?php echo (isset($oldInput['type']) && $oldInput['type'] === 'fixed_amount') ? 'selected' : ''; ?>>Fixed Amount</option>
                <option value="percentage" <?php echo (isset($oldInput['type']) && $oldInput['type'] === 'percentage') ? 'selected' : ''; ?>>Percentage</option>
             </select>
             <?php if(isset($errors['type'])): ?><div class="invalid-feedback"><?php echo $errors['type']; ?></div><?php endif; ?>
         </div>

         <div class="col-md-6 mb-3">
             <label for="value" class="form-label">Value:</label>
             <input type="number" id="value" name="value" required step="0.01" min="0"
                    value="<?php echo htmlspecialchars($oldInput['value'] ?? ''); ?>"
                    class="form-control <?php echo isset($errors['value']) ? 'is-invalid' : ''; ?>">
             <div class="form-text">Enter the amount (e.g., 500.00) or percentage (e.g., 10 for 10%).</div>
             <?php if(isset($errors['value'])): ?><div class="invalid-feedback"><?php echo $errors['value']; ?></div><?php endif; ?>
         </div>

          <div class="col-12 mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($oldInput['description'] ?? ''); ?></textarea>
            <?php if(isset($errors['description'])): ?><div class="invalid-feedback"><?php echo $errors['description']; ?></div><?php endif; ?>
         </div>

          <div class="col-12 mb-3">
            <div class="form-check">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                       <?php echo (isset($oldInput['is_active']) && $oldInput['is_active']) || !isset($oldInput) ? 'checked' : ''; // Default checked on create ?>>
                <label for="is_active" class="form-check-label"> Active</label>
             </div>
         </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Create Discount Type</button>
        <a href="/sfms_project/public/admin/fees/discount-types" class="btn btn-secondary">Cancel</a>
    </div>
</form>