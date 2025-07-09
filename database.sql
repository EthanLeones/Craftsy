-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 09, 2025 at 03:30 PM
-- Server version: 10.11.11-MariaDB-0+deb12u1
-- PHP Version: 8.2.28

-- Drop database if exists to ensure clean installation
DROP DATABASE IF EXISTS s22101184_craftsy;

-- Create database with proper character set
CREATE DATABASE s22101184_craftsy
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s22101184_craftsy`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_messages`
--

CREATE TABLE `inquiry_messages` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inquiry_messages`
--

INSERT INTO `inquiry_messages` (`id`, `thread_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 1, 2, 'Hello may i get an update of my order please', '2025-05-26 17:52:04'),
(2, 1, 1, 'ok order status updated sir', '2025-05-26 17:53:23'),
(3, 1, 1, 'hello', '2025-05-27 00:19:43'),
(4, 2, 3, 'Hello sir good job', '2025-05-27 01:10:12'),
(5, 2, 1, 'asdf', '2025-05-27 08:09:12'),
(6, 2, 1, 'asdf', '2025-05-27 08:09:26');

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_threads`
--

CREATE TABLE `inquiry_threads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inquiry_threads`
--

INSERT INTO `inquiry_threads` (`id`, `user_id`, `subject`, `order_id`, `created_at`, `updated_at`) VALUES
(1, 2, 'General Inquiry', NULL, '2025-05-26 17:51:52', '2025-05-27 00:19:43'),
(2, 3, 'General Inquiry', NULL, '2025-05-27 01:10:06', '2025-05-27 08:09:26');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','completed','failed') DEFAULT 'pending',
  `shipping_address_line1` varchar(255) DEFAULT NULL,
  `shipping_address_line2` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state_province` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `shipping_contact_number` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  `proof_of_payment_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address_line1`, `shipping_address_line2`, `shipping_city`, `shipping_state_province`, `shipping_postal_code`, `shipping_country`, `shipping_contact_number`, `payment_method`, `order_date`, `proof_of_payment_url`, `created_at`, `updated_at`) VALUES
(1, 2, 500.00, 'processing', 'test road', '', 'Cebu city', 'Cebu', '6000', 'Philippines', '09171748881', 'gcash', '2025-05-26 17:50:22', 'images/proof/proof_6834a9dedb788.jpg', '2025-05-26 17:50:22', '2025-05-26 17:51:11'),
(2, 2, 2350.00, 'shipped', 'test road', '', 'Cebu city', 'Cebu', '6000', 'Philippines', '09171748881', 'bank_transfer', '2025-05-27 01:08:42', 'images/proof/proof_6835109a111fc.jpg', '2025-05-27 01:08:42', '2025-07-08 07:12:48');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES
(1, 1, 1, 5, 100.00),
(2, 2, 1, 15, 100.00),
(3, 2, 17, 1, 50.00),
(4, 2, 16, 1, 800.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `size`, `category`, `stock_quantity`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'test2', 'test123', 100.00, NULL, NULL, 0, 'images/products/28f8203c044584e1b922d9df00fd2d05.jpg', '2025-05-26 17:41:39', '2025-05-27 01:08:42'),
(2, 'Black Bunny Luxe Pouch', 'Height 6 inches\r\nLength 10 inches', 200.00, NULL, 'Pouch', 20, 'images/products/5433c7fe2605598fedd7a6c816f38426.png', '2025-05-27 00:23:50', '2025-05-27 01:01:56'),
(3, 'Zebra Zippered Pouch', 'Height 5 inches\r\nLength 9 inches', 200.00, NULL, 'Pouch', 20, 'images/products/00ef278f5dbe3a19dd460d8c452228d4.png', '2025-05-27 00:26:20', '2025-05-27 01:01:47'),
(4, 'Checkered pouch', 'Height 5 inches\r\nLength 9 inches', 200.00, NULL, 'Pouch', 20, 'images/products/c396184bcddf4e8d8fc7909dfeaa4bc9.png', '2025-05-27 00:29:45', '2025-05-27 00:58:31'),
(5, 'Red Luxe Pouch', 'Height 5 inches\r\nLength 9 inches', 200.00, NULL, 'Pouch', 15, 'images/products/ac9f259c316710fbee1ddff149924d09.png', '2025-05-27 00:30:43', '2025-05-27 00:58:23'),
(6, 'Black Hand pianted Classic', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 1000.00, NULL, 'Classic', 3, 'images/products/4090d1c4ef4ce2ba282acf31cb9b48c4.png', '2025-05-27 00:32:09', '2025-05-27 00:58:14'),
(7, 'White Hand painted Classic', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 1000.00, NULL, 'Classic', 3, 'images/products/08ff8c91f689f1e583c7a2e86ba6124a.png', '2025-05-27 00:32:50', '2025-05-27 00:58:08'),
(8, 'Grey Hand painted Classic', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 1000.00, NULL, 'Classic', 3, 'images/products/7995aa7e5a234aecc5bbfb9aff2f1c0d.png', '2025-05-27 00:33:38', '2025-05-27 00:58:00'),
(9, 'Tricolored Classic', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 500.00, NULL, 'Classic', 6, 'images/products/07b1828d08054985c36db187489ccc14.png', '2025-05-27 00:34:17', '2025-05-27 00:57:51'),
(10, 'Zebra Classic', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 500.00, NULL, 'Classic', 7, 'images/products/3cd09db8a93e00613e73ab9b45abbc2d.png', '2025-05-27 00:34:48', '2025-05-27 00:57:44'),
(11, 'White Travel Clutch', 'Height 8 inches\r\nLength 10 inches', 300.00, NULL, 'Clutch', 9, 'images/products/204a2a37229a3862b151db26a8e19b20.png', '2025-05-27 00:35:45', '2025-05-27 00:57:37'),
(12, 'Black Travel Clutch', 'Height 8 inches\r\nLength 10 inches', 300.00, NULL, 'Clutch', 12, 'uploads/products/product_683509320ccb9.png', '2025-05-27 00:36:26', '2025-05-27 00:57:14'),
(13, 'White Luxe Handy', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 800.00, NULL, 'Handy', 15, 'images/products/8a48ee7f09d5696f03276efe6f89d61c.png', '2025-05-27 00:38:03', '2025-05-27 00:57:05'),
(14, 'Grey Luxe Handy', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 800.00, NULL, 'Handy', 13, 'images/products/108bf2b4a558bd3cc2b6c51b8d9b9741.png', '2025-05-27 00:38:47', '2025-05-27 00:56:59'),
(15, 'Black Luxe Sling', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 800.00, NULL, 'Sling', 9, 'images/products/e9dc79ae96afbbc64df5dfc9c600978a.png', '2025-05-27 00:39:47', '2025-05-27 00:56:51'),
(16, 'Brown/Black Luxe Sling', 'Height 10 inches\r\nLength top 16 inches\r\nWidth 6 inches', 800.00, NULL, 'Sling', 13, 'images/products/a328e6e64d4b344ec30b43e4d1973f44.png', '2025-05-27 00:40:42', '2025-05-27 01:08:42'),
(17, 'Pink Floral Ribbon', 'Aesthetic pink ribbon', 50.00, NULL, 'Accessories', 15, 'images/products/f92a7327df8bece188ec7b9a60d665f6.png', '2025-05-27 00:41:23', '2025-05-27 01:08:42');

-- --------------------------------------------------------

--
-- Table structure for table `sales_tracking`
--

CREATE TABLE `sales_tracking` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_sales` decimal(10,2) NOT NULL,
  `total_orders` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin', 'admin@craftsynook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-05-26 15:22:20', '2025-05-26 15:22:20'),
(2, 'Inakoy', 'Vicente Inaki Villa', 'inaki.villa13@gmail.com', '$2y$10$yHHDxITcluaJMs7pAR8nk.Nil8TDFJH/79.ri6iMJdFepTiRNZPNe', 'customer', '2025-05-26 17:49:07', '2025-05-26 17:49:07'),
(3, 'gwapo', 'Rolando gwapo Villa', 'gwapo@gmail.com', '$2y$10$CPh/w8DuymGmnzRAAzzusOza5GHajFKN1lF6VLb.8KD3yAdUJv8mi', 'customer', '2025-05-27 01:09:56', '2025-05-27 01:09:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state_province` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_line1`, `address_line2`, `city`, `state_province`, `postal_code`, `country`, `contact_number`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'test road', '', 'Cebu city', 'Cebu', '6000', 'Philippines', '09171748881', 0, '2025-05-26 17:50:03', '2025-05-26 17:50:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `inquiry_messages`
--
ALTER TABLE `inquiry_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inquiry_messages_thread` (`thread_id`),
  ADD KEY `idx_inquiry_messages_sender` (`sender_id`);

--
-- Indexes for table `inquiry_threads`
--
ALTER TABLE `inquiry_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_inquiry_threads_user` (`user_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_date` (`order_date`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_tracking`
--
ALTER TABLE `sales_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inquiry_messages`
--
ALTER TABLE `inquiry_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inquiry_threads`
--
ALTER TABLE `inquiry_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sales_tracking`
--
ALTER TABLE `sales_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inquiry_messages`
--
ALTER TABLE `inquiry_messages`
  ADD CONSTRAINT `inquiry_messages_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `inquiry_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inquiry_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inquiry_threads`
--
ALTER TABLE `inquiry_threads`
  ADD CONSTRAINT `inquiry_threads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inquiry_threads_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
