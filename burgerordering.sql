-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 03:05 PM
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
-- Database: `burgerordering`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'B K', 'bhumilkhunt8@gmail.com', 'cburger', 'good', '2026-02-23 09:22:15'),
(2, 'BHUMIL KHUNT', 'bhumilkhunt8@gmail.com', 'order', 'good', '2026-02-23 09:23:06');

-- --------------------------------------------------------

--
-- Table structure for table `loginform`
--

CREATE TABLE `loginform` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loginform`
--

INSERT INTO `loginform` (`id`, `fullname`, `email`, `username`, `password`, `created_at`, `image`, `role`) VALUES
(1, 'bhumil khunt', 'bhumilkhunt8@gmail.com', 'b k', '$2y$10$2vOC6hyjAAE3yY1ebLw64O4asWDHLCPAXW4ZogSulvF5Q3cuT9DrC', '2026-02-19 05:08:33', NULL, 'user'),
(2, 'admin', 'admin@gmail.com', 'admin', '$2y$10$ZucqFnsLrm7rbXHNW8wgPOmRVQJuoj5b7xL.pEMnNOTrGrOkDqMtS', '2026-02-19 05:18:17', NULL, 'user'),
(3, 'B K', 'khuntbhumil06@gmail.com', 'khunt bhumil', '$2y$10$XxIX3gYPiqbA3xrkwGFGgejqN4zayHQe6jiWYeT0r3IWjy1ni7CMu', '2026-02-19 09:12:10', NULL, 'user'),
(7, 'bhumil khunt', 'bhumilkhunt8@gmail.com', 'bhumilkhunt1', '$2y$10$irpj9vt9VcyapnBsQh4ireGNsaNvt11We/cJeIqczUQIdXU31pS3m', '2026-02-19 09:21:35', NULL, 'user'),
(10, 'BHUMIL KHUNT', 'bhumil23@gmail.com', 'bhumil23', '$2a$12$86xRkL0Pb1XNV41YRjl4COYCOPQ6T..VgkwbT.5vLnE9Cm1Z7DnTi', '2026-02-23 21:24:04', '1771881844.jpg', 'admin'),
(11, 'Bhumil Kuunt', 'bhumilkhunt07@gmail.com', 'BK', '$2y$10$.DVkFSw.1B9ejMtnAwaYb.f0ExrCamdTvPdbhITX1FCbKiw0UWAfu', '2026-08-24 07:26:57', '1787556417_4520.png', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `fullname` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `item` varchar(50) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `details` varchar(50) NOT NULL DEFAULT 'Confirmed',
  `payment_method` varchar(20) NOT NULL DEFAULT 'cod',
  `status_time` datetime NOT NULL DEFAULT current_timestamp(),
  `order_time` datetime NOT NULL DEFAULT current_timestamp(),
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `fullname`, `username`, `order_id`, `item`, `quantity`, `email`, `address`, `details`, `payment_method`, `status_time`, `order_time`, `price`) VALUES
(1, 'bhumil khunt', 'khunt bhumil', '19022026101657', 'Chocolate Shake, Coke, Veg Wrap, Veg Burger, ', '5, 10, 3, 7, ', 'bhumilkhunt8@gmail.com', 'surat', 'Delivered', 'cod', '2026-02-19 14:46:57', '2026-02-19 14:46:57', 2365),
(2, 'bhumil khunt', 'bhumilkhunt1', '19022026102244', 'Coke, Veg Combo, Veg Wrap, ', '10, 8, 7, ', 'bhumilkhunt8@gmail.com', 'mota varachha', 'Delivered', 'cod', '2026-02-19 14:52:44', '2026-02-19 14:52:44', 3564),
(3, 'B K', 'bhumilkhunt1', '19022026102730', 'Coke, Veg Burger, ', '5, 2, ', 'khuntbhumil06@gmail.com', 'simada', 'Delivered', 'cod', '2026-02-19 14:57:30', '2026-02-19 14:57:30', 583),
(4, 'Bhumil Khunt', 'bhumilkhunt1', '19022026102850', 'Chocolate Shake, Coke, Veg Combo, Chicken Burger, ', '7, 11, 9, 8, 6, ', 'bhumilkhunt8@gmail.com', 'mota varachha', 'Delivered', 'cod', '2026-02-19 14:58:50', '2026-02-19 14:58:50', 5983),
(5, 'meet', 'b k', '19022026105722', 'Chocolate Shake, Coke, Veg Combo, Veg Wrap, Veg Bu', '10, 10, 4, 2, 10, ', 'khuntbhumil07@gmail.com', 'vapi', 'Delivered', 'cod', '2026-02-19 15:27:22', '2026-02-19 15:27:22', 4212),
(6, 'Meet Patel', 'b k', '23022026061725', 'Chicken Burger, Veg Burger, ', '2, 12, ', 'meet12@gmail.com', 'vapi', 'Delivered', 'cod', '2026-02-23 10:47:25', '2026-02-23 10:47:25', 1901),
(7, 'Meet', 'b k', '23022026083311', 'Chocolate Shake, Coke, Non-Veg Combo, Veg Combo, C', '12, 12, 4, 2, 4, 5, 2, 10, ', 'meet12@gmail.com', 'mumbai', 'Delivered', 'cod', '2026-02-23 13:03:11', '2026-02-23 13:03:11', 6761),
(8, 'B K', 'b k', '23022026093706', 'Veg Wrap, Veg Burger, ', '2, 12, ', 'bhumilkhunt8@gmail.com', 'vapi', 'Delivered', 'cod', '2026-02-23 14:07:06', '2026-02-23 14:07:06', 1616),
(9, 'B K', 'b k', '27022026010239', 'CheeseVegBurger, ', '4, ', 'bhumilkhunt8@gmail.com', 'vapi', 'Delivered', 'cod', '2026-02-27 17:32:39', '2026-02-27 17:32:39', 408),
(10, 'B K', 'b k', '27022026011013', 'CheeseVegBurger, ', '2, ', 'bhumilkhunt8@gmail.com', 'vapi', 'Delivered', 'cod', '2026-02-27 17:40:13', '2026-02-27 17:40:13', 204),
(11, 'B K', 'BK', '12102026145948', 'Burger', '2', 'khuntbhumil06@gmail.com', 'surat', 'Confirmed', 'upi', '2026-10-12 14:59:48', '2026-10-12 18:29:48', 480),
(12, 'B K', 'BK', '12102026145951', 'Burger', '2', 'khuntbhumil06@gmail.com', 'surat', 'Confirmed', 'upi', '2026-10-12 14:59:51', '2026-10-12 18:29:51', 480),
(13, 'B K', 'BK', '12102026150047', 'Burger', '2', 'khuntbhumil06@gmail.com', 'surat', 'Confirmed', 'upi', '2026-10-12 15:00:47', '2026-10-12 18:30:47', 480);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(8,2) NOT NULL DEFAULT 0.00,
  `category` varchar(20) NOT NULL DEFAULT 'Drink'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `category`) VALUES
(1, 'AlooTikkiBurger', 150.00, 'Veg Burger'),
(2, 'CheeseVegBurger', 200.00, 'Veg Burger'),
(3, 'ClassicVegBurger', 50.00, 'Veg Burger'),
(4, 'CrispyVegBurger', 150.00, 'Veg Burger'),
(5, 'DoublePattyVegBurger', 80.00, 'Veg Burger'),
(6, 'GreenGardenBurger', 130.00, 'Veg Burger'),
(7, 'JainVegBurger', 160.00, 'Veg Burger'),
(8, 'MexicanVegBurger', 180.00, 'Veg Burger'),
(9, 'MushroomVegBurger', 200.00, 'Veg Burger'),
(10, 'PaneerTikkaBurger', 210.00, 'Veg Burger'),
(11, 'SpicyVegburger', 120.00, 'Veg Burger'),
(12, 'SpicyVegDelight', 220.00, 'Veg Burger'),
(13, 'VegCrunchBurger', 170.00, 'Veg Burger'),
(14, 'VegMaharajaBurger', 140.00, 'Veg Burger'),
(15, 'VegSupremeBurger', 200.00, 'Veg Burger'),
(16, 'BBQChickenBurger', 160.00, 'Non-Veg Burger'),
(17, 'ChickenCheeseBurger', 140.00, 'Non-Veg Burger'),
(18, 'ChickenCrunchBurger', 150.00, 'Non-Veg Burger'),
(19, 'ChickenDeluxeBurger', 130.00, 'Non-Veg Burger'),
(20, 'ChickenMaharajaBurger', 200.00, 'Non-Veg Burger'),
(21, 'ChickenPattyBurger', 220.00, 'Non-Veg Burger'),
(22, 'ChickenSupremeBurger', 230.00, 'Non-Veg Burger'),
(23, 'ChickenTandooriBurger', 150.00, 'Non-Veg Burger'),
(24, 'ClassicChickenBurger', 120.00, 'Non-Veg Burger'),
(25, 'CrispyChickenBurger', 180.00, 'Non-Veg Burger'),
(26, 'DoubleChickenBurger', 250.00, 'Non-Veg Burger'),
(27, 'EggChickenBurger', 260.00, 'Non-Veg Burger'),
(28, 'GrilledChickenBurger', 280.00, 'Non-Veg Burger'),
(29, 'PeriPeriChickenBurger', 300.00, 'Non-Veg Burger'),
(30, 'SpicyChickenBurger', 320.00, 'Non-Veg Burger'),
(31, 'ChickenCheeseCombo', 250.00, 'Combo'),
(32, 'BBQChickenCombo', 270.00, 'Combo'),
(33, 'ChickenClassicCombo', 290.00, 'Combo'),
(34, 'ChickenSupremeCombo', 300.00, 'Combo'),
(35, 'CrispyChickenCombo', 310.00, 'Combo'),
(36, 'DoubleVegCombo', 190.00, 'Combo'),
(37, 'FamilyChickenCombo', 600.00, 'Combo'),
(38, 'FamilyVegCombo', 500.00, 'Combo'),
(39, 'MaharajaChickenCombo', 700.00, 'Combo'),
(40, 'MaharajaVegCombo', 650.00, 'Combo'),
(41, 'PaneerBurgerCombo', 300.00, 'Combo'),
(42, 'PeriPeriChickenCombo', 350.00, 'Combo'),
(43, 'VegCheeseCombo', 250.00, 'Combo'),
(44, 'VegClassicCombo', 200.00, 'Combo'),
(45, 'VegSupremeCombo', 150.00, 'Combo'),
(46, 'ChocolateShake', 100.00, 'Drink'),
(47, 'CocaCola', 50.00, 'Drink'),
(48, 'ColdCoffee', 120.00, 'Drink'),
(49, 'Fanta', 50.00, 'Drink'),
(50, 'LemonIcedTea', 70.00, 'Drink'),
(51, 'Limca', 60.00, 'Drink'),
(52, 'MangoShake', 140.00, 'Drink'),
(53, 'MineralWater', 30.00, 'Drink'),
(54, 'OreoShake', 150.00, 'Drink'),
(55, 'PeachIcedTea', 100.00, 'Drink'),
(56, 'Pepsi', 50.00, 'Drink'),
(57, 'Sprite', 50.00, 'Drink'),
(58, 'StrawberryShake', 160.00, 'Drink'),
(59, 'ThumsUp', 50.00, 'Drink'),
(60, 'VanillaShake', 150.00, 'Drink'),
(62, 'Burger', 250.00, 'Veg Burger');

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `id` int(11) NOT NULL,
  `total_orders` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `happy_customers` int(11) NOT NULL DEFAULT 0,
  `burgers_sold` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loginform`
--
ALTER TABLE `loginform`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loginform`
--
ALTER TABLE `loginform`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `stats`
--
ALTER TABLE `stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
