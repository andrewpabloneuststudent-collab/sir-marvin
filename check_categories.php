<?php
session_start();
require_once __DIR__ . "/conn/database.php";

// Get all categories
$stmt = $db->prepare("SELECT * FROM product_categories ORDER BY id ASC");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>Current Categories</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Has VAT</th><th>Senior Discount</th><th>PWD Discount</th></tr>";

foreach ($categories as $cat) {
    echo "<tr>";
    echo "<td>{$cat['id']}</td>";
    echo "<td>{$cat['category_name']}</td>";
    echo "<td>" . ($cat['has_vat'] ? 'YES' : 'NO') . "</td>";
    echo "<td>" . ($cat['senior_discount'] ? 'YES' : 'NO') . "</td>";
    echo "<td>" . ($cat['pwd_discount'] ? 'YES' : 'NO') . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><br>";

// Get product count per category
echo "<h1>Products per Category</h1>";
$stmt = $db->prepare("
    SELECT pc.id, pc.category_name, COUNT(p.id) as product_count
    FROM product_categories pc
    LEFT JOIN products p ON pc.id = p.category_id
    GROUP BY pc.id
    ORDER BY pc.category_name
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Category</th><th>Product Count</th></tr>";
foreach ($results as $row) {
    echo "<tr><td>{$row['category_name']}</td><td>{$row['product_count']}</td></tr>";
}
echo "</table>";
?>
