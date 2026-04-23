<?php
require_once __DIR__ . '/../conn/database.php';

try {
    $conn = Database::getConnection();

    // Reset all to discountable and vatable first as a baseline
    $conn->exec("UPDATE product_classifications SET is_discountable = 1, is_vatable = 1");

    // Apply specific rules based on the user's image and request
    
    // Medical items (Discountable, VAT Exempt)
    $stmt = $conn->prepare("UPDATE product_classifications SET is_discountable = 1, is_vatable = 0 WHERE classification_name IN ('Essential Medicine', 'Prescription Medicine', 'Medical Supply (Essential)')");
    $stmt->execute();

    // Non-discountable, but vatable (Food, Drinks, Supplements, Cosmetics, Non-Essential)
    $stmt = $conn->prepare("UPDATE product_classifications SET is_discountable = 0, is_vatable = 1 WHERE classification_name IN ('Drink', 'Medical Supply (Non-Essential)', 'Food Item', 'Supplement', 'Cosmetic Product', 'Non-Discountable Item')");
    $stmt->execute();

    // Regular item (Discountable, Vatable - Remove VAT -> Apply Discount)
    $stmt = $conn->prepare("UPDATE product_classifications SET is_discountable = 1, is_vatable = 1 WHERE classification_name IN ('Regular Item')");
    $stmt->execute();

    echo "Product classifications updated successfully.\n";

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
