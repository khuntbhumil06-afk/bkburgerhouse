-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2026 at 05:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(10, 'BHUMIL KHUNT', 'bhumil23@gmail.com', 'bhumil23', '$2y$10$ZNNsvDITGA0veYUp8QAbp.mQ7KjhZiXNDFtlHbtUuNNtU/CKoblpC', '2026-02-23 21:24:04', '1771881844.jpg', 'admin');

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
  `order_time` datetime NOT NULL DEFAULT current_timestamp(),
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `fullname`, `username`, `order_id`, `item`, `quantity`, `email`, `address`, `details`, `order_time`, `price`) VALUES
(1, 'bhumil khunt', 'khunt bhumil', '19022026101657', 'Chocolate Shake, Coke, Veg Wrap, Veg Burger, ', '5, 10, 3, 7, ', 'bhumilkhunt8@gmail.com', 'surat', 'Delivered', '2026-02-19 14:46:57', 2365),
(2, 'bhumil khunt', 'bhumilkhunt1', '19022026102244', 'Coke, Veg Combo, Veg Wrap, ', '10, 8, 7, ', 'bhumilkhunt8@gmail.com', 'mota varachha', 'Delivered', '2026-02-19 14:52:44', 3564),
(3, 'B K', 'bhumilkhunt1', '19022026102730', 'Coke, Veg Burger, ', '5, 2, ', 'khuntbhumil06@gmail.com', 'simada', 'Delivered', '2026-02-19 14:57:30', 583),
(4, 'Bhumil Khunt', 'bhumilkhunt1', '19022026102850', 'Chocolate Shake, Coke, Veg Combo, Chicken Burger, ', '7, 11, 9, 8, 6, ', 'bhumilkhunt8@gmail.com', 'mota varachha', 'Delivered', '2026-02-19 14:58:50', 5983),
(5, 'meet', 'b k', '19022026105722', 'Chocolate Shake, Coke, Veg Combo, Veg Wrap, Veg Bu', '10, 10, 4, 2, 10, ', 'khuntbhumil07@gmail.com', 'vapi', 'Delivered', '2026-02-19 15:27:22', 4212),
(6, 'Meet Patel', 'b k', '23022026061725', 'Chicken Burger, Veg Burger, ', '2, 12, ', 'meet12@gmail.com', 'vapi', 'Delivered', '2026-02-23 10:47:25', 1901),
(7, 'Meet', 'b k', '23022026083311', 'Chocolate Shake, Coke, Non-Veg Combo, Veg Combo, C', '12, 12, 4, 2, 4, 5, 2, 10, ', 'meet12@gmail.com', 'mumbai', 'Delivered', '2026-02-23 13:03:11', 6761),
(8, 'B K', 'b k', '23022026093706', 'Veg Wrap, Veg Burger, ', '2, 12, ', 'bhumilkhunt8@gmail.com', 'vapi', 'Delivered', '2026-02-23 14:07:06', 1616),
(9, 'B K', 'b k', '27022026010239', 'CheeseVegBurger, ', '4, ', 'bhumilkhunt8@gmail.com', 'vapi', 'Delivered', '2026-02-27 17:32:39', 408),
(10, 'B K', 'b k', '27022026011013', 'CheeseVegBurger, ', '2, ', 'bhumilkhunt8@gmail.com', 'vapi', 'Delivered', '2026-02-27 17:40:13', 204);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
