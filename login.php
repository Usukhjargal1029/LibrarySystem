<?php
// login.php
session_start();
$error = "";

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'library_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please fill all fields.";
    } else {
        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "Invalid email or password.";
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Save user role
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #e5decb; /* Same beige as homepage */
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .login-card {
        background: #fff;
        padding: 35px;
        width: 100%;
        max-width: 400px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }
    .login-card h2 {
        color: #0766c3;
        margin-bottom: 20px;
    }
    .form-group {
        text-align: left;
        margin-bottom: 15px;
    }
    label {
        font-weight: bold;
        color: #444;
        font-size: 14px;
        display: block;
        margin-bottom: 5px;
    }
    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }
    input:focus {
        border-color: #257ae7;
        outline: none;
        box-shadow: 0 0 5px rgba(37,122,231,0.3);
    }
    .error {
        background: #ffe6e6;
        color: #d8000c;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        font-size: 14px;
        text-align: left;
    }
    button {
        background-color: #257ae7;
        color: #fff;
        border: none;
        padding: 10px;
        font-size: 16px;
        font-weight: 600;
        width: 100%;
        border-radius: 6px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        transition: background 0.2s;
    }
    button:hover {
        background-color: #0967cf;
    }
    p {
        margin-top: 15px;
        font-size: 14px;
    }
    p a {
        color: #257ae7;
        text-decoration: none;
        font-weight: bold;
    }
    p a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
    <div class="login-card">
        <h2><i class="fas fa-sign-in-alt"></i> Log In</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit">Log In</button>
        </form>

        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
</body>
</html>