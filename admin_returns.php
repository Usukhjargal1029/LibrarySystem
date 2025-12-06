<?php
$currentPage = basename($_SERVER['PHP_SELF']);
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: login.php'); exit();
}
include 'header.php';

// Handle Clear Returned Books POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_returned'])) {
    $clearSql = "DELETE FROM borrowed_books WHERE return_date IS NOT NULL";
    if ($conn->query($clearSql)) {
        $_SESSION['message'] = $lang['returned_books_cleared']; // 
    } else {
        $_SESSION['message'] = $lang['error_clearing_returned_books'] . $conn->error; // ✅
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch borrowed books with due date, return date, late fee
$sql = "SELECT bb.id, bb.username, b.title, bb.borrowed_at, bb.due_date, bb.return_date, bb.late_fee
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.id
        ORDER BY bb.borrowed_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?= $lang['admin_return_panel'] ?></title> <!--  -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
<style>
main { flex: 1; max-width: 1200px; margin: 40px auto 0 auto; width: 100%; }
h2.page-title { color: #007bff; font-size: 1.8em; margin: 24px 0 16px 0; font-weight: 700; }
.clear-returned-container { display: flex; justify-content: flex-end; margin-bottom: 16px; }
button#clearReturnedBtn { font-weight: 600; color: #fff; background: #dc3545; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background .2s; border: none; font-size: 0.9em; }
button#clearReturnedBtn:hover { background: #c82333; }
table { width: 100%; border-collapse: collapse; background-color: #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; color: #333; }
th { background-color: #007bff; color: white; font-weight: 600; font-size: 1em; }
tr:hover { background-color: #f1f1f1; }
tr.returned { background-color: #e6ffe6; color: #555; }
button[type="submit"] { background-color: #28a745; border: none; color: white; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 0.95em; transition: background-color 0.3s ease; }
button[type="submit"]:hover { background-color: #218838; }
.message { text-align: center; font-weight: bold; margin-top: 20px; color: green; }
</style>
<script>
  function confirmClearReturned() {
      return confirm('<?= $lang['confirm_clear_returned'] ?>'); // 
  }
</script>
</head>
<body>
<main>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?= htmlspecialchars($_SESSION['message']); ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <h2 class="page-title"><?= $lang['admin_return_panel'] ?></h2> <!--  -->

    <div class="clear-returned-container">
        <form method="POST" onsubmit="return confirmClearReturned();" style="margin:0;">
            <button type="submit" name="clear_returned" id="clearReturnedBtn" title="<?= $lang['delete_all_returned_records'] ?>">
                <?= $lang['clear_returned_books'] ?> <!--  -->
            </button>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th><?= $lang['username'] ?></th>
                    <th><?= $lang['book_title'] ?></th>
                    <th><?= $lang['borrowed_at'] ?></th>
                    <th><?= $lang['due_date'] ?></th>
                    <th><?= $lang['returned_date'] ?></th>
                    <th><?= $lang['late_fee'] ?></th>
                    <th><?= $lang['action'] ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="<?= $row['return_date'] ? 'returned' : '' ?>">
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['borrowed_at']) ?></td>
                    <td><?= htmlspecialchars($row['due_date']) ?></td>
                    <td><?= $row['return_date'] ? htmlspecialchars($row['return_date']) : '-' ?></td>
                    <td>
                        <?php
                        if (isset($row['late_fee']) && $row['late_fee'] > 0) {
                            $fee_mnt = $row['late_fee'] * 2000; // 2000 MNT per day
                            echo number_format($fee_mnt) . ' MNT';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if (!$row['return_date']): ?>
                            <form action="return_book.php" method="POST" style="margin:0;">
                                <input type="hidden" name="borrow_id" value="<?= $row['id'] ?>">
                                <button type="submit"><?= $lang['mark_as_returned'] ?></button> <!-- ✅ -->
                            </form>
                        <?php else: ?>
                            <?= $lang['returned'] ?> <!--  -->
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center; font-size:1.2em;"><?= $lang['no_borrowed_books'] ?></p> <!-- ✅ -->
    <?php endif; ?>
</main>
</body>
</html>
