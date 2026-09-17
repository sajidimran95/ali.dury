-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 17, 2026 at 08:38 PM
-- Server version: 8.4.11-0ubuntu0.26.04.1
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `japspos_imran`
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
(1, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-01 07:24:13', '2026-09-01 07:24:13'),
(2, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-01 12:05:35', '2026-09-01 12:05:35'),
(3, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-02 05:53:43', '2026-09-02 05:53:43'),
(4, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-02 07:32:21', '2026-09-02 07:32:21'),
(5, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-03 05:12:34', '2026-09-03 05:12:34'),
(6, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-07 06:44:12', '2026-09-07 06:44:12'),
(7, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-07 11:56:30', '2026-09-07 11:56:30'),
(8, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-08 08:57:59', '2026-09-08 08:57:59'),
(9, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-08 08:59:00', '2026-09-08 08:59:00'),
(10, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 04:19:19', '2026-09-15 04:19:19'),
(11, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 04:20:21', '2026-09-15 04:20:21'),
(12, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 05:06:57', '2026-09-15 05:06:57'),
(13, 'default', 'logout', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 05:15:02', '2026-09-15 05:15:02'),
(14, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 05:15:21', '2026-09-15 05:15:21'),
(15, 'default', 'added', 7, 'App\\User', NULL, 1, 1, 'App\\User', '{\"name\":\"mr Sales \"}', NULL, '2026-09-15 06:30:08', '2026-09-15 06:30:09'),
(16, 'default', 'login', 7, 'App\\User', NULL, 1, 7, 'App\\User', '[]', NULL, '2026-09-15 06:30:53', '2026-09-15 06:30:53'),
(17, 'default', 'added', 2, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 07:08:02', '2026-09-15 07:08:02'),
(18, 'default', 'added', 3, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 07:08:36', '2026-09-15 07:08:36'),
(19, 'default', 'logout', 7, 'App\\User', NULL, 1, 7, 'App\\User', '[]', NULL, '2026-09-15 07:54:20', '2026-09-15 07:54:20'),
(20, 'default', 'edited', 3, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-15 07:56:41', '2026-09-15 07:56:41'),
(21, 'default', 'login', 7, 'App\\User', NULL, 1, 7, 'App\\User', '[]', NULL, '2026-09-17 03:50:17', '2026-09-17 03:50:17'),
(22, 'default', 'logout', 7, 'App\\User', NULL, 1, 7, 'App\\User', '[]', NULL, '2026-09-17 03:50:26', '2026-09-17 03:50:26'),
(23, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-17 03:52:21', '2026-09-17 03:52:21'),
(24, 'default', 'login', 1, 'App\\User', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-17 09:34:31', '2026-09-17 09:34:31'),
(25, 'default', 'edited', 4, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-17 09:36:27', '2026-09-17 09:36:27'),
(26, 'default', 'edited', 4, 'App\\Contact', NULL, 1, 1, 'App\\User', '[]', NULL, '2026-09-17 09:37:28', '2026-09-17 09:37:28');

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
(1, 1, 1, NULL, 'gpt-4o-mini', '2026-08-26 09:18:20', '2026-08-26 09:18:20');

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
(1, 1, 'RAW', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(2, 1, 'Zig-Zag', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(3, 1, 'Clipper', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(4, 1, 'GRAV Labs', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(5, 1, 'Puffco', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(6, 1, 'Lost Mary', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(7, 1, 'RAZ Vapes', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(8, 1, 'HQD', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(9, 1, 'Storz & Bickel', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(10, 1, 'Kali Extrax', NULL, 1, 1, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06');

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
(1, 'Greencloud Wholesale', 124, '2020-06-01', NULL, 'Tax ID', NULL, NULL, NULL, NULL, NULL, NULL, 5, 25.00, 1, 'America/Chicago', 1, 'fifo', 0.00, 'includes', '1787677805_logo-transparent-png.png', NULL, 0, 'add_expiry', 'keep_selling', 0, 1, 0, NULL, 1.000, 30, 30, '{\"pos\":{\"express_checkout\":\"shift+e\",\"pay_n_ckeckout\":\"shift+p\",\"draft\":\"shift+d\",\"cancel\":\"shift+c\",\"recent_product_quantity\":\"f2\",\"weighing_scale\":null,\"edit_discount\":\"shift+i\",\"edit_order_tax\":\"shift+t\",\"add_payment_row\":\"shift+r\",\"finalize_payment\":\"shift+f\",\"add_new_product\":\"f4\"}}', '{\"amount_rounding_method\":null,\"allow_overselling\":\"1\",\"enable_sales_order\":\"1\",\"cmmsn_calculation_type\":\"invoice_value\",\"razor_pay_key_id\":null,\"razor_pay_key_secret\":null,\"stripe_public_key\":null,\"stripe_secret_key\":null,\"disable_pay_checkout\":\"1\",\"disable_express_checkout\":\"1\",\"enable_transaction_date\":\"1\",\"is_service_staff_required\":\"1\",\"enable_pwa\":1,\"pwa_app_name\":\"Japs POS\",\"pwa_short_name\":\"POS\",\"pwa_description\":\"Point of Sale system\",\"pwa_theme_color\":\"#0f0f0f\",\"pwa_background_color\":\"#0f0f0f\",\"display_screen_heading\":null,\"cash_denominations\":null,\"enable_cash_denomination_on\":\"pos_screen\",\"disable_draft\":0,\"hide_product_suggestion\":0,\"hide_recent_trans\":0,\"disable_discount\":0,\"disable_order_tax\":0,\"is_pos_subtotal_editable\":0,\"disable_suspend\":0,\"inline_service_staff\":0,\"disable_credit_sale_button\":0,\"enable_weighing_scale\":0,\"show_invoice_scheme\":0,\"show_invoice_layout\":0,\"print_on_suspend\":0,\"show_pricing_on_product_sugesstion\":0,\"pwa_icon\":null}', 1, NULL, 'ltr', '{\"label_prefix\":null,\"product_sku_length\":\"5\",\"qty_length\":\"4\",\"qty_length_decimal\":\"3\"}', 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, NULL, 1, 1, 'before', '[\"purchases\",\"add_sale\",\"pos_sale\",\"stock_transfers\",\"stock_adjustment\",\"expenses\",\"account\"]', 'm/d/Y', '24', 2, 2, '{\"purchase\":\"PO\",\"purchase_return\":null,\"purchase_requisition\":null,\"purchase_order\":null,\"stock_transfer\":\"ST\",\"stock_adjustment\":\"SA\",\"sell_return\":\"CN\",\"expense\":\"EP\",\"contacts\":\"CO\",\"purchase_payment\":\"PP\",\"sell_payment\":\"SP\",\"expense_payment\":null,\"business_location\":\"BL\",\"username\":null,\"subscription\":null,\"draft\":null,\"sales_order\":null}', 'green', NULL, 0, NULL, 1.0000, 1.0000, NULL, 1.0000, 1.0000, NULL, NULL, NULL, 'year', '{\"mail_driver\":\"smtp\",\"mail_host\":null,\"mail_port\":null,\"mail_username\":null,\"mail_password\":null,\"mail_encryption\":null,\"mail_from_address\":null,\"mail_from_name\":null}', '{\"sms_service\":\"other\",\"nexmo_key\":null,\"nexmo_secret\":null,\"nexmo_from\":null,\"twilio_sid\":null,\"twilio_token\":null,\"twilio_from\":null,\"url\":null,\"send_to_param_name\":\"to\",\"send_to_param_type\":\"string\",\"msg_param_name\":\"text\",\"request_method\":\"post\",\"data_parameter_type\":\"form-data\",\"header_1\":null,\"header_val_1\":null,\"header_2\":null,\"header_val_2\":null,\"header_3\":null,\"header_val_3\":null,\"param_1\":null,\"param_val_1\":null,\"param_2\":null,\"param_val_2\":null,\"param_3\":null,\"param_val_3\":null,\"param_4\":null,\"param_val_4\":null,\"param_5\":null,\"param_val_5\":null,\"param_6\":null,\"param_val_6\":null,\"param_7\":null,\"param_val_7\":null,\"param_8\":null,\"param_val_8\":null,\"param_9\":null,\"param_val_9\":null,\"param_10\":null,\"param_val_10\":null}', '{\"payments\":{\"custom_pay_1\":null,\"custom_pay_2\":null,\"custom_pay_3\":null,\"custom_pay_4\":null,\"custom_pay_5\":null,\"custom_pay_6\":null,\"custom_pay_7\":null},\"contact\":{\"custom_field_1\":\"MSA Class Of Trade\",\"custom_field_2\":\"MSA Store #\",\"custom_field_3\":\"MSA Cash And Carry (Y\\/N)\",\"custom_field_4\":null,\"custom_field_5\":null,\"custom_field_6\":null,\"custom_field_7\":null,\"custom_field_8\":null,\"custom_field_9\":null,\"custom_field_10\":null},\"product\":{\"custom_field_1\":\"MSA Items Per Selling Unit\",\"custom_field_2\":\"MSA Category Code\",\"custom_field_3\":\"MSA Promo Code\",\"custom_field_4\":\"MSA Promo Description\",\"custom_field_5\":null,\"custom_field_6\":null,\"custom_field_7\":null,\"custom_field_8\":null,\"custom_field_9\":null,\"custom_field_10\":null,\"custom_field_11\":null,\"custom_field_12\":null,\"custom_field_13\":null,\"custom_field_14\":null,\"custom_field_15\":null,\"custom_field_16\":null,\"custom_field_17\":null,\"custom_field_18\":null,\"custom_field_19\":null,\"custom_field_20\":null},\"product_cf_details\":{\"1\":{\"type\":\"dropdown\",\"dropdown_options\":\"1\\n4\\n5\\n10\\n20\\n25\\n40\\n60\\n100\\n200\\n250\"},\"2\":{\"type\":\"dropdown\",\"dropdown_options\":\"003231\\n003211\\n003251\\n003252\\n003292\\n003221\\n003241\\n003261\\n003263\\n003213\\n003212\\n003214\\n003215\\n003227\"},\"3\":{\"type\":\"text\",\"dropdown_options\":null},\"4\":{\"type\":\"text\",\"dropdown_options\":null},\"5\":{\"type\":null,\"dropdown_options\":null},\"6\":{\"type\":null,\"dropdown_options\":null},\"7\":{\"type\":null,\"dropdown_options\":null},\"8\":{\"type\":null,\"dropdown_options\":null},\"9\":{\"type\":null,\"dropdown_options\":null},\"10\":{\"type\":null,\"dropdown_options\":null},\"11\":{\"type\":null,\"dropdown_options\":null},\"12\":{\"type\":null,\"dropdown_options\":null},\"13\":{\"type\":null,\"dropdown_options\":null},\"14\":{\"type\":null,\"dropdown_options\":null},\"15\":{\"type\":null,\"dropdown_options\":null},\"16\":{\"type\":null,\"dropdown_options\":null},\"17\":{\"type\":null,\"dropdown_options\":null},\"18\":{\"type\":null,\"dropdown_options\":null},\"19\":{\"type\":null,\"dropdown_options\":null},\"20\":{\"type\":null,\"dropdown_options\":null}},\"location\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"user\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"purchase\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"purchase_shipping\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null,\"custom_field_5\":null},\"sell\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null},\"shipping\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null,\"custom_field_5\":null},\"types_of_service\":{\"custom_field_1\":null,\"custom_field_2\":null,\"custom_field_3\":null,\"custom_field_4\":null,\"custom_field_5\":null,\"custom_field_6\":null},\"contact_cf_details\":{\"1\":{\"type\":\"dropdown\",\"dropdown_options\":\"RETAIL\\nDISTRIBUTOR\\nOTHER\"},\"2\":{\"type\":\"text\",\"dropdown_options\":null},\"3\":{\"type\":\"dropdown\",\"dropdown_options\":\"Y\\nN\"}}}', '{\"enable_weight\":\"1\",\"default_credit_limit\":null,\"enable_purchase_order\":\"1\",\"enable_purchase_requisition\":\"1\",\"default_datatable_page_entries\":\"25\",\"msa_default_tax_id\":5,\"msa_tobacco_category_id\":1,\"msa_retail_price_group_id\":1,\"msa_wholesale_price_group_id\":2,\"msa_retail_customer_group_id\":1,\"msa_wholesale_customer_group_id\":2,\"msa_state_tax\":\"TN\",\"msa_distributor_id\":\"17033752\",\"msa_contact_first\":\"greencloud\",\"msa_contact_email\":\"greencloudwholesale@gmail.com\"}', NULL, 1, '2026-06-04 16:29:53', '2026-08-26 19:47:22');

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
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `clock_radius_meters` int UNSIGNED DEFAULT NULL,
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

INSERT INTO `business_locations` (`id`, `business_id`, `location_id`, `name`, `landmark`, `country`, `state`, `city`, `zip_code`, `latitude`, `longitude`, `clock_radius_meters`, `invoice_scheme_id`, `sale_invoice_scheme_id`, `invoice_layout_id`, `sale_invoice_layout_id`, `selling_price_group_id`, `print_receipt_on_invoice`, `receipt_printer_type`, `printer_id`, `mobile`, `alternate_number`, `email`, `website`, `featured_products`, `is_active`, `default_payment_accounts`, `custom_field1`, `custom_field2`, `custom_field3`, `custom_field4`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'BL0001', 'Japs POS', NULL, 'USA', 'Wisconsin', 'New York', '53213', NULL, NULL, 200, 1, 1, 1, 1, NULL, 1, 'browser', NULL, '(414) 252-0586', NULL, NULL, 'greencloudwholesale.com', NULL, 1, '{\"cash\":{\"is_enabled\":\"1\",\"account\":null},\"card\":{\"is_enabled\":\"1\",\"account\":null},\"cheque\":{\"is_enabled\":\"1\",\"account\":null},\"bank_transfer\":{\"is_enabled\":\"1\",\"account\":null},\"other\":{\"is_enabled\":\"1\",\"account\":null},\"custom_pay_1\":{\"account\":null},\"custom_pay_2\":{\"account\":null},\"custom_pay_3\":{\"account\":null},\"custom_pay_4\":{\"account\":null},\"custom_pay_5\":{\"account\":null},\"custom_pay_6\":{\"account\":null},\"custom_pay_7\":{\"account\":null}}', NULL, NULL, NULL, NULL, NULL, '2026-06-04 16:29:55', '2026-08-26 19:47:22');

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
(1, 1, 1, 1, 'open', NULL, 0.0000, 0, 0, NULL, NULL, '2026-09-02 06:22:00', '2026-09-02 06:22:13');

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
(1, 'Tobacco', 1, 'TOB', 0, 1, 'product', 1, NULL, NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(2, 'Cigarettes', 1, 'CIG', 1, 1, 'product', 1, 'MSA category code 003231', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(3, 'Moist Spit Tobacco', 1, 'MST', 1, 1, 'product', 1, 'MSA category code 003211', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(4, 'Cigars', 1, 'CGR', 1, 1, 'product', 1, 'MSA category code 003251', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(5, 'Little Filtered Cigars', 1, 'LFC', 1, 1, 'product', 1, 'MSA category code 003252', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(6, 'E-Cigarettes', 1, 'ECG', 1, 1, 'product', 1, 'MSA category code 003292', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(7, 'RYO Tobacco', 1, 'RYO', 1, 1, 'product', 1, 'MSA category code 003221', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(8, 'Pipe Tobacco', 1, 'PIP', 1, 1, 'product', 1, 'MSA category code 003241', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(9, 'Lighters', 1, 'LGT', 1, 1, 'product', 1, 'MSA category code 003263', NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(10, 'New Arrivals', 1, NULL, 0, 1, 'product', 1, NULL, NULL, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(11, 'Lifestyle & Specialty', 1, NULL, 0, 1, 'product', 1, NULL, NULL, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(12, 'Smoking Accessories & Tools', 1, NULL, 0, 1, 'product', 1, NULL, NULL, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(13, 'Smoking Devices & Supplies', 1, NULL, 0, 1, 'product', 1, NULL, NULL, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(14, 'THC/Delta Products', 1, NULL, 0, 1, 'product', 1, NULL, NULL, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(15, 'Vape & Nicotine', 1, NULL, 0, 1, 'product', 1, NULL, NULL, NULL, '2026-09-15 15:01:06', '2026-09-15 15:01:06');

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
  `allow_customer_app` tinyint(1) NOT NULL DEFAULT '0',
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

INSERT INTO `contacts` (`id`, `business_id`, `type`, `contact_type`, `land_mark`, `street_name`, `building_number`, `additional_number`, `supplier_business_name`, `name`, `prefix`, `first_name`, `middle_name`, `last_name`, `email`, `password`, `allow_customer_app`, `ecom_first_name`, `ecom_last_name`, `email_verified_at`, `contact_id`, `contact_status`, `crm_source`, `crm_life_stage`, `tax_number`, `city`, `state`, `country`, `address_line_1`, `address_line_2`, `zip_code`, `dob`, `mobile`, `landline`, `alternate_number`, `pay_term_number`, `pay_term_type`, `credit_limit`, `created_by`, `balance`, `total_rp`, `total_rp_used`, `total_rp_expired`, `is_default`, `shipping_address`, `shipping_custom_field_details`, `is_export`, `export_custom_field_1`, `export_custom_field_2`, `export_custom_field_3`, `export_custom_field_4`, `export_custom_field_5`, `export_custom_field_6`, `position`, `customer_group_id`, `custom_field1`, `custom_field2`, `custom_field3`, `custom_field4`, `custom_field5`, `custom_field6`, `custom_field7`, `custom_field8`, `custom_field9`, `custom_field10`, `deleted_at`, `created_at`, `updated_at`, `remember_token`) VALUES
(1, 1, 'customer', NULL, NULL, NULL, NULL, NULL, NULL, 'Walk-In Customer', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 'CO0001', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 1, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'RETAIL', NULL, 'Y', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20', NULL),
(2, 1, 'supplier', 'business', NULL, NULL, NULL, NULL, 'Meredith Gutierrez', 'Consectetur dolor r Bruno Patricia Mooney Ballard', 'Consectetur dolor r', 'Bruno', 'Patricia Mooney', 'Ballard', 'zogewo@mailinator.com', NULL, 0, NULL, NULL, NULL, 'Fugiat nisi eos ab', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Possimus aut aliqua', 'Maiores quidem ipsa', '946', NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 07:08:02', '2026-09-15 07:08:02', NULL),
(3, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'Test Customer', 'Mr test Customer', 'Mr', 'test', 'Customer', NULL, 'testcustomer@gmail.com', '$2y$10$6PUCF8J4LvUUNiDxjtlz7ek.P4i/iKrzfgaOGGCEtYvbIgcka3YXC', 1, NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '+123456789', NULL, '925', NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 07:08:36', '2026-09-15 07:56:41', NULL),
(4, 1, 'customer', 'business', NULL, NULL, NULL, NULL, 'Whole Sale Business', 'Whole Saller', NULL, 'Whole Saller', NULL, NULL, 'wholesale@japspos.com', '$2y$10$uqC2FN.vXTVpiwaPK/iBi.TyXXpm5..Vb6q3Js1tfg2gPw/MHUpDi', 1, 'Whole', 'Saler', NULL, NULL, 'active', NULL, NULL, NULL, 'Dhaka', 'CO', 'United States', 'Badda', NULL, '1212', NULL, '12345698563', NULL, NULL, NULL, NULL, NULL, 1, 0.0000, 0, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'wholesale@japspos.com', NULL, NULL, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, '2026-09-17 10:15:44', '2026-09-17 09:37:28', NULL);

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
  `inventory_slug` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all_inventory',
  `price_calculation_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `selling_price_group_id` int DEFAULT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_groups`
--

INSERT INTO `customer_groups` (`id`, `business_id`, `name`, `amount`, `inventory_slug`, `price_calculation_type`, `selling_price_group_id`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Retailer', 0.00, 'all_inventory', 'selling_price_group', 1, 1, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(2, 1, 'Wholesale', 0.00, 'all_inventory', 'selling_price_group', 2, 1, '2026-08-26 19:47:20', '2026-08-26 19:47:20');

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
-- Table structure for table `delivery_areas`
--

CREATE TABLE `delivery_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `state` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_route_orders`
--

CREATE TABLE `delivery_route_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `delivery_route_id` bigint UNSIGNED NOT NULL,
  `transaction_id` int UNSIGNED NOT NULL,
  `stop_no` int UNSIGNED NOT NULL DEFAULT '1',
  `distance_from_previous` int UNSIGNED NOT NULL DEFAULT '0',
  `estimated_duration_from_previous` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `arrived_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `fail_reason` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ship_to_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_state` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_zip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
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
-- Table structure for table `driver_delivery_routes`
--

CREATE TABLE `driver_delivery_routes` (
  `id` bigint UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `delivery_user_id` int UNSIGNED NOT NULL,
  `business_location_id` int UNSIGNED DEFAULT NULL,
  `route_date` date NOT NULL,
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned',
  `total_orders` int UNSIGNED NOT NULL DEFAULT '0',
  `total_distance` int UNSIGNED NOT NULL DEFAULT '0',
  `estimated_duration` int UNSIGNED NOT NULL DEFAULT '0',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `start_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_latitude` decimal(10,7) DEFAULT NULL,
  `start_longitude` decimal(10,7) DEFAULT NULL,
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
  `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ecommerce_settings`
--

INSERT INTO `ecommerce_settings` (`id`, `business_id`, `is_enabled`, `store_name`, `store_logo`, `location_id`, `enable_cod`, `enable_stripe`, `enable_paypal`, `stripe_key`, `stripe_secret`, `paypal_client_id`, `paypal_secret`, `default_country`, `seo_title`, `seo_description`, `contact_email`, `contact_phone`, `extra`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Greencloud Wholesale', NULL, 1, 1, 0, 0, NULL, NULL, NULL, NULL, 'United States', 'Wisconsin\'s Premier Wholesale Distributor', 'Smoke, vape, hemp & accessories for licensed retailers.', NULL, NULL, NULL, '2026-08-26 09:18:20', '2026-09-15 15:01:06');

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
(1, 1, NULL, 'ShspKo1cAZXpT0GSbrngMXjNN9XAoZml4iUBOcyA', NULL, '2026-09-15 14:50:24', '2026-09-15 14:50:24'),
(2, 1, NULL, 'lwKv3uAh6TZRRfPwc6o4pFRbaF7eD7o2o8MWPImP', NULL, '2026-09-15 15:01:24', '2026-09-15 15:01:24'),
(3, 1, NULL, '2cayVWyFVCjhNS1urkcffUdW1s1cUiHcpCoAkgS6', NULL, '2026-09-15 08:54:27', '2026-09-15 08:54:27'),
(4, 1, NULL, '0wYDykq8MauWLFSq9RpSptJq0AZNOfVV8dCpFUtF', NULL, '2026-09-15 20:38:40', '2026-09-15 20:38:40'),
(5, 1, NULL, 'm5NkvgbBwffOb2JTFGg3VzBF6KE0J02sEBTOtotW', NULL, '2026-09-15 20:39:08', '2026-09-15 20:39:08'),
(6, 1, NULL, 'c1aAtOISzFDwH5OfqLTs1kq2N4PaqVYQoimZnsM4', NULL, '2026-09-15 20:39:17', '2026-09-15 20:39:17'),
(7, 1, NULL, 'IGZ7XYnsO91815bWLaUPVqpnrFUQvGSCPNKN78Nd', NULL, '2026-09-17 14:15:45', '2026-09-17 14:15:45'),
(8, 1, NULL, '2zwXwnPxQSbl2tVkSkjYk1Jy2UMADGFF7BzDGP7S', NULL, '2026-09-17 14:30:28', '2026-09-17 14:30:28'),
(9, 1, 1882, 'TXzr4fpPlWngRnRwAeKOjAAqt6wgn8NjedES4xca', NULL, '2026-09-17 10:12:22', '2026-09-17 10:12:22'),
(10, 1, 4, 'eK1nJo2wP9EZ0Rvqou8STzc5ar5Ifk0VVzZJ3qKN', NULL, '2026-09-17 10:15:44', '2026-09-17 10:37:32'),
(11, 1, NULL, 'fWrZAamtLhADAQiiOnoojxawGMhjkKDguFQFMtgF', NULL, '2026-09-17 10:29:18', '2026-09-17 10:29:18'),
(13, 1, 3, 'ZR8Oz3lQM5dAX04t7Yw3u36eY0FfvIEOobDsoROq', NULL, '2026-09-17 10:30:17', '2026-09-17 10:30:17');

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
(3, 10, 1, 1, 1.0000, 18.0000, '2026-09-17 10:27:09', '2026-09-17 10:27:09'),
(4, 13, 1, 1, 2.0000, 10.0000, '2026-09-17 10:30:17', '2026-09-17 10:30:17'),
(5, 11, 1, 1, 2.0000, 18.0000, '2026-09-17 10:30:45', '2026-09-17 10:30:49');

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
(1, 1, 4, 'Whole', 'Saler', 'Whole Sale Business', 'Badda', NULL, 'Dhaka', 'CO', '1212', 'United States', '12345698563', 1, '2026-09-17 10:15:44', '2026-09-17 10:15:44');

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
-- Table structure for table `ecom_inquiries`
--

CREATE TABLE `ecom_inquiries` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contact',
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
  `timeline` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `billing_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `coupon_id` int UNSIGNED DEFAULT NULL,
  `payment_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_password_resets`
--

CREATE TABLE `ecom_password_resets` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `states` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ecom_shipping_rules`
--

INSERT INTO `ecom_shipping_rules` (`id`, `business_id`, `name`, `type`, `amount`, `min_order_amount`, `max_weight`, `states`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Standard Shipping', 'flat', 5.9900, NULL, NULL, NULL, 1, 1, '2026-09-15 04:20:12', '2026-09-15 04:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `ecom_stock_alerts`
--

CREATE TABLE `ecom_stock_alerts` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ecom_subscribers`
--

CREATE TABLE `ecom_subscribers` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `clock_latitude` decimal(10,7) DEFAULT NULL,
  `clock_longitude` decimal(10,7) DEFAULT NULL,
  `clock_source` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendances`
--

INSERT INTO `employee_attendances` (`id`, `business_id`, `user_id`, `clock_in_at`, `clock_out_at`, `status`, `notes`, `clock_latitude`, `clock_longitude`, `clock_source`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-08-06 16:14:57', '2026-08-06 16:16:26', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-06 20:14:57', '2026-08-06 20:16:26'),
(2, 1, 1, '2026-08-06 17:01:31', '2026-08-06 17:01:33', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-06 21:01:31', '2026-08-06 21:01:33'),
(3, 1, 1, '2026-08-06 16:19:41', '2026-08-14 10:41:41', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-06 20:19:41', '2026-08-14 14:41:41'),
(4, 1, 4, '2026-08-06 16:45:13', '2026-08-10 14:44:20', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-06 20:45:13', '2026-08-10 18:44:20'),
(5, 1, 5, '2026-08-07 13:29:55', '2026-08-08 13:56:53', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-07 17:29:55', '2026-08-08 17:56:53'),
(6, 1, 3, '2026-08-07 13:49:19', '2026-08-07 16:00:19', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-07 17:49:19', '2026-08-07 20:00:19'),
(7, 1, 3, '2026-08-08 11:24:57', '2026-08-08 14:59:42', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-08 15:24:57', '2026-08-08 18:59:42'),
(8, 1, 5, '2026-08-08 13:59:26', '2026-08-10 09:51:11', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-08 17:59:26', '2026-08-10 13:51:11'),
(9, 1, 5, '2026-08-10 09:51:12', '2026-08-22 10:51:27', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-10 13:51:12', '2026-08-22 14:51:27'),
(10, 1, 3, '2026-08-10 13:05:59', '2026-08-10 17:16:46', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-10 17:05:59', '2026-08-10 21:16:46'),
(11, 1, 4, '2026-08-10 14:44:21', '2026-08-12 12:14:34', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-10 18:44:21', '2026-08-12 16:14:34'),
(12, 1, 3, '2026-08-10 23:00:34', '2026-08-10 23:00:38', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-11 03:00:34', '2026-08-11 03:00:38'),
(13, 1, 3, '2026-08-11 11:19:42', '2026-08-11 16:45:48', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-11 15:19:42', '2026-08-11 20:45:48'),
(14, 1, 3, '2026-08-12 11:44:47', '2026-08-12 14:33:19', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-12 15:44:47', '2026-08-12 18:33:19'),
(15, 1, 4, '2026-08-12 12:14:54', '2026-08-17 16:14:33', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-12 16:14:54', '2026-08-17 20:14:33'),
(16, 1, 3, '2026-08-14 11:33:08', '2026-08-14 17:53:14', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-14 15:33:08', '2026-08-14 21:53:14'),
(17, 1, 3, '2026-08-15 12:00:09', '2026-08-15 16:18:52', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-15 16:00:09', '2026-08-15 20:18:52'),
(18, 1, 3, '2026-08-17 11:53:55', '2026-08-17 18:49:27', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-17 15:53:55', '2026-08-17 22:49:27'),
(19, 1, 4, '2026-08-17 16:14:33', '2026-08-21 12:58:30', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-17 20:14:33', '2026-08-21 16:58:30'),
(20, 1, 3, '2026-08-18 12:13:13', '2026-08-18 18:07:51', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-18 16:13:13', '2026-08-18 22:07:51'),
(21, 1, 6, '2026-08-18 14:25:51', '2026-08-19 11:02:56', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-18 18:25:51', '2026-08-19 15:02:56'),
(22, 1, 6, '2026-08-19 11:02:57', '2026-08-20 09:43:07', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-19 15:02:57', '2026-08-20 13:43:07'),
(23, 1, 3, '2026-08-19 12:25:48', '2026-08-19 18:20:39', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-19 16:25:48', '2026-08-19 22:20:39'),
(24, 1, 6, '2026-08-20 09:43:08', '2026-08-21 12:25:57', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-20 13:43:08', '2026-08-21 16:25:57'),
(25, 1, 3, '2026-08-20 11:37:27', '2026-08-21 11:06:45', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-20 15:37:27', '2026-08-21 15:06:45'),
(26, 1, 3, '2026-08-21 11:07:26', '2026-08-21 17:07:48', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-21 15:07:26', '2026-08-21 21:07:48'),
(27, 1, 6, '2026-08-21 12:25:58', '2026-08-23 13:27:08', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-21 16:25:58', '2026-08-23 17:27:08'),
(29, 1, 5, '2026-08-22 10:51:28', '2026-08-24 09:31:35', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-22 14:51:28', '2026-08-24 13:31:35'),
(30, 1, 3, '2026-08-22 13:16:33', '2026-08-22 17:18:57', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-22 17:16:33', '2026-08-22 21:18:57'),
(31, 1, 6, '2026-08-23 13:27:12', '2026-08-23 18:24:25', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-23 17:27:12', '2026-08-23 22:24:25'),
(32, 1, 5, '2026-08-24 09:31:36', '2026-08-24 17:31:40', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-24 13:31:36', '2026-08-24 21:31:40'),
(34, 1, 3, '2026-08-24 11:10:50', '2026-08-24 16:46:50', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-24 15:10:50', '2026-08-24 20:46:50'),
(36, 1, 6, '2026-08-25 10:11:27', '2026-08-26 11:01:55', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-25 14:11:27', '2026-08-26 15:01:55'),
(37, 1, 4, '2026-08-25 10:30:00', NULL, 'clocked_in', NULL, NULL, NULL, NULL, '2026-08-25 15:49:52', '2026-08-25 16:18:51'),
(38, 1, 3, '2026-08-25 11:59:03', '2026-08-26 10:40:51', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-25 15:59:03', '2026-08-26 14:40:51'),
(40, 1, 6, '2026-08-24 09:30:00', '2026-08-24 17:30:00', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-25 16:32:53', '2026-08-25 16:32:53'),
(41, 1, 5, '2026-08-25 09:30:00', '2026-08-25 17:30:08', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-25 16:55:24', '2026-08-25 21:30:08'),
(42, 1, 5, '2026-08-26 09:28:04', NULL, 'clocked_in', NULL, 43.0480624, -88.0138325, 'offsite', '2026-08-26 13:28:04', '2026-08-26 16:05:45'),
(43, 1, 5, '2026-08-26 09:28:04', '2026-08-26 12:04:33', 'clocked_out', NULL, 43.0480624, -88.0138325, 'offsite', '2026-08-26 13:28:04', '2026-08-26 16:04:33'),
(44, 1, 3, '2026-08-26 10:41:24', '2026-08-26 14:41:44', 'clocked_out', NULL, NULL, NULL, NULL, '2026-08-26 14:41:24', '2026-08-26 18:41:44'),
(45, 1, 1, '2026-09-07 12:58:42', '2026-09-07 17:56:59', 'clocked_out', NULL, 23.8654916, 90.4150996, 'offsite', '2026-09-07 06:58:42', '2026-09-07 11:56:59'),
(46, 1, 1, '2026-09-07 17:57:05', '2026-09-07 17:57:12', 'clocked_out', NULL, NULL, NULL, NULL, '2026-09-07 11:57:05', '2026-09-07 11:57:12');

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
(1, 7, '2026-08-08 13:10:59', '2026-08-08 13:54:50', NULL, '2026-08-08 17:10:59', '2026-08-08 17:54:50'),
(2, 13, '2026-08-11 13:31:23', '2026-08-11 14:32:58', NULL, '2026-08-11 17:31:23', '2026-08-11 18:32:58'),
(3, 14, '2026-08-12 13:04:51', '2026-08-12 13:53:19', NULL, '2026-08-12 17:04:51', '2026-08-12 17:53:19'),
(4, 16, '2026-08-14 12:59:45', '2026-08-14 12:59:45', NULL, '2026-08-14 16:59:45', '2026-08-14 16:59:45'),
(5, 16, '2026-08-14 12:59:52', '2026-08-14 13:55:20', NULL, '2026-08-14 16:59:52', '2026-08-14 17:55:20'),
(6, 17, '2026-08-15 13:48:58', '2026-08-15 14:40:21', NULL, '2026-08-15 17:48:58', '2026-08-15 18:40:21'),
(7, 18, '2026-08-17 12:54:02', '2026-08-17 13:29:25', NULL, '2026-08-17 16:54:02', '2026-08-17 17:29:25'),
(8, 20, '2026-08-18 13:23:14', '2026-08-18 14:23:27', NULL, '2026-08-18 17:23:14', '2026-08-18 18:23:27'),
(9, 23, '2026-08-19 14:04:16', '2026-08-19 14:38:02', NULL, '2026-08-19 18:04:16', '2026-08-19 18:38:02'),
(10, 25, '2026-08-20 13:25:40', '2026-08-20 14:12:47', NULL, '2026-08-20 17:25:41', '2026-08-20 18:12:47'),
(11, 26, '2026-08-21 13:07:05', '2026-08-21 14:07:19', NULL, '2026-08-21 17:07:05', '2026-08-21 18:07:19'),
(12, 27, '2026-08-21 15:51:54', '2026-08-23 13:27:08', NULL, '2026-08-21 19:51:54', '2026-08-23 17:27:08'),
(13, 34, '2026-08-24 12:44:51', '2026-08-24 13:44:40', NULL, '2026-08-24 16:44:51', '2026-08-24 17:44:40'),
(17, 41, '2026-08-25 11:29:00', '2026-08-25 12:12:00', NULL, '2026-08-25 16:55:24', '2026-08-25 16:55:24'),
(18, 38, '2026-08-25 13:23:07', '2026-08-25 14:14:18', NULL, '2026-08-25 17:23:07', '2026-08-25 18:14:18'),
(19, 42, '2026-08-26 11:30:49', '2026-08-26 12:05:45', NULL, '2026-08-26 15:30:49', '2026-08-26 16:05:45'),
(20, 44, '2026-08-26 14:02:01', '2026-08-26 14:41:39', NULL, '2026-08-26 18:02:01', '2026-08-26 18:41:39'),
(21, 45, '2026-09-07 12:58:46', '2026-09-07 12:58:50', NULL, '2026-09-07 06:58:46', '2026-09-07 06:58:50'),
(22, 46, '2026-09-07 17:57:07', '2026-09-07 17:57:10', NULL, '2026-09-07 11:57:07', '2026-09-07 11:57:10');

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
(1, 1, 'Default', 'blank', 'sequential', NULL, 1, 88, 10, 1, '2026-07-18 18:56:53', '2026-08-06 20:15:02');

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
(317, '2026_08_07_000001_add_attendance_edit_permission', 16),
(318, '2026_08_07_202300_add_pos_screen_layout_to_business_table', 16),
(319, '2026_08_13_000020_convert_ecommerce_sells_to_sales_orders', 16),
(320, '2026_08_18_193000_ensure_sales_representative_role_for_all_businesses', 16),
(321, '2026_08_25_200001_create_team_schedule_tables', 16),
(322, '2026_08_24_211200_add_expense_permissions_to_sales_representative_role', 17),
(323, '2026_08_25_010000_add_allow_customer_app_to_contacts_table', 17),
(324, '2026_08_31_190000_add_fulfillment_pipeline_fields', 18),
(325, '2026_09_01_030000_add_inventory_slug_to_customer_groups', 18),
(326, '2026_09_09_204900_create_sale_zero_sales_tables', 19),
(327, '2026_09_16_000100_sync_sale_zero_sale_reason_labels', 20),
(328, '2026_09_17_205200_create_delivery_person_role_and_user', 21),
(329, '2026_09_16_200000_add_delivery_management_module', 22),
(330, '2026_09_08_210000_create_pos_quick_layouts_table', 23),
(331, '2026_09_08_220000_add_sales_target_and_barcode_login_to_users', 23),
(332, '2026_09_08_230000_add_period_sales_targets_to_users', 23),
(333, '2026_09_09_211000_add_signature_to_transaction_payments_table', 23),
(334, '2026_09_10_203700_add_barcode_switch_id_to_users', 23),
(335, '2026_09_17_210000_create_ecom_storefront_tables', 23);

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
(80, 'App\\User', 3),
(80, 'App\\User', 4),
(80, 'App\\User', 5),
(80, 'App\\User', 6),
(80, 'App\\User', 7),
(82, 'App\\User', 8),
(106, 'App\\User', 8),
(107, 'App\\User', 8);

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
(1, 'App\\User', 3),
(1, 'App\\User', 4),
(1, 'App\\User', 5),
(1, 'App\\User', 6),
(3, 'App\\User', 7),
(4, 'App\\User', 8);

-- --------------------------------------------------------

--
-- Table structure for table `mws_app_branding`
--

CREATE TABLE `mws_app_branding` (
  `AbId` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `AbLockLogoPath` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `AbAdminLogoPath` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `AbFaviconPath` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `AbUpdatedAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 1, 'new_sale', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>Your invoice number is {invoice_number}<br />\r\n                    Total amount: {total_amount}<br />\r\n                    Paid amount: {received_amount}</p>\r\n\r\n                    <p>Thank you for shopping with us.</p>\r\n\r\n                    <p>{business_logo}</p>\r\n\r\n                    <p>&nbsp;</p>', 'Dear {contact_name}, Thank you for shopping with us. {business_name}', NULL, 'Thank you from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(2, 1, 'payment_received', '<p>Dear {contact_name},</p>\r\n\r\n                <p>We have received a payment of {received_amount}</p>\r\n\r\n                <p>{business_logo}</p>', 'Dear {contact_name}, We have received a payment of {received_amount}. {business_name}', NULL, 'Payment Received, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(3, 1, 'payment_reminder', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>This is to remind you that you have pending payment of {due_amount}. Kindly pay it as soon as possible.</p>\r\n\r\n                    <p>{business_logo}</p>', 'Dear {contact_name}, You have pending payment of {due_amount}. Kindly pay it as soon as possible. {business_name}', NULL, 'Payment Reminder, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(4, 1, 'new_booking', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>Your booking is confirmed</p>\r\n\r\n                    <p>Date: {start_time} to {end_time}</p>\r\n\r\n                    <p>Table: {table}</p>\r\n\r\n                    <p>Location: {location}</p>\r\n\r\n                    <p>{business_logo}</p>', 'Dear {contact_name}, Your booking is confirmed. Date: {start_time} to {end_time}, Table: {table}, Location: {location}', NULL, 'Booking Confirmed - {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(5, 1, 'new_order', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>We have a new order with reference number {order_ref_number}. Kindly process the products as soon as possible.</p>\r\n\r\n                    <p>{business_name}<br />\r\n                    {business_logo}</p>', 'Dear {contact_name}, We have a new order with reference number {order_ref_number}. Kindly process the products as soon as possible. {business_name}', NULL, 'New Order, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(6, 1, 'payment_paid', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>We have paid amount {paid_amount} again invoice number {order_ref_number}.<br />\r\n                    Kindly note it down.</p>\r\n\r\n                    <p>{business_name}<br />\r\n                    {business_logo}</p>', 'We have paid amount {paid_amount} again invoice number {order_ref_number}.\r\n                    Kindly note it down. {business_name}', NULL, 'Payment Paid, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(7, 1, 'items_received', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>We have received all items from invoice reference number {order_ref_number}. Thank you for processing it.</p>\r\n\r\n                    <p>{business_name}<br />\r\n                    {business_logo}</p>', 'We have received all items from invoice reference number {order_ref_number}. Thank you for processing it. {business_name}', NULL, 'Items received, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(8, 1, 'items_pending', '<p>Dear {contact_name},<br />\r\n                    This is to remind you that we have not yet received some items from invoice reference number {order_ref_number}. Please process it as soon as possible.</p>\r\n\r\n                    <p>{business_name}<br />\r\n                    {business_logo}</p>', 'This is to remind you that we have not yet received some items from invoice reference number {order_ref_number} . Please process it as soon as possible.{business_name}', NULL, 'Items Pending, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(9, 1, 'new_quotation', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>Your quotation number is {invoice_number}<br />\r\n                    Total amount: {total_amount}</p>\r\n\r\n                    <p>Thank you for shopping with us.</p>\r\n\r\n                    <p>{business_logo}</p>\r\n\r\n                    <p>&nbsp;</p>', 'Dear {contact_name}, Thank you for shopping with us. {business_name}', NULL, 'Thank you from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(10, 1, 'purchase_order', '<p>Dear {contact_name},</p>\r\n\r\n                    <p>We have a new purchase order with reference number {order_ref_number}. The respective invoice is attached here with.</p>\r\n\r\n                    <p>{business_logo}</p>', 'We have a new purchase order with reference number {order_ref_number}. {business_name}', NULL, 'New Purchase Order, from {business_name}', NULL, NULL, 0, 0, 0, '2026-08-26 19:47:20', '2026-08-26 19:47:20');

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
(92, 'selling_price_group.3', 'web', '2026-08-23 21:37:55', '2026-08-23 21:37:55'),
(93, 'selling_price_group.4', 'web', '2026-08-23 21:38:03', '2026-08-23 21:38:03'),
(94, 'selling_price_group.5', 'web', '2026-08-23 21:38:10', '2026-08-23 21:38:10'),
(95, 'selling_price_group.6', 'web', '2026-08-23 21:38:33', '2026-08-23 21:38:33'),
(96, 'selling_price_group.7', 'web', '2026-08-23 21:38:44', '2026-08-23 21:38:44'),
(97, 'selling_price_group.8', 'web', '2026-08-23 21:39:00', '2026-08-23 21:39:00'),
(98, 'attendance.edit', 'web', '2026-08-25 15:18:33', '2026-08-25 15:18:33'),
(99, 'so.view_own', 'web', '2026-08-25 15:18:34', '2026-08-25 15:18:34'),
(100, 'so.create', 'web', '2026-08-25 15:18:34', '2026-08-25 15:18:34'),
(101, 'so.update_own', 'web', '2026-08-25 15:18:34', '2026-08-25 15:18:34'),
(102, 'so.delete_own', 'web', '2026-08-25 15:18:34', '2026-08-25 15:18:34'),
(103, 'view_own_expense', 'web', '2026-08-26 19:49:43', '2026-08-26 19:49:43'),
(104, 'expense.add', 'web', '2026-08-26 19:49:43', '2026-08-26 19:49:43'),
(105, 'delivery.manage', 'web', '2026-09-17 14:23:51', '2026-09-17 14:23:51'),
(106, 'delivery.driver.view', 'web', '2026-09-17 14:23:51', '2026-09-17 14:23:51'),
(107, 'delivery.driver.edit', 'web', '2026-09-17 14:23:51', '2026-09-17 14:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `pos_quick_layouts`
--

CREATE TABLE `pos_quick_layouts` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keys` json DEFAULT NULL,
  `keep_folder_open` tinyint(1) NOT NULL DEFAULT '1',
  `is_current` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `ecom_gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `business_id`, `type`, `unit_id`, `secondary_unit_id`, `sub_unit_ids`, `brand_id`, `category_id`, `sub_category_id`, `tax`, `tax_type`, `enable_stock`, `alert_quantity`, `sku`, `barcode_type`, `expiry_period`, `expiry_period_type`, `enable_sr_no`, `weight`, `product_custom_field1`, `product_custom_field2`, `product_custom_field3`, `product_custom_field4`, `product_custom_field5`, `product_custom_field6`, `product_custom_field7`, `product_custom_field8`, `product_custom_field9`, `product_custom_field10`, `product_custom_field11`, `product_custom_field12`, `product_custom_field13`, `product_custom_field14`, `product_custom_field15`, `product_custom_field16`, `product_custom_field17`, `product_custom_field18`, `product_custom_field19`, `product_custom_field20`, `image`, `product_description`, `created_by`, `preparation_time_in_minutes`, `warranty_id`, `is_inactive`, `not_for_selling`, `sales_channel`, `ecom_slug`, `created_at`, `updated_at`, `ecom_short_description`, `ecom_long_description`, `ecom_seo_title`, `ecom_seo_description`, `ecom_is_featured`, `ecom_is_new_arrival`, `ecom_online_price`, `ecom_discount_price`, `ecom_gallery`) VALUES
(1, 'RAW Classic 1¼ Rolling Papers — 24-Booklet Display', 1, 'single', 1, NULL, NULL, 1, 12, NULL, NULL, 'inclusive', 1, NULL, 'RAW-CLS-114-24D', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'raw-cls-114-24d', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '24 booklets / display', 'RAW Classic 1¼ Rolling Papers — 24-Booklet Display — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 0, 18.0000, NULL, NULL),
(2, 'RAW Organic Hemp Pre-Rolled Cones 1¼ — 3-Pack, 32-Count Box', 1, 'single', 1, NULL, NULL, 1, 10, NULL, NULL, 'inclusive', 1, NULL, 'RAW-ORG-CONE-32', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'raw-org-cone-32', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '32 × 3-packs per box', 'RAW Organic Hemp Pre-Rolled Cones 1¼ — 3-Pack, 32-Count Box — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 1, 42.0000, NULL, NULL),
(3, 'Clipper Classic Lighter — Display of 48 Assorted', 1, 'single', 1, NULL, NULL, 3, 12, NULL, NULL, 'inclusive', 1, NULL, 'CLP-CLS-48', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'clp-cls-48', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '48 lighters / display', 'Clipper Classic Lighter — Display of 48 Assorted — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 0, 36.0000, NULL, NULL),
(4, 'GRAV Labs 12\" Classic Beaker Bong', 1, 'single', 1, NULL, NULL, 4, 13, NULL, NULL, 'inclusive', 1, NULL, 'GRV-BKR-12', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'grv-bkr-12', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '1 unit', 'GRAV Labs 12\" Classic Beaker Bong — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 0, 89.0000, NULL, NULL),
(5, 'GRAV Labs 4\" Spoon Pipe — Assorted (6-pack)', 1, 'single', 1, NULL, NULL, 4, 13, NULL, NULL, 'inclusive', 1, NULL, 'GRV-SPN-6', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'grv-spn-6', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '6 units / pack', 'GRAV Labs 4\" Spoon Pipe — Assorted (6-pack) — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 0, 54.0000, NULL, NULL),
(6, 'Puffco Peak Pro Smart Dab Rig', 1, 'single', 1, NULL, NULL, 5, 13, NULL, NULL, 'inclusive', 1, NULL, 'PFC-PEAK-PRO', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'pfc-peak-pro', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '1 unit', 'Puffco Peak Pro Smart Dab Rig — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 0, 329.0000, NULL, NULL),
(7, 'Lost Mary MO5000 Disposable Vape — 10-Pack Display', 1, 'single', 1, NULL, NULL, 6, 15, NULL, NULL, 'inclusive', 1, NULL, 'LM-MO5000-10', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'lm-mo5000-10', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '10 units / display', 'Lost Mary MO5000 Disposable Vape — 10-Pack Display — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 1, 79.0000, NULL, NULL),
(8, 'RAZ TN9000 Disposable Vape — 5-Pack Display', 1, 'single', 1, NULL, NULL, 7, 15, NULL, NULL, 'inclusive', 1, NULL, 'RAZ-TN9000-5', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'raz-tn9000-5', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '5 units / display', 'RAZ TN9000 Disposable Vape — 5-Pack Display — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 1, 1, 62.0000, NULL, NULL),
(9, 'Kali Extrax THCA Flower 3.5g — Berry Gelato, 12-Pack', 1, 'single', 1, NULL, NULL, 10, 14, NULL, NULL, 'inclusive', 1, NULL, 'KEX-THCA-BG-12', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'kex-thca-bg-12', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '12 jars / case', 'Kali Extrax THCA Flower 3.5g — Berry Gelato, 12-Pack — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 0, 1, 144.0000, NULL, NULL),
(10, 'HQD Cuvie Plus Disposable — 10-Pack', 1, 'single', 1, NULL, NULL, 8, 15, NULL, NULL, 'inclusive', 1, NULL, 'HQD-CVP-10', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'hqd-cvp-10', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '10 units / display', 'HQD Cuvie Plus Disposable — 10-Pack — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 0, 1, 55.0000, NULL, NULL),
(11, 'Storz & Bickel Mighty+ Vaporizer', 1, 'single', 1, NULL, NULL, 9, 11, NULL, NULL, 'inclusive', 1, NULL, 'SAB-MIGHTY-PLUS', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'sab-mighty-plus', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '1 unit', 'Storz & Bickel Mighty+ Vaporizer — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 0, 0, 289.0000, NULL, NULL),
(12, 'Zig-Zag French Orange Papers — 24-Booklet Display', 1, 'single', 1, NULL, NULL, 2, 12, NULL, NULL, 'inclusive', 1, NULL, 'ZZ-ORG-24D', 'C128', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=70', NULL, 1, NULL, NULL, 0, 0, 'pos_ecommerce', 'zz-org-24d', '2026-09-15 15:01:06', '2026-09-15 15:01:06', '24 booklets / display', 'Zig-Zag French Orange Papers — 24-Booklet Display — wholesale display pack for licensed retailers. Min. qty: 1 display.', NULL, NULL, 0, 0, 16.5000, NULL, NULL);

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
(9, 1),
(10, 1),
(11, 1),
(12, 1);

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
(1, NULL, 'DUMMY', 1, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(2, NULL, 'DUMMY', 2, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(3, NULL, 'DUMMY', 3, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(4, NULL, 'DUMMY', 4, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(5, NULL, 'DUMMY', 5, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(6, NULL, 'DUMMY', 6, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(7, NULL, 'DUMMY', 7, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(8, NULL, 'DUMMY', 8, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(9, NULL, 'DUMMY', 9, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(10, NULL, 'DUMMY', 10, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(11, NULL, 'DUMMY', 11, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06'),
(12, NULL, 'DUMMY', 12, 1, '2026-09-15 15:01:06', '2026-09-15 15:01:06');

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
(1, 'contacts', 3, 1, '2026-08-26 19:47:20', '2026-09-15 07:08:36'),
(2, 'sales_order', 5, 1, '2026-09-15 06:49:39', '2026-09-15 09:00:01');

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
(1, 'Admin#1', 'web', 1, 1, 0, '2026-08-06 06:12:54', '2026-08-06 06:12:54'),
(2, 'Cashier#1', 'web', 1, 0, 0, '2026-08-06 06:12:54', '2026-08-06 06:12:54'),
(3, 'Sales Representative#1', 'web', 1, 0, 0, '2026-08-25 15:18:34', '2026-08-25 15:18:34'),
(4, 'Delivery#1', 'web', 1, 0, 0, '2026-09-17 14:23:51', '2026-09-17 14:23:51');

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
(98, 1),
(105, 1),
(106, 1),
(107, 1),
(25, 2),
(26, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(27, 3),
(36, 3),
(37, 3),
(99, 3),
(100, 3),
(101, 3),
(102, 3),
(103, 3),
(104, 3),
(105, 4),
(106, 4),
(107, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sale_zero_sales`
--

CREATE TABLE `sale_zero_sales` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `contact_id` int UNSIGNED NOT NULL,
  `reason_id` int UNSIGNED NOT NULL,
  `created_by` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_zero_sales`
--

INSERT INTO `sale_zero_sales` (`id`, `business_id`, `contact_id`, `reason_id`, `created_by`, `location_id`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 7, 1, 'aaaaaaaaaaaaa', '2026-09-15 07:18:47', '2026-09-15 07:18:47');

-- --------------------------------------------------------

--
-- Table structure for table `sale_zero_sale_reasons`
--

CREATE TABLE `sale_zero_sale_reasons` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_zero_sale_reasons`
--

INSERT INTO `sale_zero_sale_reasons` (`id`, `name`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Call back', 'call_back', 1, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(2, 'Does not want order', 'does_not_want_order', 2, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(3, 'GPS reset', 'gps_reset', 3, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(4, 'Marchandising', 'marchandising', 4, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(5, 'Note', 'note', 5, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(6, 'Owner/manager not in', 'owner_manager_not_in', 6, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(7, 'Refused service', 'refused_service', 7, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36'),
(8, 'Will order next time', 'will_order_next_time', 8, 1, '2026-09-15 08:01:36', '2026-09-15 08:03:36');

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
(1, 'Retail', 'Retail selling prices for tobacco / MSA', 1, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(2, 'Wholesale', 'Wholesale selling prices for tobacco / MSA', 1, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20');

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
(1, 1, 'VAT@10%', 10.0000, 0, 0, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(2, 1, 'CGST@10%', 10.0000, 0, 1, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(3, 1, 'SGST@8%', 8.0000, 0, 1, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(4, 1, 'IGST@18%', 18.0000, 0, 0, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(5, 1, 'Sales Tax@9.75%', 9.7500, 0, 0, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(6, 1, 'Zero Tax@0%', 0.0000, 0, 0, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20'),
(7, 1, 'GST@18%', 18.0000, 1, 0, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20');

-- --------------------------------------------------------

--
-- Table structure for table `team_member_colors`
--

CREATE TABLE `team_member_colors` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_member_colors`
--

INSERT INTO `team_member_colors` (`id`, `business_id`, `user_id`, `color`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '#059669', '2026-08-25 18:01:21', '2026-08-25 18:01:21'),
(2, 1, 3, '#DC2626', '2026-08-25 18:01:21', '2026-08-25 18:01:21'),
(3, 1, 4, '#40ED4B', '2026-08-25 18:01:21', '2026-08-25 18:01:21'),
(4, 1, 5, '#000000', '2026-08-25 18:01:21', '2026-08-25 18:01:21'),
(5, 1, 6, '#4B08AF', '2026-08-25 18:01:21', '2026-08-25 18:01:21');

-- --------------------------------------------------------

--
-- Table structure for table `team_shifts`
--

CREATE TABLE `team_shifts` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `location_id` int UNSIGNED DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_shifts`
--

INSERT INTO `team_shifts` (`id`, `business_id`, `user_id`, `location_id`, `starts_at`, `ends_at`, `title`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 1, '2026-08-24 09:30:00', '2026-08-24 17:30:00', NULL, NULL, 4, '2026-08-25 16:03:03', '2026-08-25 16:03:03'),
(2, 1, 6, 1, '2026-08-24 09:30:00', '2026-08-24 17:30:00', NULL, NULL, 4, '2026-08-25 16:03:45', '2026-08-25 16:03:45'),
(3, 1, 6, 1, '2026-08-25 09:30:00', '2026-08-25 17:30:00', NULL, NULL, 4, '2026-08-25 16:17:57', '2026-08-25 16:17:57'),
(4, 1, 5, 1, '2026-08-25 09:30:00', '2026-08-25 17:30:00', NULL, NULL, 4, '2026-08-25 16:34:32', '2026-08-25 16:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `time_off_requests`
--

CREATE TABLE `time_off_requests` (
  `id` int UNSIGNED NOT NULL,
  `business_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'vacation',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `order_source` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `shipping_latitude` decimal(10,7) DEFAULT NULL,
  `shipping_longitude` decimal(10,7) DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `shipping_status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfillment_stage` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfillment_meta` json DEFAULT NULL,
  `shipping_date` datetime DEFAULT NULL,
  `shipping_tracking_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_of_boxes` int UNSIGNED DEFAULT NULL,
  `delivered_to` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_person` bigint DEFAULT NULL,
  `delivery_status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

INSERT INTO `transactions` (`id`, `business_id`, `location_id`, `is_kitchen_order`, `res_table_id`, `res_waiter_id`, `res_order_status`, `type`, `sub_type`, `status`, `sub_status`, `is_quotation`, `payment_status`, `adjustment_type`, `contact_id`, `customer_group_id`, `invoice_no`, `ref_no`, `source`, `order_source`, `subscription_no`, `subscription_repeat_on`, `transaction_date`, `total_before_tax`, `tax_id`, `tax_amount`, `discount_type`, `discount_amount`, `rp_redeemed`, `rp_redeemed_amount`, `shipping_details`, `shipping_method`, `shipping_address`, `shipping_latitude`, `shipping_longitude`, `delivery_date`, `shipping_status`, `fulfillment_stage`, `fulfillment_meta`, `shipping_date`, `shipping_tracking_no`, `no_of_boxes`, `delivered_to`, `delivery_person`, `delivery_status`, `shipping_charges`, `shipping_custom_field_1`, `shipping_custom_field_2`, `shipping_custom_field_3`, `shipping_custom_field_4`, `shipping_custom_field_5`, `additional_notes`, `staff_note`, `is_export`, `export_custom_fields_info`, `round_off_amount`, `additional_expense_key_1`, `additional_expense_value_1`, `additional_expense_key_2`, `additional_expense_value_2`, `additional_expense_key_3`, `additional_expense_value_3`, `additional_expense_key_4`, `additional_expense_value_4`, `final_total`, `expense_category_id`, `expense_sub_category_id`, `expense_for`, `commission_agent`, `document`, `is_direct_sale`, `crm_is_order_request`, `is_suspend`, `exchange_rate`, `total_amount_recovered`, `transfer_parent_id`, `return_parent_id`, `opening_stock_product_id`, `created_by`, `purchase_requisition_ids`, `prefer_payment_method`, `prefer_payment_account`, `sales_order_ids`, `purchase_order_ids`, `custom_field_1`, `custom_field_2`, `custom_field_3`, `custom_field_4`, `import_batch`, `import_time`, `types_of_service_id`, `packing_charge`, `packing_charge_type`, `service_custom_field_1`, `service_custom_field_2`, `service_custom_field_3`, `service_custom_field_4`, `service_custom_field_5`, `service_custom_field_6`, `is_created_from_api`, `rp_earned`, `order_addresses`, `is_recurring`, `recur_interval`, `recur_interval_type`, `recur_repetitions`, `recur_stopped_on`, `recur_parent_id`, `invoice_token`, `pay_term_number`, `pay_term_type`, `selling_price_group_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'ordered', NULL, 0, 'due', NULL, 1, 1, '2026/0001', '', 'sale_app', 'sale_app', NULL, NULL, '2026-09-15 12:49:39', 72.0000, NULL, 0.0000, 'fixed', 0.0000, 0, 0.0000, 'Delivery', 'Delivery', 'Walk-In Customer', NULL, NULL, NULL, 'ordered', 'picking', '{\"timeline\": [{\"at\": \"2026-09-15 12:49:39\", \"by\": \"System\", \"event\": \"entered_fulfillment\", \"label\": \"Entered Fulfillment\"}]}', NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 72.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 7, NULL, 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 06:49:39', '2026-09-15 06:49:39'),
(2, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'ordered', NULL, 0, 'due', NULL, 1, 1, '2026/0002', '', 'sale_app', 'sale_app', NULL, NULL, '2026-09-15 12:59:04', 108.0000, NULL, 0.0000, 'fixed', 0.0000, 0, 0.0000, 'Delivery', 'Delivery', 'Walk-In Customer', NULL, NULL, NULL, 'ordered', 'picking', '{\"timeline\": [{\"at\": \"2026-09-15 12:59:04\", \"by\": \"System\", \"event\": \"entered_fulfillment\", \"label\": \"Entered Fulfillment\"}]}', NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 108.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 7, NULL, 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 06:59:04', '2026-09-15 06:59:04'),
(3, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'ordered', NULL, 0, 'due', NULL, 3, NULL, '2026/0003', '', 'sale_app', 'sale_app', NULL, NULL, '2026-09-15 13:51:22', 267.0000, NULL, 0.0000, 'fixed', 0.0000, 0, 0.0000, 'Delivery', 'Delivery', 'Chastity Poole — Veniam dolores dolo Noble Kendall Reilly Simon (Asperiores architect)', NULL, NULL, NULL, 'ordered', 'picking', '{\"timeline\": [{\"at\": \"2026-09-15 13:51:22\", \"by\": \"System\", \"event\": \"entered_fulfillment\", \"label\": \"Entered Fulfillment\"}]}', NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 267.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 7, NULL, 'cash', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 07:51:22', '2026-09-15 07:51:22'),
(4, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'ordered', NULL, 0, NULL, NULL, 3, NULL, '2026/0004', '', 'customer_app', 'customer_app', NULL, NULL, '2026-09-15 14:57:58', 179.0000, NULL, 0.0000, 'fixed', 0.0000, 0, 0.0000, 'Delivery', 'Delivery', '', NULL, NULL, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, 'Ordered via Customer App by Test Customer', 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 179.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 08:57:58', '2026-09-15 08:57:58'),
(5, 1, 1, 0, NULL, NULL, NULL, 'sales_order', NULL, 'ordered', NULL, 0, NULL, NULL, 3, NULL, '2026/0005', '', 'customer_app', 'customer_app', NULL, NULL, '2026-09-15 15:00:01', 648.0000, NULL, 0.0000, 'fixed', 0.0000, 0, 0.0000, 'Delivery', 'Delivery', '', NULL, NULL, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, 'Ordered via Customer App by Test Customer', 0, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, NULL, 0.0000, 648.0000, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, 1.000, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.0000, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 1.0000, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 09:00:01', '2026-09-15 09:00:01');

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
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_ref_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `qty_picked` decimal(22,4) NOT NULL DEFAULT '0.0000',
  `qty_packed` decimal(22,4) NOT NULL DEFAULT '0.0000',
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

INSERT INTO `transaction_sell_lines` (`id`, `transaction_id`, `product_id`, `variation_id`, `quantity`, `qty_picked`, `qty_packed`, `secondary_unit_quantity`, `quantity_returned`, `unit_price_before_discount`, `unit_price`, `line_discount_type`, `line_discount_amount`, `unit_price_inc_tax`, `item_tax`, `tax_id`, `discount_id`, `lot_no_line_id`, `sell_line_note`, `so_line_id`, `so_quantity_invoiced`, `res_service_staff_id`, `res_line_order_status`, `parent_sell_line_id`, `children_type`, `sub_unit_id`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 3, 2.0000, 0.0000, 0.0000, 0.0000, 0.0000, 36.0000, 36.0000, 'fixed', 0.0000, 36.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 06:49:39', '2026-09-15 06:49:39'),
(2, 2, 3, 3, 3.0000, 0.0000, 0.0000, 0.0000, 0.0000, 36.0000, 36.0000, 'fixed', 0.0000, 36.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 06:59:04', '2026-09-15 06:59:04'),
(3, 3, 4, 4, 3.0000, 0.0000, 0.0000, 0.0000, 0.0000, 89.0000, 89.0000, 'fixed', 0.0000, 89.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 07:51:22', '2026-09-15 07:51:22'),
(4, 4, 3, 3, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, 36.0000, 36.0000, 'fixed', 0.0000, 36.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 08:57:58', '2026-09-15 08:57:58'),
(5, 4, 4, 4, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, 89.0000, 89.0000, 'fixed', 0.0000, 89.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 08:57:58', '2026-09-15 08:57:58'),
(6, 4, 5, 5, 1.0000, 0.0000, 0.0000, 0.0000, 0.0000, 54.0000, 54.0000, 'fixed', 0.0000, 54.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 08:57:58', '2026-09-15 08:57:58'),
(7, 5, 3, 3, 6.0000, 0.0000, 0.0000, 0.0000, 0.0000, 36.0000, 36.0000, 'fixed', 0.0000, 36.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 09:00:01', '2026-09-15 09:00:01'),
(8, 5, 4, 4, 3.0000, 0.0000, 0.0000, 0.0000, 0.0000, 89.0000, 89.0000, 'fixed', 0.0000, 89.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 09:00:01', '2026-09-15 09:00:01'),
(9, 5, 10, 10, 3.0000, 0.0000, 0.0000, 0.0000, 0.0000, 55.0000, 55.0000, 'fixed', 0.0000, 55.0000, 0.0000, NULL, NULL, NULL, '', NULL, 0.0000, NULL, NULL, NULL, '', NULL, '2026-09-15 09:00:01', '2026-09-15 09:00:01');

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
(1, 1, 'Pieces', 'Pc(s)', 0, NULL, NULL, 1, NULL, '2026-08-26 19:47:20', '2026-08-26 19:47:20');

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
  `barcode_login` tinyint(1) NOT NULL DEFAULT '0',
  `barcode_switch_id` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','terminated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_enable_service_staff_pin` tinyint(1) NOT NULL DEFAULT '0',
  `service_staff_pin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `crm_contact_id` int UNSIGNED DEFAULT NULL,
  `is_cmmsn_agnt` tinyint(1) NOT NULL DEFAULT '0',
  `cmmsn_percent` decimal(4,2) NOT NULL DEFAULT '0.00',
  `sales_target` decimal(22,4) DEFAULT NULL,
  `sales_target_daily` decimal(22,4) DEFAULT NULL,
  `sales_target_weekly` decimal(22,4) DEFAULT NULL,
  `sales_target_monthly` decimal(22,4) DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  `pos_quick_keys_enabled` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `surname`, `first_name`, `last_name`, `username`, `email`, `password`, `language`, `contact_no`, `address`, `remember_token`, `business_id`, `available_at`, `paused_at`, `max_sales_discount_percent`, `allow_login`, `barcode_login`, `barcode_switch_id`, `status`, `is_enable_service_staff_pin`, `service_staff_pin`, `crm_contact_id`, `is_cmmsn_agnt`, `cmmsn_percent`, `sales_target`, `sales_target_daily`, `sales_target_weekly`, `sales_target_monthly`, `selected_contacts`, `dob`, `gender`, `marital_status`, `blood_group`, `contact_number`, `alt_number`, `family_number`, `fb_link`, `twitter_link`, `social_media_1`, `social_media_2`, `permanent_address`, `current_address`, `guardian_name`, `custom_field_1`, `custom_field_2`, `custom_field_3`, `custom_field_4`, `bank_details`, `id_proof_name`, `id_proof_number`, `deleted_at`, `created_at`, `updated_at`, `pos_quick_keys_enabled`) VALUES
(1, 'user', NULL, 'greencloud', 'wholesale', 'japspos', 'greencloudwholesale@gmail.com', '$2y$10$LH3D2XH/82k.Av8HfGb3GOfKM3F7Qi7I86iZLyxhJFtjajS8pKHpK', 'en', NULL, NULL, 'K1JGIAfWuwH5dxUOoczsYOkIZGnHlWZGGF6YnfmTMbdNbUzrhiCCbpWFQrWw', 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-06-04 16:29:53', '2026-08-06 06:13:55', 1),
(3, 'user', NULL, 'Qiyin', 'Chen', 'chenqi', 'chen@greencloudwholesale.com', '$2y$10$2sSj9AKDZz4vgEsgN3sMjuHpJ.Om7LIqK4T8Bg.7GxNlVlp2S.BY6', 'en', NULL, NULL, 'aLHKwpnq5g7UnWtRGnkburlqWm9ZY0iZYBSSMX35jBXzCdFLrHphoxViE3kV', 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-08-06 21:03:41', '2026-08-07 17:49:13', 1),
(4, 'user', NULL, 'Justin', 'Jiang', 'jiangj', 'justin@greencloudwholesale.com', '$2y$10$sr6HaE9JZHRUxqeMpMdHeOlKXN.1gqHLckr.AwT8phq4/GFXhvOVq', 'en', NULL, NULL, 'i2JbxjLHNafHctg2qil4dbSUt8Dqtm1ZLy0IkeAZstDr2LZehy5RYIjEMZyC', 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-08-06 21:04:14', '2026-08-06 21:04:14', 1),
(5, 'user', NULL, 'Gage', 'Franklin', 'frankling', 'gage@greencloudwholesale.com', '$2y$10$F6Q7nrbgaJG8vLiHB7//x.qExUSVgOKNa210U6yyvDaRbvWYYSOr.', 'en', NULL, NULL, 'hOQ2IZEoTk2jpnuMtV2KcZok7m3H5qOaSKxvGxJvLPjctVshKJmvZzB2piHz', 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-08-07 16:32:43', '2026-08-07 16:32:43', 1),
(6, 'user', NULL, 'Kat', 'Venzera', 'venzerak', 'kat@greencloudwholesale.com', '$2y$10$FL37NsSjjOKP5mzffnmr/.SNK./Q1dzYNdlb59d1WfhdpzK20gstq', 'en', NULL, NULL, 'Kc7hBCQNjfwlSmgVofvATuoaOP4IYdUIcWUbBdQONafSvAgJLC5UNvp1eloa', 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-08-07 16:33:37', '2026-08-18 18:25:16', 1),
(7, 'user', 'mr', 'Sales', NULL, 'sales', 'sale@gmail.com', '$2y$10$ZXwr9H2zzKEAj9b.Z6xb3uZ6WXDVQ8Di.wdpTDk77AJ.A2EfH65ji', 'en', NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"account_holder_name\":null,\"account_number\":null,\"bank_name\":null,\"bank_code\":null,\"branch\":null,\"tax_payer_id\":null}', NULL, NULL, NULL, '2026-09-15 06:30:08', '2026-09-15 06:30:08', 1),
(8, 'user', 'Mr', 'Delivery', 'Person', 'deliveryboy', 'deliveryboy@greencloudwholesale.test', '$2y$10$VPSgak62rJXejPNkM5KVVO0EPZj2TiHecTrOcBCk16K38FMtcPXgW', 'en', NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 0, NULL, 'active', 0, NULL, NULL, 0, 0.00, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-17 14:23:51', '2026-09-17 14:23:51', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_contact_access`
--

CREATE TABLE `user_contact_access` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `contact_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_contact_access`
--

INSERT INTO `user_contact_access` (`id`, `user_id`, `contact_id`) VALUES
(1, 4, 2),
(2, 6, 2),
(3, 7, 2),
(4, 4, 4);

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
(1, 'DUMMY', 1, 'RAW-CLS-114-24D', 1, NULL, 12.6000, 12.6000, 25.0000, 18.0000, 18.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(2, 'DUMMY', 2, 'RAW-ORG-CONE-32', 2, NULL, 29.4000, 29.4000, 25.0000, 42.0000, 42.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(3, 'DUMMY', 3, 'CLP-CLS-48', 3, NULL, 25.2000, 25.2000, 25.0000, 36.0000, 36.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(4, 'DUMMY', 4, 'GRV-BKR-12', 4, NULL, 62.3000, 62.3000, 25.0000, 89.0000, 89.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(5, 'DUMMY', 5, 'GRV-SPN-6', 5, NULL, 37.8000, 37.8000, 25.0000, 54.0000, 54.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(6, 'DUMMY', 6, 'PFC-PEAK-PRO', 6, NULL, 230.3000, 230.3000, 25.0000, 329.0000, 329.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(7, 'DUMMY', 7, 'LM-MO5000-10', 7, NULL, 55.3000, 55.3000, 25.0000, 79.0000, 79.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(8, 'DUMMY', 8, 'RAZ-TN9000-5', 8, NULL, 43.4000, 43.4000, 25.0000, 62.0000, 62.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(9, 'DUMMY', 9, 'KEX-THCA-BG-12', 9, NULL, 100.8000, 100.8000, 25.0000, 144.0000, 144.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(10, 'DUMMY', 10, 'HQD-CVP-10', 10, NULL, 38.5000, 38.5000, 25.0000, 55.0000, 55.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(11, 'DUMMY', 11, 'SAB-MIGHTY-PLUS', 11, NULL, 202.3000, 202.3000, 25.0000, 289.0000, 289.0000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]'),
(12, 'DUMMY', 12, 'ZZ-ORG-24D', 12, NULL, 11.5500, 11.5500, 25.0000, 16.5000, 16.5000, '2026-09-15 15:01:06', '2026-09-15 15:01:06', NULL, '[]');

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
(1, 1, 1, 1, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(2, 2, 2, 2, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(3, 3, 3, 3, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(4, 4, 4, 4, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(5, 5, 5, 5, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(6, 6, 6, 6, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(7, 7, 7, 7, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(8, 8, 8, 8, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(9, 9, 9, 9, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(10, 10, 10, 10, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(11, 11, 11, 11, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51'),
(12, 12, 12, 12, 1, 100.0000, '2026-09-15 15:40:51', '2026-09-15 15:40:51');

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
-- Indexes for table `delivery_areas`
--
ALTER TABLE `delivery_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_areas_business_id_index` (`business_id`);

--
-- Indexes for table `delivery_route_orders`
--
ALTER TABLE `delivery_route_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_route_orders_delivery_route_id_transaction_id_unique` (`delivery_route_id`,`transaction_id`),
  ADD KEY `delivery_route_orders_delivery_route_id_index` (`delivery_route_id`),
  ADD KEY `delivery_route_orders_transaction_id_index` (`transaction_id`);

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
-- Indexes for table `driver_delivery_routes`
--
ALTER TABLE `driver_delivery_routes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_delivery_routes_business_id_route_date_index` (`business_id`,`route_date`),
  ADD KEY `driver_delivery_routes_delivery_user_id_route_date_index` (`delivery_user_id`,`route_date`),
  ADD KEY `driver_delivery_routes_business_id_index` (`business_id`),
  ADD KEY `driver_delivery_routes_delivery_user_id_index` (`delivery_user_id`),
  ADD KEY `driver_delivery_routes_business_location_id_index` (`business_location_id`),
  ADD KEY `driver_delivery_routes_route_date_index` (`route_date`);

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
-- Indexes for table `ecom_inquiries`
--
ALTER TABLE `ecom_inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ecom_order_meta`
--
ALTER TABLE `ecom_order_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_order_meta_transaction_id_unique` (`transaction_id`),
  ADD UNIQUE KEY `ecom_order_meta_tracking_token_unique` (`tracking_token`);

--
-- Indexes for table `ecom_password_resets`
--
ALTER TABLE `ecom_password_resets`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `ecom_stock_alerts`
--
ALTER TABLE `ecom_stock_alerts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_stock_alerts_product_id_email_unique` (`product_id`,`email`);

--
-- Indexes for table `ecom_subscribers`
--
ALTER TABLE `ecom_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ecom_subscribers_business_id_email_unique` (`business_id`,`email`);

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
-- Indexes for table `mws_app_branding`
--
ALTER TABLE `mws_app_branding`
  ADD PRIMARY KEY (`AbId`);

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
-- Indexes for table `pos_quick_layouts`
--
ALTER TABLE `pos_quick_layouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pos_quick_layouts_business_id_user_id_index` (`business_id`,`user_id`);

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
-- Indexes for table `sale_zero_sales`
--
ALTER TABLE `sale_zero_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_zero_sales_business_id_contact_id_index` (`business_id`,`contact_id`),
  ADD KEY `sale_zero_sales_created_by_index` (`created_by`);

--
-- Indexes for table `sale_zero_sale_reasons`
--
ALTER TABLE `sale_zero_sale_reasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_zero_sale_reasons_slug_unique` (`slug`);

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
-- Indexes for table `team_member_colors`
--
ALTER TABLE `team_member_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `team_member_colors_business_id_user_id_unique` (`business_id`,`user_id`),
  ADD KEY `team_member_colors_business_id_index` (`business_id`);

--
-- Indexes for table `team_shifts`
--
ALTER TABLE `team_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_shifts_business_id_starts_at_index` (`business_id`,`starts_at`),
  ADD KEY `team_shifts_business_id_user_id_index` (`business_id`,`user_id`);

--
-- Indexes for table `time_off_requests`
--
ALTER TABLE `time_off_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `time_off_requests_business_id_status_index` (`business_id`,`status`),
  ADD KEY `time_off_requests_business_id_user_id_index` (`business_id`,`user_id`),
  ADD KEY `time_off_requests_business_id_starts_at_index` (`business_id`,`starts_at`);

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
  ADD KEY `transactions_shipping_date_index` (`shipping_date`),
  ADD KEY `transactions_order_source_index` (`order_source`),
  ADD KEY `transactions_fulfillment_stage_index` (`fulfillment_stage`),
  ADD KEY `transactions_delivery_status_index` (`delivery_status`);

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cash_register_transactions`
--
ALTER TABLE `cash_register_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `delivery_areas`
--
ALTER TABLE `delivery_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_route_orders`
--
ALTER TABLE `delivery_route_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_and_notes`
--
ALTER TABLE `document_and_notes`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_delivery_routes`
--
ALTER TABLE `driver_delivery_routes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_carts`
--
ALTER TABLE `ecom_carts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ecom_cart_items`
--
ALTER TABLE `ecom_cart_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ecom_collections`
--
ALTER TABLE `ecom_collections`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ecom_customer_licenses`
--
ALTER TABLE `ecom_customer_licenses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_inquiries`
--
ALTER TABLE `ecom_inquiries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_order_meta`
--
ALTER TABLE `ecom_order_meta`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_password_resets`
--
ALTER TABLE `ecom_password_resets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `ecom_stock_alerts`
--
ALTER TABLE `ecom_stock_alerts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ecom_subscribers`
--
ALTER TABLE `ecom_subscribers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `employee_attendance_breaks`
--
ALTER TABLE `employee_attendance_breaks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=336;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `pos_quick_layouts`
--
ALTER TABLE `pos_quick_layouts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printers`
--
ALTER TABLE `printers`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_racks`
--
ALTER TABLE `product_racks`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variations`
--
ALTER TABLE `product_variations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `purchase_lines`
--
ALTER TABLE `purchase_lines`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reference_counts`
--
ALTER TABLE `reference_counts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `res_tables`
--
ALTER TABLE `res_tables`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sale_zero_sales`
--
ALTER TABLE `sale_zero_sales`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sale_zero_sale_reasons`
--
ALTER TABLE `sale_zero_sale_reasons`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `selling_price_groups`
--
ALTER TABLE `selling_price_groups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `team_member_colors`
--
ALTER TABLE `team_member_colors`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `team_shifts`
--
ALTER TABLE `team_shifts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `time_off_requests`
--
ALTER TABLE `time_off_requests`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaction_payments`
--
ALTER TABLE `transaction_payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_sell_lines`
--
ALTER TABLE `transaction_sell_lines`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transaction_sell_lines_purchase_lines`
--
ALTER TABLE `transaction_sell_lines_purchase_lines`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_contact_access`
--
ALTER TABLE `user_contact_access`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `variations`
--
ALTER TABLE `variations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `variation_group_prices`
--
ALTER TABLE `variation_group_prices`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `variation_location_details`
--
ALTER TABLE `variation_location_details`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- Constraints for table `delivery_route_orders`
--
ALTER TABLE `delivery_route_orders`
  ADD CONSTRAINT `delivery_route_orders_delivery_route_id_foreign` FOREIGN KEY (`delivery_route_id`) REFERENCES `driver_delivery_routes` (`id`) ON DELETE CASCADE;

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
