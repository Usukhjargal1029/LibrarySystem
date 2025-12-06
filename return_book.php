<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
    $borrowId = intval($_POST['borrow_id']);

    // Fetch borrow record to get due_date and book_id
    $stmt = $conn->prepare("SELECT book_id, due_date FROM borrowed_books WHERE id = ?");
    $stmt->bind_param("i", $borrowId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['message'] = "Invalid borrow record.";
        header("Location: admin_returns.php");
        exit;
    }

    $borrowed = $result->fetch_assoc();
    $bookId = $borrowed['book_id'];
    $dueDate = new DateTime($borrowed['due_date']);
    $returnDate = new DateTime();  // Current date-time

    // Calculate late days
    $lateDays = 0;
    if ($returnDate > $dueDate) {
        $interval = $dueDate->diff($returnDate);
        $lateDays = $interval->days;
    }

    // Calculate late fee (e.g., $1 per day)
    $lateFeePerDay = 1.00;
    $totalLateFee = $lateDays * $lateFeePerDay;

    // Update borrowed_books record with return_date, returned flag, and late_fee
    $stmt = $conn->prepare("UPDATE borrowed_books SET return_date = NOW(), returned = 1, late_fee = ? WHERE id = ?");
    $stmt->bind_param("di", $totalLateFee, $borrowId);
    $stmt->execute();

    // Increment book copies
    $stmt = $conn->prepare("UPDATE books SET copies = copies + 1 WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();

    $_SESSION['message'] = "Book returned successfully. Late fee: $" . number_format($totalLateFee, 2);
    header("Location: admin_returns.php");
    exit;
} else {
    echo "Invalid request.";
}
?>
