<?php
require_once __DIR__ . '/../conn/database.php';
$conn = Database::getConnection();
$stmt = $conn->query("DESCRIBE product_classifications");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
