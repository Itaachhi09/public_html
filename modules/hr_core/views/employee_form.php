<?php
/**
 * Employee Create/Edit View
 */
$page_title = isset($employee) ? 'Edit Employee' : 'Create New Employee';
$module = 'HR Core';
$is_edit = isset($employee);

// Load dropdown data
$departments = $departmentModel->getActive();
$jobTitles = $jobTitleModel->getActive();
$employmentTypes = $employmentTypeModel->getActive();
$locations = $locationModel->getActive();
$activeEmployees = $employeeModel->getActive();
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/modules/hr_core/index.php" class="form">
            <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
            <input type="hidden" name="entity" value="employee">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($employee['employee_id']); ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="employee_code">Employee Code <span style="color:red;">*</span></label>
                    <input type="text" id="employee_code" name="employee_code" 
                           value="<?php echo htmlspecialchars($employee['employee_code'] ?? ''); ?>"
                           <?php echo $is_edit ? 'readonly' : ''; ?> required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name <span style="color:red;">*</span></label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($employee['first_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" 
                           value="<?php echo htmlspecialchars($employee['middle_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span style="color:red;">*</span></label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($employee['last_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($employee['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" 
                           value="<?php echo htmlspecialchars($employee['date_of_birth'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="Other" <?php echo (isset($employee['gender']) && $employee['gender'] === 'Other') ? 'selected' : ''; ?>>Select</option>
                        <option value="Male" <?php echo (isset($employee['gender']) && $employee['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($employee['gender']) && $employee['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo (isset($employee['gender']) && $employee['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="marital_status">Marital Status</label>
                    <select id="marital_status" name="marital_status">
                        <option value="Single" <?php echo (isset($employee['marital_status']) && $employee['marital_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (isset($employee['marital_status']) && $employee['marital_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo (isset($employee['marital_status']) && $employee['marital_status'] === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Widowed" <?php echo (isset($employee['marital_status']) && $employee['marital_status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['department_id']; ?>" 
                                    <?php echo (isset($employee['department_id']) && $employee['department_id'] == $dept['department_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="job_title_id">Job Title</label>
                    <select id="job_title_id" name="job_title_id">
                        <option value="">Select Job Title</option>
                        <?php foreach ($jobTitles as $title): ?>
                            <option value="<?php echo $title['job_title_id']; ?>" 
                                    <?php echo (isset($employee['job_title_id']) && $employee['job_title_id'] == $title['job_title_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($title['job_title_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="employment_type_id">Employment Type</label>
                    <select id="employment_type_id" name="employment_type_id">
                        <option value="">Select Employment Type</option>
                        <?php foreach ($employmentTypes as $type): ?>
                            <option value="<?php echo $type['employment_type_id']; ?>" 
                                    <?php echo (isset($employee['employment_type_id']) && $employee['employment_type_id'] == $type['employment_type_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location_id">Location</label>
                    <select id="location_id" name="location_id">
                        <option value="">Select Location</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo $loc['location_id']; ?>" 
                                    <?php echo (isset($employee['location_id']) && $employee['location_id'] == $loc['location_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc['location_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="supervisor_id">Supervisor</label>
                    <select id="supervisor_id" name="supervisor_id">
                        <option value="">Select Supervisor</option>
                        <?php foreach ($activeEmployees as $emp): ?>
                            <option value="<?php echo $emp['employee_id']; ?>" 
                                    <?php echo (isset($employee['supervisor_id']) && $employee['supervisor_id'] == $emp['employee_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_of_joining">Date of Joining</label>
                    <input type="date" id="date_of_joining" name="date_of_joining" 
                           value="<?php echo htmlspecialchars($employee['date_of_joining'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="form-group">
                    <label for="employment_status">Employment Status</label>
                    <select id="employment_status" name="employment_status">
                        <option value="Active" <?php echo (isset($employee['employment_status']) && $employee['employment_status'] === 'Active') ? 'selected' : 'selected'; ?>>Active</option>
                        <option value="On Leave" <?php echo (isset($employee['employment_status']) && $employee['employment_status'] === 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                        <option value="Suspended" <?php echo (isset($employee['employment_status']) && $employee['employment_status'] === 'Suspended') ? 'selected' : ''; ?>>Suspended</option>
                        <option value="Terminated" <?php echo (isset($employee['employment_status']) && $employee['employment_status'] === 'Terminated') ? 'selected' : ''; ?>>Terminated</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($employee['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" 
                           value="<?php echo htmlspecialchars($employee['city'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" 
                           value="<?php echo htmlspecialchars($employee['state'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" 
                           value="<?php echo htmlspecialchars($employee['postal_code'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" 
                           value="<?php echo htmlspecialchars($employee['country'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="nationality">Nationality</label>
                    <input type="text" id="nationality" name="nationality" 
                           value="<?php echo htmlspecialchars($employee['nationality'] ?? ''); ?>">
                </div>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-success">
                    <?php echo $is_edit ? 'Update Employee' : 'Create Employee'; ?>
                </button>
                <a href="/modules/hr_core/index.php?action=list&entity=employee" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
