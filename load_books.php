<?php
// Connect to the database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'library_db';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch all books (limit to 12)
$sql = "SELECT id, title, author, image, copies FROM books LIMIT 12";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="book">';
        echo '<a href="book_details.php?id=' . $row["id"] . '" style="text-decoration:none; color:inherit;">';
        echo '<img src="' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["title"]) . '">';
        echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
        echo '</a>';
        echo '<p>Author: ' . htmlspecialchars($row["author"]) . '</p>';

        if ((int)$row["copies"] <= 0) {
            echo '<button class="action-btn borrow-btn" disabled>Borrowed</button>';
        } else {
            echo '<button class="action-btn borrow-btn" data-book-id="' . $row["id"] . '">Borrow</button>';
        }

        echo '</div>';
    }
} else {
    echo "<p>No books available.</p>";
}

$conn->close();
?>
