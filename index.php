<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: login.php'); exit();
}
include 'header.php';
?>

<style>
  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
  }
  body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #e5decb;
    font-family: Arial, sans-serif;
  }
  main {
    flex: 1 0 auto;
    max-width: 1200px;
    margin: 40px auto 0 auto;
    width: 100%;
  }
  .carousel-section {
    background: #fff;
    border-radius: 7px;
    box-shadow: 0 2px 10px #ddd;
    padding-bottom: 26px;
    margin-bottom: 38px;
    width: 900px;
    max-width: 99vw;
    margin-left: auto;
    margin-right: auto;
    position: relative;
  }
  .carousel-title {
    font-size: 22px;
    margin: 17px 47px 10px;
    color: #0766c3;
    text-align: left;
    font-weight: bold;
  }
  .book-carousel {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    scroll-behavior: smooth;
    gap: 16px;
    padding-bottom: 10px;
    margin: 0 32px;
    scroll-snap-type: x mandatory;
    scroll-padding: 0 32px;
  }
  .book-carousel > * {
    scroll-snap-align: start;
  }
  .book-carousel::before {
    content: '';
    flex: 0 0 1px;
  }
  .book-carousel::-webkit-scrollbar {
    height: 8px;
  }
  .book-carousel::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
  }
  .book-tile {
    flex: 0 0 130px;
    background: #f8fafd;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 7px;
    box-shadow: 0 1px 6px #d8d8d8;
    transition: box-shadow 0.2s;
  }
  .book-tile img {
    width: 90px;
    height: 135px;
    object-fit: cover;
    border-radius: 3px;
    margin-bottom: 10px;
    background: #fff;
    box-shadow: 0 1px 2px #aaa2;
  }
  .book-title {
    font-size: 15px;
    font-weight: 600;
    text-align: center;
    color: #222;
    margin: 2px 0 3px 0;
  }
  .book-author {
    font-size: 12px;
    color: #888;
    text-align: center;
    margin-bottom: 10px;
  }
  .book-btn {
    background: #257ae7;
    color: #fff;
    border: none;
    padding: 7px 0;
    width: 92px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    font-size: 14px;
    margin-top: auto;
    margin-bottom: 1px;
    box-shadow: 0 2px 6px #0002;
    transition: background 0.2s;
  }
  .book-btn[disabled] {
    background: #aaa;
    cursor: default;
  }
  .book-btn:hover:not([disabled]) {
    background: #1e62c8;
  }
  .carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #fff;
    border: none;
    font-size: 22px;
    font-weight: bold;
    color: #007bff;
    cursor: pointer;
    padding: 6px 10px;
    z-index: 10;
    box-shadow: 0 1px 6px #ccc;
    border-radius: 50%;
  }
  .carousel-nav.left { left: 8px; }
  .carousel-nav.right { right: 8px; }
</style>

<main>
  <?php
  $genres = [
    '' => $lang['available_books'] ?? 'Available Books', 
	 
    'Romance' => $lang['romance'] ?? 'Romance',
    'Fantasy' => $lang['fantasy'] ?? 'Fantasy',
    'History' => $lang['history'] ?? 'History'
];
    
    foreach ($genres as $genre => $title):
      $query = $genre ? "SELECT * FROM books WHERE genre='$genre' ORDER BY id DESC" : "SELECT * FROM books ORDER BY id DESC";
      $result = $conn->query($query);
  ?>
  <section class="carousel-section">
    <div class="carousel-title"><?= $title ?></div>
    <button class="carousel-nav left" onclick="scrollCarousel(this, -1)">&#10094;</button>
    <button class="carousel-nav right" onclick="scrollCarousel(this, 1)">&#10095;</button>
    <div class="book-carousel">
      <?php while($book = $result->fetch_assoc()): ?>
      <div class="book-tile" data-title="<?= strtolower(htmlspecialchars($book['title'])) ?>" data-author="<?= strtolower(htmlspecialchars($book['author'])) ?>">
        <a href="book_details.php?id=<?= $book['id'] ?>" style="text-decoration:none; color:inherit;">
          <img src="<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
          <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
          <div class="book-author"><?= htmlspecialchars($book['author']) ?></div>
        </a>
        <button class="book-btn borrow-btn" data-book-id="<?= $book['id'] ?>" <?= $book['copies'] <= 0 ? 'disabled' : '' ?>>
          <?= ($book['copies'] > 0) ? ($lang['borrow'] ?? 'Borrow') : ($lang['unavailable'] ?? 'Unavailable') ?>
        </button>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
  <?php endforeach; ?>
</main>


<script>
function scrollCarousel(button, direction) {
  const carousel = button.parentElement.querySelector('.book-carousel');
  const scrollAmount = 200 * direction;
  carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.borrow-btn').forEach(button => {
    button.addEventListener('click', function() {
      const btn = this;
      const bookId = btn.getAttribute('data-book-id');
      btn.disabled = true;
      btn.textContent = '<?= $lang['processing'] ?? 'Processing...' ?>';

      fetch('borrow_book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'book_id=' + encodeURIComponent(bookId)
      })
      .then(res => res.json())
      .then(data => {
        if(data.success) {
          alert('<?= $lang['borrow_success'] ?? 'Book borrowed successfully!' ?>');
          btn.textContent = '<?= $lang['unavailable'] ?? 'Unavailable' ?>';
        } else {
          alert('<?= $lang['error'] ?? 'Error' ?>: ' + data.message);
          btn.textContent = '<?= $lang['borrow'] ?? 'Borrow' ?>';
          btn.disabled = false;
        }
      })
      .catch(err => {
        alert('<?= $lang['request_failed'] ?? 'Request failed' ?>: ' + err.message);
        btn.textContent = '<?= $lang['borrow'] ?? 'Borrow' ?>';
        btn.disabled = false;
      });
    });
  });
});
</script>
<?php include 'footer.php'; ?>