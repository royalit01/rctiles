CREATE DATABASE u997998014_rc_ceramic;

USE u997998014_rc_ceramic;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: u997998014_rc_ceramic
--

-- --------------------------------------------------------

--
-- Table structure for table admin_cash_collection
--

CREATE TABLE admin_cash_collection (
  collection_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  amount decimal(12,2) NOT NULL,
  collected_at timestamp NOT NULL DEFAULT current_timestamp(),
  notes varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table admin_cash_collection
--

INSERT INTO admin_cash_collection (collection_id, user_id, amount, collected_at, notes) VALUES
(1, 2, 3200.00, '2025-06-10 18:44:25', 'Cash collected by admin'),
(2, 2, 56.00, '2025-06-10 18:44:34', 'Cash collected by admin'),
(3, 2, 4000.00, '2025-06-10 18:46:16', 'Cash collected by admin'),
(4, 2, 1523.00, '2025-06-10 18:46:21', 'Cash collected by admin');

-- --------------------------------------------------------

--
-- Table structure for table category
--

CREATE TABLE category (
  category_id int(11) NOT NULL,
  category_name varchar(255) NOT NULL,
  area int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table category
--

INSERT INTO category (category_id, category_name, area) VALUES
(1, '20X20', 0),
(2, '10X10', 0);

-- --------------------------------------------------------

--
-- Table structure for table customers
--

CREATE TABLE customers (
  customer_id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  phone_no varchar(15) NOT NULL,
  email varchar(255) DEFAULT NULL,
  address text DEFAULT NULL,
  city varchar(100) DEFAULT NULL,
  created_at datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table customers
--

INSERT INTO customers (customer_id, name, phone_no, email, address, city, created_at) VALUES
(1, 'ayushmaan sahu', '8458888458', NULL, 'mr3 near suncity', 'indore', '2025-06-10 17:27:22'),
(2, 'hello', '1234567890', NULL, 'mr3 near suncity', 'indore', '2025-06-10 17:30:13'),
(3, 'ayushmaan sahu', '1234567898', NULL, 'mr3 near suncity', 'indore', '2025-06-10 17:47:31'),
(4, 'Agrima shrivastava', '9644704487', NULL, 'indore', 'indore', '2025-06-10 17:54:53'),
(5, 'anushka', '9685745874', NULL, 'indore', 'indore', '2025-06-10 17:56:50'),
(6, 'anushka', '7745986589', NULL, 'indore', 'indore', '2025-06-10 18:14:37'),
(7, 'manku', '5165151515', NULL, 'mr3 near suncity', 'indore', '2025-06-10 18:19:27'),
(8, 'fsssffs', '1234567777', NULL, 'mr3 near suncity', 'indore', '2025-06-10 18:32:18'),
(9, 'jjjjjj', '8458888457', NULL, 'mr3 near suncity', 'indore', '2025-06-10 18:36:16'),
(10, 'Agrima shrivastava', '0964470448', NULL, 'indore', 'indore', '2025-06-10 18:50:51'),
(11, 'sadfgnhjk,jhgf', '9865246895', NULL, 'indore', 'indore', '2025-06-10 18:58:16'),
(12, 'aqsdefgtyuik', '8635685235', NULL, 'thgjk', 'jguk', '2025-06-10 19:01:03'),
(13, 'pppppppppppp', '1234577700', NULL, 'mr3 near suncity', 'indore', '2025-06-12 17:04:00');

-- --------------------------------------------------------

--
-- Table structure for table delivery_items
--

CREATE TABLE delivery_items (
  id int(11) NOT NULL,
  delivery_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  qty_ordered int(11) NOT NULL,
  qty_delivered int(11) NOT NULL DEFAULT 0,
  qty_returned int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table delivery_items
--

INSERT INTO delivery_items (id, delivery_id, product_id, qty_ordered, qty_delivered, qty_returned) VALUES
(1, 1, 3, 1, 0, 0),
(2, 1, 3, 1, 0, 0),
(3, 2, 1, 2, 0, 0),
(4, 2, 3, 3, 0, 0),
(5, 3, 1, 3, 0, 0),
(6, 3, 1, 5, 0, 0),
(7, 4, 1, 5, 0, 0),
(8, 5, 1, 2, 2, 0),
(9, 6, 1, 2, 0, 0),
(10, 7, 1, 2, 0, 0),
(11, 8, 1, 4, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table delivery_orders
--

CREATE TABLE delivery_orders (
  delivery_id int(11) NOT NULL,
  order_id int(11) NOT NULL,
  delivery_user_id int(11) DEFAULT NULL,
  rent decimal(10,2) DEFAULT 0.00,
  amount_paid decimal(10,2) DEFAULT 0.00,
  amount_remaining decimal(10,2) DEFAULT 0.00,
  status enum('Assigned','Partially Paid','Completed') DEFAULT 'Assigned',
  assigned_at datetime DEFAULT current_timestamp(),
  delivered_at datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table delivery_orders
--

INSERT INTO delivery_orders (delivery_id, order_id, delivery_user_id, rent, amount_paid, amount_remaining, status, assigned_at, delivered_at) VALUES
(1, 5, 2, 0.00, 0.00, 444.00, 'Assigned', '2025-06-10 17:51:36', NULL),
(2, 7, 2, 0.00, 0.00, 2244.00, 'Assigned', '2025-06-10 18:04:57', NULL),
(3, 8, 2, 0.00, 0.00, 6312.00, 'Assigned', '2025-06-10 18:05:06', NULL),
(4, 9, 2, 0.00, 3945.00, 0.00, 'Completed', '2025-06-10 18:15:05', NULL),
(5, 10, 2, 0.00, 3256.00, 0.00, 'Completed', '2025-06-10 18:27:01', NULL),
(6, 11, 2, 0.00, 1578.00, 0.00, 'Completed', '2025-06-10 18:42:18', NULL),
(7, 16, 2, 0.00, 21578.00, 0.00, 'Completed', '2025-06-12 17:04:50', NULL),
(8, 15, 2, 0.00, 3156.00, 0.00, 'Completed', '2025-06-12 17:47:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table delivery_payments
--

CREATE TABLE delivery_payments (
  id int(11) NOT NULL,
  delivery_id int(11) NOT NULL,
  payment_date datetime DEFAULT current_timestamp(),
  amount_paid decimal(10,2) NOT NULL,
  collected_by int(11) DEFAULT NULL,
  remarks varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table delivery_payments
--

INSERT INTO delivery_payments (id, delivery_id, payment_date, amount_paid, collected_by, remarks) VALUES
(1, 5, '2025-06-10 18:27:45', 1500.00, NULL, ''),
(2, 5, '2025-06-10 18:28:13', 1500.00, NULL, ''),
(3, 5, '2025-06-10 18:42:37', 156.00, NULL, ''),
(4, 5, '2025-06-10 18:42:49', 100.00, NULL, ''),
(5, 4, '2025-06-10 18:44:59', 3945.00, NULL, ''),
(6, 6, '2025-06-10 18:45:26', 1578.00, NULL, ''),
(7, 7, '2025-06-12 17:06:27', 578.00, NULL, ''),
(8, 7, '2025-06-12 17:06:43', 1000.00, NULL, ''),
(9, 7, '2025-06-12 17:07:03', 20000.00, NULL, ''),
(10, 8, '2025-06-12 17:48:03', 3156.00, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table minus_stock
--

CREATE TABLE minus_stock (
  id int(11) NOT NULL,
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  storage_area_id int(11) NOT NULL,
  quantity_subtracted int(11) NOT NULL,
  subtracted_by int(11) NOT NULL,
  subtracted_at datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table minus_stock
--

INSERT INTO minus_stock (id, order_id, product_id, storage_area_id, quantity_subtracted, subtracted_by, subtracted_at) VALUES
(21, 3, 1, 1, 1, 1, '2025-06-10 17:44:28'),
(22, 3, 3, 1, 1, 1, '2025-06-10 17:44:28'),
(23, 4, 1, 1, 1, 1, '2025-06-10 17:46:13'),
(46, 9, 1, 1, 5, 1, '2025-06-10 18:31:33'),
(47, 11, 1, 1, 1, 1, '2025-06-10 18:32:38'),
(48, 11, 1, 1, 1, 1, '2025-06-10 18:32:57'),
(49, 12, 4, 1, 1, 1, '2025-06-10 18:36:31'),
(50, 12, 1, 1, 2, 1, '2025-06-10 18:36:35'),
(53, 13, 1, 1, 2, 1, '2025-06-10 18:51:30'),
(54, 13, 1, 1, 2, 1, '2025-06-10 18:51:35'),
(55, 14, 1, 1, 2, 1, '2025-06-10 18:58:25'),
(56, 14, 1, 1, 1, 1, '2025-06-10 18:58:28'),
(57, 15, 1, 1, 2, 1, '2025-06-10 19:01:13'),
(58, 15, 1, 1, 2, 1, '2025-06-10 19:01:16'),
(59, 16, 1, 1, 1, 1, '2025-06-12 17:04:28'),
(60, 16, 1, 1, 1, 1, '2025-06-12 17:04:37');

-- --------------------------------------------------------

--
-- Table structure for table orders
--

CREATE TABLE orders (
  order_id int(11) NOT NULL,
  order_date datetime DEFAULT current_timestamp(),
  customer_id int(11) DEFAULT NULL,
  total_amount decimal(10,2) NOT NULL,
  final_amount DECIMAL(10,2) DEFAULT NULL,
  discounted_amount decimal(10,2) DEFAULT NULL,
  transport_rent decimal(10,2) DEFAULT 0.00,
  stock_done tinyint(1) DEFAULT 0,
  deleted tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table orders
--

INSERT INTO orders (order_id, order_date, customer_id, total_amount, discounted_amount, transport_rent, stock_done, deleted) VALUES
(1, '2025-06-10 17:27:22', 1, 1578.00, NULL, 0.00, 0, 0),
(2, '2025-06-10 17:30:13', 2, 444.00, NULL, 0.00, 0, 0),
(3, '2025-06-10 17:31:06', 1, 1011.00, NULL, 0.00, 1, 0),
(4, '2025-06-10 17:45:13', 1, 1578.00, NULL, 0.00, 1, 0),
(5, '2025-06-10 17:47:31', 3, 444.00, NULL, 0.00, 0, 0),
(6, '2025-06-10 17:52:25', 3, 1578.00, NULL, 0.00, 0, 0),
(7, '2025-06-10 17:54:53', 4, 2244.00, NULL, 0.00, 0, 0),
(8, '2025-06-10 17:56:50', 5, 6312.00, NULL, 0.00, 0, 0),
(9, '2025-06-10 18:14:37', 6, 3945.00, NULL, 0.00, 1, 0),
(10, '2025-06-10 18:19:27', 7, 1578.00, NULL, 0.00, 0, 0),
(11, '2025-06-10 18:32:18', 8, 1578.00, NULL, 0.00, 0, 0),
(12, '2025-06-10 18:36:16', 9, 1689.00, NULL, 0.00, 1, 0),
(13, '2025-06-10 18:50:51', 10, 3156.00, NULL, 0.00, 0, 0),
(14, '2025-06-10 18:58:16', 11, 2367.00, NULL, 0.00, 0, 0),
(15, '2025-06-10 19:01:03', 12, 3156.00, NULL, 0.00, 0, 0),
(16, '2025-06-12 17:04:00', 13, 1578.00, NULL, 0.00, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table pending_orders
--

CREATE TABLE pending_orders (
  id int(11) NOT NULL,
  order_id int(11) DEFAULT NULL,
  customer_id int(11) DEFAULT NULL,
  product_id int(11) DEFAULT NULL,
  product_name varchar(255) NOT NULL,
  quantity int(11) NOT NULL,
  original_price decimal(10,2) NOT NULL,
  custom_price decimal(10,2) DEFAULT NULL,
  approved tinyint(1) DEFAULT 0,
  stock_subtracted tinyint(1) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  multiplier int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table pending_orders
--

INSERT INTO pending_orders (id, order_id, customer_id, product_id, product_name, quantity, original_price, custom_price, approved, stock_subtracted, created_at, multiplier) VALUES
(17, 9, 6, 1, 'laptop', 5, 789.00, 7890.00, 1, 0, '2025-06-10 18:14:37', 1),
(19, 10, 7, 1, 'laptop', 2, 789.00, 3156.00, 1, 0, '2025-06-10 18:19:27', 1),
(21, 11, 8, 1, 'laptop', 2, 789.00, 3156.00, 1, 0, '2025-06-10 18:32:18', 1),
(23, 12, 9, 1, 'laptop', 2, 789.00, 3156.00, 1, 0, '2025-06-10 18:36:16', 1),
(25, 12, 9, 4, 'mobile', 1, 111.00, 111.00, 1, 0, '2025-06-10 18:36:16', 1),
(26, 13, 10, 1, 'laptop', 4, 789.00, 3156.00, 1, 0, '2025-06-10 18:50:51', 1),
(27, 14, 11, 1, 'laptop', 3, 789.00, 2367.00, 1, 0, '2025-06-10 18:58:16', 1),
(28, 15, 12, 1, 'laptop', 4, 789.00, 3156.00, 1, 0, '2025-06-10 19:01:03', 1),
(29, 16, 13, 1, 'laptop', 2, 789.00, 3156.00, 1, 0, '2025-06-12 17:04:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table products
--

CREATE TABLE products (
  product_id int(11) NOT NULL,
  product_name varchar(255) NOT NULL,
  description text DEFAULT NULL,
  area varchar(50) NOT NULL,
  category_id int(11) DEFAULT NULL,
  supplier_id int(11) DEFAULT NULL,
  price decimal(10,2) NOT NULL,
  cost_price decimal(10,2) DEFAULT NULL,
  product_image varchar(255) DEFAULT '../uploads/675e9296d42d0.png',
  status enum('Active','Inactive','Discontinued') DEFAULT 'Active',
  date_added datetime DEFAULT current_timestamp(),
  last_updated datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table products
--

INSERT INTO products (product_id, product_name, description, area, category_id, supplier_id, price, cost_price, product_image, status, date_added, last_updated) VALUES
(1, 'laptop', 'yyyyy', '78', 1, 1, 789.00, 1000.00, '../uploads/6848690286662.png', 'Active', '2025-06-10 17:18:58', '2025-06-21 08:13:12'),
(3, 'mug', 'qaa', '', 1, 1, 222.00, 22.00, '../uploads/6848693122f61.png', 'Active', '2025-06-10 17:19:45', '2025-06-10 17:19:45'),
(4, 'mobile', 'aaaa', '', 1, 1, 111.00, 222.00, '../uploads/684879dcc00c2.png', 'Active', '2025-06-10 18:30:52', '2025-06-10 18:30:52'),
(5, 'Hello', '1', '', 2, 2, 100.00, 200.00, '../uploads/default_img.png', 'Active', '2025-06-21 07:59:27', '2025-06-21 07:59:27');

-- --------------------------------------------------------

--
-- Table structure for table product_stock
--

CREATE TABLE product_stock (
  stock_id int(11) NOT NULL,
  product_id int(11) DEFAULT NULL,
  storage_area_id int(11) DEFAULT NULL,
  pieces_per_packet int(11) NOT NULL,
  quantity int(11) NOT NULL,
  min_stock_level int(11) DEFAULT 5,
  last_updated datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table product_stock
--

INSERT INTO product_stock (stock_id, product_id, storage_area_id, pieces_per_packet, quantity, min_stock_level, last_updated) VALUES
(1, 1, 1, 5, 0, 7, '2025-06-21 08:16:57'),
(2, 3, 1, 2, 2, 7, '2025-06-10 17:44:28'),
(3, 4, 1, 2, 20, 7, '2025-06-21 08:08:36'),
(4, 5, 3, 2, 20, 10, '2025-06-21 07:59:27'),
(5, 5, 1, 2, 10, 5, '2025-06-21 08:08:36'),
(6, 1, 3, 5, 900, 5, '2025-06-21 08:16:57');

-- --------------------------------------------------------

--
-- Table structure for table recycle_bin_delivery_items
--

CREATE TABLE recycle_bin_delivery_items (
  id int(11) NOT NULL,
  delivery_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  qty_ordered int(11) NOT NULL,
  qty_delivered int(11) NOT NULL DEFAULT 0,
  qty_returned int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table recycle_bin_delivery_orders
--

CREATE TABLE recycle_bin_delivery_orders (
  delivery_id int(11) NOT NULL,
  order_id int(11) NOT NULL,
  delivery_user_id int(11) DEFAULT NULL,
  rent decimal(10,2) DEFAULT 0.00,
  amount_paid decimal(10,2) DEFAULT 0.00,
  amount_remaining decimal(10,2) DEFAULT 0.00,
  status enum('Assigned','Partially Paid','Completed') DEFAULT 'Assigned',
  assigned_at datetime DEFAULT current_timestamp(),
  delivered_at datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table recycle_bin_orders
--

CREATE TABLE recycle_bin_orders (
  order_id int(11) NOT NULL,
  order_date datetime DEFAULT current_timestamp(),
  customer_id int(11) DEFAULT NULL,
  total_amount decimal(10,2) NOT NULL,
  discounted_amount decimal(10,2) DEFAULT NULL,
  transport_rent decimal(10,2) DEFAULT 0.00,
  stock_done tinyint(1) DEFAULT 0,
  deleted tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table recycle_bin_pending_orders
--

CREATE TABLE recycle_bin_pending_orders (
  id int(11) NOT NULL,
  order_id int(11) DEFAULT NULL,
  customer_id int(11) DEFAULT NULL,
  product_id int(11) DEFAULT NULL,
  product_name varchar(255) NOT NULL,
  quantity int(11) NOT NULL,
  original_price decimal(10,2) NOT NULL,
  custom_price decimal(10,2) DEFAULT NULL,
  approved tinyint(1) DEFAULT 0,
  stock_subtracted tinyint(1) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  multiplier int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table rider_income
--

CREATE TABLE rider_income (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  delivery_id int(11) DEFAULT NULL,
  payment_date datetime DEFAULT current_timestamp(),
  amount decimal(10,2) NOT NULL,
  remarks varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table rider_income
--

INSERT INTO rider_income (id, user_id, delivery_id, payment_date, amount, remarks) VALUES
(1, 2, 3, '2025-06-12 17:46:35', 312.00, ''),
(2, 2, 3, '2025-06-12 17:46:56', 312.00, ''),
(3, 2, 3, '2025-06-12 17:47:27', 5500.00, ''),
(4, 2, 3, '2025-06-12 17:47:38', 200.00, '');

-- --------------------------------------------------------

--
-- Table structure for table roles
--

CREATE TABLE roles (
  role_id int(11) NOT NULL,
  role_name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table roles
--

INSERT INTO roles (role_id, role_name) VALUES
(1, 'Admin'),
(4, 'Delivery'),
(2, 'Manager'),
(3, 'Salesperson');

-- --------------------------------------------------------

--
-- Table structure for table storage_areas
--

CREATE TABLE storage_areas (
  storage_area_id int(11) NOT NULL,
  storage_area_name varchar(255) NOT NULL,
  location varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table storage_areas
--

INSERT INTO storage_areas (storage_area_id, storage_area_name, location) VALUES
(1, 'industry', 'indore'),
(2, 'new shop', 'indore'),
(3, 'shop', 'indore');

-- --------------------------------------------------------

--
-- Table structure for table suppliers
--

CREATE TABLE suppliers (
  supplier_id int(11) NOT NULL,
  supplier_name varchar(255) NOT NULL,
  supplier_details text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table suppliers
--

INSERT INTO suppliers (supplier_id, supplier_name, supplier_details) VALUES
(1, 'Rc Tile', 'ujjian'),
(2, 'sharma ', 'ujjian');

-- --------------------------------------------------------

--
-- Table structure for table transactions
--

CREATE TABLE transactions (
  transaction_id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  product_id int(11) DEFAULT NULL,
  storage_area_id int(11) DEFAULT NULL,
  transaction_type enum('Add','Subtract','Delete','Edit') DEFAULT NULL,
  quantity_changed int(11) DEFAULT NULL,
  transaction_date datetime DEFAULT NULL,
  description varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table transactions
--

INSERT INTO transactions (transaction_id, user_id, product_id, storage_area_id, transaction_type, quantity_changed, transaction_date, description) VALUES
(4, 1, 1, 1, 'Subtract', 15, '2025-06-10 18:29:03', 'Stock Minus'),
(5, 1, 1, 1, 'Add', 50, '2025-06-10 18:29:43', 'Stock added'),
(6, 1, 4, 1, NULL, 0, '2025-06-10 18:30:52', 'New product added \'mobile\''),
(7, 1, 1, 1, 'Add', 60, '2025-06-12 17:11:25', 'Stock added'),
(8, 1, 1, 1, 'Subtract', 500, '2025-06-12 17:11:39', 'Stock Minus'),
(9, 1, 1, NULL, 'Edit', 0, '2025-06-12 17:12:08', 'Product \'laptop\' edited by Ayushmaan Sahu'),
(10, 1, 5, 3, NULL, 0, '2025-06-21 07:59:27', 'New product added \'Hello\''),
(11, 1, 5, 1, 'Add', 20, '2025-06-21 08:05:54', 'Stock added'),
(12, 1, 1, 1, 'Subtract', 100, '2025-06-21 08:08:36', 'Stock Minus'),
(13, 1, 4, 1, 'Subtract', 22, '2025-06-21 08:08:36', 'Stock Minus'),
(14, 1, 5, 1, 'Subtract', 10, '2025-06-21 08:08:36', 'Stock Minus'),
(15, 1, 1, NULL, 'Edit', 0, '2025-06-21 08:13:12', 'Product \'laptop\' edited by Ayushmaan Sahu'),
(16, 1, 1, 1, 'Subtract', 900, '2025-06-21 08:16:57', 'Transferred 180 box and 0 piece(s) from industry t'),
(17, 1, 1, 3, 'Add', 900, '2025-06-21 08:16:57', 'Received 180 box and 0 piece(s) in shop from indus');

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
  user_id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  storage_area_id int(100) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  phone_no varchar(15) NOT NULL,
  password varchar(255) NOT NULL,
  aadhar_id_no varchar(12) DEFAULT NULL,
  role_id int(11) DEFAULT NULL,
  date_joined datetime DEFAULT current_timestamp(),
  last_login datetime DEFAULT NULL,
  user_image varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table users
--

INSERT INTO users (user_id, name, storage_area_id, email, phone_no, password, aadhar_id_no, role_id, date_joined, last_login, user_image) VALUES
(1, 'Ayushmaan Sahu', NULL, 'ayushmaanksahu@gmail.com', '9644704488', '$2y$10$1Wy7jj.GAqp/xmdGWZ7lGeQ5rE1/lMPV49CJ8O3DvqPbrcC5Sp0R2', '', 1, '2025-06-10 17:43:39', NULL, '1.png'),
(2, 'mayank', NULL, 'rajesh.udhwani@kukufm.com', '9301137188', '$2y$10$EpykUYUrue00gOm2XL6DJuYm9BivoI/SOtzdGpIzZPpSxQunLwxdC', '111', 4, '2025-06-10 17:51:18', NULL, '68487096a34cf_1.png');

-- --------------------------------------------------------

--
-- Table structure for table user_ledger
--

CREATE TABLE user_ledger (
  ledger_id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  transaction_date datetime DEFAULT current_timestamp(),
  transaction_type enum('Owed','Repaid','Custom Paid') NOT NULL,
  amount decimal(10,2) NOT NULL,
  balance decimal(10,2) NOT NULL,
  remarks text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table admin_cash_collection
--
ALTER TABLE admin_cash_collection
  ADD PRIMARY KEY (collection_id),
  ADD KEY idx_user (user_id);

--
-- Indexes for table category
--
ALTER TABLE category
  ADD PRIMARY KEY (category_id),
  ADD UNIQUE KEY category_name (category_name),
  ADD KEY area (area);

--
-- Indexes for table customers
--
ALTER TABLE customers
  ADD PRIMARY KEY (customer_id),
  ADD UNIQUE KEY phone_no (phone_no);

--
-- Indexes for table delivery_items
--
ALTER TABLE delivery_items
  ADD PRIMARY KEY (id),
  ADD KEY fk_del_items_del (delivery_id),
  ADD KEY fk_del_items_prod (product_id);

--
-- Indexes for table delivery_orders
--
ALTER TABLE delivery_orders
  ADD PRIMARY KEY (delivery_id),
  ADD KEY order_id (order_id),
  ADD KEY delivery_user_id (delivery_user_id);

--
-- Indexes for table delivery_payments
--
ALTER TABLE delivery_payments
  ADD PRIMARY KEY (id),
  ADD KEY fk_payments_del (delivery_id);

--
-- Indexes for table minus_stock
--
ALTER TABLE minus_stock
  ADD PRIMARY KEY (id),
  ADD KEY order_id (order_id),
  ADD KEY product_id (product_id),
  ADD KEY storage_area_id (storage_area_id),
  ADD KEY subtracted_by (subtracted_by);

--
-- Indexes for table orders
--
ALTER TABLE orders
  ADD PRIMARY KEY (order_id),
  ADD KEY customer_id (customer_id);

--
-- Indexes for table pending_orders
--
ALTER TABLE pending_orders
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY uniq_order_product (order_id,product_id),
  ADD KEY fk_pending_order (order_id),
  ADD KEY fk_pending_customer (customer_id),
  ADD KEY fk_pending_product (product_id);

--
-- Indexes for table products
--
ALTER TABLE products
  ADD PRIMARY KEY (product_id),
  ADD UNIQUE KEY product_name (product_name),
  ADD KEY fk_category (category_id),
  ADD KEY products_ibfk_1 (supplier_id);

--
-- Indexes for table product_stock
--
ALTER TABLE product_stock
  ADD PRIMARY KEY (stock_id),
  ADD KEY product_id (product_id),
  ADD KEY storage_area_id (storage_area_id);

--
-- Indexes for table recycle_bin_delivery_items
--
ALTER TABLE recycle_bin_delivery_items
  ADD PRIMARY KEY (id),
  ADD KEY fk_del_items_del (delivery_id),
  ADD KEY fk_del_items_prod (product_id);

--
-- Indexes for table recycle_bin_delivery_orders
--
ALTER TABLE recycle_bin_delivery_orders
  ADD PRIMARY KEY (delivery_id),
  ADD KEY order_id (order_id),
  ADD KEY delivery_user_id (delivery_user_id);

--
-- Indexes for table recycle_bin_orders
--
ALTER TABLE recycle_bin_orders
  ADD PRIMARY KEY (order_id),
  ADD KEY customer_id (customer_id);

--
-- Indexes for table recycle_bin_pending_orders
--
ALTER TABLE recycle_bin_pending_orders
  ADD PRIMARY KEY (id),
  ADD KEY fk_pending_order (order_id),
  ADD KEY fk_pending_customer (customer_id),
  ADD KEY fk_pending_product (product_id);

--
-- Indexes for table rider_income
--
ALTER TABLE rider_income
  ADD PRIMARY KEY (id),
  ADD KEY user_id (user_id),
  ADD KEY delivery_id (delivery_id);

--
-- Indexes for table roles
--
ALTER TABLE roles
  ADD PRIMARY KEY (role_id),
  ADD UNIQUE KEY role_name (role_name);

--
-- Indexes for table storage_areas
--
ALTER TABLE storage_areas
  ADD PRIMARY KEY (storage_area_id);

--
-- Indexes for table suppliers
--
ALTER TABLE suppliers
  ADD PRIMARY KEY (supplier_id);

--
-- Indexes for table transactions
--
ALTER TABLE transactions
  ADD PRIMARY KEY (transaction_id),
  ADD KEY user_id (user_id),
  ADD KEY product_id (product_id),
  ADD KEY storage_area_id (storage_area_id);

--
-- Indexes for table users
--
ALTER TABLE users
  ADD PRIMARY KEY (user_id),
  ADD UNIQUE KEY phone_no (phone_no),
  ADD KEY role_id (role_id);
ALTER TABLE users ADD FULLTEXT KEY email (email);
ALTER TABLE users ADD FULLTEXT KEY aadhar_id_no (aadhar_id_no);

--
-- Indexes for table user_ledger
--
ALTER TABLE user_ledger
  ADD PRIMARY KEY (ledger_id),
  ADD KEY user_id (user_id);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table admin_cash_collection
--
ALTER TABLE admin_cash_collection
  MODIFY collection_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table category
--
ALTER TABLE category
  MODIFY category_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table customers
--
ALTER TABLE customers
  MODIFY customer_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table delivery_items
--
ALTER TABLE delivery_items
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table delivery_orders
--
ALTER TABLE delivery_orders
  MODIFY delivery_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table delivery_payments
--
ALTER TABLE delivery_payments
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table minus_stock
--
ALTER TABLE minus_stock
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table orders
--
ALTER TABLE orders
  MODIFY order_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table pending_orders
--
ALTER TABLE pending_orders
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table products
--
ALTER TABLE products
  MODIFY product_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table product_stock
--
ALTER TABLE product_stock
  MODIFY stock_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table recycle_bin_delivery_items
--
ALTER TABLE recycle_bin_delivery_items
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table recycle_bin_delivery_orders
--
ALTER TABLE recycle_bin_delivery_orders
  MODIFY delivery_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table recycle_bin_orders
--
ALTER TABLE recycle_bin_orders
  MODIFY order_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table recycle_bin_pending_orders
--
ALTER TABLE recycle_bin_pending_orders
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table rider_income
--
ALTER TABLE rider_income
  MODIFY id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table roles
--
ALTER TABLE roles
  MODIFY role_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table storage_areas
--
ALTER TABLE storage_areas
  MODIFY storage_area_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table suppliers
--
ALTER TABLE suppliers
  MODIFY supplier_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table transactions
--
ALTER TABLE transactions
  MODIFY transaction_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table users
--
ALTER TABLE users
  MODIFY user_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table user_ledger
--
ALTER TABLE user_ledger
  MODIFY ledger_id int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table delivery_items
--
ALTER TABLE delivery_items
  ADD CONSTRAINT fk_del_items_del FOREIGN KEY (delivery_id) REFERENCES delivery_orders (delivery_id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_del_items_prod FOREIGN KEY (product_id) REFERENCES products (product_id);

--
-- Constraints for table delivery_orders
--
ALTER TABLE delivery_orders
  ADD CONSTRAINT fk_delivery_order FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
  ADD CONSTRAINT fk_delivery_user FOREIGN KEY (delivery_user_id) REFERENCES users (user_id) ON DELETE SET NULL;

--
-- Constraints for table delivery_payments
--
ALTER TABLE delivery_payments
  ADD CONSTRAINT fk_payments_del FOREIGN KEY (delivery_id) REFERENCES delivery_orders (delivery_id) ON DELETE CASCADE;

--
-- Constraints for table minus_stock
--
ALTER TABLE minus_stock
  ADD CONSTRAINT minus_stock_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
  ADD CONSTRAINT minus_stock_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (product_id) ON DELETE CASCADE,
  ADD CONSTRAINT minus_stock_ibfk_3 FOREIGN KEY (storage_area_id) REFERENCES storage_areas (storage_area_id) ON DELETE CASCADE,
  ADD CONSTRAINT minus_stock_ibfk_4 FOREIGN KEY (subtracted_by) REFERENCES users (user_id) ON DELETE CASCADE;

--
-- Constraints for table orders
--
ALTER TABLE orders
  ADD CONSTRAINT orders_ibfk_1 FOREIGN KEY (customer_id) REFERENCES customers (customer_id) ON DELETE CASCADE;

--
-- Constraints for table products
--
ALTER TABLE products
  ADD CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES category (category_id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT products_ibfk_1 FOREIGN KEY (supplier_id) REFERENCES suppliers (supplier_id) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table product_stock
--
ALTER TABLE product_stock
  ADD CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products (product_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_storage_area FOREIGN KEY (storage_area_id) REFERENCES storage_areas (storage_area_id) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table rider_income
--
ALTER TABLE rider_income
  ADD CONSTRAINT rider_income_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
  ADD CONSTRAINT rider_income_ibfk_2 FOREIGN KEY (delivery_id) REFERENCES delivery_orders (delivery_id) ON DELETE SET NULL;

--
-- Constraints for table transactions
--
ALTER TABLE transactions
  ADD CONSTRAINT transactions_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
  ADD CONSTRAINT transactions_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (product_id),
  ADD CONSTRAINT transactions_ibfk_3 FOREIGN KEY (storage_area_id) REFERENCES storage_areas (storage_area_id);

--
-- Constraints for table users
--
ALTER TABLE users
  ADD CONSTRAINT users_ibfk_1 FOREIGN KEY (role_id) REFERENCES roles (role_id);

--
-- Constraints for table user_ledger
--
ALTER TABLE user_ledger
  ADD CONSTRAINT user_ledger_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;