<?php
$pdo = new PDO('mysql:host=localhost;dbname=mmbpos', 'root', '');
$stmt = $pdo->query('SELECT * FROM product_classifications');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['classification_name'] . ": D=" . $row['is_discountable'] . ", V=" . $row['is_vatable'] . "\n";
}
