-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2025 at 03:19 PM
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
-- Database: `newdb`
--

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
(7, '111', '111', '1111@2', '2025-02-04 11:42:20'),
(8, '222', '2222', '222', '2025-02-04 11:43:11'),
(9, '1121', '12121', '11', '2025-02-04 11:45:04'),
(12, 'qwe', '1qwe', '1qwe', '2025-02-04 11:46:13'),
(13, 'a', 'a', 'waigay2002@gmail.com', '2025-02-04 11:47:13'),
(14, '11', '11', '11', '2025-02-04 12:03:09'),
(15, '1213', '12', '22', '2025-02-04 12:28:31'),
(16, '1323', '1', '1', '2025-02-04 12:30:07'),
(18, 'aaa', 'aaaa', 'aaa', '2025-02-04 13:07:54'),
(19, 'aaaaaa', 'aaaaaaa', 'aaaaaa', '2025-02-04 13:08:12'),
(20, '123', '321', '231', '2025-02-04 13:09:47'),
(23, '1', '1', '1', '2025-02-05 08:08:37');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `userregister_db`
--
ALTER TABLE `userregister_db`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
