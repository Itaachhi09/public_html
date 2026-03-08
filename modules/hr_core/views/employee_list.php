<?php
/**
 * Employee List View
 */
$page_title = 'Employees';
$module = 'HR Core';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($module); ?></title>
    <link rel="stylesheet" href="/assets/css/crud.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p><?php echo htmlspecialchars($module); ?> Module</p>
        </div>

        <div class="breadcrumb">
            <a href="/">Home</a>
            <a href="/modules/hr_core/">HR Core</a>
            <span><?php echo htmlspecialchars($page_title); ?></span>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="/modules/hr_core/index.php?action=create&entity=employee" class="btn btn-success">+ Add New Employee</a>
            <a href="/modules/hr_core/" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="search-box">
            <input type="text" id="employee_search" placeholder="Search employees by name, email, or code...">
            <button class="btn btn-primary" onclick="performSearch()">Search</button>
        </div>

        <?php if (!empty($employees)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Employee Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($employee['employee_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($employee['job_title_name'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo ($employee['employment_status'] === 'Active') ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars($employee['employment_status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="/modules/hr_core/index.php?action=view&entity=employee&id=<?php echo $employee['employee_id']; ?>" class="btn btn-info">View</a>
                                <a href="/modules/hr_core/index.php?action=edit&entity=employee&id=<?php echo $employee['employee_id']; ?>" class="btn btn-warning">Edit</a>
                                <form method="POST" action="/modules/hr_core/index.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="entity" value="employee">
                                    <input type="hidden" name="id" value="<?php echo $employee['employee_id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h3>No Employees Found</h3>
            <p>Start by adding your first employee or check your search filters.</p>
            <a href="/modules/hr_core/index.php?action=create&entity=employee" class="btn btn-success">Add First Employee</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function performSearch() {
            const term = document.getElementById('employee_search').value;
            if (term.trim()) {
                window.location.href = '/modules/hr_core/index.php?action=search&entity=employee&q=' + encodeURIComponent(term);
            }
        }

        document.getElementById('employee_search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    </script>
</body>
</html>
