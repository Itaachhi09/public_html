<?php
// Direct database update script
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'public_html';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update PhilHealth (ID 5) and St. Luke's (ID 4) to Active
$sql = "UPDATE hmo_providers SET provider_status = 'Active' WHERE id IN (4, 5)";
$result = $conn->query($sql);

if ($result) {
    echo "Successfully updated " . $conn->affected_rows . " providers to Active status<br>";
    
    // Verify the update
    $verify = $conn->query("SELECT id, provider_code, provider_name, provider_status FROM hmo_providers WHERE id IN (4, 5) ORDER BY id");
    echo "<pre>";
    while ($row = $verify->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Code: " . $row['provider_code'] . " | Name: " . $row['provider_name'] . " | Status: " . $row['provider_status'] . "\n";
    }
    echo "</pre>";
} else {
    echo "Error updating providers: " . $conn->error;
}

$conn->close();
?>
