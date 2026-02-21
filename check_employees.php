<?php
try {
    $db = new mysqli('localhost', 'root', '', 'public_html');
    if ($db->connect_error) {
        echo 'Connection Error: ' . $db->connect_error;
    } else {
        $result = $db->query('SELECT COUNT(*) as total FROM employees');
        $row = $result->fetch_assoc();
        echo 'Total Employees: ' . $row['total'] . PHP_EOL;
        
        $result2 = $db->query('SELECT COUNT(*) as total FROM employee_salaries');
        $row2 = $result2->fetch_assoc();
        echo 'Salary Records: ' . $row2['total'] . PHP_EOL;
        
        $result3 = $db->query('SELECT DISTINCT employment_status FROM employees LIMIT 5');
        echo 'Employment Statuses: ';
        while ($row3 = $result3->fetch_assoc()) {
            echo $row3['employment_status'] . ', ';
        }
        echo PHP_EOL;
        
        $db->close();
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
