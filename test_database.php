<?php
// Test database connection and product loading

require_once __DIR__ . "/conn/database.php";
require_once __DIR__ . "/function/workingpos.php";

try {
    $product = new Product($db);
    
    echo "=== Database Diagnosis ===\n\n";
    
    // Test 1: Check products
    $products = $product->getProducts();
    echo "Products found: " . count($products) . "\n";
    if (count($products) > 0) {
        echo "First product: " . $products[0]['product_name'] . "\n";
    } else {
        echo "ERROR: No products in database!\n";
    }
    
    echo "\n";
    
    // Test 2: Check categories
    $categories = $product->getCategories();
    echo "Categories found: " . count($categories) . "\n";
    if (count($categories) > 0) {
        echo "First category: " . $categories[0]['category_name'] . "\n";
    } else {
        echo "WARNING: No categories in database\n";
    }
    
    echo "\n";
    
    // Test 3: Check discounts
    $discounts = $product->getDiscounts();
    echo "Discounts found: " . count($discounts) . "\n";
    if (count($discounts) > 0) {
        foreach ($discounts as $d) {
            echo "  - " . $d['discount_name'] . " (" . $d['discount_rate'] . "%)\n";
        }
    } else {
        echo "ERROR: No discounts in database!\n";
    }
    
    echo "\n=== Database Diagnosis Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
