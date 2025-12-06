<?php
session_start();
session_unset();
session_destroy();

// Redirect to login or homepage after logout
header("Location: login.php");
exit;
?>