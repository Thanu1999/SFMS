-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 01:56 AM
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
-- Database: `sfms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_sessions`
--

CREATE TABLE `academic_sessions` (
  `session_id` int(11) NOT NULL,
  `session_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_sessions`
--

INSERT INTO `academic_sessions` (`session_id`, `session_name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Term 1 2024-2025', '2024-09-01', '2024-12-20', 1, '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(2, 'Term 2 2024-2025', '2025-01-06', '2025-04-11', 1, '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(3, 'Term 3 2024-2025', '2025-04-28', '2025-06-30', 1, '2025-04-30 23:29:06', '2025-04-30 23:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` bigint(20) DEFAULT NULL,
  `action_details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action_type`, `table_name`, `record_id`, `action_details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', '2025-05-02 21:19:50'),
(2, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', '2025-05-02 21:20:03'),
(3, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 11:19:03'),
(4, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 15:51:44'),
(5, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 15:52:00'),
(6, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 16:45:46'),
(7, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 16:45:55'),
(8, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:12:00'),
(9, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:12:17'),
(10, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:13:00'),
(11, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:13:09'),
(12, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:27:39'),
(13, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:27:49'),
(14, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:28:32'),
(15, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 17:28:50'),
(16, 5, 'USER_PROFILE_UPDATE', 'users', 5, '{\"updated_fields\":[\"full_name\",\"contact_number\"]}', NULL, NULL, '2025-05-03 18:08:54'),
(17, 5, 'USER_PASSWORD_CHANGE', 'users', 5, NULL, NULL, NULL, '2025-05-03 18:10:52'),
(18, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 18:10:57'),
(19, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 18:11:12'),
(20, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 19:00:04'),
(21, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 19:00:15'),
(22, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 19:28:11'),
(23, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 19:28:23'),
(24, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-03 23:59:58'),
(25, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 00:00:09'),
(26, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:12:33'),
(27, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:12:55'),
(28, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:29:12'),
(29, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:29:24'),
(30, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:32:09'),
(31, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:39:32'),
(32, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:39:49'),
(33, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 01:40:04'),
(34, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 21:37:26'),
(35, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 23:43:13'),
(36, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 23:43:23'),
(37, 1, 'PAYMENT_REFUNDED', 'payments', 8, '{\"amount\":5,\"reason\":\"fault\",\"invoice_id\":4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-04 23:55:24'),
(38, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:28:03'),
(39, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:28:21'),
(40, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:34:34'),
(41, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:34:43'),
(42, 1, 'USER_LOGIN_FAILURE', NULL, NULL, '{\"username_attempted\":\"testuser\",\"reason\":\"Invalid credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:34:48'),
(43, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:34:55'),
(44, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 01:47:50'),
(45, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 02:14:10'),
(46, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 02:14:18'),
(47, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 02:14:29'),
(48, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 02:15:27'),
(49, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 02:15:34'),
(50, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 02:15:57'),
(51, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 03:15:12'),
(52, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 03:18:55'),
(53, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 20:21:36'),
(54, NULL, 'USER_LOGIN_FAILURE', NULL, NULL, '{\"username_attempted\":\"testuesr\",\"reason\":\"Invalid credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 20:24:33'),
(55, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 20:24:40'),
(56, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 20:48:37'),
(57, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-05 20:55:09'),
(58, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 04:42:15'),
(59, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 04:42:29'),
(60, 5, 'USER_PROFILE_UPDATE', 'users', 5, '{\"updated_fields\":[\"full_name\",\"contact_number\"]}', NULL, NULL, '2025-05-06 05:32:08'),
(61, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 13:48:50'),
(62, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:03:27'),
(63, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:03:36'),
(64, 1, 'SETTINGS_UPDATED', 'system_settings', NULL, '{\"school_contact\":{\"old\":\"\",\"new\":\"0115555555\"},\"bank_account_name\":{\"old\":\"\",\"new\":\"My School\"},\"bank_account_number\":{\"old\":\"\",\"new\":\"0017548888\"},\"bank_name\":{\"old\":\"\",\"new\":\"Bank of Ceylon\"},\"bank_branch\":{\"old\":\"\",\"new\":\"Main Branch\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:07:51'),
(65, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:16:55'),
(66, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:17:05'),
(67, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:18:31'),
(68, 1, 'USER_LOGIN_FAILURE', NULL, NULL, '{\"username_attempted\":\"testuser\",\"reason\":\"Invalid credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:18:58'),
(69, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:19:11'),
(70, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:20:30'),
(71, 6, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:20:44'),
(72, 6, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:32:43'),
(73, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 16:32:49'),
(74, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:03:34'),
(75, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:03:48'),
(76, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:04:00'),
(77, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:04:05'),
(78, 1, 'SETTINGS_UPDATED', 'system_settings', NULL, '{\"bank_reference_info\":{\"old\":\"\",\"new\":\"Please include Student Admission Number or Invoice Number\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:04:12'),
(79, 1, 'SETTINGS_UPDATED', 'system_settings', NULL, '{\"bank_account_name\":{\"old\":\"My School\",\"new\":\"MySchool\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:04:36'),
(80, 1, 'SETTINGS_UPDATED', 'system_settings', NULL, '{\"bank_account_name\":{\"old\":\"MySchool\",\"new\":\"My School\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:05:07'),
(81, 1, 'SETTINGS_UPDATED', 'system_settings', NULL, '{\"bank_account_name\":{\"old\":\"My School\",\"new\":\"MySchool\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:05:38'),
(82, 1, 'SETTINGS_UPDATED', 'system_settings', NULL, '{\"bank_account_name\":{\"old\":\"MySchool\",\"new\":\"My School\"}}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-06 17:05:53'),
(83, 1, 'USER_LOGIN_FAILURE', NULL, NULL, '{\"username_attempted\":\"testuser\",\"reason\":\"Invalid credentials\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 02:30:40'),
(84, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 02:30:48'),
(85, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 04:23:52'),
(86, 5, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 04:24:18'),
(87, 5, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 04:45:36'),
(88, 1, 'USER_LOGIN_SUCCESS', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 04:45:55'),
(89, 1, 'USER_LOGOUT', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-08 05:24:13');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `level` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `level`, `created_at`, `updated_at`) VALUES
(1, 'Grade 1', 'Primary', '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(2, 'Grade 2', 'Primary', '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(3, 'Grade 10', 'Secondary', '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(4, 'Kindergarten A', 'KG', '2025-04-30 23:29:06', '2025-04-30 23:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `discount_types`
--

CREATE TABLE `discount_types` (
  `discount_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed_amount') NOT NULL DEFAULT 'fixed_amount',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discount_types`
--

INSERT INTO `discount_types` (`discount_type_id`, `name`, `description`, `type`, `value`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sibling Discount', 'Applicable for students who have siblings', 'percentage', 5.00, 1, '2025-05-04 22:29:42', '2025-05-06 00:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `fee_categories`
--

CREATE TABLE `fee_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_categories`
--

INSERT INTO `fee_categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(5, 'Transport Fee', 'Charges for school bus service', '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(6, 'Exam Fee', 'Fees related to examinations', '2025-04-30 23:29:06', '2025-05-05 23:00:27'),
(7, 'Library Fee', 'Annual library usage fee', '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(8, 'Lab Fee', 'Fees for science or computer labs', '2025-04-30 23:29:06', '2025-04-30 23:29:06'),
(9, 'Tuition Fee', 'Charges for tuition service', '2025-05-06 00:32:11', '2025-05-06 00:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoices`
--

CREATE TABLE `fee_invoices` (
  `invoice_id` bigint(20) NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `session_id` int(11) NOT NULL,
  `structure_id` bigint(20) DEFAULT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `total_discount` decimal(12,2) DEFAULT 0.00,
  `total_payable` decimal(12,2) NOT NULL,
  `total_paid` decimal(12,2) DEFAULT 0.00,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','partially_paid','paid','overdue','cancelled') DEFAULT 'unpaid',
  `last_reminder_sent_at` datetime DEFAULT NULL,
  `created_by_user_id` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoices`
--

INSERT INTO `fee_invoices` (`invoice_id`, `student_id`, `session_id`, `structure_id`, `invoice_number`, `description`, `total_amount`, `total_discount`, `total_payable`, `total_paid`, `issue_date`, `due_date`, `status`, `last_reminder_sent_at`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'INV-20250501-177', 'Grade 1 Exam Fee Term 1 2024-2025', 1000.00, 0.00, 1000.00, 1000.00, '2025-05-01', '2024-09-16', 'paid', NULL, 1, '2025-05-01 23:02:12', '2025-05-02 21:50:21'),
(2, 1, 1, 3, 'INV-20250501-971', 'Grade 1 Library Fee Term 1 2024-2025', 500.00, 0.00, 500.00, 0.00, '2025-05-01', '2024-09-16', 'unpaid', NULL, 1, '2025-05-01 23:11:49', '2025-05-04 23:43:37'),
(3, 1, 1, 4, 'INV-20250502-420', 'Grade 1 Lab Fee Term 1 2024-2025', 250.00, 0.00, 250.00, 0.00, '2025-05-02', '2024-09-16', 'unpaid', NULL, 1, '2025-05-02 21:57:09', '2025-05-04 23:38:49'),
(4, 1, 1, 5, 'INV-20250502-380', 'Grade 1 Transport Fee Term 1 2024-2025', 100.00, 5.00, 95.00, 29.60, '2025-05-02', '2024-09-16', 'partially_paid', '2025-05-03 22:30:58', 1, '2025-05-02 22:24:18', '2025-05-05 01:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoice_items`
--

CREATE TABLE `fee_invoice_items` (
  `item_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) DEFAULT 0.00,
  `payable_amount` decimal(12,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoice_items`
--

INSERT INTO `fee_invoice_items` (`item_id`, `invoice_id`, `category_id`, `description`, `amount`, `discount`, `payable_amount`, `created_at`) VALUES
(1, 1, 6, 'Grade 1 Exam Fee Term 1 2024-2025', 1000.00, 0.00, 1000.00, '2025-05-01 23:02:12'),
(2, 2, 7, 'Grade 1 Library Fee Term 1 2024-2025', 500.00, 0.00, 500.00, '2025-05-01 23:11:49'),
(3, 3, 8, 'Grade 1 Lab Fee Term 1 2024-2025', 250.00, 0.00, 250.00, '2025-05-02 21:57:09'),
(4, 4, 5, 'Grade 1 Transport Fee Term 1 2024-2025', 100.00, 0.00, 100.00, '2025-05-02 22:24:18');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `structure_id` bigint(20) NOT NULL,
  `session_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `structure_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `applicable_class_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `frequency` enum('one-time','monthly','quarterly','semi-annual','annual','per_term') NOT NULL,
  `due_day` int(11) DEFAULT NULL,
  `late_fee_type` enum('none','fixed','percentage_per_day','fixed_after_days') DEFAULT 'none',
  `late_fee_amount` decimal(10,2) DEFAULT 0.00,
  `late_fee_calculation_basis` decimal(10,2) DEFAULT 0.00,
  `created_by_user_id` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`structure_id`, `session_id`, `category_id`, `structure_name`, `description`, `applicable_class_id`, `amount`, `frequency`, `due_day`, `late_fee_type`, `late_fee_amount`, `late_fee_calculation_basis`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(2, 1, 6, 'Grade 1 Exam Fee Term 1 2024-2025', 'Grade 1 Exam Fee Term 1 2024-2025', 1, 1000.00, 'one-time', 15, 'none', 0.00, 0.00, 1, '2025-05-01 22:48:20', '2025-05-05 23:40:12'),
(3, 1, 7, 'Grade 1 Library Fee Term 1 2024-2025', 'Grade 1 Library Fee Term 1 2024-2025', 1, 500.00, 'one-time', 10, 'none', 0.00, 0.00, 1, '2025-05-01 23:11:26', '2025-05-01 23:11:26'),
(4, 1, 8, 'Grade 1 Lab Fee Term 1 2024-2025', NULL, 1, 250.00, 'one-time', 30, 'none', 0.00, 0.00, 1, '2025-05-02 21:56:44', '2025-05-02 21:56:44'),
(5, 1, 5, 'Grade 1 Transport Fee Term 1 2024-2025', 'Grade 1 Transport Fee Term 1 2024-2025', 1, 100.00, 'one-time', 10, 'none', 0.00, 0.00, 1, '2025-05-02 22:23:55', '2025-05-05 23:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_discounts`
--

CREATE TABLE `invoice_discounts` (
  `invoice_discount_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `discount_type_id` int(11) NOT NULL,
  `applied_amount` decimal(12,2) NOT NULL,
  `applied_by_user_id` bigint(20) DEFAULT NULL,
  `applied_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_discounts`
--

INSERT INTO `invoice_discounts` (`invoice_discount_id`, `invoice_id`, `discount_type_id`, `applied_amount`, `applied_by_user_id`, `applied_at`, `notes`) VALUES
(2, 4, 1, 5.00, 1, '2025-05-05 01:27:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `channel` enum('email','sms','system') NOT NULL,
  `status` enum('pending','sent','failed','read') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `details` text DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `recipient_detail` varchar(255) DEFAULT NULL,
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` bigint(20) DEFAULT NULL,
  `sent_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `template_id`, `subject`, `message`, `channel`, `status`, `sent_at`, `details`, `read_at`, `recipient_detail`, `related_entity_type`, `related_entity_id`, `sent_by`, `created_at`) VALUES
(1, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-10F61', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.5.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-10F61\nReference: 25594\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'pending', NULL, NULL, NULL, NULL, 'payment', 11, NULL, '2025-05-02 23:19:55'),
(2, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-26DA7', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.5.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-26DA7\nReference: 25595\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'pending', NULL, NULL, NULL, NULL, 'payment', 12, NULL, '2025-05-02 23:23:53'),
(3, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-41EAB', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.5.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-41EAB\nReference: 25582\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, 'No valid recipient email or mail configuration missing.', NULL, NULL, 'payment', 13, NULL, '2025-05-03 00:58:06'),
(4, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-428F2', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.1.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-428F2\nReference: 25589\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, 'No valid recipient email or mail configuration missing.', NULL, NULL, 'payment', 14, NULL, '2025-05-03 01:06:18'),
(5, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-07337', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.1.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-07337\nReference: 0244556\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>SMTP Error: data not accepted.</strong><br />\n', NULL, 'newthanusan@gmail.com', 'payment', 15, NULL, '2025-05-03 01:19:31'),
(6, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-34150', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.1.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-34150\nReference: 2558855\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>SMTP Error: data not accepted.</strong><br />\n', NULL, 'newthanusan@gmail.com', 'payment', 16, NULL, '2025-05-03 01:30:05'),
(7, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-80431', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.1.00\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-80431\nReference: 25588222\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>Invalid address:  (From): test-51ndgwvv8yxlzqx8.mlsender.net</strong><br />\n', NULL, 'newthanusan@gmail.com', 'payment', 17, NULL, '2025-05-03 01:31:52'),
(8, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250502-3328B', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.0.10\nPayment Date: 2025-05-02\nReceipt Number: RCPT-20250502-3328B\nReference: 2558822\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>SMTP Error: data not accepted.</strong><br />\n', NULL, 'newthanusan@gmail.com', 'payment', 18, NULL, '2025-05-03 01:36:31'),
(9, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250503-9D504', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.0.10\nPayment Date: 2025-05-03\nReceipt Number: RCPT-20250503-9D504\nReference: 2558811\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'sent', '2025-05-03 07:49:31', NULL, NULL, 'newthanusan@gmail.com', 'payment', 19, NULL, '2025-05-03 11:19:31'),
(10, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250503-6A822', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.0.10\nPayment Date: 2025-05-03\nReceipt Number: RCPT-20250503-6A822\nReference: 1111\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>SMTP Error: data not accepted.</strong><br />\n', NULL, 'newthanusan@gmail.com', 'payment', 20, NULL, '2025-05-03 11:23:39'),
(11, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250503-5B6D3', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.0.10\nPayment Date: 2025-05-03\nReceipt Number: RCPT-20250503-5B6D3\nReference: 1111111\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'sent', '2025-05-03 08:02:44', NULL, NULL, 'newthanusan@gmail.com', 'payment', 21, NULL, '2025-05-03 11:32:44'),
(12, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250503-4F6A2', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.0.10\nPayment Date: 2025-05-03\nReceipt Number: RCPT-20250503-4F6A2\nReference: 2558855\nInvoice Ref: 4\n\nThank you,\n[Your School Name]', 'email', 'sent', '2025-05-03 08:04:46', NULL, NULL, 'newthanusan@gmail.com', 'payment', 22, NULL, '2025-05-03 11:34:46'),
(13, NULL, 1, 'Payment Confirmation - Receipt RCPT-20250503-D5AE0', 'Dear first student,\n\nThis confirms we have received your payment.\n\nAmount Paid: Rs.0.10\nPayment Date: 2025-05-03\nReceipt Number: RCPT-20250503-D5AE0\nReference: 255885555\nInvoice Ref: INV-20250502-380\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>SMTP Error: data not accepted.</strong><br />\n', NULL, 'newthanusan@gmail.com', 'payment', 28, NULL, '2025-05-04 00:42:01'),
(14, 5, 3, 'URGENT: Fee Payment Overdue - Invoice INV-20250502-380', 'Dear first student,\n\nOur records indicate that the payment for invoice #INV-20250502-380 (Amount Due: Rs.0.00) was due on Sep 16, 2024 and is now overdue.\n\nPlease make the payment at your earliest convenience to avoid late fees (if applicable).\n\nThank you,\n[Your School Name]', 'email', 'failed', NULL, '<strong>SMTP Error: data not accepted.</strong><br />\n', NULL, 'newthanusan@gmail.com', 'invoice', 4, NULL, '2025-05-04 02:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `template_id` int(11) NOT NULL,
  `template_code` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_template` text NOT NULL,
  `type` enum('email','sms','system') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`template_id`, `template_code`, `subject`, `body_template`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PAYMENT_CONFIRMATION', 'Payment Confirmation - Receipt {{receipt_number}}', 'Dear {{student_name}},\n\nThis confirms we have received your payment.\n\nAmount Paid: {{currency_symbol}}{{amount_paid}}\nPayment Date: {{payment_date}}\nReceipt Number: {{receipt_number}}\nReference: {{reference_number}}\nInvoice Ref: {{invoice_number}}\n\nThank you,\n[Your School Name]', 'email', 1, '2025-05-02 21:41:34', '2025-05-02 21:41:34'),
(2, 'FEE_DUE_SOON', 'Fee Payment Reminder - Invoice {{invoice_number}}', 'Dear {{student_name}},\n\nThis is a friendly reminder that your fee payment for invoice #{{invoice_number}} (Amount: {{currency_symbol}}{{balance_due}}) is due on {{due_date}}.\n\nPlease ensure payment is made on time.\n\nThank you,\n[Your School Name]', 'email', 1, '2025-05-03 18:30:34', '2025-05-03 18:30:34'),
(3, 'FEE_OVERDUE', 'URGENT: Fee Payment Overdue - Invoice {{invoice_number}}', 'Dear {{student_name}},\n\nOur records indicate that the payment for invoice #{{invoice_number}} (Amount Due: {{currency_symbol}}{{balance_due}}) was due on {{due_date}} and is now overdue.\n\nPlease make the payment at your earliest convenience to avoid late fees (if applicable).\n\nThank you,\n[Your School Name]', 'email', 1, '2025-05-03 18:30:34', '2025-05-03 18:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` bigint(20) NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `payment_date` datetime NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `method_id` int(11) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `payment_status` enum('pending','completed','failed','refunded','partially_refunded','cancelled') NOT NULL DEFAULT 'completed',
  `refunded_amount` decimal(12,2) DEFAULT NULL,
  `refund_date` datetime DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `refunded_by_user_id` bigint(20) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'completed',
  `processed_by_user_id` bigint(20) DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `payment_date`, `amount_paid`, `method_id`, `reference_number`, `notes`, `receipt_number`, `payment_status`, `refunded_amount`, `refund_date`, `refund_reason`, `refunded_by_user_id`, `status`, `processed_by_user_id`, `gateway_response`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-05-01 20:11:57', 500.00, 3, '0122355', 'fully paid', 'RCPT-20250501-AE4D9', 'refunded', 500.00, '2025-05-04 20:13:37', NULL, 1, 'completed', 1, NULL, '2025-05-01 23:41:57', '2025-05-04 23:43:37'),
(2, 1, '2025-05-02 18:20:21', 1000.00, 1, '024455', 'Paid by Student', 'RCPT-20250502-D1ACC', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-02 21:50:21', '2025-05-02 21:50:21'),
(3, 1, '2025-05-02 18:28:13', 100.00, 1, '25588', 'Balance due', 'RCPT-20250502-5CC22', 'refunded', 100.00, '2025-05-04 20:08:49', 'fault', 1, 'completed', 1, NULL, '2025-05-02 21:58:13', '2025-05-04 23:38:49'),
(4, 1, '2025-05-02 18:43:01', 150.00, 1, '25589', NULL, 'RCPT-20250502-6E772', 'refunded', 150.00, '2025-05-04 20:04:51', 'fault', 1, 'completed', 1, NULL, '2025-05-02 22:13:01', '2025-05-08 02:25:04'),
(5, 1, '2025-05-02 18:54:42', 50.00, 1, '25590', NULL, 'RCPT-20250502-DE9BD', 'refunded', 50.00, '2025-05-04 19:57:06', 'fault', 1, 'completed', 1, NULL, '2025-05-02 22:24:42', '2025-05-08 02:25:14'),
(6, 1, '2025-05-02 19:00:59', 10.00, 1, '25591', NULL, 'RCPT-20250502-81C80', 'refunded', 10.00, '2025-05-04 20:15:58', NULL, 1, 'completed', 1, NULL, '2025-05-02 22:30:59', '2025-05-08 02:25:11'),
(7, 1, '2025-05-02 19:01:33', 5.00, 1, '25591', NULL, 'RCPT-20250502-47081', 'refunded', 5.00, '2025-05-04 20:19:02', NULL, 1, 'completed', 1, NULL, '2025-05-02 22:31:33', '2025-05-08 02:25:21'),
(8, 1, '2025-05-02 19:11:20', 5.00, 1, '2222222', NULL, 'RCPT-20250502-EE369', 'refunded', 5.00, '2025-05-04 20:25:24', 'fault', 1, 'completed', 1, NULL, '2025-05-02 22:41:20', '2025-05-08 02:25:08'),
(9, 1, '2025-05-02 19:21:15', 5.00, 1, '25592', NULL, 'RCPT-20250502-07D13', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-02 22:51:15', '2025-05-08 02:25:17'),
(10, 1, '2025-05-02 19:45:37', 5.00, 1, '25593', NULL, 'RCPT-20250502-A22DD', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-02 23:15:37', '2025-05-08 02:25:32'),
(11, 1, '2025-05-02 19:49:55', 5.00, 1, '25594', NULL, 'RCPT-20250502-10F61', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-02 23:19:55', '2025-05-08 02:25:36'),
(12, 1, '2025-05-02 19:53:53', 5.00, 1, '25595', NULL, 'RCPT-20250502-26DA7', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-02 23:23:53', '2025-05-08 02:24:59'),
(13, 1, '2025-05-02 21:28:06', 5.00, 1, '25582', NULL, 'RCPT-20250502-41EAB', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 00:58:06', '2025-05-08 02:24:54'),
(14, 1, '2025-05-02 21:36:18', 1.00, 3, '25589', NULL, 'RCPT-20250502-428F2', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 01:06:18', '2025-05-03 01:06:18'),
(15, 1, '2025-05-02 21:49:26', 1.00, 1, '0244556', NULL, 'RCPT-20250502-07337', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 01:19:26', '2025-05-08 02:25:41'),
(16, 1, '2025-05-02 22:00:01', 1.00, 1, '2558855', NULL, 'RCPT-20250502-34150', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 01:30:01', '2025-05-08 02:25:48'),
(17, 1, '2025-05-02 22:01:52', 1.00, 1, '25588222', NULL, 'RCPT-20250502-80431', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 01:31:52', '2025-05-08 02:25:57'),
(18, 1, '2025-05-02 22:06:26', 0.10, 1, '2558822', NULL, 'RCPT-20250502-3328B', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 01:36:26', '2025-05-08 02:25:52'),
(19, 1, '2025-05-03 07:49:24', 0.10, 1, '2558811', NULL, 'RCPT-20250503-9D504', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 11:19:24', '2025-05-08 02:26:15'),
(20, 1, '2025-05-03 07:53:34', 0.10, 1, '1111', NULL, 'RCPT-20250503-6A822', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 11:23:34', '2025-05-08 02:26:11'),
(21, 1, '2025-05-03 08:02:39', 0.10, 1, '1111111', NULL, 'RCPT-20250503-5B6D3', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 11:32:39', '2025-05-08 02:26:05'),
(22, 1, '2025-05-03 08:04:42', 0.10, 1, '2558855', NULL, 'RCPT-20250503-4F6A2', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-03 11:34:42', '2025-05-08 02:26:01'),
(28, 1, '2025-05-03 21:11:56', 0.10, 3, '255885555', 'Verified against uploaded proof #1', 'RCPT-20250503-D5AE0', 'completed', NULL, NULL, NULL, NULL, 'completed', 1, NULL, '2025-05-04 00:41:56', '2025-05-04 00:41:56');

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `allocation_id` bigint(20) NOT NULL,
  `payment_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `invoice_item_id` bigint(20) DEFAULT NULL,
  `allocated_amount` decimal(12,2) NOT NULL,
  `allocation_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`allocation_id`, `payment_id`, `invoice_id`, `invoice_item_id`, `allocated_amount`, `allocation_date`) VALUES
(1, 1, 2, NULL, 0.00, '2025-05-01 23:41:57'),
(2, 2, 1, NULL, 1000.00, '2025-05-02 21:50:21'),
(3, 3, 3, NULL, 0.00, '2025-05-02 21:58:13'),
(4, 4, 3, NULL, 0.00, '2025-05-02 22:13:01'),
(5, 5, 4, NULL, 0.00, '2025-05-02 22:24:42'),
(6, 6, 4, NULL, 0.00, '2025-05-02 22:30:59'),
(7, 7, 4, NULL, 0.00, '2025-05-02 22:31:33'),
(8, 8, 4, NULL, 0.00, '2025-05-02 22:41:20'),
(9, 9, 4, NULL, 5.00, '2025-05-02 22:51:15'),
(10, 10, 4, NULL, 5.00, '2025-05-02 23:15:37'),
(11, 11, 4, NULL, 5.00, '2025-05-02 23:19:55'),
(12, 12, 4, NULL, 5.00, '2025-05-02 23:23:53'),
(13, 13, 4, NULL, 5.00, '2025-05-03 00:58:06'),
(14, 14, 4, NULL, 1.00, '2025-05-03 01:06:18'),
(15, 15, 4, NULL, 1.00, '2025-05-03 01:19:26'),
(16, 16, 4, NULL, 1.00, '2025-05-03 01:30:01'),
(17, 17, 4, NULL, 1.00, '2025-05-03 01:31:52'),
(18, 18, 4, NULL, 0.10, '2025-05-03 01:36:26'),
(19, 19, 4, NULL, 0.10, '2025-05-03 11:19:24'),
(20, 20, 4, NULL, 0.10, '2025-05-03 11:23:34'),
(21, 21, 4, NULL, 0.10, '2025-05-03 11:32:39'),
(22, 22, 4, NULL, 0.10, '2025-05-03 11:34:42'),
(28, 28, 4, NULL, 0.10, '2025-05-04 00:41:56');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `method_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`method_id`, `method_name`, `is_online`, `is_active`) VALUES
(1, 'Cash', 0, 1),
(2, 'Cheque', 0, 1),
(3, 'Bank Transfer', 0, 1),
(4, 'Online Transfer', 0, 1),
(5, 'Credit Card (Manual)', 0, 1),
(6, 'UPI', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `proof_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `uploader_user_id` bigint(20) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `verified_by_user_id` bigint(20) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `payment_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_proofs`
--

INSERT INTO `payment_proofs` (`proof_id`, `invoice_id`, `student_id`, `uploader_user_id`, `file_name`, `file_path`, `uploaded_at`, `status`, `admin_notes`, `verified_by_user_id`, `verified_at`, `payment_id`) VALUES
(1, 4, 1, 5, 'glass-bottle-manufacturers-in-sri-lanka.png', 'payment_proofs/glass-bottle-manufacturers-in-sri-lanka_1746280790_00f66ab7.png', '2025-05-03 19:29:50', 'verified', NULL, 1, '2025-05-04 00:41:56', 28),
(2, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746280895_e2a5745a.png', '2025-05-03 19:31:35', 'pending', NULL, NULL, NULL, NULL),
(3, 4, 1, 5, 'unnamed.png', 'payment_proofs/unnamed_1746281337_38b1be5f.png', '2025-05-03 19:38:57', 'pending', NULL, NULL, NULL, NULL),
(4, 4, 1, 5, 'result.png', 'payment_proofs/result_1746282048_44282a7f.png', '2025-05-03 19:50:48', 'pending', NULL, NULL, NULL, NULL),
(5, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746282676_840e5505.png', '2025-05-03 20:01:16', 'pending', NULL, NULL, NULL, NULL),
(6, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746282863_69066445.png', '2025-05-03 20:04:23', 'pending', NULL, NULL, NULL, NULL),
(7, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746283198_0f189758.png', '2025-05-03 20:09:58', 'pending', NULL, NULL, NULL, NULL),
(8, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746283913_024d91bb.png', '2025-05-03 20:21:53', 'pending', NULL, NULL, NULL, NULL),
(9, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746284026_60798208.png', '2025-05-03 20:23:46', 'pending', NULL, NULL, NULL, NULL),
(10, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746290520_018e9414.png', '2025-05-03 22:12:00', 'pending', NULL, NULL, NULL, NULL),
(11, 4, 1, 5, 'unknown.png', 'payment_proofs/unknown_1746291986_7f304d5c.png', '2025-05-03 22:36:26', 'pending', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(4, 'Parent'),
(2, 'Staff'),
(3, 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `sequence_counters`
--

CREATE TABLE `sequence_counters` (
  `sequence_name` varchar(50) NOT NULL,
  `current_value` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sequence_counters`
--

INSERT INTO `sequence_counters` (`sequence_name`, `current_value`, `updated_at`) VALUES
('admission_number', 1004, '2025-05-03 11:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `employee_id` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `joining_date` date NOT NULL,
  `status` enum('active','inactive','on_leave','terminated') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `admission_number` varchar(30) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `admission_date` date NOT NULL,
  `current_class_id` int(11) DEFAULT NULL,
  `current_session_id` int(11) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','graduated','transferred_out') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `admission_number`, `first_name`, `last_name`, `middle_name`, `date_of_birth`, `gender`, `email`, `admission_date`, `current_class_id`, `current_session_id`, `section`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, NULL, '1001', 'first', 'student', NULL, '2023-10-04', 'Male', 'newthanusan@gmail.com', '2025-05-01', 1, 1, 'A', 'active', NULL, '2025-05-01 21:24:11', '2025-05-05 22:12:09'),
(2, NULL, '1002', 'second', 'student', NULL, '2023-06-16', 'Male', 'thanusanp.20@uom.lk', '2025-05-03', 1, 1, 'A', 'active', NULL, '2025-05-03 12:38:07', '2025-05-03 12:38:07'),
(3, NULL, '1004', 'third', 'student', NULL, '2023-06-16', 'Female', 'thanusanp.20@uom.lk', '2025-05-03', 1, 1, 'A', 'active', NULL, '2025-05-03 17:00:12', '2025-05-03 17:00:12');

-- --------------------------------------------------------

--
-- Table structure for table `student_guardian_links`
--

CREATE TABLE `student_guardian_links` (
  `link_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `relationship_type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_guardian_links`
--

INSERT INTO `student_guardian_links` (`link_id`, `student_id`, `user_id`, `relationship_type`, `created_at`) VALUES
(1, 3, 5, 'Father', '2025-05-03 17:08:48'),
(3, 1, 5, 'Father', '2025-05-05 22:14:16');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_sensitive` tinyint(1) DEFAULT 0,
  `updated_by_user_id` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `is_sensitive`, `updated_by_user_id`, `updated_at`) VALUES
(1, 'school_contact', '0115555555', NULL, 0, 1, '2025-05-06 16:07:51'),
(2, 'bank_account_name', 'My School', NULL, 0, 1, '2025-05-06 17:05:52'),
(3, 'bank_account_number', '0017548888', NULL, 0, 1, '2025-05-06 16:07:51'),
(4, 'bank_name', 'Bank of Ceylon', NULL, 0, 1, '2025-05-06 16:07:51'),
(5, 'bank_branch', 'Main Branch', NULL, 0, 1, '2025-05-06 16:07:51'),
(6, 'bank_reference_info', 'Please include Student Admission Number or Invoice Number', NULL, 0, 1, '2025-05-06 17:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive','pending_verification','locked') DEFAULT 'pending_verification',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `full_name`, `contact_number`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'testuser', '$2y$10$h2xaap2zw8QjB3MeoS29rOTDXEbfjygUFhrJEVpqaU6FY3DdsVZ12', 'testuser@example.com', 'Test User One', '1234567890', 'active', NULL, '2025-04-30 00:07:04', '2025-04-30 00:07:04'),
(5, 'firstparent', '$2y$10$AM.PPTu33gMryOQiRKpPq.wLAKqKT9ossq4G0Ji2ZgwK8C/VxlYWq', 'newthanusan@gmail.com', 'first parent', '077775', 'active', NULL, '2025-05-03 12:41:11', '2025-05-06 05:32:08'),
(6, 'firststaff', '$2y$10$.c.QP003Wfd8347LoBwAVuQo1R13b3gwYSUPgovJFS9mUxiwkwZrW', 'staff1@sfms.com', 'first staff', '0123455888', 'active', NULL, '2025-05-06 16:20:27', '2025-05-06 16:20:27');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(5, 4),
(6, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_name` (`session_name`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action_type`),
  ADD KEY `idx_audit_table` (`table_name`),
  ADD KEY `idx_audit_record` (`table_name`,`record_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `discount_types`
--
ALTER TABLE `discount_types`
  ADD PRIMARY KEY (`discount_type_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `structure_id` (`structure_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`),
  ADD KEY `idx_invoice_student_session` (`student_id`,`session_id`),
  ADD KEY `idx_invoice_due_date` (`due_date`),
  ADD KEY `idx_invoice_status` (`status`),
  ADD KEY `idx_last_reminder` (`last_reminder_sent_at`);

--
-- Indexes for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`structure_id`),
  ADD UNIQUE KEY `uk_fee_structure` (`session_id`,`category_id`,`structure_name`,`applicable_class_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `applicable_class_id` (`applicable_class_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`);

--
-- Indexes for table `invoice_discounts`
--
ALTER TABLE `invoice_discounts`
  ADD PRIMARY KEY (`invoice_discount_id`),
  ADD UNIQUE KEY `uk_invoice_discount_type` (`invoice_id`,`discount_type_id`),
  ADD KEY `discount_type_id` (`discount_type_id`),
  ADD KEY `applied_by_user_id` (`applied_by_user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `fk_notification_sender` (`sent_by`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_code` (`template_code`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `processed_by_user_id` (`processed_by_user_id`),
  ADD KEY `idx_payment_student_date` (`student_id`,`payment_date`),
  ADD KEY `idx_payment_method` (`method_id`),
  ADD KEY `fk_payment_refunded_by` (`refunded_by_user_id`);

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD UNIQUE KEY `uk_payment_invoice_item` (`payment_id`,`invoice_id`,`invoice_item_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `invoice_item_id` (`invoice_item_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`method_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`proof_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `uploader_user_id` (`uploader_user_id`),
  ADD KEY `verified_by_user_id` (`verified_by_user_id`),
  ADD KEY `idx_proof_status` (`status`),
  ADD KEY `idx_proof_invoice` (`invoice_id`),
  ADD KEY `idx_proof_payment` (`payment_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `sequence_counters`
--
ALTER TABLE `sequence_counters`
  ADD PRIMARY KEY (`sequence_name`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `admission_number` (`admission_number`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `current_session_id` (`current_session_id`),
  ADD KEY `idx_student_name` (`last_name`,`first_name`),
  ADD KEY `idx_student_class_session` (`current_class_id`,`current_session_id`),
  ADD KEY `idx_student_email` (`email`);

--
-- Indexes for table `student_guardian_links`
--
ALTER TABLE `student_guardian_links`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `uk_student_guardian` (`student_id`,`user_id`),
  ADD KEY `idx_guardian_user` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_status` (`status`),
  ADD KEY `idx_user_email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_sessions`
--
ALTER TABLE `academic_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `discount_types`
--
ALTER TABLE `discount_types`
  MODIFY `discount_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_categories`
--
ALTER TABLE `fee_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `invoice_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  MODIFY `item_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `structure_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoice_discounts`
--
ALTER TABLE `invoice_discounts`
  MODIFY `invoice_discount_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `allocation_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `proof_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_guardian_links`
--
ALTER TABLE `student_guardian_links`
  MODIFY `link_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD CONSTRAINT `fee_invoices_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `fee_invoices_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`session_id`),
  ADD CONSTRAINT `fee_invoices_ibfk_3` FOREIGN KEY (`structure_id`) REFERENCES `fee_structures` (`structure_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_invoices_ibfk_4` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD CONSTRAINT `fee_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoice_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `fee_categories` (`category_id`);

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `academic_sessions` (`session_id`),
  ADD CONSTRAINT `fee_structures_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `fee_categories` (`category_id`),
  ADD CONSTRAINT `fee_structures_ibfk_3` FOREIGN KEY (`applicable_class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_structures_ibfk_4` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `invoice_discounts`
--
ALTER TABLE `invoice_discounts`
  ADD CONSTRAINT `invoice_discounts_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_discounts_ibfk_2` FOREIGN KEY (`discount_type_id`) REFERENCES `discount_types` (`discount_type_id`),
  ADD CONSTRAINT `invoice_discounts_ibfk_3` FOREIGN KEY (`applied_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_sender` FOREIGN KEY (`sent_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`template_id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_refunded_by` FOREIGN KEY (`refunded_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`method_id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`processed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `payment_allocations_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_allocations_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_allocations_ibfk_3` FOREIGN KEY (`invoice_item_id`) REFERENCES `fee_invoice_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `payment_proofs_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `fee_invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_proofs_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_proofs_ibfk_3` FOREIGN KEY (`uploader_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_proofs_ibfk_4` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`current_class_id`) REFERENCES `classes` (`class_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_ibfk_3` FOREIGN KEY (`current_session_id`) REFERENCES `academic_sessions` (`session_id`) ON DELETE SET NULL;

--
-- Constraints for table `student_guardian_links`
--
ALTER TABLE `student_guardian_links`
  ADD CONSTRAINT `student_guardian_links_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_guardian_links_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
