<?php
require_once 'db_connection.php';

$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query === '') {
    echo "<p>No search term provided.</p>";
    exit;
}

$query = mysqli_real_escape_string($conn, $query);

switch ($type) {
    case 'title':
        $sql = "SELECT * FROM books WHERE title LIKE '%$query%'";
        break;
    case 'author':
        $sql = "SELECT * FROM books WHERE author LIKE '%$query%'";
        break;
    default: // 'all'
        $sql = "SELECT * FROM books WHERE title LIKE '%$query%' OR author LIKE '%$query%'";
        break;
}

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="book">';
        echo '<img src="' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["title"]) . '">';
        echo '<h3>' . htmlspecialchars($row["title"]) . '</h3>';
        echo '<p>Author: ' . htmlspecialchars($row["author"]) . '</p>';

        if ((int)$row["copies"] <= 0) {
            echo '<button class="action-btn borrow-btn" disabled>Borrowed</button>';
        } else {
            echo '<button class="action-btn borrow-btn" data-book-id="' . $row["id"] . '">Borrow</button>';
        }

        echo '</div>';
    }
} else {
    echo "<p>No books found matching your search.</p>";
}
?>
