<?php
session_start();
include 'db_connection.php'; // your $conn

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// get logged-in user id
$stmtUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmtUser->bind_param("s", $_SESSION['username']);
$stmtUser->execute();
$resUser = $stmtUser->get_result();
$user = $resUser->fetch_assoc();
$user_id = $user['id'] ?? 0;

$input = json_decode(file_get_contents('php://input'), true);
$book_id = intval($input['book_id'] ?? 0);
$rating = intval($input['rating'] ?? 0);

if ($book_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Insert or update rating
$stmt = $conn->prepare("
    INSERT INTO book_ratings (book_id, user_id, rating) 
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = ?
");
$stmt->bind_param("iiii", $book_id, $user_id, $rating, $rating);
$stmt->execute();

echo json_encode(['success' => true]);
?>
