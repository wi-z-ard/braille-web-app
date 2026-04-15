-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 15, 2026 at 01:41 PM
-- Server version: 8.0.43-0ubuntu0.24.04.1
-- PHP Version: 8.3.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `braille_bridge`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'file_upload', 'Uploaded: تعليمي.txt (txt)', '127.0.0.1', '2026-03-26 22:33:34'),
(2, 2, 'register', 'New user registered', '127.0.0.1', '2026-04-12 12:44:08'),
(3, 2, 'login', 'User logged in', '127.0.0.1', '2026-04-12 12:44:09');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orig_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','processing','done','error') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `language` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'auto',
  `file_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_path` text COLLATE utf8mb4_unicode_ci,
  `error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `filename`, `orig_name`, `status`, `language`, `file_type`, `result_path`, `error`, `created_at`, `updated_at`) VALUES
('20260326-0298a1a617397f98', 1, '528238dfde277739431023698679f281.txt', 'تعليمي.txt', 'done', 'ar', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-0298a1a617397f98', NULL, '2026-03-26 17:16:23', '2026-03-26 17:16:26'),
('20260326-301a0b438eaa29b4', 1, '5a22981634b99a61298daf408f5c5b41.txt', 'testit.txt', 'done', 'auto', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-301a0b438eaa29b4', NULL, '2026-03-26 17:29:11', '2026-03-26 17:29:12'),
('20260326-70d284eb65f9c0b0', 1, '40c38e9a6cc6a0a0f217941c4b5576d8.txt', 'testit.txt', 'done', 'auto', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-70d284eb65f9c0b0', NULL, '2026-03-26 18:50:59', '2026-03-26 18:51:00'),
('20260326-849479e182438bf4', 1, 'ecd8b440ddfb4f3132c7bca414299ab6.txt', 'testit.txt', 'done', 'auto', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-849479e182438bf4', NULL, '2026-03-26 18:55:46', '2026-03-26 18:55:47'),
('20260326-87420a9605a8d8bd', 1, 'bb3c57eb5e89d2e50b46aa12f416512d.pdf', '65ce4dc3361670b2637199ee56f287bc.pdf', 'done', 'auto', 'pdf', '/var/www/html/brf-helper/storage/processed/20260326-87420a9605a8d8bd', NULL, '2026-03-26 19:04:48', '2026-03-26 19:04:59'),
('20260326-aa3c466ab9fd0757', 1, 'ee3f125ec8fad180d02a4d973e59e480.txt', 'testit.txt', 'done', 'auto', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-aa3c466ab9fd0757', NULL, '2026-03-26 18:59:45', '2026-03-26 18:59:46'),
('20260326-ce558a6e4362d791', 1, '1e8c437382a217d237476171c732582c.txt', 'testit.txt', 'done', 'auto', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-ce558a6e4362d791', NULL, '2026-03-26 19:32:47', '2026-03-26 19:32:48'),
('20260326-e3f36c84a9608035', 1, 'f7ec52e5770d732c78c241bb917a250f.txt', 'تعليمي.txt', 'done', 'auto', 'txt', '/var/www/html/brf-helper/storage/processed/20260326-e3f36c84a9608035', NULL, '2026-03-26 22:33:33', '2026-03-26 22:33:37');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hits` int UNSIGNED DEFAULT '1',
  `window_start` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`ip`, `action`, `hits`, `window_start`) VALUES
('127.0.0.1', 'login', 1, 1774562407),
('127.0.0.1', 'register', 1, 1775997846),
('127.0.0.1', 'upload', 1, 1774564413);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'text_conversion_limit', '5000', 'Max characters for live text-to-Braille conversion', '2026-03-26 19:12:11'),
(2, 'max_file_size_mb', '50', 'Max upload file size in MB', '2026-03-26 19:12:11'),
(3, 'rate_limit_uploads', '10', 'Max uploads per hour per user', '2026-03-26 19:12:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) DEFAULT '0',
  `text_conversion_limit` int DEFAULT '5000',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `created_at`, `is_admin`, `text_conversion_limit`, `is_active`) VALUES
(1, 'mmm@mmm.com', '$argon2id$v=19$m=65536,t=4,p=1$Q3dDVHBxQlh4aHFZSVp5UA$1VUPeHeWSdQqh3huNIggqnaSIrgNshxPQ6HrvP3trsA', 'mohammed', '2026-03-26 16:54:08', 1, 5000, 1),
(2, 'mmm@yahoo.com', '$argon2id$v=19$m=65536,t=4,p=1$dUo4cWJPSkE3YVUuZkFnRg$BDutP/by2+tDdJsxI6LvGzQjETHTnDeIFb6p7f/3yyY', 'mohammed awad', '2026-04-12 12:44:08', 0, 5000, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`ip`,`action`),
  ADD KEY `idx_window` (`window_start`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
