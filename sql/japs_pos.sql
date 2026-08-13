-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 13, 2026 at 05:41 PM
-- Server version: 8.4.3
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `japs_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `account_type_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_transactions`
--

CREATE TABLE `account_transactions` (
  `id` int UNSIGNED NOT NULL,
  `account_id` int NOT NULL,
  `type` enum('debit','credit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_type` enum('opening_balance','fund_transfer','deposit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(22,4) NOT NULL,
  `reff_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operation_date` datetime NOT NULL,
  `created_by` int NOT NULL,
  `transaction_id` int DEFAULT NULL,
  `transaction_payment_id` int DEFAULT NULL,
  `transfer_transaction_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

CREATE TABLE `account_types` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_account_type_id` int DEFAULT NULL,
  `business_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int UNSIGNED NOT NULL,
  `log_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` int DEFAULT NULL,
  `subject_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_id` int DEFAULT NULL,
  `causer_id` int DEFAULT NULL,
  `causer_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `batch_uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_id`, `subject_type`, `event`, `business_id`, `causer_id`, `causer_type`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-18 09:28:24', '2026-07-18 09:28:24'),
(2, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-18 09:32:06', '2026-07-18 09:32:06'),
(3, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-18 09:32:20', '2026-07-18 09:32:20'),
(4, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-20 05:58:21', '2026-07-20 05:58:21'),
(5, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-20 06:42:58', '2026-07-20 06:42:58'),
(6, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-20 06:43:24', '2026-07-20 06:43:24'),
(7, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-21 05:42:40', '2026-07-21 05:42:40'),
(8, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-21 10:56:33', '2026-07-21 10:56:33'),
(9, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-21 11:00:28', '2026-07-21 11:00:28'),
(10, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-22 07:33:50', '2026-07-22 07:33:50'),
(11, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-22 07:41:54', '2026-07-22 07:41:54'),
(12, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-22 07:42:10', '2026-07-22 07:42:10'),
(13, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-22 08:52:13', '2026-07-22 08:52:13'),
(14, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-23 04:40:13', '2026-07-23 04:40:13'),
(15, 'default', 'contact_deleted', 9, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-23 07:29:09', '2026-07-23 07:29:09'),
(16, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-23 09:00:52', '2026-07-23 09:00:52'),
(17, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-24 08:07:58', '2026-07-24 08:07:58'),
(18, 'default', 'added', 11, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":13.17}}', NULL, '2026-07-24 08:13:33', '2026-07-24 08:13:33'),
(19, 'default', 'added', 14, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12.07}}', NULL, '2026-07-24 10:13:56', '2026-07-24 10:13:56'),
(20, 'default', 'added', 15, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12.07}}', NULL, '2026-07-24 10:14:33', '2026-07-24 10:14:33'),
(21, 'default', 'edited', 11, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":26.34},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"13.1700\"}}', NULL, '2026-07-24 10:15:39', '2026-07-24 10:15:39'),
(22, 'default', 'added', 16, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.17}}', NULL, '2026-07-24 10:28:17', '2026-07-24 10:28:17'),
(23, 'default', 'payment_edited', 11, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":\"26.3400\"},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"26.3400\"}}', NULL, '2026-07-24 10:28:43', '2026-07-24 10:28:43'),
(24, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-27 05:23:20', '2026-07-27 05:23:20'),
(25, 'default', 'added', 19, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":21.95}}', NULL, '2026-07-27 05:34:00', '2026-07-27 05:34:00'),
(26, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-27 05:38:51', '2026-07-27 05:38:51'),
(27, 'default', 'added', 42, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":6.03}}', NULL, '2026-07-27 05:43:40', '2026-07-27 05:43:40'),
(28, 'default', 'added', 43, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":355.49}}', NULL, '2026-07-27 05:44:05', '2026-07-27 05:44:05'),
(29, 'default', 'sell_deleted', 15, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":15,\"invoice_no\":\"0005\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0700\"}}', NULL, '2026-07-27 05:44:47', '2026-07-27 05:44:47'),
(30, 'default', 'added', 44, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":5.48}}', NULL, '2026-07-27 05:44:55', '2026-07-27 05:44:55'),
(31, 'default', 'added', 45, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":5.48}}', NULL, '2026-07-27 05:46:10', '2026-07-27 05:46:10'),
(32, 'default', 'added', 46, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":21.95}}', NULL, '2026-07-27 05:47:31', '2026-07-27 05:47:31'),
(33, 'default', 'sell_deleted', 44, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":44,\"invoice_no\":\"0010\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"5.4800\"}}', NULL, '2026-07-27 06:06:37', '2026-07-27 06:06:38'),
(34, 'default', 'added', 48, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":4.99}}', NULL, '2026-07-27 06:19:24', '2026-07-27 06:19:24'),
(35, 'default', 'added', 49, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":11}}', NULL, '2026-07-27 06:19:44', '2026-07-27 06:19:44'),
(36, 'default', 'added', 52, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":11}}', NULL, '2026-07-27 06:20:48', '2026-07-27 06:20:48'),
(37, 'default', 'added', 55, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":4.99}}', NULL, '2026-07-27 06:26:44', '2026-07-27 06:26:44'),
(38, 'default', 'added', 56, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":16.46}}', NULL, '2026-07-27 06:26:55', '2026-07-27 06:26:55'),
(39, 'default', 'added', 57, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":7.61}}', NULL, '2026-07-27 06:49:08', '2026-07-27 06:49:08'),
(40, 'default', 'added', 58, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":11}}', NULL, '2026-07-27 06:49:15', '2026-07-27 06:49:15'),
(41, 'default', 'added', 59, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":6}}', NULL, '2026-07-27 08:41:32', '2026-07-27 08:41:32'),
(42, 'default', 'added', 60, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12}}', NULL, '2026-07-27 08:45:04', '2026-07-27 08:45:04'),
(43, 'default', 'edited', 46, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":8.22},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"21.9500\"}}', NULL, '2026-07-27 08:45:30', '2026-07-27 08:45:30'),
(44, 'default', 'sell_deleted', 45, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":45,\"invoice_no\":\"0011\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"5.4800\"}}', NULL, '2026-07-27 08:46:15', '2026-07-27 08:46:15'),
(45, 'default', 'added', 61, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":11}}', NULL, '2026-07-27 08:47:28', '2026-07-27 08:47:28'),
(46, 'default', 'edited', 61, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":7.49},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"11.0000\"}}', NULL, '2026-07-27 08:47:43', '2026-07-27 08:47:43'),
(47, 'default', 'added', 62, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 08:47:56', '2026-07-27 08:47:56'),
(48, 'default', 'added', 63, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":9.49}}', NULL, '2026-07-27 08:50:19', '2026-07-27 08:50:19'),
(49, 'default', 'edited', 63, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":14.5},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"9.4900\"}}', NULL, '2026-07-27 08:50:38', '2026-07-27 08:50:38'),
(50, 'default', 'sell_deleted', 62, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":62,\"invoice_no\":\"0023\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 08:56:19', '2026-07-27 08:56:19'),
(51, 'default', 'added', 64, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":9.49}}', NULL, '2026-07-27 08:56:37', '2026-07-27 08:56:37'),
(52, 'default', 'sell_deleted', 64, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":64,\"invoice_no\":\"0025\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"9.4900\"}}', NULL, '2026-07-27 08:59:24', '2026-07-27 08:59:24'),
(53, 'default', 'added', 65, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":11}}', NULL, '2026-07-27 08:59:49', '2026-07-27 08:59:49'),
(54, 'default', 'sell_deleted', 65, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":65,\"invoice_no\":\"0026\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"11.0000\"}}', NULL, '2026-07-27 08:59:57', '2026-07-27 08:59:57'),
(55, 'default', 'added', 66, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":20}}', NULL, '2026-07-27 09:00:52', '2026-07-27 09:00:52'),
(56, 'default', 'sell_deleted', 66, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":66,\"invoice_no\":\"0027\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"20.0000\"}}', NULL, '2026-07-27 09:02:00', '2026-07-27 09:02:00'),
(57, 'default', 'added', 67, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":12}}', NULL, '2026-07-27 09:02:54', '2026-07-27 09:02:54'),
(58, 'default', 'sell_deleted', 67, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":67,\"invoice_no\":\"2026\\/0004\",\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 09:03:39', '2026-07-27 09:03:39'),
(59, 'default', 'added', 68, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 09:03:51', '2026-07-27 09:03:51'),
(60, 'default', 'sell_deleted', 68, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":68,\"invoice_no\":\"0028\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 09:04:49', '2026-07-27 09:04:49'),
(61, 'default', 'added', 69, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":9.49}}', NULL, '2026-07-27 09:05:10', '2026-07-27 09:05:10'),
(62, 'default', 'sell_deleted', 69, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":69,\"invoice_no\":\"0029\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"9.4900\"}}', NULL, '2026-07-27 09:05:37', '2026-07-27 09:05:37'),
(63, 'default', 'added', 70, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 09:09:49', '2026-07-27 09:09:49'),
(64, 'default', 'added', 72, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":18.5}}', NULL, '2026-07-27 09:22:24', '2026-07-27 09:22:24'),
(65, 'default', 'sell_deleted', 70, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":70,\"invoice_no\":\"0030\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 09:28:16', '2026-07-27 09:28:16'),
(66, 'default', 'sell_deleted', 72, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":72,\"invoice_no\":\"2026\\/0005\",\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"18.5000\"}}', NULL, '2026-07-27 09:28:34', '2026-07-27 09:28:34'),
(67, 'default', 'added', 73, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 09:30:40', '2026-07-27 09:30:40'),
(68, 'default', 'edited', 73, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":21.49},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 09:30:54', '2026-07-27 09:30:54'),
(69, 'default', 'added', 74, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":9.49}}', NULL, '2026-07-27 09:32:10', '2026-07-27 09:32:10'),
(70, 'default', 'edited', 74, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"payment_status\":\"due\",\"final_total\":9.49},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"9.4900\"}}', NULL, '2026-07-27 09:35:25', '2026-07-27 09:35:25'),
(71, 'default', 'edited', 74, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":9.49},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"payment_status\":\"due\",\"final_total\":\"9.4900\"}}', NULL, '2026-07-27 09:35:37', '2026-07-27 09:35:37'),
(72, 'default', 'edited', 74, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.49},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"9.4900\"}}', NULL, '2026-07-27 09:35:46', '2026-07-27 09:35:46'),
(73, 'default', 'added', 75, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12}}', NULL, '2026-07-27 09:38:36', '2026-07-27 09:38:36'),
(74, 'default', 'added', 76, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":21.49}}', NULL, '2026-07-27 09:38:56', '2026-07-27 09:38:56'),
(75, 'default', 'edited', 76, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":46.49},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"21.4900\"}}', NULL, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(76, 'default', 'added', 77, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":4.99}}', NULL, '2026-07-27 09:41:28', '2026-07-27 09:41:28'),
(77, 'default', 'edited', 77, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"payment_status\":\"due\",\"final_total\":4.99},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"4.9900\"}}', NULL, '2026-07-27 09:41:41', '2026-07-27 09:41:41'),
(78, 'default', 'edited', 77, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":22.99},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"payment_status\":\"due\",\"final_total\":\"4.9900\"}}', NULL, '2026-07-27 09:42:01', '2026-07-27 09:42:01'),
(79, 'default', 'added', 78, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 10:02:14', '2026-07-27 10:02:14'),
(80, 'default', 'edited', 78, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 10:02:21', '2026-07-27 10:02:21'),
(81, 'default', 'added', 79, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12}}', NULL, '2026-07-27 10:02:26', '2026-07-27 10:02:26'),
(82, 'default', 'added', 80, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":12}}', NULL, '2026-07-27 10:03:11', '2026-07-27 10:03:11'),
(83, 'default', 'edited', 80, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":20.99},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 10:03:19', '2026-07-27 10:03:19'),
(84, 'default', 'added', 81, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 10:05:50', '2026-07-27 10:05:50'),
(85, 'default', 'added', 82, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":4.99}}', NULL, '2026-07-27 10:07:00', '2026-07-27 10:07:00'),
(86, 'default', 'sell_deleted', 82, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":82,\"invoice_no\":\"0042\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"4.9900\"}}', NULL, '2026-07-27 10:10:37', '2026-07-27 10:10:37'),
(87, 'default', 'sell_deleted', 81, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":81,\"invoice_no\":\"0041\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 10:10:40', '2026-07-27 10:10:40'),
(88, 'default', 'added', 83, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 10:10:47', '2026-07-27 10:10:47'),
(89, 'default', 'edited', 83, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 10:11:24', '2026-07-27 10:11:24'),
(90, 'default', 'added', 84, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":4.99}}', NULL, '2026-07-27 10:11:34', '2026-07-27 10:11:34'),
(91, 'default', 'edited', 84, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":4.99},\"old\":{\"type\":\"sell\",\"status\":\"draft\",\"final_total\":\"4.9900\"}}', NULL, '2026-07-27 10:11:43', '2026-07-27 10:11:43'),
(92, 'default', 'added', 85, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":8.99}}', NULL, '2026-07-27 10:12:13', '2026-07-27 10:12:13'),
(93, 'default', 'sell_deleted', 85, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":85,\"invoice_no\":\"0045\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"8.9900\"}}', NULL, '2026-07-27 10:20:03', '2026-07-27 10:20:03'),
(94, 'default', 'added', 86, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":12}}', NULL, '2026-07-27 10:20:18', '2026-07-27 10:20:18'),
(95, 'default', 'edited', 86, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":12},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"12.0000\"}}', NULL, '2026-07-27 10:21:29', '2026-07-27 10:21:29'),
(96, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-27 10:43:49', '2026-07-27 10:43:49'),
(97, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-28 04:22:53', '2026-07-28 04:22:53'),
(98, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-28 06:40:17', '2026-07-28 06:40:17'),
(99, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-07-28 06:40:24', '2026-07-28 06:40:24'),
(100, 'default', 'added', 87, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":8.99}}', NULL, '2026-07-28 08:11:08', '2026-07-28 08:11:08'),
(101, 'default', 'added', 88, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":92.07}}', NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(102, 'default', 'added', 89, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":8.99}}', NULL, '2026-08-03 05:36:11', '2026-08-03 05:36:11'),
(103, 'default', 'added', 93, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":21.49}}', NULL, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(104, 'default', 'added', 95, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":10}}', NULL, '2026-08-03 06:02:12', '2026-08-03 06:02:12'),
(105, 'default', 'added', 97, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":20.67}}', NULL, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(106, 'default', 'added', 98, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":15.93}}', NULL, '2026-08-03 07:13:21', '2026-08-03 07:13:21'),
(107, 'default', 'added', 99, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":13.2}}', NULL, '2026-08-03 07:14:07', '2026-08-03 07:14:07'),
(108, 'default', 'payment_edited', 99, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":\"13.2000\"},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"13.2000\"}}', NULL, '2026-08-03 07:14:52', '2026-08-03 07:14:52'),
(109, 'default', 'added', 100, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-03 07:23:58', '2026-08-03 07:23:58'),
(110, 'default', 'edited', 2, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-03 07:27:34', '2026-08-03 07:27:34'),
(111, 'default', 'added', 101, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":5.49}}', NULL, '2026-08-03 07:27:56', '2026-08-03 07:27:56'),
(112, 'default', 'added', 102, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":9.89}}', NULL, '2026-08-03 07:31:31', '2026-08-03 07:31:31'),
(113, 'default', 'sell_deleted', 98, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":98,\"invoice_no\":\"0053\",\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"15.9300\"}}', NULL, '2026-08-03 07:31:53', '2026-08-03 07:31:53'),
(114, 'default', 'added', 103, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 07:32:08', '2026-08-03 07:32:08'),
(115, 'default', 'payment_edited', 10, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":\"11.9700\"},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"due\",\"final_total\":\"11.9700\"}}', NULL, '2026-08-03 07:36:40', '2026-08-03 07:36:40'),
(116, 'default', 'added', 104, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":9.89}}', NULL, '2026-08-03 07:40:24', '2026-08-03 07:40:24'),
(117, 'default', 'added', 105, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89}}', NULL, '2026-08-03 07:42:48', '2026-08-03 07:42:48'),
(118, 'default', 'edited', 104, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"9.8900\"}}', NULL, '2026-08-03 07:57:05', '2026-08-03 07:57:05'),
(119, 'default', 'added', 106, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89}}', NULL, '2026-08-03 08:34:31', '2026-08-03 08:34:31'),
(120, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-03 08:36:29', '2026-08-03 08:36:29'),
(121, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-03 08:36:38', '2026-08-03 08:36:38'),
(122, 'default', 'added', 107, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":45.49}}', NULL, '2026-08-03 08:42:12', '2026-08-03 08:42:12'),
(123, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-03 08:44:14', '2026-08-03 08:44:14'),
(124, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-03 08:44:27', '2026-08-03 08:44:27'),
(125, 'default', 'added', 108, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89}}', NULL, '2026-08-03 08:45:04', '2026-08-03 08:45:04'),
(126, 'default', 'added', 109, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":59.89}}', NULL, '2026-08-03 08:46:35', '2026-08-03 08:46:35'),
(127, 'default', 'added', 110, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":10.44}}', NULL, '2026-08-03 08:47:42', '2026-08-03 08:47:42'),
(128, 'default', 'added', 111, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 09:07:45', '2026-08-03 09:07:45'),
(129, 'default', 'added', 112, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":5.49}}', NULL, '2026-08-03 09:19:03', '2026-08-03 09:19:03'),
(130, 'default', 'added', 113, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 09:20:11', '2026-08-03 09:20:11'),
(131, 'default', 'added', 114, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":5.49}}', NULL, '2026-08-03 09:27:47', '2026-08-03 09:27:47'),
(132, 'default', 'added', 115, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":13.2}}', NULL, '2026-08-03 09:30:59', '2026-08-03 09:30:59'),
(133, 'default', 'added', 116, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":71.56}}', NULL, '2026-08-03 09:33:47', '2026-08-03 09:33:47'),
(134, 'default', 'added', 117, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":20.78}}', NULL, '2026-08-03 09:39:11', '2026-08-03 09:39:11'),
(135, 'default', 'added', 118, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-03 09:39:32', '2026-08-03 09:39:32'),
(136, 'default', 'added', 119, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 09:40:47', '2026-08-03 09:40:47'),
(137, 'default', 'added', 120, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 09:43:42', '2026-08-03 09:43:42'),
(138, 'default', 'added', 121, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 10:33:10', '2026-08-03 10:33:10'),
(139, 'default', 'added', 122, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-03 10:35:05', '2026-08-03 10:35:05'),
(140, 'default', 'added', 123, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-03 10:45:50', '2026-08-03 10:45:50'),
(141, 'default', 'added', 124, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-03 10:47:11', '2026-08-03 10:47:11'),
(142, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-04 05:10:54', '2026-08-04 05:10:54'),
(143, 'default', 'added', 125, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(144, 'default', 'added', 126, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":65.38}}', NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(145, 'default', 'payment_edited', 115, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":\"13.2000\"},\"old\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"due\",\"final_total\":\"13.2000\"}}', NULL, '2026-08-04 08:45:04', '2026-08-04 08:45:04'),
(146, 'default', 'added', 127, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-04 08:46:22', '2026-08-04 08:46:22'),
(147, 'default', 'added', 128, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-05 04:25:37', '2026-08-05 04:25:37'),
(148, 'default', 'added', 129, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-05 05:40:29', '2026-08-05 05:40:29'),
(149, 'default', 'added', 130, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-05 05:40:59', '2026-08-05 05:40:59'),
(150, 'default', 'added', 131, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":6.04}}', NULL, '2026-08-05 06:32:36', '2026-08-05 06:32:36'),
(151, 'default', 'added', 132, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":5.49}}', NULL, '2026-08-05 08:03:09', '2026-08-05 08:03:09'),
(152, 'default', 'added', 133, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":15.38}}', NULL, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(153, 'default', 'added', 134, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":4.71}}', NULL, '2026-08-06 05:51:04', '2026-08-06 05:51:04'),
(154, 'default', 'added', 135, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89}}', NULL, '2026-08-07 05:48:54', '2026-08-07 05:48:54'),
(155, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-11 06:37:57', '2026-08-11 06:37:57'),
(156, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-12 03:56:54', '2026-08-12 03:56:54'),
(157, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-12 03:57:21', '2026-08-12 03:57:21'),
(158, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-12 03:57:35', '2026-08-12 03:57:35'),
(159, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-12 04:55:04', '2026-08-12 04:55:04'),
(160, 'default', 'added', 137, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89}}', NULL, '2026-08-12 06:35:01', '2026-08-12 06:35:01'),
(161, 'default', 'added', 138, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":140.25}}', NULL, '2026-08-12 06:35:18', '2026-08-12 06:35:18'),
(162, 'default', 'added', 139, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":163.89}}', NULL, '2026-08-12 06:35:38', '2026-08-12 06:35:38'),
(163, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-12 07:05:22', '2026-08-12 07:05:22'),
(164, 'default', 'added', 140, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":9.89}}', NULL, '2026-08-12 07:06:38', '2026-08-12 07:06:38'),
(165, 'default', 'added', 141, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"payment_status\":\"paid\",\"final_total\":396}}', NULL, '2026-08-12 07:13:57', '2026-08-12 07:13:57'),
(166, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 05:47:40', '2026-08-13 05:47:40'),
(167, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 05:50:18', '2026-08-13 05:50:18'),
(168, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 05:51:21', '2026-08-13 05:51:21'),
(169, 'default', 'status_updated', 142, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"from\":\"ordered\",\"to\":\"completed\"}', NULL, '2026-08-13 05:53:35', '2026-08-13 05:53:35'),
(170, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 06:03:28', '2026-08-13 06:03:28'),
(171, 'default', 'added', 143, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":13.2}}', NULL, '2026-08-13 06:13:49', '2026-08-13 06:13:49'),
(172, 'default', 'added', 144, 'App\\Transaction', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 06:28:33', '2026-08-13 06:28:33'),
(173, 'default', 'edited', 144, 'App\\Transaction', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 06:29:35', '2026-08-13 06:29:35'),
(174, 'default', 'added', 145, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":140.25}}', NULL, '2026-08-13 06:29:51', '2026-08-13 06:29:51'),
(175, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 06:34:03', '2026-08-13 06:34:03'),
(176, 'default', 'added', 147, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"attributes\":{\"type\":\"sell\",\"status\":\"final\",\"shipping_status\":\"ordered\",\"payment_status\":\"paid\",\"final_total\":19.19}}', NULL, '2026-08-13 06:41:27', '2026-08-13 06:41:27'),
(177, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 06:57:42', '2026-08-13 06:57:42'),
(178, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:13:41', '2026-08-13 07:13:41'),
(179, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:13:41', '2026-08-13 07:13:41'),
(180, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:13:58', '2026-08-13 07:13:58'),
(181, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:15:12', '2026-08-13 07:15:12'),
(182, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:16:34', '2026-08-13 07:16:34'),
(183, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:20:36', '2026-08-13 07:20:36'),
(184, 'default', 'so_deleted', 142, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":142,\"invoice_no\":\"2026\\/0001\"}', NULL, '2026-08-13 07:24:05', '2026-08-13 07:24:05'),
(185, 'default', 'so_deleted', 10, 'App\\Transaction', NULL, 1, 1, 'App\\User', '{\"id\":10,\"invoice_no\":\"0002\"}', NULL, '2026-08-13 07:24:28', '2026-08-13 07:24:28'),
(186, 'default', 'added', 3, 'App\\User', NULL, 1, 1, 'App\\User', '{\"name\":\"mr Sales Man\"}', NULL, '2026-08-13 07:40:09', '2026-08-13 07:40:09'),
(187, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-08-13 07:40:17', '2026-08-13 07:40:17'),
(188, 'default', 'login', 3, 'App\\User', NULL, 1, 3, 'App\\User', '[]', NULL, '2026-08-13 07:40:31', '2026-08-13 07:40:31');

-- --------------------------------------------------------

--
-- Table structure for table `ai_assistance_settings`
--

CREATE TABLE `ai_assistance_settings` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `openai_api_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `openai_model` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_assistance_settings`
--

INSERT INTO `ai_assistance_settings` (`id`, `business_id`, `is_enabled`, `openai_api_key`, `openai_model`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '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', 'gpt-4o-mini', '2026-07-23 08:24:14', '2026-08-04 06:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `barcodes`
--

CREATE TABLE `barcodes` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `width` double(22,4) DEFAULT NULL,
  `height` double(22,4) DEFAULT NULL,
  `paper_width` double(22,4) DEFAULT NULL,
  `paper_height` double(22,4) DEFAULT NULL,
  `top_margin` double(22,4) DEFAULT NULL,
  `left_margin` double(22,4) DEFAULT NULL,
  `row_distance` double(22,4) DEFAULT NULL,
  `col_distance` double(22,4) DEFAULT NULL,
  `stickers_in_one_row` int DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_continuous` tinyint(1) NOT NULL DEFAULT '0',
  `stickers_in_one_sheet` int DEFAULT NULL,
  `business_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barcodes`
--

INSERT INTO `barcodes` (`id`, `name`, `description`, `width`, `height`, `paper_width`, `paper_height`, `top_margin`, `left_margin`, `row_distance`, `col_distance`, `stickers_in_one_row`, `is_default`, `is_continuous`, `stickers_in_one_sheet`, `business_id`, `created_at`, `updated_at`) VALUES
(1, '20 Labels per Sheet', 'Sheet Size: 8.5\" x 11\", Label Size: 4\" x 1\", Labels per sheet: 20', 4.0000, 1.0000, 8.5000, 11.0000, 0.5000, 0.1250, 0.0000, 0.1875, 2, 0, 0, 20, NULL, '2017-12-18 00:13:44', '2017-12-18 00:13:44'),
(2, '30 Labels per sheet', 'Sheet Size: 8.5\" x 11\", Label Size: 2.625\" x 1\", Labels per sheet: 30', 2.6250, 1.0000, 8.5000, 11.0000, 0.5000, 0.1880, 0.0000, 0.1250, 3, 0, 0, 30, NULL, '2017-12-18 00:04:39', '2017-12-18 00:10:40'),
(3, '32 Labels per sheet', 'Sheet Size: 8.5\" x 11\", Label Size: 2\" x 1.25\", Labels per sheet: 32', 2.0000, 1.2500, 8.5000, 11.0000, 0.5000, 0.2500, 0.0000, 0.0000, 4, 0, 0, 32, NULL, '2017-12-17 23:55:40', '2017-12-17 23:55:40'),
(4, '40 Labels per sheet', 'Sheet Size: 8.5\" x 11\", Label Size: 2\" x 1\", Labels per sheet: 40', 2.0000, 1.0000, 8.5000, 11.0000, 0.5000, 0.2500, 0.0000, 0.0000, 4, 0, 0, 40, NULL, '2017-12-17 23:58:40', '2017-12-17 23:58:40'),
(5, '50 Labels per Sheet', 'Sheet Size: 8.5\" x 11\", Label Size: 1.5\" x 1\", Labels per sheet: 50', 1.5000, 1.0000, 8.5000, 11.0000, 0.5000, 0.5000, 0.0000, 0.0000, 5, 0, 0, 50, NULL, '2017-12-17 23:51:10', '2017-12-17 23:51:10'),
(6, 'Continuous Rolls - 31.75mm x 25.4mm', 'Label Size: 31.75mm x 25.4mm, Gap: 3.18mm', 1.2500, 1.0000, 1.2500, 0.0000, 0.1250, 0.0000, 0.1250, 0.0000, 1, 0, 1, NULL, NULL, '2017-12-17 23:51:10', '2017-12-17 23:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `waiter_id` int UNSIGNED DEFAULT NULL,
  `table_id` int UNSIGNED DEFAULT NULL,
  `correspondent_id` int DEFAULT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `booking_start` datetime NOT NULL,
  `booking_end` datetime NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `booking_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `show_in_pos` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `business_id`, `name`, `description`, `show_in_pos`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'American Club', 'American Club', 1, 1, NULL, '2026-07-23 13:17:16', '2026-08-06 05:14:26'),
(2, 1, 'Bugler', 'Bugler', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(3, 1, 'Grabba Leaf', 'Grabba Leaf', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(4, 1, 'Royal Blunts', 'Royal Blunts', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(5, 1, 'Phillies', 'Phillies', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(6, 1, 'Hype', 'Hype', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(7, 1, 'Jungo Leaf', 'Jungo Leaf', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(8, 1, 'Throwback', 'Throwback', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(9, 1, 'Big Dog', 'Big Dog', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(10, 1, 'Hi-Fi', 'Hi-Fi', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(11, 1, 'Artisan Snacks', 'Artisan Snacks', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(12, 1, 'Cocoa House', 'Cocoa House', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(13, 1, 'Citrus Wave', 'Citrus Wave', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(14, 1, 'Fresh Care', 'Fresh Care', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(15, 1, 'Home Basics', 'Home Basics', 1, 1, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16');

-- --------------------------------------------------------

--
-- Table structure for table `business`
--

CREATE TABLE `business` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_id` int UNSIGNED NOT NULL,
  `start_date` date DEFAULT NULL,
  `tax_number_1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_label_1` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number_2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_label_2` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_label_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_label_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_sales_tax` int UNSIGNED DEFAULT NULL,
  `default_profit_percent` double(5,2) NOT NULL DEFAULT '0.00',
  `owner_id` int UNSIGNED NOT NULL,
  `time_zone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Kolkata',
  `fy_start_month` tinyint NOT NULL DEFAULT '1',
  `accounting_method` enum('fifo','lifo','avco') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fifo',
  `default_sales_discount` decimal(5,2) DEFAULT NULL,
  `sell_price_tax` enum('includes','excludes') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'includes',
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku_prefix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_product_expiry` tinyint(1) NOT NULL DEFAULT '0',
  `expiry_type` enum('add_expiry','add_manufacturing') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'add_expiry',
  `on_product_expiry` enum('keep_selling','stop_selling','auto_delete') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'keep_selling',
  `stop_selling_before` int NOT NULL COMMENT 'Stop selling expied item n days before expiry',
  `enable_tooltip` tinyint(1) NOT NULL DEFAULT '1',
  `purchase_in_diff_currency` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Allow purchase to be in different currency then the business currency',
  `purchase_currency_id` int UNSIGNED DEFAULT NULL,
  `p_exchange_rate` decimal(20,3) NOT NULL DEFAULT '1.000',
  `transaction_edit_days` int UNSIGNED NOT NULL DEFAULT '30',
  `stock_expiry_alert_days` int UNSIGNED NOT NULL DEFAULT '30',
  `keyboard_shortcuts` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pos_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `print_per_sale_copies` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `print_per_sale_printer_id` int UNSIGNED DEFAULT NULL,
  `pos_screen_layout` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ltr',
  `weighing_scale_setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'used to store the configuration of weighing scale',
  `enable_brand` tinyint(1) NOT NULL DEFAULT '1',
  `enable_category` tinyint(1) NOT NULL DEFAULT '1',
  `enable_sub_category` tinyint(1) NOT NULL DEFAULT '1',
  `enable_price_tax` tinyint(1) NOT NULL DEFAULT '1',
  `enable_purchase_status` tinyint(1) DEFAULT '1',
  `enable_lot_number` tinyint(1) NOT NULL DEFAULT '0',
  `default_unit` int DEFAULT NULL,
  `enable_sub_units` tinyint(1) NOT NULL DEFAULT '0',
  `enable_racks` tinyint(1) NOT NULL DEFAULT '0',
  `enable_row` tinyint(1) NOT NULL DEFAULT '0',
  `enable_position` tinyint(1) NOT NULL DEFAULT '0',
  `enable_editing_product_from_purchase` tinyint(1) NOT NULL DEFAULT '1',
  `sales_cmsn_agnt` enum('logged_in_user','user','cmsn_agnt') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_addition_method` tinyint(1) NOT NULL DEFAULT '1',
  `enable_inline_tax` tinyint(1) NOT NULL DEFAULT '1',
  `currency_symbol_placement` enum('before','after') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'before',
  `enabled_modules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_format` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'm/d/Y',
  `time_format` enum('12','24') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '24',
  `currency_precision` tinyint NOT NULL DEFAULT '2',
  `quantity_precision` tinyint NOT NULL DEFAULT '2',
  `ref_no_prefixes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `theme_color` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `enable_rp` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'rp is the short form of reward points',
  `rp_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'rp is the short form of reward points',
  `amount_for_unit_rp` decimal(22,4) NOT NULL DEFAULT '1.0000' COMMENT 'rp is the short form of reward points',
  `min_order_total_for_rp` decimal(22,4) NOT NULL DEFAULT '1.0000' COMMENT 'rp is the short form of reward points',
  `max_rp_per_order` int DEFAULT NULL COMMENT 'rp is the short form of reward points',
  `redeem_amount_per_unit_rp` decimal(22,4) NOT NULL DEFAULT '1.0000' COMMENT 'rp is the short form of reward points',
  `min_order_total_for_redeem` decimal(22,4) NOT NULL DEFAULT '1.0000' COMMENT 'rp is the short form of reward points',
  `min_redeem_point` int DEFAULT NULL COMMENT 'rp is the short form of reward points',
  `max_redeem_point` int DEFAULT NULL COMMENT 'rp is the short form of reward points',
  `rp_expiry_period` int DEFAULT NULL COMMENT 'rp is the short form of reward points',
  `rp_expiry_type` enum('month','year') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'year' COMMENT 'rp is the short form of reward points',
  `email_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sms_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `custom_labels` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `common_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `crm_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business`
--

INSERT INTO `business` (`id`, `name`, `currency_id`, `start_date`, `tax_number_1`, `tax_label_1`, `tax_number_2`, `tax_label_2`, `code_label_1`, `code_1`, `code_label_2`, `code_2`, `default_sales_tax`, `default_profit_percent`, `owner_id`, `time_zone`, `fy_start_month`, `accounting_method`, `default_sales_discount`, `sell_price_tax`, `logo`, `sku_prefix`, `enable_product_expiry`, `expiry_type`, `on_product_expiry`, `stop_selling_before`, `enable_tooltip`, `purchase_in_diff_currency`, `purchase_currency_id`, `p_exchange_rate`, `transaction_edit_days`, `stock_expiry_alert_days`, `keyboard_shortcuts`, `pos_settings`, `print_per_sale_copies`, `print_per_sale_printer_id`, `pos_screen_layout`, `weighing_scale_setting`, `enable_brand`, `enable_category`, `enable_sub_category`, `enable_price_tax`, `enable_purchase_status`, `enable_lot_number`, `default_unit`, `enable_sub_units`, `enable_racks`, `enable_row`, `enable_position`, `enable_editing_product_from_purchase`, `sales_cmsn_agnt`, `item_addition_method`, `enable_inline_tax`, `currency_symbol_placement`, `enabled_modules`, `date_format`, `time_format`, `currency_precision`, `quantity_precision`, `ref_no_prefixes`, `theme_color`, `created_by`, `enable_rp`, `rp_name`, `amount_for_unit_rp`, `min_order_total_for_rp`, `max_rp_per_order`, `redeem_amount_per_unit_rp`, `min_order_total_for_redeem`, `min_redeem_point`, `max_redeem_point`, `rp_expiry_period`, `rp_expiry_type`, `email_settings`, `sms_settings`, `custom_labels`, `common_settings`, `crm_settings`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Japs POS', 124, '1970-01-01', NULL, 'Tax ID', NULL, NULL, NULL, NULL, NULL, NULL, 1, 25.00, 1, 'America/New_York', 1, 'fifo', 0.00, 'includes', '1784402430_ny-logo.png', NULL, 0, 'add_expiry', 'keep_selling', 0, 1, 0, NULL, 1.000, 30, 30, '{\"pos\":{\"express_checkout\":\"shift+e\",\"pay_n_ckeckout\":\"shift+p\",\"draft\":\"shift+d\",\"cancel\":\"shift+c\",\"recent_product_quantity\":\"f2\",\"weighing_scale\":null,\"edit_discount\":\"shift+i\",\"edit_order_tax\":\"shift+t\",\"add_payment_row\":\"shift+r\",\"finalize_payment\":\"shift+f\",\"add_new_product\":\"f4\"}}', '{\"disable_pay_checkout\":1,\"disable_express_checkout\":1,\"enable_transaction_date\":1,\"enable_pwa\":1,\"disable_draft\":0,\"hide_product_suggestion\":0,\"hide_recent_trans\":0,\"disable_discount\":0,\"disable_order_tax\":0,\"is_pos_subtotal_editable\":0,\"disable_suspend\":0,\"inline_service_staff\":0,\"is_service_staff_required\":0,\"disable_credit_sale_button\":0,\"enable_weighing_scale\":0,\"show_invoice_scheme\":0,\"show_invoice_layout\":0,\"print_on_suspend\":0,\"show_pricing_on_product_sugesstion\":0,\"pwa_app_name\":\"Japs POS\",\"pwa_short_name\":\"POS\",\"pwa_description\":\"Point of Sale system\",\"pwa_theme_color\":\"#0f0f0f\",\"pwa_background_color\":\"#0f0f0f\",\"pwa_icon\":null,\"allow_overselling\":1,\"enable_sales_order\":1,\"cmmsn_calculation_type\":\"invoice_value\",\"enable_cash_denomination_on\":\"pos_screen\",\"amount_rounding_method\":null,\"display_screen_heading\":null,\"cash_denominations\":null,\"razor_pay_key_id\":null,\"razor_pay_key_secret\":null,\"stripe_public_key\":null,\"stripe_secret_key\":null,\"pos_visible_category_ids\":[10,12,11,9,1],\"pos_visible_brand_ids\":[]}', 1, NULL, 'ltr', '{\"label_prefix\":null,\"product_sku_length\":\"5\",\"qty_length\":\"4\",\"qty_length_decimal\":\"3\"}', 1, 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, NULL, 1, 1, 'before', '[\"purchases\",\"add_sale\",\"pos_sale\",\"stock_transfers\",\"stock_adjustment\",\"expenses\"]', 'm/d/Y', '24', 2, 2, '{\"purchase\":\"PO\",\"purchase_return\":null,\"purchase_requisition\":null,\"purchase_order\":null,\"stock_transfer\":\"ST\",\"stock_adjustment\":\"SA\",\"sell_return\":\"CN\",\"expense\":\"EP\",\"contacts\":\"CO\",\"purchase_payment\":\"PP\",\"sell_payment\":\"SP\",\"expense_payment\":null,\"business_location\":\"BL\",\"username\":null,\"subscription\":null,\"draft\":null,\"sales_order\":null}', NULL, NULL, 0, NULL, 1.0000, 1.0000, NULL, 1.0000, 1.0000, NULL, NULL, NULL, 'year', '{\"mail_driver\":\"smtp\",\"mail_host\":null,\"mail_port\":null,\"mail_username\":null,\"mail_password\":null,\"mail_encryption\":null,\"mail_from_address\":null,\"mail_from_name\":null}', '{\"sms_service\":\"other\",\"nexmo_key\":null,\"nexmo_secret\":null,\"nexmo_from\":null,\"twilio_sid\":null,\"twilio_token\":null,\"twilio_from\":null,\"url\":null,\"send_to_param_name\":\"to\",\"send_to_param_type\":\"string\",\"msg_param_name\":\"text\",\"request_method\":\"post\",\"data_parameter_type\":\"form-data\",\"header_1\":null,\"header_val_1\":null,\"header_2\":null,\"header_val_2\":null,\"header_3\":null,\"header_val_3\":null,\"param_1\":null,\"param_val_1\":null,\"param_2\":null,\"param_val_2\":null,\"param_3\":null,\"param_val_3\":null,\"param_4\":null,\"param_val_4\":null,\"param_5\":null,\"param_val_5\":null,\"param_6\":null,\"param_val_6\":null,\"param_7\":null,\"param_val_7\":null,\"param_8\":null,\"param_val_8\":null,\"param_9\":null,\"param_val_9\":null,\"param_10\":null,\"param_val_10\":null}', '{\"payments\":{\"custom_pay_1\":null,\"custom_pay_2\":null,\"custom_pay_3\":null,\"custom_pay_4\":null,\"custom_pay_5\":null,\"custom_pay_6\":null,\"custom_pay_7\":null},\"contact\":{\"custom_field_1\":\"MSA Class Of Trade\",\"custom_field_2\":\"MSA Store #\",\"custom_field_3\":\"MSA Cash And Carry (Y\\/N)\",\"custom_field_4\":null,\"custom_field_5\":null,\"custom_field_6\":null,\"custom_field_7\":null,\"custom_field_8\":null,\"custom_field_9\":null,\"custom_field_10\":null},\"product\":{\"custom_field_1\":\"MSA Items Per Selling Unit\",\"custom_field_2\":\"MSA Category Code\",\"custom_field_3\":\"MSA Promo Code\",\"custom_field_4\":\"MSA Promo Description\",\"custom_field_5\":null,\"custom_field_6\":null,\"custom_field_7\":null,\"custom_field_8\":null,\"custom_field_9\":null,\"custom_field_10\":null,\"custom_field_11\":null,\"custom_field_12\":null,\"custom_field_13\":null,\"custom_field_14\":null,\"custom_field_15\":null,\"custom_field_16\":null,\"custom_field_17\":null,\"custom_field_18\":null,\"custom_field_19\":null,\"custom_field_20\":null},\"product_cf_details\":{\"1\":{\"type\":\"dropdown\",\"dropdown_options\":\"200\\r\\n5\\r\\n10\\r\\n20\\r\\n25\\r\\n40\\r\\n60\\r\\n100\\r\\n250\"},\"2\":{\"type\":\"dropdown\",\"dropdown_options\":\"003231\\r\\n003211\\r\\n003251\\r\\n003252\\r\\n003292\\r\\n003221\\r\\n003241\\r\\n003213\\r\\n003212\\r\\n003214\\r\\n003215\\r\\n003227\"},\"3\":{\"type\":\"text\",\"dropdown_options\":null},\"4\":{\"type\":\"text\",\"dropdown_options\":null},\"5\":{\"type\":null,\"dropdown_options\":null},\"6\":{\"type\":null,\"dropdown_options\":null},\"7\":{\"type\":null,\"dropdown_options\":null},\"8\":{\"type\":null,\"dropdown_options\":null},\"9\":{\"type\":null,\"dropdown_options\":null},\"10\":{\"type\":null,\"dropdown_options\":null},\"11\":{\"type\":null,\"dropdown_options\":null},\"12\":{\"type\":null,\"dropdown_options\":null},\"13\":{\"type\":null,\"dropdown_options\":null},\"14\":{\"type\":null,\"dropdown_options\":null},\"15\":{\"type\":null,\"dropdown_options\":null},\"16\":{\"type\":null,\"dropdown_options\":null},\"17\":{\"type\":null,\"dropdown_options\":null},\"18\":{\"type\":null,\"dropdown_options\":null},\"19\":{\"type\":null,\"dropdown_options\":null},\"20\":{\"type\":null,\"dropdown_options\":null}},\"location\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"user\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"purchase\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"purchase_shipping\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null,\"custom_field_5\":null},\"sell\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"shipping\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null,\"custom_field_5\":null},\"types_of_service\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null,\"custom_field_5\":null,\"custom_field_6\":null}}', '{\"default_credit_limit\":null,\"default_datatable_page_entries\":\"25\"}', NULL, 1, '2026-06-04 16:29:53', '2026-08-12 07:12:38');

-- --------------------------------------------------------

--
-- Table structure for table `business_locations`
--

CREATE TABLE `business_locations` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `location_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_scheme_id` int UNSIGNED NOT NULL,
  `sale_invoice_scheme_id` int DEFAULT NULL,
  `invoice_layout_id` int UNSIGNED NOT NULL,
  `sale_invoice_layout_id` int DEFAULT NULL,
  `selling_price_group_id` int DEFAULT NULL,
  `print_receipt_on_invoice` tinyint(1) DEFAULT '1',
  `receipt_printer_type` enum('browser','printer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'browser',
  `printer_id` int DEFAULT NULL,
  `mobile` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternate_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured_products` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `default_payment_accounts` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `custom_field1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_locations`
--

INSERT INTO `business_locations` (`id`, `business_id`, `location_id`, `name`, `landmark`, `country`, `state`, `city`, `zip_code`, `invoice_scheme_id`, `sale_invoice_scheme_id`, `invoice_layout_id`, `sale_invoice_layout_id`, `selling_price_group_id`, `print_receipt_on_invoice`, `receipt_printer_type`, `printer_id`, `mobile`, `alternate_number`, `email`, `website`, `featured_products`, `is_active`, `default_payment_accounts`, `custom_field1`, `custom_field2`, `custom_field3`, `custom_field4`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'BL0001', 'Japs POS', 'New York', 'USA', 'New York', 'New York', '11011', 1, 1, 1, 1, NULL, 1, 'browser', NULL, NULL, NULL, NULL, NULL, NULL, 1, '{\"cash\":{\"is_enabled\":\"1\"},\"card\":{\"is_enabled\":\"1\"},\"cheque\":{\"is_enabled\":\"1\"},\"bank_transfer\":{\"is_enabled\":\"1\"},\"other\":{\"is_enabled\":\"1\"}}', NULL, NULL, NULL, NULL, NULL, '2026-06-04 16:29:55', '2026-08-05 15:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `cash_denominations`
--

CREATE TABLE `cash_denominations` (
  `id` bigint UNSIGNED NOT NULL,
  `business_id` int NOT NULL,
  `amount` decimal(22,4) NOT NULL,
  `total_count` int NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_registers`
--

CREATE TABLE `cash_registers` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `location_id` int DEFAULT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `status` enum('close','open') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `closed_at` datetime DEFAULT NULL,
  `closing_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `total_card_slips` int NOT NULL DEFAULT '0',
  `total_cheques` int NOT NULL DEFAULT '0',
  `denominations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `closing_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cash_registers`
--

INSERT INTO `cash_registers` (`id`, `business_id`, `location_id`, `user_id`, `status`, `closed_at`, `closing_amount`, `total_card_slips`, `total_cheques`, `denominations`, `closing_note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'close', '2026-07-24 14:58:11', 0.0000, 0, 0, NULL, NULL, '2026-07-20 08:23:00', '2026-07-24 08:58:11'),
(2, 1, 1, 1, 'open', NULL, 0.0000, 0, 0, NULL, NULL, '2026-07-24 08:58:00', '2026-07-24 08:58:19');

-- --------------------------------------------------------

--
-- Table structure for table `cash_register_transactions`
--

CREATE TABLE `cash_register_transactions` (
  `id` int UNSIGNED NOT NULL,
  `cash_register_id` int UNSIGNED NOT NULL,
  `amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `pay_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('debit','credit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` int DEFAULT NULL,
  `transaction_payment_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cash_register_transactions`
--

INSERT INTO `cash_register_transactions` (`id`, `cash_register_id`, `amount`, `pay_method`, `type`, `transaction_type`, `transaction_id`, `transaction_payment_id`, `created_at`, `updated_at`) VALUES
(3, 2, 12.0700, 'cash', 'credit', 'sell', 14, NULL, '2026-07-24 10:13:56', '2026-07-24 10:13:56'),
(4, 2, 3.0000, 'cash', 'credit', 'sell', 16, NULL, '2026-07-24 10:28:17', '2026-07-24 10:28:17'),
(5, 2, 10.1700, 'cash', 'credit', 'sell', 16, NULL, '2026-07-24 10:28:17', '2026-07-24 10:28:17'),
(6, 2, 26.3400, 'cash', 'credit', 'advance_payment', NULL, 6, '2026-07-24 10:28:43', '2026-07-24 10:28:43'),
(9, 2, 21.9500, 'cash', 'credit', 'sell', 19, NULL, '2026-07-27 05:34:00', '2026-07-27 05:34:00'),
(12, 2, 6.0300, 'cash', 'credit', 'sell', 42, NULL, '2026-07-27 05:43:40', '2026-07-27 05:43:40'),
(13, 2, 355.4900, 'cash', 'credit', 'sell', 43, NULL, '2026-07-27 05:44:05', '2026-07-27 05:44:05'),
(14, 2, 4.9900, 'cash', 'credit', 'sell', 48, NULL, '2026-07-27 06:19:24', '2026-07-27 06:19:24'),
(15, 2, 11.0000, 'cash', 'credit', 'sell', 49, NULL, '2026-07-27 06:19:44', '2026-07-27 06:19:44'),
(16, 2, 11.0000, 'cash', 'credit', 'sell', 52, NULL, '2026-07-27 06:20:48', '2026-07-27 06:20:48'),
(17, 2, 4.9900, 'cash', 'credit', 'sell', 55, NULL, '2026-07-27 06:26:44', '2026-07-27 06:26:44'),
(18, 2, 16.4600, 'cash', 'credit', 'sell', 56, NULL, '2026-07-27 06:26:55', '2026-07-27 06:26:55'),
(19, 2, 7.6100, 'cash', 'credit', 'sell', 57, NULL, '2026-07-27 06:49:08', '2026-07-27 06:49:08'),
(20, 2, 11.0000, 'card', 'credit', 'sell', 58, NULL, '2026-07-27 06:49:15', '2026-07-27 06:49:15'),
(21, 2, 6.0000, 'cash', 'credit', 'sell', 59, NULL, '2026-07-27 08:41:32', '2026-07-27 08:41:32'),
(22, 2, 12.0000, 'cash', 'credit', 'sell', 60, NULL, '2026-07-27 08:45:04', '2026-07-27 08:45:04'),
(23, 2, 8.2200, 'cash', 'credit', 'sell', 46, NULL, '2026-07-27 08:45:30', '2026-07-27 08:45:30'),
(24, 2, 7.4900, 'cash', 'credit', 'sell', 61, NULL, '2026-07-27 08:47:43', '2026-07-27 08:47:43'),
(25, 2, 14.5000, 'cash', 'credit', 'sell', 63, NULL, '2026-07-27 08:50:38', '2026-07-27 08:50:38'),
(26, 2, 21.4900, 'cash', 'credit', 'sell', 73, NULL, '2026-07-27 09:30:54', '2026-07-27 09:30:54'),
(27, 2, 9.4900, 'cash', 'credit', 'sell', 74, NULL, '2026-07-27 09:35:46', '2026-07-27 09:35:46'),
(28, 2, 12.0000, 'cash', 'credit', 'sell', 75, NULL, '2026-07-27 09:38:36', '2026-07-27 09:38:36'),
(29, 2, 46.4900, 'cash', 'credit', 'sell', 76, NULL, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(30, 2, 22.9900, 'cash', 'credit', 'sell', 77, NULL, '2026-07-27 09:42:01', '2026-07-27 09:42:01'),
(31, 2, 12.0000, 'cash', 'credit', 'sell', 78, NULL, '2026-07-27 10:02:21', '2026-07-27 10:02:21'),
(32, 2, 12.0000, 'cash', 'credit', 'sell', 79, NULL, '2026-07-27 10:02:26', '2026-07-27 10:02:26'),
(33, 2, 20.9900, 'cash', 'credit', 'sell', 80, NULL, '2026-07-27 10:03:19', '2026-07-27 10:03:19'),
(34, 2, 12.0000, 'cash', 'credit', 'sell', 83, NULL, '2026-07-27 10:11:23', '2026-07-27 10:11:23'),
(35, 2, 4.9900, 'cash', 'credit', 'sell', 84, NULL, '2026-07-27 10:11:42', '2026-07-27 10:11:42'),
(36, 2, 12.0000, 'cash', 'credit', 'sell', 86, NULL, '2026-07-27 10:21:29', '2026-07-27 10:21:29'),
(37, 2, 8.9900, 'card', 'credit', 'sell', 87, NULL, '2026-07-28 08:11:08', '2026-07-28 08:11:08'),
(38, 2, 92.0700, 'cash', 'credit', 'sell', 88, NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(39, 2, 8.9900, 'cash', 'credit', 'sell', 89, NULL, '2026-08-03 05:36:11', '2026-08-03 05:36:11'),
(40, 2, 21.4900, 'cash', 'credit', 'sell', 93, NULL, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(41, 2, 10.0000, 'cash', 'credit', 'sell', 95, NULL, '2026-08-03 06:02:12', '2026-08-03 06:02:12'),
(42, 2, 20.6700, 'cash', 'credit', 'sell', 97, NULL, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(43, 2, 13.2000, 'cash', 'credit', 'advance_payment', NULL, 44, '2026-08-03 07:14:52', '2026-08-03 07:14:52'),
(44, 2, 13.2000, 'cash', 'credit', 'sell', 100, NULL, '2026-08-03 07:23:58', '2026-08-03 07:23:58'),
(45, 2, 5.4900, 'card', 'credit', 'sell', 103, NULL, '2026-08-03 07:32:08', '2026-08-03 07:32:08'),
(46, 2, 9.8900, 'cash', 'credit', 'sell', 105, NULL, '2026-08-03 07:42:48', '2026-08-03 07:42:48'),
(47, 2, 9.8900, 'cash', 'credit', 'sell', 104, NULL, '2026-08-03 07:57:05', '2026-08-03 07:57:05'),
(48, 2, 9.8900, 'cash', 'credit', 'sell', 106, NULL, '2026-08-03 08:34:31', '2026-08-03 08:34:31'),
(49, 2, 45.4900, 'cash', 'credit', 'sell', 107, NULL, '2026-08-03 08:42:12', '2026-08-03 08:42:12'),
(50, 2, 9.8900, 'cash', 'credit', 'sell', 108, NULL, '2026-08-03 08:45:04', '2026-08-03 08:45:04'),
(51, 2, 59.8900, 'cash', 'credit', 'sell', 109, NULL, '2026-08-03 08:46:35', '2026-08-03 08:46:35'),
(52, 2, 10.4400, 'cash', 'credit', 'sell', 110, NULL, '2026-08-03 08:47:42', '2026-08-03 08:47:42'),
(53, 2, 5.4900, 'cash', 'credit', 'sell', 111, NULL, '2026-08-03 09:07:44', '2026-08-03 09:07:44'),
(54, 2, 5.4900, 'cash', 'credit', 'sell', 113, NULL, '2026-08-03 09:20:11', '2026-08-03 09:20:11'),
(55, 2, 71.5600, 'cash', 'credit', 'sell', 116, NULL, '2026-08-03 09:33:47', '2026-08-03 09:33:47'),
(56, 2, 20.7800, 'cash', 'credit', 'sell', 117, NULL, '2026-08-03 09:39:11', '2026-08-03 09:39:11'),
(57, 2, 13.2000, 'card', 'credit', 'sell', 118, NULL, '2026-08-03 09:39:32', '2026-08-03 09:39:32'),
(58, 2, 5.4900, 'cash', 'credit', 'sell', 119, NULL, '2026-08-03 09:40:47', '2026-08-03 09:40:47'),
(59, 2, 5.4900, 'cash', 'credit', 'sell', 120, NULL, '2026-08-03 09:43:42', '2026-08-03 09:43:42'),
(60, 2, 5.4900, 'cash', 'credit', 'sell', 121, NULL, '2026-08-03 10:33:09', '2026-08-03 10:33:09'),
(61, 2, 5.4900, 'cash', 'credit', 'sell', 122, NULL, '2026-08-03 10:35:05', '2026-08-03 10:35:05'),
(62, 2, 13.2000, 'cash', 'credit', 'sell', 123, NULL, '2026-08-03 10:45:50', '2026-08-03 10:45:50'),
(63, 2, 13.2000, 'cash', 'credit', 'sell', 124, NULL, '2026-08-03 10:47:11', '2026-08-03 10:47:11'),
(64, 2, 50.0000, 'cash', 'credit', 'sell', 125, NULL, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(65, 2, -36.8000, 'card', 'credit', 'sell', 125, NULL, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(66, 2, 5.0000, 'cash', 'credit', 'sell', 126, NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(67, 2, 30.0000, 'card', 'credit', 'sell', 126, NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(68, 2, 30.3800, 'cheque', 'credit', 'sell', 126, NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(69, 2, 13.2000, 'cash', 'credit', 'advance_payment', NULL, 73, '2026-08-04 08:45:04', '2026-08-04 08:45:04'),
(70, 2, 13.2000, 'cash', 'credit', 'sell', 127, NULL, '2026-08-04 08:46:22', '2026-08-04 08:46:22'),
(71, 2, 5.4900, 'cash', 'credit', 'sell', 128, NULL, '2026-08-05 04:25:37', '2026-08-05 04:25:37'),
(72, 2, 13.2000, 'cash', 'credit', 'sell', 129, NULL, '2026-08-05 05:40:29', '2026-08-05 05:40:29'),
(73, 2, 5.4900, 'cash', 'credit', 'sell', 130, NULL, '2026-08-05 05:40:59', '2026-08-05 05:40:59'),
(74, 2, 6.0400, 'cash', 'credit', 'sell', 131, NULL, '2026-08-05 06:32:36', '2026-08-05 06:32:36'),
(75, 2, 5.4900, 'cash', 'credit', 'sell', 132, NULL, '2026-08-05 08:03:09', '2026-08-05 08:03:09'),
(76, 2, 15.3800, 'cheque', 'credit', 'sell', 133, NULL, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(77, 2, 4.7100, 'cash', 'credit', 'sell', 134, NULL, '2026-08-06 05:51:04', '2026-08-06 05:51:04'),
(78, 2, 9.8900, 'cash', 'credit', 'sell', 135, NULL, '2026-08-07 05:48:53', '2026-08-07 05:48:53'),
(79, 2, 9.8900, 'cash', 'credit', 'sell', 137, NULL, '2026-08-12 06:35:00', '2026-08-12 06:35:00'),
(80, 2, 140.2500, 'cash', 'credit', 'sell', 138, NULL, '2026-08-12 06:35:18', '2026-08-12 06:35:18'),
(81, 2, 163.8900, 'cash', 'credit', 'sell', 139, NULL, '2026-08-12 06:35:38', '2026-08-12 06:35:38'),
(82, 2, 9.8900, 'card', 'credit', 'sell', 140, NULL, '2026-08-12 07:06:38', '2026-08-12 07:06:38'),
(83, 2, 396.0000, 'cash', 'credit', 'sell', 141, NULL, '2026-08-12 07:13:57', '2026-08-12 07:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `short_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `category_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_in_pos` tinyint(1) NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `business_id`, `short_code`, `parent_id`, `created_by`, `category_type`, `show_in_pos`, `description`, `slug`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Tobacco', 1, 'TOB', 0, 1, 'product', 1, NULL, NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(2, 'Cigarettes', 1, 'CIG', 1, 1, 'product', 1, 'MSA category code 003231', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(3, 'Moist Spit Tobacco', 1, 'MST', 1, 1, 'product', 1, 'MSA category code 003211', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(4, 'Cigars', 1, 'CGR', 1, 1, 'product', 1, 'MSA category code 003251', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(5, 'Little Filtered Cigars', 1, 'LFC', 1, 1, 'product', 1, 'MSA category code 003252', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(6, 'E-Cigarettes', 1, 'ECG', 1, 1, 'product', 1, 'MSA category code 003292', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(7, 'RYO Tobacco', 1, 'RYO', 1, 1, 'product', 1, 'MSA category code 003221', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(8, 'Pipe Tobacco', 1, 'PIP', 1, 1, 'product', 1, 'MSA category code 003241', NULL, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(9, 'Snacks', 1, 'snacks', 0, 1, 'product', 1, NULL, NULL, NULL, '2026-07-22 17:13:01', '2026-07-22 17:13:01'),
(10, 'Beverages', 1, 'beverages', 0, 1, 'product', 1, NULL, NULL, NULL, '2026-07-22 17:13:01', '2026-07-22 17:13:01'),
(11, 'Personal Care', 1, 'personal-care', 0, 1, 'product', 1, NULL, NULL, NULL, '2026-07-22 17:13:01', '2026-07-22 17:13:01'),
(12, 'Household', 1, 'household', 0, 1, 'product', 1, NULL, NULL, NULL, '2026-07-22 17:13:01', '2026-08-06 05:13:43'),
(13, 'Chips & Cookies', 1, 'chips-cookies', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(14, 'Chocolate', 1, 'chocolate', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(15, 'Gum', 1, 'gum', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(16, 'Gummies & Chews', 1, 'gummies-chews', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(17, 'Lollipops', 1, 'lollipops', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(18, 'Mints', 1, 'mints', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(19, 'Peg Bag', 1, 'peg-bag', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(20, '5lb Bag', 1, '5lb-bag', 9, 1, 'product', 1, NULL, NULL, NULL, '2026-07-23 13:17:16', '2026-07-23 13:17:16'),
(21, 'Website', 1, NULL, 0, 1, 'crm_source', 1, NULL, NULL, NULL, '2026-07-23 16:50:42', '2026-07-23 16:50:42'),
(22, 'Referral', 1, NULL, 0, 1, 'crm_source', 1, NULL, NULL, NULL, '2026-07-23 16:50:42', '2026-07-23 16:50:42'),
(23, 'Walk-in', 1, NULL, 0, 1, 'crm_source', 1, NULL, NULL, NULL, '2026-07-23 16:50:42', '2026-07-23 16:50:42'),
(24, 'Phone', 1, NULL, 0, 1, 'crm_source', 1, NULL, NULL, NULL, '2026-07-23 16:50:42', '2026-07-23 16:50:42'),
(25, 'Social Media', 1, NULL, 0, 1, 'crm_source', 1, NULL, NULL, NULL, '2026-07-23 16:50:42', '2026-07-23 16:50:42'),
(26, 'New', 1, NULL, 0, 1, 'crm_life_stage', 1, NULL, NULL, NULL, '2026-07-23 16:50:43', '2026-07-23 16:50:43'),
(27, 'Contacted', 1, NULL, 0, 1, 'crm_life_stage', 1, NULL, NULL, NULL, '2026-07-23 16:50:43', '2026-07-23 16:50:43'),
(28, 'Qualified', 1, NULL, 0, 1, 'crm_life_stage', 1, NULL, NULL, NULL, '2026-07-23 16:50:43', '2026-07-23 16:50:43'),
(29, 'Proposal', 1, NULL, 0, 1, 'crm_life_stage', 1, NULL, NULL, NULL, '2026-07-23 16:50:43', '2026-07-23 16:50:43'),
(30, 'Won', 1, NULL, 0, 1, 'crm_life_stage', 1, NULL, NULL, NULL, '2026-07-23 16:50:43', '2026-07-23 16:50:43'),
(31, 'Lost', 1, NULL, 0, 1, 'crm_life_stage', 1, NULL, NULL, NULL, '2026-07-23 16:50:43', '2026-07-23 16:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `categorizables`
--

CREATE TABLE `categorizables` (
  `category_id` int NOT NULL,
  `categorizable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categorizable_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land_mark` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_business_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ecom_first_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ecom_last_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `contact_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `crm_source` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crm_life_stage` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `address_line_2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `zip_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `mobile` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `landline` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alternate_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_term_number` int DEFAULT NULL,
  `pay_term_type` enum('days','months') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_limit` decimal(22,4) DEFAULT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `balance` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `total_rp` int NOT NULL DEFAULT '0' COMMENT 'rp is the short form of reward points',
  `total_rp_used` int NOT NULL DEFAULT '0' COMMENT 'rp is the short form of reward points',
  `total_rp_expired` int NOT NULL DEFAULT '0' COMMENT 'rp is the short form of reward points',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `shipping_custom_field_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_export` tinyint(1) NOT NULL DEFAULT '0',
  `export_custom_field_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_custom_field_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_custom_field_3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_custom_field_4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_custom_field_5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `export_custom_field_6` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_group_id` int DEFAULT NULL,
  `custom_field1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field6` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field7` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field8` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field9` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field10` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `business_id`, `type`, `contact_type`, `land_mark`, `street_name`, `building_number`, `additional_number`, `supplier_business_name`, `name`, `prefix`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `ecom_first_name`, `ecom_last_name`, `email_verified_at`, `contact_id`, `contact_status`, `crm_source`, `crm_life_stage`, `tax_number`, `city`, `state`, `country`, `address_line_1`, `address_line_2`, `zip_code`, `dob`, `mobile`, `landline`, `alternate_number`, `pay_term_number`, `pay_term_type`, `credit_limit`, `created_by`, `balance`, `total_rp`, `total_rp_used`, `total_rp_expired`, `is_default`, `shipping_address`, `shipping_custom_field_details`, `is_export`, `export_custom_field_1`, `export_custom_field_2`, `export_custom_field_3`, `export_custom_field_4`, `export_custom_field_5`, `export_custom_field_6`, `position`, `customer_group_id`, `custom_field1`, `custom_field2`, `custom_field3`, `custom_field4`, `custom_field5`, `custom_field6`, `custom_field7`, `custom_field8`, `custom_field9`, `custom_field10`, `deleted_at`, `created_at`, `updated_at`, `remember_token`) VALUES
(1, 1, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 'Walk-In Customer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CO0001', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 1, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', NULL, 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 18:56:53', '2026-07-18 18:56:54', NULL),
(2, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'Nashville Corner Mart', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CO0002', 'active', NULL, NULL, NULL, 'Nashville', 'TN', 'USA', '501 Broadway', NULL, '37203', NULL, '6151110001', NULL, NULL, NULL, NULL, 500.0000, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', '1', 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-08-03 07:27:34', NULL),
(3, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'East Nashville Tobacco', 'East Nashville Tobacco', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CO0003', 'active', NULL, NULL, NULL, 'Nashville', 'TN', 'USA', '1220 Gallatin Ave', NULL, '37206', NULL, '6151110002', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', '2', 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL),
(4, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'Midtown Market', 'Midtown Market', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CO0004', 'active', NULL, NULL, NULL, 'Nashville', 'TN', 'USA', '2100 West End Ave', NULL, '37203', NULL, '6151110003', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', '3', 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL),
(5, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'MR Wholesale Distributor', 'MR Wholesale Distributor', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'CO0005', 'active', NULL, NULL, NULL, 'Nashville', 'TN', 'USA', '300 Industrial Blvd', NULL, '37209', NULL, '6151110007', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'DISTRIBUTOR', '7', 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL),
(6, 1, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 'Nash 333', NULL, NULL, NULL, NULL, 'admin@gmail.com', NULL, 'Nash', '333', NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '333', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', NULL, 'N', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-22 13:10:15', '2026-07-24 15:59:34', NULL),
(7, 1, 'customer', NULL, NULL, NULL, NULL, NULL, 'TaShya Mercado', 'Owen Cummings', NULL, NULL, NULL, NULL, 'dojujemyka@mailinator.com', '$2y$10$7FA.UNmoEn4AYWOiDExQFuXoMOVUi6NP3T2un/fgINgN3JCU.CbcW', 'Owen', 'Cummings', NULL, NULL, 'active', NULL, NULL, '459', '33', 'IN', 'United States', '26 Wood Ave Mansfield, USA', '333', '333', NULL, 'Atque architecto nul', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', 'dojujemyka@mailinator.com', 'N', 'NE 2026-07-23', 'Officiis ut quae cor', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 13:14:17', '2026-07-24 15:59:34', NULL),
(8, 1, 'customer', NULL, NULL, NULL, NULL, NULL, 'Demo Wholesale LLC', 'Demo Buyer', NULL, NULL, NULL, NULL, 'demo@japswholesale.test', '$2y$10$2QuNvkrQguCAl0evtRI.DO24k46QOBFDvPDXe40egzjn9/s5NO6BW', 'Demo', 'Buyer', NULL, NULL, 'active', NULL, NULL, 'TAX-DEMO-001', 'Dallas', 'TX', 'United States', '100 Demo Street', NULL, '75001', NULL, '5551234567', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', NULL, 'N', NULL, NULL, 'approved', NULL, NULL, NULL, NULL, NULL, '2026-07-23 13:57:24', '2026-07-24 15:59:34', NULL),
(9, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'Yen Mcfarland', 'Et consequatur Null Jonah Fritz Gross Whitaker', 'Et consequatur Null', 'Jonah', 'Fritz Gross', 'Whitaker', 'riraqahu@mailinator.com', NULL, NULL, NULL, NULL, 'Aliqua Ducimus mol', 'active', '22', '29', NULL, 'Minim occaecat qui d', 'Excepteur voluptatem', NULL, '247 First Boulevard', NULL, NULL, NULL, 'Minima ut culpa nec', 'Debitis occaecat ex', NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-23 07:29:09', '2026-07-23 07:27:00', '2026-07-23 07:29:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `crm_call_logs`
--

CREATE TABLE `crm_call_logs` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `called_on` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_lead_users`
--

CREATE TABLE `crm_lead_users` (
  `id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_schedules`
--

CREATE TABLE `crm_schedules` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `start_datetime` datetime DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int UNSIGNED NOT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thousand_separator` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `decimal_separator` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `country`, `currency`, `code`, `symbol`, `thousand_separator`, `decimal_separator`, `created_at`, `updated_at`) VALUES
(1, 'Albania', 'Leke', 'ALL', 'Lek', ',', '.', NULL, NULL),
(2, 'America', 'Dollars', 'USD', '$', ',', '.', NULL, NULL),
(3, 'Afghanistan', 'Afghanis', 'AF', '؋', ',', '.', NULL, NULL),
(4, 'Argentina', 'Pesos', 'ARS', '$', ',', '.', NULL, NULL),
(5, 'Aruba', 'Guilders', 'AWG', 'ƒ', ',', '.', NULL, NULL),
(6, 'Australia', 'Dollars', 'AUD', '$', ',', '.', NULL, NULL),
(7, 'Azerbaijan', 'New Manats', 'AZ', 'ман', ',', '.', NULL, NULL),
(8, 'Bahamas', 'Dollars', 'BSD', '$', ',', '.', NULL, NULL),
(9, 'Barbados', 'Dollars', 'BBD', '$', ',', '.', NULL, NULL),
(10, 'Belarus', 'Rubles', 'BYR', 'p.', ',', '.', NULL, NULL),
(11, 'Belgium', 'Euro', 'EUR', '€', ',', '.', NULL, NULL),
(12, 'Beliz', 'Dollars', 'BZD', 'BZ$', ',', '.', NULL, NULL),
(13, 'Bermuda', 'Dollars', 'BMD', '$', ',', '.', NULL, NULL),
(14, 'Bolivia', 'Bolivianos', 'BOB', '$b', ',', '.', NULL, NULL),
(15, 'Bosnia and Herzegovina', 'Convertible Marka', 'BAM', 'KM', ',', '.', NULL, NULL),
(16, 'Botswana', 'Pula\'s', 'BWP', 'P', ',', '.', NULL, NULL),
(17, 'Bulgaria', 'Leva', 'BG', 'лв', ',', '.', NULL, NULL),
(18, 'Brazil', 'Reais', 'BRL', 'R$', ',', '.', NULL, NULL),
(19, 'Britain [United Kingdom]', 'Pounds', 'GBP', '£', ',', '.', NULL, NULL),
(20, 'Brunei Darussalam', 'Dollars', 'BND', '$', ',', '.', NULL, NULL),
(21, 'Cambodia', 'Riels', 'KHR', '៛', ',', '.', NULL, NULL),
(22, 'Canada', 'Dollars', 'CAD', '$', ',', '.', NULL, NULL),
(23, 'Cayman Islands', 'Dollars', 'KYD', '$', ',', '.', NULL, NULL),
(24, 'Chile', 'Pesos', 'CLP', '$', ',', '.', NULL, NULL),
(25, 'China', 'Yuan Renminbi', 'CNY', '¥', ',', '.', NULL, NULL),
(26, 'Colombia', 'Pesos', 'COP', '$', ',', '.', NULL, NULL),
(27, 'Costa Rica', 'Colón', 'CRC', '₡', ',', '.', NULL, NULL),
(28, 'Croatia', 'Kuna', 'HRK', 'kn', ',', '.', NULL, NULL),
(29, 'Cuba', 'Pesos', 'CUP', '₱', ',', '.', NULL, NULL),
(30, 'Cyprus', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(31, 'Czech Republic', 'Koruny', 'CZK', 'Kč', ',', '.', NULL, NULL),
(32, 'Denmark', 'Kroner', 'DKK', 'kr', ',', '.', NULL, NULL),
(33, 'Dominican Republic', 'Pesos', 'DOP ', 'RD$', ',', '.', NULL, NULL),
(34, 'East Caribbean', 'Dollars', 'XCD', '$', ',', '.', NULL, NULL),
(35, 'Egypt', 'Pounds', 'EGP', '£', ',', '.', NULL, NULL),
(36, 'El Salvador', 'Colones', 'SVC', '$', ',', '.', NULL, NULL),
(37, 'England [United Kingdom]', 'Pounds', 'GBP', '£', ',', '.', NULL, NULL),
(38, 'Euro', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(39, 'Falkland Islands', 'Pounds', 'FKP', '£', ',', '.', NULL, NULL),
(40, 'Fiji', 'Dollars', 'FJD', '$', ',', '.', NULL, NULL),
(41, 'France', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(42, 'Ghana', 'Cedis', 'GHS', '¢', ',', '.', NULL, NULL),
(43, 'Gibraltar', 'Pounds', 'GIP', '£', ',', '.', NULL, NULL),
(44, 'Greece', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(45, 'Guatemala', 'Quetzales', 'GTQ', 'Q', ',', '.', NULL, NULL),
(46, 'Guernsey', 'Pounds', 'GGP', '£', ',', '.', NULL, NULL),
(47, 'Guyana', 'Dollars', 'GYD', '$', ',', '.', NULL, NULL),
(48, 'Holland [Netherlands]', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(49, 'Honduras', 'Lempiras', 'HNL', 'L', ',', '.', NULL, NULL),
(50, 'Hong Kong', 'Dollars', 'HKD', '$', ',', '.', NULL, NULL),
(51, 'Hungary', 'Forint', 'HUF', 'Ft', ',', '.', NULL, NULL),
(52, 'Iceland', 'Kronur', 'ISK', 'kr', ',', '.', NULL, NULL),
(53, 'India', 'Rupees', 'INR', '₹', ',', '.', NULL, NULL),
(54, 'Indonesia', 'Rupiahs', 'IDR', 'Rp', ',', '.', NULL, NULL),
(55, 'Iran', 'Rials', 'IRR', '﷼', ',', '.', NULL, NULL),
(56, 'Ireland', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(57, 'Isle of Man', 'Pounds', 'IMP', '£', ',', '.', NULL, NULL),
(58, 'Israel', 'New Shekels', 'ILS', '₪', ',', '.', NULL, NULL),
(59, 'Italy', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(60, 'Jamaica', 'Dollars', 'JMD', 'J$', ',', '.', NULL, NULL),
(61, 'Japan', 'Yen', 'JPY', '¥', ',', '.', NULL, NULL),
(62, 'Jersey', 'Pounds', 'JEP', '£', ',', '.', NULL, NULL),
(63, 'Kazakhstan', 'Tenge', 'KZT', 'лв', ',', '.', NULL, NULL),
(64, 'Korea [North]', 'Won', 'KPW', '₩', ',', '.', NULL, NULL),
(65, 'Korea [South]', 'Won', 'KRW', '₩', ',', '.', NULL, NULL),
(66, 'Kyrgyzstan', 'Soms', 'KGS', 'лв', ',', '.', NULL, NULL),
(67, 'Laos', 'Kips', 'LAK', '₭', ',', '.', NULL, NULL),
(68, 'Latvia', 'Lati', 'LVL', 'Ls', ',', '.', NULL, NULL),
(69, 'Lebanon', 'Pounds', 'LBP', '£', ',', '.', NULL, NULL),
(70, 'Liberia', 'Dollars', 'LRD', '$', ',', '.', NULL, NULL),
(71, 'Liechtenstein', 'Switzerland Francs', 'CHF', 'CHF', ',', '.', NULL, NULL),
(72, 'Lithuania', 'Litai', 'LTL', 'Lt', ',', '.', NULL, NULL),
(73, 'Luxembourg', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(74, 'Macedonia', 'Denars', 'MKD', 'ден', ',', '.', NULL, NULL),
(75, 'Malaysia', 'Ringgits', 'MYR', 'RM', ',', '.', NULL, NULL),
(76, 'Malta', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(77, 'Mauritius', 'Rupees', 'MUR', '₨', ',', '.', NULL, NULL),
(78, 'Mexico', 'Pesos', 'MXN', '$', ',', '.', NULL, NULL),
(79, 'Mongolia', 'Tugriks', 'MNT', '₮', ',', '.', NULL, NULL),
(80, 'Mozambique', 'Meticais', 'MZ', 'MT', ',', '.', NULL, NULL),
(81, 'Namibia', 'Dollars', 'NAD', '$', ',', '.', NULL, NULL),
(82, 'Nepal', 'Rupees', 'NPR', '₨', ',', '.', NULL, NULL),
(83, 'Netherlands Antilles', 'Guilders', 'ANG', 'ƒ', ',', '.', NULL, NULL),
(84, 'Netherlands', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(85, 'New Zealand', 'Dollars', 'NZD', '$', ',', '.', NULL, NULL),
(86, 'Nicaragua', 'Cordobas', 'NIO', 'C$', ',', '.', NULL, NULL),
(87, 'Nigeria', 'Nairas', 'NGN', '₦', ',', '.', NULL, NULL),
(88, 'North Korea', 'Won', 'KPW', '₩', ',', '.', NULL, NULL),
(89, 'Norway', 'Krone', 'NOK', 'kr', ',', '.', NULL, NULL),
(90, 'Oman', 'Rials', 'OMR', '﷼', ',', '.', NULL, NULL),
(91, 'Pakistan', 'Rupees', 'PKR', '₨', ',', '.', NULL, NULL),
(92, 'Panama', 'Balboa', 'PAB', 'B/.', ',', '.', NULL, NULL),
(93, 'Paraguay', 'Guarani', 'PYG', 'Gs', ',', '.', NULL, NULL),
(94, 'Peru', 'Nuevos Soles', 'PE', 'S/.', ',', '.', NULL, NULL),
(95, 'Philippines', 'Pesos', 'PHP', 'Php', ',', '.', NULL, NULL),
(96, 'Poland', 'Zlotych', 'PL', 'zł', ',', '.', NULL, NULL),
(97, 'Qatar', 'Rials', 'QAR', '﷼', ',', '.', NULL, NULL),
(98, 'Romania', 'New Lei', 'RO', 'lei', ',', '.', NULL, NULL),
(99, 'Russia', 'Rubles', 'RUB', 'руб', ',', '.', NULL, NULL),
(100, 'Saint Helena', 'Pounds', 'SHP', '£', ',', '.', NULL, NULL),
(101, 'Saudi Arabia', 'Riyals', 'SAR', '﷼', ',', '.', NULL, NULL),
(102, 'Serbia', 'Dinars', 'RSD', 'Дин.', ',', '.', NULL, NULL),
(103, 'Seychelles', 'Rupees', 'SCR', '₨', ',', '.', NULL, NULL),
(104, 'Singapore', 'Dollars', 'SGD', '$', ',', '.', NULL, NULL),
(105, 'Slovenia', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(106, 'Solomon Islands', 'Dollars', 'SBD', '$', ',', '.', NULL, NULL),
(107, 'Somalia', 'Shillings', 'SOS', 'S', ',', '.', NULL, NULL),
(108, 'South Africa', 'Rand', 'ZAR', 'R', ',', '.', NULL, NULL),
(109, 'South Korea', 'Won', 'KRW', '₩', ',', '.', NULL, NULL),
(110, 'Spain', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(111, 'Sri Lanka', 'Rupees', 'LKR', '₨', ',', '.', NULL, NULL),
(112, 'Sweden', 'Kronor', 'SEK', 'kr', ',', '.', NULL, NULL),
(113, 'Switzerland', 'Francs', 'CHF', 'CHF', ',', '.', NULL, NULL),
(114, 'Suriname', 'Dollars', 'SRD', '$', ',', '.', NULL, NULL),
(115, 'Syria', 'Pounds', 'SYP', '£', ',', '.', NULL, NULL),
(116, 'Taiwan', 'New Dollars', 'TWD', 'NT$', ',', '.', NULL, NULL),
(117, 'Thailand', 'Baht', 'THB', '฿', ',', '.', NULL, NULL),
(118, 'Trinidad and Tobago', 'Dollars', 'TTD', 'TT$', ',', '.', NULL, NULL),
(119, 'Turkey', 'Lira', 'TRY', 'TL', ',', '.', NULL, NULL),
(120, 'Turkey', 'Liras', 'TRL', '£', ',', '.', NULL, NULL),
(121, 'Tuvalu', 'Dollars', 'TVD', '$', ',', '.', NULL, NULL),
(122, 'Ukraine', 'Hryvnia', 'UAH', '₴', ',', '.', NULL, NULL),
(123, 'United Kingdom', 'Pounds', 'GBP', '£', ',', '.', NULL, NULL),
(124, 'United States of America', 'Dollars', 'USD', '$', ',', '.', NULL, NULL),
(125, 'Uruguay', 'Pesos', 'UYU', '$U', ',', '.', NULL, NULL),
(126, 'Uzbekistan', 'Sums', 'UZS', 'лв', ',', '.', NULL, NULL),
(127, 'Vatican City', 'Euro', 'EUR', '€', '.', ',', NULL, NULL),
(128, 'Venezuela', 'Bolivares Fuertes', 'VEF', 'Bs', ',', '.', NULL, NULL),
(129, 'Vietnam', 'Dong', 'VND', '₫', ',', '.', NULL, NULL),
(130, 'Yemen', 'Rials', 'YER', '﷼', ',', '.', NULL, NULL),
(131, 'Zimbabwe', 'Zimbabwe Dollars', 'ZWD', 'Z$', ',', '.', NULL, NULL),
(132, 'Iraq', 'Iraqi dinar', 'IQD', 'د.ع', ',', '.', NULL, NULL),
(133, 'Kenya', 'Kenyan shilling', 'KES', 'KSh', ',', '.', NULL, NULL),
(134, 'Bangladesh', 'Taka', 'BDT', '৳', ',', '.', NULL, NULL),
(135, 'Algerie', 'Algerian dinar', 'DZD', 'د.ج', ' ', '.', NULL, NULL),
(136, 'United Arab Emirates', 'United Arab Emirates dirham', 'AED', 'د.إ', ',', '.', NULL, NULL),
(137, 'Uganda', 'Uganda shillings', 'UGX', 'USh', ',', '.', NULL, NULL),
(138, 'Tanzania', 'Tanzanian shilling', 'TZS', 'TSh', ',', '.', NULL, NULL),
(139, 'Angola', 'Kwanza', 'AOA', 'Kz', ',', '.', NULL, NULL),
(140, 'Kuwait', 'Kuwaiti dinar', 'KWD', 'KD', ',', '.', NULL, NULL),
(141, 'Bahrain', 'Bahraini dinar', 'BHD', 'BD', ',', '.', NULL, NULL),
(142, 'Tunisia', 'Tunisian Dinar', 'TND', 'د.ت', ',', '.', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_groups`
--

CREATE TABLE `customer_groups` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(5,2) NOT NULL,
  `price_calculation_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `selling_price_group_id` int DEFAULT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_groups`
--

INSERT INTO `customer_groups` (`id`, `business_id`, `name`, `amount`, `price_calculation_type`, `selling_price_group_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Retailer', 0.00, 'selling_price_group', 1, 1, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(2, 1, 'Wholesale', 0.00, 'selling_price_group', 2, 1, '2026-07-18 18:56:54', '2026-07-18 18:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_configurations`
--

CREATE TABLE `dashboard_configurations` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `created_by` int NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `configuration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int NOT NULL,
  `brand_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `discount_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `spg` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Applicable in specified selling price group only. Use of applicable_in_spg column is discontinued',
  `applicable_in_cg` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `name`, `business_id`, `brand_id`, `category_id`, `location_id`, `priority`, `discount_type`, `discount_amount`, `starts_at`, `ends_at`, `is_active`, `spg`, `applicable_in_cg`, `created_at`, `updated_at`) VALUES
(2, '5%', 1, NULL, 10, 1, 1, 'percentage', 5.0000, '2026-08-05 15:49:00', '2026-09-06 15:49:00', 1, NULL, 0, '2026-08-06 05:49:51', '2026-08-06 05:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `discount_variations`
--

CREATE TABLE `discount_variations` (
  `discount_id` int NOT NULL,
  `variation_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_and_notes`
--

CREATE TABLE `document_and_notes` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int NOT NULL,
  `notable_id` int NOT NULL,
  `notable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `heading` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecommerce_settings`
--

CREATE TABLE `ecommerce_settings` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `store_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `enable_cod` tinyint(1) NOT NULL DEFAULT '1',
  `enable_stripe` tinyint(1) NOT NULL DEFAULT '0',
  `enable_paypal` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_client_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_secret` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'United States',
  `seo_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecommerce_settings`
--

INSERT INTO `ecommerce_settings` (`id`, `business_id`, `is_enabled`, `store_name`, `store_logo`, `location_id`, `enable_cod`, `enable_stripe`, `enable_paypal`, `stripe_key`, `stripe_secret`, `paypal_client_id`, `paypal_secret`, `default_country`, `seo_title`, `seo_description`, `contact_email`, `contact_phone`, `extra`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Japs Wholesale', NULL, 1, 1, 0, 0, NULL, NULL, NULL, NULL, 'United States', 'Japs Wholesale Distribution', 'Shop curated snacks, beverages, personal care and home essentials. Live POS inventory, secure checkout, USA shipping.', NULL, NULL, NULL, '2026-07-21 20:26:19', '2026-07-23 05:24:06');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_api_settings`
--

CREATE TABLE `ecom_api_settings` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `api_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_banners`
--

CREATE TABLE `ecom_banners` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_text` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_banners`
--

INSERT INTO `ecom_banners` (`id`, `business_id`, `title`, `subtitle`, `image`, `button_text`, `button_link`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(3, 1, 'Everyday essentials, elevated', 'Curated pantry, care, and home staples — inventory synced from our store.', 'ecom_banner_hero.jpg', 'Shop the collection', 'http://japspos.test/shop', 1, 1, '2026-07-22 17:43:04', '2026-07-22 17:43:04'),
(4, 1, 'New season refresh', 'Personal care favorites with clean formulas and soft finishes.', 'ecom_banner_care.jpg', 'Explore care', 'http://japspos.test/shop/c/11', 1, 2, '2026-07-22 17:43:07', '2026-07-22 17:43:07'),
(5, 1, 'Pantry favorites', 'Snacks and drinks restocked daily from live POS inventory.', 'ecom_banner_pantry.jpg', 'Shop snacks', 'http://japspos.test/shop/c/9', 1, 3, '2026-07-22 17:43:13', '2026-07-22 17:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_carts`
--

CREATE TABLE `ecom_carts` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED DEFAULT NULL,
  `session_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_carts`
--

INSERT INTO `ecom_carts` (`id`, `business_id`, `contact_id`, `session_id`, `coupon_code`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'vFd5yzZNvvw8hsdCuYdcrLGAvJrI1woBlqhpW2Jd', NULL, '2026-07-21 20:27:35', '2026-07-21 20:27:35'),
(2, 1, NULL, 'DZyl8FtZMbkSClsCYUxNtKCd39fPekKpAaWEqonE', NULL, '2026-07-21 20:33:26', '2026-07-21 20:33:26'),
(3, 1, NULL, 'hTh00k3eHMyugSAIL8ScB1ItsNZWaoC34IBAFRNZ', NULL, '2026-07-21 20:33:27', '2026-07-21 20:33:27'),
(4, 1, NULL, 'S73arjLu5DgEGdCcHgG3LAmr01fiACJrnQus51cn', NULL, '2026-07-22 12:57:43', '2026-07-22 12:57:43'),
(5, 1, NULL, '0fBd8XbITNilJ2W8IXSpoYPvgXsm5FZaqTviOCPg', NULL, '2026-07-22 17:02:40', '2026-07-22 17:02:40'),
(6, 1, NULL, 'TrAcrfNwCrR3k1PaiFEGDlOQPjERxwFi3dQNYoDw', NULL, '2026-07-22 17:10:04', '2026-07-22 17:10:04'),
(7, 1, NULL, 'ZrSTU8t4NO7T0fGzrW3wc9FKRD8jpJmpGg72Q0bc', NULL, '2026-07-22 17:19:23', '2026-07-22 17:19:23'),
(8, 1, NULL, 'yP9CZrmtB6tkFlc68isHGLhnetwtVKsoOh1HTHpz', NULL, '2026-07-22 17:19:24', '2026-07-22 17:19:24'),
(9, 1, NULL, '1Vim1Ne0JXK4ZP1cbNf4g9FSpdjbuximp5EacLAh', NULL, '2026-07-22 17:22:00', '2026-07-22 17:22:00'),
(10, 1, NULL, 'XlTrzoh1FjTFhitUXAol7UrgS9fZJ04X8bETZBI9', NULL, '2026-07-22 17:22:03', '2026-07-22 17:22:03'),
(11, 1, NULL, 'tyXeeLv1OEmjsKLCSqYvlyzSBvaYfrwYEKBbITMZ', NULL, '2026-07-22 17:22:05', '2026-07-22 17:22:05'),
(12, 1, NULL, 'oaBRZKsWqhWrNge3gibJg9Il1FMuBFrQ7vzi8ocm', NULL, '2026-07-22 17:22:06', '2026-07-22 17:22:06'),
(13, 1, NULL, 'iU8tKm6Q31m9FhK5ADHETOx4XlFpIOgnNpCxh9p8', NULL, '2026-07-22 17:22:07', '2026-07-22 17:22:07'),
(14, 1, NULL, '9caTCZscFoBM2LVFejEdwBdH6IGSlJtqaOXb2zGH', NULL, '2026-07-22 17:22:12', '2026-07-22 17:22:12'),
(15, 1, NULL, 'GCxRL72628a8F6QjslogmfFD7Rfnq23MglPrp03I', NULL, '2026-07-22 17:22:14', '2026-07-22 17:22:14'),
(16, 1, NULL, 'O4RbDEh087JV3LEFawlGR8cBGdNT3fcUx4jsv1EW', NULL, '2026-07-22 17:22:25', '2026-07-22 17:22:25'),
(17, 1, NULL, 'SS9vCg0GezCju5j3wbqcR1UAcDNuijJEXI0V7J7W', NULL, '2026-07-22 17:22:35', '2026-07-22 17:22:35'),
(18, 1, NULL, '6ZGWpryJAKbP8saMs2NvYWnnJMqk1W9oxPHv60so', NULL, '2026-07-22 17:22:36', '2026-07-22 17:22:36'),
(19, 1, NULL, '7Y8oXs0B0h0jZtNhDyvTdJIO6FKmJtJr3rHuGv5V', NULL, '2026-07-22 17:22:42', '2026-07-22 17:22:42'),
(20, 1, NULL, 'eVH5jNJeDl91hliGQ8Q8i92aCifkXWCBIckNHuAq', NULL, '2026-07-22 17:22:45', '2026-07-22 17:22:45'),
(21, 1, NULL, 'XihpM3DOa3pA0pOiFiVmPJmqR3Q2AEknND2KmhFU', NULL, '2026-07-22 17:22:47', '2026-07-22 17:22:47'),
(22, 1, NULL, 'gXMYOuB0t0mlBigGbB3h8xJqegkCGKjZ254OyKvS', NULL, '2026-07-22 17:22:49', '2026-07-22 17:22:49'),
(23, 1, NULL, 'jx8LNvkcRjtqBVWjIXOmZvKogUeHJI0pDr7Cie9G', NULL, '2026-07-22 17:22:55', '2026-07-22 17:22:55'),
(24, 1, NULL, 'ZE9r8bcfbOdID0l7QbYvRi4qBarPR4kYxPfxTfoC', NULL, '2026-07-22 17:22:59', '2026-07-22 17:22:59'),
(25, 1, NULL, 'ROnKLo5zZH2IMHGVIZgYikXfrnXSvJLMw9eYZ55N', NULL, '2026-07-22 17:23:37', '2026-07-22 17:23:37'),
(26, 1, NULL, 'VTODXAbTYqA0Zru95ySBHnDu8htrVEmS9W2GyxNW', NULL, '2026-07-22 17:23:39', '2026-07-22 17:23:39'),
(27, 1, NULL, '413WH3DzA5FUCLWRGC8KiXg2wu7hO5FFcYYspqN1', NULL, '2026-07-22 17:23:42', '2026-07-22 17:23:42'),
(28, 1, NULL, 't7917rILjDnAZL7TX7FqHyJwv0pg3T7haOtcIFBW', NULL, '2026-07-22 17:23:44', '2026-07-22 17:23:44'),
(29, 1, NULL, 'E0V2hNWUi6NHOsny1YyEzS2OJ7QXfMZnxZGhFqRZ', NULL, '2026-07-22 17:23:45', '2026-07-22 17:23:45'),
(30, 1, NULL, 'w2vVtWHTIWNBul6cQYdtiSQsr0pdZ9UB6ftlXRQn', NULL, '2026-07-22 17:23:47', '2026-07-22 17:23:47'),
(31, 1, NULL, 'ZRaRp0DosCrv95IteECUfiznyLy0SNs3PU5oJ7Es', NULL, '2026-07-22 17:23:56', '2026-07-22 17:23:56'),
(32, 1, NULL, 'mots8P20QP1830uZfGxBVNdmaf820ElvrHmoFkNT', NULL, '2026-07-22 17:23:58', '2026-07-22 17:23:58'),
(33, 1, NULL, 'OpKRLyEDdTqw5rlYzwsQpXrpV3AMkjw8Qvt2XMVx', NULL, '2026-07-22 17:24:04', '2026-07-22 17:24:04'),
(34, 1, NULL, '6Nutdgy3L2I3DKGwiPIucGQLL1MQ0ckpJsG2Nmxm', NULL, '2026-07-22 17:24:21', '2026-07-22 17:24:21'),
(35, 1, NULL, 'EWUUJAQjIR77oncWnZrP3NtkPp1rcYEQoBvmeLsn', NULL, '2026-07-22 17:24:23', '2026-07-22 17:24:23'),
(36, 1, NULL, 'GUfpFfcRtvJ2ACIdaGmhUo62Mi6TCR07yu5cjNfO', NULL, '2026-07-22 17:28:53', '2026-07-22 17:28:53'),
(37, 1, NULL, 'dGcOnDcdjf2uvxHpujmGTS4RLzcMDoKoELO1qehZ', NULL, '2026-07-22 17:28:54', '2026-07-22 17:28:54'),
(38, 1, NULL, 'MIppPPqbLyZhL2tAFd4HKsVGPTfszF9Qi2VtiHI7', NULL, '2026-07-22 17:28:54', '2026-07-22 17:28:54'),
(39, 1, NULL, 'gkd1VZyqxQDjsejrUvUIKaPL0UPuDzXq4iSO1JAN', NULL, '2026-07-22 17:30:02', '2026-07-22 17:30:02'),
(40, 1, NULL, 'zXj1ww0lK9uUWAHjwtbAb9p6HqUNvKafVBawNc3B', NULL, '2026-07-22 17:30:04', '2026-07-22 17:30:04'),
(41, 1, NULL, 'NUwVe8AZFDGMzlJr0F75cKXF0vDlamLZlZZGge2f', NULL, '2026-07-22 17:30:06', '2026-07-22 17:30:06'),
(42, 1, NULL, 'S4ZA7PmMXa8mhP5A9fZBGbhpAfZUULg7v6RW8ySM', NULL, '2026-07-22 17:30:09', '2026-07-22 17:30:09'),
(43, 1, NULL, 'yS1zWJp1zqkB9BnPqYZHypropPHfEJOwFT1rHL15', NULL, '2026-07-22 17:30:17', '2026-07-22 17:30:17'),
(44, 1, NULL, '75ntB4LQH91r4XUtgeVkAfkMtnVVOSoluJuIbzhs', NULL, '2026-07-22 17:30:21', '2026-07-22 17:30:21'),
(45, 1, NULL, 'EDQl07WOVRUgS2w9jMwlwZGm7NqOjKTad2tiSq5M', NULL, '2026-07-22 17:30:23', '2026-07-22 17:30:23'),
(46, 1, NULL, 'KNH7pr8GnBoayWOYCOPlElam4BTIUbjo3lr98C2X', NULL, '2026-07-22 17:30:28', '2026-07-22 17:30:28'),
(47, 1, NULL, 'peB8pvKg8FKD5N4XrmWqrqMAICgLocyZj2ME11ny', NULL, '2026-07-22 17:30:29', '2026-07-22 17:30:29'),
(48, 1, NULL, 'ebXHg2vUO2opQakhkoJgJfuckGKJgIcd66Daf2Gl', NULL, '2026-07-22 17:30:34', '2026-07-22 17:30:34'),
(49, 1, NULL, 'vgvTxkkp2oVAXhDIksBuTTUaCZdeseSubw1Hkv1f', NULL, '2026-07-22 17:31:25', '2026-07-22 17:31:25'),
(50, 1, NULL, 'BLioICjRcVyWT4BbprpoBjesn6hxT1MMH3OPW4Xk', NULL, '2026-07-22 17:33:11', '2026-07-22 17:33:11'),
(51, 1, NULL, 'eeftRwCYEQXoeqkCqurdR8ctmm9KCTrTXrzLVLUu', NULL, '2026-07-22 17:33:41', '2026-07-22 17:33:41'),
(52, 1, NULL, 'pkMe6Cn7CsMNS0ceBYttNQJiferZiBAetUXEJavc', NULL, '2026-07-22 17:33:45', '2026-07-22 17:33:45'),
(53, 1, NULL, 'h562LBERzwX5edmKjo5cRJkfFBkN8M31v1HgAoUO', NULL, '2026-07-22 17:34:14', '2026-07-22 17:34:14'),
(54, 1, NULL, '861DMFIfCHbYGlu8tjlyoKQdzXqhke1fi4UoaEZa', NULL, '2026-07-22 17:36:49', '2026-07-22 17:36:49'),
(55, 1, NULL, 'WQoRpHRqBONS0ODtxuOLcwh69nDdyM0eqCMmrvBZ', NULL, '2026-07-22 17:37:38', '2026-07-22 17:37:38'),
(56, 1, NULL, 'iTISncoM2bCil3wCDyjNCmh3WQoC9dUcOKjgVTex', NULL, '2026-07-22 17:43:41', '2026-07-22 17:43:41'),
(57, 1, NULL, 'ElTD1uFCTMYXVOKibVN9nwiQEQJ0qsFtwn6EJGum', NULL, '2026-07-23 13:07:48', '2026-07-23 13:07:48'),
(58, 1, NULL, 'MxHq1tgYgzgaOsemRkVQQ8JkvEUeOnOWJ7bPEXxd', NULL, '2026-07-23 13:08:16', '2026-07-23 13:08:16'),
(59, 1, NULL, 'r7eWiV6eGxXZHEUASshDu4OYXJbkZp5nmdXz4weX', NULL, '2026-07-23 13:08:16', '2026-07-23 13:08:16'),
(60, 1, NULL, 'zavqZxZyB9dU4V64LxHFSRZ0gPJmqT45O9iu6LBQ', NULL, '2026-07-23 13:08:16', '2026-07-23 13:08:16'),
(61, 1, NULL, 'oa3hMroy2s0hoLVCh4OIvK6zZeeAD3R4Gzxc8fXm', NULL, '2026-07-23 13:08:17', '2026-07-23 13:08:17'),
(62, 1, NULL, 'Xech59aItFOk8CwonphXZhtJFHcr0yk4gKarGdR1', NULL, '2026-07-23 13:08:17', '2026-07-23 13:08:17'),
(63, 1, NULL, 'pCm0cIzsPYmBi8NxSgVwcy1ujtkTZLXit4L2r53P', NULL, '2026-07-23 13:08:18', '2026-07-23 13:08:18'),
(64, 1, 7, 'QdfdEWqHKBIUuocM1QoTjW0Kr0PPwMA8TWMF56Hs', NULL, '2026-07-23 13:14:17', '2026-07-23 13:14:17'),
(65, 1, NULL, 'xgPiBPMZ5BzmLXVHN45WZlADKZMlf7J0POkx0yje', NULL, '2026-07-23 13:17:34', '2026-07-23 13:17:34'),
(66, 1, NULL, 'WgesI46HoRZHwVvoWOUnrPwj36u5bJQdNc7fRW47', NULL, '2026-07-23 13:29:31', '2026-07-23 13:29:31'),
(67, 1, NULL, '1ErwofGD2PKuw0gdGnv8ybIiOvRgfsUZJ5DCtqj8', NULL, '2026-07-23 13:29:32', '2026-07-23 13:29:32'),
(68, 1, NULL, 'QdfdEWqHKBIUuocM1QoTjW0Kr0PPwMA8TWMF56Hs', NULL, '2026-07-23 13:30:16', '2026-07-23 13:30:16'),
(69, 1, NULL, 'Cxr8QfFNDoI5Puc5cdZ50xpwsBACd0LPLMgKhfD9', NULL, '2026-07-23 13:53:22', '2026-07-23 13:53:22'),
(70, 1, NULL, 'o4PtUfr4IUUy2TFqQUhtLEkoJniftU3UUTa33ZeW', NULL, '2026-07-23 14:49:37', '2026-07-23 14:49:37'),
(71, 1, NULL, '4EcYfVU9H52wSpsJF7B5URP8RNkZUv3eNcTMYoaL', NULL, '2026-07-24 17:37:13', '2026-07-24 17:37:13'),
(72, 1, NULL, 'DqtcYeP47SgZlm9AQMcxtvZjhF2zNWWP6C0xeHSO', NULL, '2026-07-24 17:49:48', '2026-07-24 17:49:48'),
(73, 1, NULL, 'KLF8GYcRTQfZy7n78u5e5m2uMQKz2yyRoBSl9Stz', NULL, '2026-07-27 14:52:56', '2026-07-27 14:52:56'),
(74, 1, NULL, 'xnoG4j0rWF8FRUHp7FEoHEkVwTumQV2qP88dg5lE', NULL, '2026-07-28 13:50:51', '2026-07-28 13:50:51'),
(75, 1, NULL, 'JbD4dTRjVpm1BSozsIoiiKd6mhQSNu9lrWVlRkOb', NULL, '2026-07-28 17:01:37', '2026-07-28 17:01:37'),
(76, 1, NULL, '1z90uvYkLnARAJj1jaVWhsVNCnZwCI20OtipCWTz', NULL, '2026-08-03 13:44:30', '2026-08-03 13:44:30'),
(77, 1, NULL, 'kyUJj6srqOUC3xpmqcw1WVPHJmzAzXMSulXfT4Ro', NULL, '2026-08-03 15:31:28', '2026-08-03 15:31:28'),
(78, 1, NULL, 'DUtOTqr4qYcQact0hQmALlb5D9dDe1lCsMOB6eBq', NULL, '2026-08-03 18:11:19', '2026-08-03 18:11:19'),
(79, 1, NULL, 'bAYyVVJnNqrs2hzFjjhkpVdBV4IYAHviJ2gJiaFW', NULL, '2026-08-04 14:40:28', '2026-08-04 14:40:28'),
(80, 1, NULL, 'sxvLd4w6ainMsum14MaNXzkSi2mXl9m0SK1ltSMN', NULL, '2026-08-04 16:13:30', '2026-08-04 16:13:30'),
(81, 1, NULL, 'tpHdAbjtJmfJchovalXS2wdjlPeXNpQsXwS2kNd5', NULL, '2026-08-05 13:54:45', '2026-08-05 13:54:45'),
(82, 1, NULL, 'w1pFjRCK8ScdnItikywzM37QOsOB3WsfFskCCSUv', NULL, '2026-08-05 13:54:46', '2026-08-05 13:54:46'),
(83, 1, NULL, 'BdZ1QNGy710yUR0Uw2NATDDu8nCgfC2rrZUf2Tjh', NULL, '2026-08-06 13:08:41', '2026-08-06 13:08:41'),
(84, 1, NULL, 'zJtkEXuTVUHfyPJfaCqkF48b1VS0fKm0AuwtGqcf', NULL, '2026-08-06 13:08:42', '2026-08-06 13:08:42'),
(85, 1, NULL, '6Y83q1OMfCkEt5jLDgKKAQRrSIuVfKYJlP6LzmD4', NULL, '2026-08-06 19:13:49', '2026-08-06 19:13:49'),
(86, 1, NULL, 'oCv2utxeTUekD2zDmc5Mz9PT0opVRAd5VrdRWJvR', NULL, '2026-08-06 19:13:50', '2026-08-06 19:13:50'),
(87, 1, NULL, 'q1jM7IWbmd4wGXd7KxG3v00t0uPZORs4H07aMgrw', NULL, '2026-08-07 13:30:05', '2026-08-07 13:30:05'),
(88, 1, NULL, 'ZwybKzMVBslpeEPbbrb8u7ilMcLVFwSrKxg2Z63i', NULL, '2026-08-07 13:30:07', '2026-08-07 13:30:07'),
(89, 1, NULL, 'mlB0BrTQEdB4UuwAHL9aIne1dR0q84E7rAB63Zr9', NULL, '2026-08-11 16:05:32', '2026-08-11 16:05:32'),
(90, 1, NULL, '4cAKRBzqTjz02kNxU4NUtUSihtRONnLtkzMP72Mo', NULL, '2026-08-12 13:17:30', '2026-08-12 13:17:30'),
(91, 1, NULL, 'jfSYDMBMKn0bAnPLP1SPE8kOo2nSfKTpmu3275hg', NULL, '2026-08-12 15:56:26', '2026-08-12 15:56:26'),
(92, 1, NULL, 'ETO6XfTYNYrOHeDr7igXsuMO2Ubti4SCfGfpzbZV', NULL, '2026-08-13 15:16:23', '2026-08-13 15:16:23'),
(93, 1, NULL, 'DClgzjzjAxIYSBH49VEatikinCB2uD9eOusT73Ue', NULL, '2026-08-13 15:17:42', '2026-08-13 15:17:42'),
(94, 1, 8, 'zDLTW0XyzMVzc4iQLP9TkPy12w1DuaCL0SoZo1qG', NULL, '2026-08-13 15:20:07', '2026-08-13 15:20:07'),
(95, 1, NULL, 'zDLTW0XyzMVzc4iQLP9TkPy12w1DuaCL0SoZo1qG', NULL, '2026-08-13 15:20:20', '2026-08-13 15:20:20'),
(96, 1, NULL, 'GQ9iT9CIpXUR1PvaNXvvsjNbRoxJQU3rOr6bUn1c', NULL, '2026-08-13 15:33:16', '2026-08-13 15:33:16'),
(97, 1, NULL, 'xTbn6rqQAmvykSLqwWRfO1QVAsHHv5TMXW4891Ii', NULL, '2026-08-13 15:56:32', '2026-08-13 15:56:32'),
(98, 1, NULL, 'Pf2Wsd5I1s7TUsbpWMXcy0gHFSYLmIzFA16LIvy5', NULL, '2026-08-13 16:00:10', '2026-08-13 16:00:10'),
(99, 1, NULL, 'pWafWmZYpQBl9k7RWkSZFoWcBa6lUNnohl6P4af0', NULL, '2026-08-13 16:29:42', '2026-08-13 16:29:42'),
(100, 1, NULL, 'EUi66aF68SkeQQ5uwbtFk5ztrE8bGJ58jyDrPbjQ', NULL, '2026-08-13 16:31:47', '2026-08-13 16:31:47'),
(101, 1, NULL, 'k60D16Q391kbZFxQw1bYHXS35tCDzniXTv4vI6YU', NULL, '2026-08-13 16:42:13', '2026-08-13 16:42:13'),
(102, 1, NULL, '8vyuG4kYsRanYiRHwgrhBdOLoO8k99wFNy2MVeSp', NULL, '2026-08-13 16:44:54', '2026-08-13 16:44:54'),
(103, 1, NULL, 'l2R5atskK7fxHGdryBxKjIZd2SCegUXxY9FSHbtH', NULL, '2026-08-13 16:45:21', '2026-08-13 16:45:21'),
(104, 1, NULL, 'v14FxVTC1yGCyf1ofi93iH4dcOVbMVSKmexArc6T', NULL, '2026-08-13 16:47:18', '2026-08-13 16:47:18'),
(105, 1, NULL, 'x9UpC8IuapG6rhTytxKGaHx0pQf7FHVdZSkfW6Sq', NULL, '2026-08-13 16:51:32', '2026-08-13 16:51:32');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_cart_items`
--

CREATE TABLE `ecom_cart_items` (
  `id` int UNSIGNED NOT NULL,
  `cart_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `variation_id` int UNSIGNED NOT NULL,
  `quantity` decimal(22,4) NOT NULL DEFAULT '1.0000',
  `unit_price` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_cart_items`
--

INSERT INTO `ecom_cart_items` (`id`, `cart_id`, `product_id`, `variation_id`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 7, 1.0000, 12.0000, '2026-07-21 20:29:58', '2026-07-21 20:29:58'),
(4, 53, 38, 38, 1.0000, 13.6000, '2026-07-22 17:35:13', '2026-07-22 17:35:13'),
(5, 55, 38, 38, 1.0000, 13.6000, '2026-07-22 17:38:19', '2026-07-22 17:38:19'),
(7, 57, 42, 42, 1.0000, 4.9900, '2026-07-23 13:08:24', '2026-07-23 13:08:24'),
(9, 64, 40, 40, 11.0000, 14.5000, '2026-07-23 13:21:02', '2026-07-23 13:21:02'),
(10, 64, 39, 39, 1.0000, 12.0000, '2026-07-23 13:21:12', '2026-07-23 13:21:12'),
(13, 72, 40, 40, 2.0000, 14.5000, '2026-07-24 17:49:48', '2026-07-24 17:49:48'),
(14, 93, 7, 7, 1.0000, 12.0000, '2026-08-13 15:18:04', '2026-08-13 15:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_collections`
--

CREATE TABLE `ecom_collections` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_collections`
--

INSERT INTO `ecom_collections` (`id`, `business_id`, `name`, `slug`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Weekly Pantry', 'weekly-pantry', 'Snacks and drinks for the week ahead.', NULL, 1, 1, '2026-07-22 17:43:13', '2026-07-22 17:43:13'),
(2, 1, 'Self-Care Edit', 'self-care-edit', 'Personal care favorites, restocked.', NULL, 1, 2, '2026-07-22 17:43:13', '2026-07-22 17:43:13'),
(3, 1, 'Home Comforts', 'home-comforts', 'Towels, candles, and everyday tools.', NULL, 1, 3, '2026-07-22 17:43:13', '2026-07-22 17:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_collection_products`
--

CREATE TABLE `ecom_collection_products` (
  `id` int UNSIGNED NOT NULL,
  `collection_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_coupons`
--

CREATE TABLE `ecom_coupons` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `value` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `min_order_amount` decimal(22,4) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_coupon_usages`
--

CREATE TABLE `ecom_coupon_usages` (
  `id` int UNSIGNED NOT NULL,
  `coupon_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED DEFAULT NULL,
  `transaction_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_customer_addresses`
--

CREATE TABLE `ecom_customer_addresses` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `first_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'United States',
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_customer_addresses`
--

INSERT INTO `ecom_customer_addresses` (`id`, `business_id`, `contact_id`, `first_name`, `last_name`, `company`, `address_line_1`, `address_line_2`, `city`, `state`, `zip_code`, `country`, `phone`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 7, 'Owen', 'Cummings', 'TaShya Mercado', '26 Wood Ave Mansfield, USA', '333', '33', 'IN', '333', 'United States', 'Atque architecto nul', 1, '2026-07-23 13:14:17', '2026-07-23 13:14:17'),
(2, 1, 8, 'Demo', 'Buyer', 'Demo Wholesale LLC', '100 Demo Street', NULL, 'Dallas', 'TX', '75001', 'United States', '5551234567', 1, '2026-07-23 13:57:24', '2026-07-23 13:57:24');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_customer_licenses`
--

CREATE TABLE `ecom_customer_licenses` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `license_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `certificate_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `file_path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_order_meta`
--

CREATE TABLE `ecom_order_meta` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `transaction_id` int UNSIGNED NOT NULL,
  `tracking_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fulfillment_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `timeline` json DEFAULT NULL,
  `shipping_address` json DEFAULT NULL,
  `billing_address` json DEFAULT NULL,
  `coupon_id` int UNSIGNED DEFAULT NULL,
  `payment_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_order_meta`
--

INSERT INTO `ecom_order_meta` (`id`, `business_id`, `transaction_id`, `tracking_token`, `fulfillment_status`, `timeline`, `shipping_address`, `billing_address`, `coupon_id`, `payment_method`, `customer_email`, `customer_phone`, `created_at`, `updated_at`) VALUES
(1, 1, 9, '5SXIcacoqkyiF00X7iwAxamfqMgAO6OdNtYrBpvI', 'confirmed', '[{\"at\": \"2026-07-22 19:10:15\", \"label\": \"Order Received\", \"status\": \"pending\"}, {\"at\": \"2026-07-22 13:34:04\", \"note\": null, \"label\": \"Confirmed\", \"status\": \"confirmed\"}]', '{\"city\": \"33\", \"state\": \"KS\", \"country\": \"United States\", \"zip_code\": \"333\", \"last_name\": \"333\", \"first_name\": \"Nash\", \"address_line_1\": \"33\", \"address_line_2\": \"333\"}', '{\"city\": \"33\", \"state\": \"KS\", \"country\": \"United States\", \"zip_code\": \"333\", \"last_name\": \"333\", \"first_name\": \"Nash\", \"address_line_1\": \"33\", \"address_line_2\": \"333\"}', NULL, 'cod', 'admin@gmail.com', '333', '2026-07-22 13:10:15', '2026-07-22 07:34:04'),
(2, 1, 10, 'vpbEu1BelcEE1iFvw1lUcTduMW8x3C9By4pU6DPp', 'pending', '[{\"at\": \"2026-07-22 23:34:04\", \"label\": \"Order Received\", \"status\": \"pending\"}]', '{\"city\": \"33\", \"state\": \"KS\", \"country\": \"United States\", \"zip_code\": \"333\", \"last_name\": \"333\", \"first_name\": \"Nash\", \"address_line_1\": \"33\", \"address_line_2\": \"333\"}', '{\"city\": \"33\", \"state\": \"KS\", \"country\": \"United States\", \"zip_code\": \"333\", \"last_name\": \"333\", \"first_name\": \"Nash\", \"address_line_1\": \"33\", \"address_line_2\": \"333\"}', NULL, 'cod', 'admin@gmail.com', '333', '2026-07-22 17:34:04', '2026-07-22 17:34:04'),
(3, 1, 142, 'w1FfTA301k2i1PNnI1EAUGdSI0HznwX7vNweaJKx', 'pending', '[{\"at\": \"2026-08-13 21:20:58\", \"label\": \"Order Received\", \"status\": \"pending\"}, {\"at\": \"2026-08-13 11:54:27\", \"note\": null, \"label\": \"Confirmed\", \"status\": \"confirmed\"}, {\"at\": \"2026-08-13 12:12:23\", \"note\": null, \"label\": \"Pending\", \"status\": \"pending\"}]', '{\"city\": \"Harum in sunt rerum\", \"state\": \"TX\", \"country\": \"United States\", \"zip_code\": \"68209\", \"last_name\": \"Morin\", \"first_name\": \"Prescott\", \"address_line_1\": \"95 Nobel Lane\", \"address_line_2\": \"Qui dolorem similiqu\"}', '{\"city\": \"Harum in sunt rerum\", \"state\": \"TX\", \"country\": \"United States\", \"zip_code\": \"68209\", \"last_name\": \"Morin\", \"first_name\": \"Prescott\", \"address_line_1\": \"95 Nobel Lane\", \"address_line_2\": \"Qui dolorem similiqu\"}', NULL, 'cod', 'vasicece@mailinator.com', '+1 (648) 492-7019', '2026-08-13 15:20:58', '2026-08-13 06:12:23'),
(4, 1, 146, 'obz7AXHYfcIiuQNJHJxqTHwZ8xjFkmWngsOjPlUw', 'confirmed', '[{\"at\": \"2026-08-13 22:00:56\", \"label\": \"Order Received\", \"status\": \"pending\"}, {\"at\": \"2026-08-13 12:41:02\", \"note\": null, \"label\": \"Confirmed\", \"status\": \"confirmed\"}, {\"at\": \"2026-08-13 22:51:32\", \"label\": \"Converted to invoice 0099\", \"status\": \"invoiced\", \"invoice_id\": 147, \"invoice_no\": \"0099\"}]', '{\"city\": \"333\", \"state\": \"HI\", \"country\": \"United States\", \"zip_code\": \"33\", \"last_name\": \"sadasfas\", \"first_name\": \"Nash\", \"address_line_1\": \"eeee\", \"address_line_2\": null}', '{\"city\": \"333\", \"state\": \"HI\", \"country\": \"United States\", \"zip_code\": \"33\", \"last_name\": \"sadasfas\", \"first_name\": \"Nash\", \"address_line_1\": \"eeee\", \"address_line_2\": null}', NULL, 'cod', 'demo@japswholesale.test', '5551234567', '2026-08-13 16:00:56', '2026-08-13 16:51:32');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_reviews`
--

CREATE TABLE `ecom_reviews` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED DEFAULT NULL,
  `author_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` tinyint UNSIGNED NOT NULL DEFAULT '5',
  `review` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `images` json DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_shipping_rules`
--

CREATE TABLE `ecom_shipping_rules` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'flat',
  `amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `min_order_amount` decimal(22,4) DEFAULT NULL,
  `max_weight` decimal(22,4) DEFAULT NULL,
  `states` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ecom_shipping_rules`
--

INSERT INTO `ecom_shipping_rules` (`id`, `business_id`, `name`, `type`, `amount`, `min_order_amount`, `max_weight`, `states`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Standard Shipping', 'flat', 5.9900, NULL, NULL, NULL, 1, 1, '2026-07-22 07:42:32', '2026-07-22 07:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_tax_rules`
--

CREATE TABLE `ecom_tax_rules` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'state',
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `county` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_wishlists`
--

CREATE TABLE `ecom_wishlists` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `variation_id` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendances`
--

CREATE TABLE `employee_attendances` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `clock_in_at` datetime NOT NULL,
  `clock_out_at` datetime DEFAULT NULL,
  `status` enum('clocked_in','on_break','clocked_out') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'clocked_in',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendances`
--

INSERT INTO `employee_attendances` (`id`, `business_id`, `user_id`, `clock_in_at`, `clock_out_at`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-07-20 13:01:00', '2026-07-20 13:01:14', 'clocked_out', NULL, '2026-07-20 07:01:00', '2026-07-20 07:01:14'),
(2, 1, 1, '2026-07-23 12:43:18', '2026-07-23 12:44:04', 'clocked_out', NULL, '2026-07-23 06:43:18', '2026-07-23 06:44:04'),
(3, 1, 1, '2026-07-27 11:38:11', '2026-07-27 11:38:26', 'clocked_out', NULL, '2026-07-27 05:38:11', '2026-07-27 05:38:26'),
(4, 1, 1, '2026-07-27 11:55:18', '2026-07-27 11:55:22', 'clocked_out', NULL, '2026-07-27 05:55:18', '2026-07-27 05:55:22'),
(5, 1, 1, '2026-07-27 11:57:53', '2026-07-27 11:57:56', 'clocked_out', NULL, '2026-07-27 05:57:53', '2026-07-27 05:57:56'),
(6, 1, 1, '2026-07-27 12:13:54', '2026-07-27 12:14:23', 'clocked_out', NULL, '2026-07-27 06:13:54', '2026-07-27 06:14:23'),
(7, 1, 1, '2026-07-28 11:20:21', '2026-07-28 11:20:35', 'clocked_out', NULL, '2026-07-28 05:20:21', '2026-07-28 05:20:35');

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance_breaks`
--

CREATE TABLE `employee_attendance_breaks` (
  `id` int UNSIGNED NOT NULL,
  `attendance_id` int UNSIGNED NOT NULL,
  `break_start_at` datetime NOT NULL,
  `break_end_at` datetime DEFAULT NULL,
  `reason` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendance_breaks`
--

INSERT INTO `employee_attendance_breaks` (`id`, `attendance_id`, `break_start_at`, `break_end_at`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-07-20 13:01:06', '2026-07-20 13:01:09', NULL, '2026-07-20 07:01:06', '2026-07-20 07:01:09'),
(2, 2, '2026-07-23 12:43:26', '2026-07-23 12:44:04', NULL, '2026-07-23 06:43:26', '2026-07-23 06:44:04'),
(3, 3, '2026-07-27 11:38:20', '2026-07-27 11:38:23', NULL, '2026-07-27 05:38:20', '2026-07-27 05:38:23'),
(4, 6, '2026-07-27 12:13:58', '2026-07-27 12:14:02', NULL, '2026-07-27 06:13:58', '2026-07-27 06:14:02'),
(5, 6, '2026-07-27 12:14:12', '2026-07-27 12:14:16', NULL, '2026-07-27 06:14:12', '2026-07-27 06:14:16'),
(6, 7, '2026-07-28 11:20:27', '2026-07-28 11:20:31', NULL, '2026-07-28 05:20:27', '2026-07-28 05:20:31');

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_sub_taxes`
--

CREATE TABLE `group_sub_taxes` (
  `group_tax_id` int UNSIGNED NOT NULL,
  `tax_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `group_sub_taxes`
--

INSERT INTO `group_sub_taxes` (`group_tax_id`, `tax_id`) VALUES
(7, 2),
(7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_layouts`
--

CREATE TABLE `invoice_layouts` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `header_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `invoice_no_prefix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_no_prefix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_heading` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_heading_line1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_heading_line2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_heading_line3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_heading_line4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_heading_line5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_heading_not_paid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_heading_paid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_heading` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_total_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `round_off_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_due_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_client_id` tinyint(1) NOT NULL DEFAULT '0',
  `client_id_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_tax_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_time_format` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_time` tinyint(1) NOT NULL DEFAULT '1',
  `show_brand` tinyint(1) NOT NULL DEFAULT '0',
  `show_sku` tinyint(1) NOT NULL DEFAULT '1',
  `show_cat_code` tinyint(1) NOT NULL DEFAULT '1',
  `show_expiry` tinyint(1) NOT NULL DEFAULT '0',
  `show_lot` tinyint(1) NOT NULL DEFAULT '0',
  `show_image` tinyint(1) NOT NULL DEFAULT '0',
  `show_sale_description` tinyint(1) NOT NULL DEFAULT '0',
  `sales_person_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_sales_person` tinyint(1) NOT NULL DEFAULT '0',
  `table_product_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_qty_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_unit_price_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_subtotal_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cat_code_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_logo` tinyint(1) NOT NULL DEFAULT '0',
  `show_business_name` tinyint(1) NOT NULL DEFAULT '0',
  `show_location_name` tinyint(1) NOT NULL DEFAULT '1',
  `show_landmark` tinyint(1) NOT NULL DEFAULT '1',
  `show_city` tinyint(1) NOT NULL DEFAULT '1',
  `show_state` tinyint(1) NOT NULL DEFAULT '1',
  `show_zip_code` tinyint(1) NOT NULL DEFAULT '1',
  `show_country` tinyint(1) NOT NULL DEFAULT '1',
  `show_mobile_number` tinyint(1) NOT NULL DEFAULT '1',
  `show_alternate_number` tinyint(1) NOT NULL DEFAULT '0',
  `show_email` tinyint(1) NOT NULL DEFAULT '0',
  `show_tax_1` tinyint(1) NOT NULL DEFAULT '1',
  `show_tax_2` tinyint(1) NOT NULL DEFAULT '0',
  `show_barcode` tinyint(1) NOT NULL DEFAULT '0',
  `show_payments` tinyint(1) NOT NULL DEFAULT '0',
  `show_customer` tinyint(1) NOT NULL DEFAULT '0',
  `customer_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_agent_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_commission_agent` tinyint(1) NOT NULL DEFAULT '0',
  `show_reward_point` tinyint(1) NOT NULL DEFAULT '0',
  `highlight_color` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `module_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `common_settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `business_id` int UNSIGNED NOT NULL,
  `show_letter_head` tinyint(1) NOT NULL DEFAULT '0',
  `letter_head` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_qr_code` tinyint(1) NOT NULL DEFAULT '0',
  `qr_code_fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `design` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'classic',
  `cn_heading` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'cn = credit note',
  `cn_no_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cn_amount_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_tax_headings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `show_previous_bal` tinyint(1) NOT NULL DEFAULT '0',
  `prev_bal_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_previous_balance_due` tinyint(1) NOT NULL DEFAULT '0',
  `previous_balance_due_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_return_label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_custom_fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `location_custom_fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_layouts`
--

INSERT INTO `invoice_layouts` (`id`, `name`, `header_text`, `invoice_no_prefix`, `quotation_no_prefix`, `invoice_heading`, `sub_heading_line1`, `sub_heading_line2`, `sub_heading_line3`, `sub_heading_line4`, `sub_heading_line5`, `invoice_heading_not_paid`, `invoice_heading_paid`, `quotation_heading`, `sub_total_label`, `discount_label`, `tax_label`, `total_label`, `round_off_label`, `total_due_label`, `paid_label`, `show_client_id`, `client_id_label`, `client_tax_label`, `date_label`, `date_time_format`, `show_time`, `show_brand`, `show_sku`, `show_cat_code`, `show_expiry`, `show_lot`, `show_image`, `show_sale_description`, `sales_person_label`, `show_sales_person`, `table_product_label`, `table_qty_label`, `table_unit_price_label`, `table_subtotal_label`, `cat_code_label`, `logo`, `show_logo`, `show_business_name`, `show_location_name`, `show_landmark`, `show_city`, `show_state`, `show_zip_code`, `show_country`, `show_mobile_number`, `show_alternate_number`, `show_email`, `show_tax_1`, `show_tax_2`, `show_barcode`, `show_payments`, `show_customer`, `customer_label`, `commission_agent_label`, `show_commission_agent`, `show_reward_point`, `highlight_color`, `footer_text`, `module_info`, `common_settings`, `is_default`, `business_id`, `show_letter_head`, `letter_head`, `show_qr_code`, `qr_code_fields`, `design`, `cn_heading`, `cn_no_label`, `cn_amount_label`, `table_tax_headings`, `show_previous_bal`, `prev_bal_label`, `show_previous_balance_due`, `previous_balance_due_label`, `change_return_label`, `product_custom_fields`, `contact_custom_fields`, `location_custom_fields`, `created_at`, `updated_at`) VALUES
(1, 'Default', NULL, 'Invoice No.', NULL, 'Invoice', NULL, NULL, NULL, NULL, NULL, '', '', NULL, 'Subtotal', 'Discount', 'Tax', 'Total', NULL, 'Due Amount', 'Total Paid', 0, NULL, NULL, 'Date', NULL, 1, 1, 1, 1, 0, 0, 0, 0, NULL, 1, 'Product', 'Quantity', 'Unit Price', 'Subtotal', NULL, '1785782685_japspos.png', 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 1, 'Customer', NULL, 0, 1, '#000000', 'Thank You For Your Business!', NULL, '{\"show_due_date\":\"1\",\"show_total_in_words\":\"1\",\"num_to_word_format\":\"international\"}', 1, 1, 0, NULL, 0, NULL, 'us_letter', NULL, NULL, NULL, NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, '2026-07-18 18:56:53', '2026-08-05 15:25:24');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_schemes`
--

CREATE TABLE `invoice_schemes` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheme_type` enum('blank','year') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sequential',
  `prefix` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_number` int DEFAULT NULL,
  `invoice_count` int NOT NULL DEFAULT '0',
  `total_digits` int DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_schemes`
--

INSERT INTO `invoice_schemes` (`id`, `business_id`, `name`, `scheme_type`, `number_type`, `prefix`, `start_number`, `invoice_count`, `total_digits`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'Default', 'blank', 'sequential', NULL, 1, 99, 4, 1, '2026-07-18 18:56:53', '2026-08-13 06:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int NOT NULL,
  `file_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uploaded_by` int DEFAULT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_media_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(4, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(5, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(6, '2016_06_01_000004_create_oauth_clients_table', 1),
(7, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(8, '2017_07_05_071953_create_currencies_table', 1),
(9, '2017_07_05_073658_create_business_table', 1),
(10, '2017_07_22_075923_add_business_id_users_table', 1),
(11, '2017_07_23_113209_create_brands_table', 1),
(12, '2017_07_26_083429_create_permission_tables', 1),
(13, '2017_07_26_110000_create_tax_rates_table', 1),
(14, '2017_07_26_122313_create_units_table', 1),
(15, '2017_07_27_075706_create_contacts_table', 1),
(16, '2017_08_04_071038_create_categories_table', 1),
(17, '2017_08_08_115903_create_products_table', 1),
(18, '2017_08_09_061616_create_variation_templates_table', 1),
(19, '2017_08_09_061638_create_variation_value_templates_table', 1),
(20, '2017_08_10_061146_create_product_variations_table', 1),
(21, '2017_08_10_061216_create_variations_table', 1),
(22, '2017_08_19_054827_create_transactions_table', 1),
(23, '2017_08_31_073533_create_purchase_lines_table', 1),
(24, '2017_10_15_064638_create_transaction_payments_table', 1),
(25, '2017_10_31_065621_add_default_sales_tax_to_business_table', 1),
(26, '2017_11_20_051930_create_table_group_sub_taxes', 1),
(27, '2017_11_20_063603_create_transaction_sell_lines', 1),
(28, '2017_11_21_064540_create_barcodes_table', 1),
(29, '2017_11_23_181237_create_invoice_schemes_table', 1),
(30, '2017_12_25_122822_create_business_locations_table', 1),
(31, '2017_12_25_160253_add_location_id_to_transactions_table', 1),
(32, '2017_12_25_163227_create_variation_location_details_table', 1),
(33, '2018_01_04_115627_create_sessions_table', 1),
(34, '2018_01_05_112817_create_invoice_layouts_table', 1),
(35, '2018_01_06_112303_add_invoice_scheme_id_and_invoice_layout_id_to_business_locations', 1),
(36, '2018_01_08_104124_create_expense_categories_table', 1),
(37, '2018_01_08_123327_modify_transactions_table_for_expenses', 1),
(38, '2018_01_09_111005_modify_payment_status_in_transactions_table', 1),
(39, '2018_01_09_111109_add_paid_on_column_to_transaction_payments_table', 1),
(40, '2018_01_25_172439_add_printer_related_fields_to_business_locations_table', 1),
(41, '2018_01_27_184322_create_printers_table', 1),
(42, '2018_01_30_181442_create_cash_registers_table', 1),
(43, '2018_01_31_125836_create_cash_register_transactions_table', 1),
(44, '2018_02_07_173326_modify_business_table', 1),
(45, '2018_02_08_105425_add_enable_product_expiry_column_to_business_table', 1),
(46, '2018_02_08_111027_add_expiry_period_and_expiry_period_type_columns_to_products_table', 1),
(47, '2018_02_08_131118_add_mfg_date_and_exp_date_purchase_lines_table', 1),
(48, '2018_02_08_155348_add_exchange_rate_to_transactions_table', 1),
(49, '2018_02_09_124945_modify_transaction_payments_table_for_contact_payments', 1),
(50, '2018_02_12_113640_create_transaction_sell_lines_purchase_lines_table', 1),
(51, '2018_02_12_114605_add_quantity_sold_in_purchase_lines_table', 1),
(52, '2018_02_13_183323_alter_decimal_fields_size', 1),
(53, '2018_02_14_161928_add_transaction_edit_days_to_business_table', 1),
(54, '2018_02_15_161032_add_document_column_to_transactions_table', 1),
(55, '2018_02_17_124709_add_more_options_to_invoice_layouts', 1),
(56, '2018_02_19_111517_add_keyboard_shortcut_column_to_business_table', 1),
(57, '2018_02_19_121537_stock_adjustment_move_to_transaction_table', 1),
(58, '2018_02_20_165505_add_is_direct_sale_column_to_transactions_table', 1),
(59, '2018_02_21_105329_create_system_table', 1),
(60, '2018_02_23_100549_version_1_2', 1),
(61, '2018_02_23_125648_add_enable_editing_sp_from_purchase_column_to_business_table', 1),
(62, '2018_02_26_103612_add_sales_commission_agent_column_to_business_table', 1),
(63, '2018_02_26_130519_modify_users_table_for_sales_cmmsn_agnt', 1),
(64, '2018_02_26_134500_add_commission_agent_to_transactions_table', 1),
(65, '2018_02_27_121422_add_item_addition_method_to_business_table', 1),
(66, '2018_02_27_170232_modify_transactions_table_for_stock_transfer', 1),
(67, '2018_03_05_153510_add_enable_inline_tax_column_to_business_table', 1),
(68, '2018_03_06_210206_modify_product_barcode_types', 1),
(69, '2018_03_13_181541_add_expiry_type_to_business_table', 1),
(70, '2018_03_16_113446_product_expiry_setting_for_business', 1),
(71, '2018_03_19_113601_add_business_settings_options', 1),
(72, '2018_03_26_125334_add_pos_settings_to_business_table', 1),
(73, '2018_03_26_165350_create_customer_groups_table', 1),
(74, '2018_03_27_122720_customer_group_related_changes_in_tables', 1),
(75, '2018_03_29_110138_change_tax_field_to_nullable_in_business_table', 1),
(76, '2018_03_29_115502_add_changes_for_sr_number_in_products_and_sale_lines_table', 1),
(77, '2018_03_29_134340_add_inline_discount_fields_in_purchase_lines', 1),
(78, '2018_03_31_140921_update_transactions_table_exchange_rate', 1),
(79, '2018_04_03_103037_add_contact_id_to_contacts_table', 1),
(80, '2018_04_03_122709_add_changes_to_invoice_layouts_table', 1),
(81, '2018_04_09_135320_change_exchage_rate_size_in_business_table', 1),
(82, '2018_04_17_123122_add_lot_number_to_business', 1),
(83, '2018_04_17_160845_add_product_racks_table', 1),
(84, '2018_04_20_182015_create_res_tables_table', 1),
(85, '2018_04_24_105246_restaurant_fields_in_transaction_table', 1),
(86, '2018_04_24_114149_add_enabled_modules_business_table', 1),
(87, '2018_04_24_133704_add_modules_fields_in_invoice_layout_table', 1),
(88, '2018_04_27_132653_quotation_related_change', 1),
(89, '2018_05_02_104439_add_date_format_and_time_format_to_business', 1),
(90, '2018_05_02_111939_add_sell_return_to_transaction_payments', 1),
(91, '2018_05_14_114027_add_rows_positions_for_products', 1),
(92, '2018_05_14_125223_add_weight_to_products_table', 1),
(93, '2018_05_14_164754_add_opening_stock_permission', 1),
(94, '2018_05_15_134729_add_design_to_invoice_layouts', 1),
(95, '2018_05_16_183307_add_tax_fields_invoice_layout', 1),
(96, '2018_05_18_191956_add_sell_return_to_transaction_table', 1),
(97, '2018_05_21_131349_add_custom_fileds_to_contacts_table', 1),
(98, '2018_05_21_131607_invoice_layout_fields_for_sell_return', 1),
(99, '2018_05_21_131949_add_custom_fileds_and_website_to_business_locations_table', 1),
(100, '2018_05_22_123527_create_reference_counts_table', 1),
(101, '2018_05_22_154540_add_ref_no_prefixes_column_to_business_table', 1),
(102, '2018_05_24_132620_add_ref_no_column_to_transaction_payments_table', 1),
(103, '2018_05_24_161026_add_location_id_column_to_business_location_table', 1),
(104, '2018_05_25_180603_create_modifiers_related_table', 1),
(105, '2018_05_29_121714_add_purchase_line_id_to_stock_adjustment_line_table', 1),
(106, '2018_05_31_114645_add_res_order_status_column_to_transactions_table', 1),
(107, '2018_06_05_103530_rename_purchase_line_id_in_stock_adjustment_lines_table', 1),
(108, '2018_06_05_111905_modify_products_table_for_modifiers', 1),
(109, '2018_06_06_110524_add_parent_sell_line_id_column_to_transaction_sell_lines_table', 1),
(110, '2018_06_07_152443_add_is_service_staff_to_roles_table', 1),
(111, '2018_06_07_182258_add_image_field_to_products_table', 1),
(112, '2018_06_13_133705_create_bookings_table', 1),
(113, '2018_06_15_173636_add_email_column_to_contacts_table', 1),
(114, '2018_06_27_182835_add_superadmin_related_fields_business', 1),
(115, '2018_07_10_101913_add_custom_fields_to_products_table', 1),
(116, '2018_07_17_103434_add_sales_person_name_label_to_invoice_layouts_table', 1),
(117, '2018_07_17_163920_add_theme_skin_color_column_to_business_table', 1),
(118, '2018_07_24_160319_add_lot_no_line_id_to_transaction_sell_lines_table', 1),
(119, '2018_07_25_110004_add_show_expiry_and_show_lot_colums_to_invoice_layouts_table', 1),
(120, '2018_07_25_172004_add_discount_columns_to_transaction_sell_lines_table', 1),
(121, '2018_07_26_124720_change_design_column_type_in_invoice_layouts_table', 1),
(122, '2018_07_26_170424_add_unit_price_before_discount_column_to_transaction_sell_line_table', 1),
(123, '2018_07_28_103614_add_credit_limit_column_to_contacts_table', 1),
(124, '2018_08_08_110755_add_new_payment_methods_to_transaction_payments_table', 1),
(125, '2018_08_08_122225_modify_cash_register_transactions_table_for_new_payment_methods', 1),
(126, '2018_08_14_104036_add_opening_balance_type_to_transactions_table', 1),
(127, '2018_09_04_155900_create_accounts_table', 1),
(128, '2018_09_06_114438_create_selling_price_groups_table', 1),
(129, '2018_09_06_154057_create_variation_group_prices_table', 1),
(130, '2018_09_07_102413_add_permission_to_access_default_selling_price', 1),
(131, '2018_09_07_134858_add_selling_price_group_id_to_transactions_table', 1),
(132, '2018_09_10_112448_update_product_type_to_single_if_null_in_products_table', 1),
(133, '2018_09_10_152703_create_account_transactions_table', 1),
(134, '2018_09_10_173656_add_account_id_column_to_transaction_payments_table', 1),
(135, '2018_09_19_123914_create_notification_templates_table', 1),
(136, '2018_09_22_110504_add_sms_and_email_settings_columns_to_business_table', 1),
(137, '2018_09_24_134942_add_lot_no_line_id_to_stock_adjustment_lines_table', 1),
(138, '2018_09_26_105557_add_transaction_payments_for_existing_expenses', 1),
(139, '2018_09_27_111609_modify_transactions_table_for_purchase_return', 1),
(140, '2018_09_27_131154_add_quantity_returned_column_to_purchase_lines_table', 1),
(141, '2018_10_02_131401_add_return_quantity_column_to_transaction_sell_lines_table', 1),
(142, '2018_10_03_104918_add_qty_returned_column_to_transaction_sell_lines_purchase_lines_table', 1),
(143, '2018_10_03_185947_add_default_notification_templates_to_database', 1),
(144, '2018_10_09_153105_add_business_id_to_transaction_payments_table', 1),
(145, '2018_10_16_135229_create_permission_for_sells_and_purchase', 1),
(146, '2018_10_22_114441_add_columns_for_variable_product_modifications', 1),
(147, '2018_10_22_134428_modify_variable_product_data', 1),
(148, '2018_10_30_181558_add_table_tax_headings_to_invoice_layout', 1),
(149, '2018_10_31_122619_add_pay_terms_field_transactions_table', 1),
(150, '2018_10_31_161328_add_new_permissions_for_pos_screen', 1),
(151, '2018_10_31_174752_add_access_selected_contacts_only_to_users_table', 1),
(152, '2018_10_31_175627_add_user_contact_access', 1),
(153, '2018_10_31_180559_add_auto_send_sms_column_to_notification_templates_table', 1),
(154, '2018_11_02_171949_change_card_type_column_to_varchar_in_transaction_payments_table', 1),
(155, '2018_11_08_105621_add_role_permissions', 1),
(156, '2018_11_26_114135_add_is_suspend_column_to_transactions_table', 1),
(157, '2018_11_28_104410_modify_units_table_for_multi_unit', 1),
(158, '2018_11_28_170952_add_sub_unit_id_to_purchase_lines_and_sell_lines', 1),
(159, '2018_11_29_115918_add_primary_key_in_system_table', 1),
(160, '2018_12_03_185546_add_product_description_column_to_products_table', 1),
(161, '2018_12_06_114937_modify_system_table_and_users_table', 1),
(162, '2018_12_13_160007_add_custom_fields_display_options_to_invoice_layouts_table', 1),
(163, '2018_12_14_103307_modify_system_table', 1),
(164, '2018_12_18_133837_add_prev_balance_due_columns_to_invoice_layouts_table', 1),
(165, '2018_12_18_170656_add_invoice_token_column_to_transaction_table', 1),
(166, '2018_12_20_133639_add_date_time_format_column_to_invoice_layouts_table', 1),
(167, '2018_12_21_120659_add_recurring_invoice_fields_to_transactions_table', 1),
(168, '2018_12_24_154933_create_notifications_table', 1),
(169, '2019_01_08_112015_add_document_column_to_transaction_payments_table', 1),
(170, '2019_01_10_124645_add_account_permission', 1),
(171, '2019_01_16_125825_add_subscription_no_column_to_transactions_table', 1),
(172, '2019_01_28_111647_add_order_addresses_column_to_transactions_table', 1),
(173, '2019_02_13_173821_add_is_inactive_column_to_products_table', 1),
(174, '2019_02_19_103118_create_discounts_table', 1),
(175, '2019_02_21_120324_add_discount_id_column_to_transaction_sell_lines_table', 1),
(176, '2019_02_21_134324_add_permission_for_discount', 1),
(177, '2019_03_04_170832_add_service_staff_columns_to_transaction_sell_lines_table', 1),
(178, '2019_03_09_102425_add_sub_type_column_to_transactions_table', 1),
(179, '2019_03_09_124457_add_indexing_transaction_sell_lines_purchase_lines_table', 1),
(180, '2019_03_12_120336_create_activity_log_table', 1),
(181, '2019_03_15_132925_create_media_table', 1),
(182, '2019_05_08_130339_add_indexing_to_parent_id_in_transaction_payments_table', 1),
(183, '2019_05_10_132311_add_missing_column_indexing', 1),
(184, '2019_05_14_091812_add_show_image_column_to_invoice_layouts_table', 1),
(185, '2019_05_25_104922_add_view_purchase_price_permission', 1),
(186, '2019_06_17_103515_add_profile_informations_columns_to_users_table', 1),
(187, '2019_06_18_135524_add_permission_to_view_own_sales_only', 1),
(188, '2019_06_19_112058_add_database_changes_for_reward_points', 1),
(189, '2019_06_28_133732_change_type_column_to_string_in_transactions_table', 1),
(190, '2019_07_13_111420_add_is_created_from_api_column_to_transactions_table', 1),
(191, '2019_07_15_165136_add_fields_for_combo_product', 1),
(192, '2019_07_19_103446_add_mfg_quantity_used_column_to_purchase_lines_table', 1),
(193, '2019_07_22_152649_add_not_for_selling_in_product_table', 1),
(194, '2019_07_29_185351_add_show_reward_point_column_to_invoice_layouts_table', 1),
(195, '2019_08_08_162302_add_sub_units_related_fields', 1),
(196, '2019_08_26_133419_update_price_fields_decimal_point', 1),
(197, '2019_09_02_160054_remove_location_permissions_from_roles', 1),
(198, '2019_09_03_185259_add_permission_for_pos_screen', 1),
(199, '2019_09_04_163141_add_location_id_to_cash_registers_table', 1),
(200, '2019_09_04_184008_create_types_of_services_table', 1),
(201, '2019_09_06_131445_add_types_of_service_fields_to_transactions_table', 1),
(202, '2019_09_09_134810_add_default_selling_price_group_id_column_to_business_locations_table', 1),
(203, '2019_09_12_105616_create_product_locations_table', 1),
(204, '2019_09_17_122522_add_custom_labels_column_to_business_table', 1),
(205, '2019_09_18_164319_add_shipping_fields_to_transactions_table', 1),
(206, '2019_09_19_170927_close_all_active_registers', 1),
(207, '2019_09_23_161906_add_media_description_cloumn_to_media_table', 1),
(208, '2019_10_18_155633_create_account_types_table', 1),
(209, '2019_10_22_163335_add_common_settings_column_to_business_table', 1),
(210, '2019_10_29_132521_add_update_purchase_status_permission', 1),
(211, '2019_11_09_110522_add_indexing_to_lot_number', 1),
(212, '2019_11_19_170824_add_is_active_column_to_business_locations_table', 1),
(213, '2019_11_21_162913_change_quantity_field_types_to_decimal', 1),
(214, '2019_11_25_160340_modify_categories_table_for_polymerphic_relationship', 1),
(215, '2019_12_02_105025_create_warranties_table', 1),
(216, '2019_12_03_180342_add_common_settings_field_to_invoice_layouts_table', 1),
(217, '2019_12_05_183955_add_more_fields_to_users_table', 1),
(218, '2019_12_06_174904_add_change_return_label_column_to_invoice_layouts_table', 1),
(219, '2019_12_11_121307_add_draft_and_quotation_list_permissions', 1),
(220, '2019_12_12_180126_copy_expense_total_to_total_before_tax', 1),
(221, '2019_12_19_181412_make_alert_quantity_field_nullable_on_products_table', 1),
(222, '2019_12_25_173413_create_dashboard_configurations_table', 1),
(223, '2020_01_08_133506_create_document_and_notes_table', 1),
(224, '2020_01_09_113252_add_cc_bcc_column_to_notification_templates_table', 1),
(225, '2020_01_16_174818_add_round_off_amount_field_to_transactions_table', 1),
(226, '2020_01_28_162345_add_weighing_scale_settings_in_business_settings_table', 1),
(227, '2020_02_18_172447_add_import_fields_to_transactions_table', 1),
(228, '2020_03_13_135844_add_is_active_column_to_selling_price_groups_table', 1),
(229, '2020_03_16_115449_add_contact_status_field_to_contacts_table', 1),
(230, '2020_03_26_124736_add_allow_login_column_in_users_table', 1),
(231, '2020_04_13_154150_add_feature_products_column_to_business_loactions', 1),
(232, '2020_04_15_151802_add_user_type_to_users_table', 1),
(233, '2020_04_22_153905_add_subscription_repeat_on_column_to_transactions_table', 1),
(234, '2020_04_28_111436_add_shipping_address_to_contacts_table', 1),
(235, '2020_06_01_094654_add_max_sale_discount_column_to_users_table', 1),
(236, '2020_06_12_162245_modify_contacts_table', 1),
(237, '2020_06_22_103104_change_recur_interval_default_to_one', 1),
(238, '2020_07_09_174621_add_balance_field_to_contacts_table', 1),
(239, '2020_07_23_104933_change_status_column_to_varchar_in_transaction_table', 1),
(240, '2020_09_07_171059_change_completed_stock_transfer_status_to_final', 1),
(241, '2020_09_21_123224_modify_booking_status_column_in_bookings_table', 1),
(242, '2020_09_22_121639_create_discount_variations_table', 1),
(243, '2020_10_05_121550_modify_business_location_table_for_invoice_layout', 1),
(244, '2020_10_16_175726_set_status_as_received_for_opening_stock', 1),
(245, '2020_10_23_170823_add_for_group_tax_column_to_tax_rates_table', 1),
(246, '2020_11_04_130940_add_more_custom_fields_to_contacts_table', 1),
(247, '2020_11_10_152841_add_cash_register_permissions', 1),
(248, '2020_11_17_164041_modify_type_column_to_varchar_in_contacts_table', 1),
(249, '2020_12_18_181447_add_shipping_custom_fields_to_transactions_table', 1),
(250, '2020_12_22_164303_add_sub_status_column_to_transactions_table', 1),
(251, '2020_12_24_153050_add_custom_fields_to_transactions_table', 1),
(252, '2020_12_28_105403_add_whatsapp_text_column_to_notification_templates_table', 1),
(253, '2020_12_29_165925_add_model_document_type_to_media_table', 1),
(254, '2021_02_08_175632_add_contact_number_fields_to_users_table', 1),
(255, '2021_02_11_172217_add_indexing_for_multiple_columns', 1),
(256, '2021_02_23_122043_add_more_columns_to_customer_groups_table', 1),
(257, '2021_02_24_175551_add_print_invoice_permission_to_all_roles', 1),
(258, '2021_03_03_162021_add_purchase_order_columns_to_purchase_lines_and_transactions_table', 1),
(259, '2021_03_11_120229_add_sales_order_columns', 1),
(260, '2021_03_16_120705_add_business_id_to_activity_log_table', 1),
(261, '2021_03_16_153427_add_code_columns_to_business_table', 1),
(262, '2021_03_18_173308_add_account_details_column_to_accounts_table', 1),
(263, '2021_03_18_183119_add_prefer_payment_account_columns_to_transactions_table', 1),
(264, '2021_03_22_120810_add_more_types_of_service_custom_fields', 1),
(265, '2021_03_24_183132_add_shipping_export_custom_field_details_to_contacts_table', 1),
(266, '2021_03_25_170715_add_export_custom_fields_info_to_transactions_table', 1),
(267, '2021_04_15_063449_add_denominations_column_to_cash_registers_table', 1),
(268, '2021_05_22_083426_add_indexing_to_account_transactions_table', 1),
(269, '2021_07_08_065808_add_additional_expense_columns_to_transaction_table', 1),
(270, '2021_07_13_082918_add_qr_code_columns_to_invoice_layouts_table', 1),
(271, '2021_07_21_061615_add_fields_to_show_commission_agent_in_invoice_layout', 1),
(272, '2021_08_13_105549_add_crm_contact_id_to_users_table', 1),
(273, '2021_08_25_114932_add_payment_link_fields_to_transaction_payments_table', 1),
(274, '2021_09_01_063110_add_spg_column_to_discounts_table', 1),
(275, '2021_09_03_061528_modify_cash_register_transactions_table', 1),
(276, '2021_10_05_061658_add_source_column_to_transactions_table', 1),
(277, '2021_12_16_121851_add_parent_id_column_to_expense_categories_table', 1),
(278, '2022_04_14_075120_add_payment_type_column_to_transaction_payments_table', 1),
(279, '2022_04_21_083327_create_cash_denominations_table', 1),
(280, '2022_05_10_055307_add_delivery_date_column_to_transactions_table', 1),
(281, '2022_06_13_123135_add_currency_precision_and_quantity_precision_fields_to_business_table', 1),
(282, '2022_06_28_133342_add_secondary_unit_columns_to_products_sell_line_purchase_lines_tables', 1),
(283, '2022_07_13_114307_create_purchase_requisition_related_columns', 1),
(284, '2022_08_25_132707_add_service_staff_timer_fields_to_products_and_users_table', 1),
(285, '2023_01_28_114255_add_letter_head_column_to_invoice_layouts_table', 1),
(286, '2023_02_11_161510_add_event_column_to_activity_log_table', 1),
(287, '2023_02_11_161511_add_batch_uuid_column_to_activity_log_table', 1),
(288, '2023_03_02_170312_add_provider_to_oauth_clients_table', 1),
(289, '2023_03_21_122731_add_sale_invoice_scheme_id_business_table', 1),
(290, '2023_03_21_170446_add_number_type_to_invoice_scheme', 1),
(291, '2023_04_17_155216_add_custom_fields_to_products', 1),
(292, '2023_04_28_130247_add_price_type_to_group_price_table', 1),
(293, '2023_06_21_033923_add_delivery_person_in_transactions', 1),
(294, '2023_09_13_153555_add_service_staff_pin_columns_in_users', 1),
(295, '2023_09_15_154404_add_is_kitchen_order_in_transactions', 1),
(296, '2023_12_06_152840_add_contact_type_in_contacts', 1),
(297, '2024_10_03_151459_modify_transaction_sell_lines_purchase_lines_table', 1),
(298, '2025_03_07_114637_add_more_addresh_column_in_contact', 1),
(299, '2025_09_19_120000_add_previous_balance_due_fields_to_invoice_layouts_table', 1),
(300, '2026_04_03_000001_add_transaction_payment_id_to_cash_register_transactions', 1),
(301, '2026_04_14_000001_add_tunisian_dinar_currency', 1),
(302, '2026_07_20_000001_create_employee_attendances_tables', 2),
(303, '2026_07_20_000002_add_attendance_permissions', 2),
(304, '2026_07_22_000001_create_ecommerce_tables', 3),
(305, '2026_07_23_000010_create_ecom_customer_licenses_table', 4),
(306, '2026_07_23_230000_create_crm_tables', 5),
(307, '2026_07_23_231000_add_crm_is_order_request_to_transactions', 6),
(308, '2026_07_23_234000_create_ai_assistance_settings_table', 7),
(309, '2026_07_27_120000_add_transactions_composite_index_for_reports', 8),
(310, '2026_08_03_180000_add_shipping_methods_and_shipment_fields', 9),
(311, '2026_08_03_184500_seed_additional_shipping_methods', 10),
(312, '2026_08_04_012800_fix_walk_in_customer_credit_limit', 11),
(313, '2026_08_05_215200_sync_default_invoice_layout_checkboxes_and_footer', 12),
(314, '2026_08_05_221000_sync_default_pos_settings_for_all_businesses', 13),
(315, '2026_08_05_235900_add_print_per_sale_fields_to_business_table', 14),
(316, '2026_08_06_210700_add_show_in_pos_to_categories_and_brands', 15),
(317, '2026_08_07_202300_add_pos_screen_layout_to_business_table', 16),
(318, '2026_08_07_000001_add_attendance_edit_permission', 17),
(319, '2026_08_13_000020_convert_ecommerce_sells_to_sales_orders', 18);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` int UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(80, 'App\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` int UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\User', 1),
(3, 'App\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int NOT NULL,
  `template_for` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sms_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `whatsapp_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cc` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bcc` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_send` tinyint(1) NOT NULL DEFAULT '0',
  `auto_send_sms` tinyint(1) NOT NULL DEFAULT '0',
  `auto_send_wa_notif` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `business_id`, `template_for`, `email_body`, `sms_body`, `whatsapp_text`, `subject`, `cc`, `bcc`, `auto_send`, `auto_send_sms`, `auto_send_wa_notif`, `created_at`, `updated_at`) VALUES
(1, 1, 'new_sale', '<p>Dear {contact_name},</p>\n\n                    <p>Your invoice number is {invoice_number}<br />\n                    Total amount: {total_amount}<br />\n                    Paid amount: {received_amount}</p>\n\n                    <p>Thank you for shopping with us.</p>\n\n                    <p>{business_logo}</p>\n\n                    <p>&nbsp;</p>', 'Dear {contact_name}, Thank you for shopping with us. {business_name}', NULL, 'Thank you from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(2, 1, 'payment_received', '<p>Dear {contact_name},</p>\n\n                <p>We have received a payment of {received_amount}</p>\n\n                <p>{business_logo}</p>', 'Dear {contact_name}, We have received a payment of {received_amount}. {business_name}', NULL, 'Payment Received, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(3, 1, 'payment_reminder', '<p>Dear {contact_name},</p>\n\n                    <p>This is to remind you that you have pending payment of {due_amount}. Kindly pay it as soon as possible.</p>\n\n                    <p>{business_logo}</p>', 'Dear {contact_name}, You have pending payment of {due_amount}. Kindly pay it as soon as possible. {business_name}', NULL, 'Payment Reminder, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(4, 1, 'new_booking', '<p>Dear {contact_name},</p>\n\n                    <p>Your booking is confirmed</p>\n\n                    <p>Date: {start_time} to {end_time}</p>\n\n                    <p>Table: {table}</p>\n\n                    <p>Location: {location}</p>\n\n                    <p>{business_logo}</p>', 'Dear {contact_name}, Your booking is confirmed. Date: {start_time} to {end_time}, Table: {table}, Location: {location}', NULL, 'Booking Confirmed - {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(5, 1, 'new_order', '<p>Dear {contact_name},</p>\n\n                    <p>We have a new order with reference number {order_ref_number}. Kindly process the products as soon as possible.</p>\n\n                    <p>{business_name}<br />\n                    {business_logo}</p>', 'Dear {contact_name}, We have a new order with reference number {order_ref_number}. Kindly process the products as soon as possible. {business_name}', NULL, 'New Order, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(6, 1, 'payment_paid', '<p>Dear {contact_name},</p>\n\n                    <p>We have paid amount {paid_amount} again invoice number {order_ref_number}.<br />\n                    Kindly note it down.</p>\n\n                    <p>{business_name}<br />\n                    {business_logo}</p>', 'We have paid amount {paid_amount} again invoice number {order_ref_number}.\n                    Kindly note it down. {business_name}', NULL, 'Payment Paid, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(7, 1, 'items_received', '<p>Dear {contact_name},</p>\n\n                    <p>We have received all items from invoice reference number {order_ref_number}. Thank you for processing it.</p>\n\n                    <p>{business_name}<br />\n                    {business_logo}</p>', 'We have received all items from invoice reference number {order_ref_number}. Thank you for processing it. {business_name}', NULL, 'Items received, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(8, 1, 'items_pending', '<p>Dear {contact_name},<br />\n                    This is to remind you that we have not yet received some items from invoice reference number {order_ref_number}. Please process it as soon as possible.</p>\n\n                    <p>{business_name}<br />\n                    {business_logo}</p>', 'This is to remind you that we have not yet received some items from invoice reference number {order_ref_number} . Please process it as soon as possible.{business_name}', NULL, 'Items Pending, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(9, 1, 'new_quotation', '<p>Dear {contact_name},</p>\n\n                    <p>Your quotation number is {invoice_number}<br />\n                    Total amount: {total_amount}</p>\n\n                    <p>Thank you for shopping with us.</p>\n\n                    <p>{business_logo}</p>\n\n                    <p>&nbsp;</p>', 'Dear {contact_name}, Thank you for shopping with us. {business_name}', NULL, 'Thank you from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(10, 1, 'purchase_order', '<p>Dear {contact_name},</p>\n\n                    <p>We have a new purchase order with reference number {order_ref_number}. The respective invoice is attached here with.</p>\n\n                    <p>{business_logo}</p>', 'We have a new purchase order with reference number {order_ref_number}. {business_name}', NULL, 'New Purchase Order, from {business_name}', NULL, NULL, 0, 0, 0, '2026-07-18 18:56:54', '2026-07-18 18:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `client_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `client_id` int UNSIGNED NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int UNSIGNED NOT NULL,
  `client_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets_contacts`
--

CREATE TABLE `password_resets_contacts` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'profit_loss_report.view', 'web', '2026-06-04 15:44:24', NULL),
(2, 'direct_sell.access', 'web', '2026-06-04 15:44:24', NULL),
(3, 'product.opening_stock', 'web', '2026-06-04 15:44:30', '2026-06-04 15:44:30'),
(4, 'crud_all_bookings', 'web', '2026-06-04 15:44:32', '2026-06-04 15:44:32'),
(5, 'crud_own_bookings', 'web', '2026-06-04 15:44:32', '2026-06-04 15:44:32'),
(6, 'access_default_selling_price', 'web', '2026-06-04 15:44:35', '2026-06-04 15:44:35'),
(7, 'purchase.payments', 'web', '2026-06-04 15:44:36', '2026-06-04 15:44:36'),
(8, 'sell.payments', 'web', '2026-06-04 15:44:36', '2026-06-04 15:44:36'),
(9, 'edit_product_price_from_sale_screen', 'web', '2026-06-04 15:44:36', '2026-06-04 15:44:36'),
(10, 'edit_product_discount_from_sale_screen', 'web', '2026-06-04 15:44:36', '2026-06-04 15:44:36'),
(11, 'roles.view', 'web', '2026-06-04 15:44:37', '2026-06-04 15:44:37'),
(12, 'roles.create', 'web', '2026-06-04 15:44:37', '2026-06-04 15:44:37'),
(13, 'roles.update', 'web', '2026-06-04 15:44:37', '2026-06-04 15:44:37'),
(14, 'roles.delete', 'web', '2026-06-04 15:44:37', '2026-06-04 15:44:37'),
(15, 'account.access', 'web', '2026-06-04 15:44:38', '2026-06-04 15:44:38'),
(16, 'discount.access', 'web', '2026-06-04 15:44:39', '2026-06-04 15:44:39'),
(17, 'view_purchase_price', 'web', '2026-06-04 15:44:41', '2026-06-04 15:44:41'),
(18, 'view_own_sell_only', 'web', '2026-06-04 15:44:41', '2026-06-04 15:44:41'),
(19, 'edit_product_discount_from_pos_screen', 'web', '2026-06-04 15:44:43', '2026-06-04 15:44:43'),
(20, 'edit_product_price_from_pos_screen', 'web', '2026-06-04 15:44:43', '2026-06-04 15:44:43'),
(21, 'access_shipping', 'web', '2026-06-04 15:44:44', '2026-06-04 15:44:44'),
(22, 'purchase.update_status', 'web', '2026-06-04 15:44:44', '2026-06-04 15:44:44'),
(23, 'list_drafts', 'web', '2026-06-04 15:44:46', '2026-06-04 15:44:46'),
(24, 'list_quotations', 'web', '2026-06-04 15:44:46', '2026-06-04 15:44:46'),
(25, 'view_cash_register', 'web', '2026-06-04 15:44:50', '2026-06-04 15:44:50'),
(26, 'close_cash_register', 'web', '2026-06-04 15:44:50', '2026-06-04 15:44:50'),
(27, 'print_invoice', 'web', '2026-06-04 15:44:56', '2026-06-04 15:44:56'),
(28, 'user.view', 'web', '2026-06-04 15:45:24', NULL),
(29, 'user.create', 'web', '2026-06-04 15:45:24', NULL),
(30, 'user.update', 'web', '2026-06-04 15:45:24', NULL),
(31, 'user.delete', 'web', '2026-06-04 15:45:24', NULL),
(32, 'supplier.view', 'web', '2026-06-04 15:45:24', NULL),
(33, 'supplier.create', 'web', '2026-06-04 15:45:24', NULL),
(34, 'supplier.update', 'web', '2026-06-04 15:45:24', NULL),
(35, 'supplier.delete', 'web', '2026-06-04 15:45:24', NULL),
(36, 'customer.view', 'web', '2026-06-04 15:45:24', NULL),
(37, 'customer.create', 'web', '2026-06-04 15:45:24', NULL),
(38, 'customer.update', 'web', '2026-06-04 15:45:24', NULL),
(39, 'customer.delete', 'web', '2026-06-04 15:45:24', NULL),
(40, 'product.view', 'web', '2026-06-04 15:45:24', NULL),
(41, 'product.create', 'web', '2026-06-04 15:45:24', NULL),
(42, 'product.update', 'web', '2026-06-04 15:45:24', NULL),
(43, 'product.delete', 'web', '2026-06-04 15:45:24', NULL),
(44, 'purchase.view', 'web', '2026-06-04 15:45:24', NULL),
(45, 'purchase.create', 'web', '2026-06-04 15:45:24', NULL),
(46, 'purchase.update', 'web', '2026-06-04 15:45:24', NULL),
(47, 'purchase.delete', 'web', '2026-06-04 15:45:24', NULL),
(48, 'sell.view', 'web', '2026-06-04 15:45:24', NULL),
(49, 'sell.create', 'web', '2026-06-04 15:45:24', NULL),
(50, 'sell.update', 'web', '2026-06-04 15:45:24', NULL),
(51, 'sell.delete', 'web', '2026-06-04 15:45:24', NULL),
(52, 'purchase_n_sell_report.view', 'web', '2026-06-04 15:45:24', NULL),
(53, 'contacts_report.view', 'web', '2026-06-04 15:45:24', NULL),
(54, 'stock_report.view', 'web', '2026-06-04 15:45:24', NULL),
(55, 'tax_report.view', 'web', '2026-06-04 15:45:24', NULL),
(56, 'trending_product_report.view', 'web', '2026-06-04 15:45:24', NULL),
(57, 'register_report.view', 'web', '2026-06-04 15:45:24', NULL),
(58, 'sales_representative.view', 'web', '2026-06-04 15:45:24', NULL),
(59, 'expense_report.view', 'web', '2026-06-04 15:45:24', NULL),
(60, 'business_settings.access', 'web', '2026-06-04 15:45:24', NULL),
(61, 'barcode_settings.access', 'web', '2026-06-04 15:45:24', NULL),
(62, 'invoice_settings.access', 'web', '2026-06-04 15:45:24', NULL),
(63, 'brand.view', 'web', '2026-06-04 15:45:24', NULL),
(64, 'brand.create', 'web', '2026-06-04 15:45:24', NULL),
(65, 'brand.update', 'web', '2026-06-04 15:45:24', NULL),
(66, 'brand.delete', 'web', '2026-06-04 15:45:24', NULL),
(67, 'tax_rate.view', 'web', '2026-06-04 15:45:24', NULL),
(68, 'tax_rate.create', 'web', '2026-06-04 15:45:24', NULL),
(69, 'tax_rate.update', 'web', '2026-06-04 15:45:24', NULL),
(70, 'tax_rate.delete', 'web', '2026-06-04 15:45:24', NULL),
(71, 'unit.view', 'web', '2026-06-04 15:45:24', NULL),
(72, 'unit.create', 'web', '2026-06-04 15:45:24', NULL),
(73, 'unit.update', 'web', '2026-06-04 15:45:24', NULL),
(74, 'unit.delete', 'web', '2026-06-04 15:45:24', NULL),
(75, 'category.view', 'web', '2026-06-04 15:45:24', NULL),
(76, 'category.create', 'web', '2026-06-04 15:45:24', NULL),
(77, 'category.update', 'web', '2026-06-04 15:45:24', NULL),
(78, 'category.delete', 'web', '2026-06-04 15:45:24', NULL),
(79, 'expense.access', 'web', '2026-06-04 15:45:24', NULL),
(80, 'access_all_locations', 'web', '2026-06-04 15:45:24', NULL),
(81, 'dashboard.data', 'web', '2026-06-04 15:45:24', NULL),
(82, 'location.1', 'web', '2026-06-04 16:29:55', '2026-06-04 16:29:55'),
(83, 'selling_price_group.1', 'web', '2026-07-15 16:01:55', '2026-07-15 16:01:55'),
(84, 'selling_price_group.2', 'web', '2026-07-15 16:01:55', '2026-07-15 16:01:55'),
(85, 'attendance.clock', 'web', '2026-07-20 16:30:24', '2026-07-20 16:30:24'),
(86, 'attendance.view', 'web', '2026-07-20 16:30:24', '2026-07-20 16:30:24'),
(87, 'attendance.view_all', 'web', '2026-07-20 16:30:24', '2026-07-20 16:30:24'),
(88, 'shipping_method.view', 'web', '2026-08-03 17:12:41', '2026-08-03 17:12:41'),
(89, 'shipping_method.create', 'web', '2026-08-03 17:12:41', '2026-08-03 17:12:41'),
(90, 'shipping_method.update', 'web', '2026-08-03 17:12:41', '2026-08-03 17:12:41'),
(91, 'shipping_method.delete', 'web', '2026-08-03 17:12:41', '2026-08-03 17:12:41'),
(92, 'attendance.edit', 'web', '2026-08-12 13:15:41', '2026-08-12 13:15:41'),
(93, 'so.view_own', 'web', '2026-08-13 17:03:25', '2026-08-13 17:03:25'),
(94, 'so.create', 'web', '2026-08-13 17:03:25', '2026-08-13 17:03:25'),
(95, 'so.delete', 'web', '2026-08-13 17:03:25', '2026-08-13 17:03:25');

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE `printers` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection_type` enum('network','windows','linux') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `capability_profile` enum('default','simple','SP2000','TEP-200M','P822D') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `char_per_line` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `type` enum('single','variable','modifier','combo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_id` int UNSIGNED DEFAULT NULL,
  `secondary_unit_id` int DEFAULT NULL,
  `sub_unit_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `brand_id` int UNSIGNED DEFAULT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `sub_category_id` int UNSIGNED DEFAULT NULL,
  `tax` int UNSIGNED DEFAULT NULL,
  `tax_type` enum('inclusive','exclusive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enable_stock` tinyint(1) NOT NULL DEFAULT '0',
  `alert_quantity` decimal(22,4) DEFAULT NULL,
  `sku` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode_type` enum('C39','C128','EAN13','EAN8','UPCA','UPCE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'C128',
  `expiry_period` decimal(4,2) DEFAULT NULL,
  `expiry_period_type` enum('days','months') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_sr_no` tinyint(1) NOT NULL DEFAULT '0',
  `weight` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field6` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field7` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field8` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field9` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field10` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field11` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field12` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field13` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field14` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field15` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field16` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field17` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field18` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field19` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_custom_field20` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED NOT NULL,
  `preparation_time_in_minutes` int DEFAULT NULL,
  `warranty_id` int DEFAULT NULL,
  `is_inactive` tinyint(1) NOT NULL DEFAULT '0',
  `not_for_selling` tinyint(1) NOT NULL DEFAULT '0',
  `sales_channel` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pos_only',
  `ecom_slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ecom_short_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ecom_long_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ecom_seo_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ecom_seo_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ecom_is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `ecom_is_new_arrival` tinyint(1) NOT NULL DEFAULT '0',
  `ecom_online_price` decimal(22,4) DEFAULT NULL,
  `ecom_discount_price` decimal(22,4) DEFAULT NULL,
  `ecom_gallery` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `business_id`, `type`, `unit_id`, `secondary_unit_id`, `sub_unit_ids`, `brand_id`, `category_id`, `sub_category_id`, `tax`, `tax_type`, `enable_stock`, `alert_quantity`, `sku`, `barcode_type`, `expiry_period`, `expiry_period_type`, `enable_sr_no`, `weight`, `product_custom_field1`, `product_custom_field2`, `product_custom_field3`, `product_custom_field4`, `product_custom_field5`, `product_custom_field6`, `product_custom_field7`, `product_custom_field8`, `product_custom_field9`, `product_custom_field10`, `product_custom_field11`, `product_custom_field12`, `product_custom_field13`, `product_custom_field14`, `product_custom_field15`, `product_custom_field16`, `product_custom_field17`, `product_custom_field18`, `product_custom_field19`, `product_custom_field20`, `image`, `product_description`, `created_by`, `preparation_time_in_minutes`, `warranty_id`, `is_inactive`, `not_for_selling`, `sales_channel`, `ecom_slug`, `created_at`, `updated_at`, `ecom_short_description`, `ecom_long_description`, `ecom_seo_title`, `ecom_seo_description`, `ecom_is_featured`, `ecom_is_new_arrival`, `ecom_online_price`, `ecom_discount_price`, `ecom_gallery`) VALUES
(1, 'Lux Cigarettes Carton', 1, 'single', 1, NULL, NULL, NULL, 1, 2, 5, 'inclusive', 1, 10.0000, 'TOB-CIG-LUX', 'C128', NULL, NULL, 0, NULL, '200', '003231', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(2, 'Marlboro Red Carton', 1, 'single', 1, NULL, NULL, NULL, 1, 2, 5, 'inclusive', 1, 10.0000, 'TOB-CIG-MRB', 'C128', NULL, NULL, 0, NULL, '200', '003231', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(3, 'Copenhagen Long Cut', 1, 'single', 1, NULL, NULL, NULL, 1, 3, 5, 'inclusive', 1, 10.0000, 'TOB-MST-COP', 'C128', NULL, NULL, 0, NULL, '5', '003211', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(4, 'Swisher Sweets Cigarillos', 1, 'single', 1, NULL, NULL, NULL, 1, 4, 5, 'inclusive', 1, 10.0000, 'TOB-CGR-SWS', 'C128', NULL, NULL, 0, NULL, '20', '003251', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(5, 'Cheyenne Little Filtered Cigars', 1, 'single', 1, NULL, NULL, NULL, 1, 5, 5, 'inclusive', 1, 10.0000, 'TOB-LFC-CHE', 'C128', NULL, NULL, 0, NULL, '20', '003252', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(6, 'Juul Menthol Pods', 1, 'single', 1, NULL, NULL, NULL, 1, 6, 5, 'inclusive', 1, 10.0000, 'TOB-ECG-JUL', 'C128', NULL, NULL, 0, NULL, '4', '003292', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(7, 'Bugler RYO Tobacco', 1, 'single', 1, NULL, NULL, 1, 1, 7, 5, 'inclusive', 1, 10.0000, 'TOB-RYO-BUG', 'C128', NULL, NULL, 0, NULL, '200', '003221', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ecom_fix_7.jpg', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', NULL, '2026-07-18 19:00:29', '2026-07-23 13:17:16', 'test', NULL, NULL, NULL, 1, 1, NULL, NULL, NULL),
(8, 'Captain Black Pipe Tobacco', 1, 'single', 1, NULL, NULL, NULL, 1, 8, 5, 'inclusive', 1, 10.0000, 'TOB-PIP-CBK', 'C128', NULL, NULL, 0, NULL, '1', '003241', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(39, 'Craft Rolling Tobacco Tin', 1, 'single', 1, NULL, NULL, 2, 1, 2, 5, 'exclusive', 1, NULL, 'ECOM-001', 'C128', NULL, NULL, 0, NULL, '200', '003231', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_1.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'craft-rolling-tobacco-tin-1', '2026-07-22 17:42:40', '2026-07-24 15:59:34', 'Premium Craft Rolling Tobacco Tin selected for everyday quality. Ships fast across the USA.', 'Craft Rolling Tobacco Tin\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Craft Rolling Tobacco Tin | Japs Store', 'Buy Craft Rolling Tobacco Tin online at Japs Store.', 1, 1, 12.0000, NULL, '[\"ecom_demo_1_g.jpg\"]'),
(40, 'Premium Blue Blend Pack', 1, 'single', 1, NULL, NULL, 3, 1, 2, 5, 'exclusive', 1, NULL, 'ECOM-002', 'C128', NULL, NULL, 0, NULL, '200', '003231', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_2.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'premium-blue-blend-pack-2', '2026-07-22 17:42:40', '2026-07-24 15:59:34', 'Premium Premium Blue Blend Pack selected for everyday quality. Ships fast across the USA.', 'Premium Blue Blend Pack\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Premium Blue Blend Pack | Japs Store', 'Buy Premium Blue Blend Pack online at Japs Store.', 1, 0, 14.5000, NULL, '[\"ecom_demo_2_g.jpg\"]'),
(41, 'Classic Rolling Papers', 1, 'single', 1, NULL, NULL, 4, 1, 2, 5, 'exclusive', 1, NULL, 'ECOM-003', 'C128', NULL, NULL, 0, NULL, '200', '003231', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_3.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'classic-rolling-papers-3', '2026-07-22 17:42:43', '2026-07-24 15:59:34', 'Premium Classic Rolling Papers selected for everyday quality. Ships fast across the USA.', 'Classic Rolling Papers\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Classic Rolling Papers | Japs Store', 'Buy Classic Rolling Papers online at Japs Store.', 1, 0, 3.9900, 3.3900, '[\"ecom_demo_3_g.jpg\"]'),
(42, 'Artisan Tortilla Chips', 1, 'single', 1, NULL, NULL, 5, 9, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-004', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_4.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'artisan-tortilla-chips-4', '2026-07-22 17:42:44', '2026-07-23 13:17:16', 'Premium Artisan Tortilla Chips selected for everyday quality. Ships fast across the USA.', 'Artisan Tortilla Chips\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Artisan Tortilla Chips | Japs Store', 'Buy Artisan Tortilla Chips online at Japs Store.', 1, 1, 4.9900, NULL, '[\"ecom_demo_4_g.jpg\"]'),
(43, 'Dark Chocolate Sea Salt', 1, 'single', 1, NULL, NULL, 6, 9, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-005', 'C128', NULL, NULL, 0, NULL, '200\r\n5\r\n10\r\n20\r\n25\r\n40\r\n60\r\n100\r\n250', '003231\r\n003211\r\n003251\r\n003252\r\n003292\r\n003221\r\n003241\r\n003213\r\n003212\r\n003214\r\n003215\r\n003227', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'ecom_demo_5.jpg', '<p>Professional ecommerce demo product</p>', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'dark-chocolate-sea-salt-5', '2026-07-22 17:42:45', '2026-07-27 05:41:44', 'Premium Dark Chocolate Sea Salt selected for everyday quality. Ships fast across the USA.', 'Dark Chocolate Sea Salt\r\n\r\nCrafted for daily use with dependable quality.\r\n\r\n• Live stock from our retail POS\r\n• Secure checkout\r\n• Fast USA delivery\r\n• Easy returns within 14 days', 'Dark Chocolate Sea Salt | Japs Store', 'Buy Dark Chocolate Sea Salt online at Japs Store.', 1, 1, 5.4900, NULL, '[\"ecom_demo_5_g.jpg\"]'),
(44, 'Honey Almond Granola', 1, 'single', 1, NULL, NULL, 7, 9, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-006', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_6.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'honey-almond-granola-6', '2026-07-22 17:42:47', '2026-07-23 13:17:16', 'Premium Honey Almond Granola selected for everyday quality. Ships fast across the USA.', 'Honey Almond Granola\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Honey Almond Granola | Japs Store', 'Buy Honey Almond Granola online at Japs Store.', 1, 0, 6.9900, 5.9400, '[\"ecom_demo_6_g.jpg\"]'),
(45, 'Sea Salt Pretzel Twists', 1, 'single', 1, NULL, NULL, 8, 9, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-007', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_7.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'sea-salt-pretzel-twists-7', '2026-07-22 17:42:50', '2026-07-23 13:17:16', 'Premium Sea Salt Pretzel Twists selected for everyday quality. Ships fast across the USA.', 'Sea Salt Pretzel Twists\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Sea Salt Pretzel Twists | Japs Store', 'Buy Sea Salt Pretzel Twists online at Japs Store.', 1, 0, 3.7900, NULL, '[\"ecom_demo_7_g.jpg\"]'),
(46, 'Cold Brew Coffee 12oz', 1, 'single', 1, NULL, NULL, 9, 10, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-008', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_8.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'cold-brew-coffee-12oz-8', '2026-07-22 17:42:51', '2026-07-23 13:17:16', 'Premium Cold Brew Coffee 12oz selected for everyday quality. Ships fast across the USA.', 'Cold Brew Coffee 12oz\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Cold Brew Coffee 12oz | Japs Store', 'Buy Cold Brew Coffee 12oz online at Japs Store.', 1, 1, 4.5000, NULL, '[\"ecom_demo_8_g.jpg\"]'),
(47, 'Sparkling Citrus Water', 1, 'single', 1, NULL, NULL, 10, 10, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-009', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_9.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'sparkling-citrus-water-9', '2026-07-22 17:42:52', '2026-07-23 13:17:16', 'Premium Sparkling Citrus Water selected for everyday quality. Ships fast across the USA.', 'Sparkling Citrus Water\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Sparkling Citrus Water | Japs Store', 'Buy Sparkling Citrus Water online at Japs Store.', 0, 0, 2.9900, 2.5400, '[\"ecom_demo_9_g.jpg\"]'),
(48, 'Green Tea Matcha Can', 1, 'single', 1, NULL, NULL, 11, 10, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-010', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_10.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'green-tea-matcha-can-10', '2026-07-22 17:42:52', '2026-07-23 13:17:16', 'Premium Green Tea Matcha Can selected for everyday quality. Ships fast across the USA.', 'Green Tea Matcha Can\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Green Tea Matcha Can | Japs Store', 'Buy Green Tea Matcha Can online at Japs Store.', 0, 0, 3.4900, NULL, '[\"ecom_demo_10_g.jpg\"]'),
(49, 'Still Spring Water 1L', 1, 'single', 1, NULL, NULL, 12, 10, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-011', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_11.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'still-spring-water-1l-11', '2026-07-22 17:42:53', '2026-07-23 13:17:16', 'Premium Still Spring Water 1L selected for everyday quality. Ships fast across the USA.', 'Still Spring Water 1L\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Still Spring Water 1L | Japs Store', 'Buy Still Spring Water 1L online at Japs Store.', 0, 1, 1.9900, NULL, '[\"ecom_demo_11_g.jpg\"]'),
(50, 'Mint Fresh Toothpaste', 1, 'single', 1, NULL, NULL, 13, 11, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-012', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_12.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'mint-fresh-toothpaste-12', '2026-07-22 17:42:55', '2026-07-23 13:17:16', 'Premium Mint Fresh Toothpaste selected for everyday quality. Ships fast across the USA.', 'Mint Fresh Toothpaste\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Mint Fresh Toothpaste | Japs Store', 'Buy Mint Fresh Toothpaste online at Japs Store.', 0, 0, 5.9900, 5.0900, '[\"ecom_demo_12_g.jpg\"]'),
(51, 'Aloe Hand Cream', 1, 'single', 1, NULL, NULL, 14, 11, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-013', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_13.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'aloe-hand-cream-13', '2026-07-22 17:42:55', '2026-07-23 13:17:16', 'Premium Aloe Hand Cream selected for everyday quality. Ships fast across the USA.', 'Aloe Hand Cream\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Aloe Hand Cream | Japs Store', 'Buy Aloe Hand Cream online at Japs Store.', 0, 0, 8.9900, NULL, '[\"ecom_demo_13_g.jpg\"]'),
(52, 'Charcoal Face Wash', 1, 'single', 1, NULL, NULL, 15, 11, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-014', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_14.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'charcoal-face-wash-14', '2026-07-22 17:42:56', '2026-07-23 13:17:16', 'Premium Charcoal Face Wash selected for everyday quality. Ships fast across the USA.', 'Charcoal Face Wash\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Charcoal Face Wash | Japs Store', 'Buy Charcoal Face Wash online at Japs Store.', 0, 1, 9.4900, NULL, '[\"ecom_demo_14_g.jpg\"]'),
(53, 'Citrus Body Wash', 1, 'single', 1, NULL, NULL, 1, 11, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-015', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_15.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'citrus-body-wash-15', '2026-07-22 17:42:57', '2026-07-23 13:17:16', 'Premium Citrus Body Wash selected for everyday quality. Ships fast across the USA.', 'Citrus Body Wash\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Citrus Body Wash | Japs Store', 'Buy Citrus Body Wash online at Japs Store.', 0, 0, 7.4900, 6.3700, '[\"ecom_demo_15_g.jpg\"]'),
(54, 'Cotton Kitchen Towels', 1, 'single', 1, NULL, NULL, 2, 12, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-016', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_16.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'cotton-kitchen-towels-16', '2026-07-22 17:42:59', '2026-07-23 13:17:16', 'Premium Cotton Kitchen Towels selected for everyday quality. Ships fast across the USA.', 'Cotton Kitchen Towels\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Cotton Kitchen Towels | Japs Store', 'Buy Cotton Kitchen Towels online at Japs Store.', 0, 0, 11.9900, NULL, '[\"ecom_demo_16_g.jpg\"]'),
(55, 'Eco Dish Soap', 1, 'single', 1, NULL, NULL, 3, 12, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-017', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_17.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'eco-dish-soap-17', '2026-07-22 17:43:00', '2026-07-23 13:17:16', 'Premium Eco Dish Soap selected for everyday quality. Ships fast across the USA.', 'Eco Dish Soap\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Eco Dish Soap | Japs Store', 'Buy Eco Dish Soap online at Japs Store.', 0, 1, 4.2900, NULL, '[\"ecom_demo_17_g.jpg\"]'),
(56, 'Soy Candle Cedarwood', 1, 'single', 1, NULL, NULL, 4, 12, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-018', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_18.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'soy-candle-cedarwood-18', '2026-07-22 17:43:02', '2026-07-23 13:17:16', 'Premium Soy Candle Cedarwood selected for everyday quality. Ships fast across the USA.', 'Soy Candle Cedarwood\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Soy Candle Cedarwood | Japs Store', 'Buy Soy Candle Cedarwood online at Japs Store.', 0, 0, 16.0000, 13.6000, '[\"ecom_demo_18_g.jpg\"]'),
(57, 'Ceramic Mug Set', 1, 'single', 1, NULL, NULL, 5, 12, NULL, NULL, 'exclusive', 1, NULL, 'ECOM-019', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ecom_demo_19.jpg', 'Professional ecommerce demo product', 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'ceramic-mug-set-19', '2026-07-22 17:43:03', '2026-07-23 13:17:16', 'Premium Ceramic Mug Set selected for everyday quality. Ships fast across the USA.', 'Ceramic Mug Set\n\nCrafted for daily use with dependable quality.\n\n• Live stock from our retail POS\n• Secure checkout\n• Fast USA delivery\n• Easy returns within 14 days', 'Ceramic Mug Set | Japs Store', 'Buy Ceramic Mug Set online at Japs Store.', 0, 1, 18.5000, NULL, '[\"ecom_demo_19_g.jpg\"]'),
(58, '7T Extra Strength Pure Extract Tablets | 30mg Per Tab | 10ct Bottle | 6 Count Display | Watermelon', 1, 'single', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'exclusive', 1, NULL, '205751919317', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-08-12 06:18:30', '2026-08-12 06:18:30', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL),
(59, '7T Extra Strength Pure Extract Tablets | 30mg Per Tab | 10ct Bottle | 6 Count Display | Berry', 1, 'single', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'exclusive', 1, NULL, '892678805625', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, 0, 0, 'pos_only', NULL, '2026-08-12 06:56:32', '2026-08-12 06:56:32', NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_locations`
--

CREATE TABLE `product_locations` (
  `product_id` int NOT NULL,
  `location_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_locations`
--

INSERT INTO `product_locations` (`product_id`, `location_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_racks`
--

CREATE TABLE `product_racks` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `rack` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `row` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variations`
--

CREATE TABLE `product_variations` (
  `id` int UNSIGNED NOT NULL,
  `variation_template_id` int DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `is_dummy` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variations`
--

INSERT INTO `product_variations` (`id`, `variation_template_id`, `name`, `product_id`, `is_dummy`, `created_at`, `updated_at`) VALUES
(1, NULL, 'DUMMY', 1, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(2, NULL, 'DUMMY', 2, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(3, NULL, 'DUMMY', 3, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(4, NULL, 'DUMMY', 4, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(5, NULL, 'DUMMY', 5, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(6, NULL, 'DUMMY', 6, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(7, NULL, 'DUMMY', 7, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(8, NULL, 'DUMMY', 8, 1, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(39, NULL, 'DUMMY', 39, 1, '2026-07-22 17:42:40', '2026-07-22 17:42:40'),
(40, NULL, 'DUMMY', 40, 1, '2026-07-22 17:42:40', '2026-07-22 17:42:40'),
(41, NULL, 'DUMMY', 41, 1, '2026-07-22 17:42:43', '2026-07-22 17:42:43'),
(42, NULL, 'DUMMY', 42, 1, '2026-07-22 17:42:44', '2026-07-22 17:42:44'),
(43, NULL, 'DUMMY', 43, 1, '2026-07-22 17:42:45', '2026-07-22 17:42:45'),
(44, NULL, 'DUMMY', 44, 1, '2026-07-22 17:42:47', '2026-07-22 17:42:47'),
(45, NULL, 'DUMMY', 45, 1, '2026-07-22 17:42:50', '2026-07-22 17:42:50'),
(46, NULL, 'DUMMY', 46, 1, '2026-07-22 17:42:51', '2026-07-22 17:42:51'),
(47, NULL, 'DUMMY', 47, 1, '2026-07-22 17:42:52', '2026-07-22 17:42:52'),
(48, NULL, 'DUMMY', 48, 1, '2026-07-22 17:42:52', '2026-07-22 17:42:52'),
(49, NULL, 'DUMMY', 49, 1, '2026-07-22 17:42:53', '2026-07-22 17:42:53'),
(50, NULL, 'DUMMY', 50, 1, '2026-07-22 17:42:55', '2026-07-22 17:42:55'),
(51, NULL, 'DUMMY', 51, 1, '2026-07-22 17:42:55', '2026-07-22 17:42:55'),
(52, NULL, 'DUMMY', 52, 1, '2026-07-22 17:42:56', '2026-07-22 17:42:56'),
(53, NULL, 'DUMMY', 53, 1, '2026-07-22 17:42:57', '2026-07-22 17:42:57'),
(54, NULL, 'DUMMY', 54, 1, '2026-07-22 17:42:59', '2026-07-22 17:42:59'),
(55, NULL, 'DUMMY', 55, 1, '2026-07-22 17:43:00', '2026-07-22 17:43:00'),
(56, NULL, 'DUMMY', 56, 1, '2026-07-22 17:43:02', '2026-07-22 17:43:02'),
(57, NULL, 'DUMMY', 57, 1, '2026-07-22 17:43:03', '2026-07-22 17:43:03'),
(58, NULL, 'DUMMY', 58, 1, '2026-08-12 06:18:31', '2026-08-12 06:18:31'),
(59, NULL, 'DUMMY', 59, 1, '2026-08-12 06:56:32', '2026-08-12 06:56:32');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_lines`
--

CREATE TABLE `purchase_lines` (
  `id` int UNSIGNED NOT NULL,
  `transaction_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `variation_id` int UNSIGNED NOT NULL,
  `quantity` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `secondary_unit_quantity` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `pp_without_discount` decimal(22,4) NOT NULL DEFAULT '0.0000' COMMENT 'Purchase price before inline discounts',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Inline discount percentage',
  `purchase_price` decimal(22,4) NOT NULL,
  `purchase_price_inc_tax` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `item_tax` decimal(22,4) NOT NULL COMMENT 'Tax for one quantity',
  `tax_id` int UNSIGNED DEFAULT NULL,
  `purchase_requisition_line_id` int DEFAULT NULL,
  `purchase_order_line_id` int DEFAULT NULL,
  `quantity_sold` decimal(22,4) NOT NULL DEFAULT '0.0000' COMMENT 'Quanity sold from this purchase line',
  `quantity_adjusted` decimal(22,4) NOT NULL DEFAULT '0.0000' COMMENT 'Quanity adjusted in stock adjustment from this purchase line',
  `quantity_returned` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `po_quantity_purchased` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `mfg_quantity_used` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `mfg_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `lot_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_unit_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_lines`
--

INSERT INTO `purchase_lines` (`id`, `transaction_id`, `product_id`, `variation_id`, `quantity`, `secondary_unit_quantity`, `pp_without_discount`, `discount_percent`, `purchase_price`, `purchase_price_inc_tax`, `item_tax`, `tax_id`, `purchase_requisition_line_id`, `purchase_order_line_id`, `quantity_sold`, `quantity_adjusted`, `quantity_returned`, `po_quantity_purchased`, `mfg_quantity_used`, `mfg_date`, `exp_date`, `lot_number`, `sub_unit_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 100.0000, 0.0000, 20.0000, 0.00, 20.0000, 21.9500, 1.9500, 5, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-27 09:39:08'),
(2, 2, 2, 2, 80.0000, 0.0000, 55.0000, 0.00, 55.0000, 60.3625, 5.3625, 5, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-08-03 05:23:18'),
(3, 3, 3, 3, 60.0000, 0.0000, 28.0000, 0.00, 28.0000, 30.7300, 2.7300, 5, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(4, 4, 4, 4, 50.0000, 0.0000, 18.0000, 0.00, 18.0000, 19.7550, 1.7550, 5, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(5, 5, 5, 5, 50.0000, 0.0000, 14.0000, 0.00, 14.0000, 15.3650, 1.3650, 5, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-27 05:34:00'),
(6, 6, 6, 6, 40.0000, 0.0000, 12.0000, 0.00, 12.0000, 13.1700, 1.1700, 5, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-27 09:42:01'),
(7, 7, 7, 7, 40.0000, 0.0000, 8.0000, 0.00, 8.0000, 8.7800, 0.7800, 5, NULL, NULL, 31.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-08-13 06:41:27'),
(8, 8, 8, 8, 30.0000, 0.0000, 7.0000, 0.00, 7.0000, 7.6825, 0.6825, 5, NULL, NULL, 4.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-27 06:49:15'),
(9, 22, 39, 39, 44.0000, 0.0000, 8.7800, 0.00, 8.7800, 8.7800, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(10, 23, 40, 40, 48.0000, 0.0000, 10.2000, 0.00, 10.2000, 10.2000, 0.0000, NULL, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 08:50:38'),
(11, 24, 41, 41, 52.0000, 0.0000, 1.5000, 0.00, 1.5000, 1.5000, 0.0000, NULL, NULL, NULL, 2.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-12 04:04:23'),
(12, 25, 42, 42, 56.0000, 0.0000, 2.1000, 0.00, 2.1000, 2.1000, 0.0000, NULL, NULL, NULL, 21.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-05 08:03:38'),
(13, 26, 43, 43, 60.0000, 0.0000, 2.4000, 0.00, 2.4000, 2.4000, 0.0000, NULL, NULL, NULL, 60.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 05:44:05'),
(14, 27, 44, 44, 64.0000, 0.0000, 3.2000, 0.00, 3.2000, 3.2000, 0.0000, NULL, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-03 05:23:18'),
(15, 28, 45, 45, 68.0000, 0.0000, 1.6000, 0.00, 1.6000, 1.6000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(16, 29, 46, 46, 72.0000, 0.0000, 1.8000, 0.00, 1.8000, 1.8000, 0.0000, NULL, NULL, NULL, 2.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-06 05:51:04'),
(17, 30, 47, 47, 76.0000, 0.0000, 1.1000, 0.00, 1.1000, 1.1000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(18, 31, 48, 48, 80.0000, 0.0000, 1.4000, 0.00, 1.4000, 1.4000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(19, 32, 49, 49, 84.0000, 0.0000, 0.7000, 0.00, 0.7000, 0.7000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(20, 33, 50, 50, 88.0000, 0.0000, 2.5000, 0.00, 2.5000, 2.5000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(21, 34, 51, 51, 92.0000, 0.0000, 3.8000, 0.00, 3.8000, 3.8000, 0.0000, NULL, NULL, NULL, 16.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-12 07:06:38'),
(22, 35, 52, 52, 96.0000, 0.0000, 4.2000, 0.00, 4.2000, 4.2000, 0.0000, NULL, NULL, NULL, 6.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-12 06:35:38'),
(23, 36, 53, 53, 100.0000, 0.0000, 3.1000, 0.00, 3.1000, 3.1000, 0.0000, NULL, NULL, NULL, 3.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-12 04:04:23'),
(24, 37, 54, 54, 104.0000, 0.0000, 5.0000, 0.00, 5.0000, 5.0000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(25, 38, 55, 55, 108.0000, 0.0000, 1.9000, 0.00, 1.9000, 1.9000, 0.0000, NULL, NULL, NULL, 2.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-08-03 05:23:18'),
(26, 39, 56, 56, 112.0000, 0.0000, 7.5000, 0.00, 7.5000, 7.5000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(27, 40, 57, 57, 116.0000, 0.0000, 8.0000, 0.00, 8.0000, 8.0000, 0.0000, NULL, NULL, NULL, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(28, 41, 7, 7, 1.0000, 0.0000, 8.0000, 0.00, 8.0000, 8.7800, 0.7800, NULL, NULL, NULL, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 06:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `reference_counts`
--

CREATE TABLE `reference_counts` (
  `id` int UNSIGNED NOT NULL,
  `ref_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_count` int NOT NULL,
  `business_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reference_counts`
--

INSERT INTO `reference_counts` (`id`, `ref_type`, `ref_count`, `business_id`, `created_at`, `updated_at`) VALUES
(1, 'contacts', 6, 1, '2026-07-18 18:56:53', '2026-07-23 07:27:00'),
(4, 'sell_payment', 86, 1, '2026-07-24 10:13:56', '2026-08-13 06:41:27'),
(5, 'draft', 9, 1, '2026-07-27 05:47:31', '2026-07-27 10:11:34'),
(6, 'sales_order', 3, 1, '2026-08-13 15:20:58', '2026-08-13 16:00:56');

-- --------------------------------------------------------

--
-- Table structure for table `res_product_modifier_sets`
--

CREATE TABLE `res_product_modifier_sets` (
  `modifier_set_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL COMMENT 'Table use to store the modifier sets applicable for a product'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `res_tables`
--

CREATE TABLE `res_tables` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_service_staff` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `business_id`, `is_default`, `is_service_staff`, `created_at`, `updated_at`) VALUES
(1, 'Admin#1', 'web', 1, 1, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(2, 'Cashier#1', 'web', 1, 0, 0, '2026-07-18 18:56:53', '2026-07-18 18:56:53'),
(3, 'Sales Representative#1', 'web', 1, 0, 0, '2026-08-13 17:03:25', '2026-08-13 17:03:25');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` int UNSIGNED NOT NULL,
  `role_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(25, 2),
(26, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(80, 2),
(85, 2),
(86, 2),
(27, 3),
(36, 3),
(37, 3),
(40, 3),
(48, 3),
(49, 3),
(93, 3),
(94, 3),
(95, 3);

-- --------------------------------------------------------

--
-- Table structure for table `selling_price_groups`
--

CREATE TABLE `selling_price_groups` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `business_id` int UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `selling_price_groups`
--

INSERT INTO `selling_price_groups` (`id`, `name`, `description`, `business_id`, `is_active`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Retail', 'Retail selling prices for tobacco / MSA', 1, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(2, 'Wholesale', 'Wholesale selling prices for tobacco / MSA', 1, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `sell_line_warranties`
--

CREATE TABLE `sell_line_warranties` (
  `sell_line_id` int NOT NULL,
  `warranty_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `business_id`, `name`, `description`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Pickup', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(2, 1, 'Local Delivery', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(3, 1, 'UPS', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(4, 1, 'FedEx', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(5, 1, 'USPS', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(6, 1, 'Freight', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(7, 1, 'Other', NULL, 1, 1, '2026-08-03 17:12:41', '2026-08-03 17:12:41', NULL),
(8, 1, 'DHL', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(9, 1, 'Amazon Logistics', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(10, 1, 'OnTrac', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(11, 1, 'Same Day Delivery', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(12, 1, 'Next Day Air', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(13, 1, 'Ground Shipping', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(14, 1, 'Customer Pickup', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(15, 1, 'Will Call', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL),
(16, 1, 'Courier', NULL, 1, 1, '2026-08-03 17:18:15', '2026-08-03 17:18:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments_temp`
--

CREATE TABLE `stock_adjustments_temp` (
  `id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_lines`
--

CREATE TABLE `stock_adjustment_lines` (
  `id` int UNSIGNED NOT NULL,
  `transaction_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `variation_id` int UNSIGNED NOT NULL,
  `quantity` decimal(22,4) NOT NULL,
  `secondary_unit_quantity` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `unit_price` decimal(22,4) DEFAULT NULL COMMENT 'Last purchase unit price',
  `removed_purchase_line` int DEFAULT NULL,
  `lot_no_line_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system`
--

CREATE TABLE `system` (
  `id` int UNSIGNED NOT NULL,
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system`
--

INSERT INTO `system` (`id`, `key`, `value`) VALUES
(1, 'db_version', '7.0'),
(2, 'default_business_active_status', '1'),
(3, 'ecommerce_version', '1.0.0'),
(4, 'crm_version', '1.0.0'),
(5, 'aiassistance_version', '1.0.0');

-- --------------------------------------------------------

--
-- Table structure for table `tax_rates`
--

CREATE TABLE `tax_rates` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double(22,4) NOT NULL,
  `is_tax_group` tinyint(1) NOT NULL DEFAULT '0',
  `for_tax_group` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tax_rates`
--

INSERT INTO `tax_rates` (`id`, `business_id`, `name`, `amount`, `is_tax_group`, `for_tax_group`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'VAT@10%', 10.0000, 0, 0, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(2, 1, 'CGST@10%', 10.0000, 0, 1, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(3, 1, 'SGST@8%', 8.0000, 0, 1, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(4, 1, 'IGST@18%', 18.0000, 0, 0, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(5, 1, 'Sales Tax@9.75%', 9.7500, 0, 0, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(6, 1, 'Zero Tax@0%', 0.0000, 0, 0, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54'),
(7, 1, 'GST@18%', 18.0000, 1, 0, 1, NULL, '2026-07-18 18:56:54', '2026-07-18 18:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `is_kitchen_order` tinyint(1) NOT NULL DEFAULT '0',
  `res_table_id` int UNSIGNED DEFAULT NULL COMMENT 'fields to restaurant module',
  `res_waiter_id` int UNSIGNED DEFAULT NULL COMMENT 'fields to restaurant module',
  `res_order_status` enum('received','cooked','served') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_quotation` tinyint(1) NOT NULL DEFAULT '0',
  `payment_status` enum('paid','due','partial') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adjustment_type` enum('normal','abnormal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_id` int UNSIGNED DEFAULT NULL,
  `customer_group_id` int DEFAULT NULL COMMENT 'used to add customer group while selling',
  `invoice_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_repeat_on` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `total_before_tax` decimal(22,4) NOT NULL DEFAULT '0.0000' COMMENT 'Total before the purchase/invoice tax, this includeds the indivisual product tax',
  `tax_id` int UNSIGNED DEFAULT NULL,
  `tax_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `discount_type` enum('fixed','percentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` decimal(22,4) DEFAULT '0.0000',
  `rp_redeemed` int NOT NULL DEFAULT '0' COMMENT 'rp is the short form of reward points',
  `rp_redeemed_amount` decimal(22,4) NOT NULL DEFAULT '0.0000' COMMENT 'rp is the short form of reward points',
  `shipping_details` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delivery_date` datetime DEFAULT NULL,
  `shipping_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  `shipping_tracking_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_of_boxes` int UNSIGNED DEFAULT NULL,
  `delivered_to` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_person` bigint DEFAULT NULL,
  `shipping_charges` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `shipping_custom_field_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_custom_field_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_custom_field_3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_custom_field_4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_custom_field_5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `staff_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_export` tinyint(1) NOT NULL DEFAULT '0',
  `export_custom_fields_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `round_off_amount` decimal(22,4) NOT NULL DEFAULT '0.0000' COMMENT 'Difference of rounded total and actual total',
  `additional_expense_key_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_expense_value_1` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `additional_expense_key_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_expense_value_2` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `additional_expense_key_3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_expense_value_3` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `additional_expense_key_4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_expense_value_4` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `final_total` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `expense_category_id` int UNSIGNED DEFAULT NULL,
  `expense_sub_category_id` int DEFAULT NULL,
  `expense_for` int UNSIGNED DEFAULT NULL,
  `commission_agent` int DEFAULT NULL,
  `document` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_direct_sale` tinyint(1) NOT NULL DEFAULT '0',
  `crm_is_order_request` tinyint(1) NOT NULL DEFAULT '0',
  `is_suspend` tinyint(1) NOT NULL DEFAULT '0',
  `exchange_rate` decimal(20,3) NOT NULL DEFAULT '1.000',
  `total_amount_recovered` decimal(22,4) DEFAULT NULL COMMENT 'Used for stock adjustment.',
  `transfer_parent_id` int DEFAULT NULL,
  `return_parent_id` int DEFAULT NULL,
  `opening_stock_product_id` int DEFAULT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `purchase_requisition_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `prefer_payment_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefer_payment_account` int DEFAULT NULL,
  `sales_order_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_order_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `custom_field_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `import_batch` int DEFAULT NULL,
  `import_time` datetime DEFAULT NULL,
  `types_of_service_id` int DEFAULT NULL,
  `packing_charge` decimal(22,4) DEFAULT NULL,
  `packing_charge_type` enum('fixed','percent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_custom_field_1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_custom_field_2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_custom_field_3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_custom_field_4` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_custom_field_5` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_custom_field_6` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_created_from_api` tinyint(1) NOT NULL DEFAULT '0',
  `rp_earned` int NOT NULL DEFAULT '0' COMMENT 'rp is the short form of reward points',
  `order_addresses` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recur_interval` double(22,4) DEFAULT NULL,
  `recur_interval_type` enum('days','months','years') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recur_repetitions` int DEFAULT NULL,
  `recur_stopped_on` datetime DEFAULT NULL,
  `recur_parent_id` int DEFAULT NULL,
  `invoice_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_term_number` int DEFAULT NULL,
  `pay_term_type` enum('days','months') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selling_price_group_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `business_id`, `location_id`, `is_kitchen_order`, `res_table_id`, `res_waiter_id`, `res_order_status`, `type`, `sub_type`, `status`, `sub_status`, `is_quotation`, `payment_status`, `adjustment_type`, `contact_id`, `customer_group_id`, `invoice_no`, `ref_no`, `source`, `subscription_no`, `subscription_repeat_on`, `transaction_date`, `total_before_tax`, `tax_id`, `tax_amount`, `discount_type`, `discount_amount`, `rp_redeemed`, `rp_redeemed_amount`, `shipping_details`, `shipping_method`, `shipping_address`, `delivery_date`, `shipping_status`, `shipping_date`, `shipping_tracking_no`, `no_of_boxes`, `delivered_to`, `delivery_person`, `shipping_charges`, `shipping_custom_field_1`, `shipping_custom_field_2`, `shipping_custom_field_3`, `shipping_custom_field_4`, `shipping_custom_field_5`, `additional_notes`, `staff_note`, `is_export`, `export_custom_fields_info`, `round_off_amount`, `additional_expense_key_1`, `additional_expense_value_1`, `additional_expense_key_2`, `additional_expense_value_2`, `additional_expense_key_3`, `additional_expense_value_3`, `additional_expense_key_4`, `additional_expense_value_4`, `final_total`, `expense_category_id`, `expense_sub_category_id`, `expense_for`, `commission_agent`, `document`, `is_direct_sale`, `crm_is_order_request`, `is_suspend`, `exchange_rate`, `total_amount_recovered`, `transfer_parent_id`, `return_parent_id`, `opening_stock_product_id`, `created_by`, `purchase_requisition_ids`, `prefer_payment_method`, `prefer_payment_account`, `sales_order_ids`, `purchase_order_ids`, `custom_field_1`, `custom_field_2`, `custom_field_3`, `custom_field_4`, `import_batch`, `import_time`, `types_of_service_id`, `packing_charge`, `packing_charge_type`, `service_custom_field_1`, `service_custom_field_2`, `service_custom_field_3`, `service_custom_field_4`, `service_custom_field_5`, `service_custom_field_6`, `is_created_from_api`, `rp_earned`, `order_addresses`, `is_recurring`, `recur_interval`, `recur_interval_type`, `recur_repetitions`, `recur_stopped_on`, `recur_parent_id`, `invoice_token`, `pay_term_number`, `pay_term_type`, `selling_price_group_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 2195.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 2195.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(2, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 4829.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 4829.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(3, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 1843.8000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 1843.8000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 3, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(4, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 987.7500, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 987.7500, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 4, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(5, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 768.2500, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 768.2500, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 5, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(6, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 526.8000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 526.8000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 6, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(7, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 351.2000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 351.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(8, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-19 01:00:29', 230.4750, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 230.4750, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 8, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(9, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'completed', NULL, 0, 'due', NULL, 6, NULL, '0001', '', 'ecommerce', NULL, NULL, '2026-07-22 19:10:15', 12.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, '33', NULL, '33, 333, 33, KS 333, United States', NULL, 'ordered', NULL, NULL, NULL, 'Nash 333', NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, 'Ecommerce order', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-22 13:10:15', '2026-08-13 06:13:49'),
(11, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0003', '', NULL, NULL, NULL, '2026-07-24 22:13:00', 24.0000, 5, 2.3400, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 26.3400, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-24 08:13:33', '2026-07-24 10:28:43'),
(14, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0004', '', NULL, NULL, NULL, '2026-07-24 20:13:00', 11.0000, 5, 1.0725, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0700, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-24 10:13:56', '2026-07-24 10:13:56'),
(16, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0006', '', NULL, NULL, NULL, '2026-07-24 20:27:00', 12.0000, 5, 1.1700, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.1700, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-24 10:28:16', '2026-07-24 10:28:17'),
(19, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0007', '', NULL, NULL, NULL, '2026-07-27 15:33:00', 20.0000, 5, 1.9500, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 21.9500, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 05:34:00', '2026-07-27 05:34:00'),
(22, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 386.3200, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 386.3200, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 39, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(23, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 489.6000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 489.6000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 40, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(24, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 78.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 78.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 41, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(25, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 117.6000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 117.6000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 42, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(26, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 144.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 144.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 43, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(27, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 204.8000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 204.8000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 44, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(28, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 108.8000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 108.8000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 45, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(29, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 129.6000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 129.6000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 46, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(30, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 83.6000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 83.6000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 47, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(31, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 112.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 112.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 48, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(32, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 58.8000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 58.8000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 49, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(33, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 220.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 220.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 50, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(34, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 349.6000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 349.6000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 51, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(35, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 403.2000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 403.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 52, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(36, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 310.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 310.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 53, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(37, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 520.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 520.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 54, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(38, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 205.2000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 205.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 55, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(39, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 840.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 840.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 56, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(40, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 928.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 928.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 57, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(41, 1, 1, 0, NULL, NULL, NULL, 'opening_stock', NULL, 'received', NULL, 0, 'paid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-01 00:00:00', 8.7800, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 8.7800, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, 7, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:11:58', '2026-07-27 15:11:58'),
(42, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0008', '', NULL, NULL, NULL, '2026-07-27 15:43:00', 5.4900, 5, 0.5353, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 6.0300, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 05:43:40', '2026-07-27 05:43:40'),
(43, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0009', '', NULL, NULL, NULL, '2026-07-27 11:43:00', 323.9100, 5, 31.5812, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 355.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 05:44:05', '2026-07-27 05:44:05'),
(46, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0021', '', NULL, NULL, NULL, '2026-07-27 19:46:00', 7.4900, 5, 0.7303, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 8.2200, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 05:47:31', '2026-07-27 08:45:30'),
(48, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0012', '', NULL, NULL, NULL, '2026-07-27 16:18:00', 4.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 4.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:19:24', '2026-07-27 06:19:24'),
(49, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0013', '', NULL, NULL, NULL, '2026-07-27 12:19:00', 11.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 11.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:19:44', '2026-07-27 06:19:44'),
(52, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0014', '', NULL, NULL, NULL, '2026-07-27 12:19:00', 11.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 11.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:20:48', '2026-07-27 06:20:48'),
(55, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0015', '', NULL, NULL, NULL, '2026-07-27 16:26:00', 4.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 4.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:26:44', '2026-07-27 06:26:44'),
(56, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0016', '', NULL, NULL, NULL, '2026-07-27 12:26:00', 16.4600, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 16.4600, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:26:55', '2026-07-27 06:26:55'),
(57, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0017', '', NULL, NULL, NULL, '2026-07-27 16:48:00', 7.6100, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 7.6100, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:49:08', '2026-07-27 06:49:08'),
(58, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0018', '', NULL, NULL, NULL, '2026-07-27 12:49:00', 11.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 11.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 06:49:15', '2026-07-27 06:49:15'),
(59, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0019', '', NULL, NULL, NULL, '2026-07-27 18:41:00', 6.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 6.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 08:41:32', '2026-07-27 08:41:32'),
(60, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0020', '', NULL, NULL, NULL, '2026-07-27 18:44:00', 12.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 08:45:04', '2026-07-27 08:45:04'),
(61, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0022', '', NULL, NULL, NULL, '2026-07-27 22:47:00', 7.4900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 7.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 08:47:28', '2026-07-27 08:47:43'),
(63, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0024', '', NULL, NULL, NULL, '2026-07-27 22:50:00', 14.5000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 14.5000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 08:50:19', '2026-07-27 08:50:38'),
(73, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0031', '', NULL, NULL, NULL, '2026-07-27 23:30:00', 21.4900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, 'ddd', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 21.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 09:30:40', '2026-07-27 09:30:54'),
(74, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0033', '', NULL, NULL, NULL, '2026-07-28 07:32:00', 9.4900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 09:32:10', '2026-07-27 09:35:46'),
(75, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0034', '', NULL, NULL, NULL, '2026-07-27 19:38:00', 12.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 09:38:36', '2026-07-27 09:38:36'),
(76, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0035', '', NULL, NULL, NULL, '2026-07-27 23:38:00', 46.4900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 46.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 09:38:56', '2026-07-27 09:39:08'),
(77, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0037', '', NULL, NULL, NULL, '2026-07-28 03:41:00', 22.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 22.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 09:41:28', '2026-07-27 09:42:01'),
(78, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0038', '', NULL, NULL, NULL, '2026-07-28 00:00:00', 12.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 10:02:14', '2026-07-27 10:02:21'),
(79, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0039', '', NULL, NULL, NULL, '2026-07-27 20:02:00', 12.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 10:02:26', '2026-07-27 10:02:26'),
(80, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0040', '', NULL, NULL, NULL, '2026-07-27 20:02:00', 20.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 20.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 10:03:11', '2026-07-27 10:03:19'),
(83, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0043', '', NULL, NULL, NULL, '2026-07-28 00:10:00', 12.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 10:10:47', '2026-07-27 10:11:24'),
(84, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0044', '', NULL, NULL, NULL, '2026-07-28 00:11:00', 4.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 4.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 10:11:34', '2026-07-27 10:11:42'),
(86, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0046', '', NULL, NULL, NULL, '2026-07-28 00:20:00', 12.0000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 12.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-27 10:20:18', '2026-07-27 10:21:29'),
(87, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0047', '', NULL, NULL, NULL, '2026-07-28 18:11:00', 8.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 8.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-28 08:11:07', '2026-07-28 08:11:08'),
(88, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0048', '', NULL, NULL, NULL, '2026-08-03 15:23:00', 92.0700, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 92.0700, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(89, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0049', '', NULL, NULL, NULL, '2026-08-03 15:36:00', 8.9900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 8.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 05:36:11', '2026-08-03 05:36:11'),
(93, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0050', '', NULL, NULL, NULL, '2026-08-03 16:00:00', 21.4900, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 21.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(95, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0051', '', NULL, NULL, NULL, '2026-08-03 16:01:00', 12.0000, NULL, 0.0000, 'fixed', 2.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 10.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 06:02:12', '2026-08-03 06:02:12'),
(97, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0052', '', NULL, NULL, NULL, '2026-08-03 17:10:00', 18.7900, 1, 1.8790, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 20.6700, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(99, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0054', '', NULL, NULL, NULL, '2026-08-03 17:13:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:14:07', '2026-08-03 07:14:52'),
(100, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0055', '', NULL, NULL, NULL, '2026-08-03 17:21:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:23:58', '2026-08-03 07:23:58'),
(101, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'due', NULL, 2, 1, '0056', '', NULL, NULL, NULL, '2026-08-03 17:27:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:27:56', '2026-08-03 07:27:56'),
(102, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'due', NULL, 2, 1, '0057', '', NULL, NULL, NULL, '2026-08-03 17:31:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:31:31', '2026-08-03 07:31:31');
INSERT INTO `transactions` (`id`, `business_id`, `location_id`, `is_kitchen_order`, `res_table_id`, `res_waiter_id`, `res_order_status`, `type`, `sub_type`, `status`, `sub_status`, `is_quotation`, `payment_status`, `adjustment_type`, `contact_id`, `customer_group_id`, `invoice_no`, `ref_no`, `source`, `subscription_no`, `subscription_repeat_on`, `transaction_date`, `total_before_tax`, `tax_id`, `tax_amount`, `discount_type`, `discount_amount`, `rp_redeemed`, `rp_redeemed_amount`, `shipping_details`, `shipping_method`, `shipping_address`, `delivery_date`, `shipping_status`, `shipping_date`, `shipping_tracking_no`, `no_of_boxes`, `delivered_to`, `delivery_person`, `shipping_charges`, `shipping_custom_field_1`, `shipping_custom_field_2`, `shipping_custom_field_3`, `shipping_custom_field_4`, `shipping_custom_field_5`, `additional_notes`, `staff_note`, `is_export`, `export_custom_fields_info`, `round_off_amount`, `additional_expense_key_1`, `additional_expense_value_1`, `additional_expense_key_2`, `additional_expense_value_2`, `additional_expense_key_3`, `additional_expense_value_3`, `additional_expense_key_4`, `additional_expense_value_4`, `final_total`, `expense_category_id`, `expense_sub_category_id`, `expense_for`, `commission_agent`, `document`, `is_direct_sale`, `crm_is_order_request`, `is_suspend`, `exchange_rate`, `total_amount_recovered`, `transfer_parent_id`, `return_parent_id`, `opening_stock_product_id`, `created_by`, `purchase_requisition_ids`, `prefer_payment_method`, `prefer_payment_account`, `sales_order_ids`, `purchase_order_ids`, `custom_field_1`, `custom_field_2`, `custom_field_3`, `custom_field_4`, `import_batch`, `import_time`, `types_of_service_id`, `packing_charge`, `packing_charge_type`, `service_custom_field_1`, `service_custom_field_2`, `service_custom_field_3`, `service_custom_field_4`, `service_custom_field_5`, `service_custom_field_6`, `is_created_from_api`, `rp_earned`, `order_addresses`, `is_recurring`, `recur_interval`, `recur_interval_type`, `recur_repetitions`, `recur_stopped_on`, `recur_parent_id`, `invoice_token`, `pay_term_number`, `pay_term_type`, `selling_price_group_id`, `created_at`, `updated_at`) VALUES
(103, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0058', '', NULL, NULL, NULL, '2026-08-03 13:31:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:32:08', '2026-08-03 07:32:08'),
(104, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0059', '', NULL, NULL, NULL, '2026-08-03 21:40:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:40:24', '2026-08-03 07:57:05'),
(105, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 3, 1, '0060', '', NULL, NULL, NULL, '2026-08-03 13:40:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 07:42:48', '2026-08-03 07:42:48'),
(106, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0061', '', NULL, NULL, NULL, '2026-08-03 18:34:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 08:34:31', '2026-08-03 08:34:31'),
(107, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0062', '', NULL, NULL, NULL, '2026-08-03 18:41:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, 'dafasf', 'Courier', 'adfasf\r\nsadfa\r\nASdf', NULL, 'ordered', '2026-08-04 14:42:12', '3333', 3, 'dddd', 1, 40.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 45.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 08:42:12', '2026-08-03 08:42:12'),
(108, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0063', '', NULL, NULL, NULL, '2026-08-03 18:44:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 08:45:04', '2026-08-03 08:45:04'),
(109, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0064', '', NULL, NULL, NULL, '2026-08-03 14:45:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, 'dd', 'DHL', 'ddd', NULL, 'ordered', '2026-08-03 14:46:35', 'eee', 3, 'eeee', 1, 50.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 59.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 08:46:35', '2026-08-03 08:46:35'),
(110, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0065', '', NULL, NULL, NULL, '2026-08-03 14:46:00', 9.4900, 1, 0.9490, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 10.4400, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 08:47:42', '2026-08-03 08:47:42'),
(111, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 2, 1, '0066', '', NULL, NULL, NULL, '2026-08-03 19:07:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:07:44', '2026-08-03 09:07:45'),
(112, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'due', NULL, 2, 1, '0067', '', NULL, NULL, NULL, '2026-08-03 19:18:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:19:03', '2026-08-03 09:19:03'),
(113, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0068', '', NULL, NULL, NULL, '2026-08-03 19:20:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:20:11', '2026-08-03 09:20:11'),
(114, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'due', NULL, 5, 2, '0069', '', NULL, NULL, NULL, '2026-08-03 19:27:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:27:47', '2026-08-03 09:27:47'),
(115, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0070', '', NULL, NULL, NULL, '2026-08-03 19:30:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:30:59', '2026-08-04 08:45:04'),
(116, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0071', '', NULL, NULL, NULL, '2026-08-03 15:31:00', 19.6000, 1, 1.9600, 'percentage', 0.0000, 0, 0.0000, 'dd', 'Amazon Logistics', 'ddd', NULL, 'ordered', '2026-08-04 15:33:47', '33333,dddd', 3, 'ddd', 1, 50.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 71.5600, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:33:47', '2026-08-03 09:33:47'),
(117, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0072', '', NULL, NULL, NULL, '2026-08-03 19:38:00', 9.8000, 1, 0.9800, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 20.7800, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:39:11', '2026-08-03 09:39:11'),
(118, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0073', '', NULL, NULL, NULL, '2026-08-03 15:39:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:39:32', '2026-08-03 09:39:32'),
(119, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0074', '', NULL, NULL, NULL, '2026-08-03 15:39:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:40:47', '2026-08-03 09:40:47'),
(120, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0075', '', NULL, NULL, NULL, '2026-08-03 19:43:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 09:43:42', '2026-08-03 09:43:42'),
(121, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 3, 1, '0076', '', NULL, NULL, NULL, '2026-08-03 20:05:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 10:33:09', '2026-08-03 10:33:09'),
(122, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0077', '', NULL, NULL, NULL, '2026-08-03 20:35:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 10:35:05', '2026-08-03 10:35:05'),
(123, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0078', '', NULL, NULL, NULL, '2026-08-03 16:35:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 10:45:50', '2026-08-03 10:45:50'),
(124, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0079', '', NULL, NULL, NULL, '2026-08-03 16:45:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-03 10:47:11', '2026-08-03 10:47:11'),
(125, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0080', '', NULL, NULL, NULL, '2026-08-04 15:11:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(126, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0081', '', NULL, NULL, NULL, '2026-08-04 18:25:00', 13.9800, 1, 1.3980, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 50.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 65.3800, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(127, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0082', '', NULL, NULL, NULL, '2026-08-04 18:45:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-04 08:46:22', '2026-08-04 08:46:22'),
(128, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0083', '', NULL, NULL, NULL, '2026-08-05 14:25:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-05 04:25:36', '2026-08-05 04:25:37'),
(129, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0084', '', NULL, NULL, NULL, '2026-08-05 15:40:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-05 05:40:28', '2026-08-05 05:40:29'),
(130, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0085', '', NULL, NULL, NULL, '2026-08-05 11:40:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-05 05:40:59', '2026-08-05 05:40:59'),
(131, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0086', '', NULL, NULL, NULL, '2026-08-05 16:32:00', 5.4900, 1, 0.5490, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 6.0400, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-05 06:32:36', '2026-08-05 06:32:36'),
(132, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0087', '', NULL, NULL, NULL, '2026-08-05 18:02:00', 4.9900, 1, 0.4990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 5.4900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-05 08:03:09', '2026-08-05 08:03:09'),
(133, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0088', '', NULL, NULL, NULL, '2026-08-05 14:03:00', 13.9800, 1, 1.3980, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 15.3800, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(134, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0089', '', NULL, NULL, NULL, '2026-08-06 15:50:00', 4.2800, 1, 0.4280, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 4.7100, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-06 05:51:04', '2026-08-06 05:51:04'),
(135, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0090', '', NULL, NULL, NULL, '2026-08-07 15:48:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-07 05:48:53', '2026-08-07 05:48:53'),
(136, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'due', NULL, 8, 1, '0091', '', 'sale_app', NULL, NULL, '2026-08-12 10:04:23', 24.4600, NULL, 0.0000, 'fixed', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 24.4600, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(137, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0092', '', NULL, NULL, NULL, '2026-08-12 16:34:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-12 06:35:00', '2026-08-12 06:35:00'),
(138, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0093', '', NULL, NULL, NULL, '2026-08-12 12:35:00', 127.5000, 1, 12.7500, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 140.2500, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-12 06:35:18', '2026-08-12 06:35:18'),
(139, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0094', '', NULL, NULL, NULL, '2026-08-12 12:35:00', 148.9900, 1, 14.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 163.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-12 06:35:37', '2026-08-12 06:35:38'),
(140, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0095', '', NULL, NULL, NULL, '2026-08-12 17:03:00', 8.9900, 1, 0.8990, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 9.8900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-12 07:06:38', '2026-08-12 07:06:38'),
(141, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 1, 1, '0096', '', NULL, NULL, NULL, '2026-08-12 17:13:00', 360.0000, 1, 36.0000, 'percentage', 0.0000, 0, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 396.0000, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-12 07:13:57', '2026-08-12 07:13:57'),
(143, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 6, 1, '0097', '', NULL, NULL, NULL, '2026-08-13 16:13:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, '33', NULL, '33, 333, 33, KS 333, United States', NULL, 'ordered', NULL, NULL, NULL, 'Nash 333', NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, 'Ecommerce order', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 13.2000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '[\"9\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 06:13:49', '2026-08-13 06:13:49'),
(144, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'completed', NULL, 0, NULL, NULL, 4, 1, '2026/0002', '', NULL, NULL, NULL, '2026-08-13 20:27:00', 127.5000, NULL, 0.0000, 'percentage', 0.0000, 0, 0.0000, 'dd', NULL, 'ds', NULL, 'ordered', NULL, NULL, NULL, NULL, 1, 0.0000, NULL, NULL, NULL, NULL, NULL, 'dd', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 127.5000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-13 06:28:33', '2026-08-13 06:29:51'),
(145, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 4, 1, '0098', '', NULL, NULL, NULL, '2026-08-13 16:29:00', 127.5000, 1, 12.7500, 'percentage', 0.0000, 0, 0.0000, 'dd', NULL, 'ds', NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, 'dd', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 140.2500, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '[\"144\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 06:29:51', '2026-08-13 06:29:51'),
(146, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'completed', NULL, 0, 'due', NULL, 8, NULL, '2026/0003', '', 'ecommerce', NULL, NULL, '2026-08-13 22:00:56', 12.0000, NULL, 0.0000, NULL, 0.0000, 0, 0.0000, 'eeee', NULL, 'eeee, 333, HI 33, United States', NULL, 'ordered', NULL, NULL, NULL, 'Nash sadasfas', NULL, 5.9900, NULL, NULL, NULL, NULL, NULL, 'Ecommerce order', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 17.9900, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 16:00:56', '2026-08-13 06:41:27'),
(147, 1, 1, 0, NULL, NULL, NULL, 'sell', NULL, 'final', NULL, 0, 'paid', NULL, 8, 1, '0099', '', NULL, NULL, NULL, '2026-08-13 16:41:00', 12.0000, 1, 1.2000, 'percentage', 0.0000, 0, 0.0000, 'eeee', NULL, 'eeee, 333, HI 33, United States', NULL, 'ordered', NULL, NULL, NULL, 'Nash sadasfas', NULL, 5.9900, NULL, NULL, NULL, NULL, NULL, 'Ecommerce order', NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 19.1900, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '[\"146\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, 'days', 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 06:41:27', '2026-08-13 06:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_payments`
--

CREATE TABLE `transaction_payments` (
  `id` int UNSIGNED NOT NULL,
  `transaction_id` int UNSIGNED DEFAULT NULL,
  `business_id` int DEFAULT NULL,
  `is_return` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Used during sales to return the change',
  `amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_transaction_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_holder_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_month` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_year` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_security` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cheque_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_on` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `paid_through_link` tinyint(1) NOT NULL DEFAULT '0',
  `gateway` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_advance` tinyint(1) NOT NULL DEFAULT '0',
  `payment_for` int DEFAULT NULL COMMENT 'stores the contact id',
  `parent_id` int DEFAULT NULL,
  `note` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_ref_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_payments`
--

INSERT INTO `transaction_payments` (`id`, `transaction_id`, `business_id`, `is_return`, `amount`, `method`, `payment_type`, `transaction_no`, `card_transaction_number`, `card_number`, `card_type`, `card_holder_name`, `card_month`, `card_year`, `card_security`, `cheque_number`, `bank_account_number`, `paid_on`, `created_by`, `paid_through_link`, `gateway`, `is_advance`, `payment_for`, `parent_id`, `note`, `document`, `payment_ref_no`, `account_id`, `created_at`, `updated_at`) VALUES
(3, 14, 1, 0, 12.0700, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-24 16:13:56', 1, 0, NULL, 0, 2, NULL, NULL, NULL, 'SP2026/0001', NULL, '2026-07-24 10:13:56', '2026-07-24 10:13:56'),
(4, 16, 1, 0, 3.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-24 16:28:16', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0002', NULL, '2026-07-24 10:28:16', '2026-07-24 10:28:16'),
(5, 16, 1, 0, 10.1700, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-24 16:28:16', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0003', NULL, '2026-07-24 10:28:17', '2026-07-24 10:28:17'),
(6, NULL, 1, 0, 26.3400, 'cash', 'credit', NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-24 20:28:00', 1, 0, NULL, 1, 2, NULL, NULL, NULL, 'SP2026/0004', NULL, '2026-07-24 10:28:43', '2026-07-24 10:28:43'),
(7, 11, 1, 0, 26.3400, 'cash', NULL, 'cash', NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-24 20:28:00', 1, 0, NULL, 0, 2, 6, NULL, NULL, 'SP2026/0005', NULL, '2026-07-24 10:28:43', '2026-07-24 10:28:43'),
(10, 19, 1, 0, 21.9500, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 11:34:00', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0006', NULL, '2026-07-27 05:34:00', '2026-07-27 05:34:00'),
(13, 42, 1, 0, 6.0300, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 11:43:40', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0007', NULL, '2026-07-27 05:43:40', '2026-07-27 05:43:40'),
(14, 43, 1, 0, 355.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 11:44:05', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0008', NULL, '2026-07-27 05:44:05', '2026-07-27 05:44:05'),
(15, 48, 1, 0, 4.9900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:19:24', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0009', NULL, '2026-07-27 06:19:24', '2026-07-27 06:19:24'),
(16, 49, 1, 0, 11.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:19:44', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0010', NULL, '2026-07-27 06:19:44', '2026-07-27 06:19:44'),
(17, 52, 1, 0, 11.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:20:48', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0011', NULL, '2026-07-27 06:20:48', '2026-07-27 06:20:48'),
(18, 55, 1, 0, 4.9900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:26:44', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0012', NULL, '2026-07-27 06:26:44', '2026-07-27 06:26:44'),
(19, 56, 1, 0, 16.4600, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:26:55', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0013', NULL, '2026-07-27 06:26:55', '2026-07-27 06:26:55'),
(20, 57, 1, 0, 7.6100, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:49:08', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0014', NULL, '2026-07-27 06:49:08', '2026-07-27 06:49:08'),
(21, 58, 1, 0, 11.0000, 'card', NULL, NULL, NULL, NULL, 'visa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 12:49:15', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0015', NULL, '2026-07-27 06:49:15', '2026-07-27 06:49:15'),
(22, 59, 1, 0, 6.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 14:41:32', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0016', NULL, '2026-07-27 08:41:32', '2026-07-27 08:41:32'),
(23, 60, 1, 0, 12.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 14:45:04', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0017', NULL, '2026-07-27 08:45:04', '2026-07-27 08:45:04'),
(24, 46, 1, 0, 8.2200, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 14:45:30', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0018', NULL, '2026-07-27 08:45:30', '2026-07-27 08:45:30'),
(25, 61, 1, 0, 7.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 14:47:43', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0019', NULL, '2026-07-27 08:47:43', '2026-07-27 08:47:43'),
(26, 63, 1, 0, 14.5000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 14:50:38', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0020', NULL, '2026-07-27 08:50:38', '2026-07-27 08:50:38'),
(27, 73, 1, 0, 21.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:30:54', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0021', NULL, '2026-07-27 09:30:54', '2026-07-27 09:30:54'),
(28, 74, 1, 0, 9.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:35:46', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0022', NULL, '2026-07-27 09:35:46', '2026-07-27 09:35:46'),
(29, 75, 1, 0, 12.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:38:36', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0023', NULL, '2026-07-27 09:38:36', '2026-07-27 09:38:36'),
(30, 76, 1, 0, 46.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:39:08', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0024', NULL, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(31, 77, 1, 0, 22.9900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 15:42:01', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0025', NULL, '2026-07-27 09:42:01', '2026-07-27 09:42:01'),
(32, 78, 1, 0, 12.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 16:02:21', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0026', NULL, '2026-07-27 10:02:21', '2026-07-27 10:02:21'),
(33, 79, 1, 0, 12.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 16:02:26', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0027', NULL, '2026-07-27 10:02:26', '2026-07-27 10:02:26'),
(34, 80, 1, 0, 20.9900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 16:03:19', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0028', NULL, '2026-07-27 10:03:19', '2026-07-27 10:03:19'),
(35, 83, 1, 0, 12.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 16:11:23', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0029', NULL, '2026-07-27 10:11:23', '2026-07-27 10:11:23'),
(36, 84, 1, 0, 4.9900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 16:11:42', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0030', NULL, '2026-07-27 10:11:42', '2026-07-27 10:11:42'),
(37, 86, 1, 0, 12.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-27 16:21:29', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0031', NULL, '2026-07-27 10:21:29', '2026-07-27 10:21:29'),
(38, 87, 1, 0, 8.9900, 'card', NULL, NULL, NULL, NULL, 'visa', NULL, NULL, NULL, NULL, NULL, NULL, '2026-07-28 14:11:07', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0032', NULL, '2026-07-28 08:11:07', '2026-07-28 08:11:07'),
(39, 88, 1, 0, 92.0700, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 11:23:18', 1, 0, NULL, 0, 2, NULL, NULL, NULL, 'SP2026/0033', NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(40, 89, 1, 0, 8.9900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 11:36:11', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0034', NULL, '2026-08-03 05:36:11', '2026-08-03 05:36:11'),
(41, 93, 1, 0, 21.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 12:00:19', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0035', NULL, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(42, 95, 1, 0, 10.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 12:02:12', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0036', NULL, '2026-08-03 06:02:12', '2026-08-03 06:02:12'),
(43, 97, 1, 0, 20.6700, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 13:10:27', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0037', NULL, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(44, NULL, 1, 0, 13.2000, 'cash', 'credit', NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 17:14:00', 1, 0, NULL, 1, 2, NULL, NULL, NULL, 'SP2026/0038', NULL, '2026-08-03 07:14:52', '2026-08-03 07:14:52'),
(45, 99, 1, 0, 13.2000, 'cash', NULL, 'cash', NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 17:14:00', 1, 0, NULL, 0, 2, 44, NULL, NULL, 'SP2026/0039', NULL, '2026-08-03 07:14:52', '2026-08-03 07:14:52'),
(46, 100, 1, 0, 13.2000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 13:23:58', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0040', NULL, '2026-08-03 07:23:58', '2026-08-03 07:23:58'),
(47, 103, 1, 0, 5.4900, 'card', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 13:32:08', 1, 0, NULL, 0, 2, NULL, NULL, NULL, 'SP2026/0041', NULL, '2026-08-03 07:32:08', '2026-08-03 07:32:08'),
(49, 105, 1, 0, 9.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 13:42:48', 1, 0, NULL, 0, 3, NULL, NULL, NULL, 'SP2026/0043', NULL, '2026-08-03 07:42:48', '2026-08-03 07:42:48'),
(50, 104, 1, 0, 4.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 13:57:05', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0044', NULL, '2026-08-03 07:57:05', '2026-08-03 07:57:05'),
(51, 104, 1, 0, 5.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 13:57:05', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0045', NULL, '2026-08-03 07:57:05', '2026-08-03 07:57:05'),
(52, 106, 1, 0, 9.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:34:31', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0046', NULL, '2026-08-03 08:34:31', '2026-08-03 08:34:31'),
(53, 107, 1, 0, 45.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:42:12', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0047', NULL, '2026-08-03 08:42:12', '2026-08-03 08:42:12'),
(54, 108, 1, 0, 9.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:45:04', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0048', NULL, '2026-08-03 08:45:04', '2026-08-03 08:45:04'),
(55, 109, 1, 0, 59.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:46:35', 1, 0, NULL, 0, 2, NULL, NULL, NULL, 'SP2026/0049', NULL, '2026-08-03 08:46:35', '2026-08-03 08:46:35'),
(56, 110, 1, 0, 10.4400, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 14:47:42', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0050', NULL, '2026-08-03 08:47:42', '2026-08-03 08:47:42'),
(57, 111, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:07:44', 1, 0, NULL, 0, 2, NULL, NULL, NULL, 'SP2026/0051', NULL, '2026-08-03 09:07:44', '2026-08-03 09:07:44'),
(58, 113, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:20:11', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0052', NULL, '2026-08-03 09:20:11', '2026-08-03 09:20:11'),
(59, 116, 1, 0, 71.5600, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:33:47', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0053', NULL, '2026-08-03 09:33:47', '2026-08-03 09:33:47'),
(60, 117, 1, 0, 20.7800, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:39:11', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0054', NULL, '2026-08-03 09:39:11', '2026-08-03 09:39:11'),
(61, 118, 1, 0, 13.2000, 'card', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:39:32', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0055', NULL, '2026-08-03 09:39:32', '2026-08-03 09:39:32'),
(62, 119, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:40:47', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0056', NULL, '2026-08-03 09:40:47', '2026-08-03 09:40:47'),
(63, 120, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 15:43:42', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0057', NULL, '2026-08-03 09:43:42', '2026-08-03 09:43:42'),
(64, 121, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 16:33:09', 1, 0, NULL, 0, 3, NULL, NULL, NULL, 'SP2026/0058', NULL, '2026-08-03 10:33:09', '2026-08-03 10:33:09'),
(65, 122, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 16:35:05', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0059', NULL, '2026-08-03 10:35:05', '2026-08-03 10:35:05'),
(66, 123, 1, 0, 13.2000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 16:45:50', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0060', NULL, '2026-08-03 10:45:50', '2026-08-03 10:45:50'),
(67, 124, 1, 0, 13.2000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 16:47:11', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0061', NULL, '2026-08-03 10:47:11', '2026-08-03 10:47:11'),
(68, 125, 1, 0, 50.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 11:19:16', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0062', NULL, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(69, 125, 1, 1, 36.8000, 'card', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 11:19:16', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0063', NULL, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(70, 126, 1, 0, 5.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 14:28:24', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0064', NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(71, 126, 1, 0, 30.0000, 'card', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 14:28:24', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0065', NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(72, 126, 1, 0, 30.3800, 'cheque', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 14:28:24', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0066', NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(73, NULL, 1, 0, 13.2000, 'cash', 'credit', NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 18:45:00', 1, 0, NULL, 1, 1, NULL, NULL, NULL, 'SP2026/0067', NULL, '2026-08-04 08:45:04', '2026-08-04 08:45:04'),
(74, 115, 1, 0, 13.2000, 'cash', NULL, 'cash', NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 18:45:00', 1, 0, NULL, 0, 1, 73, NULL, NULL, 'SP2026/0068', NULL, '2026-08-04 08:45:04', '2026-08-04 08:45:04'),
(75, 127, 1, 0, 13.2000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-04 14:46:22', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0069', NULL, '2026-08-04 08:46:22', '2026-08-04 08:46:22'),
(76, 128, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-05 10:25:37', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0070', NULL, '2026-08-05 04:25:37', '2026-08-05 04:25:37'),
(77, 129, 1, 0, 13.2000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-05 11:40:28', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0071', NULL, '2026-08-05 05:40:28', '2026-08-05 05:40:28'),
(78, 130, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-05 11:40:59', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0072', NULL, '2026-08-05 05:40:59', '2026-08-05 05:40:59'),
(79, 131, 1, 0, 6.0400, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-05 12:32:36', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0073', NULL, '2026-08-05 06:32:36', '2026-08-05 06:32:36'),
(80, 132, 1, 0, 5.4900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-05 14:03:09', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0074', NULL, '2026-08-05 08:03:09', '2026-08-05 08:03:09'),
(81, 133, 1, 0, 15.3800, 'cheque', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-05 14:03:38', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0075', NULL, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(82, 134, 1, 0, 4.7100, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-06 11:51:04', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0076', NULL, '2026-08-06 05:51:04', '2026-08-06 05:51:04'),
(83, 135, 1, 0, 9.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-07 11:48:53', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0077', NULL, '2026-08-07 05:48:53', '2026-08-07 05:48:53'),
(84, 137, 1, 0, 9.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 12:35:00', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0078', NULL, '2026-08-12 06:35:00', '2026-08-12 06:35:00'),
(85, 138, 1, 0, 140.2500, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 12:35:18', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0079', NULL, '2026-08-12 06:35:18', '2026-08-12 06:35:18'),
(86, 139, 1, 0, 163.8900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 12:35:37', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0080', NULL, '2026-08-12 06:35:37', '2026-08-12 06:35:37'),
(87, 140, 1, 0, 9.8900, 'card', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 13:06:38', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0081', NULL, '2026-08-12 07:06:38', '2026-08-12 07:06:38'),
(88, 141, 1, 0, 396.0000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-12 13:13:57', 1, 0, NULL, 0, 1, NULL, NULL, NULL, 'SP2026/0082', NULL, '2026-08-12 07:13:57', '2026-08-12 07:13:57'),
(89, 143, 1, 0, 13.2000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 16:13:00', 1, 0, NULL, 0, 6, NULL, NULL, NULL, 'SP2026/0083', NULL, '2026-08-13 06:13:49', '2026-08-13 06:13:49'),
(90, 144, 1, 0, 127.5000, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 16:28:00', 1, 0, NULL, 0, 4, NULL, NULL, NULL, 'SP2026/0084', NULL, '2026-08-13 06:29:35', '2026-08-13 06:29:35'),
(91, 145, 1, 0, 140.2500, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 16:29:00', 1, 0, NULL, 0, 4, NULL, NULL, NULL, 'SP2026/0085', NULL, '2026-08-13 06:29:51', '2026-08-13 06:29:51'),
(92, 147, 1, 0, 19.1900, 'cash', NULL, NULL, NULL, NULL, 'credit', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-13 16:41:00', 1, 0, NULL, 0, 8, NULL, NULL, NULL, 'SP2026/0086', NULL, '2026-08-13 06:41:27', '2026-08-13 06:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_sell_lines`
--

CREATE TABLE `transaction_sell_lines` (
  `id` int UNSIGNED NOT NULL,
  `transaction_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `variation_id` int UNSIGNED NOT NULL,
  `quantity` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `secondary_unit_quantity` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `quantity_returned` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `unit_price_before_discount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `unit_price` decimal(22,4) DEFAULT NULL COMMENT 'Sell price excluding tax',
  `line_discount_type` enum('fixed','percentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_discount_amount` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `unit_price_inc_tax` decimal(22,4) DEFAULT NULL COMMENT 'Sell price including tax',
  `item_tax` decimal(22,4) NOT NULL COMMENT 'Tax for one quantity',
  `tax_id` int UNSIGNED DEFAULT NULL,
  `discount_id` int DEFAULT NULL,
  `lot_no_line_id` int DEFAULT NULL,
  `sell_line_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `so_line_id` int DEFAULT NULL,
  `so_quantity_invoiced` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `res_service_staff_id` int DEFAULT NULL,
  `res_line_order_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_sell_line_id` int DEFAULT NULL,
  `children_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Type of children for the parent, like modifier or combo',
  `sub_unit_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_sell_lines`
--

INSERT INTO `transaction_sell_lines` (`id`, `transaction_id`, `product_id`, `variation_id`, `quantity`, `secondary_unit_quantity`, `quantity_returned`, `unit_price_before_discount`, `unit_price`, `line_discount_type`, `line_discount_amount`, `unit_price_inc_tax`, `item_tax`, `tax_id`, `discount_id`, `lot_no_line_id`, `sell_line_note`, `so_line_id`, `so_quantity_invoiced`, `res_service_staff_id`, `res_line_order_status`, `parent_sell_line_id`, `children_type`, `sub_unit_id`, `created_at`, `updated_at`) VALUES
(1, 9, 7, 7, 1.0000, 0.0000, 0.0000, 12.0000, 12.0000, NULL, 0.0000, 12.0000, 0.0000, NULL, NULL, NULL, '', NULL, 1.0000, NULL, NULL, NULL, '', NULL, '2026-07-22 13:10:15', '2026-08-13 06:13:49'),
(3, 11, 7, 7, 2.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-24 08:13:33', '2026-07-24 10:15:39'),
(6, 14, 8, 8, 1.0000, 0.0000, 0.0000, 10.0200, 10.0200, 'fixed', 0.0000, 11.0000, 0.9800, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-24 10:13:56', '2026-07-24 10:13:56'),
(8, 16, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-24 10:28:16', '2026-07-24 10:28:16'),
(11, 19, 5, 5, 1.0000, 0.0000, 0.0000, 18.2200, 18.2200, 'fixed', 0.0000, 20.0000, 1.7800, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 05:34:00', '2026-07-27 05:34:00'),
(14, 42, 43, 43, 1.0000, 0.0000, 0.0000, 5.4900, 5.4900, 'fixed', 0.0000, 5.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 05:43:40', '2026-07-27 05:43:40'),
(15, 43, 43, 43, 59.0000, 0.0000, 0.0000, 5.4900, 5.4900, 'fixed', 0.0000, 5.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 05:44:05', '2026-07-27 05:44:05'),
(19, 48, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:19:24', '2026-07-27 06:19:24'),
(20, 49, 8, 8, 1.0000, 0.0000, 0.0000, 10.0200, 10.0200, 'fixed', 0.0000, 11.0000, 0.9800, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:19:44', '2026-07-27 06:19:44'),
(21, 52, 8, 8, 1.0000, 0.0000, 0.0000, 10.0200, 10.0200, 'fixed', 0.0000, 11.0000, 0.9800, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:20:48', '2026-07-27 06:20:48'),
(22, 55, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:26:44', '2026-07-27 06:26:44'),
(23, 56, 7, 7, 1.0000, 0.0000, 0.0000, 15.0000, 15.0000, 'fixed', 0.0000, 16.4600, 1.4600, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:26:55', '2026-07-27 06:26:55'),
(24, 57, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 6.9300, 'fixed', 4.0000, 7.6100, 0.6800, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:49:08', '2026-07-27 06:49:08'),
(25, 58, 8, 8, 1.0000, 0.0000, 0.0000, 10.0200, 10.0200, 'fixed', 0.0000, 11.0000, 0.9800, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 06:49:15', '2026-07-27 06:49:15'),
(26, 59, 42, 42, 1.0000, 0.0000, 0.0000, 6.0000, 6.0000, 'fixed', 0.0000, 6.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 08:41:32', '2026-07-27 08:41:32'),
(27, 60, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 08:45:04', '2026-07-27 08:45:04'),
(28, 46, 53, 53, 1.0000, 0.0000, 0.0000, 7.4900, 7.4900, 'fixed', 0.0000, 7.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 08:45:30', '2026-07-27 08:45:30'),
(30, 61, 53, 53, 1.0000, 0.0000, 0.0000, 7.4900, 7.4900, 'fixed', 0.0000, 7.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 08:47:43', '2026-07-27 08:47:43'),
(33, 63, 40, 40, 1.0000, 0.0000, 0.0000, 13.2100, 13.2100, 'fixed', 0.0000, 14.5000, 1.2900, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 08:50:38', '2026-07-27 08:50:38'),
(42, 73, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:30:40', '2026-07-27 09:30:54'),
(43, 73, 52, 52, 1.0000, 0.0000, 0.0000, 9.4900, 9.4900, 'fixed', 0.0000, 9.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:30:54', '2026-07-27 09:30:54'),
(44, 74, 52, 52, 1.0000, 0.0000, 0.0000, 9.4900, 9.4900, 'fixed', 0.0000, 9.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:32:10', '2026-07-27 09:35:46'),
(45, 75, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:38:36', '2026-07-27 09:38:36'),
(46, 76, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:38:56', '2026-07-27 09:39:08'),
(47, 76, 52, 52, 1.0000, 0.0000, 0.0000, 9.4900, 9.4900, 'fixed', 0.0000, 9.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:38:56', '2026-07-27 09:39:08'),
(48, 76, 1, 1, 1.0000, 0.0000, 0.0000, 22.7800, 22.7800, 'fixed', 0.0000, 25.0000, 2.2200, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(49, 77, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:41:28', '2026-07-27 09:42:01'),
(50, 77, 6, 6, 1.0000, 0.0000, 0.0000, 16.4000, 16.4000, 'fixed', 0.0000, 18.0000, 1.6000, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 09:42:01', '2026-07-27 09:42:01'),
(51, 78, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:02:14', '2026-07-27 10:02:21'),
(52, 79, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:02:26', '2026-07-27 10:02:26'),
(53, 80, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:03:11', '2026-07-27 10:03:19'),
(54, 80, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:03:19', '2026-07-27 10:03:19'),
(57, 83, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:10:47', '2026-07-27 10:11:23'),
(58, 84, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:11:34', '2026-07-27 10:11:42'),
(60, 86, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-27 10:20:18', '2026-07-27 10:21:29'),
(61, 87, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-07-28 08:11:07', '2026-07-28 08:11:07'),
(62, 88, 44, 44, 1.0000, 0.0000, 0.0000, 6.9900, 6.9900, 'fixed', 0.0000, 6.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(63, 88, 2, 2, 1.0000, 0.0000, 0.0000, 65.6000, 65.6000, 'fixed', 0.0000, 72.0000, 6.4000, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(64, 88, 55, 55, 2.0000, 0.0000, 0.0000, 4.2900, 4.2900, 'fixed', 0.0000, 4.2900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(65, 88, 46, 46, 1.0000, 0.0000, 0.0000, 4.5000, 4.5000, 'fixed', 0.0000, 4.5000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(66, 89, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 05:36:11', '2026-08-03 05:36:11'),
(67, 93, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(68, 93, 52, 52, 1.0000, 0.0000, 0.0000, 9.4900, 9.4900, 'fixed', 0.0000, 9.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(69, 95, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 06:02:12', '2026-08-03 06:02:12'),
(70, 97, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(71, 97, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 8.9300, 'fixed', 2.0000, 9.8000, 0.8700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(74, 99, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:14:07', '2026-08-03 07:14:07'),
(75, 100, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:23:58', '2026-08-03 07:23:58'),
(76, 101, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:27:56', '2026-08-03 07:27:56'),
(77, 102, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:31:31', '2026-08-03 07:31:31'),
(78, 103, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:32:08', '2026-08-03 07:32:08'),
(79, 104, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:40:24', '2026-08-03 07:57:05'),
(80, 105, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 07:42:48', '2026-08-03 07:42:48'),
(81, 106, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 08:34:31', '2026-08-03 08:34:31'),
(82, 107, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 08:42:12', '2026-08-03 08:42:12'),
(83, 108, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 08:45:04', '2026-08-03 08:45:04'),
(84, 109, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 08:46:35', '2026-08-03 08:46:35'),
(85, 110, 52, 52, 1.0000, 0.0000, 0.0000, 9.4900, 9.4900, 'fixed', 0.0000, 9.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 08:47:42', '2026-08-03 08:47:42'),
(86, 111, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:07:44', '2026-08-03 09:07:44'),
(87, 112, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:19:03', '2026-08-03 09:19:03'),
(88, 113, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:20:11', '2026-08-03 09:20:11'),
(89, 114, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:27:47', '2026-08-03 09:27:47'),
(90, 115, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:30:59', '2026-08-03 09:30:59'),
(91, 116, 7, 7, 2.0000, 0.0000, 0.0000, 10.9300, 8.9300, 'fixed', 2.0000, 9.8000, 0.8700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:33:47', '2026-08-03 09:33:47'),
(92, 117, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 8.9300, 'fixed', 2.0000, 9.8000, 0.8700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:39:11', '2026-08-03 09:39:11'),
(93, 118, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:39:32', '2026-08-03 09:39:32'),
(94, 119, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:40:47', '2026-08-03 09:40:47'),
(95, 120, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 09:43:42', '2026-08-03 09:43:42'),
(96, 121, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 10:33:09', '2026-08-03 10:33:09'),
(97, 122, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 10:35:05', '2026-08-03 10:35:05'),
(98, 123, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 10:45:50', '2026-08-03 10:45:50'),
(99, 124, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-03 10:47:11', '2026-08-03 10:47:11'),
(100, 125, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(101, 126, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(102, 126, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(103, 127, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-04 08:46:22', '2026-08-04 08:46:22'),
(104, 128, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 04:25:37', '2026-08-05 04:25:37'),
(105, 129, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 05:40:28', '2026-08-05 05:40:28'),
(106, 130, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 05:40:59', '2026-08-05 05:40:59'),
(107, 131, 43, 43, 1.0000, 0.0000, 0.0000, 5.4900, 5.4900, 'fixed', 0.0000, 5.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 06:32:36', '2026-08-05 06:32:36'),
(108, 132, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 08:03:09', '2026-08-05 08:03:09'),
(109, 133, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(110, 133, 42, 42, 1.0000, 0.0000, 0.0000, 4.9900, 4.9900, 'fixed', 0.0000, 4.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(111, 134, 46, 46, 1.0000, 0.0000, 0.0000, 4.5000, 4.2750, 'percentage', 5.0000, 4.2800, 0.0000, NULL, 2, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-06 05:51:04', '2026-08-06 05:51:04'),
(112, 135, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-07 05:48:53', '2026-08-07 05:48:53'),
(113, 136, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(114, 136, 41, 41, 2.0000, 0.0000, 0.0000, 3.9900, 3.9900, 'fixed', 0.0000, 3.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(115, 136, 53, 53, 1.0000, 0.0000, 0.0000, 7.4900, 7.4900, 'fixed', 0.0000, 7.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(116, 137, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 06:35:00', '2026-08-12 06:35:00'),
(117, 138, 58, 58, 1.0000, 0.0000, 0.0000, 127.5000, 127.5000, 'fixed', 0.0000, 127.5000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 06:35:18', '2026-08-12 06:35:18'),
(118, 139, 52, 52, 1.0000, 0.0000, 0.0000, 9.4900, 9.4900, 'fixed', 0.0000, 9.4900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 06:35:37', '2026-08-12 06:35:37'),
(119, 139, 7, 7, 1.0000, 0.0000, 0.0000, 10.9300, 10.9300, 'fixed', 0.0000, 12.0000, 1.0700, 5, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 06:35:37', '2026-08-12 06:35:37'),
(120, 139, 58, 58, 1.0000, 0.0000, 0.0000, 127.5000, 127.5000, 'fixed', 0.0000, 127.5000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 06:35:37', '2026-08-12 06:35:37'),
(121, 140, 51, 51, 1.0000, 0.0000, 0.0000, 8.9900, 8.9900, 'fixed', 0.0000, 8.9900, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 07:06:38', '2026-08-12 07:06:38'),
(122, 141, 58, 58, 2.0000, 0.0000, 0.0000, 200.0000, 180.0000, 'fixed', 20.0000, 180.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-12 07:13:57', '2026-08-12 07:13:57'),
(124, 143, 7, 7, 1.0000, 0.0000, 0.0000, 12.0000, 12.0000, 'fixed', 0.0000, 12.0000, 0.0000, NULL, NULL, NULL, '', 1, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-13 06:13:49', '2026-08-13 06:13:49'),
(125, 144, 59, 59, 1.0000, 0.0000, 0.0000, 127.5000, 127.5000, 'fixed', 0.0000, 127.5000, 0.0000, NULL, NULL, NULL, '', NULL, 1.0000, NULL, NULL, NULL, '', NULL, '2026-08-13 06:28:33', '2026-08-13 06:29:51'),
(126, 145, 59, 59, 1.0000, 0.0000, 0.0000, 127.5000, 127.5000, 'fixed', 0.0000, 127.5000, 0.0000, NULL, NULL, NULL, '', 125, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-13 06:29:51', '2026-08-13 06:29:51'),
(127, 146, 7, 7, 1.0000, 0.0000, 0.0000, 12.0000, 12.0000, NULL, 0.0000, 12.0000, 0.0000, NULL, NULL, NULL, '', NULL, 1.0000, NULL, NULL, NULL, '', NULL, '2026-08-13 16:00:56', '2026-08-13 06:41:27'),
(128, 147, 7, 7, 1.0000, 0.0000, 0.0000, 12.0000, 12.0000, 'fixed', 0.0000, 12.0000, 0.0000, NULL, NULL, NULL, '', 127, 0.0000, NULL, NULL, NULL, '', NULL, '2026-08-13 06:41:27', '2026-08-13 06:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_sell_lines_purchase_lines`
--

CREATE TABLE `transaction_sell_lines_purchase_lines` (
  `id` bigint NOT NULL,
  `sell_line_id` int UNSIGNED DEFAULT NULL COMMENT 'id from transaction_sell_lines',
  `stock_adjustment_line_id` int UNSIGNED DEFAULT NULL COMMENT 'id from stock_adjustment_lines',
  `purchase_line_id` int UNSIGNED NOT NULL COMMENT 'id from purchase_lines',
  `quantity` decimal(22,4) NOT NULL,
  `qty_returned` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_sell_lines_purchase_lines`
--

INSERT INTO `transaction_sell_lines_purchase_lines` (`id`, `sell_line_id`, `stock_adjustment_line_id`, `purchase_line_id`, `quantity`, `qty_returned`, `created_at`, `updated_at`) VALUES
(1, 6, NULL, 8, 1.0000, 0.0000, '2026-07-24 10:13:56', '2026-07-24 10:13:56'),
(2, 3, NULL, 7, 2.0000, 0.0000, '2026-07-24 10:15:39', '2026-07-24 10:15:39'),
(3, 8, NULL, 7, 1.0000, 0.0000, '2026-07-24 10:28:17', '2026-07-24 10:28:17'),
(4, 11, NULL, 5, 1.0000, 0.0000, '2026-07-27 05:34:00', '2026-07-27 05:34:00'),
(5, 14, NULL, 13, 1.0000, 0.0000, '2026-07-27 05:43:40', '2026-07-27 05:43:40'),
(6, 15, NULL, 13, 59.0000, 0.0000, '2026-07-27 05:44:05', '2026-07-27 05:44:05'),
(7, 19, NULL, 12, 1.0000, 0.0000, '2026-07-27 06:19:24', '2026-07-27 06:19:24'),
(8, 20, NULL, 8, 1.0000, 0.0000, '2026-07-27 06:19:44', '2026-07-27 06:19:44'),
(9, 21, NULL, 8, 1.0000, 0.0000, '2026-07-27 06:20:48', '2026-07-27 06:20:48'),
(10, 22, NULL, 12, 1.0000, 0.0000, '2026-07-27 06:26:44', '2026-07-27 06:26:44'),
(11, 23, NULL, 28, 1.0000, 0.0000, '2026-07-27 06:26:55', '2026-07-27 06:26:55'),
(12, 24, NULL, 7, 1.0000, 0.0000, '2026-07-27 06:49:08', '2026-07-27 06:49:08'),
(13, 25, NULL, 8, 1.0000, 0.0000, '2026-07-27 06:49:15', '2026-07-27 06:49:15'),
(14, 26, NULL, 12, 1.0000, 0.0000, '2026-07-27 08:41:32', '2026-07-27 08:41:32'),
(15, 27, NULL, 7, 1.0000, 0.0000, '2026-07-27 08:45:04', '2026-07-27 08:45:04'),
(16, 28, NULL, 23, 1.0000, 0.0000, '2026-07-27 08:45:30', '2026-07-27 08:45:30'),
(17, 30, NULL, 23, 1.0000, 0.0000, '2026-07-27 08:47:43', '2026-07-27 08:47:43'),
(18, 33, NULL, 10, 1.0000, 0.0000, '2026-07-27 08:50:38', '2026-07-27 08:50:38'),
(19, 42, NULL, 7, 1.0000, 0.0000, '2026-07-27 09:30:54', '2026-07-27 09:30:54'),
(20, 43, NULL, 22, 1.0000, 0.0000, '2026-07-27 09:30:54', '2026-07-27 09:30:54'),
(21, 44, NULL, 22, 1.0000, 0.0000, '2026-07-27 09:35:37', '2026-07-27 09:35:37'),
(22, 45, NULL, 7, 1.0000, 0.0000, '2026-07-27 09:38:36', '2026-07-27 09:38:36'),
(23, 46, NULL, 7, 1.0000, 0.0000, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(24, 47, NULL, 22, 1.0000, 0.0000, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(25, 48, NULL, 1, 1.0000, 0.0000, '2026-07-27 09:39:08', '2026-07-27 09:39:08'),
(26, 49, NULL, 12, 1.0000, 0.0000, '2026-07-27 09:42:01', '2026-07-27 09:42:01'),
(27, 50, NULL, 6, 1.0000, 0.0000, '2026-07-27 09:42:01', '2026-07-27 09:42:01'),
(28, 51, NULL, 7, 1.0000, 0.0000, '2026-07-27 10:02:21', '2026-07-27 10:02:21'),
(29, 52, NULL, 7, 1.0000, 0.0000, '2026-07-27 10:02:26', '2026-07-27 10:02:26'),
(30, 53, NULL, 7, 1.0000, 0.0000, '2026-07-27 10:03:19', '2026-07-27 10:03:19'),
(31, 54, NULL, 21, 1.0000, 0.0000, '2026-07-27 10:03:19', '2026-07-27 10:03:19'),
(32, 57, NULL, 7, 1.0000, 0.0000, '2026-07-27 10:11:24', '2026-07-27 10:11:24'),
(33, 58, NULL, 12, 1.0000, 0.0000, '2026-07-27 10:11:43', '2026-07-27 10:11:43'),
(34, 60, NULL, 7, 1.0000, 0.0000, '2026-07-27 10:21:29', '2026-07-27 10:21:29'),
(35, 61, NULL, 21, 1.0000, 0.0000, '2026-07-28 08:11:08', '2026-07-28 08:11:08'),
(36, 62, NULL, 14, 1.0000, 0.0000, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(37, 63, NULL, 2, 1.0000, 0.0000, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(38, 64, NULL, 25, 2.0000, 0.0000, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(39, 65, NULL, 16, 1.0000, 0.0000, '2026-08-03 05:23:18', '2026-08-03 05:23:18'),
(40, 66, NULL, 21, 1.0000, 0.0000, '2026-08-03 05:36:11', '2026-08-03 05:36:11'),
(41, 67, NULL, 7, 1.0000, 0.0000, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(42, 68, NULL, 22, 1.0000, 0.0000, '2026-08-03 06:00:19', '2026-08-03 06:00:19'),
(43, 69, NULL, 7, 1.0000, 0.0000, '2026-08-03 06:02:12', '2026-08-03 06:02:12'),
(44, 70, NULL, 21, 1.0000, 0.0000, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(45, 71, NULL, 7, 1.0000, 0.0000, '2026-08-03 07:10:27', '2026-08-03 07:10:27'),
(46, 74, NULL, 7, 1.0000, 0.0000, '2026-08-03 07:14:07', '2026-08-03 07:14:07'),
(47, 75, NULL, 7, 1.0000, 0.0000, '2026-08-03 07:23:58', '2026-08-03 07:23:58'),
(48, 76, NULL, 12, 1.0000, 0.0000, '2026-08-03 07:27:56', '2026-08-03 07:27:56'),
(49, 77, NULL, 21, 1.0000, 0.0000, '2026-08-03 07:31:31', '2026-08-03 07:31:31'),
(50, 78, NULL, 12, 1.0000, 0.0000, '2026-08-03 07:32:08', '2026-08-03 07:32:08'),
(51, 80, NULL, 21, 1.0000, 0.0000, '2026-08-03 07:42:48', '2026-08-03 07:42:48'),
(52, 79, NULL, 21, 1.0000, 0.0000, '2026-08-03 07:57:05', '2026-08-03 07:57:05'),
(53, 81, NULL, 21, 1.0000, 0.0000, '2026-08-03 08:34:31', '2026-08-03 08:34:31'),
(54, 82, NULL, 12, 1.0000, 0.0000, '2026-08-03 08:42:12', '2026-08-03 08:42:12'),
(55, 83, NULL, 21, 1.0000, 0.0000, '2026-08-03 08:45:04', '2026-08-03 08:45:04'),
(56, 84, NULL, 21, 1.0000, 0.0000, '2026-08-03 08:46:35', '2026-08-03 08:46:35'),
(57, 85, NULL, 22, 1.0000, 0.0000, '2026-08-03 08:47:42', '2026-08-03 08:47:42'),
(58, 86, NULL, 12, 1.0000, 0.0000, '2026-08-03 09:07:45', '2026-08-03 09:07:45'),
(59, 87, NULL, 12, 1.0000, 0.0000, '2026-08-03 09:19:03', '2026-08-03 09:19:03'),
(60, 88, NULL, 12, 1.0000, 0.0000, '2026-08-03 09:20:11', '2026-08-03 09:20:11'),
(61, 89, NULL, 12, 1.0000, 0.0000, '2026-08-03 09:27:47', '2026-08-03 09:27:47'),
(62, 90, NULL, 7, 1.0000, 0.0000, '2026-08-03 09:30:59', '2026-08-03 09:30:59'),
(63, 91, NULL, 7, 2.0000, 0.0000, '2026-08-03 09:33:47', '2026-08-03 09:33:47'),
(64, 92, NULL, 7, 1.0000, 0.0000, '2026-08-03 09:39:11', '2026-08-03 09:39:11'),
(65, 93, NULL, 7, 1.0000, 0.0000, '2026-08-03 09:39:32', '2026-08-03 09:39:32'),
(66, 94, NULL, 12, 1.0000, 0.0000, '2026-08-03 09:40:47', '2026-08-03 09:40:47'),
(67, 95, NULL, 12, 1.0000, 0.0000, '2026-08-03 09:43:42', '2026-08-03 09:43:42'),
(68, 96, NULL, 12, 1.0000, 0.0000, '2026-08-03 10:33:09', '2026-08-03 10:33:09'),
(69, 97, NULL, 12, 1.0000, 0.0000, '2026-08-03 10:35:05', '2026-08-03 10:35:05'),
(70, 98, NULL, 7, 1.0000, 0.0000, '2026-08-03 10:45:50', '2026-08-03 10:45:50'),
(71, 99, NULL, 7, 1.0000, 0.0000, '2026-08-03 10:47:11', '2026-08-03 10:47:11'),
(72, 100, NULL, 7, 1.0000, 0.0000, '2026-08-04 05:19:16', '2026-08-04 05:19:16'),
(73, 101, NULL, 12, 1.0000, 0.0000, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(74, 102, NULL, 21, 1.0000, 0.0000, '2026-08-04 08:28:24', '2026-08-04 08:28:24'),
(75, 103, NULL, 7, 1.0000, 0.0000, '2026-08-04 08:46:22', '2026-08-04 08:46:22'),
(76, 104, NULL, 12, 1.0000, 0.0000, '2026-08-05 04:25:37', '2026-08-05 04:25:37'),
(77, 105, NULL, 7, 1.0000, 0.0000, '2026-08-05 05:40:29', '2026-08-05 05:40:29'),
(78, 106, NULL, 12, 1.0000, 0.0000, '2026-08-05 05:40:59', '2026-08-05 05:40:59'),
(79, 107, NULL, 0, 1.0000, 0.0000, '2026-08-05 06:32:36', '2026-08-05 06:32:36'),
(80, 108, NULL, 12, 1.0000, 0.0000, '2026-08-05 08:03:09', '2026-08-05 08:03:09'),
(81, 109, NULL, 21, 1.0000, 0.0000, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(82, 110, NULL, 12, 1.0000, 0.0000, '2026-08-05 08:03:38', '2026-08-05 08:03:38'),
(83, 111, NULL, 16, 1.0000, 0.0000, '2026-08-06 05:51:04', '2026-08-06 05:51:04'),
(84, 112, NULL, 21, 1.0000, 0.0000, '2026-08-07 05:48:53', '2026-08-07 05:48:53'),
(85, 113, NULL, 21, 1.0000, 0.0000, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(86, 114, NULL, 11, 2.0000, 0.0000, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(87, 115, NULL, 23, 1.0000, 0.0000, '2026-08-12 04:04:23', '2026-08-12 04:04:23'),
(88, 116, NULL, 21, 1.0000, 0.0000, '2026-08-12 06:35:00', '2026-08-12 06:35:00'),
(89, 117, NULL, 0, 1.0000, 0.0000, '2026-08-12 06:35:18', '2026-08-12 06:35:18'),
(90, 118, NULL, 22, 1.0000, 0.0000, '2026-08-12 06:35:38', '2026-08-12 06:35:38'),
(91, 119, NULL, 7, 1.0000, 0.0000, '2026-08-12 06:35:38', '2026-08-12 06:35:38'),
(92, 120, NULL, 0, 1.0000, 0.0000, '2026-08-12 06:35:38', '2026-08-12 06:35:38'),
(93, 121, NULL, 21, 1.0000, 0.0000, '2026-08-12 07:06:38', '2026-08-12 07:06:38'),
(94, 122, NULL, 0, 2.0000, 0.0000, '2026-08-12 07:13:57', '2026-08-12 07:13:57'),
(95, 124, NULL, 7, 1.0000, 0.0000, '2026-08-13 06:13:49', '2026-08-13 06:13:49'),
(96, 126, NULL, 0, 1.0000, 0.0000, '2026-08-13 06:29:51', '2026-08-13 06:29:51'),
(97, 128, NULL, 7, 1.0000, 0.0000, '2026-08-13 06:41:27', '2026-08-13 06:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `types_of_services`
--

CREATE TABLE `types_of_services` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `business_id` int NOT NULL,
  `location_price_group` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `packing_charge` decimal(22,4) DEFAULT NULL,
  `packing_charge_type` enum('fixed','percent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_custom_fields` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `actual_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allow_decimal` tinyint(1) NOT NULL,
  `base_unit_id` int DEFAULT NULL,
  `base_unit_multiplier` decimal(20,4) DEFAULT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `business_id`, `actual_name`, `short_name`, `allow_decimal`, `base_unit_id`, `base_unit_multiplier`, `created_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pieces', 'Pc(s)', 0, NULL, NULL, 1, NULL, '2026-07-18 18:56:53', '2026-07-18 18:56:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `user_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `surname` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `language` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `contact_no` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_id` int UNSIGNED DEFAULT NULL,
  `available_at` datetime DEFAULT NULL COMMENT 'Service staff avilable at. Calculated from product preparation_time_in_minutes',
  `paused_at` datetime DEFAULT NULL COMMENT 'Service staff available time paused at, Will be nulled on resume.',
  `max_sales_discount_percent` decimal(5,2) DEFAULT NULL,
  `allow_login` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','inactive','terminated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_enable_service_staff_pin` tinyint(1) NOT NULL DEFAULT '0',
  `service_staff_pin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `crm_contact_id` int UNSIGNED DEFAULT NULL,
  `is_cmmsn_agnt` tinyint(1) NOT NULL DEFAULT '0',
  `cmmsn_percent` decimal(4,2) NOT NULL DEFAULT '0.00',
  `selected_contacts` tinyint(1) NOT NULL DEFAULT '0',
  `dob` date DEFAULT NULL,
  `gender` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` enum('married','unmarried','divorced') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `family_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fb_link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_media_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_media_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `current_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `guardian_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_1` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_field_4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_proof_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_proof_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `surname`, `first_name`, `last_name`, `username`, `email`, `password`, `language`, `contact_no`, `address`, `remember_token`, `business_id`, `available_at`, `paused_at`, `max_sales_discount_percent`, `allow_login`, `status`, `is_enable_service_staff_pin`, `service_staff_pin`, `crm_contact_id`, `is_cmmsn_agnt`, `cmmsn_percent`, `selected_contacts`, `dob`, `gender`, `marital_status`, `blood_group`, `contact_number`, `alt_number`, `family_number`, `fb_link`, `twitter_link`, `social_media_1`, `social_media_2`, `permanent_address`, `current_address`, `guardian_name`, `custom_field_1`, `custom_field_2`, `custom_field_3`, `custom_field_4`, `bank_details`, `id_proof_name`, `id_proof_number`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'user', NULL, 'Japs', 'POS', 'japspos', 'japspos@gmail.com', '$2y$10$LH3D2XH/82k.Av8HfGb3GOfKM3F7Qi7I86iZLyxhJFtjajS8pKHpK', 'en', NULL, NULL, 'SyP9LQsIpbXbGOSaVXyzifBMd7K5lOaUoKToYhP6v3NlRJLu3vrYbOJo6SHx', 1, NULL, NULL, NULL, 1, 'active', 0, NULL, NULL, 0, 0.00, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-06-04 16:29:53', '2026-07-20 06:42:21'),
(3, 'user', 'mr', 'Sales', 'Man', 'sales', 'sales@gmail.com', '$2y$10$OzGObq/ceIZ3APtS0cuaQ.7NuE.ATg5eQkOcMUcxdlz1qVAFMKpd6', 'en', NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 'active', 0, NULL, NULL, 0, 0.00, 0, NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-08-13 07:40:09', '2026-08-13 07:40:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_contact_access`
--

CREATE TABLE `user_contact_access` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `contact_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variations`
--

CREATE TABLE `variations` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `sub_sku` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_variation_id` int UNSIGNED NOT NULL,
  `variation_value_id` int DEFAULT NULL,
  `default_purchase_price` decimal(22,4) DEFAULT NULL,
  `dpp_inc_tax` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `profit_percent` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `default_sell_price` decimal(22,4) DEFAULT NULL,
  `sell_price_inc_tax` decimal(22,4) DEFAULT NULL COMMENT 'Sell price including tax',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `combo_variations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Contains the combo variation details'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variations`
--

INSERT INTO `variations` (`id`, `name`, `product_id`, `sub_sku`, `product_variation_id`, `variation_value_id`, `default_purchase_price`, `dpp_inc_tax`, `profit_percent`, `default_sell_price`, `sell_price_inc_tax`, `created_at`, `updated_at`, `deleted_at`, `combo_variations`) VALUES
(1, 'DUMMY', 1, 'TOB-CIG-LUX', 1, NULL, 20.0000, 21.9500, 13.8950, 22.7790, 25.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(2, 'DUMMY', 2, 'TOB-CIG-MRB', 2, NULL, 55.0000, 60.3625, 19.2793, 65.6036, 72.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(3, 'DUMMY', 3, 'TOB-MST-COP', 3, NULL, 28.0000, 30.7300, 23.6575, 34.6241, 38.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(4, 'DUMMY', 4, 'TOB-CGR-SWS', 4, NULL, 18.0000, 19.7550, 31.6122, 23.6902, 26.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(5, 'DUMMY', 5, 'TOB-LFC-CHE', 5, NULL, 14.0000, 15.3650, 30.1657, 18.2232, 20.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(6, 'DUMMY', 6, 'TOB-ECG-JUL', 6, NULL, 12.0000, 13.1700, 36.6742, 16.4009, 18.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(7, 'DUMMY', 7, 'TOB-RYO-BUG', 7, NULL, 8.0000, 8.7800, 36.6700, 10.9300, 12.0000, '2026-07-18 19:00:29', '2026-07-21 10:59:42', NULL, '[]'),
(8, 'DUMMY', 8, 'TOB-PIP-CBK', 8, NULL, 7.0000, 7.6825, 43.1829, 10.0228, 11.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29', NULL, '[]'),
(39, 'DUMMY', 39, 'ECOM-001', 39, NULL, 8.7800, 8.7800, 36.6700, 12.0000, 12.0000, '2026-07-22 17:42:40', '2026-07-22 17:42:40', NULL, NULL),
(40, 'DUMMY', 40, 'ECOM-002', 40, NULL, 10.2000, 10.2000, 42.1600, 14.5000, 14.5000, '2026-07-22 17:42:40', '2026-07-22 17:42:40', NULL, NULL),
(41, 'DUMMY', 41, 'ECOM-003', 41, NULL, 1.5000, 1.5000, 166.0000, 3.9900, 3.9900, '2026-07-22 17:42:43', '2026-07-22 17:42:43', NULL, NULL),
(42, 'DUMMY', 42, 'ECOM-004', 42, NULL, 2.1000, 2.1000, 137.6200, 4.9900, 4.9900, '2026-07-22 17:42:44', '2026-07-22 17:42:44', NULL, NULL),
(43, 'DUMMY', 43, 'ECOM-005', 43, NULL, 2.4000, 2.4000, 128.7500, 5.4900, 5.4900, '2026-07-22 17:42:45', '2026-07-27 05:41:44', NULL, NULL),
(44, 'DUMMY', 44, 'ECOM-006', 44, NULL, 3.2000, 3.2000, 118.4400, 6.9900, 6.9900, '2026-07-22 17:42:47', '2026-07-22 17:42:47', NULL, NULL),
(45, 'DUMMY', 45, 'ECOM-007', 45, NULL, 1.6000, 1.6000, 136.8800, 3.7900, 3.7900, '2026-07-22 17:42:50', '2026-07-22 17:42:50', NULL, NULL),
(46, 'DUMMY', 46, 'ECOM-008', 46, NULL, 1.8000, 1.8000, 150.0000, 4.5000, 4.5000, '2026-07-22 17:42:51', '2026-07-22 17:42:51', NULL, NULL),
(47, 'DUMMY', 47, 'ECOM-009', 47, NULL, 1.1000, 1.1000, 171.8200, 2.9900, 2.9900, '2026-07-22 17:42:52', '2026-07-22 17:42:52', NULL, NULL),
(48, 'DUMMY', 48, 'ECOM-010', 48, NULL, 1.4000, 1.4000, 149.2900, 3.4900, 3.4900, '2026-07-22 17:42:52', '2026-07-22 17:42:52', NULL, NULL),
(49, 'DUMMY', 49, 'ECOM-011', 49, NULL, 0.7000, 0.7000, 184.2900, 1.9900, 1.9900, '2026-07-22 17:42:53', '2026-07-22 17:42:53', NULL, NULL),
(50, 'DUMMY', 50, 'ECOM-012', 50, NULL, 2.5000, 2.5000, 139.6000, 5.9900, 5.9900, '2026-07-22 17:42:55', '2026-07-22 17:42:55', NULL, NULL),
(51, 'DUMMY', 51, 'ECOM-013', 51, NULL, 3.8000, 3.8000, 136.5800, 8.9900, 8.9900, '2026-07-22 17:42:55', '2026-07-22 17:42:55', NULL, NULL),
(52, 'DUMMY', 52, 'ECOM-014', 52, NULL, 4.2000, 4.2000, 125.9500, 9.4900, 9.4900, '2026-07-22 17:42:56', '2026-07-22 17:42:56', NULL, NULL),
(53, 'DUMMY', 53, 'ECOM-015', 53, NULL, 3.1000, 3.1000, 141.6100, 7.4900, 7.4900, '2026-07-22 17:42:57', '2026-07-22 17:42:57', NULL, NULL),
(54, 'DUMMY', 54, 'ECOM-016', 54, NULL, 5.0000, 5.0000, 139.8000, 11.9900, 11.9900, '2026-07-22 17:42:59', '2026-07-22 17:42:59', NULL, NULL),
(55, 'DUMMY', 55, 'ECOM-017', 55, NULL, 1.9000, 1.9000, 125.7900, 4.2900, 4.2900, '2026-07-22 17:43:00', '2026-07-22 17:43:00', NULL, NULL),
(56, 'DUMMY', 56, 'ECOM-018', 56, NULL, 7.5000, 7.5000, 113.3300, 16.0000, 16.0000, '2026-07-22 17:43:02', '2026-07-22 17:43:02', NULL, NULL),
(57, 'DUMMY', 57, 'ECOM-019', 57, NULL, 8.0000, 8.0000, 131.2500, 18.5000, 18.5000, '2026-07-22 17:43:03', '2026-07-22 17:43:03', NULL, NULL),
(58, 'DUMMY', 58, '205751919317', 58, NULL, 102.0000, 102.0000, 25.0000, 127.5000, 127.5000, '2026-08-12 06:18:31', '2026-08-12 06:18:31', NULL, '[]'),
(59, 'DUMMY', 59, '892678805625', 59, NULL, 102.0000, 102.0000, 25.0000, 127.5000, 127.5000, '2026-08-12 06:56:32', '2026-08-12 06:56:32', NULL, '[]');

-- --------------------------------------------------------

--
-- Table structure for table `variation_group_prices`
--

CREATE TABLE `variation_group_prices` (
  `id` int UNSIGNED NOT NULL,
  `variation_id` int UNSIGNED NOT NULL,
  `price_group_id` int UNSIGNED NOT NULL,
  `price_inc_tax` decimal(22,4) NOT NULL,
  `price_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variation_group_prices`
--

INSERT INTO `variation_group_prices` (`id`, `variation_id`, `price_group_id`, `price_inc_tax`, `price_type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 25.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(2, 1, 2, 22.5000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(3, 2, 1, 72.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(4, 2, 2, 64.8000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(5, 3, 1, 38.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(6, 3, 2, 33.0600, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(7, 4, 1, 26.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(8, 4, 2, 22.1000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(9, 5, 1, 20.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(10, 5, 2, 17.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(11, 6, 1, 18.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(12, 6, 2, 14.9400, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(13, 7, 1, 12.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(14, 7, 2, 9.9600, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(15, 8, 1, 11.0000, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(16, 8, 2, 9.0200, 'fixed', '2026-07-18 19:00:29', '2026-07-24 15:59:34'),
(17, 39, 1, 12.0000, 'fixed', '2026-07-24 15:59:34', '2026-07-24 15:59:34'),
(18, 39, 2, 10.8000, 'fixed', '2026-07-24 15:59:34', '2026-07-24 15:59:34'),
(19, 40, 1, 14.5000, 'fixed', '2026-07-24 15:59:34', '2026-07-24 15:59:34'),
(20, 40, 2, 13.0500, 'fixed', '2026-07-24 15:59:34', '2026-07-24 15:59:34'),
(21, 41, 1, 3.9900, 'fixed', '2026-07-24 15:59:34', '2026-07-24 15:59:34'),
(22, 41, 2, 3.5910, 'fixed', '2026-07-24 15:59:34', '2026-07-24 15:59:34'),
(23, 58, 1, 127.5000, 'fixed', '2026-08-12 06:18:31', '2026-08-12 06:18:31'),
(24, 58, 2, 127.5000, 'fixed', '2026-08-12 06:18:31', '2026-08-12 06:18:31'),
(25, 59, 1, 127.5000, 'fixed', '2026-08-12 06:56:32', '2026-08-12 06:56:32'),
(26, 59, 2, 127.5000, 'fixed', '2026-08-12 06:56:32', '2026-08-12 06:56:32');

-- --------------------------------------------------------

--
-- Table structure for table `variation_location_details`
--

CREATE TABLE `variation_location_details` (
  `id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `product_variation_id` int UNSIGNED NOT NULL COMMENT 'id from product_variations table',
  `variation_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED NOT NULL,
  `qty_available` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variation_location_details`
--

INSERT INTO `variation_location_details` (`id`, `product_id`, `product_variation_id`, `variation_id`, `location_id`, `qty_available`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 99.0000, '2026-07-18 19:00:29', '2026-07-27 09:39:08'),
(2, 2, 2, 2, 1, 79.0000, '2026-07-18 19:00:29', '2026-08-03 05:23:18'),
(3, 3, 3, 3, 1, 60.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(4, 4, 4, 4, 1, 50.0000, '2026-07-18 19:00:29', '2026-07-18 19:00:29'),
(5, 5, 5, 5, 1, 50.0000, '2026-07-18 19:00:29', '2026-07-27 09:02:00'),
(6, 6, 6, 6, 1, 39.0000, '2026-07-18 19:00:29', '2026-07-27 09:42:01'),
(7, 7, 7, 7, 1, 19.0000, '2026-07-18 19:00:29', '2026-08-13 07:24:05'),
(8, 8, 8, 8, 1, 28.0000, '2026-07-18 19:00:29', '2026-07-27 08:59:57'),
(39, 39, 39, 39, 1, 44.0000, '2026-07-22 17:42:40', '2026-07-22 17:42:40'),
(40, 40, 40, 40, 1, 47.0000, '2026-07-22 17:42:40', '2026-07-27 08:50:38'),
(41, 41, 41, 41, 1, 50.0000, '2026-07-22 17:42:43', '2026-08-12 04:04:23'),
(42, 42, 42, 42, 1, 40.0000, '2026-07-22 17:42:44', '2026-08-05 08:03:38'),
(43, 43, 43, 43, 1, -1.0000, '2026-07-22 17:42:45', '2026-08-05 06:32:36'),
(44, 44, 44, 44, 1, 63.0000, '2026-07-22 17:42:47', '2026-08-03 05:23:18'),
(45, 45, 45, 45, 1, 68.0000, '2026-07-22 17:42:50', '2026-07-22 17:42:50'),
(46, 46, 46, 46, 1, 70.0000, '2026-07-22 17:42:51', '2026-08-06 05:51:04'),
(47, 47, 47, 47, 1, 76.0000, '2026-07-22 17:42:52', '2026-07-22 17:42:52'),
(48, 48, 48, 48, 1, 80.0000, '2026-07-22 17:42:52', '2026-07-22 17:42:52'),
(49, 49, 49, 49, 1, 84.0000, '2026-07-22 17:42:53', '2026-07-22 17:42:53'),
(50, 50, 50, 50, 1, 88.0000, '2026-07-22 17:42:55', '2026-07-22 17:42:55'),
(51, 51, 51, 51, 1, 78.0000, '2026-07-22 17:42:55', '2026-08-12 07:06:38'),
(52, 52, 52, 52, 1, 95.0000, '2026-07-22 17:42:56', '2026-08-12 06:35:38'),
(53, 53, 53, 53, 1, 97.0000, '2026-07-22 17:42:57', '2026-08-12 04:04:23'),
(54, 54, 54, 54, 1, 104.0000, '2026-07-22 17:42:59', '2026-07-22 17:42:59'),
(55, 55, 55, 55, 1, 106.0000, '2026-07-22 17:43:00', '2026-08-03 05:23:18'),
(56, 56, 56, 56, 1, 112.0000, '2026-07-22 17:43:02', '2026-07-22 17:43:02'),
(57, 57, 57, 57, 1, 116.0000, '2026-07-22 17:43:03', '2026-07-22 17:43:03'),
(58, 58, 58, 58, 1, -4.0000, '2026-08-12 06:35:18', '2026-08-12 07:13:57'),
(59, 59, 59, 59, 1, -1.0000, '2026-08-13 06:29:51', '2026-08-13 06:29:51');

-- --------------------------------------------------------

--
-- Table structure for table `variation_templates`
--

CREATE TABLE `variation_templates` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variation_value_templates`
--

CREATE TABLE `variation_value_templates` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variation_template_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranties`
--

CREATE TABLE `warranties` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_id` int NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duration` int NOT NULL,
  `duration_type` enum('days','months','years') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accounts_business_id_index` (`business_id`),
  ADD KEY `accounts_account_type_id_index` (`account_type_id`),
  ADD KEY `accounts_created_by_index` (`created_by`);

--
-- Indexes for table `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_transactions_account_id_index` (`account_id`),
  ADD KEY `account_transactions_transaction_id_index` (`transaction_id`),
  ADD KEY `account_transactions_transaction_payment_id_index` (`transaction_payment_id`),
  ADD KEY `account_transactions_transfer_transaction_id_index` (`transfer_transaction_id`),
  ADD KEY `account_transactions_created_by_index` (`created_by`),
  ADD KEY `account_transactions_type_index` (`type`),
  ADD KEY `account_transactions_sub_type_index` (`sub_type`),
  ADD KEY `account_transactions_operation_date_index` (`operation_date`);

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_types_parent_account_type_id_index` (`parent_account_type_id`),
  ADD KEY `account_types_business_id_index` (`business_id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `ai_assistance_settings`
--
ALTER TABLE `ai_assistance_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_assistance_settings_business_id_unique` (`business_id`);

--
-- Indexes for table `barcodes`
--
ALTER TABLE `barcodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barcodes_business_id_foreign` (`business_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_contact_id_foreign` (`contact_id`),
  ADD KEY `bookings_business_id_foreign` (`business_id`),
  ADD KEY `bookings_created_by_foreign` (`created_by`),
  ADD KEY `bookings_table_id_index` (`table_id`),
  ADD KEY `bookings_waiter_id_index` (`waiter_id`),
  ADD KEY `bookings_location_id_index` (`location_id`),
  ADD KEY `bookings_booking_status_index` (`booking_status`),
  ADD KEY `bookings_correspondent_id_index` (`correspondent_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brands_business_id_foreign` (`business_id`),
  ADD KEY `brands_created_by_foreign` (`created_by`);

--
-- Indexes for table `business`
--
ALTER TABLE `business`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_owner_id_foreign` (`owner_id`),
  ADD KEY `business_currency_id_foreign` (`currency_id`),
  ADD KEY `business_default_sales_tax_foreign` (`default_sales_tax`);

--
-- Indexes for table `business_locations`
--
ALTER TABLE `business_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_locations_business_id_index` (`business_id`),
  ADD KEY `business_locations_invoice_scheme_id_foreign` (`invoice_scheme_id`),
  ADD KEY `business_locations_invoice_layout_id_foreign` (`invoice_layout_id`),
  ADD KEY `business_locations_sale_invoice_layout_id_index` (`sale_invoice_layout_id`),
  ADD KEY `business_locations_selling_price_group_id_index` (`selling_price_group_id`),
  ADD KEY `business_locations_receipt_printer_type_index` (`receipt_printer_type`),
  ADD KEY `business_locations_printer_id_index` (`printer_id`);

--
-- Indexes for table `cash_denominations`
--
ALTER TABLE `cash_denominations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_denominations_model_type_model_id_index` (`model_type`,`model_id`);

--
-- Indexes for table `cash_registers`
--
ALTER TABLE `cash_registers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_registers_business_id_foreign` (`business_id`),
  ADD KEY `cash_registers_user_id_foreign` (`user_id`),
  ADD KEY `cash_registers_location_id_index` (`location_id`);

--
-- Indexes for table `cash_register_transactions`
--
ALTER TABLE `cash_register_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_register_transactions_cash_register_id_foreign` (`cash_register_id`),
  ADD KEY `cash_register_transactions_transaction_id_index` (`transaction_id`),
  ADD KEY `cash_register_transactions_type_index` (`type`),
  ADD KEY `cash_register_transactions_transaction_type_index` (`transaction_type`),
  ADD KEY `cash_register_transactions_transaction_payment_id_foreign` (`transaction_payment_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_business_id_foreign` (`business_id`),
  ADD KEY `categories_created_by_foreign` (`created_by`),
  ADD KEY `categories_parent_id_index` (`parent_id`);

--
-- Indexes for table `categorizables`
--
ALTER TABLE `categorizables`
  ADD KEY `categorizables_categorizable_type_categorizable_id_index` (`categorizable_type`,`categorizable_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_business_id_foreign` (`business_id`),
  ADD KEY `contacts_created_by_foreign` (`created_by`),
  ADD KEY `contacts_type_index` (`type`),
  ADD KEY `contacts_contact_status_index` (`contact_status`);

--
-- Indexes for table `crm_call_logs`
--
ALTER TABLE `crm_call_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crm_call_logs_contact_id_foreign` (`contact_id`),
  ADD KEY `crm_call_logs_business_id_contact_id_index` (`business_id`,`contact_id`);

--
-- Indexes for table `crm_lead_users`
--
ALTER TABLE `crm_lead_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `crm_lead_users_contact_id_user_id_unique` (`contact_id`,`user_id`),
  ADD KEY `crm_lead_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `crm_schedules`
--
ALTER TABLE `crm_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `crm_schedules_contact_id_foreign` (`contact_id`),
  ADD KEY `crm_schedules_business_id_status_index` (`business_id`,`status`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_groups_business_id_foreign` (`business_id`),
  ADD KEY `customer_groups_created_by_index` (`created_by`),
  ADD KEY `customer_groups_price_calculation_type_index` (`price_calculation_type`),
  ADD KEY `customer_groups_selling_price_group_id_index` (`selling_price_group_id`);

--
-- Indexes for table `dashboard_configurations`
--
ALTER TABLE `dashboard_configurations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dashboard_configurations_business_id_foreign` (`business_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `discounts_business_id_index` (`business_id`),
  ADD KEY `discounts_brand_id_index` (`brand_id`),
  ADD KEY `discounts_category_id_index` (`category_id`),
  ADD KEY `discounts_location_id_index` (`location_id`),
  ADD KEY `discounts_priority_index` (`priority`),
  ADD KEY `discounts_spg_index` (`spg`);

--
-- Indexes for table `discount_variations`
--
ALTER TABLE `discount_variations`
  ADD KEY `discount_variations_discount_id_index` (`discount_id`),
  ADD KEY `discount_variations_variation_id_index` (`variation_id`);

--
-- Indexes for table `document_and_notes`
--
ALTER TABLE `document_and_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_and_notes_business_id_index` (`business_id`),
  ADD KEY `document_and_notes_notable_id_index` (`notable_id`),
  ADD KEY `document_and_notes_created_by_index` (`created_by`);

--
-- Indexes for table `ecommerce_settings`
--
ALTER TABLE `ecommerce_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecommerce_settings_business_id_unique` (`business_id`);

--
-- Indexes for table `ecom_api_settings`
--
ALTER TABLE `ecom_api_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_banners`
--
ALTER TABLE `ecom_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_carts`
--
ALTER TABLE `ecom_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ecom_carts_session_id_index` (`session_id`);

--
-- Indexes for table `ecom_cart_items`
--
ALTER TABLE `ecom_cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_cart_items_cart_id_variation_id_unique` (`cart_id`,`variation_id`);

--
-- Indexes for table `ecom_collections`
--
ALTER TABLE `ecom_collections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_collections_business_id_slug_unique` (`business_id`,`slug`);

--
-- Indexes for table `ecom_collection_products`
--
ALTER TABLE `ecom_collection_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_collection_products_collection_id_product_id_unique` (`collection_id`,`product_id`);

--
-- Indexes for table `ecom_coupons`
--
ALTER TABLE `ecom_coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_coupons_business_id_code_unique` (`business_id`,`code`);

--
-- Indexes for table `ecom_coupon_usages`
--
ALTER TABLE `ecom_coupon_usages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_customer_addresses`
--
ALTER TABLE `ecom_customer_addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_customer_licenses`
--
ALTER TABLE `ecom_customer_licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ecom_customer_licenses_contact_id_index` (`contact_id`);

--
-- Indexes for table `ecom_order_meta`
--
ALTER TABLE `ecom_order_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_order_meta_transaction_id_unique` (`transaction_id`),
  ADD UNIQUE KEY `ecom_order_meta_tracking_token_unique` (`tracking_token`);

--
-- Indexes for table `ecom_reviews`
--
ALTER TABLE `ecom_reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_shipping_rules`
--
ALTER TABLE `ecom_shipping_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_tax_rules`
--
ALTER TABLE `ecom_tax_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_wishlists`
--
ALTER TABLE `ecom_wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_wishlists_contact_id_product_id_variation_id_unique` (`contact_id`,`product_id`,`variation_id`);

--
-- Indexes for table `employee_attendances`
--
ALTER TABLE `employee_attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_attendances_business_id_user_id_index` (`business_id`,`user_id`),
  ADD KEY `employee_attendances_business_id_clock_in_at_index` (`business_id`,`clock_in_at`),
  ADD KEY `employee_attendances_user_id_status_index` (`user_id`,`status`);

--
-- Indexes for table `employee_attendance_breaks`
--
ALTER TABLE `employee_attendance_breaks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_attendance_breaks_attendance_id_index` (`attendance_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expense_categories_business_id_foreign` (`business_id`);

--
-- Indexes for table `group_sub_taxes`
--
ALTER TABLE `group_sub_taxes`
  ADD KEY `group_sub_taxes_group_tax_id_foreign` (`group_tax_id`),
  ADD KEY `group_sub_taxes_tax_id_foreign` (`tax_id`);

--
-- Indexes for table `invoice_layouts`
--
ALTER TABLE `invoice_layouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_layouts_business_id_foreign` (`business_id`);

--
-- Indexes for table `invoice_schemes`
--
ALTER TABLE `invoice_schemes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_schemes_business_id_foreign` (`business_id`),
  ADD KEY `invoice_schemes_scheme_type_index` (`scheme_type`),
  ADD KEY `invoice_schemes_number_type_index` (`number_type`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_business_id_index` (`business_id`),
  ADD KEY `media_uploaded_by_index` (`uploaded_by`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_type_model_id_index` (`model_type`,`model_id`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_type_model_id_index` (`model_type`,`model_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `password_resets_contacts`
--
ALTER TABLE `password_resets_contacts`
  ADD KEY `password_resets_contacts_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `printers`
--
ALTER TABLE `printers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `printers_business_id_foreign` (`business_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_brand_id_foreign` (`brand_id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_sub_category_id_foreign` (`sub_category_id`),
  ADD KEY `products_tax_foreign` (`tax`),
  ADD KEY `products_name_index` (`name`),
  ADD KEY `products_business_id_index` (`business_id`),
  ADD KEY `products_unit_id_index` (`unit_id`),
  ADD KEY `products_created_by_index` (`created_by`),
  ADD KEY `products_warranty_id_index` (`warranty_id`),
  ADD KEY `products_type_index` (`type`),
  ADD KEY `products_tax_type_index` (`tax_type`),
  ADD KEY `products_barcode_type_index` (`barcode_type`),
  ADD KEY `products_secondary_unit_id_index` (`secondary_unit_id`),
  ADD KEY `products_business_id_sales_channel_index` (`business_id`,`sales_channel`),
  ADD KEY `products_business_id_ecom_slug_index` (`business_id`,`ecom_slug`);

--
-- Indexes for table `product_locations`
--
ALTER TABLE `product_locations`
  ADD KEY `product_locations_product_id_index` (`product_id`),
  ADD KEY `product_locations_location_id_index` (`location_id`);

--
-- Indexes for table `product_racks`
--
ALTER TABLE `product_racks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_racks_business_id_index` (`business_id`),
  ADD KEY `product_racks_location_id_index` (`location_id`),
  ADD KEY `product_racks_product_id_index` (`product_id`);

--
-- Indexes for table `product_variations`
--
ALTER TABLE `product_variations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variations_name_index` (`name`),
  ADD KEY `product_variations_product_id_index` (`product_id`);

--
-- Indexes for table `purchase_lines`
--
ALTER TABLE `purchase_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_lines_transaction_id_foreign` (`transaction_id`),
  ADD KEY `purchase_lines_product_id_foreign` (`product_id`),
  ADD KEY `purchase_lines_variation_id_foreign` (`variation_id`),
  ADD KEY `purchase_lines_tax_id_foreign` (`tax_id`),
  ADD KEY `purchase_lines_sub_unit_id_index` (`sub_unit_id`),
  ADD KEY `purchase_lines_lot_number_index` (`lot_number`);

--
-- Indexes for table `reference_counts`
--
ALTER TABLE `reference_counts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reference_counts_business_id_index` (`business_id`);

--
-- Indexes for table `res_product_modifier_sets`
--
ALTER TABLE `res_product_modifier_sets`
  ADD KEY `res_product_modifier_sets_modifier_set_id_foreign` (`modifier_set_id`);

--
-- Indexes for table `res_tables`
--
ALTER TABLE `res_tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `res_tables_business_id_foreign` (`business_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `roles_business_id_foreign` (`business_id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `selling_price_groups`
--
ALTER TABLE `selling_price_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `selling_price_groups_business_id_foreign` (`business_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD UNIQUE KEY `sessions_id_unique` (`id`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipping_methods_business_id_name_unique` (`business_id`,`name`),
  ADD KEY `shipping_methods_business_id_index` (`business_id`),
  ADD KEY `shipping_methods_is_active_index` (`is_active`);

--
-- Indexes for table `stock_adjustment_lines`
--
ALTER TABLE `stock_adjustment_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_adjustment_lines_product_id_foreign` (`product_id`),
  ADD KEY `stock_adjustment_lines_variation_id_foreign` (`variation_id`),
  ADD KEY `stock_adjustment_lines_transaction_id_index` (`transaction_id`),
  ADD KEY `stock_adjustment_lines_lot_no_line_id_index` (`lot_no_line_id`);

--
-- Indexes for table `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tax_rates_business_id_foreign` (`business_id`),
  ADD KEY `tax_rates_created_by_foreign` (`created_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_tax_id_foreign` (`tax_id`),
  ADD KEY `transactions_business_id_index` (`business_id`),
  ADD KEY `transactions_type_index` (`type`),
  ADD KEY `transactions_contact_id_index` (`contact_id`),
  ADD KEY `transactions_transaction_date_index` (`transaction_date`),
  ADD KEY `transactions_created_by_index` (`created_by`),
  ADD KEY `transactions_location_id_index` (`location_id`),
  ADD KEY `transactions_expense_for_foreign` (`expense_for`),
  ADD KEY `transactions_expense_category_id_index` (`expense_category_id`),
  ADD KEY `transactions_sub_type_index` (`sub_type`),
  ADD KEY `transactions_return_parent_id_index` (`return_parent_id`),
  ADD KEY `type` (`type`),
  ADD KEY `transactions_status_index` (`status`),
  ADD KEY `transactions_sub_status_index` (`sub_status`),
  ADD KEY `transactions_res_table_id_index` (`res_table_id`),
  ADD KEY `transactions_res_waiter_id_index` (`res_waiter_id`),
  ADD KEY `transactions_res_order_status_index` (`res_order_status`),
  ADD KEY `transactions_payment_status_index` (`payment_status`),
  ADD KEY `transactions_discount_type_index` (`discount_type`),
  ADD KEY `transactions_commission_agent_index` (`commission_agent`),
  ADD KEY `transactions_transfer_parent_id_index` (`transfer_parent_id`),
  ADD KEY `transactions_types_of_service_id_index` (`types_of_service_id`),
  ADD KEY `transactions_packing_charge_type_index` (`packing_charge_type`),
  ADD KEY `transactions_recur_parent_id_index` (`recur_parent_id`),
  ADD KEY `transactions_selling_price_group_id_index` (`selling_price_group_id`),
  ADD KEY `transactions_delivery_date_index` (`delivery_date`),
  ADD KEY `transactions_delivery_person_index` (`delivery_person`),
  ADD KEY `transactions_biz_type_status_date_index` (`business_id`,`type`,`status`,`transaction_date`),
  ADD KEY `transactions_shipping_date_index` (`shipping_date`);

--
-- Indexes for table `transaction_payments`
--
ALTER TABLE `transaction_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_payments_transaction_id_foreign` (`transaction_id`),
  ADD KEY `transaction_payments_created_by_index` (`created_by`),
  ADD KEY `transaction_payments_parent_id_index` (`parent_id`),
  ADD KEY `transaction_payments_payment_type_index` (`payment_type`);

--
-- Indexes for table `transaction_sell_lines`
--
ALTER TABLE `transaction_sell_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_sell_lines_transaction_id_foreign` (`transaction_id`),
  ADD KEY `transaction_sell_lines_product_id_foreign` (`product_id`),
  ADD KEY `transaction_sell_lines_variation_id_foreign` (`variation_id`),
  ADD KEY `transaction_sell_lines_tax_id_foreign` (`tax_id`),
  ADD KEY `transaction_sell_lines_children_type_index` (`children_type`),
  ADD KEY `transaction_sell_lines_parent_sell_line_id_index` (`parent_sell_line_id`),
  ADD KEY `transaction_sell_lines_line_discount_type_index` (`line_discount_type`),
  ADD KEY `transaction_sell_lines_discount_id_index` (`discount_id`),
  ADD KEY `transaction_sell_lines_lot_no_line_id_index` (`lot_no_line_id`),
  ADD KEY `transaction_sell_lines_sub_unit_id_index` (`sub_unit_id`);

--
-- Indexes for table `transaction_sell_lines_purchase_lines`
--
ALTER TABLE `transaction_sell_lines_purchase_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sell_line_id` (`sell_line_id`),
  ADD KEY `stock_adjustment_line_id` (`stock_adjustment_line_id`),
  ADD KEY `purchase_line_id` (`purchase_line_id`);

--
-- Indexes for table `types_of_services`
--
ALTER TABLE `types_of_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `types_of_services_business_id_index` (`business_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `units_business_id_foreign` (`business_id`),
  ADD KEY `units_created_by_foreign` (`created_by`),
  ADD KEY `units_base_unit_id_index` (`base_unit_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_business_id_foreign` (`business_id`),
  ADD KEY `users_user_type_index` (`user_type`),
  ADD KEY `users_crm_contact_id_foreign` (`crm_contact_id`);

--
-- Indexes for table `user_contact_access`
--
ALTER TABLE `user_contact_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_contact_access_user_id_index` (`user_id`),
  ADD KEY `user_contact_access_contact_id_index` (`contact_id`);

--
-- Indexes for table `variations`
--
ALTER TABLE `variations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variations_product_id_foreign` (`product_id`),
  ADD KEY `variations_product_variation_id_foreign` (`product_variation_id`),
  ADD KEY `variations_name_index` (`name`),
  ADD KEY `variations_sub_sku_index` (`sub_sku`),
  ADD KEY `variations_variation_value_id_index` (`variation_value_id`);

--
-- Indexes for table `variation_group_prices`
--
ALTER TABLE `variation_group_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variation_group_prices_variation_id_foreign` (`variation_id`),
  ADD KEY `variation_group_prices_price_group_id_foreign` (`price_group_id`);

--
-- Indexes for table `variation_location_details`
--
ALTER TABLE `variation_location_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variation_location_details_location_id_foreign` (`location_id`),
  ADD KEY `variation_location_details_product_id_index` (`product_id`),
  ADD KEY `variation_location_details_product_variation_id_index` (`product_variation_id`),
  ADD KEY `variation_location_details_variation_id_index` (`variation_id`);

--
-- Indexes for table `variation_templates`
--
ALTER TABLE `variation_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variation_templates_business_id_foreign` (`business_id`);

--
-- Indexes for table `variation_value_templates`
--
ALTER TABLE `variation_value_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variation_value_templates_name_index` (`name`),
  ADD KEY `variation_value_templates_variation_template_id_index` (`variation_template_id`);

--
-- Indexes for table `warranties`
--
ALTER TABLE `warranties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranties_business_id_index` (`business_id`),
  ADD KEY `warranties_duration_type_index` (`duration_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_transactions`
--
ALTER TABLE `account_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `ai_assistance_settings`
--
ALTER TABLE `ai_assistance_settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barcodes`
--
ALTER TABLE `barcodes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `business`
--
ALTER TABLE `business`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business_locations`
--
ALTER TABLE `business_locations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cash_denominations`
--
ALTER TABLE `cash_denominations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_registers`
--
ALTER TABLE `cash_registers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cash_register_transactions`
--
ALTER TABLE `cash_register_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `crm_call_logs`
--
ALTER TABLE `crm_call_logs`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_lead_users`
--
ALTER TABLE `crm_lead_users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crm_schedules`
--
ALTER TABLE `crm_schedules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `customer_groups`
--
ALTER TABLE `customer_groups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dashboard_configurations`
--
ALTER TABLE `dashboard_configurations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `document_and_notes`
--
ALTER TABLE `document_and_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecommerce_settings`
--
ALTER TABLE `ecommerce_settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ecom_api_settings`
--
ALTER TABLE `ecom_api_settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_banners`
--
ALTER TABLE `ecom_banners`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ecom_carts`
--
ALTER TABLE `ecom_carts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `ecom_cart_items`
--
ALTER TABLE `ecom_cart_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ecom_collections`
--
ALTER TABLE `ecom_collections`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ecom_collection_products`
--
ALTER TABLE `ecom_collection_products`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_coupons`
--
ALTER TABLE `ecom_coupons`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_coupon_usages`
--
ALTER TABLE `ecom_coupon_usages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_customer_addresses`
--
ALTER TABLE `ecom_customer_addresses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ecom_customer_licenses`
--
ALTER TABLE `ecom_customer_licenses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_order_meta`
--
ALTER TABLE `ecom_order_meta`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ecom_reviews`
--
ALTER TABLE `ecom_reviews`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_shipping_rules`
--
ALTER TABLE `ecom_shipping_rules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ecom_tax_rules`
--
ALTER TABLE `ecom_tax_rules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_wishlists`
--
ALTER TABLE `ecom_wishlists`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_attendances`
--
ALTER TABLE `employee_attendances`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `employee_attendance_breaks`
--
ALTER TABLE `employee_attendance_breaks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_layouts`
--
ALTER TABLE `invoice_layouts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoice_schemes`
--
ALTER TABLE `invoice_schemes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=320;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `printers`
--
ALTER TABLE `printers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `product_racks`
--
ALTER TABLE `product_racks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variations`
--
ALTER TABLE `product_variations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `purchase_lines`
--
ALTER TABLE `purchase_lines`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reference_counts`
--
ALTER TABLE `reference_counts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `res_tables`
--
ALTER TABLE `res_tables`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `selling_price_groups`
--
ALTER TABLE `selling_price_groups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stock_adjustment_lines`
--
ALTER TABLE `stock_adjustment_lines`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system`
--
ALTER TABLE `system`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tax_rates`
--
ALTER TABLE `tax_rates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `transaction_payments`
--
ALTER TABLE `transaction_payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `transaction_sell_lines`
--
ALTER TABLE `transaction_sell_lines`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `transaction_sell_lines_purchase_lines`
--
ALTER TABLE `transaction_sell_lines_purchase_lines`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `types_of_services`
--
ALTER TABLE `types_of_services`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_contact_access`
--
ALTER TABLE `user_contact_access`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `variations`
--
ALTER TABLE `variations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `variation_group_prices`
--
ALTER TABLE `variation_group_prices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `variation_location_details`
--
ALTER TABLE `variation_location_details`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `variation_templates`
--
ALTER TABLE `variation_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `variation_value_templates`
--
ALTER TABLE `variation_value_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warranties`
--
ALTER TABLE `warranties`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_assistance_settings`
--
ALTER TABLE `ai_assistance_settings`
  ADD CONSTRAINT `ai_assistance_settings_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `barcodes`
--
ALTER TABLE `barcodes`
  ADD CONSTRAINT `barcodes_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brands_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business`
--
ALTER TABLE `business`
  ADD CONSTRAINT `business_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `business_default_sales_tax_foreign` FOREIGN KEY (`default_sales_tax`) REFERENCES `tax_rates` (`id`),
  ADD CONSTRAINT `business_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_locations`
--
ALTER TABLE `business_locations`
  ADD CONSTRAINT `business_locations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `business_locations_invoice_layout_id_foreign` FOREIGN KEY (`invoice_layout_id`) REFERENCES `invoice_layouts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `business_locations_invoice_scheme_id_foreign` FOREIGN KEY (`invoice_scheme_id`) REFERENCES `invoice_schemes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cash_registers`
--
ALTER TABLE `cash_registers`
  ADD CONSTRAINT `cash_registers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cash_registers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cash_register_transactions`
--
ALTER TABLE `cash_register_transactions`
  ADD CONSTRAINT `cash_register_transactions_cash_register_id_foreign` FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cash_register_transactions_transaction_payment_id_foreign` FOREIGN KEY (`transaction_payment_id`) REFERENCES `transaction_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contacts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crm_call_logs`
--
ALTER TABLE `crm_call_logs`
  ADD CONSTRAINT `crm_call_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `crm_call_logs_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crm_lead_users`
--
ALTER TABLE `crm_lead_users`
  ADD CONSTRAINT `crm_lead_users_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `crm_lead_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crm_schedules`
--
ALTER TABLE `crm_schedules`
  ADD CONSTRAINT `crm_schedules_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `crm_schedules_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD CONSTRAINT `customer_groups_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dashboard_configurations`
--
ALTER TABLE `dashboard_configurations`
  ADD CONSTRAINT `dashboard_configurations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD CONSTRAINT `expense_categories_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_sub_taxes`
--
ALTER TABLE `group_sub_taxes`
  ADD CONSTRAINT `group_sub_taxes_group_tax_id_foreign` FOREIGN KEY (`group_tax_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_sub_taxes_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_layouts`
--
ALTER TABLE `invoice_layouts`
  ADD CONSTRAINT `invoice_layouts_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_schemes`
--
ALTER TABLE `invoice_schemes`
  ADD CONSTRAINT `invoice_schemes_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `printers`
--
ALTER TABLE `printers`
  ADD CONSTRAINT `printers_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_tax_foreign` FOREIGN KEY (`tax`) REFERENCES `tax_rates` (`id`),
  ADD CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variations`
--
ALTER TABLE `product_variations`
  ADD CONSTRAINT `product_variations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_lines`
--
ALTER TABLE `purchase_lines`
  ADD CONSTRAINT `purchase_lines_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_lines_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_lines_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_lines_variation_id_foreign` FOREIGN KEY (`variation_id`) REFERENCES `variations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `res_product_modifier_sets`
--
ALTER TABLE `res_product_modifier_sets`
  ADD CONSTRAINT `res_product_modifier_sets_modifier_set_id_foreign` FOREIGN KEY (`modifier_set_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `res_tables`
--
ALTER TABLE `res_tables`
  ADD CONSTRAINT `res_tables_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `selling_price_groups`
--
ALTER TABLE `selling_price_groups`
  ADD CONSTRAINT `selling_price_groups_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustment_lines`
--
ALTER TABLE `stock_adjustment_lines`
  ADD CONSTRAINT `stock_adjustment_lines_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_lines_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_lines_variation_id_foreign` FOREIGN KEY (`variation_id`) REFERENCES `variations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tax_rates`
--
ALTER TABLE `tax_rates`
  ADD CONSTRAINT `tax_rates_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tax_rates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_expense_for_foreign` FOREIGN KEY (`expense_for`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `business_locations` (`id`),
  ADD CONSTRAINT `transactions_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_payments`
--
ALTER TABLE `transaction_payments`
  ADD CONSTRAINT `transaction_payments_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_sell_lines`
--
ALTER TABLE `transaction_sell_lines`
  ADD CONSTRAINT `transaction_sell_lines_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_sell_lines_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `tax_rates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_sell_lines_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_sell_lines_variation_id_foreign` FOREIGN KEY (`variation_id`) REFERENCES `variations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `units_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_crm_contact_id_foreign` FOREIGN KEY (`crm_contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variations`
--
ALTER TABLE `variations`
  ADD CONSTRAINT `variations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `variations_product_variation_id_foreign` FOREIGN KEY (`product_variation_id`) REFERENCES `product_variations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variation_group_prices`
--
ALTER TABLE `variation_group_prices`
  ADD CONSTRAINT `variation_group_prices_price_group_id_foreign` FOREIGN KEY (`price_group_id`) REFERENCES `selling_price_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `variation_group_prices_variation_id_foreign` FOREIGN KEY (`variation_id`) REFERENCES `variations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variation_location_details`
--
ALTER TABLE `variation_location_details`
  ADD CONSTRAINT `variation_location_details_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `business_locations` (`id`),
  ADD CONSTRAINT `variation_location_details_variation_id_foreign` FOREIGN KEY (`variation_id`) REFERENCES `variations` (`id`);

--
-- Constraints for table `variation_templates`
--
ALTER TABLE `variation_templates`
  ADD CONSTRAINT `variation_templates_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variation_value_templates`
--
ALTER TABLE `variation_value_templates`
  ADD CONSTRAINT `variation_value_templates_variation_template_id_foreign` FOREIGN KEY (`variation_template_id`) REFERENCES `variation_templates` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
