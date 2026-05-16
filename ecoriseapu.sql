-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2026 at 04:23 PM
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
-- Database: `ecoriseapu`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
(1, 'Environmental Cleanup', 'Activities focused on cleaning natural environments such as beaches, rivers, and parks'),
(2, 'Tree Planting', 'Programs aimed at planting trees and restoring green areas'),
(3, 'Recycling & Waste Management', 'Events promoting recycling, waste separation, and sustainable disposal'),
(4, 'Sustainability Awareness', 'Workshops and campaigns educating people about sustainable living'),
(5, 'Wildlife Conservation', 'Activities focused on protecting animals and preserving biodiversity'),
(6, 'Community Green Projects', 'Community initiatives such as urban gardening and green space development');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `max_participants` int(11) NOT NULL,
  `event_image` varchar(255) NOT NULL DEFAULT '../assets/uploads/default_event.avif',
  `trees_planted` int(11) DEFAULT NULL,
  `waste_collected` int(11) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','COMPLETED') NOT NULL DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `organizer_id`, `category_id`, `title`, `description`, `event_date`, `time_start`, `time_end`, `location`, `max_participants`, `event_image`, `trees_planted`, `waste_collected`, `status`) VALUES
(4, 28, 1, 'Community Plastic Drive', '0', '2026-04-15', '09:00:00', '13:00:00', 'Greenwood Community Center', 50, '../assets/uploads/default_event.avif', 251, NULL, 'COMPLETED'),
(8, 28, 1, 'Campus Plastic Clean-up Day', '0', '2026-04-15', '09:00:00', '13:00:00', 'APU Atrium', 50, '../assets/uploads/default_event.avif', NULL, NULL, 'APPROVED'),
(9, 28, 1, 'Tree Planting Drive', '0', '2026-05-10', '10:00:00', '16:00:00', 'Green Zone', 100, '../assets/uploads/default_event.avif', NULL, NULL, 'APPROVED'),
(10, 28, 1, 'Food Wastage Hackathon', '0', '2026-06-01', '08:00:00', '20:00:00', 'Room 303', 30, '../assets/uploads/default_event.avif', NULL, NULL, 'REJECTED');

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_joined` datetime NOT NULL DEFAULT current_timestamp(),
  `proof_image` varchar(255) DEFAULT NULL,
  `attendance_status` enum('PENDING','PRESENT','ABSENT','REGISTERED') NOT NULL DEFAULT 'REGISTERED',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_participants`
--

INSERT INTO `event_participants` (`event_id`, `user_id`, `date_joined`, `proof_image`, `attendance_status`, `description`) VALUES
(4, 27, '2026-03-11 14:10:41', '../assets/uploads/1773557145_edit.png', 'PRESENT', 'dfgdfgfgfgfgdfgfgfdgfdg'),
(4, 32, '2026-03-12 20:02:57', '../assets/uploads/1773316990_erd_rwdd.drawio.png', 'PRESENT', 'dsdcdcsqssdsqdsd'),
(10, 27, '2026-03-20 12:08:19', '../assets/uploads/1773979740_Screenshot 2026-03-16 225532.png', 'PRESENT', 'zcsdcsdcsdcddd'),
(4, 45, '2026-03-21 16:38:56', NULL, 'REGISTERED', NULL),
(9, 27, '2026-03-24 22:01:55', '../assets/uploads/1774361731_square-image (7).jpg', 'PENDING', 'acddsdsaddasdsadasddd'),
(8, 50, '2026-03-28 11:36:21', NULL, 'REGISTERED', NULL),
(9, 50, '2026-03-28 14:52:28', '../assets/uploads/1774680775_pexels-julia-m-cameron-6995380.jpg', 'PENDING', 'adfefreferfrefrfrfr');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '../assets/uploads/default_user_icon.webp',
  `role` enum('STUDENT','ORGANIZER','ADMIN') NOT NULL DEFAULT 'STUDENT',
  `total_volunteer_hours` decimal(8,2) NOT NULL DEFAULT 0.00,
  `current_points` int(11) NOT NULL DEFAULT 0,
  `account_status` enum('ACTIVE','BANNED') NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `avatar`, `role`, `total_volunteer_hours`, `current_points`, `account_status`) VALUES
(27, 'jack', 'a@gmail.com', '$2y$10$s52RqnTZ3qJC39L1A0w39OcMXise/v92Md74VWMr0DqQOO3q2OzAG', '../assets/uploads/default_user_icon.webp', 'STUDENT', 32.02, 2370, 'ACTIVE'),
(28, 'bb', 'b@gmail.com', '$2y$10$2VGrgRYAxcUGBiY/J5b8OO4ooT0nGRDBWpIINNI8svNPywJHEJhX6', '../assets/uploads/1773636640_istockphoto-178447404-612x612.jpg', 'ORGANIZER', 0.00, 0, 'ACTIVE'),
(30, 'cc', 'c@gmail.com', '$2y$10$FY5sSFRHoR7t/cvoTsZuA.wgTNy73TARBYRmaNP1H3lNnmV14qbwK', '../assets/uploads/1773834850_part_bg.png', 'ADMIN', 0.00, 0, 'ACTIVE'),
(32, 'dd', 'd@gmail.com', '$2y$10$um4WXfjj4FOonuvQ.m9WUekdWBl1DX1tUSIh3d5k/X6k87Mv7gQou', '../assets/uploads/default_user_icon.webp', 'ORGANIZER', 4.00, 400, 'ACTIVE'),
(45, 'ee', 'e@gmail.com', '$2y$10$7TGwzHF.WnbAD7M8saBTL.bArgSyGrCGgQcpSOIh2mXoQqCzRmUXG', '../assets/uploads/default_user_icon.webp', 'STUDENT', 2.00, 0, 'BANNED'),
(50, 'student1', 'student@apu.edu.my', '$2y$10$CiAy7NmxV3H6R5ZLBZ0.huCssOrO0fZMeKjg3aK/jhihXc3m5m9aS', '../assets/uploads/default_user_icon.webp', 'STUDENT', 0.00, 0, 'ACTIVE'),
(51, 'organizer1', 'organizer@apu.edu.my', '$2y$10$I8iKwd.opyIS9iFyqqA6Se1uEC.9F172gp/17lxBFHy527ZBSFuhe', '../assets/uploads/1774497750_pexels-julia-m-cameron-6995380.jpg', 'ORGANIZER', 0.00, 0, 'ACTIVE'),
(52, 'admin1', 'admin@apu.edu.my', '$2y$10$QmXpLFrZNbJHnANs.sruZ.LS0X.vdaVvaTitj4fn7rnAY2WnkZnGu', '../assets/uploads/default_user_icon.webp', 'ADMIN', 0.00, 0, 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `points_cost` int(11) NOT NULL,
  `voucher_image` varchar(255) NOT NULL,
  `status` enum('ACTIVE','INACTIVE','') NOT NULL DEFAULT 'ACTIVE',
  `badge` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `title`, `description`, `points_cost`, `voucher_image`, `status`, `badge`) VALUES
(1, 'RM5 Voucher @ BilaBila Mart APU Campus', 'Get RM5 off your total bill! Valid for any purchase at BilaBila Mart APU Campus. Perfect for your daily snacks and drinks.', 500, '../assets/uploads/1772868928_bilabila.jpg', 'ACTIVE', ''),
(2, 'RM10 Voucher @ BilaBila Mart APU Campus', 'Get RM10 off your total bill! Valid for any purchase at BilaBila Mart APU Campus. Perfect for your daily snacks and drinks.', 800, '../assets/uploads/1774186418_bilabila.jpg', 'ACTIVE', 'Popular'),
(3, 'Buy 1 Free 1 Subway Item @ Subway APU Campus', 'Double the joy! Purchase any 6-inch sub and get another one absolutely free. Valid at APU Campus outlet.', 1000, '../assets/uploads/1772868928_bilabila.jpg', 'ACTIVE', 'Popular');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_claims`
--

CREATE TABLE `voucher_claims` (
  `voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `unique_code` varchar(255) NOT NULL,
  `claimed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher_claims`
--

INSERT INTO `voucher_claims` (`voucher_id`, `user_id`, `unique_code`, `claimed_at`, `expires_at`) VALUES
(1, 27, 'ECO-69AFC73808991', '2026-03-10 15:24:40', '2026-04-10 15:24:40'),
(3, 27, 'ECO-69B107E21D999', '2026-03-11 14:12:50', '2026-04-11 14:12:50'),
(1, 27, 'ECO-69BD78A8EA899', '2026-03-21 00:41:12', '2026-04-21 00:41:12'),
(1, 50, 'ECO-69C77ADBE3D47', '2026-03-28 14:53:15', '2026-04-28 14:53:15'),
(2, 50, 'ECO-69C9331E98FF4', '2026-03-29 22:11:42', '2026-04-29 22:11:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD KEY `event_id` (`event_id`,`user_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`);

--
-- Indexes for table `voucher_claims`
--
ALTER TABLE `voucher_claims`
  ADD UNIQUE KEY `unique_code` (`unique_code`),
  ADD KEY `voucher_id` (`voucher_id`,`user_id`),
  ADD KEY `claim_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_events_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `fk_event_id` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `voucher_claims`
--
ALTER TABLE `voucher_claims`
  ADD CONSTRAINT `claim_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claim_voucher_id` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`voucher_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
