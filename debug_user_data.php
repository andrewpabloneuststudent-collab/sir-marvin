<?php
require_once "conn/database.php";

$username = $_SESSION['username'] ?? 'andrew';

// Check users table
$stmt = $GLOBALS['db']->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

echo "=== USERS TABLE ===<br>";
echo "<pre>";
print_r($user);
echo "</pre>";

// Check users_info table
$stmt2 = $GLOBALS['db']->prepare("SELECT * FROM users_info WHERE user_id = ?");
$stmt2->execute([$user['id']]);
$info = $stmt2->fetch();

echo "=== USERS_INFO TABLE ===<br>";
echo "<pre>";
print_r($info);
echo "</pre>";

// Test JOIN
$stmt3 = $GLOBALS['db']->prepare("SELECT u.*, ui.firstname, ui.lastname FROM users u LEFT JOIN users_info ui ON u.id = ui.user_id WHERE u.username = ?");
$stmt3->execute([$username]);
$joined = $stmt3->fetch();

echo "=== JOINED RESULT ===<br>";
echo "<pre>";
print_r($joined);
echo "</pre>";
?>
