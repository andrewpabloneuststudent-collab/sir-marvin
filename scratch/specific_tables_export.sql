-- Specific Tables Export for Neil (Cleaned)

-- 1. Table structure for table `product_categories`
DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `has_vat` tinyint(1) NOT NULL DEFAULT 0,
  `senior_discount` tinyint(1) NOT NULL DEFAULT 0,
  `pwd_discount` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Dumping data for table `product_categories`
INSERT INTO `product_categories` (`id`, `category_name`, `has_vat`, `senior_discount`, `pwd_discount`) VALUES
(1, 'Beverage', 1, 0, 0),
(2, 'Prescription Medicines', 0, 1, 1),
(3, 'Over-the-Counter (OTC)', 0, 1, 1),
(4, 'Medical Supplies', 1, 1, 1),
(5, 'Vitamins & Supplements', 1, 1, 1),
(6, 'Personal Care', 1, 0, 0),
(7, 'Baby Care', 1, 0, 0),
(8, 'Health & Wellness', 1, 0, 0),
(9, 'First Aid', 1, 0, 0),
(10, 'Diagnostics', 1, 0, 0),
(11, 'Herbal Products', 1, 0, 0),
(12, 'Beverages', 1, 0, 0),
(13, 'Snacks', 1, 0, 0),
(14, 'Canned Goods', 1, 0, 0),
(15, 'Instant Food', 1, 0, 0),
(16, 'Dairy Products', 1, 0, 0);

-- 3. Table structure for table `override_log`
DROP TABLE IF EXISTS `override_log`;
CREATE TABLE `override_log` (
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
