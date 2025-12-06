<?php
session_start();
require 'db_connection.php';

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    $_SESSION['search_error'] = "Please enter a search term.";
    header("Location: index.php");
    exit;
}

$searchTerm = $conn->real_escape_string(trim($_GET['q']));
$searchType = $_GET['type'] ?? 'all';

// Build query based on search type
if ($searchType === 'title') {
    $sql = "SELECT id FROM books WHERE title LIKE '%$searchTerm%' LIMIT 1";
} elseif ($searchType === 'author') {
    $sql = "SELECT id FROM books WHERE author LIKE '%$searchTerm%' LIMIT 1";
} else {
    $sql = "SELECT id FROM books WHERE title LIKE '%$searchTerm%' OR author LIKE '%$searchTerm%' LIMIT 1";
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $book = $result->fetch_assoc();
    header("Location: book_details.php?id=" . $book['id']);
    exit;
} else {
    $_SESSION['search_error'] = "No results found for '" . htmlspecialchars($searchTerm) . "'";
    header("Location: index.php");
    exit;
}
