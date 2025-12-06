<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); exit();
}
include 'header.php';

// Check if id parameter is present
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid book ID."; exit;
}

$bookId = intval($_GET['id']);

// Fetch book info
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $bookId);
$stmt->execute();
$result = $stmt->get_result();
$ratingStmt = $conn->prepare("SELECT rating FROM book_ratings WHERE user_id=? AND book_id=?");
$ratingStmt->bind_param("ii", $_SESSION['user_id'], $bookId);
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();
$myRating = $ratingResult->num_rows ? intval($ratingResult->fetch_assoc()['rating']) : 0;

if ($result->num_rows === 0) {
    echo "Book not found."; exit;
}

$book = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($book['title']) ?> - Book Details</title>
  <link rel="stylesheet" href="css/book_details.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php if (isset($_SESSION['message'])): ?>
<div class="message"><?= htmlspecialchars($_SESSION['message']) ?></div>
<?php unset($_SESSION['message']); endif; ?>

<main>
  <div class="container">
    <div class="cover">
      <img src="<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
      <div class="back-link-container">
        <a href="index.php">← <?= $lang['back_to_list'] ?? 'Back to book list' ?></a>
      </div>

      <?php if ($book['copies'] > 0): ?>
        <button class="borrow-btn" data-book-id="<?= $book['id'] ?>">
          <?= $lang['borrow_book'] ?? 'Borrow This Book' ?>
        </button>

        <!-- Read Online Link -->
        <?php if (!empty($book['readable_file'])): ?>
            <a href="read_book.php?id=<?= $book['id'] ?>" target="_blank" class="read-online-btn">
                <?= $lang['read_online'] ?? 'Read Online' ?>
            </a>
        <?php endif; ?>

        <!-- Star rating -->
       <div class="star-rating" data-book-id="<?= $book['id'] ?>" data-user-rating="<?= $myRating ?>">
          <span class="fa fa-star" data-value="1"></span>
          <span class="fa fa-star" data-value="2"></span>
          <span class="fa fa-star" data-value="3"></span>
          <span class="fa fa-star" data-value="4"></span>
          <span class="fa fa-star" data-value="5"></span>
        </div>
      <?php else: ?>
        <p class="unavailable"><?= $lang['unavailable'] ?? 'Currently unavailable for borrowing.' ?></p>
      <?php endif; ?>
    </div>

    <div class="details">
      <h1><?= htmlspecialchars($book['title']) ?></h1>
      <?php if (!empty($book['subtitle'])): ?>
        <h2><?= htmlspecialchars($book['subtitle']) ?></h2>
      <?php endif; ?>

      <p class="author"><?= $lang['author'] ?? 'Author' ?>: <?= htmlspecialchars($book['author']) ?></p>

      <div class="description">
        <strong><?= $lang['description'] ?? 'Description' ?>:</strong>
        <p id="descText" 
           data-full-text="<?= htmlspecialchars(strip_tags($book['description'] ?? 'No description available.')) ?>">
        </p>
        <?php if (!empty($book['description']) && strlen(strip_tags($book['description'])) > 300): ?>
          <span id="readMoreLink"
                data-show-more="<?= $lang['read_more'] ?? 'Read more' ?>"
                data-show-less="<?= $lang['show_less'] ?? 'Show less' ?>">
            <?= $lang['read_more'] ?? 'Read more' ?> ▾
          </span>
        <?php endif; ?>
      </div>

      <div class="meta">
        <div class="meta-item"><strong><?= $lang['available_copies'] ?? 'Available Copies' ?></strong><?= htmlspecialchars($book['copies']) ?></div>
        <div class="meta-item"><strong><?= $lang['language'] ?? 'Language' ?></strong><?= htmlspecialchars($book['language'] ?? 'Unknown') ?></div>
        <div class="meta-item"><strong><?= $lang['publisher'] ?? 'Publisher' ?></strong><?= htmlspecialchars($book['publisher'] ?? 'Unknown') ?></div>
        <div class="meta-item"><strong><?= $lang['pages'] ?? 'Pages' ?></strong><?= htmlspecialchars($book['pages'] ?? 'N/A') ?></div>
        <div class="meta-item"><strong><?= $lang['published'] ?? 'Published' ?></strong><?= htmlspecialchars($book['publish_date'] ?? 'Unknown') ?></div>
      </div>
    </div>
  </div>
</main>

<script src="js/book_details.js"></script>
</body>

</html>
<?php include 'footer.php'; ?>
