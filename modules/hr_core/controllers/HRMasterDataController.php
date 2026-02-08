<?php
/**
 * HR Master Data Controller
 * Manages shifts, work schedules, and other reference data
 */

require_once __DIR__ . '/../../../config/Database.php';
require_once __DIR__ . '/../../../config/Response.php';
require_once __DIR__ . '/../../../config/BaseController.php';
require_once __DIR__ . '/../models/Shift.php';
require_once __DIR__ . '/../models/WorkSchedule.php';

class HRMasterDataController extends BaseController {
    private $shiftModel;
    private $scheduleModel;

    public function __construct() {
        parent::__construct();
        $this->shiftModel = new Shift();
        $this->scheduleModel = new WorkSchedule();
    }

    // ==================== SHIFT MANAGEMENT ====================

    /**
     * Get all shifts
     * GET ?action=getShifts&limit=20&offset=0
     */
    public function getShifts() {
        $this->checkRole(['admin', 'hr', 'manager']);

        $limit = $this->getInput('limit') ?? 20;
        $offset = $this->getInput('offset') ?? 0;

        $shifts = $this->shiftModel->getAllActive($limit, $offset);
        $total = $this->shiftModel->count();

        $this->respondSuccess([
            'shifts' => $shifts,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get single shift details
     * GET ?action=getShift&shift_id=1
     */
    public function getShift() {
        $this->checkRole(['admin', 'hr', 'manager']);

        $shift_id = $this->getInput('shift_id');
        if (!$shift_id) {
            $this->respondError('Shift ID required', 400);
            return;
        }

        $shift = $this->shiftModel->getWithEmployeeCount($shift_id);

        if (!$shift) {
            $this->respondError('Shift not found', 404);
            return;
        }

        // Get employees assigned to this shift
        $shift['employees'] = $this->shiftModel->getEmployeesByShift($shift_id);

        $this->respondSuccess($shift);
    }

    /**
     * Create new shift
     * POST ?action=createShift
     */
    public function createShift() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();

        // Validate required fields
        if (empty($input['shift_name']) || empty($input['shift_code'])) {
            $this->respondError('Shift name and code are required', 400);
            return;
        }

        // Check duplicate code
        if ($this->shiftModel->findByCode($input['shift_code'])) {
            $this->respondError('Shift code already exists', 400);
            return;
        }

        try {
            $shiftData = [
                'shift_name' => $input['shift_name'],
                'shift_code' => $input['shift_code'],
                'start_time' => $input['start_time'] ?? null,
                'end_time' => $input['end_time'] ?? null,
                'duration_hours' => $input['duration_hours'] ?? 8,
                'break_hours' => $input['break_hours'] ?? 1,
                'description' => $input['description'] ?? null,
                'is_night_shift' => $input['is_night_shift'] ?? 0,
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $shiftId = $this->shiftModel->create($shiftData);

            $this->respondSuccess(['shift_id' => $shiftId], 'Shift created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating shift: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update shift
     * POST ?action=updateShift&shift_id=1
     */
    public function updateShift() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $shift_id = $input['shift_id'] ?? null;

        if (!$shift_id) {
            $this->respondError('Shift ID required', 400);
            return;
        }

        try {
            $existing = $this->shiftModel->find($shift_id);
            if (!$existing) {
                $this->respondError('Shift not found', 404);
                return;
            }

            $updateData = [];
            $updateFields = ['shift_name', 'shift_code', 'start_time', 'end_time', 'duration_hours', 'break_hours', 'description', 'is_night_shift', 'status'];

            foreach ($updateFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = $input[$field];
                }
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $this->shiftModel->update($shift_id, $updateData);

            $this->respondSuccess(null, 'Shift updated successfully');
        } catch (Exception $e) {
            $this->respondError('Error updating shift: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete shift
     * POST ?action=deleteShift&shift_id=1
     */
    public function deleteShift() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $shift_id = $input['shift_id'] ?? null;

        if (!$shift_id) {
            $this->respondError('Shift ID required', 400);
            return;
        }

        try {
            $shift = $this->shiftModel->find($shift_id);
            if (!$shift) {
                $this->respondError('Shift not found', 404);
                return;
            }

            // Soft delete
            $this->shiftModel->update($shift_id, ['status' => 'Inactive', 'updated_at' => date('Y-m-d H:i:s')]);

            $this->respondSuccess(null, 'Shift deleted successfully');
        } catch (Exception $e) {
            $this->respondError('Error deleting shift: ' . $e->getMessage(), 500);
        }
    }

    // ==================== WORK SCHEDULE MANAGEMENT ====================

    /**
     * Get all work schedules
     * GET ?action=getSchedules&limit=20&offset=0
     */
    public function getSchedules() {
        $this->checkRole(['admin', 'hr', 'manager']);

        $limit = $this->getInput('limit') ?? 20;
        $offset = $this->getInput('offset') ?? 0;

        $schedules = $this->scheduleModel->getAllActive($limit, $offset);
        $total = $this->scheduleModel->count();

        $this->respondSuccess([
            'schedules' => $schedules,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Get single work schedule
     * GET ?action=getSchedule&schedule_id=1
     */
    public function getSchedule() {
        $this->checkRole(['admin', 'hr', 'manager']);

        $schedule_id = $this->getInput('schedule_id');
        if (!$schedule_id) {
            $this->respondError('Schedule ID required', 400);
            return;
        }

        $schedule = $this->scheduleModel->getWithEmployeeCount($schedule_id);

        if (!$schedule) {
            $this->respondError('Schedule not found', 404);
            return;
        }

        // Get employees on this schedule
        $schedule['employees'] = $this->scheduleModel->getEmployeesBySchedule($schedule_id);

        $this->respondSuccess($schedule);
    }

    /**
     * Create new work schedule
     * POST ?action=createSchedule
     */
    public function createSchedule() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();

        if (empty($input['schedule_name'])) {
            $this->respondError('Schedule name is required', 400);
            return;
        }

        try {
            $scheduleData = [
                'schedule_name' => $input['schedule_name'],
                'schedule_type' => $input['schedule_type'] ?? 'Fixed',
                'monday' => $input['monday'] ?? '08:00-17:00',
                'tuesday' => $input['tuesday'] ?? '08:00-17:00',
                'wednesday' => $input['wednesday'] ?? '08:00-17:00',
                'thursday' => $input['thursday'] ?? '08:00-17:00',
                'friday' => $input['friday'] ?? '08:00-17:00',
                'saturday' => $input['saturday'] ?? 'Off',
                'sunday' => $input['sunday'] ?? 'Off',
                'weekly_hours' => $input['weekly_hours'] ?? 40,
                'description' => $input['description'] ?? null,
                'status' => 'Active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $scheduleId = $this->scheduleModel->create($scheduleData);

            $this->respondSuccess(['schedule_id' => $scheduleId], 'Schedule created successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error creating schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update work schedule
     * POST ?action=updateSchedule&schedule_id=1
     */
    public function updateSchedule() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $schedule_id = $input['schedule_id'] ?? null;

        if (!$schedule_id) {
            $this->respondError('Schedule ID required', 400);
            return;
        }

        try {
            $existing = $this->scheduleModel->find($schedule_id);
            if (!$existing) {
                $this->respondError('Schedule not found', 404);
                return;
            }

            $updateData = [];
            $updateFields = ['schedule_name', 'schedule_type', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'weekly_hours', 'description', 'status'];

            foreach ($updateFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = $input[$field];
                }
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $this->scheduleModel->update($schedule_id, $updateData);

            $this->respondSuccess(null, 'Schedule updated successfully');
        } catch (Exception $e) {
            $this->respondError('Error updating schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete work schedule
     * POST ?action=deleteSchedule&schedule_id=1
     */
    public function deleteSchedule() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $schedule_id = $input['schedule_id'] ?? null;

        if (!$schedule_id) {
            $this->respondError('Schedule ID required', 400);
            return;
        }

        try {
            $schedule = $this->scheduleModel->find($schedule_id);
            if (!$schedule) {
                $this->respondError('Schedule not found', 404);
                return;
            }

            // Soft delete
            $this->scheduleModel->update($schedule_id, ['status' => 'Inactive', 'updated_at' => date('Y-m-d H:i:s')]);

            $this->respondSuccess(null, 'Schedule deleted successfully');
        } catch (Exception $e) {
            $this->respondError('Error deleting schedule: ' . $e->getMessage(), 500);
        }
    }

    // ==================== EMPLOYEE-SHIFT ASSIGNMENT ====================

    /**
     * Assign shift to employee
     * POST ?action=assignShift&emp_id=1&shift_id=1
     */
    public function assignShift() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $emp_id = $input['emp_id'] ?? null;
        $shift_id = $input['shift_id'] ?? null;

        if (!$emp_id || !$shift_id) {
            $this->respondError('Employee ID and Shift ID are required', 400);
            return;
        }

        try {
            $dbInstance = new Database();
            $db = $dbInstance->connect();
            
            // Check if already assigned
            $query = "SELECT * FROM employee_shifts WHERE employee_id = ? AND shift_id = ? AND status = 'Active'";
            $stmt = $db->prepare($query);
            $stmt->execute([$emp_id, $shift_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $this->respondError('Employee is already assigned to this shift', 400);
                return;
            }

            // Insert assignment
            $effective_date = $input['effective_date'] ?? date('Y-m-d');
            $createdAt = date('Y-m-d H:i:s');
            
            $query = "
                INSERT INTO employee_shifts (employee_id, shift_id, effective_date, status, created_at)
                VALUES (?, ?, ?, 'Active', ?)
            ";
            $stmt = $db->prepare($query);
            $stmt->execute([$emp_id, $shift_id, $effective_date, $createdAt]);

            $this->respondSuccess(null, 'Shift assigned successfully', 201);
        } catch (Exception $e) {
            $this->respondError('Error assigning shift: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove shift from employee
     * POST ?action=removeShift&emp_id=1&shift_id=1
     */
    public function removeShift() {
        $this->checkRole(['admin', 'hr']);

        $input = $this->getInput();
        $emp_id = $input['emp_id'] ?? null;
        $shift_id = $input['shift_id'] ?? null;

        if (!$emp_id || !$shift_id) {
            $this->respondError('Employee ID and Shift ID are required', 400);
            return;
        }

        try {
            $dbInstance = new Database();
            $db = $dbInstance->connect();
            $query = "UPDATE employee_shifts SET status = 'Inactive' WHERE employee_id = ? AND shift_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$emp_id, $shift_id]);

            $this->respondSuccess(null, 'Shift removed successfully');
        } catch (Exception $e) {
            $this->respondError('Error removing shift: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Action router
     */
    public function route() {
        $action = $this->getInput('action') ?? 'index';

        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            $this->respondError('Action not found', 404);
        }
    }
}

// Instantiate and route
$controller = new HRMasterDataController();
$controller->route();
?>
