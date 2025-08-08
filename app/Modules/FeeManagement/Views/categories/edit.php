<?php
// File: app/Modules/FeeManagement/Views/categories/edit.php
// Included by layout_admin.php

// $pageTitle, $category, $errors, $oldInput, $viewError passed from controller
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Edit Fee Category'; ?></h1>

<?php if (isset($viewError) && $viewError): ?><div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Please correct the errors below:</strong>
        <ul><?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?></ul>
    </div>
 <?php endif; ?>

<?php if (isset($category)): ?>
    <?php
        // Use $oldInput if errors exist, otherwise use $category data
        $formData = !empty($errors) ? $oldInput : $category;
    ?>
    <form action="/sfms_project/public/admin/fees/categories/update/<?php echo $category['category_id']; ?>" method="POST">
        <?php  // CSRF Token ?>

        <div class="mb-3">
            <label for="category_name" class="form-label">Category Name:</label>
            <input type="text" id="category_name" name="category_name" required maxlength="100"
                   value="<?php echo htmlspecialchars($formData['category_name'] ?? ''); ?>"
                   class="form-control <?php echo isset($errors['category_name']) ? 'is-invalid' : ''; ?>">
            <?php if(isset($errors['category_name'])): ?><div class="invalid-feedback"><?php echo $errors['category_name']; ?></div><?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
            <?php if(isset($errors['description'])): ?><div class="invalid-feedback"><?php echo $errors['description']; ?></div><?php endif; ?>
        </div>

         <div class="mt-3">
             <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Category</button>
             <a href="/sfms_project/public/admin/fees/categories" class="btn btn-secondary">Cancel</a>
         </div>
    </form>

<?php else: // Category not found ?>
    <div class="alert alert-danger">Fee Category not found.</div>
    <a href="/sfms_project/public/admin/fees/categories" class="btn btn-secondary">Back to List</a>
<?php endif; ?>