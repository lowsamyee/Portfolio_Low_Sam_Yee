-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Feb 11, 2025 at 10:15 PM
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
-- Database: `newdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminregister_db`
--

CREATE TABLE `adminregister_db` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminregister_db`
--

INSERT INTO `adminregister_db` (`id`, `user_name`, `password`, `email`, `created_at`) VALUES
(15, '1212', '1212', '22@g', '2025-02-04 12:28:31'),
(16, '1323', '1', '1', '2025-02-04 12:30:07'),
(18, 'aaa', 'aaaa', 'aaa', '2025-02-04 13:07:54'),
(19, 'aaaaaa', 'aaaaaaa', 'aaaaaa', '2025-02-04 13:08:12'),
(20, '123', '321', '231', '2025-02-04 13:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `guest_sessions`
--

CREATE TABLE `guest_sessions` (
  `guest_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managerregister_db`
--

CREATE TABLE `managerregister_db` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managerregister_db`
--

INSERT INTO `managerregister_db` (`id`, `user_name`, `password`, `email`, `created_at`) VALUES
(1, '1', '1', '1', '2025-02-04 11:33:08'),
(7, '111', '111', '1111', '2025-02-04 11:42:20'),
(8, '222', '2222', '222', '2025-02-04 11:43:11'),
(20, '123', '321', '231', '2025-02-04 13:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `person_involved` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `time` time DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_id` varchar(255) DEFAULT NULL,
  `files` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `date`, `category`, `person_involved`, `description`, `time`, `user_id`, `guest_id`, `files`) VALUES
(10, '2025-02-05', 'C', '2', '2', '12:11:00', 0, NULL, ''),
(11, '2025-02-06', 'A', '1', '1', NULL, 0, NULL, 'uploads/1739112555_屏幕截图 2025-01-24 221930.png,uploads/1739112555_屏幕截图 2025-01-24 223417.png,uploads/1739112555_屏幕截图 2025-01-24 223428.png,uploads/1739112555_屏幕截图 2025-01-24 223436.png'),
(13, '2025-02-07', 'A', '1', '1', NULL, 0, NULL, 'uploads/1739112568_屏幕截图 2024-09-30 224142.png,uploads/1739112568_屏幕截图 2024-09-30 224216.png,uploads/1739112568_屏幕截图 2024-09-30 224237.png'),
(14, '2025-02-07', 'A', '1', '1', NULL, 23, NULL, 'uploads/1739112568_屏幕截图 2024-09-30 224142.png,uploads/1739112568_屏幕截图 2024-09-30 224216.png,uploads/1739112568_屏幕截图 2024-09-30 224237.png'),
(19, '2025-02-13', 'A', '2', '2', NULL, 30, NULL, ''),
(34, '2025-02-11', 'Deadlines', '1', 'task', '00:00:00', 23, NULL, ''),
(37, '2025-02-11', 'Travel', 'N/A', 'Travel1', '00:00:00', 23, NULL, ''),
(39, '2025-02-12', 'Personal', '1', 'test1', '00:00:00', 23, NULL, ''),
(43, '2025-02-13', 'Work', '', 'start', '00:00:00', 30, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `userregister_db`
--

CREATE TABLE `userregister_db` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userregister_db`
--

INSERT INTO `userregister_db` (`id`, `user_name`, `password`, `email`, `created_at`) VALUES
(7, '', '', '', '2025-02-07 14:39:28'),
(23, '1', '1', '1', '2025-02-05 08:08:37'),
(30, '2', '2', '2', '2025-02-07 15:05:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminregister_db`
--
ALTER TABLE `adminregister_db`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- Indexes for table `guest_sessions`
--
ALTER TABLE `guest_sessions`
  ADD PRIMARY KEY (`guest_id`);

--
-- Indexes for table `managerregister_db`
--
ALTER TABLE `managerregister_db`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userregister_db`
--
ALTER TABLE `userregister_db`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminregister_db`
--
ALTER TABLE `adminregister_db`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `managerregister_db`
--
ALTER TABLE `managerregister_db`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `userregister_db`
--
ALTER TABLE `userregister_db`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
