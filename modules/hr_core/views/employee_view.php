<?php
/**
 * Employee View Details
 */
$page_title = 'Employee Details';
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
            <a href="/modules/hr_core/index.php?action=list&entity=employee">Employees</a>
            <span><?php echo htmlspecialchars($page_title); ?></span>
        </div>

        <div class="detail-view">
            <div style="margin-bottom: 20px;">
                <h2><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h2>
                <p style="color: #7f8c8d;">Employee Code: <?php echo htmlspecialchars($employee['employee_code']); ?></p>
            </div>

            <div class="btn-group">
                <a href="/modules/hr_core/index.php?action=edit&entity=employee&id=<?php echo $employee['employee_id']; ?>" class="btn btn-warning">Edit</a>
                <a href="/modules/hr_core/index.php?action=list&entity=employee" class="btn btn-secondary">Back to List</a>
            </div>

            <h3 style="margin-top: 30px; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">Personal Information</h3>

            <div class="detail-row">
                <div class="detail-label">Full Name:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . ($employee['middle_name'] ?? '') . ' ' . $employee['last_name']); ?>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Phone:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Date of Birth:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['date_of_birth'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Gender:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['gender'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Marital Status:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['marital_status'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Nationality:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['nationality'] ?? 'N/A'); ?></div>
            </div>

            <h3 style="margin-top: 30px; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">Professional Information</h3>

            <div class="detail-row">
                <div class="detail-label">Employee Code:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['employee_code']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Department:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Job Title:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['job_title_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Employment Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['type_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Location:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['location_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Date of Joining:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['date_of_joining']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Employment Status:</div>
                <div class="detail-value">
                    <span class="badge badge-<?php echo ($employee['employment_status'] === 'Active') ? 'success' : 'danger'; ?>">
                        <?php echo htmlspecialchars($employee['employment_status']); ?>
                    </span>
                </div>
            </div>

            <h3 style="margin-top: 30px; margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">Address Information</h3>

            <div class="detail-row">
                <div class="detail-label">Address:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['address'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">City:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['city'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">State:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['state'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Postal Code:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['postal_code'] ?? 'N/A'); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Country:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['country'] ?? 'N/A'); ?></div>
            </div>

            <div class="btn-group" style="margin-top: 30px;">
                <a href="/modules/hr_core/index.php?action=edit&entity=employee&id=<?php echo $employee['employee_id']; ?>" class="btn btn-warning">Edit</a>
                <form method="POST" action="/modules/hr_core/index.php" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="entity" value="employee">
                    <input type="hidden" name="id" value="<?php echo $employee['employee_id']; ?>">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</button>
                </form>
                <a href="/modules/hr_core/index.php?action=list&entity=employee" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</body>
</html>
