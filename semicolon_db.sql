-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 03:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `semicolon_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `difficulty` varchar(255) DEFAULT NULL,
  `private_path` varchar(255) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `description`, `subject`, `difficulty`, `private_path`, `cover_image`, `slug`, `token`, `created_at`) VALUES
(1, 'How to Code in React.js', 'DigitalOcean', 'A comprehensive guide to React.js.', 'Web Development', 'Beginner', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/react-js.jpg', 'how-to-code-in-react-js', 'tok_react123', '2025-12-04 06:30:29'),
(2, 'Introduction to Algorithms', 'Thomas H. Cormen', 'The bible of algorithms.', 'Data Structures and Algorithms', 'Advanced', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/introduction-to-algorithms.jpg', 'introduction-to-algorithms', 'tok_algo456', '2025-12-04 06:30:29'),
(4, 'PHP book', 'none', 'some description', 'Web Development', 'Easy', 'private/books/6933bd83570d8.pdf', NULL, 'php-book-4d90e', '4cac8a3ea5b7dbf7923a2625bf751fb0', '2025-12-06 05:22:11');

-- --------------------------------------------------------

--
-- Table structure for table `material_requests`
--

CREATE TABLE `material_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `material_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_publisher` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_requests`
--

INSERT INTO `material_requests` (`id`, `user_id`, `material_type`, `title`, `author_publisher`, `details`, `status`, `requested_at`) VALUES
(1, 2, 'video', 'GSAP Tutorial', 'Sheriyansh', '', 'approved', '2025-12-04 16:44:41'),
(4, 2, 'paper', 'last year maths paper of bca', '', '', 'approved', '2025-12-04 17:03:09'),
(5, 2, 'book', 'rust launguage book', '', '', 'rejected', '2025-12-04 17:08:30'),
(7, 5, 'book', 'php book', '', '', 'approved', '2025-12-06 05:18:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Request Approved!', 'Great news! Your request for \"last year maths paper of bca\" (Paper) has been approved. We\'ll add it to our library within 24-48 hours. Thank you for your patience!', 0, '2025-12-04 17:03:58'),
(2, 2, 'Request Update', 'Unfortunately, we couldn\'t fulfill your request for \"rust launguage book\" (Book) at this time. This could be due to availability or licensing restrictions. Please try requesting something else.', 0, '2025-12-04 17:08:46');

-- --------------------------------------------------------

--
-- Table structure for table `papers`
--

CREATE TABLE `papers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `private_path` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `papers`
--

INSERT INTO `papers` (`id`, `title`, `subject`, `year`, `private_path`, `slug`, `token`, `created_at`) VALUES
(1, 'Mid-Term Exam 2024', 'Database Management Systems', 2024, 'https://drive.google.com/file/d/1kZa2--Ebg2dgy4Nho6WKVOJ8GuacHdFN/view?usp=sharing', 'dbms-mid-2024', 'tok_dbms24', '2025-12-04 06:30:29'),
(2, 'Final Exam 2023', 'Operating Systems', 2023, 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'os-final-2023', 'tok_os23', '2025-12-04 06:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `pro_plans`
--

CREATE TABLE `pro_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_type` enum('book','paper','video') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reactions`
--

INSERT INTO `reactions` (`id`, `user_id`, `resource_type`, `resource_id`, `is_helpful`, `created_at`) VALUES
(1, 2, 'paper', 2, 1, '2025-12-04 06:48:24'),
(2, 2, 'paper', 1, 1, '2025-12-04 06:48:35'),
(3, 2, 'book', 1, 1, '2025-12-04 06:48:41'),
(6, 2, 'book', 2, 0, '2025-12-04 16:42:31'),
(7, 5, 'paper', 2, 1, '2025-12-06 05:13:41');

-- --------------------------------------------------------

--
-- Table structure for table `secure_files`
--

CREATE TABLE `secure_files` (
  `id` int(11) NOT NULL,
  `resource_type` enum('book','paper') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `random_token` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `status` enum('active','banned') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'Admin@123', 'admin', 'active', NULL, '2025-12-04 06:30:29'),
(2, 'testuser', 'Password@123', 'user', 'active', NULL, '2025-12-04 06:47:34'),
(4, 'dax', 'Pass@123', 'user', 'active', NULL, '2025-12-05 07:18:59'),
(5, 'daxmore', 'Password@123', 'user', 'active', NULL, '2025-12-05 20:35:28'),
(7, 'divya', 'password1', 'user', 'active', NULL, '2025-12-05 20:56:34'),
(8, 'sahil', 'Password@1', 'user', 'active', NULL, '2025-12-05 20:59:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_history`
--

CREATE TABLE `user_history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_type` enum('book','paper','video') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_history`
--

INSERT INTO `user_history` (`history_id`, `user_id`, `resource_type`, `resource_id`, `viewed_at`) VALUES
(2, 2, 'paper', 2, '2025-12-04 06:48:17'),
(3, 2, 'paper', 1, '2025-12-04 06:48:31'),
(4, 2, 'book', 1, '2025-12-04 06:48:39'),
(7, 2, 'paper', 1, '2025-12-04 09:24:02'),
(8, 2, 'paper', 1, '2025-12-04 09:25:10'),
(9, 2, 'paper', 1, '2025-12-04 16:42:06'),
(10, 2, 'book', 2, '2025-12-04 16:42:27'),
(11, 2, 'book', 1, '2025-12-04 17:10:18'),
(12, 2, 'paper', 1, '2025-12-04 18:24:33'),
(13, 2, 'paper', 1, '2025-12-04 18:24:50'),
(14, 2, 'paper', 1, '2025-12-04 18:24:53'),
(15, 2, 'paper', 1, '2025-12-04 18:25:10'),
(16, 2, 'paper', 1, '2025-12-04 18:25:16'),
(17, 2, 'paper', 1, '2025-12-04 18:25:47'),
(18, 2, 'paper', 2, '2025-12-04 18:25:58'),
(19, 2, 'paper', 1, '2025-12-04 18:26:01'),
(20, 2, 'book', 1, '2025-12-05 03:30:42'),
(21, 2, 'book', 1, '2025-12-05 08:26:54'),
(22, 2, 'book', 3, '2025-12-05 08:28:23'),
(23, 5, 'book', 1, '2025-12-05 20:35:49'),
(24, 5, 'book', 1, '2025-12-05 20:35:49'),
(25, 5, 'paper', 2, '2025-12-06 05:13:33'),
(26, 5, 'paper', 2, '2025-12-06 05:14:15'),
(27, 5, 'paper', 2, '2025-12-06 05:14:35'),
(28, 5, 'paper', 2, '2025-12-06 05:14:57'),
(29, 5, 'book', 1, '2025-12-06 05:15:35'),
(30, 5, 'paper', 2, '2025-12-06 05:15:47'),
(31, 5, 'book', 1, '2025-12-06 05:18:09'),
(32, 2, 'book', 4, '2025-12-06 05:22:19'),
(33, 2, 'paper', 1, '2026-01-12 18:01:59'),
(34, 2, 'book', 1, '2026-01-12 18:02:12'),
(35, 2, 'paper', 2, '2026-01-12 18:02:44'),
(36, 2, 'paper', 1, '2026-01-12 18:02:47'),
(37, 2, 'book', 1, '2026-01-12 18:05:05'),
(38, 2, 'book', 1, '2026-01-19 04:36:17'),
(39, 2, 'book', 1, '2026-02-22 10:20:08');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `youtube_url` text NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `description`, `youtube_url`, `slug`, `created_at`) VALUES
(1, 'PHP for Beginners', 'A comprehensive tutorial for getting started with PHP.', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/zZ6vybT1HQs?si=UrU7KWZd0jSAv9lo\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>', 'php-for-beginners', '2025-12-04 06:30:29'),
(2, 'Tailwind CSS Crash Course', 'Learn the basics of Tailwind CSS in this crash course.', '<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/6biMWgD6_JY?si=MHZvpeXbYKJBS-K9\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>', 'tailwind-css-crash-course', '2025-12-04 06:30:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `papers`
--
ALTER TABLE `papers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `pro_plans`
--
ALTER TABLE `pro_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`user_id`,`resource_type`,`resource_id`);

--
-- Indexes for table `secure_files`
--
ALTER TABLE `secure_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `random_token` (`random_token`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_history`
--
ALTER TABLE `user_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `material_requests`
--
ALTER TABLE `material_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `papers`
--
ALTER TABLE `papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pro_plans`
--
ALTER TABLE `pro_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `secure_files`
--
ALTER TABLE `secure_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_history`
--
ALTER TABLE `user_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `material_requests`
--
ALTER TABLE `material_requests`
  ADD CONSTRAINT `material_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_history`
--
ALTER TABLE `user_history`
  ADD CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
