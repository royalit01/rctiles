-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2025 at 11:28 PM
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
-- Table structure for table `admin_cash_collection`
--

CREATE TABLE `admin_cash_collection` (
  `collection_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_cash_collection`
--

INSERT INTO `admin_cash_collection` (`collection_id`, `user_id`, `amount`, `collected_at`, `notes`) VALUES
(1, 6, 584.00, '2025-05-24 19:11:35', 'Cash collected by admin'),
(2, 6, 1000.00, '2025-05-24 19:38:00', 'Cash collected by admin'),
(3, 6, 1000.00, '2025-05-24 19:41:15', 'Cash collected by admin'),
(4, 6, 500.00, '2025-05-24 19:41:21', 'Cash collected by admin');

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
(30, '12X18', 0),
(31, '12X78', 0),
(32, '10X10', 0),
(33, '32X32', 0);

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
(28, 'vini', '8596857456', NULL, 'agar', 'agarmalwa', '2025-03-01 17:30:18'),
(29, 'ayush', '09644704488', NULL, 'indore', 'indore', '2025-03-01 17:44:09'),
(30, 'ayush', '8548549658', NULL, 'ujjain', 'ujjain', '2025-03-01 18:29:06'),
(31, 'Agrima shrivastava', '09644704', NULL, 'indore', 'indore', '2025-03-01 18:54:18'),
(32, 'Agrima shrivastava', '0964470448', NULL, 'indore', 'indore', '2025-03-21 00:57:07'),
(33, 'Avadhi', '9644704488', NULL, 'nhgfd', 'indore', '2025-03-24 23:12:41'),
(34, 'ravi', '8888555522', NULL, 'indoreia', 'indore', '2025-04-02 00:39:28'),
(35, 'raas', '3446765745', NULL, 'ffs', 'fsdf', '2025-04-02 00:55:41'),
(36, 'sdcs', '3456789222', NULL, '22', 'rrrwe', '2025-04-02 00:57:36'),
(37, 'mrs.sahu', '9685745698', NULL, 'indore, mahalaxmi', 'indore', '2025-05-01 01:07:24'),
(38, 'divya', '0964470477', NULL, 'indore', 'indore', '2025-05-15 21:09:48'),
(39, 'ayushmaan sahu', '8458888458', NULL, 'ujjain', 'ujjain', '2025-05-16 11:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_items`
--

CREATE TABLE `delivery_items` (
  `id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty_ordered` int(11) NOT NULL,
  `qty_delivered` int(11) NOT NULL DEFAULT 0,
  `qty_returned` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_items`
--

INSERT INTO `delivery_items` (`id`, `delivery_id`, `product_id`, `qty_ordered`, `qty_delivered`, `qty_returned`) VALUES
(1, 6, 19, 3, 2, 1),
(2, 6, 24, 2, 2, 0),
(3, 7, 31, 2, 0, 0),
(4, 8, 31, 3, 0, 0),
(5, 8, 19, 3, 0, 0),
(6, 8, 23, 2, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_orders`
--

CREATE TABLE `delivery_orders` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_user_id` int(11) DEFAULT NULL,
  `rent` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `amount_remaining` decimal(10,2) DEFAULT 0.00,
  `status` enum('Assigned','Partially Paid','Completed') DEFAULT 'Assigned',
  `assigned_at` datetime DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL
  `item_delivered ` tinyint(1) DEFAULT 0,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_orders`
--

INSERT INTO `delivery_orders` (`delivery_id`, `order_id`, `delivery_user_id`, `rent`, `amount_paid`, `amount_remaining`, `status`, `assigned_at`, `delivered_at`) VALUES
(1, 20, 6, 0.00, 1000.00, 0.00, 'Completed', '2025-05-17 20:57:34', NULL),
(2, 21, 6, 300.00, 0.00, 1800.00, 'Assigned', '2025-05-20 20:44:56', NULL),
(3, 21, 6, 300.00, 0.00, 1800.00, 'Assigned', '2025-05-20 20:58:09', NULL),
(6, 10, 6, 200.00, 800.00, 100.00, 'Completed', '2025-05-24 00:40:07', NULL),
(7, 2, 6, 200.00, 800.00, 0.00, 'Completed', '2025-05-24 23:27:34', NULL),
(8, 4, 6, 900.00, 1584.00, 89000.00, 'Partially Paid', '2025-05-24 23:48:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_payments`
--

CREATE TABLE `delivery_payments` (
  `id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL,
  `collected_by` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_payments`
--

INSERT INTO `delivery_payments` (`id`, `delivery_id`, `payment_date`, `amount_paid`, `collected_by`, `remarks`) VALUES
(3, 1, '2025-05-24 00:38:10', 800.00, NULL, ''),
(4, 1, '2025-05-24 00:38:27', 200.00, NULL, ''),
(5, 6, '2025-05-24 00:47:03', 700.00, NULL, '200 baki'),
(6, 6, '2025-05-24 22:34:26', 100.00, NULL, 'cash'),
(9, 8, '2025-05-24 23:49:04', 584.00, NULL, 'cash'),
(10, 8, '2025-05-25 01:07:36', 1000.00, NULL, 'cash');

-- --------------------------------------------------------

--
-- Table structure for table `minus_stock`
--

CREATE TABLE `minus_stock` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `storage_area_id` int(11) NOT NULL,
  `quantity_subtracted` int(11) NOT NULL,
  `subtracted_by` int(11) NOT NULL,
  `subtracted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `minus_stock`
--

INSERT INTO `minus_stock` (`id`, `order_id`, `product_id`, `storage_area_id`, `quantity_subtracted`, `subtracted_by`, `subtracted_at`) VALUES
(1, 2, 31, 13, 1, 4, '2025-05-26 02:14:47'),
(2, 2, 31, 13, 1, 4, '2025-05-26 02:14:59'),
(3, 20, 19, 12, 2, 4, '2025-05-26 02:22:25'),
(4, 20, 23, 12, 3, 4, '2025-05-26 02:22:25'),
(5, 10, 19, 12, 1, 21, '2025-05-26 02:45:13');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discounted_amount` decimal(10,2) DEFAULT NULL,
  `transport_rent` decimal(10,2) DEFAULT 0.00,
  `stock_done` tinyint(1) DEFAULT 0,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_date`, `customer_id`, `total_amount`, `discounted_amount`, `transport_rent`, `stock_done`, `deleted`) VALUES
(2, '2025-03-01 17:30:18', 28, 600.00, 600.00, 200.00, 1, 0),
(3, '2025-03-01 17:44:09', 29, 1200.00, NULL, 0.00, 0, 0),
(4, '2025-03-01 18:29:06', 30, 89684.00, 89684.00, 900.00, 0, 0),
(5, '2025-03-01 18:47:22', 29, 131.00, NULL, 0.00, 0, 0),
(6, '2025-03-01 18:51:15', 29, 131.00, NULL, 0.00, 0, 0),
(7, '2025-03-01 18:51:57', 29, 131.00, NULL, 0.00, 0, 0),
(8, '2025-03-01 18:54:18', 31, 89584.00, NULL, 0.00, 0, 0),
(9, '2025-03-21 00:57:07', 32, 746.00, NULL, 0.00, 0, 0),
(10, '2025-03-24 23:12:41', 33, 764.00, 700.00, 200.00, 1, 0),
(11, '2025-04-02 00:39:28', 34, 177568.00, NULL, 0.00, 0, 0),
(12, '2025-04-02 00:55:41', 35, 177468.00, NULL, 0.00, 0, 0),
(13, '2025-04-02 00:57:36', 36, 44342.00, NULL, 0.00, 0, 0),
(14, '2025-05-01 01:05:06', 32, 431.00, NULL, 0.00, 0, 0),
(16, '2025-05-01 01:16:35', 32, 433.00, NULL, 0.00, 0, 0),
(17, '2025-05-01 01:18:28', 32, 300.00, NULL, 0.00, 0, 0),
(20, '2025-05-15 21:09:48', 38, 1000.00, 800.00, 200.00, 1, 0),
(21, '2025-05-16 11:53:50', 39, 1500.00, NULL, 300.00, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pending_orders`
--

CREATE TABLE `pending_orders` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `stock_subtracted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `multiplier` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_orders`
--

INSERT INTO `pending_orders` (`id`, `order_id`, `customer_id`, `product_id`, `product_name`, `quantity`, `original_price`, `custom_price`, `approved`, `stock_subtracted`, `created_at`, `multiplier`) VALUES
(3, 2, 28, 31, 'magentaa tile', 2, 300.00, 600.00, 1, 1, '2025-03-01 12:00:18', 1),
(4, 3, 29, 31, 'magentaa tile', 4, 300.00, 1200.00, -1, 0, '2025-03-01 12:14:09', 1),
(5, 4, 30, 31, 'magentaa tile', 3, 300.00, 900.00, 1, 0, '2025-03-01 12:59:06', 1),
(6, 4, 30, 19, 'buds 1', 3, 100.00, 300.00, 1, 0, '2025-03-01 12:59:06', 1),
(7, 4, 30, 23, 'tub', 2, 44242.00, 88484.00, 1, 0, '2025-03-01 12:59:06', 1),
(8, 5, 29, 30, 'e2we', 1, 131.00, 131.00, 0, 0, '2025-03-01 13:17:22', 1),
(9, 6, 29, 30, 'e2we', 1, 131.00, 131.00, 0, 0, '2025-03-01 13:21:15', 1),
(10, 7, 29, 30, 'e2we', 1, 131.00, 131.00, 0, 0, '2025-03-01 13:21:57', 1),
(11, 8, 31, 31, 'magentaa tile', 3, 300.00, 900.00, 1, 0, '2025-03-01 13:24:18', 1),
(12, 8, 31, 19, 'buds 1', 2, 100.00, 200.00, 1, 0, '2025-03-01 13:24:18', 1),
(13, 8, 31, 23, 'tub', 2, 44242.00, 88484.00, 1, 0, '2025-03-01 13:24:18', 1),
(14, 9, 32, 19, 'buds 1', 2, 100.00, 200.00, 1, 0, '2025-03-20 19:27:07', 1),
(15, 9, 32, 25, 'mug2323', 2, 11.00, 22.00, 1, 0, '2025-03-20 19:27:07', 1),
(16, 9, 32, 30, 'e2we', 4, 131.00, 524.00, 1, 0, '2025-03-20 19:27:07', 1),
(17, 10, 33, 19, 'buds 1', 3, 100.00, 300.00, 1, 0, '2025-03-24 17:42:41', 1),
(18, 10, 33, 24, 'wdwdedf', 2, 232.00, 464.00, 1, 0, '2025-03-24 17:42:41', 1),
(19, 11, 34, 19, 'buds 1', 6, 100.00, 600.00, -1, 0, '2025-04-01 19:09:28', 2),
(20, 11, 34, 23, 'tub', 4, 44242.00, 176968.00, -1, 0, '2025-04-01 19:09:28', 2),
(21, 12, 35, 19, 'buds 1', 5, 100.00, 500.00, -1, 0, '2025-04-01 19:25:41', 1),
(22, 12, 35, 23, 'tub', 4, 44242.00, 176968.00, -1, 0, '2025-04-01 19:25:41', 1),
(23, 13, 36, 23, 'tub', 1, 44242.00, 44242.00, -1, 0, '2025-04-01 19:27:36', 1),
(24, 13, 36, 19, 'buds 1', 1, 100.00, 100.00, -1, 0, '2025-04-01 19:27:36', 1),
(25, 14, 32, 30, 'e2we', 1, 131.00, 131.00, -1, 0, '2025-04-30 19:35:06', 1),
(26, 14, 32, 31, 'magentaa tile', 1, 300.00, 300.00, -1, 0, '2025-04-30 19:35:06', 1),
(27, 15, 37, 30, 'e2we', 5, 131.00, 610.74, 1, 1, '2025-04-30 19:37:24', 1),
(28, 15, 37, 31, 'magentaa tile', 3, 300.00, 789.26, 1, 1, '2025-04-30 19:37:24', 1),
(29, 16, 32, 23, 'tub', 1, 400.00, 400.00, 0, 0, '2025-04-30 19:46:35', 1),
(30, 16, 32, 25, 'mug2323', 1, 11.00, 11.00, 0, 0, '2025-04-30 19:46:35', 1),
(31, 16, 32, 26, 'tub one11', 2, 11.00, 22.00, 0, 0, '2025-04-30 19:46:35', 1),
(32, 17, 32, 31, 'magentaa tile', 1, 300.00, 300.00, 0, 0, '2025-04-30 19:48:28', 1),
(33, 18, 32, 26, 'tub one11', 2, 11.00, 14.29, 1, 1, '2025-05-01 08:50:47', 1),
(34, 18, 32, 25, 'mug2323', 4, 11.00, 35.71, 1, 1, '2025-05-01 08:50:47', 1),
(35, 19, 32, 30, 'e2we', 2, 131.00, 240.00, 1, 1, '2025-05-15 14:23:59', 1),
(36, 19, 32, 31, 'magentaa tile', 3, 300.00, 750.00, 1, 1, '2025-05-15 14:23:59', 1),
(37, 20, 38, 19, 'buds 1', 2, 100.00, 150.94, 1, 0, '2025-05-15 15:39:48', 1),
(38, 20, 38, 23, 'tub', 3, 400.00, 849.06, 1, 0, '2025-05-15 15:39:48', 1),
(39, 21, 39, 19, 'buds 1', 2, 100.00, 153.85, 1, 0, '2025-05-16 06:23:50', 1),
(40, 21, 39, 23, 'tub', 4, 400.00, 1346.15, 1, 0, '2025-05-16 06:23:50', 1);

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
  `product_image` varchar(255) DEFAULT '../uploads/675e9296d42d0.png',
  `status` enum('Active','Inactive','Discontinued') DEFAULT 'Active',
  `date_added` datetime DEFAULT current_timestamp(),
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `area`, `category_id`, `supplier_id`, `price`, `cost_price`, `product_image`, `status`, `date_added`, `last_updated`) VALUES
(19, 'buds 1', 'Realme', '100', 30, NULL, 100.00, 200.00, NULL, 'Active', '2024-12-10 23:43:02', '2024-12-11 00:38:55'),
(20, 'buds 2', 'Realme 2', '200', NULL, NULL, 500.00, 600.00, NULL, 'Active', '2024-12-10 23:43:02', '2024-12-10 23:43:02'),
(22, 'hello', 'gjj', '', NULL, 4, 111.00, 111.00, '../uploads/67589034b7ed1.png', 'Active', '2024-12-11 00:32:12', '2024-12-11 00:32:12'),
(23, 'tub', 'sfggre', '100', 30, 4, 400.00, 22.00, NULL, 'Active', '2024-12-15 13:54:36', '2025-04-02 00:58:02'),
(24, 'wdwdedf', 'dadd', '', 30, 4, 232.00, 22.00, '../uploads/675e9296d42d0.png', 'Active', '2024-12-15 13:55:58', '2024-12-15 13:55:58'),
(25, 'mug2323', '1111', '', 30, 4, 11.00, 1.00, NULL, 'Active', '2024-12-15 13:57:11', '2024-12-15 13:57:11'),
(26, 'tub one11', '11', '', 30, 4, 11.00, 11.00, '..\\assets\\img\\default_img.jpg', 'Active', '2024-12-15 17:13:00', '2024-12-15 17:13:00'),
(27, 'tubqqq', 'qqq', '', 30, 4, 11.00, 11.00, '..\\assets\\img\\default-image-icon-vector-missing-picture-page-website-design-mobile-app-no-photo-available_87543-11093.avif', 'Active', '2024-12-15 17:13:55', '2024-12-15 17:13:55'),
(28, 'tub one2', '22', '', 30, 4, 22.00, 22.00, '..\\assets\\img\\default-image-icon-vector-missing-picture-page-website-design-mobile-app-no-photo-available_87543-11093.avif', 'Active', '2024-12-15 17:15:27', '2024-12-15 17:15:27'),
(29, 'Ddd', 'adadad', '', 33, 4, 30.00, 1212.00, '..\\assets\\img\\default-image-icon-vector-missing-picture-page-website-design-mobile-app-no-photo-available_87543-11093.avif', 'Active', '2025-01-05 20:41:38', '2025-04-02 00:58:13'),
(30, 'e2we', 'sDd', '', 32, 4, 131.00, 12.00, '..\\assets\\img\\default-image-icon-vector-missing-picture-page-website-design-mobile-app-no-photo-available_87543-11093.avif', 'Active', '2025-01-05 20:41:48', '2025-01-05 20:41:48'),
(31, 'magentaa tile', 'blue', '500', 32, 4, 300.00, 200.00, '../uploads/bill demo.jpg', 'Active', '2025-02-28 02:39:57', '2025-03-01 15:51:37');

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
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`stock_id`, `product_id`, `storage_area_id`, `pieces_per_packet`, `quantity`, `min_stock_level`, `last_updated`) VALUES
(79, 19, 12, 6, -23, 10, '2025-05-26 02:45:13'),
(80, 20, 13, 8, 240, 10, '2025-03-01 15:34:11'),
(81, 22, 12, 3, 363, 7, '2024-12-11 00:32:12'),
(82, 20, 12, 8, 880, 5, '2025-02-27 15:56:57'),
(84, 23, 12, 22, 175, 7, '2025-05-26 02:22:25'),
(85, 24, 12, 2, 42, 7, '2025-02-27 21:26:43'),
(86, 25, 12, 1, 8, 7, '2025-05-16 01:12:48'),
(87, 26, 12, 11, 110, 7, '2025-05-16 01:12:48'),
(88, 27, 12, 1, 111, 7, '2025-02-27 21:26:59'),
(89, 28, 12, 2, 4, 7, '2024-12-15 17:15:27'),
(90, 29, 13, 12, 2280, 7, '2025-03-01 15:34:11'),
(91, 30, 12, 121, 20686, 7, '2025-05-17 19:47:10'),
(92, 24, 13, 2, 4, 5, '2025-03-01 15:50:09'),
(93, 31, 12, 5, 2, 7, '2025-05-25 21:08:33'),
(94, 31, 13, 5, 283, 5, '2025-05-26 02:14:59'),
(95, 31, 17, 5, 110, 5, '2025-03-01 15:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `recycle_bin_delivery_items`
--

CREATE TABLE `recycle_bin_delivery_items` (
  `id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty_ordered` int(11) NOT NULL,
  `qty_delivered` int(11) NOT NULL DEFAULT 0,
  `qty_returned` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycle_bin_delivery_orders`
--

CREATE TABLE `recycle_bin_delivery_orders` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_user_id` int(11) DEFAULT NULL,
  `rent` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `amount_remaining` decimal(10,2) DEFAULT 0.00,
  `status` enum('Assigned','Partially Paid','Completed') DEFAULT 'Assigned',
  `assigned_at` datetime DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycle_bin_orders`
--

CREATE TABLE `recycle_bin_orders` (
  `order_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discounted_amount` decimal(10,2) DEFAULT NULL,
  `transport_rent` decimal(10,2) DEFAULT 0.00,
  `stock_done` tinyint(1) DEFAULT 0,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recycle_bin_pending_orders`
--

CREATE TABLE `recycle_bin_pending_orders` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `custom_price` decimal(10,2) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `stock_subtracted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `multiplier` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rider_income`
--

CREATE TABLE `rider_income` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivery_id` int(11) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rider_income`
--

INSERT INTO `rider_income` (`id`, `user_id`, `delivery_id`, `payment_date`, `amount`, `remarks`) VALUES
(1, 6, 1, '2025-05-25 01:42:20', 300.00, 'payment done'),
(2, 6, NULL, '2025-05-25 19:34:47', 200.00, '');

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
(13, 'hp factory', 'japan'),
(17, 'agrima ', 'agar');

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
(9, 'adadad', 'adad');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `storage_area_id` int(11) DEFAULT NULL,
  `transaction_type` enum('Add','Subtract') DEFAULT NULL,
  `quantity_changed` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `description` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `product_id`, `storage_area_id`, `transaction_type`, `quantity_changed`, `transaction_date`, `description`) VALUES
(50, 4, 19, 12, 'Subtract', 2, '2025-02-27 21:26:22', NULL),
(51, 4, 23, 12, 'Subtract', 22, '2025-02-27 21:26:22', NULL),
(52, 4, 27, 12, 'Subtract', 2, '2025-02-27 21:26:22', NULL),
(53, 4, 24, 12, 'Subtract', 2, '2025-02-27 21:26:43', NULL),
(54, 4, 27, 12, 'Add', 5, '2025-02-27 21:26:59', NULL),
(55, 4, 19, 12, 'Add', 144, '2025-03-01 15:38:36', NULL),
(56, 4, 31, 13, 'Add', 3, '2025-03-01 15:38:59', NULL),
(57, 4, 24, 13, 'Subtract', 2, '2025-03-01 15:50:09', NULL),
(58, 4, 31, 12, 'Subtract', 5, '2025-03-01 15:52:19', NULL),
(65, 4, 30, 12, 'Subtract', 5, '2025-05-16 00:37:32', 'Order 15'),
(66, 4, 31, 12, 'Subtract', 3, '2025-05-16 00:37:32', 'Order 15'),
(67, 4, 26, 12, 'Subtract', 2, '2025-05-16 01:12:48', 'Order 18'),
(68, 4, 25, 12, 'Subtract', 4, '2025-05-16 01:12:48', 'Order 18'),
(69, 4, 30, 12, 'Subtract', 2, '2025-05-16 11:58:15', 'Order 19'),
(70, 4, 31, 13, 'Subtract', 3, '2025-05-16 11:58:15', 'Order 19'),
(71, 4, 30, 12, 'Subtract', 5, '2025-05-17 19:47:10', 'Order 15'),
(72, 4, 31, 12, 'Subtract', 3, '2025-05-17 19:47:10', 'Order 15'),
(73, 4, 31, 12, 'Subtract', 1, '2025-05-25 21:08:33', 'Order 2'),
(74, 4, 31, 13, 'Subtract', 1, '2025-05-26 01:18:08', 'Order 2'),
(75, 4, 31, 13, 'Subtract', 1, '2025-05-26 01:18:20', 'Order 2'),
(76, 4, 31, 13, 'Subtract', 1, '2025-05-26 01:33:10', 'Order 2'),
(77, 4, 31, 13, 'Subtract', 1, '2025-05-26 01:33:25', 'Order 2');

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
(4, 'Ayushmaan', 13, 'ayushmaansahu@example.com', '8458888458', '$2y$10$whPtloXqIqnTm2i99n3El.H6ZwLcGDtE/ygE4TBfLcunQz.jiCGQO', '', 1, '2024-09-23 22:03:08', NULL, 'Ayush photo.png'),
(6, 'vedansh', 0, 'vedansh@example.com', '7894561230', '$2y$10$YvSvJu1OaiHzxQGlhO.3t.BRVeU3407um9aeayJuMXpHHcv7B2hkG', '', 4, '2024-09-23 22:03:08', NULL, NULL),
(20, 'ramesh salelsman', 0, '', '7418529630', '$2y$10$Ca0U5N9ZGubSVqBoSyAVjuirB3tyEj2eUO2saPZ5GhflRV/qQmZgG', '', 3, '2025-05-24 17:50:19', NULL, NULL),
(21, 'uday Manager', NULL, '', '9638527410', '$2y$10$YsQ/OrniyndcrDbkuhzXkOaG4cp3ZujOYtqkDIIgPXmV6l/NGWNhS', '', 2, '2025-05-24 17:57:01', NULL, NULL);

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
-- Dumping data for table `user_ledger`
--

INSERT INTO `user_ledger` (`ledger_id`, `user_id`, `transaction_date`, `transaction_type`, `amount`, `balance`, `remarks`) VALUES
(1, 6, '2025-05-25 00:14:22', 'Repaid', 584.00, -584.00, 'Cash handed over to admin');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_delivery_balance`
-- (See below for the actual view)
--
CREATE TABLE `v_delivery_balance` (
`delivery_id` int(11)
,`order_id` int(11)
,`status` enum('Assigned','Partially Paid','Completed')
,`amount_paid` decimal(10,2)
,`amount_remaining` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Structure for view `v_delivery_balance`
--
DROP TABLE IF EXISTS `v_delivery_balance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`` SQL SECURITY DEFINER VIEW `v_delivery_balance`  AS SELECT `d`.`delivery_id` AS `delivery_id`, `d`.`order_id` AS `order_id`, `d`.`status` AS `status`, `d`.`amount_paid` AS `amount_paid`, `d`.`amount_remaining` AS `amount_remaining` FROM `delivery_orders` AS `d` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_cash_collection`
--
ALTER TABLE `admin_cash_collection`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `idx_user` (`user_id`);

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
-- Indexes for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_del_items_del` (`delivery_id`),
  ADD KEY `fk_del_items_prod` (`product_id`);

--
-- Indexes for table `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `delivery_user_id` (`delivery_user_id`);

--
-- Indexes for table `delivery_payments`
--
ALTER TABLE `delivery_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_del` (`delivery_id`);

--
-- Indexes for table `minus_stock`
--
ALTER TABLE `minus_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `storage_area_id` (`storage_area_id`),
  ADD KEY `subtracted_by` (`subtracted_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pending_order` (`order_id`),
  ADD KEY `fk_pending_customer` (`customer_id`),
  ADD KEY `fk_pending_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_name` (`product_name`),
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
-- Indexes for table `recycle_bin_delivery_items`
--
ALTER TABLE `recycle_bin_delivery_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_del_items_del` (`delivery_id`),
  ADD KEY `fk_del_items_prod` (`product_id`);

--
-- Indexes for table `recycle_bin_delivery_orders`
--
ALTER TABLE `recycle_bin_delivery_orders`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `delivery_user_id` (`delivery_user_id`);

--
-- Indexes for table `recycle_bin_orders`
--
ALTER TABLE `recycle_bin_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `recycle_bin_pending_orders`
--
ALTER TABLE `recycle_bin_pending_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pending_order` (`order_id`),
  ADD KEY `fk_pending_customer` (`customer_id`),
  ADD KEY `fk_pending_product` (`product_id`);

--
-- Indexes for table `rider_income`
--
ALTER TABLE `rider_income`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `delivery_id` (`delivery_id`);

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
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `storage_area_id` (`storage_area_id`);

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
-- AUTO_INCREMENT for table `admin_cash_collection`
--
ALTER TABLE `admin_cash_collection`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `delivery_items`
--
ALTER TABLE `delivery_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `delivery_orders`
--
ALTER TABLE `delivery_orders`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `delivery_payments`
--
ALTER TABLE `delivery_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `minus_stock`
--
ALTER TABLE `minus_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pending_orders`
--
ALTER TABLE `pending_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `recycle_bin_delivery_items`
--
ALTER TABLE `recycle_bin_delivery_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `recycle_bin_delivery_orders`
--
ALTER TABLE `recycle_bin_delivery_orders`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `recycle_bin_orders`
--
ALTER TABLE `recycle_bin_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recycle_bin_pending_orders`
--
ALTER TABLE `recycle_bin_pending_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rider_income`
--
ALTER TABLE `rider_income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `storage_areas`
--
ALTER TABLE `storage_areas`
  MODIFY `storage_area_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_ledger`
--
ALTER TABLE `user_ledger`
  MODIFY `ledger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD CONSTRAINT `fk_del_items_del` FOREIGN KEY (`delivery_id`) REFERENCES `delivery_orders` (`delivery_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_del_items_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD CONSTRAINT `fk_delivery_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_delivery_user` FOREIGN KEY (`delivery_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `delivery_payments`
--
ALTER TABLE `delivery_payments`
  ADD CONSTRAINT `fk_payments_del` FOREIGN KEY (`delivery_id`) REFERENCES `delivery_orders` (`delivery_id`) ON DELETE CASCADE;

--
-- Constraints for table `minus_stock`
--
ALTER TABLE `minus_stock`
  ADD CONSTRAINT `minus_stock_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `minus_stock_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `minus_stock_ibfk_3` FOREIGN KEY (`storage_area_id`) REFERENCES `storage_areas` (`storage_area_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `minus_stock_ibfk_4` FOREIGN KEY (`subtracted_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_storage_area` FOREIGN KEY (`storage_area_id`) REFERENCES `storage_areas` (`storage_area_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rider_income`
--
ALTER TABLE `rider_income`
  ADD CONSTRAINT `rider_income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `rider_income_ibfk_2` FOREIGN KEY (`delivery_id`) REFERENCES `delivery_orders` (`delivery_id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`storage_area_id`) REFERENCES `storage_areas` (`storage_area_id`);

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
