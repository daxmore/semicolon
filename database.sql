-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026

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
CREATE DATABASE IF NOT EXISTS `semicolon_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `semicolon_db`;

-- --------------------------------------------------------
-- Existing Tables from semicolon_db.sql
-- --------------------------------------------------------

CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `difficulty` varchar(255) DEFAULT NULL,
  `private_path` varchar(255) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `books` (`id`, `title`, `author`, `description`, `subject`, `difficulty`, `private_path`, `cover_image`, `slug`, `token`, `created_at`) VALUES
(1, 'How to Code in React.js', 'DigitalOcean', 'A comprehensive guide to React.js.', 'Web Development', 'Beginner', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/react-js.jpg', 'how-to-code-in-react-js', 'tok_react123', '2025-12-04 06:30:29'),
(2, 'Introduction to Algorithms', 'Thomas H. Cormen', 'The bible of algorithms.', 'Data Structures and Algorithms', 'Advanced', 'http://assets.digitalocean.com/books/how-to-code-in-reactjs.pdf', 'uploads/covers/introduction-to-algorithms.jpg', 'introduction-to-algorithms', 'tok_algo456', '2025-12-04 06:30:29'),
(4, 'PHP book', 'none', 'some description', 'Web Development', 'Easy', 'private/books/6933bd83570d8.pdf', NULL, 'php-book-4d90e', '4cac8a3ea5b7dbf7923a2625bf751fb0', '2025-12-06 05:22:11');

-- Users Table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `status` enum('active','banned') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `username`, `password`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 'admin', 'Admin@123', 'admin', 'active', NULL, '2025-12-04 06:30:29'),
(2, 'testuser', 'Password@123', 'user', 'active', NULL, '2025-12-04 06:47:34'),
(4, 'dax', 'Pass@123', 'user', 'active', NULL, '2025-12-05 07:18:59'),
(5, 'daxmore', 'Password@123', 'user', 'active', NULL, '2025-12-05 20:35:28'),
(7, 'divya', 'password1', 'user', 'active', NULL, '2025-12-05 20:56:34'),
(8, 'sahil', 'Password@1', 'user', 'active', NULL, '2025-12-05 20:59:17');

-- Material Requests
CREATE TABLE `material_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `material_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_publisher` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `material_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Existing Notifications Table (Modified slightly to support type)
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT 'system',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 2, 'system', 'Request Approved!', 'Great news! Your request for \"last year maths paper of bca\" (Paper) has been approved. We\'ll add it to our library within 24-48 hours. Thank you for your patience!', NULL, 0, '2025-12-04 17:03:58'),
(2, 2, 'system', 'Request Update', 'Unfortunately, we couldn\'t fulfill your request for \"rust launguage book\" (Book) at this time. This could be due to availability or licensing restrictions. Please try requesting something else.', NULL, 0, '2025-12-04 17:08:46');

-- Papers Table
CREATE TABLE `papers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `private_path` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Videos Table
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `youtube_url` text NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Reactions Table (Library)
CREATE TABLE `reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_type` enum('book','paper','video') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reaction` (`user_id`,`resource_type`,`resource_id`),
  CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- User History Table
CREATE TABLE `user_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resource_type` enum('book','paper','video','post') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Secure Files Table
CREATE TABLE `secure_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` enum('book','paper') NOT NULL,
  `resource_id` int(11) NOT NULL,
  `random_token` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `random_token` (`random_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Pro Plans Table
CREATE TABLE `pro_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- NEW: Semicolon Community Tables
-- --------------------------------------------------------

-- Community Posts
CREATE TABLE `community_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_url` LONGTEXT DEFAULT NULL,
  `category` enum('Frontend', 'Backend', 'Full Stack', 'App Dev', 'Game Dev', 'UI/UX Design', 'Graphic Design', 'Video Editing', 'Motion Graphics', 'Data Science', 'AI & ML', 'Cybersecurity', 'DevOps', 'Cloud Computing', 'General Tech') NOT NULL,
  `upvotes` int(11) DEFAULT 0,
  `downvotes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cp_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Community Comments
CREATE TABLE `community_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cc_post_fk` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cc_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Community Reactions (Voting Tracking)
CREATE TABLE `community_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `reaction_type` enum('upvote','downvote') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comm_reaction` (`user_id`,`post_id`),
  CONSTRAINT `cr_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cr_post_fk` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;