-- scratch/product_classifications_export.sql
SET FOREIGN_KEY_CHECKS = 0;

-- Structure for table `product_classifications`
CREATE TABLE IF NOT EXISTS `product_classifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classification_name` varchar(255) NOT NULL,
  `is_discountable` tinyint(1) NOT NULL DEFAULT 1,
  `is_vatable` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classification_name` (`classification_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data for table `product_classifications`
TRUNCATE TABLE `product_classifications`;
INSERT INTO `product_classifications` (`classification_name`, `is_discountable`, `is_vatable`) VALUES 
('Essential Medicine', 1, 0),
('Prescription Medicine', 1, 0),
('Medical Supply (Essential)', 1, 0),
('Drink', 0, 1),
('Medical Supply (Non-Essential)', 0, 1),
('Food Item', 0, 1),
('Supplement', 0, 1),
('Cosmetic Product', 0, 1),
('Non-Discountable Item', 0, 1),
('Regular Item', 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
