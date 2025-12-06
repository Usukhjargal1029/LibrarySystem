<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: login.php'); exit();
}

include 'header.php';

// Borrowed books per genre
$genreStmt = $conn->prepare("
    SELECT b.genre, COUNT(bb.id) AS borrowed_count
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    GROUP BY b.genre
");
$genreStmt->execute();
$genreResult = $genreStmt->get_result();

$borrowedGenres = [];
$borrowedCounts = [];
while($row = $genreResult->fetch_assoc()) {
    $borrowedGenres[] = $row['genre'] ?: ($lang['unknown'] ?? 'Unknown');
    $borrowedCounts[] = intval($row['borrowed_count']);
}

// Average rating per genre
$ratingStmt = $conn->prepare("
    SELECT b.genre, AVG(br.rating) AS avg_rating
    FROM book_ratings br
    JOIN books b ON br.book_id = b.id
    GROUP BY b.genre
");
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();

$ratingGenres = [];
$avgRatings = [];
while($row = $ratingResult->fetch_assoc()) {
    $ratingGenres[] = $row['genre'] ?: ($lang['unknown'] ?? 'Unknown');
    $avgRatings[] = round(floatval($row['avg_rating']), 2);
}
?>

<main>
  <div class="profile-wrapper">
    <aside class="profile-sidebar">
      <ul>
        <li><a href="profile.php" class="<?= $currentPage=='profile.php'?'active':'' ?>"><i class="fas fa-user"></i> <?= $lang['my_profile'] ?? 'My Profile' ?></a></li>
        <li><a href="mybooks.php" class="<?= $currentPage=='mybooks.php'?'active':'' ?>"><i class="fas fa-book"></i> <?= $lang['my_books'] ?? 'My Books' ?></a></li>
        <?php if($is_admin): ?>
        <li><a href="dashboard.php" class="<?= $currentPage=='dashboard.php'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> <?= $lang['dashboard'] ?? 'Dashboard' ?></a></li>
        <?php endif; ?>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <?= $lang['log_out'] ?? 'Log Out' ?></a></li>
      </ul>
    </aside>

    <section class="profile-main">
      <div class="profile-header">
        <div class="profile-avatar" onclick="document.getElementById('avatarInput').click()" title="<?= $lang['change_avatar'] ?? 'Click to change avatar' ?>">
          <img src="<?= htmlspecialchars($userAvatarPath ?? 'default-avatar.png') ?>" alt="<?= $lang['avatar'] ?? 'Avatar' ?>" />
        </div>
        <div class="profile-header-content">
          <span class="profile-username"><?= htmlspecialchars($username ?? '') ?></span>
        </div>
      </div>

      <div class="profile-links">
        <a href="admin_returns.php"><?= $lang['return_books'] ?? 'Return Books' ?></a>
        <a href="manage_books.php"><?= $lang['manage_books'] ?? 'Manage Books' ?></a>
        <a href="profile.php" style="background:#eaf1fb;border:none;color:#1987f6;font-weight:700;"><?= $lang['privacy_public'] ?? 'Privacy: Public' ?></a>
      </div>

      <div class="chart-carousel">
        <button class="arrow left">&#10094;</button>
        <div class="chart-track">
          <div class="chart-slide">
            <h2><?= $lang['borrowed_books_by_genre'] ?? 'Borrowed Books by Genre' ?></h2>
            <div style="display:flex; align-items:center;">
              <canvas id="borrowedGenreChart" style="width:250px; height:250px;"></canvas>
              <div id="borrowedGenreLegend" class="chart-legend"></div>
            </div>
          </div>
          <div class="chart-slide">
            <h2><?= $lang['average_rating_by_genre'] ?? 'Average Rating by Genre' ?></h2>
            <canvas id="ratingGenreChart" style="width:400px; height:250px;"></canvas>
          </div>
        </div>
        <button class="arrow right">&#10095;</button>
      </div>
    </section>
  </div>
</main>

<form id="avatarForm" method="POST" action="upload_avatar.php" enctype="multipart/form-data" style="display:none;">
  <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg"
         onchange="document.getElementById('avatarForm').submit();">
</form>

<style>
.profile-avatar { position: relative; cursor: pointer; display: inline-block; }
.profile-avatar:hover::after {
    content: '\f030';
    font-family: 'Font Awesome 6 Free'; font-weight: 900;
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    color: white; font-size: 18px; background: rgba(0,0,0,0.6);
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; opacity: 0; animation: fadeIn 0.2s forwards;
}
@keyframes fadeIn { to { opacity: 1; } }

.chart-carousel { position: relative; width: 100%; overflow: hidden; margin-top: 30px; }
.chart-track { display: flex; transition: transform 0.5s ease; }
.chart-slide { flex: 0 0 100%; display: flex; flex-direction: column; align-items: center; }
.arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.3); border: none; color: white; font-size: 24px; cursor: pointer; padding: 10px; z-index: 1; }
.arrow.left { left: 10px; } .arrow.right { right: 10px; }
.chart-legend { margin-left: 20px; display: flex; flex-direction: column; font-size: 14px; }
.legend-item { display: flex; align-items: center; margin-bottom: 5px; }
.legend-color { width: 16px; height: 16px; margin-right: 8px; border-radius: 4px; }
</style>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const borrowedGenres = <?= json_encode($borrowedGenres) ?>;
const borrowedCounts = <?= json_encode($borrowedCounts) ?>;
const ratingGenres = <?= json_encode($ratingGenres) ?>;
const avgRatings = <?= json_encode($avgRatings) ?>;
const colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF'];

const borrowedChart = new Chart(document.getElementById('borrowedGenreChart'), {
    type: 'pie',
    data: { labels: borrowedGenres, datasets: [{ data: borrowedCounts, backgroundColor: colors }] },
    options: { plugins: { legend: { display: false } } }
});

const legendContainer = document.getElementById('borrowedGenreLegend');
const total = borrowedCounts.reduce((a,b)=>a+b,0);
borrowedGenres.forEach((genre,index)=>{
    const percent = total ? ((borrowedCounts[index]/total)*100).toFixed(1) : 0;
    const item = document.createElement('div');
    item.classList.add('legend-item');
    const colorBox = document.createElement('div');
    colorBox.classList.add('legend-color');
    colorBox.style.backgroundColor = colors[index % colors.length];
    const text = document.createElement('span');
    text.textContent = `${genre}: ${percent}%`;
    item.appendChild(colorBox);
    item.appendChild(text);
    legendContainer.appendChild(item);
});

new Chart(document.getElementById('ratingGenreChart'), {
    type: 'bar',
    data: { labels: ratingGenres, datasets: [{ label: '<?= $lang['average_rating'] ?? 'Average Rating' ?>', data: avgRatings, backgroundColor: '#36A2EB' }] },
    options: { scales: { y: { beginAtZero: true, max: 5 } } }
});

const track = document.querySelector('.chart-track');
const slides = document.querySelectorAll('.chart-slide');
const leftArrow = document.querySelector('.arrow.left');
const rightArrow = document.querySelector('.arrow.right');
let currentIndex = 0;
function updateSlide() { track.style.transform = `translateX(-${currentIndex * 100}%)`; }
leftArrow.addEventListener('click', () => { currentIndex = (currentIndex - 1 + slides.length) % slides.length; updateSlide(); });
rightArrow.addEventListener('click', () => { currentIndex = (currentIndex + 1) % slides.length; updateSlide(); });
</script>
