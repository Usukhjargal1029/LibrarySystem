<?php
// signup.php

$success = null;
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli('localhost', 'root', '', 'library_db');

    if ($conn->connect_error) {
        $success = false;
        $message = "Connection failed: " . $conn->connect_error;
    } else {
        // Sanitize and validate inputs
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$username || !$email || !$password) {
            $success = false;
            $message = "Please fill all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $success = false;
            $message = "Invalid email format.";
        } else {
            // Check if email already exists
            $sql = "SELECT * FROM users WHERE email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $success = false;
                $message = "Email is already registered.";
            } else {
                // Hash password and insert into database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    $success = true;
                    $message = "Registration successful. <a href='login.php'>Log in here</a>.";
                } else {
                    $success = false;
                    $message = "Error registering user. Please try again.";
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #e5decb;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .signup-card {
        background: #fff;
        padding: 35px;
        width: 100%;
        max-width: 400px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }
    .signup-card h2 {
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
    .success {
        background: #e6ffed;
        color: #006b1b;
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
    <div class="signup-card">
        <h2><i class="fas fa-user-plus"></i> Sign Up</h2>

        <?php if ($message): ?>
            <div class="<?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="signup.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter your username" required />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required />
            </div>
            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>