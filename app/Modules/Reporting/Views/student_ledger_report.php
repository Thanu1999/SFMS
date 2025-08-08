<?php
// File: app/Modules/Reporting/Views/student_ledger_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $allStudents, $selectedStudentId, $studentDetails, $ledgerEntries, $viewError
// Global flash handled by layout
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div>
<?php endif; ?>

<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/student-ledger" method="GET" class="row gx-3 gy-2 align-items-center">
            <div class="col-md-8 col-lg-6">
                <label for="student_id" class="form-label fw-bold">Select Student:</label>
                <select id="student_id" name="student_id" required class="form-select form-select-sm select2-enable"> <option value="">-- Type to search or Select Student --</option>
                     <?php foreach ($allStudents ?? [] as $id => $display): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($selectedStudentId) && $id == $selectedStudentId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($display); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm mt-4"><i class="bi bi-eye"></i> View Ledger</button>
            </div>
        </form>
    </div>
</div>


<?php if (isset($selectedStudentId) && isset($studentDetails)): ?>
    <div class="student-details mb-3">
        <h2>Ledger for: <?php echo htmlspecialchars($studentDetails['first_name'] . ' ' . $studentDetails['last_name']); ?></h2>
        <p class="text-muted">
            Adm No: <?php echo htmlspecialchars($studentDetails['admission_number']); ?> |
            Class: <?php echo htmlspecialchars($studentDetails['class_name'] ?? 'N/A'); ?> |
            Status: <?php echo htmlspecialchars(ucfirst($studentDetails['status'])); ?>
        </p>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-end">Charges (+)</th>
                    <th class="text-end">Credits (-)</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ledgerEntries)): ?>
                    <?php foreach ($ledgerEntries as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($entry['date']))); ?></td>
                            <td><?php echo htmlspecialchars($entry['type']); ?></td>
                            <td><?php echo htmlspecialchars($entry['ref']); ?></td>
                            <td><?php echo htmlspecialchars($entry['description']); ?></td>
                            <td class="text-end"><?php echo $entry['charge'] > 0 ? htmlspecialchars(number_format($entry['charge'], 2)) : ''; ?></td>
                            <td class="text-end"><?php echo $entry['credit'] > 0 ? htmlspecialchars(number_format($entry['credit'], 2)) : ''; ?></td>
                            <td class="text-end fw-bold"><?php echo htmlspecialchars(number_format($entry['balance'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                     <tr><td colspan="7" class="text-center">No transactions found for this student.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
 <?php elseif (isset($selectedStudentId)): // Selection made but failed ?>
     <div class="alert alert-warning">Could not load details for the selected student.</div>
 <?php else: // No student selected yet ?>
     <div class="alert alert-info">Please select a student using the dropdown above to view their ledger.</div>
 <?php endif; ?>
 <?php

// Add Select2 Initialization Script - This should ideally be in the main layout's footer script area,
// but can be placed here if needed specifically for this page. Ensure jQuery is loaded first (in layout footer).
// Only include if jQuery/Select2 aren't globally initialized in your layout footer.
/*
?>
<script>
if (typeof $ !== 'undefined' && typeof $.fn.select2 === 'function') {
    $(document).ready(function() {
        $('.select2-enable').select2({ // Use class selector
            placeholder: "-- Type to search or Select Student --",
            allowClear: true,
            theme: 'bootstrap-5' // Optional: Use Bootstrap 5 theme if available/included
        });
    });
} else {
    console.error("Select2 or jQuery not loaded - Searchable dropdown disabled.");
}
</script>
<?php
*/
?>