<?php
require_once 'config/Database.php';

$db = new Database();
$conn = $db->connect();

// Check if user already exists
$check = $conn->prepare('SELECT id FROM users WHERE email = ?');
$check->execute(['boglhee93@gmail.com']);

if ($check->rowCount() > 0) {
    echo 'User already exists';
    exit;
}

// Hash the password
$hashed = password_hash('chief@123', PASSWORD_BCRYPT);

// Get max ID
$idStmt = $conn->query('SELECT MAX(id) as max_id FROM users');
$row = $idStmt->fetch(PDO::FETCH_ASSOC);
$next_id = ($row['max_id'] ?? 0) + 1;

// Insert user
$insert = $conn->prepare('INSERT INTO users (id, username, email, password, first_name, last_name, name, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');

$result = $insert->execute([
    $next_id,
    'boglhee',
    'boglhee93@gmail.com',
    $hashed,
    'Bogle',
    'HR',
    'Bogle HR Chief',
    'hr_chief',
    'active'
]);

if ($result) {
    echo "✅ HR Chief user created successfully!\n";
    echo "ID: $next_id\n";
    echo "Email: boglhee93@gmail.com\n";
    echo "Password: chief@123\n";
    echo "Role: hr_chief\n";
} else {
    echo 'Failed to create user';
}
?>
