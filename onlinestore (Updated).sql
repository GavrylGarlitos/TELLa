-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2025 at 03:59 PM
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
-- Database: `onlinestore`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `profile_image` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `address`, `contact`, `birthday`, `profile_image`, `created_at`) VALUES
(1, 'admin', 'Admin@123', 'admin@example.com', NULL, NULL, NULL, NULL, '2025-06-26 23:33:26');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'Pending',
  `shipping_carrier` varchar(50) DEFAULT NULL,
  `shipping_method` varchar(50) DEFAULT NULL,
  `estimated_days` int(11) DEFAULT NULL,
  `ordered_date` datetime DEFAULT current_timestamp(),
  `delivered_date` date DEFAULT NULL,
  `shipping_status` varchar(50) DEFAULT 'Preparing',
  `order_notes` text DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `return_requested` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `product_id`, `quantity`, `total_price`, `payment_method`, `payment_status`, `shipping_carrier`, `shipping_method`, `estimated_days`, `ordered_date`, `delivered_date`, `shipping_status`, `order_notes`, `tracking_number`, `return_requested`) VALUES
(1, 41, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-07 22:12:40', NULL, 'Shipping', 'Paolo gorospe', NULL, 0),
(2, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 18:55:26', NULL, 'Delivered', '', NULL, 0),
(3, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 18:55:35', NULL, 'Delivered', '', NULL, 0),
(4, 42, 3, 1, 223.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 22:40:53', NULL, 'Delivered', '', NULL, 0),
(5, 42, 3, 1, 223.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 22:48:23', NULL, 'Delivered', '', NULL, 0),
(6, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:18:28', NULL, 'Preparing', NULL, NULL, 0),
(7, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:24:32', NULL, 'Preparing', NULL, NULL, 0),
(8, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:30:40', NULL, 'Preparing', NULL, NULL, 0),
(9, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:33:02', NULL, 'Preparing', NULL, NULL, 0),
(10, 42, 2, 1, 223.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:36:07', NULL, 'Preparing', NULL, NULL, 0),
(11, 42, 3, 1, 223.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:36:32', NULL, 'Preparing', NULL, NULL, 0),
(12, 42, 1, 1, 12.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:43:17', NULL, 'Preparing', NULL, NULL, 0),
(13, 42, 2, 1, 123.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:43:17', NULL, 'Preparing', NULL, NULL, 0),
(14, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:44:42', NULL, 'Preparing', NULL, NULL, 0),
(15, 42, 2, 1, 223.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:45:40', NULL, 'Preparing', NULL, NULL, 0),
(16, 42, 2, 1, 223.00, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:45:48', NULL, 'Preparing', NULL, NULL, 0),
(17, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:45:52', NULL, 'Preparing', NULL, NULL, 0),
(18, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:47:09', NULL, 'Preparing', NULL, NULL, 0),
(19, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-08 23:53:50', NULL, 'Preparing', NULL, NULL, 0),
(20, 42, 1, 1, 112.30, 'credit_card', 'Pending', NULL, '0', 5, '2025-07-09 21:43:16', NULL, 'Preparing', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `quantity`, `image_path`) VALUES
(1, 'MAMA MO', 'TATAYO MO', 12.30, 4, 'uploads/products/prod_6866470ce77725.62862539.png'),
(2, 'Paolo', 'Paolo', 123.00, 123, ''),
(3, '123', '123', 123.00, 123, '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `profile_image` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `address`, `contact`, `birthday`, `profile_image`, `created_at`, `firstname`, `lastname`, `region`, `province`, `city`, `barangay`, `blocked`) VALUES
(43, 'paolo', '$2y$10$xs7UemgorX/PAkqNcwey6uW9V0UyL9uzxwtQs.1oiCEMG2bBEK0LK', 'paologorospe901@gmail.com', '12B Balimbing Street', '09064940995', '2025-07-31', NULL, '2025-07-09 13:56:09', 'Paolo', 'Gorospe', '13', '1339', '133907', '133907008', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
