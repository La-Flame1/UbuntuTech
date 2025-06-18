-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 11:46 AM
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
-- Database: `ubuntu_tech`
--

-- --------------------------------------------------------

--
-- Table structure for table `help_desk`
--

DROP TABLE IF EXISTS `help_desk`;
CREATE TABLE IF NOT EXISTS `help_desk` (
  `query_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `issue_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolution_notes` text DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `help_desk`
--

INSERT INTO `help_desk` (`query_id`, `user_email`, `issue_type`, `description`, `created_at`, `resolution_notes`, `is_resolved`, `resolved_at`) VALUES
(1, 'donell.oageng@gmail.com', 'Billing', 'Froze during processing.', '2025-06-18 09:42:47', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `order_items` text NOT NULL,
  `description` text DEFAULT NULL,
  `total_value` decimal(10,2) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_email`, `order_items`, `description`, `total_value`, `delivery_date`, `payment_method`, `coupon_code`, `created_at`) VALUES
(1, 'donell.oageng@gmail.com', '[{\"product_id\":16,\"name\":\"Apple AirPods Pro (2nd Generation) with MagSafe Charging Case\",\"price\":\"4999.00\",\"quantity\":20,\"subtotal\":99980,\"description\":\"Experience unparalleled audio with Active Noise Cancellation up to 2x more effective, Adaptive Transparency, and Personalized Spatial Audio. Comes with a MagSafe Charging Case with built-in speaker for Find My and lanyard loop.\"}]', '', 1999600.00, '2025-06-25', '0', '', '2025-06-18 08:40:47'),
(2, 'donell.oageng@gmail.com', '[{\"product_id\":16,\"name\":\"Apple AirPods Pro (2nd Generation) with MagSafe Charging Case\",\"price\":\"4999.00\",\"quantity\":1,\"subtotal\":4999,\"description\":\"Experience unparalleled audio with Active Noise Cancellation up to 2x more effective, Adaptive Transparency, and Personalized Spatial Audio. Comes with a MagSafe Charging Case with built-in speaker for Find My and lanyard loop.\"}]', '', 4999.00, '2025-06-25', '0', '', '2025-06-18 08:44:35');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `catalog` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `layout_option` varchar(50) DEFAULT 'default',
  `serial_number` varchar(50) NOT NULL,
  `sales_count` int(11) DEFAULT 0,
  `user_email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`product_id`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `catalog`, `image_path`, `name`, `description`, `price`, `stock`, `layout_option`, `serial_number`, `sales_count`, `user_email`, `status`) VALUES
(3, NULL, 'images/huawei-nova-13.jpg', 'Huawei Nova 13', 'AI-powered photography, immersive display, fast performance, long-lasting battery.', 2499.90, 4, 'Standard', 'SN123456', 0, NULL, 'active'),
(5, NULL, 'images/macbook-pro-m3.jpg', 'Apple MacBook Pro M3', 'M3 chip, stunning Retina display, all-day battery life.', 4999.90, 3, 'Standard', 'SN123458', 0, NULL, 'active'),
(7, NULL, 'images/dell-xps-15.jpg', 'Dell XPS 15', 'Powerful performance, 4K OLED display, sleek design.', 3999.90, 5, 'Premium', 'SN123460', 0, NULL, 'active'),
(8, 'Camera', 'uploads/WhatsApp Image 2025-06-06 at 18.04.31_85a187ba.jpg', 'Nixon ProCam X2000', 'A professional-grade digital camera designed for crisp, high-resolution imaging and versatile shooting modes. Features a durable build, intuitive controls, and excellent low-light performance, making it ideal for both amateur enthusiasts and seasoned photographers.', 18499.90, 5, 'default', ' NC-X2000-01 ', 0, NULL, 'active'),
(9, 'Wearables', 'uploads/WhatsApp Image 2025-06-06 at 18.04.31_dd775daa.jpg', 'Huawei Watch GT 4', 'A stylish and feature-rich smartwatch offering advanced health monitoring (heart rate, SpO2, sleep tracking), multi-sport modes, and long-lasting battery life. Seamlessly integrates with your smartphone for notifications and calls.', 4500.00, 12, 'default', 'HWSW-GT4-BLACK-002', 0, NULL, 'active'),
(15, 'Smartphones', 'uploads/WhatsApp Image 2025-06-06 at 18.04.31_0abbc866.jpg', 'Apple iPhone 13 (128GB, Blue)', 'Featuring the powerful A15 Bionic chip, a super Retina XDR display, and an advanced dual-camera system for stunning photos and videos. Offers durable Ceramic Shield front cover and 5G connectivity for a fast and fluid experience.', 16999.00, 7, 'default', 'IP13-128-BLUE-A2633', 0, NULL, 'active'),
(16, 'Audio', 'uploads/WhatsApp Image 2025-06-06 at 18.04.31_8de7bc74.jpg', 'Apple AirPods Pro (2nd Generation) with MagSafe Charging Case', 'Experience unparalleled audio with Active Noise Cancellation up to 2x more effective, Adaptive Transparency, and Personalized Spatial Audio. Comes with a MagSafe Charging Case with built-in speaker for Find My and lanyard loop.', 4999.00, 15, 'default', 'APP2-MAGSAFE-CASE-001', 0, NULL, 'active'),
(17, 'Laptops', 'uploads/WhatsApp Image 2025-06-06 at 18.04.32_32e81992.jpg', 'ASUS Laptop (Intel Core i5, Z2342 Model)', 'A reliable and efficient laptop powered by an Intel Core i5 processor (Z2342 series), suitable for everyday productivity, casual gaming, and multimedia consumption. Features a crisp display and a comfortable keyboard for extended use.', 10500.00, 7, 'default', 'ASUS-LAP-I5-Z2342-03 (', 0, NULL, 'active'),
(18, 'Smarthphones', 'uploads/WhatsApp Image 2025-06-06 at 18.04.32_a1560fbc.jpg', 'Samsung Galaxy S22 (128GB, Phantom Black)', 'A premium smartphone with a dynamic AMOLED 2X display, advanced pro-grade camera system for stunning photos and videos in any light, and a powerful processor. Features a sleek design and robust performance for a flagship experience.', 15499.00, 10, 'default', ' SGS22-128-PHANTOM-BLK-004', 0, NULL, 'active'),
(20, 'Gaming Consoles', 'C:\\Users\\Donell\\Downloads\\WhatsApp Image 2025-06-06 at 18.36.45_6ae62ae7.jpg', 'Sony PlayStation 5 (Disc Edition)', 'Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, and 3D Audio, and an all-new generation of incredible PlayStation games. Includes wireless controller.', 13999.00, 3, 'default', 'PS5-DISC-MODEL-005', 0, 'donell.oageng@gmail.com', 'active'),
(21, 'Gaming Consoles', 'C:\\Users\\Donell\\Downloads\\WhatsApp Image 2025-06-06 at 18.36.45_6ae62ae7.jpg', 'Sony PlayStation 5 (Disc Edition)', 'Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, and 3D Audio, and an all-new generation of incredible PlayStation games. Includes wireless controller.', 13999.00, 3, 'default', 'PS5-DISC-MODEL-005', 0, 'donell.oageng@gmail.com', 'active'),
(22, 'Gaming Consoles', 'C:\\Users\\Donell\\Downloads\\WhatsApp Image 2025-06-06 at 18.36.45_6ae62ae7.jpg', 'Sony PlayStation 5 (Disc Edition)', 'Experience lightning-fast loading with an ultra-high speed SSD, deeper immersion with support for haptic feedback, adaptive triggers, and 3D Audio, and an all-new generation of incredible PlayStation games. Includes wireless controller.', 13999.00, 3, 'default', 'PS5-DISC-MODEL-005', 0, 'donell.oageng@gmail.com', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` char(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','employee','admin') NOT NULL DEFAULT 'user',
  `terms_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `phone_number`, `password`, `role`, `terms_accepted`, `created_at`) VALUES
(1, 'admin', 'admin@ubuntutech.com', '0812551122', '$2y$10$SGCJScoYKcf86Oa0HmEXs.8sN2aPjwzm5Ukz5sCPWRtTRguSXqXmO', 'admin', 1, '2025-06-05 18:55:48'),
(3, 'La_Flame', 'donell.oageng@gmail.com', '0812331122', '$2y$10$wOCZQmozDuI6Ncco38yiSOpkeO4xnH6YoYzm419vddhiaczbLDbDW', 'user', 0, '2025-06-06 08:19:57'),
(4, 'king', 'manager@ubuntutech.com', '0815651122', '$2y$10$GDE7X0sn0ZCr9xMgiyEFWedEUsfzFixqYVNTCSi43U75tzVnlV/Uq', 'employee', 0, '2025-06-06 09:47:41'),
(6, 'asdas', 'dfa@hdhua', '', '$2y$10$qDqa54vecZrQZTU83erV/.MoJywljC4OCnoqOcJmecg/ceAb9q3xO', 'user', 0, '2025-06-06 21:33:06'),
(7, 'eduvos', 'aduvos1234@gmail.com', '', '$2y$10$JXgNRNLIvbbEZVLtjq2ZTOT3KYyuMFgMcULRl3grjlIFDCnTRVZSm', 'user', 0, '2025-06-18 09:14:12'),
(8, 'flame', 'flame@mweb.com', '0812551120', '$2y$10$nHPz3pyCyq0i6.l5jGMWCebMKqBXsnl4oYXAsM7eQWz8K7TZT2NSW', 'user', 0, '2025-06-18 09:15:43');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `users` (`email`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
