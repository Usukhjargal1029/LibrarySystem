<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: login.php'); exit();
}
include 'header.php';
?>

<!-- Simple CSS addition for profile avatar editing -->
<style>
.profile-avatar {
    position: relative;
    cursor: pointer;
    display: inline-block;
}

.profile-avatar:hover::after {
    content: '\f030'; /* FontAwesome camera icon */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 18px;
    background: rgba(0,0,0,0.6);
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    opacity: 0;
    animation: fadeIn 0.2s forwards;
}

@keyframes fadeIn {
    to { opacity: 1; }
}
</style>

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
                <div class="profile-avatar" onclick="document.getElementById('avatarInput').click()" title="<?= $lang['change_avatar'] ?? 'Click to change avatar' ?>">
                    <img src="<?= htmlspecialchars($userAvatarPath) ?>" alt="<?= $lang['avatar'] ?? 'Avatar' ?>" />
                </div>
                <div class="profile-header-content">
                    <span class="profile-username"><?= htmlspecialchars($username) ?></span>
                </div>
            </div>
            <div class="profile-links">
                <a href="profile.php"><?= $lang['my_reading_stats'] ?? 'My Reading Stats' ?></a>
                <a href="profile.php"><?= $lang['import_export'] ?? 'Import & Export' ?></a>
                <a href="profile.php" style="background:#eaf1fb;border:none;color:#1987f6;font-weight:700;">
                    <?= $lang['privacy_public'] ?? 'Privacy: Public' ?>
                </a>
            </div>
        </section>
    </div>
</main>

<!-- Hidden form that submits to your existing upload_avatar.php -->
<form id="avatarForm" method="POST" action="upload_avatar.php" enctype="multipart/form-data" style="display:none;">
    <input type="file" id="avatarInput" name="avatar" accept="image/png,image/jpeg" onchange="document.getElementById('avatarForm').submit();">
</form>

<?php include 'footer.php'; ?>
