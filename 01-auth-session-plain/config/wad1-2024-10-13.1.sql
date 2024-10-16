-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2024 at 08:52 AM
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
-- Database: `wad1-2024-10-13.1`
--

-- --------------------------------------------------------

--
-- Table structure for table `shoutouts`
--

CREATE TABLE `shoutouts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_mode` varchar(16) NOT NULL,
  `selected_user` int(10) UNSIGNED DEFAULT NULL,
  `inputted_user` varchar(128) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shoutouts`
--

INSERT INTO `shoutouts` (`id`, `user_id`, `user_mode`, `selected_user`, `inputted_user`, `message`, `created_at`, `updated_at`) VALUES
(1, 1, 'inputted', NULL, 'wasout', 'meow meow', '2024-10-15 00:39:44', '2024-10-15 00:39:44'),
(2, 1, 'inputted', NULL, 'Arparp', 'meow meow', '2024-10-15 18:20:04', '2024-10-15 18:20:04'),
(3, 2, 'selected', 1, '', 'YOWW', '2024-10-15 18:22:41', '2024-10-15 18:22:41'),
(4, 3, 'selected', 1, '', 'arparp', '2024-10-15 18:25:46', '2024-10-15 18:25:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstname` varchar(64) NOT NULL,
  `lastname` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `username`, `password`, `created_at`, `updated_at`, `avatar`, `profile_picture`) VALUES
(1, 'julius', 'nava', 'juliusnava@example.com', 'julz', 'generalnava939', '2024-10-14 12:22:48', '2024-10-16 06:15:28', 'img/670ea39099bdd.png', 'img/670f5a00daa0e.jpg'),
(2, 'kobee', 'ube', 'kobeee@example.com', 'kobee', 'generalnava939', '2024-10-15 18:22:29', '2024-10-15 18:23:13', NULL, 'img/670eb31114789.jpg'),
(3, 'celili', 'maali', 'celiee@example.com', 'celili', 'generalnava939', '2024-10-15 18:25:38', '2024-10-15 18:25:55', NULL, 'img/670eb3b3c3cde.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shoutouts`
--
ALTER TABLE `shoutouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `selected_user` (`selected_user`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shoutouts`
--
ALTER TABLE `shoutouts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shoutouts`
--
ALTER TABLE `shoutouts`
  ADD CONSTRAINT `shoutouts_ibfk_1` FOREIGN KEY (`selected_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `shoutouts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
