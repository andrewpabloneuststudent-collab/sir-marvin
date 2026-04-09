<?php 
session_start();

// 🔐 CHECK IF LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: /MMBPOS/login.php");
    exit;
}

// 🔐 OPTIONAL: CHECK ROLE (case-insensitive)
if (strtolower($_SESSION['position']) !== 'admin') {
    echo "Access denied";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    Admin Dashboard
    <a href="../login_logout_page/logout.php" class="btn btn-danger">Logout</a>
</body>
</html>