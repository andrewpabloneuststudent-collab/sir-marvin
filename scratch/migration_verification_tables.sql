-- Migration: Create Senior and PWD Customer Verification Tables
-- Run this in phpMyAdmin on your mmbpos database

CREATE TABLE IF NOT EXISTS `senior_customers` (
    `id`            INT(11) NOT NULL AUTO_INCREMENT,
    `customer_name` VARCHAR(255) NOT NULL,
    `id_number`     VARCHAR(100) NOT NULL,
    `cashier_id`    INT(11) NOT NULL,
    `verified_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_senior_id` (`id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pwd_customers` (
    `id`            INT(11) NOT NULL AUTO_INCREMENT,
    `customer_name` VARCHAR(255) NOT NULL,
    `id_number`     VARCHAR(100) NOT NULL,
    `cashier_id`    INT(11) NOT NULL,
    `verified_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_pwd_id` (`id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
