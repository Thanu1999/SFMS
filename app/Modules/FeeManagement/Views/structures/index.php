<?php
// File: app/Modules/FeeManagement/Views/structures/index.php
// Included by layout_admin.php
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Fee Structures'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<div class="mb-3">
    <a href="/sfms_project/public/admin/fees/structures/create" class="btn btn-success"><i class="bi bi-plus-circle-fill"></i> Add New Fee Structure</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Session</th>
                <th>Category</th>
                <th>Structure Name</th>
                <th>Class</th>
                <th class="text-end">Amount</th>
                <th>Frequency</th>
                <th>Due Day</th>
                <th>Late Fee</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($structures) && !empty($structures)): ?>
                <?php foreach ($structures as $structure): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($structure['structure_id']); ?></td>
                        <td><?php echo htmlspecialchars($structure['session_name']); ?></td>
                        <td><?php echo htmlspecialchars($structure['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($structure['structure_name']); ?></td>
                        <td><?php echo htmlspecialchars($structure['class_name'] ?? 'All'); ?></td>
                        <td class="text-end"><?php echo htmlspecialchars(number_format((float)$structure['amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($structure['frequency'])); ?></td>
                        <td><?php echo htmlspecialchars($structure['due_day'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $structure['late_fee_type']))); ?></td>
                        <td class="actions text-nowrap">
                             <a href="/sfms_project/public/admin/fees/structures/edit/<?php echo $structure['structure_id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                             <form action="/sfms_project/public/admin/fees/structures/delete/<?php echo $structure['structure_id']; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete structure \'<?php echo htmlspecialchars(addslashes($structure['structure_name'])); ?>\'? This might fail if invoices use it.');">
                                 <?php // Add CSRF token ?>
                                 <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                             </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">No fee structures found.</td> </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>