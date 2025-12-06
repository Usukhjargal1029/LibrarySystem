<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

require_once 'header.php';
$current_lang = $_GET['lang'] ?? $_SESSION['language'] ?? 'en';
$_SESSION['language'] = $current_lang;
include($current_lang == 'mn' ? 'mn.php' : 'en.php');

// Configuration
define('UPLOAD_DIR', 'images/');
define('PDF_DIR', 'pdfs/');
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_PDF_EXTENSIONS', ['pdf']);
define('GENRES', [
    'Romance',
    'Classic', 
    'Science Fiction',
    'Fantasy',
    'Mystery',
    'Thriller',
    'Horror',
    'Biography',
    'History',
    'Self-Help',
    'Nonfiction',
    'Young Adult',
    'Children',
    'Poetry',
    'Other',
	'Online Readable'
]);

// Initialize variables
$errors = [];
$success = '';
$editMode = false;
$bookData = [
    'id' => 0,
    'title' => '',
    'author' => '',
    'isbn' => '',
    'copies' => 1,
    'description' => '',
    'genre' => '',
    'image' => ''
];

// Ensure upload directories exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!file_exists(PDF_DIR)) {
    mkdir(PDF_DIR, 0777, true);
}

// Helper functions
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function deleteImage($imagePath) {
    if (!empty($imagePath) && file_exists($imagePath)) {
        return unlink($imagePath);
    }
    return false;
}

function uploadImage($file, $oldImagePath = null) {
    global $errors;
    
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed with error code: " . $file['error'];
        return false;
    }
    
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    
    // Validate file extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXTENSIONS)) {
        $errors[] = "Invalid image file type. Allowed: " . implode(', ', ALLOWED_IMAGE_EXTENSIONS);
        return false;
    }
    
    // Validate file size (5MB max)
    if ($fileSize > 5 * 1024 * 1024) {
        $errors[] = "Image file size exceeds 5MB limit.";
        return false;
    }
    
    // Validate it's actually an image
    $imageInfo = getimagesize($fileTmp);
    if ($imageInfo === false) {
        $errors[] = "Invalid image file.";
        return false;
    }
    
    // Generate unique filename
    $newFileName = UPLOAD_DIR . uniqid('book_') . '_' . time() . '.' . $ext;
    
    // Move uploaded file
    if (move_uploaded_file($fileTmp, $newFileName)) {
        // Delete old image if exists
        if ($oldImagePath) {
            deleteImage($oldImagePath);
        }
        return $newFileName;
    } else {
        $errors[] = "Failed to save uploaded image file.";
        return false;
    }
}

function uploadPDF($file, $oldPDFPath = null) {
    global $errors;
    
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "PDF upload failed with error code: " . $file['error'];
        return false;
    }
    
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    
    // Validate file extension
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_PDF_EXTENSIONS)) {
        $errors[] = "Invalid file type. Only PDF files are allowed.";
        return false;
    }
    
    // Validate file size (20MB max for PDFs)
    if ($fileSize > 20 * 1024 * 1024) {
        $errors[] = "PDF file size exceeds 20MB limit.";
        return false;
    }
    
    // Validate it's actually a PDF
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmp);
    finfo_close($finfo);
    
    if ($mimeType !== 'application/pdf') {
        $errors[] = "Invalid PDF file.";
        return false;
    }
    
    // Generate unique filename
    $newFileName = PDF_DIR . uniqid('book_') . '_' . time() . '.pdf';
    
    // Move uploaded file
    if (move_uploaded_file($fileTmp, $newFileName)) {
        // Delete old PDF if exists
        if ($oldPDFPath) {
            deleteImage($oldPDFPath);
        }
        return $newFileName;
    } else {
        $errors[] = "Failed to save uploaded PDF file.";
        return false;
    }
}

function getBookById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Handle Delete Action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    // Get book details first
    $book = getBookById($conn, $deleteId);
    if ($book) {
        // Delete associated images
        deleteImage($book['image']);
        deleteImage($book['cover_image']);
        deleteImage($book['readable_file']);
        
        // Delete book record
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        if ($stmt->execute()) {
            $success = "Book deleted successfully.";
        } else {
            $errors[] = "Failed to delete book from database.";
        }
    } else {
        $errors[] = "Book not found.";
    }
}

// Handle Edit Mode
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $book = getBookById($conn, $editId);
    if ($book) {
        $editMode = true;
        $bookData = array_merge($bookData, $book);
    } else {
        $errors[] = "Book not found for editing.";
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate form data
    $bookData['id'] = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $bookData['title'] = clean($_POST['title'] ?? '');
    $bookData['author'] = clean($_POST['author'] ?? '');
    $bookData['isbn'] = clean($_POST['isbn'] ?? '');
    $bookData['copies'] = max(0, intval($_POST['copies'] ?? 1));
    $bookData['description'] = clean($_POST['description'] ?? '');
    $bookData['genre'] = clean($_POST['genre'] ?? '');
    
    // Validation
    if (empty($bookData['title'])) {
        $errors[] = "Title is required.";
    }
    if (empty($bookData['author'])) {
        $errors[] = "Author is required.";
    }
    if (strlen($bookData['title']) > 100) {
        $errors[] = "Title must be 100 characters or less.";
    }
    if (strlen($bookData['author']) > 100) {
        $errors[] = "Author must be 100 characters or less.";
    }
    if (!empty($bookData['isbn']) && !preg_match('/^[0-9\-X]{10,20}$/', $bookData['isbn'])) {
        $errors[] = "Invalid ISBN format.";
    }
    
    // Process image upload if no errors
    if (empty($errors)) {
        $imagePath = null;
        $pdfPath = null;
        
        if ($bookData['id'] > 0) {
            // Editing existing book
            $existingBook = getBookById($conn, $bookData['id']);
            $imagePath = $existingBook['image']; // Keep existing image by default
            $pdfPath = $existingBook['readable_file']; // Keep existing PDF by default
            
            // Check if new image uploaded
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newImagePath = uploadImage($_FILES['image'], $existingBook['image']);
                if ($newImagePath !== false) {
                    $imagePath = $newImagePath ?: $imagePath;
                }
            }
            
            // Check if new PDF uploaded
            if (isset($_FILES['readable_file']) && $_FILES['readable_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newPDFPath = uploadPDF($_FILES['readable_file'], $existingBook['readable_file']);
                if ($newPDFPath !== false) {
                    $pdfPath = $newPDFPath ?: $pdfPath;
                }
            }
        } else {
            // Adding new book
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newImagePath = uploadImage($_FILES['image']);
                if ($newImagePath !== false) {
                    $imagePath = $newImagePath;
                }
            }
            
            if (isset($_FILES['readable_file']) && $_FILES['readable_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newPDFPath = uploadPDF($_FILES['readable_file']);
                if ($newPDFPath !== false) {
                    $pdfPath = $newPDFPath;
                }
            }
        }
        
        // Save to database if no errors
        if (empty($errors)) {
            if ($bookData['id'] > 0) {
                // Update existing book
                $stmt = $conn->prepare("
                    UPDATE books 
                    SET title = ?, author = ?, isbn = ?, copies = ?, 
                        description = ?, genre = ?, image = ?, readable_file = ?
                    WHERE id = ?
                ");
                $stmt->bind_param(
                    "sssissssi",
                    $bookData['title'],
                    $bookData['author'],
                    $bookData['isbn'],
                    $bookData['copies'],
                    $bookData['description'],
                    $bookData['genre'],
                    $imagePath,
                    $pdfPath,
                    $bookData['id']
                );
                
                if ($stmt->execute()) {
                    $success = "Book updated successfully.";
                    // Reset form after successful update
                    $editMode = false;
                    $bookData = [
                        'id' => 0,
                        'title' => '',
                        'author' => '',
                        'isbn' => '',
                        'copies' => 1,
                        'description' => '',
                        'genre' => '',
                        'image' => ''
                    ];
                } else {
                    $errors[] = "Failed to update book: " . $conn->error;
                }
            } else {
                // Insert new book
                $stmt = $conn->prepare("
                    INSERT INTO books (title, author, isbn, copies, description, genre, image, readable_file)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    "sssissss",
                    $bookData['title'],
                    $bookData['author'],
                    $bookData['isbn'],
                    $bookData['copies'],
                    $bookData['description'],
                    $bookData['genre'],
                    $imagePath,
                    $pdfPath
                );
                
                if ($stmt->execute()) {
                    $success = "Book added successfully.";
                    // Reset form after successful insert
                    $bookData = [
                        'id' => 0,
                        'title' => '',
                        'author' => '',
                        'isbn' => '',
                        'copies' => 1,
                        'description' => '',
                        'genre' => '',
                        'image' => ''
                    ];
                } else {
                    $errors[] = "Failed to add book: " . $conn->error;
                }
            }
        }
    }
}

// Fetch all books for display
$searchQuery = isset($_GET['search']) ? clean($_GET['search']) : '';
$filterGenre = isset($_GET['filter_genre']) ? clean($_GET['filter_genre']) : '';

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];
$types = "";

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $params[] = &$searchParam;
    $types .= "sss";
}

if (!empty($filterGenre)) {
    $sql .= " AND genre = ?";
    $params[] = &$filterGenre;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['manage_books_title'] ?> - Library System</title>
  <link rel="stylesheet" href="css/manage_books.css">
</head>
<body>
    <div class="container">
        <h1>📚 <?= $lang['manage_books_title'] ?></h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✓ <?= $success ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong><?= $lang['please_fix_errors'] ?></strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2><?= $editMode ? '✏️ '.$lang['edit_book'] : '➕ '.$lang['add_new_book'] ?></h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $bookData['id'] ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title"><?= $lang['title'] ?> *</label>
                        <input type="text" id="title" name="title" 
                               value="<?= clean($bookData['title']) ?>" 
                               required maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="author"><?= $lang['author'] ?> *</label>
                        <input type="text" id="author" name="author" 
                               value="<?= clean($bookData['author']) ?>" 
                               required maxlength="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="isbn"><?= $lang['isbn'] ?></label>
                        <input type="text" id="isbn" name="isbn" 
                               value="<?= clean($bookData['isbn'] ?? '') ?>" 
                               maxlength="20" placeholder="<?= $lang['isbn_placeholder'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="copies"><?= $lang['available_copies'] ?></label>
                        <input type="number" id="copies" name="copies" 
                               value="<?= $bookData['copies'] ?>" 
                               min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="genre"><?= $lang['genre_category'] ?></label>
                        <select id="genre" name="genre">
                            <option value="">-- <?= $lang['select_genre'] ?> --</option>
                            <?php foreach (GENRES as $genre): ?>
                                <option value="<?= $genre ?>" 
                                        <?= $bookData['genre'] == $genre ? 'selected' : '' ?>>
                                    <?= $genre ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="image"><?= $lang['cover_image'] ?></label>
                        <input type="file" id="image" name="image" accept="image/*">
                        
                        <?php if ($editMode && !empty($bookData['image']) && file_exists($bookData['image'])): ?>
                            <div class="current-image">
                                <small><?= $lang['current_image'] ?>:</small><br>
                                <img src="<?= clean($bookData['image']) ?>" alt="<?= $lang['current_cover'] ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="readable_file">📄 <?= $lang['readable_pdf'] ?></label>
                    <input type="file" id="readable_file" name="readable_file" accept="application/pdf">
                    
                    <?php if ($editMode && !empty($bookData['readable_file']) && file_exists($bookData['readable_file'])): ?>
                        <div class="current-image">
                            <small><?= $lang['current_pdf'] ?> </small>
                            <a href="read_book.php?id=<?= $bookData['id'] ?>" target="_blank" style="color: #3498db;">
                                📄 <?= $lang['view_current_pdf'] ?>
                            </a>
                            <small>(<?= $lang['upload_new_file_to_replace'] ?>)</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group full-width">
                    <label for="description"><?= $lang['description'] ?></label>
                    <textarea id="description" name="description" 
                              placeholder="<?= $lang['enter_book_description'] ?>"><?= clean($bookData['description'] ?? '') ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <?= $editMode ? '💾 '.$lang['update_book'] : '➕ '.$lang['add_book'] ?>
                    </button>
                    
                    <?php if ($editMode): ?>
                        <a href="?" class="btn btn-secondary"><?= $lang['cancel'] ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="search-filter">
            <form method="GET">
                <input type="text" name="search" placeholder="<?= $lang['search_placeholder_manage'] ?>" 
                       value="<?= $searchQuery ?>">
                <select name="filter_genre">
                    <option value=""><?= $lang['all_genres'] ?></option>
                    <?php foreach (GENRES as $genre): ?>
                        <option value="<?= $genre ?>" <?= $filterGenre == $genre ? 'selected' : '' ?>>
                            <?= $genre ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">🔍 <?= $lang['search'] ?></button>
                <?php if ($searchQuery || $filterGenre): ?>
                    <a href="?" class="btn btn-secondary"><?= $lang['clear'] ?></a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><?= $lang['cover'] ?></th>
                        <th><?= $lang['title'] ?></th>
                        <th><?= $lang['author'] ?></th>
                        <th><?= $lang['isbn'] ?></th>
                        <th><?= $lang['copies'] ?></th>
                        <th><?= $lang['genre'] ?></th>
                        <th><?= $lang['pdf'] ?></th>
                        <th><?= $lang['actions'] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($book = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($book['image']) && file_exists($book['image'])): ?>
                                        <img src="<?= clean($book['image']) ?>" 
                                             alt="<?= $lang['cover'] ?>" class="book-cover">
                                    <?php else: ?>
                                        <div class="no-image"><?= $lang['no_image'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= clean($book['title']) ?></strong>
                                    <?php if (!empty($book['description'])): ?>
                                        <br>
                                        <small style="color: #7f8c8d;">
                                            <?= clean(mb_strimwidth($book['description'], 0, 60, '...')) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= clean($book['author']) ?></td>
                                <td><?= clean($book['isbn'] ?? '-') ?></td>
                                <td>
                                    <?php if ($book['copies'] == 0): ?>
                                        <span class="badge badge-warning"><?= $lang['out_of_stock'] ?></span>
                                    <?php elseif ($book['copies'] < 5): ?>
                                        <span class="badge badge-info"><?= $book['copies'] ?> <?= $lang['left'] ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?= $book['copies'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= clean($book['genre'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($book['readable_file']) && file_exists($book['readable_file'])): ?>
                                        <a href="read_book.php?id=<?= $book['id'] ?>" target="_blank" 
                                           class="btn btn-success" style="padding: 4px 8px; font-size: 11px;">
                                            📄 <?= $lang['view'] ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #95a5a6;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="?edit=<?= $book['id'] ?>" 
                                           class="btn btn-warning" style="padding: 6px 12px; font-size: 12px;">
                                            <?= $lang['edit'] ?>
                                        </a>
                                        <a href="?delete=<?= $book['id'] ?>" 
                                           class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;"
                                           onclick="return confirm('<?= $lang['confirm_delete'] ?> &quot;<?= clean($book['title']) ?>&quot;?');">
                                            <?= $lang['delete'] ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <h3>📚 <?= $lang['no_books_found'] ?></h3>
                                    <p><?= $lang['start_adding_books'] ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>