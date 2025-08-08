<?php
// File: app/Modules/Reporting/Views/audit_log_report.php
// Included by layout_admin.php

// Variables passed: $pageTitle, $logs, $allUsers, $filterStartDate, $filterEndDate, $filterUserId, $filterActionType, $currentPage, $totalPages, $totalRecords, $viewError
// Global flash handled by layout header
?>

<h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Report'; ?></h1>

<?php if (isset($viewError) && $viewError): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($viewError); ?></div><?php endif; ?>

<div class="card bg-light mb-4">
    <div class="card-body">
        <form action="/sfms_project/public/admin/reports/audit-log" method="GET" class="row gx-3 gy-2 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label">From:</label>
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($filterStartDate ?? ''); ?>" required>
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">To:</label>
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($filterEndDate ?? ''); ?>" required>
            </div>
            <div class="col-md-3">
                <label for="user_id" class="form-label">User:</label>
                <select id="user_id" name="user_id" class="form-select form-select-sm select2-enable">
                    <option value="">-- All Users --</option>
                    <?php foreach ($allUsers ?? [] as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($filterUserId) && $id == $filterUserId) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?> (ID: <?php echo $id; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="action_type" class="form-label">Action Type:</label>
                <input type="text" id="action_type" name="action_type" class="form-control form-control-sm"
                    value="<?php echo htmlspecialchars($filterActionType ?? ''); ?>" placeholder="Contains...">
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0"> <button type="submit" class="btn btn-primary btn-sm w-100"><i
                        class="bi bi-filter"></i> Apply Filter</button>
            </div>
        </form>
    </div>
</div>

<p>Total Records Found: <?php echo $totalRecords ?? 0; ?></p>

<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action Type</th>
                <th>Target</th>
                <th>Details</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($logs) && !empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="text-nowrap"><?php echo htmlspecialchars($log['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($log['username'] ?? 'System/Unknown'); ?> <small class="text-muted">(ID:
                                <?php echo htmlspecialchars($log['user_id'] ?? 'N/A'); ?>)</small></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($log['action_type']); ?></span>
                        </td>
                        <td>
                            <?php if (!empty($log['table_name'])): ?>
                                <span class="text-muted"><?php echo htmlspecialchars($log['table_name']); ?></span>
                                <?php if (!empty($log['record_id'])): ?>
                                    <span class="text-muted"> (ID: <?php echo htmlspecialchars($log['record_id']); ?>)</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php // Attempt to decode JSON details for pretty printing, otherwise show raw
                                    $detailsOutput = $log['action_details'] ?? '';
                                    $decodedDetails = json_decode($detailsOutput, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDetails)) {
                                        echo '<pre style="max-height: 150px; overflow-y: auto; background-color: #f0f0f0; padding: 5px; border: 1px solid #ccc; font-size: 0.85em;">';
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDetails)) {
                                            echo '<ul style="margin: 0; padding-left: 1.2em; font-size: 0.9em;">';
                                            foreach ($decodedDetails as $key => $value) {
                                                $displayValue = '';
                                                if (is_array($value) || is_object($value)) {
                                                    // If value is an array/object, encode it back to JSON for display
                                                    $displayValue = json_encode($value, JSON_PRETTY_PRINT);
                                                } elseif (is_null($value)) {
                                                    $displayValue = 'NULL';
                                                } elseif (is_bool($value)) {
                                                    $displayValue = $value ? 'true' : 'false';
                                                } else {
                                                    // If it's a scalar value (string, int, float), treat as string
                                                    $displayValue = (string) $value;
                                                }

                                                // Now use htmlspecialchars on the guaranteed string $displayValue
                                                echo '<li><strong>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . htmlspecialchars($displayValue) . '</li>'; // This replaces the original line 82
                                            }
                                            echo '</ul>';
                                        } else { // Fallback remains the same
                                            echo '<pre style="white-space: pre-wrap; word-wrap: break-word;">' . htmlspecialchars($detailsOutput) . '</pre>';
                                        }
                                        echo '</pre>';
                                    } else {
                                        echo '<pre style="white-space: pre-wrap; word-wrap: break-word;">' . htmlspecialchars($detailsOutput) . '</pre>';
                                    }
                                    ?>
                        </td>
                        <td><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No audit log entries found matching the criteria.</td>
                </tr> <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($totalPages) && $totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php
            $queryParams = $_GET;
            unset($queryParams['page']);
            $baseUrl = "/sfms_project/public/admin/reports/audit-log?" . http_build_query($queryParams);
            $baseUrl .= (empty($queryParams) ? '' : '&') . 'page=';

            $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
            echo "<li class='page-item {$prevDisabled}'><a class='page-link' href='{$baseUrl}" . ($currentPage - 1) . "'>&laquo; Prev</a></li>";

            for ($i = 1; $i <= $totalPages; $i++):
                $activeClass = ($i == $currentPage) ? 'active' : '';
                echo "<li class='page-item {$activeClass}'><a class='page-link' href='{$baseUrl}{$i}'>{$i}</a></li>";
            endfor;

            $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
            echo "<li class='page-item {$nextDisabled}'><a class='page-link' href='{$baseUrl}" . ($currentPage + 1) . "'>Next &raquo;</a></li>";
            ?>
        </ul>
    </nav>
<?php endif; ?>