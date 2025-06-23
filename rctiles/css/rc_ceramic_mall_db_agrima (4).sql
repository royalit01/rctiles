-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2024 at 04:02 PM
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
-- Database: `rc_ceramic_mall_db_agrima`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `area` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `area`) VALUES
(25, 'laptop', 10),
(26, 'water tap', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `phone_no`, `email`, `address`, `city`, `created_at`) VALUES
(1, 'Agrima shrivastava', '9644704488', NULL, 'indore M.P', NULL, '2024-12-01 18:53:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('Pending','Processed','Delivered') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `area` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `status` enum('Active','Inactive','Discontinued') DEFAULT 'Active',
  `date_added` datetime DEFAULT current_timestamp(),
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `area`, `category_id`, `supplier_id`, `price`, `cost_price`, `status`, `date_added`, `last_updated`) VALUES
(51, 'pink tile', 'washroom Tile', '27312', NULL, 4, 800.00, 750.00, 'Active', '2024-11-08 20:40:46', '2024-11-08 20:40:46'),
(55, 'dell laptop', '1 tb', '201', 25, NULL, 80000.00, 75000.00, 'Active', '2024-11-10 01:23:31', '2024-11-10 01:23:31'),
(56, 'thinkpad', '1tb', '', 25, 4, 80000.00, NULL, 'Active', '2024-11-12 18:36:18', '2024-11-12 18:36:18'),
(67, 'thinkpad', 'htgrfed', '', 25, 4, 65432.00, 4343.00, 'Active', '2024-11-12 19:14:13', '2024-11-12 19:14:13'),
(69, 'red tile', 'lapp', '', 25, 4, 40000.00, 30000.00, 'Active', '2024-11-12 22:41:08', '2024-11-12 22:41:08'),
(70, 'red tile', 'hbgvf', '', 26, 4, 654.00, 4532.00, 'Active', '2024-11-12 22:41:49', '2024-11-12 22:41:49'),
(71, 'mug', 'awesdtgh', '', 25, 4, 800.00, 56.00, 'Active', '2024-12-01 21:17:45', '2024-12-01 21:17:45');

-- --------------------------------------------------------

--
-- Table structure for table `product_stock`
--

CREATE TABLE `product_stock` (
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `storage_area_id` int(11) DEFAULT NULL,
  `pieces_per_packet` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `min_stock_level` int(11) DEFAULT 5,
  `product_image` varchar(255) DEFAULT NULL,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`stock_id`, `product_id`, `storage_area_id`, `pieces_per_packet`, `quantity`, `min_stock_level`, `product_image`, `last_updated`) VALUES
(39, 51, NULL, 6, 176, 5, '../uploads/672e29f69fc6e.jpg', '2024-11-11 00:31:26'),
(40, 51, 12, 6, 12, 5, NULL, '2024-11-12 22:35:52'),
(50, 55, 12, 3, 30, 4, NULL, '2024-11-10 01:23:31'),
(55, 67, NULL, 3, 96, 3, NULL, '2024-11-12 19:14:13'),
(56, 51, 13, 6, 6, 5, NULL, '2024-11-12 22:35:52'),
(57, 69, 13, 3, 120, 8, NULL, '2024-11-12 22:41:08'),
(58, 70, 12, 4, 20, 7, NULL, '2024-11-12 22:41:49'),
(59, 71, 12, 6, 420, 7, '../uploads/674c85219312a.png', '2024-12-01 21:17:45');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(4, 'Delivery'),
(2, 'Manager'),
(3, 'Salesperson');

-- --------------------------------------------------------

--
-- Table structure for table `storage_areas`
--

CREATE TABLE `storage_areas` (
  `storage_area_id` int(11) NOT NULL,
  `storage_area_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storage_areas`
--

INSERT INTO `storage_areas` (`storage_area_id`, `storage_area_name`, `location`) VALUES
(12, 'godown ', 'Indore'),
(13, 'hp factory', 'japan');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `supplier_details`) VALUES
(4, 'Agrima ', 'indore tiles'),
(5, 'ayush', 'indore seat');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `storage_area_id` int(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_no` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `aadhar_id_no` varchar(12) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `date_joined` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `user_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `storage_area_id`, `email`, `phone_no`, `password`, `aadhar_id_no`, `role_id`, `date_joined`, `last_login`, `user_image`) VALUES
(4, 'Ayushmaan', 0, 'ayushmaan@example.com', '8458888458', '$2y$10$whPtloXqIqnTm2i99n3El.H6ZwLcGDtE/ygE4TBfLcunQz.jiCGQO', NULL, 1, '2024-09-23 22:03:08', NULL, NULL),
(5, 'Mayank', 0, 'mayank@example.com', '8458888459', '$2y$10$znntg2J5Y0Omy3xIJhB5BukYd/X/y1FBOHBpGuoDewCtXmgVQV8V6', NULL, 2, '2024-09-23 22:03:08', NULL, NULL),
(6, 'vedansh', 0, 'vedansh@example.com', '8458888457', '$2y$10$0zJnW2EPH35bmCxVBOSx3umyeaZLuxir4oyt7Y4Izzqmrd5FhVScS', NULL, 3, '2024-09-23 22:03:08', NULL, NULL),
(9, 'Agrima shrivastava', 12, '', '9644704488', '$2y$10$rGym8RVI8Bz6DtOIof6KF.Yp2AMs7S9FVNNMxQEHAZq78f.7me7Vm', '', 1, '2024-12-02 00:07:19', NULL, NULL),
(15, 'vini', NULL, '', '0964470448855', '$2y$10$OCzBLmh2jpohy4cV/HkU2O57eGODa/35zNw4gF0ytt0ahNh1lKce.', '', 1, '2024-12-02 00:20:23', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_ledger`
--

CREATE TABLE `user_ledger` (
  `ledger_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `transaction_type` enum('Owed','Repaid','Custom Paid') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`),
  ADD KEY `area` (`area`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `phone_no` (`phone_no`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_category` (`category_id`),
  ADD KEY `products_ibfk_1` (`supplier_id`);

--
-- Indexes for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`stock_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `storage_area_id` (`storage_area_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `storage_areas`
--
ALTER TABLE `storage_areas`
  ADD PRIMARY KEY (`storage_area_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `phone_no` (`phone_no`),
  ADD KEY `role_id` (`role_id`);
ALTER TABLE `users` ADD FULLTEXT KEY `email` (`email`);
ALTER TABLE `users` ADD FULLTEXT KEY `aadhar_id_no` (`aadhar_id_no`);

--
-- Indexes for table `user_ledger`
--
ALTER TABLE `user_ledger`
  ADD PRIMARY KEY (`ledger_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `storage_areas`
--
ALTER TABLE `storage_areas`
  MODIFY `storage_area_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_ledger`
--
ALTER TABLE `user_ledger`
  MODIFY `ledger_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_storage_area` FOREIGN KEY (`storage_area_id`) REFERENCES `storage_areas` (`storage_area_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `user_ledger`
--
ALTER TABLE `user_ledger`
  ADD CONSTRAINT `user_ledger_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
