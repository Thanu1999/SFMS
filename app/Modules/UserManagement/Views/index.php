<?php
// File: app/Modules/UserManagement/Views/index.php
// Included by layout_admin.php

// $pageTitle is set by controller and used by layout header
// $users array is passed by controller
// $viewError may contain DB error message
// Global flash messages are handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'User Management'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="/sfms_project/public/admin/users/create" class="btn btn-success"><i class="bi bi-plus-circle-fill"></i> Add New User</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Full Name</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($users) && !empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                         <td>
                            <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars(ucfirst($user['status'])); ?>
                            </span>
                         </td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user['created_at']))); ?></td>
                        <td class="actions">
                            <a href="/sfms_project/public/admin/users/edit/<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['user_id']): // Prevent self-delete button ?>
                            <form action="/sfms_project/public/admin/users/delete/<?php echo $user['user_id']; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user <?php echo htmlspecialchars(addslashes($user['username'])); ?>?');">
                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_csrf_token ?? ''); ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>