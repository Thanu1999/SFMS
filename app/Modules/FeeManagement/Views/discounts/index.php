<?php
// File: app/Modules/FeeManagement/Views/discounts/index.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Manage Discount Types'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<div class="mb-3">
    <a href="/sfms_project/public/admin/fees/discount-types/create" class="btn btn-success"><i class="bi bi-plus-circle-fill"></i> Add New Discount Type</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Type</th>
                <th class="text-end">Value</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($discountTypes) && !empty($discountTypes)): ?>
                <?php foreach ($discountTypes as $type): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($type['discount_type_id']); ?></td>
                        <td><?php echo htmlspecialchars($type['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($type['description'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type['type']))); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$type['value'], 2)) . ($type['type'] == 'percentage' ? '%' : ''); ?></td>
                        <td>
                             <span class="badge <?php echo $type['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                 <?php echo $type['is_active'] ? 'Active' : 'Inactive'; ?>
                             </span>
                        </td>
                        <td class="actions text-nowrap">
                            <a href="/sfms_project/public/admin/fees/discount-types/edit/<?php echo $type['discount_type_id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <form action="/sfms_project/public/admin/fees/discount-types/toggle/<?php echo $type['discount_type_id']; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to toggle the status?');">
                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token ?? ''); ?>">
                                <button type="submit" class="btn btn-sm <?php echo $type['is_active'] ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $type['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="bi <?php echo $type['is_active'] ? 'bi-toggle-off' : 'bi-toggle-on'; ?>"></i>
                                </button>
                            </form>
                            </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No discount types found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>