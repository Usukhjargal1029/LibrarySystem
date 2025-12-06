<?php
if(session_status() === PHP_SESSION_NONE) session_start();
require 'db_connection.php';

// Determine current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Language handling
$current_lang = $_GET['lang'] ?? $_SESSION['language'] ?? 'en';
$_SESSION['language'] = $current_lang;
include($current_lang=='mn' ? 'mn.php' : 'en.php');

// User info
$username = $_SESSION['username'] ?? 'Guest';
$stmt = $conn->prepare("SELECT avatar FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($userAvatarPath);
$stmt->fetch();
$stmt->close();

// Default avatar
if (!$userAvatarPath || !file_exists(__DIR__.'/'.$userAvatarPath)) {
    $userAvatarPath = 'images/default_avatar.png';
}

// Admin check
$is_admin = (($_SESSION['role'] ?? null) === 'admin') || !empty($_SESSION['is_admin']);
?>

<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($username) ?> - <?= $lang['my_profile'] ?? 'Profile' ?></title>

<!-- Shared CSS for all pages -->
<link rel="stylesheet" href="css/style.css" />

<!-- Header-specific CSS -->
<link rel="stylesheet" href="css/header.css" />

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

<!-- Page-specific CSS (optional) -->
<?php
$pageCSS = [
    'profile.php'   => 'css/profile.css',
    'mybooks.php'   => 'css/profile.css',
    'dashboard.php' => 'css/profile.css',
];
if (isset($pageCSS[$currentPage])) {
    echo '<link rel="stylesheet" href="' . $pageCSS[$currentPage] . '" />';
}
?>
</head>
<body>

<header class="new-header">
  <div class="header-left">
    <a href="index.php" class="logo"><img src="logo.jpg" alt="<?= $lang['logo'] ?? 'Logo' ?>" /></a>
    <nav class="main-nav">
      <ul>
        <li><a href="mybooks.php"><?= $lang['my_books'] ?? 'My Books' ?></a></li>
        <li class="dropdown">
          <a href="#" class="dropbtn"><?= $lang['browse_all'] ?? 'Browse' ?> <i class="fas fa-caret-down"></i></a>
          <div class="dropdown-content">
            <a href="#"><?= $lang['subjects'] ?? 'Subjects' ?></a>
            <a href="#"><?= $lang['trending'] ?? 'Trending' ?></a>
          </div>
        </li>
      </ul>
    </nav>
  </div>

  <div class="header-center">
    <div class="search-container">
      <select class="search-dropdown" id="searchType">
        <option value="all"><?= $lang['all'] ?? 'All' ?></option>
        <option value="title"><?= $lang['title'] ?? 'Title' ?></option>
        <option value="author"><?= $lang['author'] ?? 'Author' ?></option>
      </select>
      <input type="text" id="searchInput" class="search-input" placeholder="<?= $lang['search_placeholder'] ?? 'Search...' ?>" />
      <button id="searchButton" class="search-button"><i class="fas fa-search"></i></button>
    </div>
  </div>

  <div class="header-right">
    <?php if (isset($_SESSION['username'])): ?>
      <span class="header-greeting"><?= $lang['hello_user'] ?? 'Hello' ?>, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
      <div id="avatarContainer" title="<?= $lang['edit_avatar'] ?? 'Edit Avatar' ?>">
        <img src="<?= htmlspecialchars($userAvatarPath) ?>" alt="Avatar" id="headerAvatar" />
        <i class="fas fa-pencil-alt" id="editAvatarIcon"></i>
        <input type="file" id="avatarInput" accept="image/png, image/jpeg" style="display:none;">
      </div>
      <button id="userMenuToggle" class="hamburger-btn" aria-label="User menu" title="User menu">
        <i class="fas fa-bars"></i>
      </button>
      <div id="userDropdown" class="user-dropdown">
        <a href="profile.php"><?= $lang['my_profile'] ?? 'Profile' ?></a>
        <div class="language-select">
          <span><?= $lang['language'] ?? 'Language' ?></span>
          <ul>
            <li><a href="?lang=en">English</a></li>
            <li><a href="?lang=mn">Монгол</a></li>
          </ul>
        </div>
      </div>
      <?php if ($is_admin): ?>
        <a href="dashboard.php" class="header-btn"><?= $lang['dashboard'] ?? 'Dashboard' ?></a>
      <?php endif; ?>
      <a href="logout.php" class="header-btn"><?= $lang['log_out'] ?? 'Log Out' ?></a>
    <?php else: ?>
      <a href="login.php" class="header-btn"><?= $lang['log_in'] ?? 'Log In' ?></a>
      <a href="signup.php" class="header-btn"><?= $lang['sign_up'] ?? 'Sign Up' ?></a>
    <?php endif; ?>
  </div>
</header>

<!-- Inline JavaScript -->
	<!-- search bar -->
	<script>
document.getElementById("searchButton").addEventListener("click", function() {
    const type = document.getElementById("searchType").value;
    const query = document.getElementById("searchInput").value.trim();
    if (!query) return;
    // Example: redirect to search page with query params
    window.location.href = `search.php?type=${encodeURIComponent(type)}&q=${encodeURIComponent(query)}`;
});
</script>
	<!-- hamburger dropdown -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const userMenuToggle = document.getElementById("userMenuToggle");
  const userDropdown = document.getElementById("userDropdown");

  if (userMenuToggle && userDropdown) {
    // Toggle dropdown on button click
    userMenuToggle.addEventListener("click", e => {
      e.stopPropagation();
      userDropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", e => {
      if (!userDropdown.contains(e.target) && e.target !== userMenuToggle) {
        userDropdown.classList.remove("show");
      }
    });
  }
});
</script>
	<!-- upload avatar -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const avatarContainer = document.getElementById("avatarContainer");
  const avatarInput = document.getElementById("avatarInput");
  const headerAvatar = document.getElementById("headerAvatar");

  // Click pencil icon or container triggers file select
  avatarContainer.addEventListener("click", () => {
    avatarInput.click();
  });

  // When user selects a file
  avatarInput.addEventListener("change", () => {
    const file = avatarInput.files[0];
    if (!file) return;

    // Preview immediately
    const reader = new FileReader();
    reader.onload = e => {
      headerAvatar.src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Upload via AJAX (optional) or submit a hidden form
    const formData = new FormData();
    formData.append("avatar", file);

    fetch("upload_avatar.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        console.log("Avatar updated!");
      } else {
        alert("Failed to upload avatar.");
      }
    })
    .catch(err => {
      console.error(err);
      alert("Error uploading avatar.");
    });
  });
});
</script>
</body>
</html>
