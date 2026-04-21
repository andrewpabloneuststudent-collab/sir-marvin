-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 07:45 AM
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
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `expiry_date`) VALUES
(1, 1, 511, '2026-12-31'),
(2, 2, 121, '2028-08-11'),
(8, 12, 1, '2026-04-30'),
(9, 16, 21, '2026-04-12');

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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `classification_id` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `barcode`, `category_id`, `classification_id`, `unit`, `price`) VALUES
(1, 'Coca Cola', '123456789', 1, 1, 'Bottle', 25.00),
(2, 'C2', '155665666566565', 1, 1, '144', 13.00),
(12, 'Cobras', '123321123', 1, 1, 'Bottle', 16.00),
(16, 'Biogesic 500 mg Tablet', '123455456', 3, 2, 'Tablet', 21.00);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `category_name`) VALUES
(7, 'Baby Care'),
(1, 'Beverage'),
(12, 'Beverages'),
(14, 'Canned Goods'),
(16, 'Dairy Products'),
(10, 'Diagnostics'),
(9, 'First Aid'),
(8, 'Health & Wellness'),
(11, 'Herbal Products'),
(15, 'Instant Food'),
(4, 'Medical Supplies'),
(3, 'Over-the-Counter (OTC)'),
(6, 'Personal Care'),
(2, 'Prescription Medicines'),
(13, 'Snacks'),
(5, 'Vitamins & Supplements');

-- --------------------------------------------------------

--
-- Table structure for table `product_classifications`
--

CREATE TABLE `product_classifications` (
  `id` int(11) NOT NULL,
  `classification_name` varchar(100) NOT NULL,
  `is_discountable` tinyint(1) DEFAULT 1,
  `is_vatable` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_classifications`
--

INSERT INTO `product_classifications` (`id`, `classification_name`, `is_discountable`, `is_vatable`) VALUES
(1, 'Drink', 1, 1),
(2, 'Essential Medicine', 1, 0),
(3, 'Prescription Medicine', 1, 0),
(4, 'Medical Supply (Essential)', 1, 0),
(5, 'Medical Supply (Non-Essential)', 0, 1),
(6, 'Food Item', 0, 1),
(7, 'Supplement', 0, 1),
(8, 'Cosmetic Product', 0, 1),
(9, 'Regular Item', 1, 1),
(10, 'Non-Discountable Item', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(155) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(20) DEFAULT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `position`, `failed_attempts`, `last_attempt`) VALUES
(1, 'andrew', '$2y$10$d.pNrIKVs0trqnlcG.8IH.eRqC.1QdWmdwcDh59DJvf//fQdRxpGa', 'Owner', 0, NULL),
(2, 'ivhan', '$2y$10$gFAk.Z7k13eIpr8hrf0EWOTB92vgXJMxS43wYwdT/tS1yF5LQadj6', 'Owner', 0, NULL),
(3, 'admin', '$2y$10$81n/UDhkSxRSZMceR4Izu.hgvXQLAUys.YQVEMFjwNvIT74fiCRk2', 'Owner', 0, NULL),
(4, 'staff1', '$2y$10$vNfSsV4iU.D/AEzis7swm.6CK.H4U/rP1j9vCZs2Psk.Pcs2YKc7W', 'Staff', 0, NULL);

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
(1, 1, 'Andreww', 'G', 'Pablo', 22, 'Sample Street', 'Poblacion', 'Bulacan', 'Bulacan', 'Philippines', 'andrew@example.com', '09123456789'),
(2, 2, 'Ivhan Grace', 'Aguilar', 'Pablo', 25, 'N/A', '', 'Jaen', 'Nueva Ecija', 'Philippines', 'andrewpablo2005@gmail.com', '09651800675'),
(3, 3, 'admin', 'Gonzales', 'admin', 0, 'N/A', '', 'JAEN (NUEVA ECIJA)', '', 'Philippines', 'andrewpablo2005@gmail.com', ''),
(4, 4, 'Andrew', 'Gonzales', 'Pablo', 0, 'N/A', '', 'JAEN (NUEVA ECIJA)', '', 'Philippines', 'andrewpablo2005@gmail.com', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `barcode_2` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `classification_id` (`classification_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `product_classifications`
--
ALTER TABLE `product_classifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `classification_name` (`classification_name`);

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
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `product_classifications`
--
ALTER TABLE `product_classifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`classification_id`) REFERENCES `product_classifications` (`id`);

--
-- Constraints for table `users_info`
--
ALTER TABLE `users_info`
  ADD CONSTRAINT `fk_user_info` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
