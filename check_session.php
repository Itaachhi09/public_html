<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

echo json_encode([
    'has_token' => !empty($_SESSION['token']),
    'token' => $_SESSION['token'] ?? '',
    'name' => $_SESSION['name'] ?? 'unknown',
    'role' => $_SESSION['role'] ?? 'unknown',
    'user_id' => $_SESSION['user_id'] ?? 'unknown',
    'session_id' => session_id()
]);
?>
