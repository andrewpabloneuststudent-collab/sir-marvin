<?php
$content = file_get_contents(__DIR__ . "/../main_db_backup.sql");
// Convert from UTF-16 to UTF-8 if necessary
if (substr($content, 0, 2) === "\xFF\xFE") {
    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
}

$output = "-- Specific Tables Export for Neil (Manual Extraction)\n\n";

// Extract product_categories
if (preg_match('/-- Table structure for table `product_categories`(.*?)-- --------------------------------------------------------/s', $content, $matches)) {
    $block = $matches[1];
    // Add the missing columns to the CREATE TABLE statement if they aren't there
    if (!strpos($block, 'has_vat')) {
        $block = str_replace("PRIMARY KEY (`id`)", "  `has_vat` tinyint(1) NOT NULL DEFAULT 0,\n  `senior_discount` tinyint(1) NOT NULL DEFAULT 0,\n  `pwd_discount` tinyint(1) NOT NULL DEFAULT 0,\n  PRIMARY KEY (`id`)", $block);
    }
    $output .= "-- Table structure for table `product_categories`" . $block . "\n\n";
}

// Extract product_categories data
if (preg_match('/-- Dumping data for table `product_categories`(.*?)-- --------------------------------------------------------/s', $content, $matches)) {
    $output .= "-- Dumping data for table `product_categories` " . $matches[1] . "\n\n";
}

// Add override_log structure (since it's new)
$output .= "-- Table structure for table `override_log`
CREATE TABLE IF NOT EXISTS `override_log` (
    `id`               INT(11) AUTO_INCREMENT PRIMARY KEY,
    `transaction_id`   INT(11) NULL,
    `product_id`       INT(11) NOT NULL,
    `product_name`     VARCHAR(255) NOT NULL,
    `cashier_id`       INT(11) NOT NULL,
    `cashier_name`     VARCHAR(255) NOT NULL,
    `approver_id`      INT(11) NOT NULL,
    `approver_name`    VARCHAR(255) NOT NULL,
    `original_price`   DECIMAL(10,2) NOT NULL,
    `discounted_price` DECIMAL(10,2) NOT NULL,
    `discount_amount`  DECIMAL(10,2) NOT NULL,
    `discount_percent` DECIMAL(5,2) NOT NULL,
    `reason`           TEXT NOT NULL,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n";

file_put_contents(__DIR__ . "/specific_tables_export.sql", $output);
echo "Extracted specific tables to scratch/specific_tables_export.sql\n";
