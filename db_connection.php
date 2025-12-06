<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library_db_dummy';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Function to get user email by user ID
function getUserEmailById($userId) {
    global $conn;  // Use the global database connection

    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    return $user['email'];  // Return the email address
}
?>