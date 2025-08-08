<?php
// File: app/Modules/FeeManagement/Views/categories/index.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Fee Categories'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<div class="mb-3">
    <a href="/sfms_project/public/admin/fees/categories/create" class="btn btn-success"><i class="bi bi-plus-circle-fill"></i> Add New Category</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($categories) && !empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($category['description'] ?? '')); // Use nl2br for descriptions ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($category['created_at']))); // Format date ?></td>
                        <td class="actions">
                            <a href="/sfms_project/public/admin/fees/categories/edit/<?php echo $category['category_id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <form action="/sfms_project/public/admin/fees/categories/delete/<?php echo $category['category_id']; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete category \'<?php echo htmlspecialchars(addslashes($category['category_name'])); ?>\'? This cannot be undone if no fee structures use it.');">
                                 <?php // Add CSRF token ?>
                                 <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                             </form>
                            </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No fee categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>