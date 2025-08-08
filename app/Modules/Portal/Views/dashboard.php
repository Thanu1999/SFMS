<?php
// File: app/Modules/Portal/Views/dashboard.php
// Included by layout_portal.php

// Variables passed: $pageTitle, $student, $linkedStudents, $selectedStudentId, $outstandingBalance, $viewError
// Global flash messages handled by layout header
?>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>
<?php if (isset($linkedStudents) && count($linkedStudents) > 1 && (isset($_SESSION['roles']) && in_array('Parent', $_SESSION['roles']))): ?>
    <div class="card bg-light mb-4 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-start flex-wrap gap-2">
            <form action="/sfms_project/public/portal/dashboard" method="GET" class="d-flex align-items-center mb-0" id="studentSelectorForm">
                <label for="view_student_id" class="form-label fw-bold me-2 mb-0">Viewing Child:</label>
                <select id="view_student_id" name="view_student_id" class="form-select form-select-sm select2-enable" onchange="this.form.submit()" style="width: auto; min-width: 250px;">
                    <?php foreach ($linkedStudents as $child): ?>
                        <option value="<?php echo $child['student_id']; ?>" <?php echo (isset($selectedStudentId) && $child['student_id'] == $selectedStudentId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name'] . ' (' . $child['admission_number'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </form>
        </div>
    </div>
<?php endif; ?>
<?php if (isset($student) && $student): ?>
    <h2 class="mb-3">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h2>
    <?php // Show which student's info is being displayed, especially for parents ?>
    <?php if (isset($linkedStudents) && count($linkedStudents) > 1): ?>
         <p class="lead fs-6 text-muted">Showing details for: <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
    <?php endif; ?>


    <div class="row">
        <div class="col-md-6 mb-3">
             <div class="card h-100 shadow-sm">
                 <div class="card-body">
                     <h5 class="card-title"><i class="bi bi-person-badge me-2"></i>Student Information</h5>
                     <p class="card-text mb-1">
                         <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                     </p>
                     <p class="card-text mb-1">
                         <small class="text-muted">Admission No:</small> <?php echo htmlspecialchars($student['admission_number']); ?>
                     </p>
                     <p class="card-text">
                         <small class="text-muted">Class:</small> <?php echo htmlspecialchars($student['class_name'] ?? 'N/A'); ?>
                     </p>
                     <a href="/sfms_project/public/portal/profile" class="btn btn-sm btn-outline-secondary">View/Edit My Profile</a>
                 </div>
             </div>
        </div>
         <div class="col-md-6 mb-3">
             <div class="card h-100 shadow-sm <?php echo (($outstandingBalance ?? 0) > 0) ? 'text-white bg-danger' : 'text-white bg-success'; ?>">
                <div class="card-header">Outstanding Balance</div>
                 <div class="card-body text-center">
                    <h3 class="card-title display-5">Rs. 3000<?php echo htmlspecialchars(number_format($outstandingBalance ?? 0.00, 2)); ?></h3>
                    <a href="/sfms_project/public/portal/fees?view_student_id=<?php echo $selectedStudentId; // Pass selected ID ?>" class="btn btn-light mt-2 btn-sm">View Fee Details <i class="bi bi-arrow-right-circle"></i></a>
                 </div>
             </div>
         </div>
    </div>

    <div class="row">
         <div class="col-12">
             <div class="card">
                <div class="card-header">Announcements</div>
                <div class="card-body">
                    <p class="text-muted">School announcements will appear here.</p>
                </div>
            </div>
         </div>
    </div>


<?php else: ?>
     <?php // Display specific error if passed, otherwise generic message ?>
     <?php $errorMessage = $viewError ?? ($_SESSION['flash_error'] ?? "Student details could not be loaded. Please contact administration if you believe this is an error."); unset($_SESSION['flash_error']); ?>
     <div class="alert alert-warning"><?php echo htmlspecialchars($errorMessage); ?></div>
<?php endif; ?>