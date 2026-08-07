-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 04, 2026 at 10:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mmbpos`
--

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int(11) NOT NULL,
  `discount_name` varchar(50) DEFAULT NULL,
  `discount_rate` decimal(5,2) DEFAULT NULL,
  `is_vat_exempt` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `discount_name`, `discount_rate`, `is_vat_exempt`) VALUES
(1, 'Regular', 0.00, 0),
(2, 'Senior Citizen', 20.00, 1),
(3, 'PWD', 20.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `batch_number`, `quantity`, `expiry_date`) VALUES
(1, 1, 'MED-00001', 98, '2028-11-15'),
(2, 2, 'mikk0001', 98, '2032-09-03');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_disposals`
--

CREATE TABLE `inventory_disposals` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `reason` varchar(100) NOT NULL DEFAULT 'Expired',
  `disposed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `override_log`
--

CREATE TABLE `override_log` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `cashier_name` varchar(255) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approver_name` varchar(255) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `discounted_price` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL,
  `reason` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_approved_users`
--

CREATE TABLE `pre_approved_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `void_password` varchar(255) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pre_approved_users`
--

INSERT INTO `pre_approved_users` (`id`, `username`, `password`, `void_password`, `position`) VALUES
(1, 'sampleAccount', '$2y$10$G.IAWGX.MPWQ8E1bIvuykO7Ph//70zWAqxZdMdDVMFiZDu7qFtBLm', NULL, 'Staff');

-- --------------------------------------------------------

--
-- Table structure for table `pre_approved_users_info`
--

CREATE TABLE `pre_approved_users_info` (
  `id` int(11) NOT NULL,
  `pre_user_id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `street` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `email` varchar(155) NOT NULL,
  `contactnumber` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pre_approved_users_info`
--

INSERT INTO `pre_approved_users_info` (`id`, `pre_user_id`, `firstname`, `middlename`, `lastname`, `age`, `street`, `barangay`, `city`, `province`, `country`, `email`, `contactnumber`, `created_at`) VALUES
(1, 1, 'Sample', 'Gonzales', 'Pablo', 19, 'Purok', 'Niyugan', 'JAEN (NUEVA ECIJA)', 'Nueva Ecija', 'Philippines', 'andrewpablo2005@gmail.com', '09651800675', '2026-05-13 04:17:17');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `branded_name` varchar(255) NOT NULL,
  `generic_name` varchar(255) NOT NULL,
  `strength` int(11) NOT NULL,
  `measurement_id` int(11) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `classification_id` int(11) DEFAULT NULL,
  `pcs` int(50) DEFAULT NULL,
  `net_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `imageproduct` varchar(500) NOT NULL,
  `is_basic_necessities` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `branded_name`, `generic_name`, `strength`, `measurement_id`, `barcode`, `category_id`, `classification_id`, `pcs`, `net_price`, `total_price`, `imageproduct`, `is_basic_necessities`) VALUES
(1, 'Amoxil', 'Amoxicillin', 500, 2, '400352649802', 17, NULL, 0, 5.00, 5.25, '6a71a46e730be-1785832558Amoxil.png', 0),
(2, '', 'Nestle Fresh Milk', 1, 7, '195285135741', 31, NULL, 0, 87.00, 91.35, '6a71a5408f6f1-1785832768SM2099217-14-1.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `has_vat` tinyint(1) NOT NULL DEFAULT 0,
  `senior_discount` tinyint(1) NOT NULL DEFAULT 0,
  `pwd_discount` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`, `has_vat`, `senior_discount`, `pwd_discount`) VALUES
(17, 'Prescription Medicines', 0, 1, 1),
(18, 'Over-the-Counter (OTC)', 0, 1, 1),
(19, 'Medical Supplies', 0, 1, 1),
(20, 'Vitamins & Supplements', 0, 1, 1),
(21, 'First Aid', 0, 1, 1),
(22, 'Diagnostics', 0, 1, 1),
(23, 'Herbal Products', 0, 1, 1),
(24, 'Health & Wellness', 0, 1, 1),
(25, 'Personal Care', 1, 0, 0),
(26, 'Baby Care', 1, 0, 0),
(27, 'Beverage/Beverages', 1, 0, 0),
(28, 'Snacks', 1, 0, 0),
(29, 'Canned Goods', 1, 0, 0),
(30, 'Instant Food', 1, 0, 0),
(31, 'Dairy Products', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pwd_customers`
--

CREATE TABLE `pwd_customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `verified_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pwd_customers`
--

INSERT INTO `pwd_customers` (`id`, `customer_name`, `id_number`, `cashier_id`, `verified_at`) VALUES
(1, 'Andrew Pablo', '1234567', 1, '2026-07-25 12:23:57');

-- --------------------------------------------------------

--
-- Table structure for table `return_items`
--

CREATE TABLE `return_items` (
  `id` int(11) NOT NULL,
  `return_transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `item_type` enum('returned','replacement') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_transactions`
--

CREATE TABLE `return_transactions` (
  `id` int(11) NOT NULL,
  `original_transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `replacement_product_id` int(11) DEFAULT NULL,
  `replacement_quantity` int(11) NOT NULL DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `refund_method` varchar(50) DEFAULT 'cash',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `senior_customers`
--

CREATE TABLE `senior_customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `verified_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `senior_customers`
--

INSERT INTO `senior_customers` (`id`, `customer_name`, `id_number`, `cashier_id`, `verified_at`) VALUES
(1, 'Andrew Pablo', '1234567', 1, '2026-07-25 08:47:10'),
(2, 'akfhkhf', '54454', 1, '2026-07-25 13:59:46');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `discount_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_id` varchar(100) DEFAULT NULL,
  `customer_type` varchar(20) DEFAULT NULL COMMENT 'Type of customer: regular, pwd, senior',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_total` decimal(10,2) DEFAULT 0.00,
  `total_vat_exemption` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `discount_id`, `customer_name`, `customer_id`, `customer_type`, `total_amount`, `created_at`, `discount_total`, `total_vat_exemption`) VALUES
(1, 1, 1, 'Walk-in', NULL, '', 5.25, '2026-08-04 08:36:12', 0.00, 0.00),
(2, 1, 1, 'Walk-in', NULL, '', 91.35, '2026-08-04 08:39:47', 0.00, 0.00),
(3, 1, 2, 'Andrew Pablo', '1', 'senior', 96.60, '2026-08-04 08:40:32', 1.05, 0.56);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`id`, `transaction_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 1, 1, 5.25, 5.25),
(2, 2, 2, 1, 91.35, 91.35),
(3, 3, 1, 1, 5.25, 5.25),
(4, 3, 2, 1, 91.35, 91.35);

-- --------------------------------------------------------

--
-- Table structure for table `unit_measurement`
--

CREATE TABLE `unit_measurement` (
  `unit_id` int(11) NOT NULL,
  `different_measurement` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit_measurement`
--

INSERT INTO `unit_measurement` (`unit_id`, `different_measurement`) VALUES
(1, 'mcg'),
(2, 'mg'),
(3, 'g'),
(4, 'kg'),
(5, 'µL'),
(6, 'mL'),
(7, 'L'),
(8, 'mm'),
(9, 'cm'),
(10, 'm'),
(11, '%'),
(12, 'IU'),
(13, 'mEq'),
(14, 'mmol'),
(15, 'Units');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(155) NOT NULL,
  `password` varchar(255) NOT NULL,
  `void_password` varchar(255) DEFAULT NULL,
  `position` varchar(20) DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `void_password`, `position`, `failed_attempts`, `last_attempt`) VALUES
(1, 'andrew_owner', '$2y$10$U9nvr0YPYlqswVjqumcw1OTvtVJYVwdl5MFcCfvumt.nUzhqJTe9C', '1234567', 'Owner', 0, '2026-07-29 03:16:59'),
(2, 'andrew_admin', '$2y$10$66NN40jrGg8tJ3wtlQY2jeUzoCbU98yphzEaZ6kAr6xgAQbV.tMG2', '1234567', 'Admin', 0, '2026-07-29 03:58:12'),
(3, 'andrew_staff', '$2y$10$WESQ6f2mApseNhMhKMmW8e6gg.tp9AU8CsY/mQrU4g6GHEWmFCWGG', '1234567', 'Staff', 0, '2026-07-26 14:50:35'),
(4, 'staff1', '$2y$10$U60z2JyVRKxJ.x36cqpJkuzZDPRFtOF5aZqUO7QCyBBN.P614oIoy', NULL, 'Staff', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users_info`
--

CREATE TABLE `users_info` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `street` varchar(155) NOT NULL,
  `barangay` varchar(155) NOT NULL,
  `city` varchar(155) NOT NULL,
  `province` varchar(155) NOT NULL,
  `country` varchar(155) NOT NULL,
  `email` varchar(155) NOT NULL,
  `contactnumber` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_info`
--

INSERT INTO `users_info` (`id`, `user_id`, `firstname`, `middlename`, `lastname`, `age`, `street`, `barangay`, `city`, `province`, `country`, `email`, `contactnumber`) VALUES
(1, 1, 'Andrew', 'Gonzales', 'Pablo', 0, '', '', '', '', '', 'andrewpablo2005@gmail.com', '09651800675'),
(2, 2, 'Jhon Bryan', 'Gonzales', 'Palero', 22, 'N/A', 'Malapit', 'San Isidro', 'Nueva Ecija', 'Philippines', 'palero@gmail.com', '09651800675'),
(3, 3, 'Neil Paolo', 'Gonzales', 'Cabrera', 21, 'N/A', 'Marelu', 'Gapan', 'Nueva Ecija', 'Philippines', 'cabrera@gmail.com', '09651800675'),
(4, 4, 'Ivhan Grace', 'De Belen', 'Aguilar', 20, 'N/A', 'Niyugan', 'Jaen', 'Nueva Ecija', 'Philippines', 'andrewpablo2005@gmail.com', '09651800675');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `inventory_disposals`
--
ALTER TABLE `inventory_disposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_disposals_product` (`product_id`),
  ADD KEY `idx_inventory_disposals_expiry` (`expiry_date`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `override_log`
--
ALTER TABLE `override_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pre_approved_users`
--
ALTER TABLE `pre_approved_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pre_approved_users_info`
--
ALTER TABLE `pre_approved_users_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pre_users_info` (`pre_user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `barcode_2` (`barcode`),
  ADD UNIQUE KEY `barcode_3` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `classification_id` (`classification_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `pwd_customers`
--
ALTER TABLE `pwd_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pwd_id` (`id_number`);

--
-- Indexes for table `return_items`
--
ALTER TABLE `return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_return_items_return_transaction` (`return_transaction_id`),
  ADD KEY `idx_return_items_product` (`product_id`);

--
-- Indexes for table `return_transactions`
--
ALTER TABLE `return_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_return_original_transaction` (`original_transaction_id`),
  ADD KEY `idx_return_user` (`user_id`);

--
-- Indexes for table `senior_customers`
--
ALTER TABLE `senior_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_senior_id` (`id_number`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discount_id` (`discount_id`),
  ADD KEY `idx_customer_type` (`customer_type`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `unit_measurement`
--
ALTER TABLE `unit_measurement`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_info`
--
ALTER TABLE `users_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_info` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_disposals`
--
ALTER TABLE `inventory_disposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `override_log`
--
ALTER TABLE `override_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pre_approved_users`
--
ALTER TABLE `pre_approved_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pre_approved_users_info`
--
ALTER TABLE `pre_approved_users_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `pwd_customers`
--
ALTER TABLE `pwd_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `return_items`
--
ALTER TABLE `return_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_transactions`
--
ALTER TABLE `return_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `senior_customers`
--
ALTER TABLE `senior_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `unit_measurement`
--
ALTER TABLE `unit_measurement`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users_info`
--
ALTER TABLE `users_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pre_approved_users_info`
--
ALTER TABLE `pre_approved_users_info`
  ADD CONSTRAINT `fk_pre_users_info` FOREIGN KEY (`pre_user_id`) REFERENCES `pre_approved_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
