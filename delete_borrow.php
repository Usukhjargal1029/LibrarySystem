<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
    $borrowId = intval($_POST['borrow_id']);

    $stmt = $conn->prepare("DELETE FROM borrowed_books WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $borrowId, $username);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?deleted=1");
        exit();
    } else {
        echo "Error deleting record.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
