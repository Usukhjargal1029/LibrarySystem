<?php
session_start();
require 'db_connection.php';

// Check user logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $uploadDir = __DIR__ . '/uploads/avatars/' . $username . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['avatar'];
    $allowedMimeTypes = ['image/jpg', 'image/png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    // Basic validations
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = "File upload error. Please try again.";
    } elseif (!in_array(mime_content_type($file['tmp_name']), $allowedMimeTypes)) {
        $message = "Invalid file type. Only JPG and PNG are allowed.";
    } elseif ($file['size'] > $maxFileSize) {
        $message = "File size exceeds 2MB limit.";
    } else {
        // Generate unique file name
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'avatar_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Save relative path to DB, e.g. "uploads/avatars/username/avatar_123456.jpg"
            $relativePath = 'uploads/avatars/' . $username . '/' . $filename;

            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE username = ?");
            $stmt->bind_param('ss', $relativePath, $username);
            if ($stmt->execute()) {
                $message = "Avatar updated successfully.";
            } else {
                $message = "Database update failed: " . $conn->error;
            }
            $stmt->close();
        } else {
            $message = "Failed to move uploaded file.";
        }
    }
}

// Fetch current avatar path from DB
$stmt = $conn->prepare("SELECT avatar FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($avatarPath);
$stmt->fetch();
$stmt->close();

// Provide default avatar if none
if (empty($avatarPath) || !file_exists(__DIR__ . '/' . $avatarPath)) {
    $avatarPath = 'images/default_avatar.png'; // Adjust path to your default avatar
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Avatar - <?= htmlspecialchars($username) ?></title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    body { font-family: Arial, sans-serif; background: #e5decb; margin: 0; padding: 40px; }
    .container { max-width: 420px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 3px 12px #ccc; margin: auto; }
    h1 { margin-bottom: 20px; color: #0766c3; }
    .current-avatar {
        text-align: center;
        margin-bottom: 20px;
    }
    .current-avatar img {
        width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #0766c3;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }
    input[type="file"] {
        width: 100%;
        margin-bottom: 16px;
    }
    button[type="submit"] {
        background: #257ae7;
        border: none;
        color: white;
        padding: 10px 0;
        width: 100%;
        font-size: 16px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    button[type="submit"]:hover {
        background: #0d5fce;
    }
    .message {
        margin-top: 15px;
        font-weight: 600;
        color: green;
        text-align:center;
    }
    .error {
        color: red;
        text-align: center;
        margin-top: 15px;
        font-weight: 600;
    }
    a.back-link {
        display: inline-block;
        margin-bottom: 25px;
        color: #0766c3;
        text-decoration: none;
        font-weight: 600;
    }
    a.back-link:hover {
        text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="profile.php" class="back-link">&#8592; Back to Profile</a>
    <h1>Edit Avatar</h1>

    <div class="current-avatar">
      <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Current Avatar" />
    </div>

    <form method="POST" enctype="multipart/form-data">
      <label for="avatar">Choose new avatar (JPG, PNG; max 2MB):</label>
      <input type="file" name="avatar" id="avatar" accept=".jpg,.jpeg,.png" required />
      <button type="submit">Update Avatar</button>
    </form>

    <?php if ($message): ?>
      <p class="<?= strpos($message, 'failed') !== false || strpos($message, 'error') !== false ? 'error' : 'message' ?>">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>
  </div>
</body>
</html>