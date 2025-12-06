<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to borrow books.'
    ]);
    exit();
}

require 'db_connection.php';
require __DIR__ . '/vendor/autoload.php';
require 'mail.php';  // Assuming sendMail() is inside this file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['book_id']) || !is_numeric($_POST['book_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid or missing Book ID"]);
        exit;
    }

    $bookId = intval($_POST['book_id']);
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    try {
        // Check if book exists and get current copies
        $stmt = $conn->prepare("SELECT copies, title FROM books WHERE id = ?");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "Book not found"]);
            exit;
        }

        $book = $result->fetch_assoc();

        if ($book['copies'] > 0) {
            // Decrease copies
            $stmt = $conn->prepare("UPDATE books SET copies = copies - 1 WHERE id = ?");
            $stmt->bind_param("i", $bookId);
            $stmt->execute();

            // Insert borrow record with borrow_date, due_date, borrowed_at
            $stmt = $conn->prepare(
                "INSERT INTO borrowed_books (user_id, username, book_id, borrow_date, due_date, borrowed_at) 
                 VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), NOW())"
            );
            $stmt->bind_param("isi", $userId, $username, $bookId);
            $stmt->execute();

            // Get the user's email address (implement this function)
            $userEmail = getUserEmailById($userId);

            // Prepare email content (Mongolian)
            $subject = "Ном зээлсэн: " . $book['title'];
            $body = "Сайн байна уу, {$username}!\n\nТа \"" . $book['title'] . "\" номыг амжилттай зээллээ. Та энэ номыг хугацаанд нь буцааж өгөөрэй.\n\nМанай номын сангийн үйлчилгээг ашигласанд баярлалаа.";

            // Send confirmation email
            if (sendMail($userEmail, $subject, $body)) {
                echo json_encode(["success" => true, "message" => "Book borrowed and email sent"]);
            } else {
                echo json_encode(["success" => true, "message" => "Book borrowed, but failed to send email"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Out of stock"]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error processing your request: ' . $e->getMessage()
        ]);
    } finally {
        // Close database connection
        $conn->close();
    }
}
?>
