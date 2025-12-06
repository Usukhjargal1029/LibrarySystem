<?php
session_start();
require 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $allowedMimeTypes = ['image/jpeg', 'image/png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload error']);
        exit;
    }

    // Validate MIME type safely
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit;
    }

    if ($file['size'] > $maxFileSize) {
        echo json_encode(['success' => false, 'error' => 'File size exceeds limit']);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/avatars/' . $username . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'avatar_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $relativePath = 'uploads/avatars/' . $username . '/' . $filename;

        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE username = ?");
        $stmt->bind_param("ss", $relativePath, $username);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'avatar_path' => $relativePath]);
        } else {
            echo json_encode(['success' => false, 'error' => 'DB update failed']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
}
