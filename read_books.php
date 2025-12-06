<?php
// read_book.php

session_start();
include 'db_connection.php'; // Your DB connection

// Get book ID from URL
if (!isset($_GET['id'])) {
    die("No book ID provided.");
}

$bookId = intval($_GET['id']);

// Fetch book info from database
$stmt = $conn->prepare("SELECT title, readable_file FROM books WHERE id = ?");
$stmt->bind_param("i", $bookId);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();

if (!$book || empty($book['readable_file'])) {
    die("Book PDF not found.");
}

// Build the full path to the PDF
$pdfPath = __DIR__ . '/' . $book['readable_file'];

if (!file_exists($pdfPath)) {
    die("PDF file does not exist on the server.");
}

// Serve the PDF in browser
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($pdfPath) . '"');
header('Content-Length: ' . filesize($pdfPath));

readfile($pdfPath);
exit;
?>