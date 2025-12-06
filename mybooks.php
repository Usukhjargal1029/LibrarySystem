<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: login.php'); 
    exit();
}

// Variables
$username = $_SESSION['username'];
$is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : false;

// Avatar
$userAvatarPath = 'uploads/avatars/' . $username . '.jpg';
if (!file_exists($userAvatarPath)) {
    $userAvatarPath = 'assets/images/default-avatar.png';
}

// Language array
$lang = [
    'my_reading_stats' => 'My Reading Stats',
    'import_export' => 'Import & Export', 
    'privacy_public' => 'Privacy: Public'
];

include 'header.php';

// Fetch books
$currentBooksStmt = $conn->prepare("
    SELECT b.title, b.image, bb.due_date
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    WHERE bb.username = ? AND bb.returned = 0
");
$currentBooksStmt->bind_param("s", $username);
$currentBooksStmt->execute();
$currentBooks = $currentBooksStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pastBooksStmt = $conn->prepare("
    SELECT b.title, b.image, bb.return_date
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    WHERE bb.username = ? AND bb.returned = 1
");
$pastBooksStmt->bind_param("s", $username);
$pastBooksStmt->execute();
$pastBooks = $pastBooksStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<main>
    <div class="profile-wrapper">
        <aside class="profile-sidebar">
            <ul>
                <li>
                    <a href="profile.php" class="<?= $currentPage=='profile.php'?'active':'' ?>">
                        <i class="fas fa-user"></i> <?= $lang['my_profile'] ?? 'My Profile' ?>
                    </a>
                </li>
                <li>
                    <a href="mybooks.php" class="<?= $currentPage=='mybooks.php'?'active':'' ?>">
                        <i class="fas fa-book"></i> <?= $lang['my_books'] ?? 'My Books' ?>
                    </a>
                </li>
                <?php if($is_admin): ?>
                <li>
                    <a href="dashboard.php" class="<?= $currentPage=='dashboard.php'?'active':'' ?>">
                        <i class="fas fa-tachometer-alt"></i> <?= $lang['dashboard'] ?? 'Dashboard' ?>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> <?= $lang['log_out'] ?? 'Log Out' ?>
                    </a>
                </li>
            </ul>
        </aside>

    <section class="profile-main">
      <div class="profile-header">
        <div class="profile-avatar" onclick="document.getElementById('avatarInput').click()" title="Click to change avatar">
          <img src="<?= htmlspecialchars($userAvatarPath) ?>" alt="Avatar" />
        </div>
        <form id="avatarForm" method="POST" action="upload_avatar.php" enctype="multipart/form-data" style="display:none;">
          <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg" onchange="document.getElementById('avatarForm').submit();">
        </form>
        <div class="profile-header-content">
          <span class="profile-username"><?= htmlspecialchars($username) ?></span>
        </div>
      </div>

      <div class="profile-links">
        <a href="profile.php"><?= $lang['my_reading_stats'] ?></a>
        <a href="profile.php"><?= $lang['import_export'] ?></a>
        <a href="profile.php" style="background:#eaf1fb;border:none;color:#1987f6;font-weight:700;"><?= $lang['privacy_public'] ?></a>
      </div>

<!-- Currently Borrowed Books Carousel -->
<h3><?= $lang['currently_borrowed_books'] ?></h3>
<div class="carousel">
    <?php foreach($currentBooks as $book): 
        $cover = !empty($book['image']) ? $book['image'] : 'assets/images/default-cover.png';
        $title = $book['title'] ?? '';
    ?>
        <div class="carousel-item">
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($title) ?>">
            <p><?= htmlspecialchars($title) ?></p>
            <small><?= $lang['due'] ?>: <?= htmlspecialchars($book['due_date'] ?? '') ?></small>
        </div>
    <?php endforeach; ?>
</div>

<!-- Previously Borrowed Books Carousel -->
<h3><?= $lang['previously_borrowed_books'] ?></h3>
<div class="carousel">
    <?php foreach($pastBooks as $book): 
        $cover = !empty($book['image']) ? $book['image'] : 'assets/images/default-cover.png';
        $title = $book['title'] ?? '';
    ?>
        <div class="carousel-item">
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($title) ?>">
            <p><?= htmlspecialchars($title) ?></p>
            <small><?= $lang['returned'] ?>: <?= htmlspecialchars($book['return_date'] ?? '') ?></small>
        </div>
    <?php endforeach; ?>
</div>

    </section>
  </div>
</main>

<style>
.profile-avatar { position: relative; cursor: pointer; display: inline-block; }
.profile-avatar:hover::after {
    content: '\f030';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 18px;
    background: rgba(0,0,0,0.6);
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%; opacity: 0;
    animation: fadeIn 0.2s forwards;
}
@keyframes fadeIn { to { opacity: 1; } }

/* Carousel styles */
.carousel { display: flex; overflow-x: auto; gap: 15px; padding: 10px 0; }
.carousel-item { flex: 0 0 auto; width: 150px; text-align: center; }
.carousel-item img { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
</style>

<?php include 'footer.php'; ?>
