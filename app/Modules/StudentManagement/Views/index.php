<?php
// File: app/Modules/StudentManagement/Views/index.php
// Included by layout_admin.php

// $pageTitle is set by controller and used by layout header
// $students array is passed by controller
// $viewError may contain DB error message
// Global flash messages are handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Students'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="/sfms_project/public/admin/students/create" class="btn btn-success"><i class="bi bi-plus-circle-fill"></i> Add New Student</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Adm No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Class</th>
                <th>Section</th>
                <th>Session</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($students) && !empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($student['admission_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ($student['middle_name'] ? ' ' . $student['middle_name'] : '')); ?></td>
                        <td><?php echo htmlspecialchars($student['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['section'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['session_name'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge <?php echo $student['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars(ucfirst($student['status'])); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="/sfms_project/public/admin/students/edit/<?php echo $student['student_id']; ?>" class="btn btn-sm btn-primary me-1" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <form action="/sfms_project/public/admin/students/delete/<?php echo $student['student_id']; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete student <?php echo htmlspecialchars(addslashes($student['first_name'] . ' ' . $student['last_name'])); ?>? This might fail if they have financial records.');">
                                <?php // Add CSRF token ?>
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No students found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>