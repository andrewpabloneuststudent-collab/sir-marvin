<?php
require_once __DIR__ . '/../conn/database.php';

try {
    $stmt = $db->prepare("UPDATE users SET void_password = '1234567' WHERE position IN ('Owner', 'Admin')");
    $stmt->execute();
    echo "Successfully updated all Admin and Owner void PINs to 1234567.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
