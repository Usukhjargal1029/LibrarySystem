<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: login.php'); exit();
}
include 'header.php';

// Handle Add/Edit/Delete actions
$errors = [];
$success_msg = "";

function clean($data) {
    return htmlspecialchars(trim($data));
}

// Delete book
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image FROM books WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image']) && file_exists($row['image'])) {
            unlink($row['image']);
        }
    }
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $success_msg = "Book deleted successfully.";
}

// Add or Update book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $title = clean($_POST['title'] ?? '');
    $author = clean($_POST['author'] ?? '');
    $copies = intval($_POST['copies'] ?? 0);
    $description = clean($_POST['description'] ?? '');
    $genre = clean($_POST['genre'] ?? '');

    if (!$title || !$author) {
        $errors[] = "Title and Author are required.";
    }

    $imagePath = '';
    $imageUploaded = false;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errors[] = "Invalid image file type. Allowed types: jpg, jpeg, png, gif.";
        } else {
            $newFileName = 'images/' . uniqid('book_', true) . '.' . $fileExt;
            if (!move_uploaded_file($fileTmp, $newFileName)) {
                $errors[] = "Failed to upload image.";
            } else {
                $imagePath = $newFileName;
                $imageUploaded = true;
            }
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            if ($imageUploaded) {
                $stmt = $conn->prepare("SELECT image FROM books WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if (!empty($row['image']) && file_exists($row['image'])) {
                        unlink($row['image']);
                    }
                }
                $stmt = $conn->prepare("UPDATE books SET title=?, author=?, copies=?, description=?, image=?, genre=? WHERE id=?");
                $stmt->bind_param("ssisisi", $title, $author, $copies, $description, $imagePath, $genre, $id);
            } else {
                $stmt = $conn->prepare("UPDATE books SET title=?, author=?, copies=?, description=?, genre=? WHERE id=?");
                $stmt->bind_param("ssissi", $title, $author, $copies, $description, $genre, $id);
            }
            $stmt->execute();
            $success_msg = "Book updated successfully.";
        } else {
            $stmt = $conn->prepare("INSERT INTO books (title, author, copies, description, image, genre) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisss", $title, $author, $copies, $description, $imagePath, $genre);
            $stmt->execute();
            $success_msg = "New book added successfully.";
        }
    }
}

// Fetch books
$result = $conn->query("SELECT * FROM books ORDER BY id DESC");

$editBook = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $editBook = $res->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Books</title>
<style>
  
   body {
    font-family: Arial, sans-serif; /* Or whatever your main site font is */
}
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background-color: #007BFF; color: white; }
    a.button, button {
        background-color: #28a745; color: white; padding: 8px 12px; text-decoration: none; border: none; border-radius: 4px; cursor:pointer;
    }
    a.button.edit { background-color: #ffc107; color: black; }
    a.button.delete { background-color: #dc3545; }
    form { background:#f1f1f1; padding: 15px; border-radius:6px; margin-bottom: 40px; }
    label { display: block; margin-top: 10px; }
    input[type=text], input[type=number], textarea, select { width: 100%; padding: 8px; box-sizing: border-box; margin-top: 4px;}
    input[type=file] { margin-top: 8px; }
    .success { background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin-bottom: 20px; }
    .error { background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-bottom: 20px; }
    img.thumb { max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px; }
</style>
</head>
<body>
<div class="container">
    <h1>Manage Books</h1>

    <?php if ($success_msg): ?>
        <div class="success"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="error"><?= implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <h2><?= $editBook ? "Edit Book" : "Add New Book" ?></h2>
        <input type="hidden" name="id" value="<?= $editBook ? $editBook['id'] : '' ?>" />

        <label for="title">Title *</label>
        <input type="text" id="title" name="title" required value="<?= $editBook ? htmlspecialchars($editBook['title']) : '' ?>" />

        <label for="author">Author *</label>
        <input type="text" id="author" name="author" required value="<?= $editBook ? htmlspecialchars($editBook['author']) : '' ?>" />

        <label for="copies">Available Copies</label>
        <input type="number" id="copies" name="copies" min="0" value="<?= $editBook ? intval($editBook['copies']) : 1 ?>" />

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= $editBook ? htmlspecialchars($editBook['description']) : '' ?></textarea>

        <label for="genre">Genre/Category</label>
        <select id="genre" name="genre">
            <option value="">Select Genre</option>
            <option value="Romance" <?= ($editBook && $editBook['genre']=="Romance") ? 'selected' : '' ?>>Romance</option>
            <option value="Classic" <?= ($editBook && $editBook['genre']=="Classic") ? 'selected' : '' ?>>Classic</option>
            <option value="Science Fiction" <?= ($editBook && $editBook['genre']=="Science Fiction") ? 'selected' : '' ?>>Science Fiction</option>
            <option value="Fantasy" <?= ($editBook && $editBook['genre']=="Fantasy") ? 'selected' : '' ?>>Fantasy</option>
            <option value="Mystery" <?= ($editBook && $editBook['genre']=="Mystery") ? 'selected' : '' ?>>Mystery</option>
            <option value="Nonfiction" <?= ($editBook && $editBook['genre']=="Nonfiction") ? 'selected' : '' ?>>Nonfiction</option>
            <option value="Other" <?= ($editBook && $editBook['genre']=="Other") ? 'selected' : '' ?>>Other</option>
        </select>
        
        <label for="image">Cover Image <?= $editBook && $editBook['image'] ? '(upload to replace existing)' : '' ?></label>
        <input type="file" id="image" name="image" accept="image/*" />
        <?php if ($editBook && $editBook['image']): ?>
            <p>Current image:<br />
                <img src="<?= htmlspecialchars($editBook['image']) ?>" class="thumb" alt="Cover Image" />
            </p>
        <?php endif; ?>
        <br>
        <button type="submit"><?= $editBook ? "Update Book" : "Add Book" ?></button>
    </form>

    <h2>Book List</h2>
    <table>
        <thead>
            <tr>
                <th>Cover</th>
                <th>Title</th>
                <th>Author</th>
                <th>Copies</th>
                <th>Genre</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($book = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($book['image']) && file_exists($book['image'])): ?>
                            <img src="<?= htmlspecialchars($book['image']) ?>" class="thumb" alt="Book Image" />
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= intval($book['copies']) ?></td>
                    <td><?= htmlspecialchars($book['genre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($book['description'] ?? '', 0, 60, "...")) ?></td>
                    <td>
                        <a href="?edit=<?= $book['id'] ?>" class="button edit">Edit</a>
                        <a href="?delete=<?= $book['id'] ?>" class="button delete" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>