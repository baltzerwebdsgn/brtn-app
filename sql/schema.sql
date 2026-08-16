-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Aug 14, 2026 at 02:40 PM
-- Server version: 8.0.46
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cleaning_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `id` int NOT NULL,
  `household_name` varchar(255) NOT NULL,
  `invite_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_tasks`
--

CREATE TABLE `household_tasks` (
  `id` int NOT NULL,
  `household_id` int NOT NULL,
  `library_task_id` int DEFAULT NULL,
  `custom_name` varchar(255) DEFAULT NULL,
  `custom_instructions` text,
  `is_active` tinyint DEFAULT '1',
  `assigned_to` int DEFAULT NULL,
  `custom_room` varchar(100) DEFAULT NULL,
  `custom_frequency` varchar(20) DEFAULT NULL,
  `custom_total_time` int DEFAULT NULL,
  `custom_day_of_week` varchar(50) DEFAULT NULL,
  `custom_week_of_month` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_day_status`
--

CREATE TABLE `task_day_status` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `last_completed` datetime DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `day_of_week_key` varchar(20) GENERATED ALWAYS AS (coalesce(`day_of_week`,_utf8mb4'')) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_history`
--

CREATE TABLE `task_history` (
  `id` int NOT NULL,
  `task_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `day_of_week` varchar(20) DEFAULT NULL,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_library`
--

CREATE TABLE `task_library` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL,
  `instructions` text,
  `hidden_time` int DEFAULT NULL,
  `conditional_time` tinyint(1) DEFAULT '0',
  `total_time` int DEFAULT NULL,
  `day_of_week` varchar(50) DEFAULT NULL,
  `week_of_month` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'head',
  `household_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` int NOT NULL,
  `household_id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invite_code` (`invite_code`);

--
-- Indexes for table `household_tasks`
--
ALTER TABLE `household_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `household_id` (`household_id`),
  ADD KEY `library_task_id` (`library_task_id`);

--
-- Indexes for table `task_day_status`
--
ALTER TABLE `task_day_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `task_day_unique` (`task_id`,`day_of_week_key`);

--
-- Indexes for table `task_history`
--
ALTER TABLE `task_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `task_history_ibfk_1` (`task_id`);

--
-- Indexes for table `task_library`
--
ALTER TABLE `task_library`
  ADD PRIMARY KEY (`id`),
  ADD KEY `day_of_week` (`day_of_week`),
  ADD KEY `room` (`room`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `household_id` (`household_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `household_tasks`
--
ALTER TABLE `household_tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_day_status`
--
ALTER TABLE `task_day_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_history`
--
ALTER TABLE `task_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_library`
--
ALTER TABLE `task_library`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `household_tasks`
--
ALTER TABLE `household_tasks`
  ADD CONSTRAINT `household_tasks_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `household_tasks_ibfk_2` FOREIGN KEY (`library_task_id`) REFERENCES `task_library` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_day_status`
--
ALTER TABLE `task_day_status`
  ADD CONSTRAINT `task_day_status_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `household_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_history`
--
ALTER TABLE `task_history`
  ADD CONSTRAINT `task_history_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `household_tasks` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `task_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `zones`
--
ALTER TABLE `zones`
  ADD CONSTRAINT `zones_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
