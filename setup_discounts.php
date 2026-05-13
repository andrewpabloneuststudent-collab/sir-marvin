<?php
// Insert default discounts into database

require_once __DIR__ . "/conn/database.php";

try {
    $db->beginTransaction();
    
    $discounts = [
        ['Regular', 0, 0],
        ['Senior Citizen (20%)', 20, 1],
        ['PWD (20%)', 20, 1],
        ['Employee', 10, 1],
        ['NCARD (15%)', 15, 1]
    ];
    
    $stmt = $db->prepare("INSERT INTO discounts (discount_name, discount_rate, is_vat_exempt) VALUES (?, ?, ?)");
    
    foreach ($discounts as $discount) {
        $stmt->execute($discount);
        echo "Inserted: " . $discount[0] . "\n";
    }
    
    $db->commit();
    echo "\nAll discounts inserted successfully!\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
