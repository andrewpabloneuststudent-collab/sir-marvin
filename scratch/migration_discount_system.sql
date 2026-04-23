-- Run this SQL in phpMyAdmin to set up the new discount system
-- Step 1: Add discount flags to product_categories
ALTER TABLE `product_categories`
    ADD COLUMN IF NOT EXISTS `has_vat` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `senior_discount` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `pwd_discount` TINYINT(1) NOT NULL DEFAULT 0;

-- Step 2: Create override_log table for audit trail
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
