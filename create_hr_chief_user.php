<?php
/**
 * Quick HR Chief User Creator
 * Creates HR Chief user: boglhee93@gmail.com with password: chief@123
 */

require_once __DIR__ . '/config/Database.php';

try {
    $db = new Database();
    $conn = $db->connect();

    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['boglhee93@gmail.com']);
    
    if ($stmt->rowCount() > 0) {
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "User with email boglhee93@gmail.com already exists (ID: {$existing['id']})";
        exit;
    }

    // Get max ID for next user
    $idStmt = $conn->query("SELECT MAX(id) as max_id FROM users");
    $row = $idStmt->fetch(PDO::FETCH_ASSOC);
    $next_id = ($row['max_id'] ?? 0) + 1;

    // Hash password: chief@123
    $hashed_password = password_hash('chief@123', PASSWORD_BCRYPT);

    // Insert HR Chief user
    $insertStmt = $conn->prepare("
        INSERT INTO users (id, username, email, password, first_name, last_name, name, role, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $result = $insertStmt->execute([
        $next_id,
        'boglhee',
        'boglhee93@gmail.com',
        $hashed_password,
        'Bogle',
        'HR',
        'Bogle HR Chief',
        'hr_chief',
        'active'
    ]);

    if ($result) {
        echo "<h2 style='color: green;'>✅ HR Chief User Created Successfully!</h2>";
        echo "<p><strong>User Details:</strong></p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> {$next_id}</li>";
        echo "<li><strong>Username:</strong> boglhee</li>";
        echo "<li><strong>Email:</strong> boglhee93@gmail.com</li>";
        echo "<li><strong>Password:</strong> chief@123</li>";
        echo "<li><strong>Role:</strong> hr_chief</li>";
        echo "</ul>";
        echo "<p><a href='index.php' style='color: blue; text-decoration: underline;'>Go to Login Page →</a></p>";
    } else {
        echo "❌ Failed to create user";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
