-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 19, 2026 at 05:38 PM
-- Server version: 11.4.13-MariaDB-cll-lve
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `k66x4ro_fittrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_locations`
--

CREATE TABLE `activity_locations` (
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `location_type` enum('home','gym','swimming_pool','park','sports_centre','outdoor_route','other') NOT NULL,
  `label` varchar(120) DEFAULT NULL,
  `latitude_encrypted` mediumtext DEFAULT NULL,
  `longitude_encrypted` mediumtext DEFAULT NULL,
  `precision_level` varchar(30) DEFAULT NULL,
  `consent_recorded_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `activity_locations`
--

INSERT INTO `activity_locations` (`location_id`, `tenant_id`, `user_id`, `location_type`, `label`, `latitude_encrypted`, `longitude_encrypted`, `precision_level`, `consent_recorded_at`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 'outdoor_route', 'Liverpool area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 3, 5, 'other', 'Wirral area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 3, 10, 'outdoor_route', 'Manchester area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 3, 11, 'other', 'Salford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 3, 1, 'swimming_pool', 'Salford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 3, 16, 'outdoor_route', 'Sefton area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 3, 17, 'other', 'St Helens area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 3, 18, 'gym', 'Warrington area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(9, 3, 19, 'swimming_pool', 'Preston area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(10, 3, 20, 'park', 'Manchester area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(11, 3, 21, 'sports_centre', 'Salford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(12, 3, 22, 'outdoor_route', 'Trafford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(13, 3, 23, 'other', 'Stockport area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(14, 3, 24, 'gym', 'Liverpool area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(15, 3, 25, 'swimming_pool', 'Wirral area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, 4, 6, 'gym', 'Sefton area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(17, 4, 7, 'swimming_pool', 'St Helens area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(18, 4, 12, 'gym', 'Trafford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(19, 4, 13, 'swimming_pool', 'Stockport area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(20, 4, 2, 'park', 'Trafford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(21, 4, 26, 'park', 'Sefton area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(22, 4, 27, 'sports_centre', 'St Helens area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(23, 4, 28, 'outdoor_route', 'Warrington area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(24, 4, 29, 'other', 'Preston area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(25, 4, 30, 'gym', 'Manchester area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(26, 4, 31, 'swimming_pool', 'Salford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(27, 4, 32, 'park', 'Trafford area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(28, 4, 33, 'sports_centre', 'Stockport area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(29, 4, 34, 'outdoor_route', 'Liverpool area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(30, 4, 35, 'other', 'Wirral area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(31, 5, 8, 'park', 'Warrington area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(32, 5, 9, 'sports_centre', 'Preston area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(33, 5, 14, 'park', 'Liverpool area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(34, 5, 3, 'sports_centre', 'Stockport area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(35, 5, 36, 'gym', 'Sefton area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(36, 5, 37, 'swimming_pool', 'St Helens area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(37, 5, 38, 'park', 'Warrington area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(38, 5, 39, 'sports_centre', 'Preston area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(39, 5, 40, 'outdoor_route', 'Manchester area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(40, 3, 8, 'park', 'Warrington area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(41, 4, 15, 'sports_centre', 'Wirral area', NULL, NULL, NULL, NULL, '2026-09-30 17:13:52', '2026-09-30 17:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `activity_log_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `activity_type_id` bigint(20) UNSIGNED NOT NULL,
  `activity_date` date NOT NULL,
  `title` varchar(100) NOT NULL,
  `duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `steps` int(10) UNSIGNED DEFAULT NULL,
  `intensity` enum('light','moderate','vigorous') DEFAULT NULL,
  `notes` varchar(1000) DEFAULT NULL,
  `visibility` enum('private','tenant','public') NOT NULL DEFAULT 'private',
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`activity_log_id`, `tenant_id`, `user_id`, `activity_type_id`, `activity_date`, `title`, `duration_minutes`, `distance_km`, `steps`, `intensity`, `notes`, `visibility`, `location_id`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 13, '2026-09-26', 'Football training session', 64, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 3, 4, 7, '2026-09-26', 'North West training run', 29, 3.40, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 3, 4, 4, '2026-09-26', 'Pool swimming session', 29, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 3, 4, 10, '2026-09-26', 'Local area walk', 24, 1.40, 3500, 'moderate', 'Synthetic demonstration activity record.', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 3, 10, 13, '2026-09-20', 'Football training session', 70, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 3, 10, 7, '2026-09-20', 'North West training run', 35, 4.00, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 3, 10, 4, '2026-09-20', 'Pool swimming session', 35, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 3, 10, 10, '2026-09-20', 'Local area walk', 30, 2.00, 6500, 'moderate', 'Synthetic demonstration activity record.', 'private', 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(9, 3, 16, 13, '2026-10-11', 'Football training session', 76, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 6, '2026-09-30 17:13:52', '2026-10-17 08:33:13'),
(10, 3, 16, 7, '2026-10-13', 'North West training run', 41, 4.60, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 6, '2026-09-30 17:13:52', '2026-10-17 08:33:13'),
(11, 3, 16, 4, '2026-10-16', 'Pool swimming session', 41, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 6, '2026-09-30 17:13:52', '2026-10-17 08:33:13'),
(12, 3, 16, 10, '2026-10-14', 'Local area walk', 36, 2.60, 9500, 'moderate', 'Synthetic demonstration activity record.', 'private', 6, '2026-09-30 17:13:52', '2026-10-17 08:33:13'),
(13, 3, 18, 13, '2026-09-12', 'Football training session', 78, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 8, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(14, 3, 18, 7, '2026-09-12', 'North West training run', 43, 4.80, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 8, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(15, 3, 18, 4, '2026-09-12', 'Pool swimming session', 43, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 8, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, 3, 18, 10, '2026-09-12', 'Local area walk', 38, 2.80, 10500, 'light', 'Synthetic demonstration activity record.', 'private', 8, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(17, 3, 20, 13, '2026-09-10', 'Football training session', 80, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 10, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(18, 3, 20, 7, '2026-09-10', 'North West training run', 45, 5.00, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 10, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(19, 3, 20, 4, '2026-09-10', 'Pool swimming session', 45, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 10, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(20, 3, 20, 10, '2026-09-10', 'Local area walk', 40, 3.00, 1500, 'vigorous', 'Synthetic demonstration activity record.', 'private', 10, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(21, 3, 22, 13, '2026-09-08', 'Football training session', 82, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 12, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(22, 3, 22, 7, '2026-09-08', 'North West training run', 47, 5.20, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 12, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(23, 3, 22, 4, '2026-09-08', 'Pool swimming session', 47, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 12, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(24, 3, 22, 10, '2026-09-08', 'Local area walk', 42, 3.20, 2500, 'moderate', 'Synthetic demonstration activity record.', 'private', 12, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(25, 3, 24, 13, '2026-09-06', 'Football training session', 84, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 14, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(26, 3, 24, 7, '2026-09-06', 'North West training run', 49, 5.40, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 14, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(27, 3, 24, 4, '2026-09-06', 'Pool swimming session', 49, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 14, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(28, 3, 24, 10, '2026-09-06', 'Local area walk', 44, 3.40, 3500, 'light', 'Synthetic demonstration activity record.', 'private', 14, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(29, 4, 6, 14, '2026-09-24', 'Football training session', 66, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 16, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(30, 4, 6, 8, '2026-09-24', 'North West training run', 31, 3.60, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 16, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(31, 4, 6, 5, '2026-09-24', 'Pool swimming session', 31, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 16, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(32, 4, 6, 11, '2026-09-24', 'Local area walk', 26, 1.60, 4500, 'light', 'Synthetic demonstration activity record.', 'private', 16, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(33, 4, 12, 14, '2026-09-18', 'Football training session', 72, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 18, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(34, 4, 12, 8, '2026-09-18', 'North West training run', 37, 4.20, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 18, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(35, 4, 12, 5, '2026-09-18', 'Pool swimming session', 37, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 18, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(36, 4, 12, 11, '2026-09-18', 'Local area walk', 32, 2.20, 7500, 'light', 'Synthetic demonstration activity record.', 'private', 18, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(37, 4, 2, 14, '2026-09-28', 'Football training session', 62, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 20, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(38, 4, 2, 8, '2026-09-28', 'North West training run', 27, 3.20, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 20, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(39, 4, 2, 5, '2026-09-28', 'Pool swimming session', 27, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 20, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(40, 4, 2, 11, '2026-09-28', 'Local area walk', 22, 1.20, 2500, 'vigorous', 'Synthetic demonstration activity record.', 'private', 20, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(41, 4, 26, 14, '2026-09-04', 'Football training session', 86, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 21, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(42, 4, 26, 8, '2026-09-04', 'North West training run', 51, 5.60, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 21, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(43, 4, 26, 5, '2026-09-04', 'Pool swimming session', 51, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 21, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(44, 4, 26, 11, '2026-09-04', 'Local area walk', 46, 3.60, 4500, 'vigorous', 'Synthetic demonstration activity record.', 'private', 21, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(45, 4, 28, 14, '2026-09-02', 'Football training session', 88, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'tenant', 23, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(46, 4, 28, 8, '2026-09-02', 'North West training run', 53, 5.80, NULL, 'moderate', 'Synthetic demonstration activity record.', 'tenant', 23, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(47, 4, 28, 5, '2026-09-02', 'Pool swimming session', 53, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'tenant', 23, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(48, 4, 28, 11, '2026-09-02', 'Local area walk', 48, 3.80, 5500, 'moderate', 'Synthetic demonstration activity record.', 'tenant', 23, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(49, 4, 30, 14, '2026-08-31', 'Football training session', 90, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 25, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(50, 4, 30, 8, '2026-08-31', 'North West training run', 55, 6.00, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 25, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(51, 4, 30, 5, '2026-08-31', 'Pool swimming session', 55, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 25, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(52, 4, 30, 11, '2026-08-31', 'Local area walk', 50, 4.00, 6500, 'light', 'Synthetic demonstration activity record.', 'private', 25, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(53, 4, 32, 14, '2026-08-29', 'Football training session', 92, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 27, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(54, 4, 32, 8, '2026-08-29', 'North West training run', 57, 6.20, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 27, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(55, 4, 32, 5, '2026-08-29', 'Pool swimming session', 57, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 27, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(56, 4, 32, 11, '2026-08-29', 'Local area walk', 52, 4.20, 7500, 'vigorous', 'Synthetic demonstration activity record.', 'private', 27, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(57, 4, 34, 14, '2026-08-27', 'Football training session', 94, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 29, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(58, 4, 34, 8, '2026-08-27', 'North West training run', 59, 6.40, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 29, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(59, 4, 34, 5, '2026-08-27', 'Pool swimming session', 59, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 29, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(60, 4, 34, 11, '2026-08-27', 'Local area walk', 54, 4.40, 8500, 'moderate', 'Synthetic demonstration activity record.', 'private', 29, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(61, 5, 8, 15, '2026-09-22', 'Football training session', 68, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 31, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(62, 5, 8, 9, '2026-09-22', 'North West training run', 33, 3.80, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 31, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(63, 5, 8, 6, '2026-09-22', 'Pool swimming session', 33, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 31, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(64, 5, 8, 12, '2026-09-22', 'Local area walk', 28, 1.80, 5500, 'vigorous', 'Synthetic demonstration activity record.', 'private', 31, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(65, 5, 14, 15, '2026-09-16', 'Football training session', 74, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 33, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(66, 5, 14, 9, '2026-09-16', 'North West training run', 39, 4.40, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 33, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(67, 5, 14, 6, '2026-09-16', 'Pool swimming session', 39, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 33, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(68, 5, 14, 12, '2026-09-16', 'Local area walk', 34, 2.40, 8500, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 33, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(69, 5, 36, 15, '2026-08-25', 'Football training session', 61, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 35, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(70, 5, 36, 9, '2026-08-25', 'North West training run', 61, 6.60, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 35, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(71, 5, 36, 6, '2026-08-25', 'Pool swimming session', 61, NULL, NULL, 'light', 'Synthetic demonstration activity record.', 'private', 35, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(72, 5, 36, 12, '2026-08-25', 'Local area walk', 56, 4.60, 9500, 'light', 'Synthetic demonstration activity record.', 'private', 35, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(73, 5, 38, 15, '2026-08-23', 'Football training session', 63, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 37, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(74, 5, 38, 9, '2026-08-23', 'North West training run', 63, 6.80, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 37, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(75, 5, 38, 6, '2026-08-23', 'Pool swimming session', 63, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'private', 37, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(76, 5, 38, 12, '2026-08-23', 'Local area walk', 58, 4.80, 10500, 'vigorous', 'Synthetic demonstration activity record.', 'private', 37, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(77, 5, 40, 15, '2026-08-21', 'Football training session', 65, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 39, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(78, 5, 40, 9, '2026-08-21', 'North West training run', 65, 7.00, NULL, 'moderate', 'Synthetic demonstration activity record.', 'private', 39, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(79, 5, 40, 6, '2026-08-21', 'Pool swimming session', 65, NULL, NULL, 'moderate', 'Synthetic demonstration activity record.', 'tenant', 39, '2026-09-30 17:13:52', '2026-10-03 18:14:11'),
(80, 5, 40, 12, '2026-08-21', 'Local area walk', 60, 5.00, 1500, 'moderate', 'Synthetic demonstration activity record.', 'tenant', 39, '2026-09-30 17:13:52', '2026-10-03 18:14:11'),
(81, 3, 8, 13, '2026-09-22', 'Football training session', 68, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 40, '2026-09-30 17:13:52', '2026-10-03 18:14:11'),
(82, 3, 8, 7, '2026-09-22', 'North West training run', 33, 3.80, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 40, '2026-09-30 17:13:52', '2026-10-03 18:14:11'),
(83, 3, 8, 4, '2026-09-22', 'Pool swimming session', 33, NULL, NULL, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 40, '2026-09-30 17:13:52', '2026-10-03 18:14:11'),
(84, 3, 8, 10, '2026-09-22', 'Local area walk', 28, 1.80, 5500, 'vigorous', 'Synthetic demonstration activity record.', 'tenant', 40, '2026-09-30 17:13:52', '2026-10-03 18:14:11'),
(128, 3, 16, 1, '2026-10-15', 'morning run', 120, NULL, NULL, NULL, NULL, 'tenant', NULL, '2026-09-30 17:54:04', '2026-10-17 08:33:13'),
(129, 3, 16, 4, '2026-10-12', 'evening gym', 240, NULL, NULL, NULL, NULL, 'tenant', NULL, '2026-09-30 18:21:30', '2026-10-17 08:33:13'),
(130, 3, 16, 2, '2026-10-12', 'morning', 20, NULL, NULL, NULL, NULL, 'tenant', NULL, '2026-09-30 18:22:34', '2026-10-17 08:33:13'),
(131, 3, 16, 1, '2026-10-16', 'morning run', 20, NULL, NULL, NULL, NULL, 'tenant', NULL, '2026-10-02 17:26:14', '2026-10-17 08:33:13'),
(132, 3, 16, 7, '2026-10-17', 'Community test run', 45, 5.20, NULL, 'moderate', NULL, 'tenant', NULL, '2026-10-03 18:14:11', '2026-10-17 08:33:13'),
(135, 3, 16, 1, '2026-10-15', 'Lunch workout', 25, 10.66, NULL, 'moderate', NULL, 'public', NULL, '2026-10-02 00:00:00', '2026-10-17 08:33:13'),
(136, 3, 16, 13, '2026-10-12', 'Evening practice', 61, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-10-01 00:00:00', '2026-10-17 08:33:13'),
(137, 3, 16, 10, '2026-10-17', 'Afternoon workout', 24, 11.53, NULL, 'light', NULL, 'public', NULL, '2026-09-30 00:00:00', '2026-10-17 08:33:13'),
(138, 3, 16, 13, '2026-10-13', 'Afternoon session', 80, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-09-29 00:00:00', '2026-10-17 08:33:13'),
(139, 3, 16, 10, '2026-10-15', 'Evening run', 34, NULL, NULL, 'light', NULL, 'public', NULL, '2026-09-28 00:00:00', '2026-10-17 08:33:13'),
(140, 3, 16, 4, '2026-10-11', 'Lunch practice', 100, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-24 00:00:00', '2026-10-17 08:33:13'),
(141, 3, 16, 7, '2026-10-13', 'Evening run', 83, 5.42, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-23 00:00:00', '2026-10-17 08:33:13'),
(142, 3, 16, 13, '2026-10-16', 'Morning practice', 53, 6.06, NULL, 'light', NULL, 'tenant', NULL, '2026-09-21 00:00:00', '2026-10-17 08:33:13'),
(143, 3, 16, 1, '2026-10-17', 'Night session', 43, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-09-20 00:00:00', '2026-10-17 08:33:13'),
(144, 3, 16, 10, '2026-10-13', 'Night practice', 56, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-09-19 00:00:00', '2026-10-17 08:33:13'),
(145, 3, 16, 10, '2026-10-17', 'Afternoon practice', 41, NULL, NULL, 'light', NULL, 'private', NULL, '2026-09-17 00:00:00', '2026-10-17 08:33:13'),
(146, 3, 16, 10, '2026-10-14', 'Evening workout', 115, 14.80, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-16 00:00:00', '2026-10-17 08:33:13'),
(147, 3, 16, 7, '2026-10-16', 'Morning workout', 97, 6.72, NULL, 'light', NULL, 'private', NULL, '2026-09-15 00:00:00', '2026-10-17 08:33:13'),
(148, 3, 16, 4, '2026-10-14', 'Afternoon practice', 85, 4.15, NULL, 'moderate', NULL, 'private', NULL, '2026-09-14 00:00:00', '2026-10-17 08:33:13'),
(149, 3, 16, 1, '2026-10-12', 'Night run', 59, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-09-13 00:00:00', '2026-10-17 08:33:13'),
(150, 3, 16, 1, '2026-10-15', 'Night practice', 108, 10.39, NULL, 'light', NULL, 'public', NULL, '2026-09-11 00:00:00', '2026-10-17 08:33:13'),
(151, 3, 16, 13, '2026-10-14', 'Morning practice', 99, NULL, NULL, 'light', NULL, 'public', NULL, '2026-09-07 00:00:00', '2026-10-17 08:33:13'),
(152, 3, 16, 7, '2026-10-15', 'Night training', 28, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-09-06 00:00:00', '2026-10-17 08:33:13'),
(153, 3, 16, 10, '2026-10-14', 'Lunch training', 32, 4.47, NULL, 'moderate', NULL, 'private', NULL, '2026-09-05 00:00:00', '2026-10-17 08:33:13'),
(154, 3, 16, 4, '2026-10-15', 'Evening workout', 116, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-09-04 00:00:00', '2026-10-17 08:33:13'),
(155, 3, 16, 4, '2026-10-16', 'Lunch training', 58, 13.59, NULL, 'light', NULL, 'tenant', NULL, '2026-09-03 00:00:00', '2026-10-17 08:33:13'),
(156, 3, 16, 13, '2026-10-11', 'Afternoon run', 36, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-09-02 00:00:00', '2026-10-17 08:33:13'),
(157, 3, 16, 4, '2026-10-17', 'Evening run', 101, 3.68, NULL, 'light', NULL, 'private', NULL, '2026-09-01 00:00:00', '2026-10-17 08:33:13'),
(158, 3, 16, 1, '2026-10-15', 'Night workout', 47, 4.76, NULL, 'moderate', NULL, 'private', NULL, '2026-08-31 00:00:00', '2026-10-17 08:33:13'),
(159, 3, 16, 4, '2026-10-12', 'Afternoon practice', 119, NULL, NULL, 'light', NULL, 'public', NULL, '2026-08-29 00:00:00', '2026-10-17 08:33:13'),
(160, 3, 16, 4, '2026-10-13', 'Morning workout', 81, NULL, NULL, 'light', NULL, 'private', NULL, '2026-08-28 00:00:00', '2026-10-17 08:33:13'),
(161, 3, 16, 4, '2026-10-16', 'Morning run', 21, 3.24, NULL, 'light', NULL, 'public', NULL, '2026-08-27 00:00:00', '2026-10-17 08:33:13'),
(162, 3, 16, 4, '2026-10-17', 'Night run', 71, NULL, NULL, 'light', NULL, 'private', NULL, '2026-08-26 00:00:00', '2026-10-17 08:33:13'),
(163, 3, 16, 7, '2026-10-14', 'Lunch workout', 105, 7.61, NULL, 'light', NULL, 'public', NULL, '2026-08-25 00:00:00', '2026-10-17 08:33:13'),
(164, 3, 16, 13, '2026-10-15', 'Morning run', 97, 15.25, NULL, 'light', NULL, 'private', NULL, '2026-08-24 00:00:00', '2026-10-17 08:33:13'),
(165, 3, 16, 1, '2026-10-14', 'Evening run', 109, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-08-23 00:00:00', '2026-10-17 08:33:13'),
(166, 3, 16, 10, '2026-10-14', 'Night training', 33, 9.64, NULL, 'moderate', NULL, 'public', NULL, '2026-08-19 00:00:00', '2026-10-17 08:33:13'),
(167, 3, 16, 13, '2026-10-17', 'Morning run', 67, 12.74, NULL, 'moderate', NULL, 'tenant', NULL, '2026-08-17 00:00:00', '2026-10-17 08:33:13'),
(168, 3, 16, 13, '2026-10-13', 'Afternoon session', 81, NULL, NULL, 'light', NULL, 'public', NULL, '2026-08-16 00:00:00', '2026-10-17 08:33:13'),
(169, 3, 16, 13, '2026-10-11', 'Night practice', 118, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-08-15 00:00:00', '2026-10-17 08:33:13'),
(170, 3, 16, 1, '2026-10-12', 'Night training', 62, NULL, NULL, 'light', NULL, 'public', NULL, '2026-08-11 00:00:00', '2026-10-17 08:33:13'),
(171, 3, 16, 7, '2026-10-17', 'Morning training', 100, 3.32, NULL, 'moderate', NULL, 'public', NULL, '2026-08-10 00:00:00', '2026-10-17 08:33:13'),
(172, 3, 16, 4, '2026-10-15', 'Afternoon practice', 58, 7.25, NULL, 'moderate', NULL, 'tenant', NULL, '2026-08-09 00:00:00', '2026-10-17 08:33:13'),
(173, 3, 16, 10, '2026-10-16', 'Morning training', 113, 7.38, NULL, 'light', NULL, 'tenant', NULL, '2026-08-08 00:00:00', '2026-10-17 08:33:13'),
(174, 3, 16, 10, '2026-10-16', 'Afternoon training', 51, 11.77, NULL, 'moderate', NULL, 'public', NULL, '2026-08-07 00:00:00', '2026-10-17 08:33:13'),
(175, 3, 16, 7, '2026-10-13', 'Morning training', 47, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-08-06 00:00:00', '2026-10-17 08:33:13'),
(176, 3, 16, 10, '2026-10-16', 'Morning practice', 91, 13.67, NULL, 'light', NULL, 'tenant', NULL, '2026-08-05 00:00:00', '2026-10-17 08:33:13'),
(177, 3, 16, 4, '2026-10-14', 'Evening session', 85, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-08-03 00:00:00', '2026-10-17 08:33:13'),
(178, 3, 16, 1, '2026-10-12', 'Night practice', 87, 9.57, NULL, 'vigorous', NULL, 'public', NULL, '2026-08-01 00:00:00', '2026-10-17 08:33:13'),
(179, 3, 16, 13, '2026-10-17', 'Evening session', 65, 10.05, NULL, 'light', NULL, 'tenant', NULL, '2026-07-31 00:00:00', '2026-10-17 08:33:13'),
(180, 3, 16, 4, '2026-10-15', 'Afternoon run', 44, 5.35, NULL, 'light', NULL, 'private', NULL, '2026-07-29 00:00:00', '2026-10-17 08:33:13'),
(181, 3, 16, 10, '2026-10-13', 'Lunch training', 42, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-07-27 00:00:00', '2026-10-17 08:33:13'),
(182, 3, 16, 1, '2026-10-17', 'Evening training', 96, 9.30, NULL, 'light', NULL, 'tenant', NULL, '2026-07-25 00:00:00', '2026-10-17 08:33:13'),
(183, 3, 16, 7, '2026-10-14', 'Afternoon practice', 31, 5.70, NULL, 'vigorous', NULL, 'public', NULL, '2026-07-24 00:00:00', '2026-10-17 08:33:13'),
(184, 3, 16, 10, '2026-10-17', 'Lunch session', 38, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-07-22 00:00:00', '2026-10-17 08:33:13'),
(185, 3, 16, 13, '2026-10-13', 'Morning training', 114, 10.88, NULL, 'moderate', NULL, 'private', NULL, '2026-07-21 00:00:00', '2026-10-17 08:33:13'),
(186, 3, 16, 1, '2026-10-16', 'Night training', 105, NULL, NULL, 'light', NULL, 'public', NULL, '2026-07-19 00:00:00', '2026-10-17 08:33:13'),
(187, 3, 16, 7, '2026-10-15', 'Night session', 117, 7.45, NULL, 'moderate', NULL, 'tenant', NULL, '2026-07-17 00:00:00', '2026-10-17 08:33:13'),
(188, 3, 16, 7, '2026-10-16', 'Morning practice', 70, 5.91, NULL, 'light', NULL, 'tenant', NULL, '2026-07-16 00:00:00', '2026-10-17 08:33:13'),
(189, 3, 16, 7, '2026-10-12', 'Night practice', 57, 9.23, NULL, 'vigorous', NULL, 'private', NULL, '2026-07-10 00:00:00', '2026-10-17 08:33:13'),
(190, 3, 16, 7, '2026-10-16', 'Lunch training', 61, 2.26, NULL, 'vigorous', NULL, 'public', NULL, '2026-07-09 00:00:00', '2026-10-17 08:33:13'),
(191, 3, 16, 13, '2026-10-13', 'Lunch practice', 33, NULL, NULL, 'light', NULL, 'public', NULL, '2026-07-08 00:00:00', '2026-10-17 08:33:13'),
(192, 3, 16, 10, '2026-10-13', 'Lunch practice', 91, 8.40, NULL, 'light', NULL, 'public', NULL, '2026-07-03 00:00:00', '2026-10-17 08:33:13'),
(193, 3, 16, 7, '2026-10-14', 'Evening training', 114, 11.22, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-07-02 00:00:00', '2026-10-17 08:33:13'),
(194, 3, 16, 4, '2026-10-13', 'Afternoon run', 80, NULL, NULL, 'light', NULL, 'private', NULL, '2026-06-29 00:00:00', '2026-10-17 08:33:13'),
(195, 3, 16, 13, '2026-10-16', 'Lunch session', 111, 8.53, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-28 00:00:00', '2026-10-17 08:33:13'),
(196, 3, 16, 10, '2026-10-13', 'Lunch training', 116, 13.71, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-27 00:00:00', '2026-10-17 08:33:13'),
(197, 3, 16, 13, '2026-10-13', 'Night session', 100, 7.95, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-25 00:00:00', '2026-10-17 08:33:13'),
(198, 3, 16, 13, '2026-10-11', 'Evening session', 117, 5.62, NULL, 'light', NULL, 'private', NULL, '2026-06-24 00:00:00', '2026-10-17 08:33:13'),
(199, 3, 16, 1, '2026-10-17', 'Lunch practice', 63, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-23 00:00:00', '2026-10-17 08:33:13'),
(200, 3, 16, 13, '2026-10-17', 'Evening training', 35, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-21 00:00:00', '2026-10-17 08:33:13'),
(201, 3, 16, 13, '2026-10-14', 'Lunch practice', 66, 12.51, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-06-20 00:00:00', '2026-10-17 08:33:13'),
(202, 3, 16, 10, '2026-10-15', 'Night practice', 51, 7.85, NULL, 'light', NULL, 'public', NULL, '2026-06-19 00:00:00', '2026-10-17 08:33:13'),
(203, 3, 16, 10, '2026-10-14', 'Morning run', 100, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-18 00:00:00', '2026-10-17 08:33:13'),
(204, 3, 16, 4, '2026-10-17', 'Night practice', 36, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-06-17 00:00:00', '2026-10-17 08:33:13'),
(205, 3, 16, 7, '2026-10-11', 'Evening workout', 96, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-06-13 00:00:00', '2026-10-17 08:33:13'),
(206, 3, 16, 1, '2026-10-16', 'Lunch workout', 51, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-06-08 00:00:00', '2026-10-17 08:33:13'),
(207, 3, 16, 4, '2026-10-15', 'Lunch run', 64, 11.26, NULL, 'light', NULL, 'public', NULL, '2026-06-07 00:00:00', '2026-10-17 08:33:13'),
(208, 3, 16, 13, '2026-10-11', 'Morning workout', 107, 1.47, NULL, 'moderate', NULL, 'public', NULL, '2026-06-06 00:00:00', '2026-10-17 08:33:13'),
(209, 3, 16, 10, '2026-10-11', 'Night practice', 68, 9.51, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-04 00:00:00', '2026-10-17 08:33:13'),
(210, 3, 16, 7, '2026-10-13', 'Morning training', 84, 14.58, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-03 00:00:00', '2026-10-17 08:33:13'),
(211, 3, 16, 4, '2026-10-15', 'Night run', 36, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-01 00:00:00', '2026-10-17 08:33:13'),
(212, 3, 16, 7, '2026-10-17', 'Lunch training', 46, NULL, NULL, 'light', NULL, 'private', NULL, '2026-05-28 00:00:00', '2026-10-17 08:33:13'),
(213, 3, 16, 13, '2026-10-15', 'Morning training', 56, 1.13, NULL, 'light', NULL, 'public', NULL, '2026-05-27 00:00:00', '2026-10-17 08:33:13'),
(214, 3, 16, 7, '2026-10-13', 'Night session', 44, 3.53, NULL, 'light', NULL, 'public', NULL, '2026-05-26 00:00:00', '2026-10-17 08:33:13'),
(215, 3, 16, 10, '2026-10-11', 'Night practice', 33, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-25 00:00:00', '2026-10-17 08:33:13'),
(216, 3, 16, 10, '2026-10-13', 'Night training', 62, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-05-24 00:00:00', '2026-10-17 08:33:13'),
(217, 3, 16, 10, '2026-10-12', 'Night training', 108, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-05-23 00:00:00', '2026-10-17 08:33:13'),
(218, 3, 16, 1, '2026-10-13', 'Morning run', 40, 4.36, NULL, 'moderate', NULL, 'tenant', NULL, '2026-05-22 00:00:00', '2026-10-17 08:33:13'),
(219, 3, 16, 7, '2026-10-17', 'Morning run', 48, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-05-21 00:00:00', '2026-10-17 08:33:13'),
(220, 3, 16, 1, '2026-10-16', 'Lunch workout', 100, NULL, NULL, 'light', NULL, 'public', NULL, '2026-05-20 00:00:00', '2026-10-17 08:33:13'),
(221, 3, 16, 13, '2026-10-12', 'Night training', 21, 3.24, NULL, 'light', NULL, 'public', NULL, '2026-05-19 00:00:00', '2026-10-17 08:33:13'),
(222, 3, 16, 10, '2026-10-16', 'Afternoon training', 96, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-05-18 00:00:00', '2026-10-17 08:33:13'),
(223, 3, 16, 13, '2026-10-12', 'Morning workout', 70, 1.61, NULL, 'moderate', NULL, 'private', NULL, '2026-05-17 00:00:00', '2026-10-17 08:33:13'),
(224, 3, 16, 4, '2026-10-13', 'Night run', 80, 5.92, NULL, 'light', NULL, 'public', NULL, '2026-05-16 00:00:00', '2026-10-17 08:33:13'),
(225, 3, 16, 13, '2026-10-15', 'Morning training', 80, 12.08, NULL, 'moderate', NULL, 'tenant', NULL, '2026-05-15 00:00:00', '2026-10-17 08:33:13'),
(226, 3, 16, 7, '2026-10-15', 'Afternoon training', 110, 6.53, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-10 00:00:00', '2026-10-17 08:33:13'),
(227, 3, 16, 4, '2026-10-15', 'Lunch session', 84, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-09 00:00:00', '2026-10-17 08:33:13'),
(228, 3, 16, 1, '2026-10-14', 'Evening session', 20, 14.43, NULL, 'moderate', NULL, 'public', NULL, '2026-05-08 00:00:00', '2026-10-17 08:33:13'),
(229, 3, 16, 1, '2026-10-12', 'Lunch training', 32, 14.42, NULL, 'moderate', NULL, 'public', NULL, '2026-05-06 00:00:00', '2026-10-17 08:33:13'),
(230, 3, 16, 7, '2026-10-14', 'Evening session', 72, 1.70, NULL, 'light', NULL, 'private', NULL, '2026-05-05 00:00:00', '2026-10-17 08:33:13'),
(231, 3, 16, 7, '2026-10-12', 'Evening run', 105, 11.69, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-04 00:00:00', '2026-10-17 08:33:13'),
(232, 3, 16, 4, '2026-10-15', 'Afternoon training', 26, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-04-29 00:00:00', '2026-10-17 08:33:13'),
(233, 3, 16, 4, '2026-10-13', 'Morning session', 107, 14.69, NULL, 'light', NULL, 'tenant', NULL, '2026-04-27 00:00:00', '2026-10-17 08:33:13'),
(234, 3, 16, 7, '2026-10-11', 'Evening session', 88, NULL, NULL, 'light', NULL, 'private', NULL, '2026-04-21 00:00:00', '2026-10-17 08:33:13'),
(235, 3, 16, 1, '2026-10-14', 'Afternoon session', 27, NULL, NULL, 'light', NULL, 'public', NULL, '2026-04-20 00:00:00', '2026-10-17 08:33:13'),
(236, 3, 16, 10, '2026-10-17', 'Evening training', 37, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-04-18 00:00:00', '2026-10-17 08:33:13'),
(237, 3, 16, 7, '2026-10-17', 'Morning workout', 58, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-04-17 00:00:00', '2026-10-17 08:33:13'),
(238, 3, 16, 13, '2026-10-12', 'Lunch workout', 68, 7.00, NULL, 'light', NULL, 'private', NULL, '2026-04-16 00:00:00', '2026-10-17 08:33:13'),
(239, 3, 16, 7, '2026-10-12', 'Afternoon practice', 41, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-04-15 00:00:00', '2026-10-17 08:33:13'),
(240, 3, 16, 1, '2026-10-13', 'Evening practice', 25, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-04-11 00:00:00', '2026-10-17 08:33:13'),
(241, 3, 16, 13, '2026-10-11', 'Evening training', 85, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-04-10 00:00:00', '2026-10-17 08:33:13'),
(242, 3, 16, 13, '2026-10-12', 'Night session', 41, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-04-09 00:00:00', '2026-10-17 08:33:13'),
(243, 3, 16, 7, '2026-10-15', 'Evening practice', 103, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-04-07 00:00:00', '2026-10-17 08:33:13'),
(244, 3, 16, 1, '2026-10-11', 'Morning session', 119, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-04-06 00:00:00', '2026-10-17 08:33:13'),
(245, 3, 16, 7, '2026-10-11', 'Afternoon training', 33, 12.28, NULL, 'vigorous', NULL, 'private', NULL, '2026-04-04 00:00:00', '2026-10-17 08:33:13'),
(246, 3, 16, 4, '2026-10-11', 'Afternoon practice', 51, 4.14, NULL, 'light', NULL, 'private', NULL, '2026-04-03 00:00:00', '2026-10-17 08:33:13'),
(247, 3, 16, 4, '2026-10-13', 'Night practice', 101, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-04-01 00:00:00', '2026-10-17 08:33:13'),
(248, 3, 16, 13, '2026-10-13', 'Night workout', 44, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-03-31 00:00:00', '2026-10-17 08:33:13'),
(249, 3, 16, 13, '2026-10-14', 'Afternoon session', 46, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-03-29 00:00:00', '2026-10-17 08:33:13'),
(250, 3, 16, 13, '2026-10-17', 'Afternoon workout', 85, 11.74, NULL, 'vigorous', NULL, 'public', NULL, '2026-03-28 00:00:00', '2026-10-17 08:33:13'),
(251, 3, 16, 7, '2026-10-15', 'Lunch workout', 40, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-03-27 00:00:00', '2026-10-17 08:33:13'),
(252, 3, 16, 1, '2026-10-16', 'Night session', 100, NULL, NULL, 'light', NULL, 'private', NULL, '2026-03-25 00:00:00', '2026-10-17 08:33:13'),
(253, 3, 16, 1, '2026-10-17', 'Night practice', 96, NULL, NULL, 'light', NULL, 'private', NULL, '2026-03-24 00:00:00', '2026-10-17 08:33:13'),
(254, 3, 16, 13, '2026-10-11', 'Evening training', 102, 5.94, NULL, 'vigorous', NULL, 'public', NULL, '2026-03-20 00:00:00', '2026-10-17 08:33:13'),
(255, 3, 16, 1, '2026-10-16', 'Evening workout', 103, 12.44, NULL, 'vigorous', NULL, 'public', NULL, '2026-03-19 00:00:00', '2026-10-17 08:33:13'),
(256, 3, 16, 4, '2026-10-15', 'Afternoon practice', 47, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-03-17 00:00:00', '2026-10-17 08:33:13'),
(257, 3, 16, 10, '2026-10-11', 'Lunch training', 77, 2.80, NULL, 'vigorous', NULL, 'private', NULL, '2026-03-16 00:00:00', '2026-10-17 08:33:13'),
(258, 3, 16, 1, '2026-10-12', 'Afternoon training', 84, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-03-15 00:00:00', '2026-10-17 08:33:13'),
(259, 3, 16, 7, '2026-10-17', 'Evening workout', 75, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-03-14 00:00:00', '2026-10-17 08:33:13'),
(260, 3, 16, 13, '2026-10-12', 'Morning session', 107, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-03-13 00:00:00', '2026-10-17 08:33:13'),
(261, 3, 16, 13, '2026-10-13', 'Afternoon practice', 38, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-03-12 00:00:00', '2026-10-17 08:33:13'),
(262, 3, 16, 1, '2026-10-12', 'Evening workout', 78, 15.92, NULL, 'light', NULL, 'tenant', NULL, '2026-03-11 00:00:00', '2026-10-17 08:33:13'),
(263, 3, 16, 10, '2026-10-17', 'Morning training', 116, 8.32, NULL, 'moderate', NULL, 'tenant', NULL, '2026-03-09 00:00:00', '2026-10-17 08:33:13'),
(264, 3, 16, 1, '2026-10-17', 'Morning workout', 61, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-03-08 00:00:00', '2026-10-17 08:33:13'),
(265, 3, 16, 1, '2026-10-11', 'Night practice', 107, 4.73, NULL, 'moderate', NULL, 'public', NULL, '2026-03-06 00:00:00', '2026-10-17 08:33:13'),
(266, 3, 16, 1, '2026-10-15', 'Night training', 82, NULL, NULL, 'light', NULL, 'public', NULL, '2026-03-05 00:00:00', '2026-10-17 08:33:13'),
(267, 3, 16, 1, '2026-10-11', 'Night practice', 115, 15.93, NULL, 'light', NULL, 'private', NULL, '2026-03-04 00:00:00', '2026-10-17 08:33:13'),
(268, 3, 16, 10, '2026-10-14', 'Morning workout', 70, 12.21, NULL, 'light', NULL, 'public', NULL, '2026-03-01 00:00:00', '2026-10-17 08:33:13'),
(269, 3, 16, 1, '2026-10-13', 'Afternoon run', 105, 4.88, NULL, 'moderate', NULL, 'private', NULL, '2026-02-28 00:00:00', '2026-10-17 08:33:13'),
(270, 3, 16, 13, '2026-10-13', 'Lunch practice', 45, 8.69, NULL, 'moderate', NULL, 'tenant', NULL, '2026-02-27 00:00:00', '2026-10-17 08:33:13'),
(271, 3, 16, 1, '2026-10-16', 'Evening session', 99, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-02-25 00:00:00', '2026-10-17 08:33:13'),
(272, 3, 16, 13, '2026-10-15', 'Evening run', 70, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-02-23 00:00:00', '2026-10-17 08:33:13'),
(273, 3, 16, 7, '2026-10-16', 'Night practice', 43, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-02-21 00:00:00', '2026-10-17 08:33:13'),
(274, 3, 16, 13, '2026-10-17', 'Lunch practice', 105, NULL, NULL, 'light', NULL, 'private', NULL, '2026-02-20 00:00:00', '2026-10-17 08:33:13'),
(275, 3, 16, 7, '2026-10-15', 'Afternoon session', 50, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-02-18 00:00:00', '2026-10-17 08:33:13'),
(276, 3, 16, 13, '2026-10-14', 'Evening practice', 101, NULL, NULL, 'light', NULL, 'private', NULL, '2026-02-17 00:00:00', '2026-10-17 08:33:13'),
(277, 3, 16, 7, '2026-10-12', 'Lunch practice', 75, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-02-12 00:00:00', '2026-10-17 08:33:13'),
(278, 3, 16, 10, '2026-10-17', 'Lunch workout', 41, 1.10, NULL, 'vigorous', NULL, 'private', NULL, '2026-02-11 00:00:00', '2026-10-17 08:33:13'),
(279, 3, 16, 1, '2026-10-11', 'Lunch practice', 74, 15.48, NULL, 'light', NULL, 'tenant', NULL, '2026-02-09 00:00:00', '2026-10-17 08:33:13'),
(280, 3, 16, 10, '2026-10-13', 'Night training', 90, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-02-08 00:00:00', '2026-10-17 08:33:13'),
(281, 3, 16, 13, '2026-10-15', 'Morning session', 86, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-02-07 00:00:00', '2026-10-17 08:33:13'),
(282, 3, 16, 4, '2026-10-11', 'Morning session', 46, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-02-05 00:00:00', '2026-10-17 08:33:13'),
(283, 3, 16, 13, '2026-10-16', 'Afternoon run', 51, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-02-02 00:00:00', '2026-10-17 08:33:13'),
(284, 3, 16, 7, '2026-10-13', 'Evening workout', 33, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-02-01 00:00:00', '2026-10-17 08:33:13'),
(285, 3, 16, 13, '2026-10-16', 'Lunch practice', 48, 4.65, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-31 00:00:00', '2026-10-17 08:33:13'),
(286, 3, 16, 1, '2026-10-14', 'Lunch training', 96, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-01-30 00:00:00', '2026-10-17 08:33:13'),
(287, 3, 16, 13, '2026-10-14', 'Evening training', 24, 10.76, NULL, 'moderate', NULL, 'public', NULL, '2026-01-29 00:00:00', '2026-10-17 08:33:13'),
(288, 3, 16, 7, '2026-10-11', 'Lunch workout', 56, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-28 00:00:00', '2026-10-17 08:33:13'),
(289, 3, 16, 4, '2026-10-17', 'Night training', 53, 8.95, NULL, 'moderate', NULL, 'private', NULL, '2026-01-26 00:00:00', '2026-10-17 08:33:13'),
(290, 3, 16, 4, '2026-10-12', 'Lunch training', 27, NULL, NULL, 'light', NULL, 'public', NULL, '2026-01-25 00:00:00', '2026-10-17 08:33:13'),
(291, 3, 16, 7, '2026-10-11', 'Night run', 51, NULL, NULL, 'light', NULL, 'private', NULL, '2026-01-24 00:00:00', '2026-10-17 08:33:13'),
(292, 3, 16, 7, '2026-10-15', 'Lunch run', 85, 7.89, NULL, 'moderate', NULL, 'public', NULL, '2026-01-23 00:00:00', '2026-10-17 08:33:13'),
(293, 3, 16, 13, '2026-10-12', 'Evening workout', 104, 8.12, NULL, 'moderate', NULL, 'tenant', NULL, '2026-01-21 00:00:00', '2026-10-17 08:33:13'),
(294, 3, 16, 7, '2026-10-11', 'Night practice', 72, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-01-19 00:00:00', '2026-10-17 08:33:13'),
(295, 3, 16, 7, '2026-10-15', 'Lunch workout', 36, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-01-18 00:00:00', '2026-10-17 08:33:13'),
(296, 3, 16, 7, '2026-10-17', 'Evening run', 103, 8.64, NULL, 'light', NULL, 'private', NULL, '2026-01-17 00:00:00', '2026-10-17 08:33:13'),
(297, 3, 16, 7, '2026-10-15', 'Lunch training', 111, 7.88, NULL, 'moderate', NULL, 'tenant', NULL, '2026-01-16 00:00:00', '2026-10-17 08:33:13'),
(298, 3, 16, 13, '2026-10-16', 'Evening workout', 40, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-14 00:00:00', '2026-10-17 08:33:13'),
(299, 3, 16, 13, '2026-10-17', 'Night session', 53, 11.00, NULL, 'moderate', NULL, 'tenant', NULL, '2026-01-13 00:00:00', '2026-10-17 08:33:13'),
(300, 3, 16, 1, '2026-10-12', 'Morning session', 98, NULL, NULL, 'light', NULL, 'private', NULL, '2026-01-12 00:00:00', '2026-10-17 08:33:13'),
(301, 3, 16, 4, '2026-10-14', 'Afternoon practice', 73, 4.18, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-11 00:00:00', '2026-10-17 08:33:13'),
(302, 3, 16, 1, '2026-10-14', 'Night session', 56, 9.96, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-10 00:00:00', '2026-10-17 08:33:13'),
(303, 3, 16, 7, '2026-10-12', 'Lunch workout', 50, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-01-08 00:00:00', '2026-10-17 08:33:13'),
(304, 3, 16, 4, '2026-10-16', 'Night training', 21, 9.72, NULL, 'light', NULL, 'public', NULL, '2026-01-07 00:00:00', '2026-10-17 08:33:13'),
(305, 3, 16, 7, '2026-10-11', 'Lunch training', 23, 14.34, NULL, 'light', NULL, 'tenant', NULL, '2026-01-05 00:00:00', '2026-10-17 08:33:13'),
(306, 3, 16, 10, '2026-10-17', 'Morning run', 54, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-01-04 00:00:00', '2026-10-17 08:33:13'),
(307, 3, 16, 10, '2026-10-12', 'Evening workout', 69, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-01-03 00:00:00', '2026-10-17 08:33:13'),
(308, 3, 16, 1, '2026-10-16', 'Afternoon run', 109, NULL, NULL, 'light', NULL, 'public', NULL, '2026-01-02 00:00:00', '2026-10-17 08:33:13'),
(309, 3, 16, 7, '2026-10-17', 'Lunch session', 65, NULL, NULL, 'light', NULL, 'private', NULL, '2026-01-01 00:00:00', '2026-10-17 08:33:13'),
(310, 3, 16, 10, '2026-10-13', 'Morning practice', 74, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-12-31 00:00:00', '2026-10-17 08:33:13'),
(311, 3, 16, 13, '2026-10-11', 'Lunch session', 63, NULL, NULL, 'light', NULL, 'private', NULL, '2025-12-30 00:00:00', '2026-10-17 08:33:13'),
(312, 3, 16, 7, '2026-10-13', 'Evening run', 97, 7.23, NULL, 'moderate', NULL, 'public', NULL, '2025-12-29 00:00:00', '2026-10-17 08:33:13'),
(313, 3, 16, 1, '2026-10-13', 'Afternoon practice', 104, 3.71, NULL, 'light', NULL, 'public', NULL, '2025-12-27 00:00:00', '2026-10-17 08:33:13'),
(314, 3, 16, 4, '2026-10-11', 'Afternoon workout', 58, 1.82, NULL, 'moderate', NULL, 'tenant', NULL, '2025-12-25 00:00:00', '2026-10-17 08:33:13'),
(315, 3, 16, 7, '2026-10-17', 'Morning training', 105, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-12-24 00:00:00', '2026-10-17 08:33:13'),
(316, 3, 16, 4, '2026-10-13', 'Morning practice', 65, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-12-21 00:00:00', '2026-10-17 08:33:13'),
(317, 3, 16, 10, '2026-10-12', 'Morning session', 67, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-12-20 00:00:00', '2026-10-17 08:33:13'),
(318, 3, 16, 13, '2026-10-11', 'Lunch practice', 95, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2025-12-19 00:00:00', '2026-10-17 08:33:13'),
(319, 3, 16, 7, '2026-10-15', 'Morning session', 48, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2025-12-18 00:00:00', '2026-10-17 08:33:13'),
(320, 3, 16, 7, '2026-10-16', 'Morning workout', 43, 8.41, NULL, 'moderate', NULL, 'private', NULL, '2025-12-17 00:00:00', '2026-10-17 08:33:13'),
(321, 3, 16, 4, '2026-10-13', 'Afternoon run', 82, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-12-16 00:00:00', '2026-10-17 08:33:13'),
(322, 3, 16, 13, '2026-10-12', 'Evening session', 81, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-12-15 00:00:00', '2026-10-17 08:33:13'),
(323, 3, 16, 10, '2026-10-17', 'Lunch workout', 62, 13.05, NULL, 'light', NULL, 'tenant', NULL, '2025-12-14 00:00:00', '2026-10-17 08:33:13'),
(324, 3, 16, 13, '2026-10-13', 'Night training', 82, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-12-13 00:00:00', '2026-10-17 08:33:13'),
(325, 3, 16, 10, '2026-10-14', 'Morning practice', 34, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-12-12 00:00:00', '2026-10-17 08:33:13'),
(326, 3, 16, 4, '2026-10-17', 'Lunch practice', 55, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2025-12-11 00:00:00', '2026-10-17 08:33:13'),
(327, 3, 16, 10, '2026-10-17', 'Night workout', 101, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-12-10 00:00:00', '2026-10-17 08:33:13'),
(328, 3, 16, 10, '2026-10-11', 'Evening practice', 63, 5.76, NULL, 'moderate', NULL, 'private', NULL, '2025-12-05 00:00:00', '2026-10-17 08:33:13'),
(329, 3, 16, 10, '2026-10-15', 'Night run', 49, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-12-04 00:00:00', '2026-10-17 08:33:13'),
(330, 3, 16, 4, '2026-10-17', 'Lunch workout', 74, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-12-03 00:00:00', '2026-10-17 08:33:13'),
(331, 3, 16, 4, '2026-10-14', 'Lunch training', 45, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-12-02 00:00:00', '2026-10-17 08:33:13'),
(332, 3, 16, 13, '2026-10-15', 'Afternoon run', 84, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-11-30 00:00:00', '2026-10-17 08:33:13'),
(333, 3, 16, 1, '2026-10-17', 'Evening run', 102, 11.24, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-11-28 00:00:00', '2026-10-17 08:33:13'),
(334, 3, 16, 4, '2026-10-16', 'Night run', 59, 6.21, NULL, 'light', NULL, 'private', NULL, '2025-11-26 00:00:00', '2026-10-17 08:33:13'),
(335, 3, 16, 13, '2026-10-12', 'Morning session', 33, 3.76, NULL, 'light', NULL, 'private', NULL, '2025-11-22 00:00:00', '2026-10-17 08:33:13'),
(336, 3, 16, 1, '2026-10-15', 'Night workout', 30, 1.67, NULL, 'moderate', NULL, 'public', NULL, '2025-11-20 00:00:00', '2026-10-17 08:33:13'),
(337, 3, 16, 13, '2026-10-13', 'Afternoon practice', 36, 14.81, NULL, 'vigorous', NULL, 'private', NULL, '2025-11-19 00:00:00', '2026-10-17 08:33:13'),
(338, 3, 16, 13, '2026-10-17', 'Afternoon practice', 49, 2.52, NULL, 'vigorous', NULL, 'private', NULL, '2025-11-17 00:00:00', '2026-10-17 08:33:13'),
(339, 3, 16, 10, '2026-10-14', 'Evening workout', 78, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-11-15 00:00:00', '2026-10-17 08:33:13'),
(340, 3, 16, 1, '2026-10-15', 'Afternoon run', 75, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-11-14 00:00:00', '2026-10-17 08:33:13'),
(341, 3, 16, 13, '2026-10-17', 'Evening run', 117, 5.47, NULL, 'vigorous', NULL, 'public', NULL, '2025-11-13 00:00:00', '2026-10-17 08:33:13'),
(342, 3, 16, 13, '2026-10-14', 'Lunch session', 58, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-11-11 00:00:00', '2026-10-17 08:33:13'),
(343, 3, 16, 10, '2026-10-15', 'Night session', 51, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-11-10 00:00:00', '2026-10-17 08:33:13'),
(344, 3, 16, 7, '2026-10-17', 'Evening run', 29, 11.49, NULL, 'moderate', NULL, 'tenant', NULL, '2025-11-08 00:00:00', '2026-10-17 08:33:13'),
(345, 3, 16, 7, '2026-10-16', 'Evening practice', 29, 6.24, NULL, 'light', NULL, 'public', NULL, '2025-11-07 00:00:00', '2026-10-17 08:33:13'),
(346, 3, 16, 1, '2026-10-11', 'Morning training', 41, 15.44, NULL, 'light', NULL, 'private', NULL, '2025-11-05 00:00:00', '2026-10-17 08:33:13'),
(347, 3, 16, 4, '2026-10-17', 'Lunch workout', 72, 14.70, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-11-04 00:00:00', '2026-10-17 08:33:13'),
(348, 3, 16, 13, '2026-10-13', 'Lunch session', 21, NULL, NULL, 'light', NULL, 'private', NULL, '2025-11-03 00:00:00', '2026-10-17 08:33:13'),
(349, 3, 16, 10, '2026-10-13', 'Afternoon training', 101, 8.33, NULL, 'moderate', NULL, 'public', NULL, '2025-11-01 00:00:00', '2026-10-17 08:33:13'),
(350, 3, 16, 10, '2026-10-15', 'Evening workout', 83, 6.04, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-10-29 00:00:00', '2026-10-17 08:33:13'),
(351, 3, 16, 1, '2026-10-16', 'Afternoon session', 94, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-10-26 00:00:00', '2026-10-17 08:33:13'),
(352, 3, 16, 13, '2026-10-14', 'Evening session', 88, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-10-25 00:00:00', '2026-10-17 08:33:13'),
(353, 3, 16, 7, '2026-10-15', 'Evening run', 109, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-10-24 00:00:00', '2026-10-17 08:33:13'),
(354, 3, 16, 13, '2026-10-17', 'Lunch session', 44, 2.40, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-10-23 00:00:00', '2026-10-17 08:33:13'),
(355, 3, 16, 10, '2026-10-16', 'Morning run', 25, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-10-22 00:00:00', '2026-10-17 08:33:13'),
(356, 3, 16, 7, '2026-10-17', 'Lunch workout', 76, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-10-21 00:00:00', '2026-10-17 08:33:13'),
(357, 3, 16, 4, '2026-10-12', 'Night run', 119, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-10-18 00:00:00', '2026-10-17 08:33:13'),
(358, 3, 16, 4, '2026-10-14', 'Lunch practice', 102, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-10-17 00:00:00', '2026-10-17 08:33:13'),
(359, 3, 16, 7, '2026-10-16', 'Evening workout', 28, NULL, NULL, 'light', NULL, 'private', NULL, '2025-10-16 00:00:00', '2026-10-17 08:33:13'),
(360, 3, 16, 1, '2026-10-12', 'Lunch workout', 61, 1.29, NULL, 'light', NULL, 'tenant', NULL, '2025-10-15 00:00:00', '2026-10-17 08:33:13'),
(361, 3, 16, 1, '2026-10-11', 'Lunch run', 116, 7.16, NULL, 'light', NULL, 'private', NULL, '2025-10-14 00:00:00', '2026-10-17 08:33:13'),
(362, 3, 16, 10, '2026-10-16', 'Afternoon practice', 32, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-10-13 00:00:00', '2026-10-17 08:33:13'),
(363, 3, 16, 1, '2026-10-14', 'Morning run', 27, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-10-12 00:00:00', '2026-10-17 08:33:13'),
(364, 3, 16, 4, '2026-10-17', 'Evening run', 88, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-10-11 00:00:00', '2026-10-17 08:33:13'),
(365, 3, 16, 13, '2026-10-12', 'Morning training', 65, NULL, NULL, 'light', NULL, 'public', NULL, '2025-10-09 00:00:00', '2026-10-17 08:33:13'),
(366, 3, 16, 4, '2026-10-12', 'Lunch run', 58, 7.88, NULL, 'light', NULL, 'private', NULL, '2025-10-05 00:00:00', '2026-10-17 08:33:13'),
(367, 3, 16, 7, '2026-10-13', 'Morning training', 107, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-10-04 00:00:00', '2026-10-17 08:33:13'),
(390, 3, 16, 13, '2026-10-14', 'Afternoon practice', 23, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-10-01 00:00:00', '2026-10-17 08:33:13');
INSERT INTO `activity_logs` (`activity_log_id`, `tenant_id`, `user_id`, `activity_type_id`, `activity_date`, `title`, `duration_minutes`, `distance_km`, `steps`, `intensity`, `notes`, `visibility`, `location_id`, `created_at`, `updated_at`) VALUES
(391, 3, 16, 13, '2026-10-12', 'Night workout', 49, 12.53, NULL, 'moderate', NULL, 'public', NULL, '2026-09-30 00:00:00', '2026-10-17 08:33:13'),
(392, 3, 16, 1, '2026-10-17', 'Morning session', 29, 8.54, NULL, 'light', NULL, 'public', NULL, '2026-09-29 00:00:00', '2026-10-17 08:33:13'),
(393, 3, 16, 10, '2026-10-14', 'Evening training', 70, NULL, NULL, 'light', NULL, 'private', NULL, '2026-09-28 00:00:00', '2026-10-17 08:33:13'),
(394, 3, 16, 10, '2026-10-16', 'Night workout', 41, 8.78, NULL, 'light', NULL, 'public', NULL, '2026-09-27 00:00:00', '2026-10-17 08:33:13'),
(395, 3, 16, 4, '2026-10-15', 'Morning run', 37, 4.76, NULL, 'moderate', NULL, 'private', NULL, '2026-09-26 00:00:00', '2026-10-17 08:33:13'),
(396, 3, 16, 4, '2026-10-14', 'Afternoon run', 27, 12.37, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-25 00:00:00', '2026-10-17 08:33:13'),
(397, 3, 16, 10, '2026-10-11', 'Morning training', 45, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-23 00:00:00', '2026-10-17 08:33:13'),
(398, 3, 16, 10, '2026-10-14', 'Morning session', 72, 1.65, NULL, 'moderate', NULL, 'private', NULL, '2026-09-22 00:00:00', '2026-10-17 08:33:13'),
(399, 3, 16, 4, '2026-10-11', 'Afternoon run', 104, NULL, NULL, 'light', NULL, 'public', NULL, '2026-09-21 00:00:00', '2026-10-17 08:33:13'),
(400, 3, 16, 1, '2026-10-11', 'Night session', 65, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-09-20 00:00:00', '2026-10-17 08:33:13'),
(401, 3, 16, 7, '2026-10-13', 'Lunch training', 65, 10.28, NULL, 'light', NULL, 'public', NULL, '2026-09-17 00:00:00', '2026-10-17 08:33:13'),
(402, 3, 16, 4, '2026-10-13', 'Night practice', 59, 9.72, NULL, 'light', NULL, 'tenant', NULL, '2026-09-14 00:00:00', '2026-10-17 08:33:13'),
(403, 3, 16, 1, '2026-10-15', 'Morning run', 88, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-09-13 00:00:00', '2026-10-17 08:33:13'),
(404, 3, 16, 13, '2026-10-11', 'Afternoon training', 27, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-12 00:00:00', '2026-10-17 08:33:13'),
(405, 3, 16, 13, '2026-10-12', 'Morning session', 88, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-09-11 00:00:00', '2026-10-17 08:33:13'),
(406, 3, 16, 1, '2026-10-12', 'Morning run', 103, 8.18, NULL, 'moderate', NULL, 'tenant', NULL, '2026-09-10 00:00:00', '2026-10-17 08:33:13'),
(407, 3, 16, 7, '2026-10-13', 'Night workout', 46, 15.54, NULL, 'vigorous', NULL, 'private', NULL, '2026-09-09 00:00:00', '2026-10-17 08:33:13'),
(408, 3, 16, 4, '2026-10-17', 'Evening practice', 51, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-09-07 00:00:00', '2026-10-17 08:33:13'),
(409, 3, 16, 7, '2026-10-15', 'Lunch run', 106, 7.87, NULL, 'vigorous', NULL, 'public', NULL, '2026-09-01 00:00:00', '2026-10-17 08:33:13'),
(410, 3, 16, 13, '2026-10-12', 'Evening run', 59, 10.26, NULL, 'vigorous', NULL, 'private', NULL, '2026-08-30 00:00:00', '2026-10-17 08:33:13'),
(411, 3, 16, 1, '2026-10-15', 'Morning training', 85, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-08-29 00:00:00', '2026-10-17 08:33:13'),
(412, 3, 16, 1, '2026-10-12', 'Lunch run', 66, 1.39, NULL, 'moderate', NULL, 'private', NULL, '2026-08-28 00:00:00', '2026-10-17 08:33:13'),
(413, 3, 16, 4, '2026-10-17', 'Afternoon training', 36, 3.42, NULL, 'moderate', NULL, 'tenant', NULL, '2026-08-24 00:00:00', '2026-10-17 08:33:13'),
(414, 3, 16, 1, '2026-10-14', 'Afternoon workout', 62, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-08-21 00:00:00', '2026-10-17 08:33:13'),
(415, 3, 16, 13, '2026-10-14', 'Lunch practice', 28, 8.05, NULL, 'moderate', NULL, 'private', NULL, '2026-08-19 00:00:00', '2026-10-17 08:33:13'),
(416, 3, 16, 4, '2026-10-11', 'Night practice', 117, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-08-13 00:00:00', '2026-10-17 08:33:13'),
(417, 3, 16, 1, '2026-10-11', 'Night session', 73, 14.55, NULL, 'light', NULL, 'private', NULL, '2026-08-12 00:00:00', '2026-10-17 08:33:13'),
(418, 3, 16, 13, '2026-10-11', 'Morning session', 49, 6.46, NULL, 'vigorous', NULL, 'private', NULL, '2026-08-11 00:00:00', '2026-10-17 08:33:13'),
(419, 3, 16, 4, '2026-10-12', 'Lunch training', 84, NULL, NULL, 'light', NULL, 'private', NULL, '2026-08-10 00:00:00', '2026-10-17 08:33:13'),
(420, 3, 16, 13, '2026-10-15', 'Morning session', 82, NULL, NULL, 'light', NULL, 'public', NULL, '2026-08-09 00:00:00', '2026-10-17 08:33:13'),
(421, 3, 16, 13, '2026-10-13', 'Night training', 86, NULL, NULL, 'light', NULL, 'public', NULL, '2026-08-08 00:00:00', '2026-10-17 08:33:13'),
(422, 3, 16, 10, '2026-10-12', 'Lunch run', 114, NULL, NULL, 'light', NULL, 'public', NULL, '2026-08-05 00:00:00', '2026-10-17 08:33:13'),
(423, 3, 16, 13, '2026-10-15', 'Lunch session', 95, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-07-31 00:00:00', '2026-10-17 08:33:13'),
(424, 3, 16, 1, '2026-10-15', 'Evening training', 26, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-07-30 00:00:00', '2026-10-17 08:33:13'),
(425, 3, 16, 7, '2026-10-13', 'Morning session', 57, 13.64, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-07-29 00:00:00', '2026-10-17 08:33:13'),
(426, 3, 16, 10, '2026-10-12', 'Evening run', 22, 6.84, NULL, 'vigorous', NULL, 'private', NULL, '2026-07-28 00:00:00', '2026-10-17 08:33:13'),
(427, 3, 16, 13, '2026-10-14', 'Night session', 91, 6.22, NULL, 'light', NULL, 'private', NULL, '2026-07-25 00:00:00', '2026-10-17 08:33:13'),
(428, 3, 16, 7, '2026-10-12', 'Evening run', 97, 4.23, NULL, 'moderate', NULL, 'private', NULL, '2026-07-24 00:00:00', '2026-10-17 08:33:13'),
(429, 3, 16, 4, '2026-10-13', 'Evening session', 102, 8.75, NULL, 'moderate', NULL, 'tenant', NULL, '2026-07-23 00:00:00', '2026-10-17 08:33:13'),
(430, 3, 16, 4, '2026-10-12', 'Afternoon session', 38, 9.66, NULL, 'moderate', NULL, 'private', NULL, '2026-07-20 00:00:00', '2026-10-17 08:33:13'),
(431, 3, 16, 10, '2026-10-16', 'Afternoon workout', 48, 6.65, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-07-17 00:00:00', '2026-10-17 08:33:13'),
(432, 3, 16, 4, '2026-10-15', 'Morning session', 98, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-07-14 00:00:00', '2026-10-17 08:33:13'),
(433, 3, 16, 7, '2026-10-11', 'Afternoon practice', 64, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-07-12 00:00:00', '2026-10-17 08:33:13'),
(434, 3, 16, 7, '2026-10-17', 'Lunch workout', 88, 5.86, NULL, 'moderate', NULL, 'public', NULL, '2026-07-11 00:00:00', '2026-10-17 08:33:13'),
(435, 3, 16, 13, '2026-10-15', 'Afternoon run', 24, 3.95, NULL, 'moderate', NULL, 'private', NULL, '2026-07-08 00:00:00', '2026-10-17 08:33:13'),
(436, 3, 16, 1, '2026-10-13', 'Night workout', 21, 10.78, NULL, 'moderate', NULL, 'private', NULL, '2026-07-07 00:00:00', '2026-10-17 08:33:13'),
(437, 3, 16, 10, '2026-10-17', 'Night workout', 99, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-07-06 00:00:00', '2026-10-17 08:33:13'),
(438, 3, 16, 1, '2026-10-14', 'Evening run', 59, 9.03, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-07-05 00:00:00', '2026-10-17 08:33:13'),
(439, 3, 16, 7, '2026-10-17', 'Afternoon session', 46, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-07-04 00:00:00', '2026-10-17 08:33:13'),
(440, 3, 16, 13, '2026-10-16', 'Afternoon session', 107, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-07-03 00:00:00', '2026-10-17 08:33:13'),
(441, 3, 16, 7, '2026-10-11', 'Lunch session', 70, 15.93, NULL, 'moderate', NULL, 'tenant', NULL, '2026-07-02 00:00:00', '2026-10-17 08:33:13'),
(442, 3, 16, 1, '2026-10-13', 'Night session', 42, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-30 00:00:00', '2026-10-17 08:33:13'),
(443, 3, 16, 10, '2026-10-13', 'Afternoon training', 65, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-28 00:00:00', '2026-10-17 08:33:13'),
(444, 3, 16, 4, '2026-10-17', 'Night run', 49, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-06-26 00:00:00', '2026-10-17 08:33:13'),
(445, 3, 16, 7, '2026-10-13', 'Morning training', 60, 10.19, NULL, 'light', NULL, 'public', NULL, '2026-06-24 00:00:00', '2026-10-17 08:33:13'),
(446, 3, 16, 7, '2026-10-11', 'Afternoon training', 61, 1.63, NULL, 'moderate', NULL, 'private', NULL, '2026-06-23 00:00:00', '2026-10-17 08:33:13'),
(447, 3, 16, 7, '2026-10-11', 'Evening session', 73, 12.05, NULL, 'light', NULL, 'private', NULL, '2026-06-21 00:00:00', '2026-10-17 08:33:13'),
(448, 3, 16, 13, '2026-10-14', 'Afternoon practice', 34, 13.37, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-20 00:00:00', '2026-10-17 08:33:13'),
(449, 3, 16, 13, '2026-10-17', 'Morning run', 90, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-06-18 00:00:00', '2026-10-17 08:33:13'),
(450, 3, 16, 10, '2026-10-11', 'Night session', 110, 6.15, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-06-17 00:00:00', '2026-10-17 08:33:13'),
(451, 3, 16, 7, '2026-10-15', 'Night practice', 24, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-06-14 00:00:00', '2026-10-17 08:33:13'),
(452, 3, 16, 13, '2026-10-11', 'Afternoon training', 45, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-06-13 00:00:00', '2026-10-17 08:33:13'),
(453, 3, 16, 10, '2026-10-13', 'Lunch workout', 38, 4.32, NULL, 'moderate', NULL, 'tenant', NULL, '2026-06-12 00:00:00', '2026-10-17 08:33:13'),
(454, 3, 16, 1, '2026-10-15', 'Lunch run', 72, NULL, NULL, 'light', NULL, 'public', NULL, '2026-06-11 00:00:00', '2026-10-17 08:33:13'),
(455, 3, 16, 7, '2026-10-11', 'Night training', 44, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-06-10 00:00:00', '2026-10-17 08:33:13'),
(456, 3, 16, 13, '2026-10-14', 'Night run', 64, 10.84, NULL, 'light', NULL, 'tenant', NULL, '2026-06-09 00:00:00', '2026-10-17 08:33:13'),
(457, 3, 16, 4, '2026-10-14', 'Morning training', 111, 2.49, NULL, 'light', NULL, 'tenant', NULL, '2026-06-07 00:00:00', '2026-10-17 08:33:13'),
(458, 3, 16, 1, '2026-10-16', 'Morning session', 75, NULL, NULL, 'light', NULL, 'public', NULL, '2026-06-06 00:00:00', '2026-10-17 08:33:13'),
(459, 3, 16, 4, '2026-10-14', 'Lunch session', 91, 1.09, NULL, 'light', NULL, 'public', NULL, '2026-06-04 00:00:00', '2026-10-17 08:33:13'),
(460, 3, 16, 10, '2026-10-12', 'Morning run', 109, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-06-03 00:00:00', '2026-10-17 08:33:13'),
(461, 3, 16, 10, '2026-10-13', 'Evening practice', 41, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-06-02 00:00:00', '2026-10-17 08:33:13'),
(462, 3, 16, 13, '2026-10-13', 'Lunch practice', 98, 3.14, NULL, 'light', NULL, 'tenant', NULL, '2026-05-30 00:00:00', '2026-10-17 08:33:13'),
(463, 3, 16, 1, '2026-10-16', 'Evening training', 32, 9.88, NULL, 'moderate', NULL, 'private', NULL, '2026-05-29 00:00:00', '2026-10-17 08:33:13'),
(464, 3, 16, 4, '2026-10-16', 'Morning session', 59, 15.65, NULL, 'moderate', NULL, 'private', NULL, '2026-05-28 00:00:00', '2026-10-17 08:33:13'),
(465, 3, 16, 4, '2026-10-13', 'Evening workout', 75, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-05-27 00:00:00', '2026-10-17 08:33:13'),
(466, 3, 16, 13, '2026-10-16', 'Morning session', 82, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-05-26 00:00:00', '2026-10-17 08:33:13'),
(467, 3, 16, 4, '2026-10-15', 'Night workout', 70, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-05-25 00:00:00', '2026-10-17 08:33:13'),
(468, 3, 16, 10, '2026-10-16', 'Lunch session', 47, 8.27, NULL, 'moderate', NULL, 'public', NULL, '2026-05-24 00:00:00', '2026-10-17 08:33:13'),
(469, 3, 16, 7, '2026-10-12', 'Night training', 41, 7.94, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-22 00:00:00', '2026-10-17 08:33:13'),
(470, 3, 16, 7, '2026-10-15', 'Night training', 85, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-05-21 00:00:00', '2026-10-17 08:33:13'),
(471, 3, 16, 4, '2026-10-12', 'Night workout', 112, 14.52, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-19 00:00:00', '2026-10-17 08:33:13'),
(472, 3, 16, 13, '2026-10-15', 'Afternoon workout', 50, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-05-18 00:00:00', '2026-10-17 08:33:13'),
(473, 3, 16, 7, '2026-10-14', 'Night run', 25, 12.79, NULL, 'moderate', NULL, 'tenant', NULL, '2026-05-16 00:00:00', '2026-10-17 08:33:13'),
(474, 3, 16, 10, '2026-10-16', 'Lunch run', 110, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-05-14 00:00:00', '2026-10-17 08:33:13'),
(475, 3, 16, 1, '2026-10-11', 'Evening practice', 78, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-05-11 00:00:00', '2026-10-17 08:33:13'),
(476, 3, 16, 4, '2026-10-13', 'Night workout', 35, 4.60, NULL, 'moderate', NULL, 'private', NULL, '2026-05-10 00:00:00', '2026-10-17 08:33:13'),
(477, 3, 16, 10, '2026-10-14', 'Morning session', 57, 10.07, NULL, 'light', NULL, 'private', NULL, '2026-05-08 00:00:00', '2026-10-17 08:33:13'),
(478, 3, 16, 7, '2026-10-15', 'Morning training', 81, 8.04, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-05-07 00:00:00', '2026-10-17 08:33:13'),
(479, 3, 16, 13, '2026-10-14', 'Night training', 55, 11.77, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-05-06 00:00:00', '2026-10-17 08:33:13'),
(480, 3, 16, 1, '2026-10-14', 'Morning workout', 50, 8.81, NULL, 'vigorous', NULL, 'private', NULL, '2026-05-03 00:00:00', '2026-10-17 08:33:13'),
(481, 3, 16, 7, '2026-10-17', 'Morning practice', 99, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-05-01 00:00:00', '2026-10-17 08:33:13'),
(482, 3, 16, 7, '2026-10-11', 'Afternoon training', 60, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-04-30 00:00:00', '2026-10-17 08:33:13'),
(483, 3, 16, 1, '2026-10-15', 'Afternoon practice', 94, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-04-29 00:00:00', '2026-10-17 08:33:13'),
(484, 3, 16, 1, '2026-10-11', 'Lunch training', 107, 15.43, NULL, 'light', NULL, 'tenant', NULL, '2026-04-28 00:00:00', '2026-10-17 08:33:13'),
(485, 3, 16, 13, '2026-10-15', 'Lunch training', 64, NULL, NULL, 'light', NULL, 'private', NULL, '2026-04-26 00:00:00', '2026-10-17 08:33:13'),
(486, 3, 16, 7, '2026-10-17', 'Lunch workout', 52, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-04-24 00:00:00', '2026-10-17 08:33:13'),
(487, 3, 16, 7, '2026-10-14', 'Morning run', 68, 13.06, NULL, 'moderate', NULL, 'public', NULL, '2026-04-19 00:00:00', '2026-10-17 08:33:13'),
(488, 3, 16, 13, '2026-10-11', 'Night training', 78, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-04-17 00:00:00', '2026-10-17 08:33:13'),
(489, 3, 16, 1, '2026-10-17', 'Lunch session', 79, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-04-16 00:00:00', '2026-10-17 08:33:13'),
(490, 3, 16, 1, '2026-10-11', 'Evening training', 100, 10.86, NULL, 'vigorous', NULL, 'private', NULL, '2026-04-15 00:00:00', '2026-10-17 08:33:13'),
(491, 3, 16, 1, '2026-10-16', 'Afternoon practice', 43, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-04-14 00:00:00', '2026-10-17 08:33:13'),
(492, 3, 16, 1, '2026-10-16', 'Afternoon training', 44, 8.86, NULL, 'light', NULL, 'public', NULL, '2026-04-12 00:00:00', '2026-10-17 08:33:13'),
(493, 3, 16, 1, '2026-10-14', 'Night session', 58, 14.29, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-04-11 00:00:00', '2026-10-17 08:33:13'),
(494, 3, 16, 4, '2026-10-17', 'Evening session', 100, 15.50, NULL, 'light', NULL, 'private', NULL, '2026-04-10 00:00:00', '2026-10-17 08:33:13'),
(495, 3, 16, 7, '2026-10-14', 'Afternoon workout', 85, NULL, NULL, 'light', NULL, 'private', NULL, '2026-04-09 00:00:00', '2026-10-17 08:33:13'),
(496, 3, 16, 4, '2026-10-15', 'Night session', 68, 3.39, NULL, 'moderate', NULL, 'tenant', NULL, '2026-04-07 00:00:00', '2026-10-17 08:33:13'),
(497, 3, 16, 13, '2026-10-15', 'Night session', 81, NULL, NULL, 'light', NULL, 'private', NULL, '2026-04-04 00:00:00', '2026-10-17 08:33:13'),
(498, 3, 16, 4, '2026-10-17', 'Morning practice', 79, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-04-03 00:00:00', '2026-10-17 08:33:13'),
(499, 3, 16, 13, '2026-10-12', 'Evening run', 40, 2.34, NULL, 'moderate', NULL, 'public', NULL, '2026-04-02 00:00:00', '2026-10-17 08:33:13'),
(500, 3, 16, 4, '2026-10-13', 'Morning session', 42, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-04-01 00:00:00', '2026-10-17 08:33:13'),
(501, 3, 16, 4, '2026-10-11', 'Night run', 104, 1.41, NULL, 'moderate', NULL, 'public', NULL, '2026-03-31 00:00:00', '2026-10-17 08:33:13'),
(502, 3, 16, 10, '2026-10-12', 'Lunch session', 42, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-03-27 00:00:00', '2026-10-17 08:33:13'),
(503, 3, 16, 4, '2026-10-17', 'Morning run', 106, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-03-26 00:00:00', '2026-10-17 08:33:13'),
(504, 3, 16, 4, '2026-10-17', 'Afternoon session', 31, NULL, NULL, 'light', NULL, 'public', NULL, '2026-03-23 00:00:00', '2026-10-17 08:33:13'),
(505, 3, 16, 7, '2026-10-17', 'Lunch session', 88, NULL, NULL, 'light', NULL, 'private', NULL, '2026-03-21 00:00:00', '2026-10-17 08:33:13'),
(506, 3, 16, 1, '2026-10-16', 'Night session', 91, 13.43, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-03-20 00:00:00', '2026-10-17 08:33:13'),
(507, 3, 16, 7, '2026-10-12', 'Morning training', 80, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-03-19 00:00:00', '2026-10-17 08:33:13'),
(508, 3, 16, 7, '2026-10-13', 'Evening run', 91, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-03-18 00:00:00', '2026-10-17 08:33:13'),
(509, 3, 16, 1, '2026-10-15', 'Evening workout', 38, NULL, NULL, 'light', NULL, 'private', NULL, '2026-03-16 00:00:00', '2026-10-17 08:33:13'),
(510, 3, 16, 7, '2026-10-11', 'Afternoon session', 24, NULL, NULL, 'light', NULL, 'public', NULL, '2026-03-13 00:00:00', '2026-10-17 08:33:13'),
(511, 3, 16, 1, '2026-10-13', 'Morning training', 95, 2.96, NULL, 'light', NULL, 'public', NULL, '2026-03-12 00:00:00', '2026-10-17 08:33:13'),
(512, 3, 16, 7, '2026-10-13', 'Afternoon session', 22, NULL, NULL, 'light', NULL, 'private', NULL, '2026-03-11 00:00:00', '2026-10-17 08:33:13'),
(513, 3, 16, 10, '2026-10-17', 'Lunch run', 48, NULL, NULL, 'light', NULL, 'private', NULL, '2026-03-10 00:00:00', '2026-10-17 08:33:13'),
(514, 3, 16, 1, '2026-10-15', 'Evening session', 92, 9.71, NULL, 'moderate', NULL, 'tenant', NULL, '2026-03-09 00:00:00', '2026-10-17 08:33:13'),
(515, 3, 16, 10, '2026-10-15', 'Evening run', 115, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-03-08 00:00:00', '2026-10-17 08:33:13'),
(516, 3, 16, 1, '2026-10-16', 'Lunch practice', 20, 13.54, NULL, 'moderate', NULL, 'public', NULL, '2026-03-07 00:00:00', '2026-10-17 08:33:13'),
(517, 3, 16, 13, '2026-10-12', 'Afternoon practice', 108, 2.08, NULL, 'light', NULL, 'tenant', NULL, '2026-03-06 00:00:00', '2026-10-17 08:33:13'),
(518, 3, 16, 4, '2026-10-15', 'Morning session', 24, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-03-05 00:00:00', '2026-10-17 08:33:13'),
(519, 3, 16, 13, '2026-10-15', 'Lunch session', 110, 12.84, NULL, 'moderate', NULL, 'public', NULL, '2026-03-04 00:00:00', '2026-10-17 08:33:13'),
(520, 3, 16, 7, '2026-10-12', 'Lunch workout', 97, 2.25, NULL, 'moderate', NULL, 'tenant', NULL, '2026-03-03 00:00:00', '2026-10-17 08:33:13'),
(521, 3, 16, 7, '2026-10-14', 'Evening practice', 39, 5.00, NULL, 'moderate', NULL, 'private', NULL, '2026-03-02 00:00:00', '2026-10-17 08:33:13'),
(522, 3, 16, 13, '2026-10-13', 'Night workout', 68, 14.13, NULL, 'moderate', NULL, 'private', NULL, '2026-02-28 00:00:00', '2026-10-17 08:33:13'),
(523, 3, 16, 13, '2026-10-14', 'Afternoon session', 33, 13.77, NULL, 'vigorous', NULL, 'public', NULL, '2026-02-25 00:00:00', '2026-10-17 08:33:13'),
(524, 3, 16, 7, '2026-10-15', 'Evening practice', 55, NULL, NULL, 'light', NULL, 'private', NULL, '2026-02-24 00:00:00', '2026-10-17 08:33:13'),
(525, 3, 16, 1, '2026-10-14', 'Lunch practice', 28, 11.47, NULL, 'light', NULL, 'tenant', NULL, '2026-02-23 00:00:00', '2026-10-17 08:33:13'),
(526, 3, 16, 7, '2026-10-14', 'Night practice', 79, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-02-21 00:00:00', '2026-10-17 08:33:13'),
(527, 3, 16, 1, '2026-10-11', 'Morning training', 115, 14.79, NULL, 'light', NULL, 'public', NULL, '2026-02-15 00:00:00', '2026-10-17 08:33:13'),
(528, 3, 16, 10, '2026-10-17', 'Morning session', 29, 1.97, NULL, 'light', NULL, 'tenant', NULL, '2026-02-14 00:00:00', '2026-10-17 08:33:13'),
(529, 3, 16, 1, '2026-10-12', 'Morning session', 112, 12.37, NULL, 'vigorous', NULL, 'private', NULL, '2026-02-10 00:00:00', '2026-10-17 08:33:13'),
(530, 3, 16, 1, '2026-10-15', 'Lunch training', 100, 14.99, NULL, 'moderate', NULL, 'tenant', NULL, '2026-02-09 00:00:00', '2026-10-17 08:33:13'),
(531, 3, 16, 10, '2026-10-13', 'Night workout', 70, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-02-08 00:00:00', '2026-10-17 08:33:13'),
(532, 3, 16, 13, '2026-10-15', 'Night practice', 58, NULL, NULL, 'light', NULL, 'private', NULL, '2026-02-06 00:00:00', '2026-10-17 08:33:13'),
(533, 3, 16, 13, '2026-10-15', 'Night session', 22, 11.56, NULL, 'moderate', NULL, 'private', NULL, '2026-02-05 00:00:00', '2026-10-17 08:33:13'),
(534, 3, 16, 13, '2026-10-17', 'Morning session', 66, NULL, NULL, 'light', NULL, 'private', NULL, '2026-02-04 00:00:00', '2026-10-17 08:33:13'),
(535, 3, 16, 10, '2026-10-17', 'Evening session', 117, 11.84, NULL, 'moderate', NULL, 'public', NULL, '2026-02-02 00:00:00', '2026-10-17 08:33:13'),
(536, 3, 16, 4, '2026-10-15', 'Night session', 25, NULL, NULL, 'moderate', NULL, 'public', NULL, '2026-02-01 00:00:00', '2026-10-17 08:33:13'),
(537, 3, 16, 1, '2026-10-16', 'Afternoon practice', 82, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2026-01-30 00:00:00', '2026-10-17 08:33:13'),
(538, 3, 16, 7, '2026-10-17', 'Evening run', 60, 14.16, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-28 00:00:00', '2026-10-17 08:33:13'),
(539, 3, 16, 4, '2026-10-13', 'Evening run', 114, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-26 00:00:00', '2026-10-17 08:33:13'),
(540, 3, 16, 10, '2026-10-17', 'Morning run', 74, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-25 00:00:00', '2026-10-17 08:33:13'),
(541, 3, 16, 10, '2026-10-15', 'Evening training', 33, 11.96, NULL, 'light', NULL, 'public', NULL, '2026-01-23 00:00:00', '2026-10-17 08:33:13'),
(542, 3, 16, 7, '2026-10-15', 'Morning training', 102, 11.02, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-22 00:00:00', '2026-10-17 08:33:13'),
(543, 3, 16, 1, '2026-10-16', 'Afternoon run', 88, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-01-21 00:00:00', '2026-10-17 08:33:13'),
(544, 3, 16, 1, '2026-10-14', 'Evening run', 69, NULL, NULL, 'light', NULL, 'tenant', NULL, '2026-01-20 00:00:00', '2026-10-17 08:33:13'),
(545, 3, 16, 1, '2026-10-17', 'Morning practice', 54, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-01-19 00:00:00', '2026-10-17 08:33:13'),
(546, 3, 16, 7, '2026-10-13', 'Afternoon run', 108, NULL, NULL, 'light', NULL, 'private', NULL, '2026-01-18 00:00:00', '2026-10-17 08:33:13'),
(547, 3, 16, 10, '2026-10-14', 'Evening practice', 31, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-01-17 00:00:00', '2026-10-17 08:33:13'),
(548, 3, 16, 7, '2026-10-17', 'Afternoon workout', 28, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-01-16 00:00:00', '2026-10-17 08:33:13'),
(549, 3, 16, 4, '2026-10-12', 'Night practice', 109, 10.88, NULL, 'light', NULL, 'tenant', NULL, '2026-01-13 00:00:00', '2026-10-17 08:33:13'),
(550, 3, 16, 10, '2026-10-16', 'Lunch training', 34, 1.30, NULL, 'light', NULL, 'private', NULL, '2026-01-11 00:00:00', '2026-10-17 08:33:13'),
(551, 3, 16, 4, '2026-10-15', 'Evening run', 52, 12.55, NULL, 'light', NULL, 'public', NULL, '2026-01-08 00:00:00', '2026-10-17 08:33:13'),
(552, 3, 16, 13, '2026-10-15', 'Morning practice', 50, 10.39, NULL, 'light', NULL, 'private', NULL, '2026-01-07 00:00:00', '2026-10-17 08:33:13'),
(553, 3, 16, 10, '2026-10-14', 'Night session', 107, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2026-01-06 00:00:00', '2026-10-17 08:33:13'),
(554, 3, 16, 4, '2026-10-14', 'Lunch run', 108, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2026-01-04 00:00:00', '2026-10-17 08:33:13'),
(555, 3, 16, 4, '2026-10-11', 'Evening run', 50, NULL, NULL, 'moderate', NULL, 'private', NULL, '2026-01-03 00:00:00', '2026-10-17 08:33:13'),
(556, 3, 16, 1, '2026-10-14', 'Lunch run', 21, 14.64, NULL, 'vigorous', NULL, 'private', NULL, '2026-01-02 00:00:00', '2026-10-17 08:33:13'),
(557, 3, 16, 4, '2026-10-15', 'Evening workout', 66, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-12-30 00:00:00', '2026-10-17 08:33:13'),
(558, 3, 16, 13, '2026-10-14', 'Evening workout', 33, NULL, NULL, 'light', NULL, 'private', NULL, '2025-12-26 00:00:00', '2026-10-17 08:33:13'),
(559, 3, 16, 10, '2026-10-16', 'Morning workout', 54, 14.14, NULL, 'moderate', NULL, 'tenant', NULL, '2025-12-25 00:00:00', '2026-10-17 08:33:13'),
(560, 3, 16, 4, '2026-10-15', 'Lunch training', 66, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-12-24 00:00:00', '2026-10-17 08:33:13'),
(561, 3, 16, 1, '2026-10-13', 'Lunch training', 76, 6.48, NULL, 'vigorous', NULL, 'public', NULL, '2025-12-23 00:00:00', '2026-10-17 08:33:13'),
(562, 3, 16, 4, '2026-10-12', 'Lunch workout', 58, 9.27, NULL, 'light', NULL, 'public', NULL, '2025-12-21 00:00:00', '2026-10-17 08:33:13'),
(563, 3, 16, 13, '2026-10-11', 'Evening run', 62, NULL, NULL, 'light', NULL, 'private', NULL, '2025-12-20 00:00:00', '2026-10-17 08:33:13'),
(564, 3, 16, 1, '2026-10-13', 'Night session', 105, NULL, NULL, 'moderate', NULL, 'public', NULL, '2025-12-19 00:00:00', '2026-10-17 08:33:13'),
(565, 3, 16, 4, '2026-10-14', 'Night run', 84, 10.08, NULL, 'light', NULL, 'public', NULL, '2025-12-16 00:00:00', '2026-10-17 08:33:13'),
(566, 3, 16, 10, '2026-10-17', 'Lunch run', 92, 13.28, NULL, 'light', NULL, 'private', NULL, '2025-12-15 00:00:00', '2026-10-17 08:33:13'),
(567, 3, 16, 1, '2026-10-15', 'Morning run', 89, 14.04, NULL, 'moderate', NULL, 'private', NULL, '2025-12-12 00:00:00', '2026-10-17 08:33:13'),
(568, 3, 16, 10, '2026-10-16', 'Night practice', 111, 10.30, NULL, 'light', NULL, 'tenant', NULL, '2025-12-10 00:00:00', '2026-10-17 08:33:13'),
(569, 3, 16, 1, '2026-10-15', 'Night training', 46, NULL, NULL, 'vigorous', NULL, 'public', NULL, '2025-12-09 00:00:00', '2026-10-17 08:33:13'),
(570, 3, 16, 13, '2026-10-16', 'Morning practice', 61, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2025-12-08 00:00:00', '2026-10-17 08:33:13'),
(571, 3, 16, 7, '2026-10-12', 'Afternoon training', 54, 10.39, NULL, 'moderate', NULL, 'public', NULL, '2025-12-05 00:00:00', '2026-10-17 08:33:13'),
(572, 3, 16, 10, '2026-10-14', 'Morning practice', 117, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-12-03 00:00:00', '2026-10-17 08:33:13'),
(573, 3, 16, 1, '2026-10-17', 'Night workout', 65, 9.51, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-12-02 00:00:00', '2026-10-17 08:33:13'),
(574, 3, 16, 13, '2026-10-14', 'Morning practice', 40, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-12-01 00:00:00', '2026-10-17 08:33:13'),
(575, 3, 16, 7, '2026-10-12', 'Evening workout', 97, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-11-26 00:00:00', '2026-10-17 08:33:13'),
(576, 3, 16, 4, '2026-10-17', 'Lunch session', 67, 9.57, NULL, 'light', NULL, 'tenant', NULL, '2025-11-25 00:00:00', '2026-10-17 08:33:13'),
(577, 3, 16, 4, '2026-10-15', 'Afternoon practice', 52, 13.61, NULL, 'vigorous', NULL, 'private', NULL, '2025-11-24 00:00:00', '2026-10-17 08:33:13'),
(578, 3, 16, 13, '2026-10-14', 'Lunch practice', 35, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2025-11-23 00:00:00', '2026-10-17 08:33:13'),
(579, 3, 16, 10, '2026-10-16', 'Afternoon practice', 42, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-11-22 00:00:00', '2026-10-17 08:33:13'),
(580, 3, 16, 13, '2026-10-13', 'Evening workout', 114, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-11-21 00:00:00', '2026-10-17 08:33:13'),
(581, 3, 16, 10, '2026-10-14', 'Afternoon practice', 95, NULL, NULL, 'moderate', NULL, 'private', NULL, '2025-11-20 00:00:00', '2026-10-17 08:33:13'),
(582, 3, 16, 10, '2026-10-12', 'Night session', 22, NULL, NULL, 'moderate', NULL, 'tenant', NULL, '2025-11-19 00:00:00', '2026-10-17 08:33:13'),
(583, 3, 16, 13, '2026-10-16', 'Evening workout', 70, NULL, NULL, 'light', NULL, 'public', NULL, '2025-11-15 00:00:00', '2026-10-17 08:33:13'),
(584, 3, 16, 10, '2026-10-13', 'Lunch practice', 92, 13.25, NULL, 'vigorous', NULL, 'private', NULL, '2025-11-14 00:00:00', '2026-10-17 08:33:13'),
(585, 3, 16, 13, '2026-10-15', 'Morning session', 49, NULL, NULL, 'light', NULL, 'public', NULL, '2025-11-13 00:00:00', '2026-10-17 08:33:13'),
(586, 3, 16, 7, '2026-10-16', 'Morning run', 39, 4.42, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-11-12 00:00:00', '2026-10-17 08:33:13'),
(587, 3, 16, 4, '2026-10-17', 'Night practice', 57, 5.83, NULL, 'vigorous', NULL, 'public', NULL, '2025-11-11 00:00:00', '2026-10-17 08:33:13'),
(588, 3, 16, 1, '2026-10-13', 'Night run', 24, 11.00, NULL, 'moderate', NULL, 'private', NULL, '2025-11-10 00:00:00', '2026-10-17 08:33:13'),
(589, 3, 16, 10, '2026-10-16', 'Night session', 115, 2.87, NULL, 'light', NULL, 'public', NULL, '2025-11-09 00:00:00', '2026-10-17 08:33:13'),
(590, 3, 16, 7, '2026-10-17', 'Lunch practice', 50, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-11-04 00:00:00', '2026-10-17 08:33:13'),
(591, 3, 16, 10, '2026-10-13', 'Lunch practice', 116, NULL, NULL, 'light', NULL, 'public', NULL, '2025-10-31 00:00:00', '2026-10-17 08:33:13'),
(592, 3, 16, 10, '2026-10-17', 'Lunch workout', 101, NULL, NULL, 'moderate', NULL, 'public', NULL, '2025-10-30 00:00:00', '2026-10-17 08:33:13'),
(593, 3, 16, 7, '2026-10-17', 'Evening run', 87, NULL, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-10-28 00:00:00', '2026-10-17 08:33:13'),
(594, 3, 16, 4, '2026-10-13', 'Lunch workout', 84, NULL, NULL, 'moderate', NULL, 'public', NULL, '2025-10-27 00:00:00', '2026-10-17 08:33:13'),
(595, 3, 16, 10, '2026-10-12', 'Evening session', 36, NULL, NULL, 'light', NULL, 'private', NULL, '2025-10-26 00:00:00', '2026-10-17 08:33:13'),
(596, 3, 16, 1, '2026-10-12', 'Evening session', 65, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-10-22 00:00:00', '2026-10-17 08:33:13'),
(597, 3, 16, 10, '2026-10-11', 'Morning run', 45, NULL, NULL, 'moderate', NULL, 'public', NULL, '2025-10-21 00:00:00', '2026-10-17 08:33:13'),
(598, 3, 16, 1, '2026-10-14', 'Evening run', 37, NULL, NULL, 'light', NULL, 'tenant', NULL, '2025-10-16 00:00:00', '2026-10-17 08:33:13'),
(599, 3, 16, 4, '2026-10-16', 'Night practice', 79, 2.49, NULL, 'vigorous', NULL, 'tenant', NULL, '2025-10-15 00:00:00', '2026-10-17 08:33:13'),
(600, 3, 16, 10, '2026-10-12', 'Afternoon practice', 63, 6.48, NULL, 'moderate', NULL, 'private', NULL, '2025-10-10 00:00:00', '2026-10-17 08:33:13'),
(601, 3, 16, 4, '2026-10-16', 'Night session', 97, 10.28, NULL, 'light', NULL, 'public', NULL, '2025-10-08 00:00:00', '2026-10-17 08:33:13'),
(602, 3, 16, 1, '2026-10-13', 'Lunch session', 31, NULL, NULL, 'vigorous', NULL, 'private', NULL, '2025-10-07 00:00:00', '2026-10-17 08:33:13'),
(603, 3, 16, 7, '2026-10-12', 'Night practice', 30, 13.27, NULL, 'light', NULL, 'tenant', NULL, '2025-10-05 00:00:00', '2026-10-17 08:33:13'),
(604, 3, 16, 13, '2026-10-11', 'Evening workout', 25, 5.52, NULL, 'moderate', NULL, 'private', NULL, '2025-10-03 00:00:00', '2026-10-17 08:33:13');

-- --------------------------------------------------------

--
-- Table structure for table `activity_types`
--

CREATE TABLE `activity_types` (
  `activity_type_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `category` enum('strength','cardio','aquatic','sport','general') NOT NULL,
  `measurement_model` enum('gym','swimming','distance','sport','duration','general') NOT NULL,
  `default_unit` enum('kg','km','metres','minutes','none') NOT NULL DEFAULT 'none',
  `description` text NOT NULL,
  `safety_notes` text DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `activity_types`
--

INSERT INTO `activity_types` (`activity_type_id`, `tenant_id`, `name`, `slug`, `category`, `measurement_model`, `default_unit`, `description`, `safety_notes`, `is_published`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 3, 'Gym', 'gym', 'strength', 'gym', 'kg', 'Strength training.', 'Use equipment safely.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(2, 4, 'Gym', 'gym', 'strength', 'gym', 'kg', 'Strength training.', 'Use equipment safely.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(3, 5, 'Gym', 'gym', 'strength', 'gym', 'kg', 'Strength training.', 'Use equipment safely.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(4, 3, 'Swimming', 'swimming', 'aquatic', 'swimming', 'metres', 'Pool swimming.', 'Follow pool rules.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(5, 4, 'Swimming', 'swimming', 'aquatic', 'swimming', 'metres', 'Pool swimming.', 'Follow pool rules.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(6, 5, 'Swimming', 'swimming', 'aquatic', 'swimming', 'metres', 'Pool swimming.', 'Follow pool rules.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(7, 3, 'Running', 'running', 'cardio', 'distance', 'km', 'Running activity.', 'Warm up first.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(8, 4, 'Running', 'running', 'cardio', 'distance', 'km', 'Running activity.', 'Warm up first.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(9, 5, 'Running', 'running', 'cardio', 'distance', 'km', 'Running activity.', 'Warm up first.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(10, 3, 'Walking', 'walking', 'cardio', 'distance', 'km', 'Walking activity.', 'Wear suitable shoes.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(11, 4, 'Walking', 'walking', 'cardio', 'distance', 'km', 'Walking activity.', 'Wear suitable shoes.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(12, 5, 'Walking', 'walking', 'cardio', 'distance', 'km', 'Walking activity.', 'Wear suitable shoes.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(13, 3, 'Football', 'football', 'sport', 'sport', 'minutes', 'Football session.', 'Use suitable footwear.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(14, 4, 'Football', 'football', 'sport', 'sport', 'minutes', 'Football session.', 'Use suitable footwear.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(15, 5, 'Football', 'football', 'sport', 'sport', 'minutes', 'Football session.', 'Use suitable footwear.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(16, 3, 'Other sport', 'other-sport', 'sport', 'sport', 'minutes', 'Other sports.', 'Use appropriate equipment.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(17, 4, 'Other sport', 'other-sport', 'sport', 'sport', 'minutes', 'Other sports.', 'Use appropriate equipment.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(18, 5, 'Other sport', 'other-sport', 'sport', 'sport', 'minutes', 'Other sports.', 'Use appropriate equipment.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(19, 3, 'Other activity', 'other-activity', 'general', 'general', 'none', 'Other activity.', 'Choose appropriate intensity.', 1, 1, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(20, 4, 'Other activity', 'other-activity', 'general', 'general', 'none', 'Other activity.', 'Choose appropriate intensity.', 1, 2, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(21, 5, 'Other activity', 'other-activity', 'general', 'general', 'none', 'Other activity.', 'Choose appropriate intensity.', 1, 3, '2026-09-30 17:13:52', '2026-10-16 13:22:18'),
(22, 1, 'Gym', 'gym', 'strength', 'gym', 'kg', 'Strength training.', 'Use equipment safely.', 1, 51, '2026-10-16 13:12:55', '2026-10-16 13:12:55'),
(23, 1, 'Swimming', 'swimming', 'aquatic', 'swimming', 'metres', 'Pool swimming.', 'Follow pool rules.', 1, 51, '2026-10-16 13:12:55', '2026-10-16 13:12:55'),
(24, 1, 'Running', 'running', 'cardio', 'distance', 'km', 'Running activity.', 'Warm up first.', 1, 51, '2026-10-16 13:12:55', '2026-10-16 13:12:55'),
(25, 1, 'Walking', 'walking', 'cardio', 'distance', 'km', 'Walking activity.', 'Wear suitable shoes.', 1, 51, '2026-10-16 13:12:55', '2026-10-16 13:12:55'),
(26, 1, 'Football', 'football', 'sport', 'sport', 'minutes', 'Football session.', 'Use suitable footwear.', 1, 51, '2026-10-16 13:12:55', '2026-10-16 13:12:55'),
(29, 1, 'Other sport', 'other-sport', 'sport', 'sport', 'minutes', 'Other sports.', 'Use appropriate equipment.', 1, 51, '2026-10-16 13:22:18', '2026-10-16 13:22:18'),
(30, 1, 'Other activity', 'other-activity', 'general', 'general', 'none', 'Other activity.', 'Choose appropriate intensity.', 1, 51, '2026-10-16 13:22:18', '2026-10-16 13:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `audit_log_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `outcome` enum('success','failure','denied') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`audit_log_id`, `tenant_id`, `user_id`, `action`, `entity_type`, `entity_id`, `outcome`, `ip_address`, `user_agent`, `description`, `created_at`) VALUES
(1, 3, 4, 'login_success', 'synthetic_test_record', 4, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(2, 3, 5, 'profile_updated', 'synthetic_test_record', 5, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(3, 3, 10, 'activity_created', 'synthetic_test_record', 10, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(4, 3, 11, 'preferences_updated', 'synthetic_test_record', 11, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(5, 3, 1, 'profile_updated', 'synthetic_test_record', 1, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(6, 3, 16, 'login_success', 'synthetic_test_record', 16, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(7, 3, 17, 'profile_updated', 'synthetic_test_record', 17, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(8, 3, 18, 'activity_created', 'synthetic_test_record', 18, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(9, 3, 19, 'preferences_updated', 'synthetic_test_record', 19, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(10, 3, 20, 'login_success', 'synthetic_test_record', 20, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(11, 3, 21, 'profile_updated', 'synthetic_test_record', 21, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(12, 3, 22, 'activity_created', 'synthetic_test_record', 22, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(13, 3, 23, 'preferences_updated', 'synthetic_test_record', 23, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(14, 3, 24, 'login_success', 'synthetic_test_record', 24, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(15, 3, 25, 'profile_updated', 'synthetic_test_record', 25, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(16, 3, 41, 'profile_updated', 'synthetic_test_record', 41, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(17, 3, 42, 'activity_created', 'synthetic_test_record', 42, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(18, 3, 43, 'preferences_updated', 'synthetic_test_record', 43, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(19, 3, 44, 'login_success', 'synthetic_test_record', 44, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(20, 3, 45, 'profile_updated', 'synthetic_test_record', 45, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(21, 4, 6, 'activity_created', 'synthetic_test_record', 6, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(22, 4, 7, 'preferences_updated', 'synthetic_test_record', 7, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(23, 4, 12, 'login_success', 'synthetic_test_record', 12, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(24, 4, 13, 'profile_updated', 'synthetic_test_record', 13, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(25, 4, 2, 'activity_created', 'synthetic_test_record', 2, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(26, 4, 26, 'activity_created', 'synthetic_test_record', 26, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(27, 4, 27, 'preferences_updated', 'synthetic_test_record', 27, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(28, 4, 28, 'login_success', 'synthetic_test_record', 28, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(29, 4, 29, 'profile_updated', 'synthetic_test_record', 29, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(30, 4, 30, 'activity_created', 'synthetic_test_record', 30, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(31, 4, 31, 'preferences_updated', 'synthetic_test_record', 31, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(32, 4, 32, 'login_success', 'synthetic_test_record', 32, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(33, 4, 33, 'profile_updated', 'synthetic_test_record', 33, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(34, 4, 34, 'activity_created', 'synthetic_test_record', 34, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(35, 4, 35, 'preferences_updated', 'synthetic_test_record', 35, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(36, 4, 46, 'activity_created', 'synthetic_test_record', 46, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(37, 4, 47, 'preferences_updated', 'synthetic_test_record', 47, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(38, 4, 48, 'login_success', 'synthetic_test_record', 48, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(39, 4, 49, 'profile_updated', 'synthetic_test_record', 49, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(40, 4, 50, 'activity_created', 'synthetic_test_record', 50, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(41, 5, 8, 'login_success', 'synthetic_test_record', 8, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(42, 5, 9, 'profile_updated', 'synthetic_test_record', 9, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(43, 5, 14, 'activity_created', 'synthetic_test_record', 14, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(44, 5, 15, 'preferences_updated', 'synthetic_test_record', 15, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(45, 5, 3, 'preferences_updated', 'synthetic_test_record', 3, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(46, 5, 36, 'login_success', 'synthetic_test_record', 36, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(47, 5, 37, 'profile_updated', 'synthetic_test_record', 37, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(48, 5, 38, 'activity_created', 'synthetic_test_record', 38, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(49, 5, 39, 'preferences_updated', 'synthetic_test_record', 39, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(50, 5, 40, 'login_success', 'synthetic_test_record', 40, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(51, 3, 8, 'login_success', 'synthetic_test_record', 8, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52'),
(52, 4, 15, 'preferences_updated', 'synthetic_test_record', 15, 'success', '192.0.2.10', 'FitTrack synthetic test client', 'Synthetic audit event for development testing.', '2026-09-30 17:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `challenges`
--

CREATE TABLE `challenges` (
  `challenge_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `target_value` int(10) UNSIGNED NOT NULL,
  `current_value` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','completed','archived') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `challenges`
--

INSERT INTO `challenges` (`challenge_id`, `tenant_id`, `user_id`, `title`, `description`, `target_value`, `current_value`, `unit`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 3, 16, '100km Running Challenge', 'Run 100km in 3 months', 100, 67, 'km', '2026-08-04', '2026-11-02', 'active', '2026-08-04 00:00:00'),
(2, 3, 16, '30 Workouts in 30 Days', 'Complete 30 workouts', 30, 22, 'sessions', '2026-09-11', '2026-10-11', 'active', '2026-09-11 00:00:00'),
(3, 3, 16, 'Burn 10,000 Calories', 'Burn 10k calories this month', 10000, 7500, 'kcal', '2026-10-01', '2026-10-31', 'active', '2026-10-01 00:00:00'),
(4, 3, 16, 'run', NULL, 2, 0, 'km', '2026-10-06', '2026-11-05', 'active', '2026-10-06 04:49:56'),
(5, 3, 16, 'run', NULL, 15, 0, 'km', '2026-10-18', '2026-11-17', 'active', '2026-10-18 20:37:45');

-- --------------------------------------------------------

--
-- Table structure for table `daily_nutrition`
--

CREATE TABLE `daily_nutrition` (
  `nutrition_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `calories_consumed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `calories_burned` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `protein_g` int(10) UNSIGNED DEFAULT 0,
  `carbs_g` int(10) UNSIGNED DEFAULT 0,
  `fats_g` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `daily_nutrition`
--

INSERT INTO `daily_nutrition` (`nutrition_id`, `tenant_id`, `user_id`, `log_date`, `calories_consumed`, `calories_burned`, `notes`, `created_at`, `updated_at`, `protein_g`, `carbs_g`, `fats_g`) VALUES
(14, 3, 16, '2026-10-18', 1829, 0, NULL, '2026-10-17 09:09:14', '2026-10-17 09:09:14', 137, 206, 51),
(15, 3, 16, '2026-10-19', 1781, 0, NULL, '2026-10-18 09:40:02', '2026-10-18 09:40:02', 134, 200, 49),
(16, 3, 16, '2026-10-20', 1822, 0, NULL, '2026-10-19 14:30:23', '2026-10-19 14:30:23', 137, 205, 51);

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `exercise_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(60) NOT NULL,
  `description` text NOT NULL,
  `equipment` varchar(150) DEFAULT NULL,
  `safety_notes` text DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`exercise_id`, `tenant_id`, `name`, `category`, `description`, `equipment`, `safety_notes`, `is_published`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 3, 'Barbell squat', 'legs', 'A lower-body resistance exercise.', 'Barbell and rack', 'Use suitable loading and controlled technique.', 1, 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 4, 'Barbell squat', 'legs', 'A lower-body resistance exercise.', 'Barbell and rack', 'Use suitable loading and controlled technique.', 1, 2, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 5, 'Barbell squat', 'legs', 'A lower-body resistance exercise.', 'Barbell and rack', 'Use suitable loading and controlled technique.', 1, 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 3, 'Bench press', 'chest', 'A horizontal pressing exercise.', 'Barbell and bench', 'Use a safe setup and appropriate assistance where required.', 1, 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 4, 'Bench press', 'chest', 'A horizontal pressing exercise.', 'Barbell and bench', 'Use a safe setup and appropriate assistance where required.', 1, 2, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 5, 'Bench press', 'chest', 'A horizontal pressing exercise.', 'Barbell and bench', 'Use a safe setup and appropriate assistance where required.', 1, 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 3, 'Seated row', 'back', 'A pulling exercise for the upper back.', 'Cable machine', 'Keep movement controlled and use suitable resistance.', 1, 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 4, 'Seated row', 'back', 'A pulling exercise for the upper back.', 'Cable machine', 'Keep movement controlled and use suitable resistance.', 1, 2, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(9, 5, 'Seated row', 'back', 'A pulling exercise for the upper back.', 'Cable machine', 'Keep movement controlled and use suitable resistance.', 1, 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(10, 3, 'Dumbbell shoulder press', 'shoulders', 'An overhead pressing exercise.', 'Dumbbells', 'Use a stable position and suitable weight.', 1, 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(11, 4, 'Dumbbell shoulder press', 'shoulders', 'An overhead pressing exercise.', 'Dumbbells', 'Use a stable position and suitable weight.', 1, 2, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(12, 5, 'Dumbbell shoulder press', 'shoulders', 'An overhead pressing exercise.', 'Dumbbells', 'Use a stable position and suitable weight.', 1, 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(13, 3, 'Bodyweight lunge', 'legs', 'A bodyweight lower-body exercise.', 'None', 'Use a stable surface and controlled movement.', 1, 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(14, 4, 'Bodyweight lunge', 'legs', 'A bodyweight lower-body exercise.', 'None', 'Use a stable surface and controlled movement.', 1, 2, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(15, 5, 'Bodyweight lunge', 'legs', 'A bodyweight lower-body exercise.', 'None', 'Use a stable surface and controlled movement.', 1, 3, '2026-09-30 17:13:52', '2026-09-30 17:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `gym_sessions`
--

CREATE TABLE `gym_sessions` (
  `gym_session_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `activity_log_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_date` date NOT NULL,
  `session_name` varchar(100) NOT NULL,
  `duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `notes` varchar(1000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gym_session_exercises`
--

CREATE TABLE `gym_session_exercises` (
  `session_exercise_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `gym_session_id` bigint(20) UNSIGNED NOT NULL,
  `exercise_id` bigint(20) UNSIGNED NOT NULL,
  `exercise_order` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `notes` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gym_sets`
--

CREATE TABLE `gym_sets` (
  `set_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `session_exercise_id` bigint(20) UNSIGNED NOT NULL,
  `set_number` smallint(5) UNSIGNED NOT NULL,
  `repetitions` smallint(5) UNSIGNED DEFAULT NULL,
  `weight_kg` decimal(8,2) DEFAULT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kudos`
--

CREATE TABLE `kudos` (
  `kudos_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `activity_log_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `kudos`
--

INSERT INTO `kudos` (`kudos_id`, `user_id`, `activity_log_id`, `created_at`) VALUES
(1, 5, 81, '2026-09-30 17:13:52'),
(2, 43, 81, '2026-09-30 17:13:52'),
(3, 25, 82, '2026-09-30 17:13:52'),
(4, 42, 82, '2026-09-30 17:13:52'),
(5, 45, 83, '2026-09-30 17:13:52'),
(6, 4, 84, '2026-09-30 17:13:52'),
(7, 24, 84, '2026-09-30 17:13:52'),
(8, 4, 367, '2025-10-04 00:00:00'),
(9, 10, 367, '2025-10-04 00:00:00'),
(10, 22, 367, '2025-10-04 00:00:00'),
(11, 23, 367, '2025-10-04 00:00:00'),
(12, 41, 367, '2025-10-04 00:00:00'),
(13, 8, 603, '2025-10-05 00:00:00'),
(14, 11, 603, '2025-10-05 00:00:00'),
(15, 17, 601, '2025-10-08 00:00:00'),
(16, 18, 601, '2025-10-08 00:00:00'),
(17, 19, 601, '2025-10-08 00:00:00'),
(18, 25, 601, '2025-10-08 00:00:00'),
(19, 42, 601, '2025-10-08 00:00:00'),
(20, 18, 365, '2025-10-09 00:00:00'),
(21, 22, 365, '2025-10-09 00:00:00'),
(22, 5, 364, '2025-10-11 00:00:00'),
(23, 11, 364, '2025-10-11 00:00:00'),
(24, 25, 364, '2025-10-11 00:00:00'),
(25, 41, 364, '2025-10-11 00:00:00'),
(26, 45, 364, '2025-10-11 00:00:00'),
(27, 20, 363, '2025-10-12 00:00:00'),
(28, 23, 363, '2025-10-12 00:00:00'),
(29, 24, 363, '2025-10-12 00:00:00'),
(30, 4, 360, '2025-10-15 00:00:00'),
(31, 45, 360, '2025-10-15 00:00:00'),
(32, 43, 599, '2025-10-15 00:00:00'),
(33, 22, 598, '2025-10-16 00:00:00'),
(34, 25, 598, '2025-10-16 00:00:00'),
(35, 1, 357, '2025-10-18 00:00:00'),
(36, 11, 357, '2025-10-18 00:00:00'),
(37, 17, 357, '2025-10-18 00:00:00'),
(38, 5, 354, '2025-10-23 00:00:00'),
(39, 44, 354, '2025-10-23 00:00:00'),
(40, 8, 352, '2025-10-25 00:00:00'),
(41, 18, 352, '2025-10-25 00:00:00'),
(42, 19, 352, '2025-10-25 00:00:00'),
(43, 20, 352, '2025-10-25 00:00:00'),
(44, 25, 352, '2025-10-25 00:00:00'),
(45, 42, 352, '2025-10-25 00:00:00'),
(46, 5, 351, '2025-10-26 00:00:00'),
(47, 11, 351, '2025-10-26 00:00:00'),
(48, 17, 351, '2025-10-26 00:00:00'),
(49, 41, 351, '2025-10-26 00:00:00'),
(50, 18, 594, '2025-10-27 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `meal_plan_items`
--

CREATE TABLE `meal_plan_items` (
  `meal_plan_item_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `meal_date` date NOT NULL,
  `meal_slot` enum('breakfast','lunch','dinner','snacks') NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `calories` int(10) UNSIGNED DEFAULT NULL,
  `protein_g` decimal(5,1) DEFAULT NULL,
  `carbs_g` decimal(5,1) DEFAULT NULL,
  `fats_g` decimal(5,1) DEFAULT NULL,
  `notes` varchar(250) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meal_plan_items`
--

INSERT INTO `meal_plan_items` (`meal_plan_item_id`, `tenant_id`, `user_id`, `meal_date`, `meal_slot`, `food_name`, `calories`, `protein_g`, `carbs_g`, `fats_g`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 16, '2026-10-19', 'breakfast', 'Porridge with Banana', 320, 10.0, 58.0, 6.0, 'Add honey for extra energy', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(2, 3, 16, '2026-10-19', 'lunch', 'Chicken Caesar Salad', 450, 38.0, 12.0, 28.0, 'Use Greek yoghurt dressing', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(3, 3, 16, '2026-10-19', 'dinner', 'Salmon with Roasted Vegetables', 520, 42.0, 22.0, 28.0, 'Wild-caught salmon preferred', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(4, 3, 16, '2026-10-19', 'snacks', 'Apple with Almond Butter', 220, 6.0, 24.0, 14.0, NULL, '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(5, 3, 16, '2026-10-20', 'breakfast', 'Greek Yoghurt with Berries', 280, 18.0, 36.0, 6.0, 'Use full-fat yoghurt', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(6, 3, 16, '2026-10-20', 'lunch', 'Turkey and Avocado Wrap', 480, 32.0, 38.0, 22.0, 'Wholemeal wrap', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(7, 3, 16, '2026-10-20', 'dinner', 'Beef Stir-Fry with Rice', 580, 36.0, 62.0, 18.0, 'Use lean beef mince', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(8, 3, 16, '2026-10-20', 'snacks', 'Protein Bar', 210, 20.0, 22.0, 8.0, NULL, '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(9, 3, 16, '2026-10-21', 'breakfast', 'Scrambled Eggs on Toast', 380, 22.0, 28.0, 20.0, 'Two eggs, wholemeal bread', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(10, 3, 16, '2026-10-21', 'lunch', 'Tuna Salad Sandwich', 420, 34.0, 42.0, 12.0, 'Add cucumber and tomato', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(11, 3, 16, '2026-10-21', 'dinner', 'Chicken Curry with Rice', 620, 44.0, 68.0, 18.0, 'Medium spice level', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(12, 3, 16, '2026-10-21', 'snacks', 'Handful of Mixed Nuts', 180, 6.0, 6.0, 16.0, 'Unsalted variety', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(13, 3, 16, '2026-10-22', 'breakfast', 'Smoothie Bowl', 340, 14.0, 52.0, 8.0, 'Banana, berries, protein powder', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(14, 3, 16, '2026-10-22', 'lunch', 'Vegetable Soup with Bread', 320, 12.0, 48.0, 8.0, 'Homemade minestrone', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(15, 3, 16, '2026-10-22', 'dinner', 'Pasta Bolognese', 640, 32.0, 78.0, 22.0, 'Use wholewheat pasta', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(16, 3, 16, '2026-10-22', 'snacks', 'Carrot Sticks with Hummus', 150, 6.0, 18.0, 6.0, NULL, '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(17, 3, 16, '2026-10-23', 'breakfast', 'Avocado Toast with Poached Egg', 420, 18.0, 32.0, 26.0, 'Sourdough bread', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(18, 3, 16, '2026-10-23', 'lunch', 'Chicken and Rice Bowl', 520, 38.0, 58.0, 12.0, 'Add steamed broccoli', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(19, 3, 16, '2026-10-23', 'dinner', 'Fish and Chips (Baked)', 480, 32.0, 52.0, 16.0, 'Oven-baked, not fried', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(20, 3, 16, '2026-10-23', 'snacks', 'Dark Chocolate Square', 120, 2.0, 12.0, 8.0, '70% cocoa minimum', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(21, 3, 16, '2026-10-24', 'breakfast', 'Pancakes with Maple Syrup', 420, 10.0, 68.0, 12.0, 'Weekend treat', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(22, 3, 16, '2026-10-24', 'lunch', 'BLT Sandwich', 460, 24.0, 38.0, 24.0, 'Add extra lettuce', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(23, 3, 16, '2026-10-24', 'dinner', 'Homemade Pizza', 680, 28.0, 82.0, 26.0, 'Thin crust, margherita', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(24, 3, 16, '2026-10-24', 'snacks', 'Fruit Salad', 120, 2.0, 28.0, 1.0, 'Seasonal fruits', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(25, 3, 16, '2026-10-25', 'breakfast', 'Full English Breakfast', 580, 32.0, 28.0, 38.0, 'Weekend special', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(26, 3, 16, '2026-10-25', 'lunch', 'Sunday Roast Chicken', 620, 48.0, 42.0, 28.0, 'With roast potatoes and veg', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(27, 3, 16, '2026-10-25', 'dinner', 'Leftover Roast Sandwich', 380, 24.0, 38.0, 14.0, 'Use Sunday roast leftovers', '2026-10-18 19:37:18', '2026-10-18 19:37:18'),
(28, 3, 16, '2026-10-25', 'snacks', 'Biscuits and Tea', 180, 3.0, 24.0, 8.0, 'Two digestive biscuits', '2026-10-18 19:37:18', '2026-10-18 19:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `nutrition_logs`
--

CREATE TABLE `nutrition_logs` (
  `nutrition_log_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `calories_consumed` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `calories_burned` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `protein_g` int(10) UNSIGNED DEFAULT NULL,
  `carbs_g` int(10) UNSIGNED DEFAULT NULL,
  `fat_g` int(10) UNSIGNED DEFAULT NULL,
  `water_litres` decimal(4,1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `nutrition_logs`
--

INSERT INTO `nutrition_logs` (`nutrition_log_id`, `tenant_id`, `user_id`, `log_date`, `calories_consumed`, `calories_burned`, `protein_g`, `carbs_g`, `fat_g`, `water_litres`, `created_at`) VALUES
(1, 3, 16, '2026-10-02', 2270, 702, 80, 294, 54, 2.6, '2026-10-02 00:00:00'),
(2, 3, 16, '2026-10-01', 1957, 785, 92, 348, 65, 1.5, '2026-10-01 00:00:00'),
(3, 3, 16, '2026-09-30', 2427, 514, 112, 202, 78, 2.2, '2026-09-30 00:00:00'),
(4, 3, 16, '2026-09-29', 2016, 341, 114, 231, 73, 1.9, '2026-09-29 00:00:00'),
(5, 3, 16, '2026-09-28', 2194, 319, 115, 262, 72, 2.3, '2026-09-28 00:00:00'),
(6, 3, 16, '2026-09-27', 1896, 796, 118, 314, 56, 2.3, '2026-09-27 00:00:00'),
(7, 3, 16, '2026-09-26', 1891, 341, 81, 329, 60, 2.5, '2026-09-26 00:00:00'),
(8, 3, 16, '2026-09-25', 2211, 765, 142, 300, 76, 2.0, '2026-09-25 00:00:00'),
(9, 3, 16, '2026-09-24', 2319, 626, 82, 235, 52, 2.4, '2026-09-24 00:00:00'),
(10, 3, 16, '2026-09-23', 2443, 663, 141, 227, 62, 3.0, '2026-09-23 00:00:00'),
(11, 3, 16, '2026-09-22', 1820, 385, 134, 251, 65, 2.9, '2026-09-22 00:00:00'),
(12, 3, 16, '2026-09-21', 2154, 660, 86, 242, 56, 2.9, '2026-09-21 00:00:00'),
(13, 3, 16, '2026-09-20', 1947, 421, 120, 224, 53, 3.0, '2026-09-20 00:00:00'),
(14, 3, 16, '2026-09-19', 2211, 308, 102, 283, 82, 2.1, '2026-09-19 00:00:00'),
(15, 3, 16, '2026-09-18', 2250, 780, 140, 270, 79, 2.0, '2026-09-18 00:00:00'),
(16, 3, 16, '2026-09-17', 2053, 723, 90, 230, 72, 1.8, '2026-09-17 00:00:00'),
(17, 3, 16, '2026-09-16', 2054, 392, 138, 292, 73, 1.6, '2026-09-16 00:00:00'),
(18, 3, 16, '2026-09-15', 2114, 370, 104, 253, 78, 2.3, '2026-09-15 00:00:00'),
(19, 3, 16, '2026-09-14', 2114, 641, 84, 244, 61, 2.3, '2026-09-14 00:00:00'),
(20, 3, 16, '2026-09-13', 2332, 418, 142, 317, 58, 2.6, '2026-09-13 00:00:00'),
(21, 3, 16, '2026-09-12', 1909, 543, 147, 256, 88, 2.6, '2026-09-12 00:00:00'),
(22, 3, 16, '2026-09-11', 2303, 510, 146, 268, 68, 2.8, '2026-09-11 00:00:00'),
(23, 3, 16, '2026-09-10', 1851, 655, 103, 282, 79, 1.6, '2026-09-10 00:00:00'),
(24, 3, 16, '2026-09-09', 1895, 528, 141, 200, 65, 2.9, '2026-09-09 00:00:00'),
(25, 3, 16, '2026-09-08', 2109, 527, 146, 258, 53, 1.9, '2026-09-08 00:00:00'),
(26, 3, 16, '2026-09-07', 1857, 601, 134, 207, 87, 2.3, '2026-09-07 00:00:00'),
(27, 3, 16, '2026-09-06', 2465, 343, 120, 298, 71, 2.6, '2026-09-06 00:00:00'),
(28, 3, 16, '2026-09-05', 1876, 449, 91, 342, 59, 2.0, '2026-09-05 00:00:00'),
(29, 3, 16, '2026-09-04', 1824, 362, 116, 230, 69, 2.6, '2026-09-04 00:00:00'),
(30, 3, 16, '2026-09-03', 2075, 639, 94, 203, 68, 2.0, '2026-09-03 00:00:00'),
(31, 3, 16, '2026-09-02', 1860, 563, 106, 247, 67, 1.9, '2026-09-02 00:00:00'),
(32, 3, 16, '2026-09-01', 1828, 488, 133, 304, 57, 2.7, '2026-09-01 00:00:00'),
(33, 3, 16, '2026-08-31', 2223, 566, 139, 295, 76, 2.0, '2026-08-31 00:00:00'),
(34, 3, 16, '2026-08-30', 2363, 781, 108, 217, 64, 2.2, '2026-08-30 00:00:00'),
(35, 3, 16, '2026-08-29', 2049, 454, 113, 270, 86, 1.7, '2026-08-29 00:00:00'),
(36, 3, 16, '2026-08-28', 2433, 373, 81, 303, 64, 2.6, '2026-08-28 00:00:00'),
(37, 3, 16, '2026-08-27', 2200, 636, 125, 231, 54, 2.9, '2026-08-27 00:00:00'),
(38, 3, 16, '2026-08-26', 1990, 600, 92, 218, 52, 2.9, '2026-08-26 00:00:00'),
(39, 3, 16, '2026-08-25', 2136, 603, 121, 222, 88, 2.1, '2026-08-25 00:00:00'),
(40, 3, 16, '2026-08-24', 1841, 358, 108, 300, 55, 2.5, '2026-08-24 00:00:00'),
(41, 3, 16, '2026-08-23', 2357, 336, 148, 301, 68, 1.8, '2026-08-23 00:00:00'),
(42, 3, 16, '2026-08-22', 2359, 449, 86, 287, 74, 2.0, '2026-08-22 00:00:00'),
(43, 3, 16, '2026-08-21', 2440, 553, 135, 268, 85, 1.7, '2026-08-21 00:00:00'),
(44, 3, 16, '2026-08-20', 2442, 411, 105, 218, 72, 2.1, '2026-08-20 00:00:00'),
(45, 3, 16, '2026-08-19', 2032, 529, 100, 218, 78, 1.7, '2026-08-19 00:00:00'),
(46, 3, 16, '2026-08-18', 2274, 756, 117, 340, 53, 2.4, '2026-08-18 00:00:00'),
(47, 3, 16, '2026-08-17', 2335, 306, 134, 324, 82, 2.4, '2026-08-17 00:00:00'),
(48, 3, 16, '2026-08-16', 2165, 714, 120, 263, 64, 2.4, '2026-08-16 00:00:00'),
(49, 3, 16, '2026-08-15', 2413, 590, 99, 295, 63, 2.7, '2026-08-15 00:00:00'),
(50, 3, 16, '2026-08-14', 1844, 729, 87, 339, 64, 2.9, '2026-08-14 00:00:00'),
(51, 3, 16, '2026-08-13', 2334, 763, 104, 342, 78, 2.5, '2026-08-13 00:00:00'),
(52, 3, 16, '2026-08-12', 2018, 551, 120, 254, 53, 2.1, '2026-08-12 00:00:00'),
(53, 3, 16, '2026-08-11', 2194, 659, 143, 251, 51, 1.7, '2026-08-11 00:00:00'),
(54, 3, 16, '2026-08-10', 2095, 703, 134, 265, 84, 1.5, '2026-08-10 00:00:00'),
(55, 3, 16, '2026-08-09', 2118, 424, 141, 297, 74, 1.7, '2026-08-09 00:00:00'),
(56, 3, 16, '2026-08-08', 2411, 764, 81, 248, 72, 2.7, '2026-08-08 00:00:00'),
(57, 3, 16, '2026-08-07', 2082, 584, 124, 272, 69, 1.6, '2026-08-07 00:00:00'),
(58, 3, 16, '2026-08-06', 2319, 583, 122, 253, 87, 2.5, '2026-08-06 00:00:00'),
(59, 3, 16, '2026-08-05', 2150, 549, 149, 267, 61, 1.6, '2026-08-05 00:00:00'),
(60, 3, 16, '2026-08-04', 2171, 511, 116, 251, 55, 2.5, '2026-08-04 00:00:00'),
(61, 3, 16, '2026-08-03', 1829, 363, 115, 224, 61, 2.9, '2026-08-03 00:00:00'),
(62, 3, 16, '2026-08-02', 2365, 421, 135, 235, 81, 1.9, '2026-08-02 00:00:00'),
(63, 3, 16, '2026-08-01', 1834, 490, 132, 292, 83, 2.0, '2026-08-01 00:00:00'),
(64, 3, 16, '2026-07-31', 1849, 506, 139, 201, 70, 2.3, '2026-07-31 00:00:00'),
(65, 3, 16, '2026-07-30', 1889, 316, 135, 324, 81, 2.2, '2026-07-30 00:00:00'),
(66, 3, 16, '2026-07-29', 1844, 722, 82, 294, 51, 2.0, '2026-07-29 00:00:00'),
(67, 3, 16, '2026-07-28', 2103, 418, 142, 310, 50, 2.7, '2026-07-28 00:00:00'),
(68, 3, 16, '2026-07-27', 1800, 601, 81, 240, 61, 2.5, '2026-07-27 00:00:00'),
(69, 3, 16, '2026-07-26', 2097, 372, 111, 320, 77, 3.0, '2026-07-26 00:00:00'),
(70, 3, 16, '2026-07-25', 2434, 580, 86, 314, 71, 2.2, '2026-07-25 00:00:00'),
(71, 3, 16, '2026-07-24', 2181, 508, 111, 202, 79, 2.4, '2026-07-24 00:00:00'),
(72, 3, 16, '2026-07-23', 2336, 330, 80, 326, 57, 2.2, '2026-07-23 00:00:00'),
(73, 3, 16, '2026-07-22', 2236, 697, 87, 218, 62, 1.8, '2026-07-22 00:00:00'),
(74, 3, 16, '2026-07-21', 1848, 667, 113, 224, 65, 2.1, '2026-07-21 00:00:00'),
(75, 3, 16, '2026-07-20', 2472, 572, 139, 287, 65, 1.8, '2026-07-20 00:00:00'),
(76, 3, 16, '2026-07-19', 2446, 776, 149, 214, 70, 1.9, '2026-07-19 00:00:00'),
(77, 3, 16, '2026-07-18', 2262, 598, 80, 236, 58, 1.9, '2026-07-18 00:00:00'),
(78, 3, 16, '2026-07-17', 2333, 795, 126, 257, 85, 2.0, '2026-07-17 00:00:00'),
(79, 3, 16, '2026-07-16', 2466, 691, 83, 337, 67, 2.2, '2026-07-16 00:00:00'),
(80, 3, 16, '2026-07-15', 2399, 789, 102, 304, 70, 2.2, '2026-07-15 00:00:00'),
(81, 3, 16, '2026-07-14', 2392, 696, 110, 315, 72, 2.2, '2026-07-14 00:00:00'),
(82, 3, 16, '2026-07-13', 2317, 424, 81, 256, 82, 2.9, '2026-07-13 00:00:00'),
(83, 3, 16, '2026-07-12', 1962, 480, 87, 265, 84, 1.5, '2026-07-12 00:00:00'),
(84, 3, 16, '2026-07-11', 2155, 538, 140, 331, 82, 2.1, '2026-07-11 00:00:00'),
(85, 3, 16, '2026-07-10', 2150, 479, 100, 256, 50, 2.9, '2026-07-10 00:00:00'),
(86, 3, 16, '2026-07-09', 2285, 610, 81, 239, 59, 2.0, '2026-07-09 00:00:00'),
(87, 3, 16, '2026-07-08', 1888, 572, 104, 213, 66, 2.7, '2026-07-08 00:00:00'),
(88, 3, 16, '2026-07-07', 2375, 633, 140, 252, 55, 2.4, '2026-07-07 00:00:00'),
(89, 3, 16, '2026-07-06', 2192, 321, 116, 277, 89, 2.1, '2026-07-06 00:00:00'),
(90, 3, 16, '2026-07-05', 2481, 655, 124, 207, 63, 2.4, '2026-07-05 00:00:00'),
(91, 3, 16, '2026-07-04', 2396, 563, 85, 323, 84, 2.8, '2026-07-04 00:00:00'),
(92, 3, 16, '2026-07-03', 2398, 594, 106, 220, 71, 1.9, '2026-07-03 00:00:00'),
(93, 3, 16, '2026-07-02', 2370, 406, 123, 271, 70, 1.7, '2026-07-02 00:00:00'),
(94, 3, 16, '2026-07-01', 1855, 315, 144, 273, 78, 1.6, '2026-07-01 00:00:00'),
(95, 3, 16, '2026-06-30', 1977, 318, 109, 348, 78, 2.3, '2026-06-30 00:00:00'),
(96, 3, 16, '2026-06-29', 2211, 461, 139, 239, 81, 1.7, '2026-06-29 00:00:00'),
(97, 3, 16, '2026-06-28', 2014, 366, 132, 251, 68, 1.9, '2026-06-28 00:00:00'),
(98, 3, 16, '2026-06-27', 2480, 325, 103, 279, 75, 2.5, '2026-06-27 00:00:00'),
(99, 3, 16, '2026-06-26', 1971, 456, 138, 233, 74, 2.1, '2026-06-26 00:00:00'),
(100, 3, 16, '2026-06-25', 1996, 360, 133, 265, 85, 1.8, '2026-06-25 00:00:00'),
(101, 3, 16, '2026-06-24', 1941, 536, 133, 257, 75, 1.6, '2026-06-24 00:00:00'),
(102, 3, 16, '2026-06-23', 2129, 353, 88, 239, 88, 1.6, '2026-06-23 00:00:00'),
(103, 3, 16, '2026-06-22', 2082, 714, 145, 228, 55, 1.7, '2026-06-22 00:00:00'),
(104, 3, 16, '2026-06-21', 1913, 534, 139, 330, 81, 2.1, '2026-06-21 00:00:00'),
(105, 3, 16, '2026-06-20', 2117, 379, 110, 307, 60, 1.7, '2026-06-20 00:00:00'),
(106, 3, 16, '2026-06-19', 2428, 345, 133, 278, 63, 1.6, '2026-06-19 00:00:00'),
(107, 3, 16, '2026-06-18', 2139, 366, 94, 300, 77, 2.2, '2026-06-18 00:00:00'),
(108, 3, 16, '2026-06-17', 2032, 403, 82, 285, 79, 3.0, '2026-06-17 00:00:00'),
(109, 3, 16, '2026-06-16', 2244, 435, 111, 265, 83, 2.8, '2026-06-16 00:00:00'),
(110, 3, 16, '2026-06-15', 2460, 317, 104, 292, 51, 2.1, '2026-06-15 00:00:00'),
(111, 3, 16, '2026-06-14', 2359, 716, 133, 248, 63, 2.5, '2026-06-14 00:00:00'),
(112, 3, 16, '2026-06-13', 2048, 686, 135, 300, 87, 2.6, '2026-06-13 00:00:00'),
(113, 3, 16, '2026-06-12', 2360, 709, 128, 201, 88, 2.7, '2026-06-12 00:00:00'),
(114, 3, 16, '2026-06-11', 1877, 374, 108, 290, 81, 1.8, '2026-06-11 00:00:00'),
(115, 3, 16, '2026-06-10', 2131, 723, 136, 278, 57, 2.1, '2026-06-10 00:00:00'),
(116, 3, 16, '2026-06-09', 2035, 566, 125, 302, 67, 1.7, '2026-06-09 00:00:00'),
(117, 3, 16, '2026-06-08', 2028, 427, 101, 310, 80, 2.4, '2026-06-08 00:00:00'),
(118, 3, 16, '2026-06-07', 2402, 503, 111, 209, 87, 2.2, '2026-06-07 00:00:00'),
(119, 3, 16, '2026-06-06', 2261, 709, 88, 220, 63, 1.9, '2026-06-06 00:00:00'),
(120, 3, 16, '2026-06-05', 1986, 584, 83, 280, 71, 1.5, '2026-06-05 00:00:00'),
(121, 3, 16, '2026-06-04', 2199, 680, 86, 229, 77, 2.8, '2026-06-04 00:00:00'),
(122, 3, 16, '2026-06-03', 1981, 648, 129, 266, 53, 1.7, '2026-06-03 00:00:00'),
(123, 3, 16, '2026-06-02', 1987, 312, 102, 278, 76, 2.6, '2026-06-02 00:00:00'),
(124, 3, 16, '2026-06-01', 2369, 689, 111, 337, 58, 2.1, '2026-06-01 00:00:00'),
(125, 3, 16, '2026-05-31', 1956, 788, 95, 222, 53, 1.5, '2026-05-31 00:00:00'),
(126, 3, 16, '2026-05-30', 2411, 436, 132, 334, 60, 2.4, '2026-05-30 00:00:00'),
(127, 3, 16, '2026-05-29', 1924, 361, 85, 205, 87, 2.4, '2026-05-29 00:00:00'),
(128, 3, 16, '2026-05-28', 1929, 356, 81, 310, 75, 3.0, '2026-05-28 00:00:00'),
(129, 3, 16, '2026-05-27', 1808, 349, 112, 203, 78, 2.3, '2026-05-27 00:00:00'),
(130, 3, 16, '2026-05-26', 2135, 708, 125, 315, 86, 1.9, '2026-05-26 00:00:00'),
(131, 3, 16, '2026-05-25', 2205, 352, 135, 292, 79, 2.7, '2026-05-25 00:00:00'),
(132, 3, 16, '2026-05-24', 2297, 409, 147, 225, 88, 1.9, '2026-05-24 00:00:00'),
(133, 3, 16, '2026-05-23', 2133, 587, 111, 274, 56, 1.9, '2026-05-23 00:00:00'),
(134, 3, 16, '2026-05-22', 2398, 545, 142, 344, 56, 2.9, '2026-05-22 00:00:00'),
(135, 3, 16, '2026-05-21', 1957, 441, 131, 328, 52, 2.6, '2026-05-21 00:00:00'),
(136, 3, 16, '2026-05-20', 2216, 648, 129, 263, 50, 2.6, '2026-05-20 00:00:00'),
(137, 3, 16, '2026-05-19', 2298, 464, 115, 283, 60, 2.5, '2026-05-19 00:00:00'),
(138, 3, 16, '2026-05-18', 2088, 368, 111, 328, 87, 1.6, '2026-05-18 00:00:00'),
(139, 3, 16, '2026-05-17', 2263, 316, 92, 319, 67, 2.7, '2026-05-17 00:00:00'),
(140, 3, 16, '2026-05-16', 2320, 439, 91, 200, 70, 2.4, '2026-05-16 00:00:00'),
(141, 3, 16, '2026-05-15', 2008, 690, 80, 307, 72, 2.4, '2026-05-15 00:00:00'),
(142, 3, 16, '2026-05-14', 2126, 526, 140, 342, 56, 2.9, '2026-05-14 00:00:00'),
(143, 3, 16, '2026-05-13', 1981, 529, 115, 227, 64, 2.0, '2026-05-13 00:00:00'),
(144, 3, 16, '2026-05-12', 2139, 530, 139, 333, 85, 2.7, '2026-05-12 00:00:00'),
(145, 3, 16, '2026-05-11', 1927, 600, 111, 271, 51, 2.6, '2026-05-11 00:00:00'),
(146, 3, 16, '2026-05-10', 2102, 326, 147, 301, 68, 2.0, '2026-05-10 00:00:00'),
(147, 3, 16, '2026-05-09', 2011, 537, 113, 340, 60, 2.3, '2026-05-09 00:00:00'),
(148, 3, 16, '2026-05-08', 2435, 752, 136, 244, 53, 2.3, '2026-05-08 00:00:00'),
(149, 3, 16, '2026-05-07', 2006, 778, 142, 291, 64, 1.5, '2026-05-07 00:00:00'),
(150, 3, 16, '2026-05-06', 1804, 778, 133, 344, 70, 2.5, '2026-05-06 00:00:00'),
(151, 3, 16, '2026-05-05', 2409, 451, 143, 290, 62, 2.6, '2026-05-05 00:00:00'),
(152, 3, 16, '2026-05-04', 2340, 619, 141, 271, 80, 2.0, '2026-05-04 00:00:00'),
(153, 3, 16, '2026-05-03', 2119, 423, 140, 290, 66, 1.9, '2026-05-03 00:00:00'),
(154, 3, 16, '2026-05-02', 1876, 663, 101, 253, 84, 1.8, '2026-05-02 00:00:00'),
(155, 3, 16, '2026-05-01', 2172, 798, 107, 343, 74, 1.8, '2026-05-01 00:00:00'),
(156, 3, 16, '2026-04-30', 1830, 648, 105, 304, 66, 1.5, '2026-04-30 00:00:00'),
(157, 3, 16, '2026-04-29', 2326, 677, 116, 247, 50, 1.8, '2026-04-29 00:00:00'),
(158, 3, 16, '2026-04-28', 2350, 509, 131, 266, 89, 2.4, '2026-04-28 00:00:00'),
(159, 3, 16, '2026-04-27', 1875, 646, 90, 296, 81, 1.5, '2026-04-27 00:00:00'),
(160, 3, 16, '2026-04-26', 2254, 420, 98, 285, 53, 2.5, '2026-04-26 00:00:00'),
(161, 3, 16, '2026-04-25', 1870, 552, 95, 290, 63, 2.9, '2026-04-25 00:00:00'),
(162, 3, 16, '2026-04-24', 2228, 432, 114, 297, 81, 2.9, '2026-04-24 00:00:00'),
(163, 3, 16, '2026-04-23', 2063, 327, 90, 285, 66, 2.1, '2026-04-23 00:00:00'),
(164, 3, 16, '2026-04-22', 2219, 744, 124, 280, 81, 1.9, '2026-04-22 00:00:00'),
(165, 3, 16, '2026-04-21', 2475, 325, 105, 299, 58, 1.6, '2026-04-21 00:00:00'),
(166, 3, 16, '2026-04-20', 2317, 536, 90, 245, 53, 2.3, '2026-04-20 00:00:00'),
(167, 3, 16, '2026-04-19', 2055, 423, 89, 343, 64, 2.9, '2026-04-19 00:00:00'),
(168, 3, 16, '2026-04-18', 2198, 330, 121, 320, 58, 2.5, '2026-04-18 00:00:00'),
(169, 3, 16, '2026-04-17', 2270, 488, 140, 228, 64, 1.9, '2026-04-17 00:00:00'),
(170, 3, 16, '2026-04-16', 1904, 300, 119, 318, 61, 1.6, '2026-04-16 00:00:00'),
(171, 3, 16, '2026-04-15', 2076, 710, 144, 224, 51, 2.5, '2026-04-15 00:00:00'),
(172, 3, 16, '2026-04-14', 1923, 764, 87, 317, 72, 2.3, '2026-04-14 00:00:00'),
(173, 3, 16, '2026-04-13', 2378, 604, 119, 348, 60, 2.0, '2026-04-13 00:00:00'),
(174, 3, 16, '2026-04-12', 2378, 387, 107, 264, 89, 2.4, '2026-04-12 00:00:00'),
(175, 3, 16, '2026-04-11', 1934, 336, 135, 307, 58, 2.9, '2026-04-11 00:00:00'),
(176, 3, 16, '2026-04-10', 1802, 416, 90, 206, 80, 2.5, '2026-04-10 00:00:00'),
(177, 3, 16, '2026-04-09', 1863, 515, 141, 219, 50, 2.5, '2026-04-09 00:00:00'),
(178, 3, 16, '2026-04-08', 1930, 304, 113, 256, 67, 1.6, '2026-04-08 00:00:00'),
(179, 3, 16, '2026-04-07', 1827, 789, 134, 342, 67, 1.9, '2026-04-07 00:00:00'),
(180, 3, 16, '2026-04-06', 1924, 304, 115, 277, 51, 2.6, '2026-04-06 00:00:00'),
(181, 3, 16, '2026-04-05', 2052, 650, 109, 202, 82, 3.0, '2026-04-05 00:00:00'),
(182, 3, 16, '2026-04-04', 2192, 704, 105, 259, 85, 1.8, '2026-04-04 00:00:00'),
(183, 3, 16, '2026-04-03', 2105, 568, 106, 240, 58, 1.9, '2026-04-03 00:00:00'),
(184, 3, 16, '2026-04-02', 2284, 629, 95, 215, 84, 3.0, '2026-04-02 00:00:00'),
(185, 3, 16, '2026-04-01', 2059, 743, 102, 341, 79, 2.8, '2026-04-01 00:00:00'),
(186, 3, 16, '2026-03-31', 1963, 544, 131, 237, 50, 2.0, '2026-03-31 00:00:00'),
(187, 3, 16, '2026-03-30', 2188, 708, 109, 297, 89, 2.9, '2026-03-30 00:00:00'),
(188, 3, 16, '2026-03-29', 2385, 457, 84, 256, 77, 2.0, '2026-03-29 00:00:00'),
(189, 3, 16, '2026-03-28', 2141, 554, 85, 331, 55, 1.5, '2026-03-28 00:00:00'),
(190, 3, 16, '2026-03-27', 2302, 564, 114, 328, 82, 2.2, '2026-03-27 00:00:00'),
(191, 3, 16, '2026-03-26', 1809, 600, 147, 202, 57, 2.8, '2026-03-26 00:00:00'),
(192, 3, 16, '2026-03-25', 2340, 435, 82, 258, 82, 2.9, '2026-03-25 00:00:00'),
(193, 3, 16, '2026-03-24', 1928, 366, 88, 228, 73, 2.1, '2026-03-24 00:00:00'),
(194, 3, 16, '2026-03-23', 2011, 427, 105, 207, 56, 2.5, '2026-03-23 00:00:00'),
(195, 3, 16, '2026-03-22', 2488, 710, 90, 246, 52, 2.2, '2026-03-22 00:00:00'),
(196, 3, 16, '2026-03-21', 2484, 585, 144, 336, 81, 1.8, '2026-03-21 00:00:00'),
(197, 3, 16, '2026-03-20', 2187, 413, 113, 303, 51, 1.6, '2026-03-20 00:00:00'),
(198, 3, 16, '2026-03-19', 1988, 366, 139, 331, 83, 2.3, '2026-03-19 00:00:00'),
(199, 3, 16, '2026-03-18', 1871, 777, 112, 271, 88, 2.1, '2026-03-18 00:00:00'),
(200, 3, 16, '2026-03-17', 1889, 512, 132, 265, 88, 2.3, '2026-03-17 00:00:00'),
(201, 3, 16, '2026-03-16', 2267, 699, 149, 282, 81, 1.9, '2026-03-16 00:00:00'),
(202, 3, 16, '2026-03-15', 1816, 443, 105, 347, 82, 1.7, '2026-03-15 00:00:00'),
(203, 3, 16, '2026-03-14', 1918, 538, 141, 345, 58, 1.7, '2026-03-14 00:00:00'),
(204, 3, 16, '2026-03-13', 1893, 402, 123, 273, 72, 2.1, '2026-03-13 00:00:00'),
(205, 3, 16, '2026-03-12', 2031, 511, 88, 251, 64, 2.6, '2026-03-12 00:00:00'),
(206, 3, 16, '2026-03-11', 2200, 641, 129, 269, 58, 2.5, '2026-03-11 00:00:00'),
(207, 3, 16, '2026-03-10', 2220, 335, 118, 282, 54, 2.8, '2026-03-10 00:00:00'),
(208, 3, 16, '2026-03-09', 2469, 413, 98, 298, 68, 2.1, '2026-03-09 00:00:00'),
(209, 3, 16, '2026-03-08', 2126, 406, 126, 300, 64, 2.8, '2026-03-08 00:00:00'),
(210, 3, 16, '2026-03-07', 1848, 720, 149, 266, 59, 2.9, '2026-03-07 00:00:00'),
(211, 3, 16, '2026-03-06', 2355, 420, 137, 260, 71, 2.2, '2026-03-06 00:00:00'),
(212, 3, 16, '2026-03-05', 2288, 357, 113, 204, 79, 2.4, '2026-03-05 00:00:00'),
(213, 3, 16, '2026-03-04', 2363, 404, 123, 275, 75, 2.6, '2026-03-04 00:00:00'),
(214, 3, 16, '2026-03-03', 2205, 696, 95, 311, 52, 1.5, '2026-03-03 00:00:00'),
(215, 3, 16, '2026-03-02', 2470, 659, 130, 269, 55, 2.0, '2026-03-02 00:00:00'),
(216, 3, 16, '2026-03-01', 1961, 378, 86, 200, 78, 2.4, '2026-03-01 00:00:00'),
(217, 3, 16, '2026-02-28', 2365, 428, 140, 283, 57, 1.9, '2026-02-28 00:00:00'),
(218, 3, 16, '2026-02-27', 2333, 309, 136, 347, 69, 2.2, '2026-02-27 00:00:00'),
(219, 3, 16, '2026-02-26', 2424, 327, 122, 327, 67, 2.5, '2026-02-26 00:00:00'),
(220, 3, 16, '2026-02-25', 2436, 604, 102, 317, 87, 2.1, '2026-02-25 00:00:00'),
(221, 3, 16, '2026-02-24', 1817, 305, 148, 331, 66, 2.3, '2026-02-24 00:00:00'),
(222, 3, 16, '2026-02-23', 1965, 637, 126, 247, 73, 2.9, '2026-02-23 00:00:00'),
(223, 3, 16, '2026-02-22', 2499, 377, 134, 263, 81, 2.5, '2026-02-22 00:00:00'),
(224, 3, 16, '2026-02-21', 1858, 468, 110, 226, 72, 2.0, '2026-02-21 00:00:00'),
(225, 3, 16, '2026-02-20', 2458, 654, 130, 271, 59, 2.6, '2026-02-20 00:00:00'),
(226, 3, 16, '2026-02-19', 2382, 326, 133, 303, 55, 2.4, '2026-02-19 00:00:00'),
(227, 3, 16, '2026-02-18', 2244, 474, 138, 223, 60, 2.7, '2026-02-18 00:00:00'),
(228, 3, 16, '2026-02-17', 2007, 321, 102, 274, 70, 1.6, '2026-02-17 00:00:00'),
(229, 3, 16, '2026-02-16', 2430, 417, 112, 296, 81, 1.6, '2026-02-16 00:00:00'),
(230, 3, 16, '2026-02-15', 2432, 471, 80, 349, 89, 2.9, '2026-02-15 00:00:00'),
(231, 3, 16, '2026-02-14', 2213, 416, 107, 237, 52, 2.4, '2026-02-14 00:00:00'),
(232, 3, 16, '2026-02-13', 2385, 478, 99, 243, 75, 1.9, '2026-02-13 00:00:00'),
(233, 3, 16, '2026-02-12', 2187, 748, 138, 268, 81, 2.3, '2026-02-12 00:00:00'),
(234, 3, 16, '2026-02-11', 2041, 358, 118, 257, 61, 1.9, '2026-02-11 00:00:00'),
(235, 3, 16, '2026-02-10', 2067, 389, 132, 229, 79, 1.6, '2026-02-10 00:00:00'),
(236, 3, 16, '2026-02-09', 1974, 786, 88, 302, 52, 1.9, '2026-02-09 00:00:00'),
(237, 3, 16, '2026-02-08', 1837, 563, 112, 316, 68, 3.0, '2026-02-08 00:00:00'),
(238, 3, 16, '2026-02-07', 2210, 771, 146, 340, 83, 2.0, '2026-02-07 00:00:00'),
(239, 3, 16, '2026-02-06', 1963, 363, 145, 246, 79, 2.7, '2026-02-06 00:00:00'),
(240, 3, 16, '2026-02-05', 2304, 414, 148, 231, 54, 2.9, '2026-02-05 00:00:00'),
(241, 3, 16, '2026-02-04', 2022, 699, 82, 318, 83, 2.7, '2026-02-04 00:00:00'),
(242, 3, 16, '2026-02-03', 2149, 350, 80, 305, 70, 2.2, '2026-02-03 00:00:00'),
(243, 3, 16, '2026-02-02', 2373, 636, 143, 275, 82, 2.3, '2026-02-02 00:00:00'),
(244, 3, 16, '2026-02-01', 1995, 685, 80, 312, 78, 2.0, '2026-02-01 00:00:00'),
(245, 3, 16, '2026-01-31', 2209, 738, 123, 275, 75, 2.5, '2026-01-31 00:00:00'),
(246, 3, 16, '2026-01-30', 2081, 313, 144, 282, 89, 1.9, '2026-01-30 00:00:00'),
(247, 3, 16, '2026-01-29', 2070, 364, 114, 206, 80, 2.5, '2026-01-29 00:00:00'),
(248, 3, 16, '2026-01-28', 1811, 353, 114, 219, 57, 2.2, '2026-01-28 00:00:00'),
(249, 3, 16, '2026-01-27', 2455, 400, 93, 255, 60, 1.8, '2026-01-27 00:00:00'),
(250, 3, 16, '2026-01-26', 1895, 368, 99, 348, 54, 2.3, '2026-01-26 00:00:00'),
(251, 3, 16, '2026-01-25', 2122, 620, 137, 229, 69, 2.9, '2026-01-25 00:00:00'),
(252, 3, 16, '2026-01-24', 1825, 532, 95, 305, 83, 1.7, '2026-01-24 00:00:00'),
(253, 3, 16, '2026-01-23', 1889, 417, 135, 238, 86, 2.7, '2026-01-23 00:00:00'),
(254, 3, 16, '2026-01-22', 1945, 636, 132, 304, 59, 1.7, '2026-01-22 00:00:00'),
(255, 3, 16, '2026-01-21', 2474, 500, 88, 256, 71, 2.3, '2026-01-21 00:00:00'),
(256, 3, 16, '2026-01-20', 1918, 389, 107, 259, 83, 2.9, '2026-01-20 00:00:00'),
(257, 3, 16, '2026-01-19', 2034, 687, 140, 204, 71, 2.4, '2026-01-19 00:00:00'),
(258, 3, 16, '2026-01-18', 2089, 425, 80, 244, 68, 2.0, '2026-01-18 00:00:00'),
(259, 3, 16, '2026-01-17', 2126, 421, 137, 252, 62, 2.3, '2026-01-17 00:00:00'),
(260, 3, 16, '2026-01-16', 2262, 667, 128, 242, 62, 2.6, '2026-01-16 00:00:00'),
(261, 3, 16, '2026-01-15', 2271, 400, 149, 249, 77, 2.2, '2026-01-15 00:00:00'),
(262, 3, 16, '2026-01-14', 2077, 543, 97, 316, 55, 2.0, '2026-01-14 00:00:00'),
(263, 3, 16, '2026-01-13', 1985, 464, 139, 239, 80, 1.6, '2026-01-13 00:00:00'),
(264, 3, 16, '2026-01-12', 2431, 495, 97, 210, 74, 2.8, '2026-01-12 00:00:00'),
(265, 3, 16, '2026-01-11', 2068, 494, 135, 317, 71, 2.0, '2026-01-11 00:00:00'),
(266, 3, 16, '2026-01-10', 1843, 454, 104, 328, 58, 2.3, '2026-01-10 00:00:00'),
(267, 3, 16, '2026-01-09', 1838, 618, 81, 228, 85, 2.9, '2026-01-09 00:00:00'),
(268, 3, 16, '2026-01-08', 2415, 625, 123, 218, 81, 2.3, '2026-01-08 00:00:00'),
(269, 3, 16, '2026-01-07', 1992, 703, 94, 296, 73, 3.0, '2026-01-07 00:00:00'),
(270, 3, 16, '2026-01-06', 1928, 777, 95, 240, 76, 2.3, '2026-01-06 00:00:00'),
(271, 3, 16, '2026-01-05', 2314, 312, 144, 280, 86, 2.9, '2026-01-05 00:00:00'),
(272, 3, 16, '2026-01-04', 2400, 574, 91, 229, 68, 2.6, '2026-01-04 00:00:00'),
(273, 3, 16, '2026-01-03', 2089, 686, 123, 322, 58, 2.4, '2026-01-03 00:00:00'),
(274, 3, 16, '2026-01-02', 2100, 449, 94, 223, 56, 2.0, '2026-01-02 00:00:00'),
(275, 3, 16, '2026-01-01', 1859, 544, 93, 273, 85, 2.9, '2026-01-01 00:00:00'),
(276, 3, 16, '2025-12-31', 2456, 775, 145, 327, 66, 2.3, '2025-12-31 00:00:00'),
(277, 3, 16, '2025-12-30', 2171, 790, 101, 290, 53, 2.4, '2025-12-30 00:00:00'),
(278, 3, 16, '2025-12-29', 2266, 597, 148, 214, 72, 2.2, '2025-12-29 00:00:00'),
(279, 3, 16, '2025-12-28', 2300, 374, 122, 283, 89, 1.8, '2025-12-28 00:00:00'),
(280, 3, 16, '2025-12-27', 1927, 420, 125, 282, 81, 2.0, '2025-12-27 00:00:00'),
(281, 3, 16, '2025-12-26', 2013, 551, 122, 274, 77, 2.9, '2025-12-26 00:00:00'),
(282, 3, 16, '2025-12-25', 2226, 423, 108, 245, 61, 2.3, '2025-12-25 00:00:00'),
(283, 3, 16, '2025-12-24', 2366, 519, 133, 278, 62, 1.5, '2025-12-24 00:00:00'),
(284, 3, 16, '2025-12-23', 1894, 611, 129, 303, 62, 2.2, '2025-12-23 00:00:00'),
(285, 3, 16, '2025-12-22', 2016, 411, 92, 236, 76, 2.4, '2025-12-22 00:00:00'),
(286, 3, 16, '2025-12-21', 1817, 454, 112, 262, 77, 1.8, '2025-12-21 00:00:00'),
(287, 3, 16, '2025-12-20', 2395, 651, 147, 304, 74, 2.9, '2025-12-20 00:00:00'),
(288, 3, 16, '2025-12-19', 2474, 769, 136, 232, 76, 2.4, '2025-12-19 00:00:00'),
(289, 3, 16, '2025-12-18', 1894, 708, 127, 345, 82, 1.7, '2025-12-18 00:00:00'),
(290, 3, 16, '2025-12-17', 1900, 489, 112, 230, 74, 2.2, '2025-12-17 00:00:00'),
(291, 3, 16, '2025-12-16', 2082, 638, 92, 324, 75, 2.6, '2025-12-16 00:00:00'),
(292, 3, 16, '2025-12-15', 2325, 567, 109, 276, 61, 2.9, '2025-12-15 00:00:00'),
(293, 3, 16, '2025-12-14', 2328, 796, 129, 279, 72, 1.8, '2025-12-14 00:00:00'),
(294, 3, 16, '2025-12-13', 1989, 692, 87, 231, 78, 2.8, '2025-12-13 00:00:00'),
(295, 3, 16, '2025-12-12', 2008, 725, 105, 236, 56, 1.5, '2025-12-12 00:00:00'),
(296, 3, 16, '2025-12-11', 2263, 420, 94, 251, 53, 2.1, '2025-12-11 00:00:00'),
(297, 3, 16, '2025-12-10', 2226, 767, 139, 266, 76, 1.5, '2025-12-10 00:00:00'),
(298, 3, 16, '2025-12-09', 1832, 400, 140, 306, 88, 2.5, '2025-12-09 00:00:00'),
(299, 3, 16, '2025-12-08', 2142, 510, 124, 341, 81, 1.6, '2025-12-08 00:00:00'),
(300, 3, 16, '2025-12-07', 1833, 301, 141, 256, 60, 1.7, '2025-12-07 00:00:00'),
(301, 3, 16, '2025-12-06', 1802, 576, 132, 216, 60, 1.6, '2025-12-06 00:00:00'),
(302, 3, 16, '2025-12-05', 2111, 332, 149, 311, 79, 2.3, '2025-12-05 00:00:00'),
(303, 3, 16, '2025-12-04', 2028, 346, 113, 220, 59, 2.7, '2025-12-04 00:00:00'),
(304, 3, 16, '2025-12-03', 1972, 723, 114, 341, 58, 1.9, '2025-12-03 00:00:00'),
(305, 3, 16, '2025-12-02', 2246, 508, 91, 288, 68, 2.2, '2025-12-02 00:00:00'),
(306, 3, 16, '2025-12-01', 1881, 347, 88, 249, 61, 2.2, '2025-12-01 00:00:00'),
(307, 3, 16, '2025-11-30', 2163, 375, 93, 280, 53, 2.7, '2025-11-30 00:00:00'),
(308, 3, 16, '2025-11-29', 2421, 772, 84, 274, 61, 2.9, '2025-11-29 00:00:00'),
(309, 3, 16, '2025-11-28', 2268, 623, 96, 231, 64, 1.7, '2025-11-28 00:00:00'),
(310, 3, 16, '2025-11-27', 2253, 706, 88, 226, 70, 1.6, '2025-11-27 00:00:00'),
(311, 3, 16, '2025-11-26', 2385, 767, 91, 206, 78, 2.1, '2025-11-26 00:00:00'),
(312, 3, 16, '2025-11-25', 2360, 731, 143, 346, 55, 2.6, '2025-11-25 00:00:00'),
(313, 3, 16, '2025-11-24', 2013, 450, 121, 206, 67, 1.6, '2025-11-24 00:00:00'),
(314, 3, 16, '2025-11-23', 1803, 717, 91, 243, 89, 1.6, '2025-11-23 00:00:00'),
(315, 3, 16, '2025-11-22', 1957, 310, 109, 211, 54, 2.0, '2025-11-22 00:00:00'),
(316, 3, 16, '2025-11-21', 1991, 502, 93, 317, 63, 1.9, '2025-11-21 00:00:00'),
(317, 3, 16, '2025-11-20', 2145, 588, 108, 243, 60, 2.1, '2025-11-20 00:00:00'),
(318, 3, 16, '2025-11-19', 1875, 511, 135, 304, 53, 2.1, '2025-11-19 00:00:00'),
(319, 3, 16, '2025-11-18', 2280, 423, 92, 219, 54, 1.8, '2025-11-18 00:00:00'),
(320, 3, 16, '2025-11-17', 2300, 765, 115, 311, 58, 2.7, '2025-11-17 00:00:00'),
(321, 3, 16, '2025-11-16', 2035, 456, 118, 322, 66, 2.5, '2025-11-16 00:00:00'),
(322, 3, 16, '2025-11-15', 1844, 461, 109, 219, 65, 2.3, '2025-11-15 00:00:00'),
(323, 3, 16, '2025-11-14', 2085, 558, 104, 234, 53, 2.7, '2025-11-14 00:00:00'),
(324, 3, 16, '2025-11-13', 2219, 631, 116, 291, 70, 2.5, '2025-11-13 00:00:00'),
(325, 3, 16, '2025-11-12', 2398, 429, 131, 332, 59, 2.3, '2025-11-12 00:00:00'),
(326, 3, 16, '2025-11-11', 2375, 598, 116, 318, 66, 2.5, '2025-11-11 00:00:00'),
(327, 3, 16, '2025-11-10', 1890, 615, 134, 344, 70, 2.4, '2025-11-10 00:00:00'),
(328, 3, 16, '2025-11-09', 2246, 448, 120, 347, 57, 1.5, '2025-11-09 00:00:00'),
(329, 3, 16, '2025-11-08', 2107, 396, 125, 297, 62, 2.5, '2025-11-08 00:00:00'),
(330, 3, 16, '2025-11-07', 1953, 397, 102, 201, 53, 2.2, '2025-11-07 00:00:00'),
(331, 3, 16, '2025-11-06', 2456, 477, 147, 312, 84, 1.7, '2025-11-06 00:00:00'),
(332, 3, 16, '2025-11-05', 2449, 454, 133, 332, 55, 1.5, '2025-11-05 00:00:00'),
(333, 3, 16, '2025-11-04', 2254, 408, 89, 206, 81, 2.7, '2025-11-04 00:00:00'),
(334, 3, 16, '2025-11-03', 2321, 428, 83, 272, 60, 2.8, '2025-11-03 00:00:00'),
(335, 3, 16, '2025-11-02', 2244, 555, 125, 310, 78, 2.0, '2025-11-02 00:00:00'),
(336, 3, 16, '2025-11-01', 2209, 749, 132, 202, 83, 1.8, '2025-11-01 00:00:00'),
(337, 3, 16, '2025-10-31', 2065, 473, 121, 342, 88, 2.9, '2025-10-31 00:00:00'),
(338, 3, 16, '2025-10-30', 2372, 436, 143, 310, 88, 2.4, '2025-10-30 00:00:00'),
(339, 3, 16, '2025-10-29', 2493, 416, 93, 240, 80, 1.6, '2025-10-29 00:00:00'),
(340, 3, 16, '2025-10-28', 2445, 534, 120, 271, 75, 2.7, '2025-10-28 00:00:00'),
(341, 3, 16, '2025-10-27', 1897, 423, 137, 251, 60, 1.9, '2025-10-27 00:00:00'),
(342, 3, 16, '2025-10-26', 2185, 780, 90, 329, 84, 2.6, '2025-10-26 00:00:00'),
(343, 3, 16, '2025-10-25', 2461, 607, 96, 251, 89, 2.9, '2025-10-25 00:00:00'),
(344, 3, 16, '2025-10-24', 2375, 411, 125, 286, 87, 3.0, '2025-10-24 00:00:00'),
(345, 3, 16, '2025-10-23', 1861, 544, 92, 263, 73, 2.5, '2025-10-23 00:00:00'),
(346, 3, 16, '2025-10-22', 2194, 709, 108, 286, 76, 2.5, '2025-10-22 00:00:00'),
(347, 3, 16, '2025-10-21', 1908, 732, 140, 307, 50, 2.8, '2025-10-21 00:00:00'),
(348, 3, 16, '2025-10-20', 2067, 433, 93, 222, 56, 2.1, '2025-10-20 00:00:00'),
(349, 3, 16, '2025-10-19', 2185, 558, 145, 214, 77, 1.7, '2025-10-19 00:00:00'),
(350, 3, 16, '2025-10-18', 2236, 656, 128, 251, 74, 1.6, '2025-10-18 00:00:00'),
(351, 3, 16, '2025-10-17', 2068, 693, 134, 282, 65, 2.0, '2025-10-17 00:00:00'),
(352, 3, 16, '2025-10-16', 2184, 650, 139, 223, 58, 2.4, '2025-10-16 00:00:00'),
(353, 3, 16, '2025-10-15', 2149, 600, 115, 313, 59, 2.9, '2025-10-15 00:00:00'),
(354, 3, 16, '2025-10-14', 2427, 659, 143, 252, 51, 1.7, '2025-10-14 00:00:00'),
(355, 3, 16, '2025-10-13', 2256, 700, 83, 323, 89, 2.2, '2025-10-13 00:00:00'),
(356, 3, 16, '2025-10-12', 1959, 719, 115, 207, 78, 2.0, '2025-10-12 00:00:00'),
(357, 3, 16, '2025-10-11', 2281, 483, 134, 314, 70, 1.8, '2025-10-11 00:00:00'),
(358, 3, 16, '2025-10-10', 2252, 568, 131, 213, 59, 2.8, '2025-10-10 00:00:00'),
(359, 3, 16, '2025-10-09', 2241, 584, 146, 206, 65, 2.6, '2025-10-09 00:00:00'),
(360, 3, 16, '2025-10-08', 2243, 754, 124, 271, 68, 2.8, '2025-10-08 00:00:00'),
(361, 3, 16, '2025-10-07', 2480, 423, 102, 332, 67, 2.2, '2025-10-07 00:00:00'),
(362, 3, 16, '2025-10-06', 1939, 553, 145, 223, 89, 2.1, '2025-10-06 00:00:00'),
(363, 3, 16, '2025-10-05', 1898, 529, 141, 201, 67, 1.7, '2025-10-05 00:00:00'),
(364, 3, 16, '2025-10-04', 1969, 746, 131, 204, 87, 2.3, '2025-10-04 00:00:00'),
(365, 3, 16, '2025-10-03', 2461, 343, 121, 309, 83, 1.6, '2025-10-03 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `reset_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `request_ip_hash` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` smallint(5) UNSIGNED NOT NULL,
  `permission_key` varchar(80) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`permission_id`, `permission_key`, `description`) VALUES
(1, 'view_public_content', 'View published public content'),
(2, 'view_own_profile', 'View the current user profile'),
(3, 'edit_own_profile', 'Edit the current user profile'),
(4, 'create_activity', 'Create personal activity records'),
(5, 'edit_own_activity', 'Edit personal activity records'),
(6, 'delete_own_activity', 'Delete personal activity records'),
(7, 'manage_activity_content', 'Create, edit, publish, and remove activity content'),
(8, 'manage_exercises', 'Create, edit, publish, and remove exercises'),
(9, 'review_reports', 'Review and resolve tenant reports'),
(10, 'manage_users', 'Manage tenant user memberships and statuses'),
(11, 'manage_roles', 'Assign tenant-scoped roles'),
(12, 'view_audit_logs', 'View tenant audit logs');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` enum('draft','active','completed','archived') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`plan_id`, `tenant_id`, `user_id`, `title`, `start_date`, `end_date`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 18, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 3, 21, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 3, 24, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 4, 27, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 4, 30, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 4, 33, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 5, 36, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 5, 39, 'Weekly movement plan', '2026-09-30', '2026-10-06', 'Synthetic demonstration plan for testing.', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, 3, 16, 'Weekly Training Plan', '2026-09-03', '2026-12-02', 'Balanced weekly routine', 'active', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(17, 3, 1, '2 weeks', '2026-10-14', '2026-10-31', '', 'active', '2026-10-14 17:20:09', '2026-10-14 17:20:09'),
(18, 3, 16, 'run', '2026-10-14', '2026-10-18', '', 'active', '2026-10-14 18:23:12', '2026-10-14 18:23:12'),
(19, 3, 16, 'gym', '2026-10-14', '2026-10-28', '', 'active', '2026-10-14 18:23:36', '2026-10-14 18:23:36'),
(20, 3, 16, 'gym', '2026-10-15', '2026-10-21', '', 'active', '2026-10-14 18:24:08', '2026-10-14 18:24:08'),
(21, 3, 16, 'swimming', '2026-10-16', '2026-10-16', '', 'active', '2026-10-15 16:16:53', '2026-10-15 16:16:53'),
(22, 3, 16, 'gym 1st session', '2026-10-15', '2026-10-22', '', 'active', '2026-10-15 16:17:31', '2026-10-15 16:17:31'),
(23, 3, 16, 'run', '2026-10-15', '2026-10-18', '', 'active', '2026-10-15 21:13:46', '2026-10-15 21:13:46'),
(24, 3, 16, 'walking plan', '2026-10-16', '2026-10-23', NULL, 'active', '2026-10-16 08:41:31', '2026-10-16 08:41:31'),
(25, 3, 16, 'run', '2026-10-12', '2026-10-18', '', 'active', '2026-10-16 15:00:19', '2026-10-16 15:00:19'),
(26, 3, 16, 'swim 2', '2026-10-12', '2026-10-17', '', 'active', '2026-10-16 15:06:24', '2026-10-16 15:06:24');

-- --------------------------------------------------------

--
-- Table structure for table `plan_items`
--

CREATE TABLE `plan_items` (
  `plan_item_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `activity_type_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `planned_date` date NOT NULL,
  `planned_duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `target_distance_km` decimal(8,2) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `completion_status` enum('planned','completed','skipped') NOT NULL DEFAULT 'planned',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `plan_items`
--

INSERT INTO `plan_items` (`plan_item_id`, `tenant_id`, `plan_id`, `activity_type_id`, `user_id`, `planned_date`, `planned_duration_minutes`, `target_distance_km`, `notes`, `completion_status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 10, 18, '2026-10-01', 45, 3.00, 'Synthetic plan item.', 'planned', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 3, 2, 4, 21, '2026-10-02', 30, NULL, 'Synthetic plan item.', 'planned', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 3, 3, 7, 24, '2026-10-03', 30, 5.00, 'Synthetic plan item.', 'completed', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 4, 4, 11, 27, '2026-10-04', 45, 3.00, 'Synthetic plan item.', 'planned', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 4, 5, 5, 30, '2026-10-05', 30, NULL, 'Synthetic plan item.', 'planned', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 4, 6, 8, 33, '2026-09-30', 30, 5.00, 'Synthetic plan item.', 'completed', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 5, 7, 12, 36, '2026-10-01', 45, 3.00, 'Synthetic plan item.', 'planned', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 5, 8, 6, 39, '2026-10-02', 30, NULL, 'Synthetic plan item.', 'planned', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, 3, 16, 10, 16, '2026-10-04', 30, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(17, 3, 16, 1, 16, '2026-10-05', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(18, 3, 16, 7, 16, '2026-10-06', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(19, 3, 16, 1, 16, '2026-10-07', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(20, 3, 16, 4, 16, '2026-10-08', 50, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(21, 3, 16, 13, 16, '2026-10-09', 90, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(22, 3, 16, 7, 16, '2026-10-10', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(23, 3, 16, 10, 16, '2026-10-11', 30, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(24, 3, 16, 1, 16, '2026-10-12', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(25, 3, 16, 7, 16, '2026-10-13', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(26, 3, 16, 1, 16, '2026-10-14', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(27, 3, 16, 4, 16, '2026-10-15', 50, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(28, 3, 16, 13, 16, '2026-10-16', 90, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(29, 3, 16, 7, 16, '2026-10-17', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(30, 3, 16, 10, 16, '2026-10-18', 30, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(31, 3, 16, 1, 16, '2026-10-19', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(32, 3, 16, 7, 16, '2026-10-20', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(33, 3, 16, 1, 16, '2026-10-21', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(34, 3, 16, 4, 16, '2026-10-22', 50, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(35, 3, 16, 13, 16, '2026-10-23', 90, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(36, 3, 16, 7, 16, '2026-10-24', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(37, 3, 16, 10, 16, '2026-10-25', 30, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(38, 3, 16, 1, 16, '2026-10-26', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(39, 3, 16, 7, 16, '2026-10-27', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(40, 3, 16, 1, 16, '2026-10-28', 45, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(41, 3, 16, 4, 16, '2026-10-29', 50, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(42, 3, 16, 13, 16, '2026-10-30', 90, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(43, 3, 16, 7, 16, '2026-10-31', 60, NULL, 'Scheduled session', 'planned', '2026-09-03 00:00:00', '2026-10-03 21:24:15'),
(44, 3, 17, 1, 1, '2026-10-15', 40, NULL, 'start routines', 'planned', '2026-10-14 17:20:33', '2026-10-14 17:20:33');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `rate_limit_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `subject_type` enum('ip','username','user_id','session') NOT NULL,
  `subject_hash` char(64) NOT NULL,
  `window_start` datetime NOT NULL,
  `attempt_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recovery_codes`
--

CREATE TABLE `recovery_codes` (
  `recovery_code_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `reporter_user_id` bigint(20) UNSIGNED NOT NULL,
  `target_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_type` varchar(40) NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(500) NOT NULL,
  `report_status` enum('open','reviewed','resolved','dismissed') NOT NULL DEFAULT 'open',
  `moderator_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `moderator_notes` varchar(1000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `tenant_id`, `reporter_user_id`, `target_user_id`, `target_type`, `target_id`, `reason`, `report_status`, `moderator_user_id`, `moderator_notes`, `created_at`, `resolved_at`) VALUES
(1, 3, 10, NULL, 'activity_log', 17, 'Synthetic moderation test report.', 'reviewed', NULL, NULL, '2026-09-30 17:13:52', NULL),
(2, 3, 11, NULL, 'activity_log', 17, 'Synthetic moderation test report.', 'reviewed', NULL, NULL, '2026-09-30 17:13:52', NULL),
(3, 4, 12, NULL, 'activity_log', 51, 'Synthetic moderation test report.', 'reviewed', NULL, NULL, '2026-09-30 17:13:52', NULL),
(4, 4, 13, NULL, 'activity_log', 34, 'Synthetic moderation test report.', 'open', NULL, NULL, '2026-09-30 17:13:52', NULL),
(5, 4, 13, NULL, 'activity_log', 51, 'Synthetic moderation test report.', 'reviewed', NULL, NULL, '2026-09-30 17:13:52', NULL),
(6, 4, 15, NULL, 'activity_log', 34, 'Synthetic moderation test report.', 'open', NULL, NULL, '2026-09-30 17:13:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` smallint(5) UNSIGNED NOT NULL,
  `role_key` varchar(40) NOT NULL,
  `role_name` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_key`, `role_name`, `description`) VALUES
(1, 'super_administrator', 'Super administrator', 'Full control within an assigned tenant'),
(2, 'administrator', 'Administrator', 'Manages tenant content and selected users'),
(3, 'moderator', 'Moderator', 'Reviews reports and permitted content'),
(4, 'user', 'User', 'Records and manages personal physical activities'),
(5, 'viewer', 'Viewer', 'Views permitted published content without editing records');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` smallint(5) UNSIGNED NOT NULL,
  `permission_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_permission_id`, `role_id`, `permission_id`) VALUES
(42, 1, 1),
(41, 1, 2),
(34, 1, 3),
(31, 1, 4),
(33, 1, 5),
(32, 1, 6),
(35, 1, 7),
(36, 1, 8),
(39, 1, 9),
(38, 1, 10),
(37, 1, 11),
(40, 1, 12),
(25, 2, 1),
(24, 2, 2),
(19, 2, 3),
(16, 2, 4),
(18, 2, 5),
(17, 2, 6),
(20, 2, 7),
(21, 2, 8),
(23, 2, 9),
(22, 2, 10),
(15, 3, 1),
(14, 3, 2),
(12, 3, 3),
(9, 3, 4),
(11, 3, 5),
(10, 3, 6),
(13, 3, 9),
(7, 4, 1),
(6, 4, 2),
(5, 4, 3),
(2, 4, 4),
(4, 4, 5),
(3, 4, 6),
(1, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `security_events`
--

CREATE TABLE `security_events` (
  `security_event_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `event_type` varchar(60) NOT NULL,
  `outcome` enum('success','failure','denied') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `details` varchar(1000) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `security_events`
--

INSERT INTO `security_events` (`security_event_id`, `tenant_id`, `user_id`, `event_type`, `outcome`, `ip_address`, `user_agent`, `details`, `created_at`) VALUES
(1, 3, 16, 'login', 'success', '192.168.1.24', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-10-02 00:00:00'),
(2, 3, 16, '2fa_enabled', 'success', '192.168.1.113', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-19 00:00:00'),
(3, 3, 16, 'login', 'success', '192.168.1.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-06 00:00:00'),
(4, 3, 16, 'password_changed', 'success', '192.168.1.175', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-05 00:00:00'),
(5, 3, 16, 'profile_updated', 'success', '192.168.1.199', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-12 00:00:00'),
(6, 3, 16, 'login', 'success', '192.168.1.234', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-05 00:00:00'),
(7, 3, 16, 'login', 'success', '192.168.1.100', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-10 00:00:00'),
(8, 3, 16, '2fa_enabled', 'success', '192.168.1.116', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-04 00:00:00'),
(9, 3, 16, 'profile_updated', 'success', '192.168.1.224', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-12 00:00:00'),
(10, 3, 16, '2fa_enabled', 'success', '192.168.1.53', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-20 00:00:00'),
(11, 3, 16, 'profile_updated', 'success', '192.168.1.163', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-21 00:00:00'),
(12, 3, 16, 'login', 'success', '192.168.1.195', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-28 00:00:00'),
(13, 3, 16, 'profile_updated', 'success', '192.168.1.213', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-30 00:00:00'),
(14, 3, 16, 'login', 'success', '192.168.1.62', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-09-08 00:00:00'),
(15, 3, 16, 'password_changed', 'success', '192.168.1.231', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', NULL, '2026-10-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sport_logs`
--

CREATE TABLE `sport_logs` (
  `sport_log_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `activity_log_id` bigint(20) UNSIGNED NOT NULL,
  `sport_name` varchar(60) NOT NULL,
  `session_type` enum('match','training','casual_game','other') NOT NULL DEFAULT 'other',
  `team_or_group` varchar(100) DEFAULT NULL,
  `position_or_role` varchar(80) DEFAULT NULL,
  `result` varchar(80) DEFAULT NULL,
  `notes` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `sport_logs`
--

INSERT INTO `sport_logs` (`sport_log_id`, `tenant_id`, `activity_log_id`, `sport_name`, `session_type`, `team_or_group`, `position_or_role`, `result`, `notes`) VALUES
(1, 3, 1, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(2, 3, 81, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(3, 3, 5, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(4, 3, 9, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(5, 3, 13, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(6, 3, 17, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(7, 3, 21, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(8, 3, 25, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(9, 4, 37, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(10, 4, 29, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(11, 4, 33, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(12, 4, 41, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(13, 4, 45, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(14, 4, 49, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(15, 4, 53, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(16, 4, 57, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(17, 5, 61, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(18, 5, 65, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(19, 5, 69, 'Football', 'match', 'Synthetic local sports group', 'defence', 'Synthetic result', 'Synthetic football record.'),
(20, 5, 73, 'Football', 'training', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.'),
(21, 5, 77, 'Football', 'casual_game', 'Synthetic local sports group', 'defence', NULL, 'Synthetic football record.');

-- --------------------------------------------------------

--
-- Table structure for table `swimming_logs`
--

CREATE TABLE `swimming_logs` (
  `swimming_log_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `activity_log_id` bigint(20) UNSIGNED NOT NULL,
  `pool_length_metres` decimal(7,2) NOT NULL,
  `pool_lengths` int(10) UNSIGNED NOT NULL,
  `distance_metres` decimal(10,2) NOT NULL,
  `stroke` enum('freestyle','breaststroke','backstroke','butterfly','mixed','other') NOT NULL,
  `duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `notes` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `swimming_logs`
--

INSERT INTO `swimming_logs` (`swimming_log_id`, `tenant_id`, `activity_log_id`, `pool_length_metres`, `pool_lengths`, `distance_metres`, `stroke`, `duration_minutes`, `notes`) VALUES
(1, 3, 3, 20.00, 13, 260.00, 'mixed', 29, 'Synthetic swimming record.'),
(2, 3, 83, 50.00, 13, 650.00, 'mixed', 33, 'Synthetic swimming record.'),
(3, 3, 7, 25.00, 17, 425.00, 'backstroke', 35, 'Synthetic swimming record.'),
(4, 3, 11, 50.00, 21, 1050.00, 'breaststroke', 41, 'Synthetic swimming record.'),
(5, 3, 15, 20.00, 25, 500.00, 'freestyle', 43, 'Synthetic swimming record.'),
(6, 3, 19, 25.00, 29, 725.00, 'other', 45, 'Synthetic swimming record.'),
(7, 3, 23, 50.00, 33, 1650.00, 'mixed', 47, 'Synthetic swimming record.'),
(8, 3, 27, 20.00, 37, 740.00, 'backstroke', 49, 'Synthetic swimming record.'),
(9, 4, 39, 20.00, 49, 980.00, 'other', 27, 'Synthetic swimming record.'),
(10, 4, 31, 25.00, 41, 1025.00, 'breaststroke', 31, 'Synthetic swimming record.'),
(11, 4, 35, 50.00, 45, 2250.00, 'freestyle', 37, 'Synthetic swimming record.'),
(12, 4, 43, 25.00, 13, 325.00, 'mixed', 51, 'Synthetic swimming record.'),
(13, 4, 47, 50.00, 17, 850.00, 'backstroke', 53, 'Synthetic swimming record.'),
(14, 4, 51, 20.00, 21, 420.00, 'breaststroke', 55, 'Synthetic swimming record.'),
(15, 4, 55, 25.00, 25, 625.00, 'freestyle', 57, 'Synthetic swimming record.'),
(16, 4, 59, 50.00, 29, 1450.00, 'other', 59, 'Synthetic swimming record.'),
(17, 5, 63, 20.00, 33, 660.00, 'mixed', 33, 'Synthetic swimming record.'),
(18, 5, 67, 25.00, 37, 925.00, 'backstroke', 39, 'Synthetic swimming record.'),
(19, 5, 71, 50.00, 41, 2050.00, 'breaststroke', 61, 'Synthetic swimming record.'),
(20, 5, 75, 20.00, 45, 900.00, 'freestyle', 63, 'Synthetic swimming record.'),
(21, 5, 79, 25.00, 49, 1225.00, 'other', 65, 'Synthetic swimming record.');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_name` varchar(100) NOT NULL,
  `tenant_slug` varchar(100) NOT NULL,
  `tenant_status` enum('active','suspended','archived') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `tenant_slug`, `tenant_status`, `created_at`, `updated_at`) VALUES
(1, 'North Yorkshire Fitness', 'north-yorkshire-fitness', 'active', '2026-09-30 17:11:09', '2026-09-30 17:11:09'),
(2, 'West Yorkshire Fitness', 'west-yorkshire-fitness', 'active', '2026-09-30 17:11:09', '2026-09-30 17:11:09'),
(3, 'Greater Manchester Active', 'greater-manchester-active', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 'Liverpool City Region Fitness', 'liverpool-city-region-fitness', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 'North West Community Sport', 'north-west-community-sport', 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_memberships`
--

CREATE TABLE `tenant_memberships` (
  `membership_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` smallint(5) UNSIGNED NOT NULL,
  `membership_status` enum('active','suspended','removed') NOT NULL DEFAULT 'active',
  `joined_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `tenant_memberships`
--

INSERT INTO `tenant_memberships` (`membership_id`, `tenant_id`, `user_id`, `role_id`, `membership_status`, `joined_at`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 3, 5, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 3, 10, 3, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 3, 11, 3, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 3, 1, 1, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 3, 16, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 3, 17, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 3, 18, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(9, 3, 19, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(10, 3, 20, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(11, 3, 21, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(12, 3, 22, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(13, 3, 23, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(14, 3, 24, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(15, 3, 25, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, 3, 41, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(17, 3, 42, 5, 'suspended', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-10-18 21:15:43'),
(18, 3, 43, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(19, 3, 44, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(20, 3, 45, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(32, 4, 6, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(33, 4, 7, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(34, 4, 12, 3, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(35, 4, 13, 3, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(36, 4, 2, 1, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(37, 4, 26, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(38, 4, 27, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(39, 4, 28, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(40, 4, 29, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(41, 4, 30, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(42, 4, 31, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(43, 4, 32, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(44, 4, 33, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(45, 4, 34, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(46, 4, 35, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(47, 4, 46, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(48, 4, 47, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(49, 4, 48, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(50, 4, 49, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(51, 4, 50, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(63, 5, 8, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(64, 5, 9, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(65, 5, 14, 3, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(66, 5, 15, 5, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(67, 5, 3, 1, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(68, 5, 36, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(69, 5, 37, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(70, 5, 38, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(71, 5, 39, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(72, 5, 40, 4, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(78, 3, 8, 2, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(79, 4, 15, 3, 'active', '2026-09-30 17:13:52', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(81, 1, 51, 4, 'active', '2026-10-02 17:14:29', '2026-10-02 17:14:29', '2026-10-02 17:14:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `display_name` varchar(80) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `bio` varchar(500) DEFAULT NULL,
  `date_of_birth_encrypted` text DEFAULT NULL,
  `account_status` enum('active','suspended','disabled') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `display_name`, `avatar_path`, `bio`, `date_of_birth_encrypted`, `account_status`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'fit_super_001', 'super001@example.test', '$2y$10$iW7bemQW5HxcFSwHzA3D1uOSFa50z65O4AKDaGosRRu38Cw7XWGvG', 'Alex Morgan', NULL, 'Synthetic demonstration account.', NULL, 'active', '2026-10-19 14:57:32', '2026-09-30 17:13:52', '2026-10-19 14:57:32'),
(2, 'fit_super_002', 'super002@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Priya Shah', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(3, 'fit_super_003', 'super003@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Daniel Hughes', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(4, 'fit_admin_001', 'admin001@example.test', '$2y$10$iW7bemQW5HxcFSwHzA3D1uOSFa50z65O4AKDaGosRRu38Cw7XWGvG', 'Emily Carter', NULL, 'Synthetic demonstration account.', NULL, 'active', '2026-10-18 21:15:09', '2026-09-30 17:13:52', '2026-10-18 21:15:09'),
(5, 'fit_admin_002', 'admin002@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Jordan Williams', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(6, 'fit_admin_003', 'admin003@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Sophie Evans', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(7, 'fit_admin_004', 'admin004@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Owen Taylor', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(8, 'fit_admin_005', 'admin005@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Amelia Robinson', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(9, 'fit_admin_006', 'admin006@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Thomas Davies', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(10, 'fit_mod_001', 'moderator001@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Grace Wilson', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(11, 'fit_mod_002', 'moderator002@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Noah Thompson', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(12, 'fit_mod_003', 'moderator003@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Mia Roberts', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(13, 'fit_mod_004', 'moderator004@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Oliver Johnson', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(14, 'fit_mod_005', 'moderator005@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Isla Brown', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(15, 'fit_mod_006', 'moderator006@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'George Walker', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(16, 'fit_user_001', 'user001@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Lily Wright', 'uploads/avatars/user_16_1792333702.jpg', 'Synthetic demonstration account.', NULL, 'active', '2026-10-19 16:21:46', '2026-09-30 17:13:52', '2026-10-19 16:21:46'),
(17, 'fit_user_002', 'user002@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Arthur Wilson', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(18, 'fit_user_003', 'user003@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Ella Thomas', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(19, 'fit_user_004', 'user004@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Harry Green', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(20, 'fit_user_005', 'user005@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Freya Hall', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(21, 'fit_user_006', 'user006@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Jack Clarke', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(22, 'fit_user_007', 'user007@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Evie Lewis', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(23, 'fit_user_008', 'user008@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Charlie Harris', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(24, 'fit_user_009', 'user009@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Poppy Baker', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(25, 'fit_user_010', 'user010@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Oscar King', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(26, 'fit_user_011', 'user011@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Isabelle Adams', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(27, 'fit_user_012', 'user012@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Leo Allen', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(28, 'fit_user_013', 'user013@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Daisy Scott', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(29, 'fit_user_014', 'user014@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Arthur Young', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(30, 'fit_user_015', 'user015@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Sienna Mitchell', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(31, 'fit_user_016', 'user016@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Henry Collins', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(32, 'fit_user_017', 'user017@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Ruby Stewart', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(33, 'fit_user_018', 'user018@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Finley Morris', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(34, 'fit_user_019', 'user019@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Ivy Rogers', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(35, 'fit_user_020', 'user020@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'William Reed', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(36, 'fit_user_021', 'user021@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Maya Cook', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(37, 'fit_user_022', 'user022@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Freddie Morgan', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(38, 'fit_user_023', 'user023@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Esme Bell', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(39, 'fit_user_024', 'user024@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Arthur Murphy', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(40, 'fit_user_025', 'user025@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Florence Bailey', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(41, 'fit_viewer_001', 'viewer001@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Lucy Miller', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(42, 'fit_viewer_002', 'viewer002@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Theodore Cox', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(43, 'fit_viewer_003', 'viewer003@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Matilda Richards', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(44, 'fit_viewer_004', 'viewer004@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Archie Bailey', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(45, 'fit_viewer_005', 'viewer005@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Alice Cooper', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(46, 'fit_viewer_006', 'viewer006@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Edward Phillips', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(47, 'fit_viewer_007', 'viewer007@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Ada Bennett', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(48, 'fit_viewer_008', 'viewer008@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Arthur Foster', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(49, 'fit_viewer_009', 'viewer009@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'Chloe Gray', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(50, 'fit_viewer_010', 'viewer010@example.test', '$2y$12$p5UTi3DR2ZDO7wdP390aUOxQNnkhAQknEOfTSJhPJqGjLoT2JJ4BO', 'James Ellis', NULL, 'Synthetic demonstration account.', NULL, 'active', NULL, '2026-09-30 17:13:52', '2026-09-30 17:43:12'),
(51, 'tim', 'timski.sansom@gmail.com', '$2y$12$61t2l69/ZYUEfYgYzMqPs.ncmU3Y6fOmq/uSpDrhKFMb6uS.kSO1m', 'tim', NULL, NULL, NULL, 'active', NULL, '2026-10-02 17:14:29', '2026-10-02 17:14:29');

-- --------------------------------------------------------

--
-- Table structure for table `user_measurements`
--

CREATE TABLE `user_measurements` (
  `measurement_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `weight_kg` decimal(5,1) DEFAULT NULL,
  `height_cm` decimal(5,1) DEFAULT NULL,
  `bmi` decimal(4,1) DEFAULT NULL,
  `chest_cm` decimal(5,1) DEFAULT NULL,
  `waist_cm` decimal(5,1) DEFAULT NULL,
  `hips_cm` decimal(5,1) DEFAULT NULL,
  `left_arm_cm` decimal(5,1) DEFAULT NULL,
  `right_arm_cm` decimal(5,1) DEFAULT NULL,
  `left_leg_cm` decimal(5,1) DEFAULT NULL,
  `right_leg_cm` decimal(5,1) DEFAULT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_measurements`
--

INSERT INTO `user_measurements` (`measurement_id`, `user_id`, `weight_kg`, `height_cm`, `bmi`, `chest_cm`, `waist_cm`, `hips_cm`, `left_arm_cm`, `right_arm_cm`, `left_leg_cm`, `right_leg_cm`, `recorded_at`) VALUES
(1, 16, 70.6, 175.0, 22.9, 96.9, 83.2, 102.2, 33.7, 33.2, 56.8, 55.3, '2026-09-19 00:00:00'),
(2, 16, 70.8, 175.0, 23.0, 96.7, 82.0, 100.6, 32.2, 33.8, 56.0, 57.5, '2026-09-05 00:00:00'),
(3, 16, 70.0, 175.0, 23.0, 97.3, 83.0, 99.9, 33.3, 32.0, 55.4, 56.9, '2026-08-22 00:00:00'),
(4, 16, 71.2, 175.0, 23.1, 98.6, 81.8, 98.3, 33.9, 33.3, 56.3, 55.3, '2026-08-08 00:00:00'),
(5, 16, 70.4, 175.0, 23.2, 99.3, 82.5, 100.8, 33.9, 32.0, 55.6, 58.0, '2026-07-25 00:00:00'),
(6, 16, 70.9, 175.0, 23.2, 99.6, 81.8, 100.6, 32.4, 32.9, 57.0, 55.0, '2026-07-11 00:00:00'),
(7, 16, 70.4, 175.0, 23.3, 95.6, 81.9, 98.4, 33.9, 33.0, 56.8, 56.6, '2026-06-27 00:00:00'),
(8, 16, 72.2, 175.0, 23.4, 98.4, 83.4, 99.0, 32.9, 33.4, 55.4, 56.8, '2026-06-13 00:00:00'),
(9, 16, 71.8, 175.0, 23.4, 99.7, 80.4, 101.1, 33.7, 32.8, 56.0, 56.6, '2026-05-30 00:00:00'),
(10, 16, 72.3, 175.0, 23.5, 99.3, 80.8, 100.2, 33.2, 33.2, 55.5, 55.4, '2026-05-16 00:00:00'),
(11, 16, 71.4, 175.0, 23.5, 96.7, 81.0, 99.3, 33.1, 33.8, 57.8, 57.9, '2026-05-02 00:00:00'),
(12, 16, 73.3, 175.0, 23.6, 95.4, 81.7, 102.6, 32.6, 33.6, 57.9, 56.3, '2026-04-18 00:00:00'),
(13, 16, 70.8, NULL, 23.1, 96.9, 83.2, 98.0, 33.7, 33.2, 56.8, 55.3, '2026-10-05 08:00:00'),
(14, 16, 70.6, NULL, 23.1, 96.9, 83.2, 98.0, 33.7, 33.2, 56.8, 55.3, '2026-10-12 08:00:00'),
(15, 16, 70.7, NULL, 23.1, 96.9, 83.2, 98.0, 33.7, 33.2, 56.8, 55.3, '2026-10-19 08:00:00'),
(16, 16, 71.2, NULL, 23.3, 97.5, 83.8, NULL, 33.9, 33.4, 57.0, 55.5, '2026-09-20 00:00:00'),
(17, 16, 71.0, NULL, 23.2, 97.3, 83.6, NULL, 33.8, 33.3, 56.9, 55.4, '2026-09-27 00:00:00'),
(18, 16, 70.9, NULL, 23.2, 97.1, 83.5, NULL, 33.8, 33.3, 56.8, 55.3, '2026-10-04 00:00:00'),
(19, 16, 70.8, NULL, 23.1, 97.0, 83.3, NULL, 33.7, 33.2, 56.8, 55.3, '2026-10-11 00:00:00'),
(20, 16, 70.7, NULL, 23.1, 96.9, 83.2, NULL, 33.7, 33.2, 56.8, 55.3, '2026-10-18 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `preference_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `distance_unit` enum('km','miles') NOT NULL DEFAULT 'km',
  `weight_unit` enum('kg','stone_pounds') NOT NULL DEFAULT 'kg',
  `temperature_unit` enum('celsius','fahrenheit') NOT NULL DEFAULT 'celsius',
  `theme_preference` enum('system','light','dark') NOT NULL DEFAULT 'system',
  `default_activity_visibility` enum('private','tenant','public') NOT NULL DEFAULT 'private',
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`preference_id`, `tenant_id`, `user_id`, `distance_unit`, `weight_unit`, `temperature_unit`, `theme_preference`, `default_activity_visibility`, `notifications_enabled`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 'km', 'stone_pounds', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, 3, 5, 'miles', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, 3, 10, 'miles', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, 3, 11, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, 3, 1, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, 3, 16, 'km', 'stone_pounds', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, 3, 17, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, 3, 18, 'km', 'kg', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(9, 3, 19, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(10, 3, 20, 'miles', 'stone_pounds', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(11, 3, 21, 'km', 'kg', 'celsius', 'dark', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(12, 3, 22, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(13, 3, 23, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(14, 3, 24, 'km', 'stone_pounds', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(15, 3, 25, 'miles', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, 3, 41, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(17, 3, 42, 'km', 'kg', 'fahrenheit', 'dark', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(18, 3, 43, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(19, 3, 44, 'km', 'stone_pounds', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(20, 3, 45, 'miles', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(21, 4, 6, 'km', 'kg', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(22, 4, 7, 'km', 'kg', 'celsius', 'light', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(23, 4, 12, 'km', 'stone_pounds', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(24, 4, 13, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(25, 4, 2, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(26, 4, 26, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(27, 4, 27, 'km', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(28, 4, 28, 'km', 'stone_pounds', 'celsius', 'light', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(29, 4, 29, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(30, 4, 30, 'miles', 'kg', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(31, 4, 31, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(32, 4, 32, 'km', 'stone_pounds', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(33, 4, 33, 'km', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(34, 4, 34, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(35, 4, 35, 'miles', 'kg', 'celsius', 'system', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(36, 4, 46, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(37, 4, 47, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(38, 4, 48, 'km', 'stone_pounds', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(39, 4, 49, 'km', 'kg', 'celsius', 'light', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(40, 4, 50, 'miles', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(41, 5, 8, 'km', 'stone_pounds', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(42, 5, 9, 'km', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(43, 5, 14, 'km', 'kg', 'celsius', 'system', 'tenant', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(44, 5, 15, 'miles', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(45, 5, 3, 'km', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(46, 5, 36, 'km', 'stone_pounds', 'fahrenheit', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(47, 5, 37, 'km', 'kg', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(48, 5, 38, 'km', 'kg', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(49, 5, 39, 'km', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(50, 5, 40, 'miles', 'stone_pounds', 'celsius', 'light', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(51, 3, 8, 'km', 'stone_pounds', 'celsius', 'system', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(52, 4, 15, 'miles', 'kg', 'celsius', 'dark', 'private', 1, '2026-09-30 17:13:52', '2026-09-30 17:13:52');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `age_band` enum('16-17','18-24','25-34','35-44','45-54','55-64','65-80') NOT NULL,
  `gender_identity` enum('female','male','non_binary','prefer_not_to_say') NOT NULL DEFAULT 'prefer_not_to_say',
  `locality` varchar(80) NOT NULL,
  `bio` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `age_band`, `gender_identity`, `locality`, `bio`, `created_at`, `updated_at`) VALUES
(1, '18-24', 'male', 'Salford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(2, '25-34', 'non_binary', 'Trafford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(3, '35-44', 'prefer_not_to_say', 'Stockport', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(4, '45-54', 'female', 'Oldham', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(5, '55-64', 'male', 'Bolton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(6, '65-80', 'non_binary', 'Liverpool', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(7, '16-17', 'prefer_not_to_say', 'Wirral', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(8, '18-24', 'female', 'Sefton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(9, '25-34', 'male', 'St Helens', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(10, '35-44', 'non_binary', 'Warrington', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(11, '45-54', 'prefer_not_to_say', 'Preston', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(12, '55-64', 'female', 'Manchester', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(13, '65-80', 'male', 'Salford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(14, '16-17', 'non_binary', 'Trafford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(15, '18-24', 'prefer_not_to_say', 'Stockport', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(16, '25-34', 'female', 'Oldham', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(17, '35-44', 'male', 'Bolton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(18, '45-54', 'non_binary', 'Liverpool', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(19, '55-64', 'prefer_not_to_say', 'Wirral', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(20, '65-80', 'female', 'Sefton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(21, '16-17', 'male', 'St Helens', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(22, '18-24', 'non_binary', 'Warrington', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(23, '25-34', 'prefer_not_to_say', 'Preston', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(24, '35-44', 'female', 'Manchester', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(25, '45-54', 'male', 'Salford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(26, '55-64', 'non_binary', 'Trafford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(27, '65-80', 'prefer_not_to_say', 'Stockport', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(28, '16-17', 'female', 'Oldham', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(29, '18-24', 'male', 'Bolton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(30, '25-34', 'non_binary', 'Liverpool', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(31, '35-44', 'prefer_not_to_say', 'Wirral', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(32, '45-54', 'female', 'Sefton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(33, '55-64', 'male', 'St Helens', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(34, '65-80', 'non_binary', 'Warrington', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(35, '16-17', 'prefer_not_to_say', 'Preston', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(36, '18-24', 'female', 'Manchester', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(37, '25-34', 'male', 'Salford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(38, '35-44', 'non_binary', 'Trafford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(39, '45-54', 'prefer_not_to_say', 'Stockport', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(40, '55-64', 'female', 'Oldham', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(41, '65-80', 'male', 'Bolton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(42, '16-17', 'non_binary', 'Liverpool', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(43, '18-24', 'prefer_not_to_say', 'Wirral', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(44, '25-34', 'female', 'Sefton', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(45, '35-44', 'male', 'St Helens', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(46, '45-54', 'non_binary', 'Warrington', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(47, '55-64', 'prefer_not_to_say', 'Preston', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(48, '65-80', 'female', 'Manchester', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(49, '16-17', 'male', 'Salford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(50, '18-24', 'non_binary', 'Trafford', 'Synthetic demonstration profile.', '2026-09-30 17:13:52', '2026-09-30 17:13:52'),
(51, '25-34', 'prefer_not_to_say', '', '', '2026-10-02 17:15:34', '2026-10-02 17:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_totp`
--

CREATE TABLE `user_totp` (
  `totp_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `secret_encrypted` mediumtext NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `setup_started_at` datetime DEFAULT NULL,
  `enabled_at` datetime DEFAULT NULL,
  `last_used_time_step` bigint(20) DEFAULT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `user_totp`
--

INSERT INTO `user_totp` (`totp_id`, `tenant_id`, `user_id`, `secret_encrypted`, `is_enabled`, `setup_started_at`, `enabled_at`, `last_used_time_step`, `failed_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 3, 16, 'FLDAXy59kQpDKAPxSWcbKTo6UlR2aS9nUitrRCt4UC9HK2tXY1RZdkRlYmRPWHNabVFYaDEzajlXVHVEM3ZoQ2gwbmd3Wll1SzBQU0F6SlAvYQ==', 0, '2026-10-13 08:37:50', NULL, NULL, 0, NULL, '2026-10-13 08:37:50', '2026-10-13 08:37:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_locations`
--
ALTER TABLE `activity_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `idx_loc_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `fk_loc_user` (`user_id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`activity_log_id`),
  ADD KEY `idx_al_tenant_user_date` (`tenant_id`,`user_id`,`activity_date`),
  ADD KEY `idx_al_type` (`activity_type_id`),
  ADD KEY `idx_al_location` (`location_id`),
  ADD KEY `fk_al_user` (`user_id`);

--
-- Indexes for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD PRIMARY KEY (`activity_type_id`),
  ADD UNIQUE KEY `uq_at_tenant_slug` (`tenant_id`,`slug`),
  ADD KEY `idx_at_tenant` (`tenant_id`),
  ADD KEY `idx_at_creator` (`created_by_user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`audit_log_id`),
  ADD KEY `idx_al_audit_tenant` (`tenant_id`),
  ADD KEY `idx_al_audit_user` (`user_id`);

--
-- Indexes for table `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`challenge_id`),
  ADD KEY `idx_challenges_tenant_user` (`tenant_id`,`user_id`);

--
-- Indexes for table `daily_nutrition`
--
ALTER TABLE `daily_nutrition`
  ADD PRIMARY KEY (`nutrition_id`),
  ADD UNIQUE KEY `uq_nutrition_user_date` (`tenant_id`,`user_id`,`log_date`);

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`exercise_id`),
  ADD UNIQUE KEY `uq_ex_tenant_name` (`tenant_id`,`name`),
  ADD KEY `idx_ex_tenant` (`tenant_id`),
  ADD KEY `idx_ex_creator` (`created_by_user_id`);

--
-- Indexes for table `gym_sessions`
--
ALTER TABLE `gym_sessions`
  ADD PRIMARY KEY (`gym_session_id`),
  ADD UNIQUE KEY `uq_gs_activity_log` (`activity_log_id`),
  ADD KEY `idx_gs_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `fk_gs_user` (`user_id`);

--
-- Indexes for table `gym_session_exercises`
--
ALTER TABLE `gym_session_exercises`
  ADD PRIMARY KEY (`session_exercise_id`),
  ADD UNIQUE KEY `uq_gse_order` (`gym_session_id`,`exercise_order`),
  ADD KEY `idx_gse_session` (`gym_session_id`),
  ADD KEY `idx_gse_exercise` (`exercise_id`),
  ADD KEY `fk_gse_tenant` (`tenant_id`);

--
-- Indexes for table `gym_sets`
--
ALTER TABLE `gym_sets`
  ADD PRIMARY KEY (`set_id`),
  ADD UNIQUE KEY `uq_set_number` (`session_exercise_id`,`set_number`),
  ADD KEY `idx_set_exercise` (`session_exercise_id`),
  ADD KEY `fk_set_tenant` (`tenant_id`);

--
-- Indexes for table `kudos`
--
ALTER TABLE `kudos`
  ADD PRIMARY KEY (`kudos_id`),
  ADD UNIQUE KEY `uq_kudos_user_activity` (`user_id`,`activity_log_id`),
  ADD KEY `idx_kudos_activity` (`activity_log_id`);

--
-- Indexes for table `meal_plan_items`
--
ALTER TABLE `meal_plan_items`
  ADD PRIMARY KEY (`meal_plan_item_id`),
  ADD UNIQUE KEY `uq_meal_slot` (`tenant_id`,`user_id`,`meal_date`,`meal_slot`),
  ADD KEY `idx_meal_plan_date` (`tenant_id`,`user_id`,`meal_date`);

--
-- Indexes for table `nutrition_logs`
--
ALTER TABLE `nutrition_logs`
  ADD PRIMARY KEY (`nutrition_log_id`),
  ADD KEY `idx_nutrition_tenant_user_date` (`tenant_id`,`user_id`,`log_date`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `uq_prt_token_hash` (`token_hash`),
  ADD KEY `idx_prt_user` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `uq_permissions_key` (`permission_key`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `idx_pl_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `fk_pl_user` (`user_id`);

--
-- Indexes for table `plan_items`
--
ALTER TABLE `plan_items`
  ADD PRIMARY KEY (`plan_item_id`),
  ADD KEY `idx_pi_plan` (`plan_id`),
  ADD KEY `idx_pi_type` (`activity_type_id`),
  ADD KEY `fk_pi_user` (`user_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`rate_limit_id`),
  ADD UNIQUE KEY `uq_rl_bucket` (`tenant_id`,`action`,`subject_type`,`subject_hash`),
  ADD KEY `idx_rl_blocked` (`blocked_until`);

--
-- Indexes for table `recovery_codes`
--
ALTER TABLE `recovery_codes`
  ADD PRIMARY KEY (`recovery_code_id`),
  ADD KEY `idx_rc_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `fk_rc_user` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `idx_rep_tenant` (`tenant_id`),
  ADD KEY `idx_rep_reporter` (`reporter_user_id`),
  ADD KEY `idx_rep_target` (`target_user_id`),
  ADD KEY `idx_rep_moderator` (`moderator_user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `uq_roles_key` (`role_key`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_permission_id`),
  ADD UNIQUE KEY `uq_role_perm` (`role_id`,`permission_id`),
  ADD KEY `fk_rp_perm` (`permission_id`);

--
-- Indexes for table `security_events`
--
ALTER TABLE `security_events`
  ADD PRIMARY KEY (`security_event_id`),
  ADD KEY `idx_se_tenant` (`tenant_id`);

--
-- Indexes for table `sport_logs`
--
ALTER TABLE `sport_logs`
  ADD PRIMARY KEY (`sport_log_id`),
  ADD UNIQUE KEY `uq_sp_activity_log` (`activity_log_id`),
  ADD KEY `fk_sp_tenant` (`tenant_id`);

--
-- Indexes for table `swimming_logs`
--
ALTER TABLE `swimming_logs`
  ADD PRIMARY KEY (`swimming_log_id`),
  ADD UNIQUE KEY `uq_sw_activity_log` (`activity_log_id`),
  ADD KEY `fk_sw_tenant` (`tenant_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`tenant_id`),
  ADD UNIQUE KEY `uq_tenants_slug` (`tenant_slug`);

--
-- Indexes for table `tenant_memberships`
--
ALTER TABLE `tenant_memberships`
  ADD PRIMARY KEY (`membership_id`),
  ADD UNIQUE KEY `uq_membership_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `idx_tm_user` (`user_id`),
  ADD KEY `idx_tm_role` (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `user_measurements`
--
ALTER TABLE `user_measurements`
  ADD PRIMARY KEY (`measurement_id`),
  ADD KEY `idx_measurements_user` (`user_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `uq_pref_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `fk_pref_user` (`user_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_totp`
--
ALTER TABLE `user_totp`
  ADD PRIMARY KEY (`totp_id`),
  ADD UNIQUE KEY `uq_totp_tenant_user` (`tenant_id`,`user_id`),
  ADD KEY `fk_totp_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_locations`
--
ALTER TABLE `activity_locations`
  MODIFY `location_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `activity_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=605;

--
-- AUTO_INCREMENT for table `activity_types`
--
ALTER TABLE `activity_types`
  MODIFY `activity_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `audit_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `challenges`
--
ALTER TABLE `challenges`
  MODIFY `challenge_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `daily_nutrition`
--
ALTER TABLE `daily_nutrition`
  MODIFY `nutrition_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `exercise_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `gym_sessions`
--
ALTER TABLE `gym_sessions`
  MODIFY `gym_session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gym_session_exercises`
--
ALTER TABLE `gym_session_exercises`
  MODIFY `session_exercise_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gym_sets`
--
ALTER TABLE `gym_sets`
  MODIFY `set_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kudos`
--
ALTER TABLE `kudos`
  MODIFY `kudos_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `meal_plan_items`
--
ALTER TABLE `meal_plan_items`
  MODIFY `meal_plan_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `nutrition_logs`
--
ALTER TABLE `nutrition_logs`
  MODIFY `nutrition_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=366;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `reset_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `plan_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `plan_items`
--
ALTER TABLE `plan_items`
  MODIFY `plan_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `rate_limit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recovery_codes`
--
ALTER TABLE `recovery_codes`
  MODIFY `recovery_code_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `role_permission_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `security_events`
--
ALTER TABLE `security_events`
  MODIFY `security_event_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sport_logs`
--
ALTER TABLE `sport_logs`
  MODIFY `sport_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `swimming_logs`
--
ALTER TABLE `swimming_logs`
  MODIFY `swimming_log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tenant_memberships`
--
ALTER TABLE `tenant_memberships`
  MODIFY `membership_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `user_measurements`
--
ALTER TABLE `user_measurements`
  MODIFY `measurement_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `preference_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `user_totp`
--
ALTER TABLE `user_totp`
  MODIFY `totp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_locations`
--
ALTER TABLE `activity_locations`
  ADD CONSTRAINT `fk_loc_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_loc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_al_location` FOREIGN KEY (`location_id`) REFERENCES `activity_locations` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_al_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_al_type` FOREIGN KEY (`activity_type_id`) REFERENCES `activity_types` (`activity_type_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `activity_types`
--
ALTER TABLE `activity_types`
  ADD CONSTRAINT `fk_at_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_at_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_al_audit_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_al_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `fk_challenges_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_challenges_user` FOREIGN KEY (`tenant_id`,`user_id`) REFERENCES `tenant_memberships` (`tenant_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `daily_nutrition`
--
ALTER TABLE `daily_nutrition`
  ADD CONSTRAINT `fk_nutrition_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nutrition_user` FOREIGN KEY (`tenant_id`,`user_id`) REFERENCES `tenant_memberships` (`tenant_id`, `user_id`) ON DELETE CASCADE;

--
-- Constraints for table `exercises`
--
ALTER TABLE `exercises`
  ADD CONSTRAINT `fk_ex_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ex_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gym_sessions`
--
ALTER TABLE `gym_sessions`
  ADD CONSTRAINT `fk_gs_al` FOREIGN KEY (`activity_log_id`) REFERENCES `activity_logs` (`activity_log_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gym_session_exercises`
--
ALTER TABLE `gym_session_exercises`
  ADD CONSTRAINT `fk_gse_exercise` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`exercise_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gse_session` FOREIGN KEY (`gym_session_id`) REFERENCES `gym_sessions` (`gym_session_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gse_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gym_sets`
--
ALTER TABLE `gym_sets`
  ADD CONSTRAINT `fk_set_exercise` FOREIGN KEY (`session_exercise_id`) REFERENCES `gym_session_exercises` (`session_exercise_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_set_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kudos`
--
ALTER TABLE `kudos`
  ADD CONSTRAINT `fk_kudos_activity` FOREIGN KEY (`activity_log_id`) REFERENCES `activity_logs` (`activity_log_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kudos_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `meal_plan_items`
--
ALTER TABLE `meal_plan_items`
  ADD CONSTRAINT `fk_meal_plan_membership` FOREIGN KEY (`tenant_id`,`user_id`) REFERENCES `tenant_memberships` (`tenant_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nutrition_logs`
--
ALTER TABLE `nutrition_logs`
  ADD CONSTRAINT `fk_nutrition_logs_membership` FOREIGN KEY (`tenant_id`,`user_id`) REFERENCES `tenant_memberships` (`tenant_id`, `user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nutrition_logs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_prt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plans`
--
ALTER TABLE `plans`
  ADD CONSTRAINT `fk_pl_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `plan_items`
--
ALTER TABLE `plan_items`
  ADD CONSTRAINT `fk_pi_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`plan_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pi_type` FOREIGN KEY (`activity_type_id`) REFERENCES `activity_types` (`activity_type_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pi_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD CONSTRAINT `fk_rl_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recovery_codes`
--
ALTER TABLE `recovery_codes`
  ADD CONSTRAINT `fk_rc_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_rep_moderator` FOREIGN KEY (`moderator_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rep_reporter` FOREIGN KEY (`reporter_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rep_target` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rep_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `security_events`
--
ALTER TABLE `security_events`
  ADD CONSTRAINT `fk_se_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sport_logs`
--
ALTER TABLE `sport_logs`
  ADD CONSTRAINT `fk_sp_al` FOREIGN KEY (`activity_log_id`) REFERENCES `activity_logs` (`activity_log_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `swimming_logs`
--
ALTER TABLE `swimming_logs`
  ADD CONSTRAINT `fk_sw_al` FOREIGN KEY (`activity_log_id`) REFERENCES `activity_logs` (`activity_log_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sw_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenant_memberships`
--
ALTER TABLE `tenant_memberships`
  ADD CONSTRAINT `fk_tm_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tm_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_measurements`
--
ALTER TABLE `user_measurements`
  ADD CONSTRAINT `fk_measurements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `fk_pref_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_dummy_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_totp`
--
ALTER TABLE `user_totp`
  ADD CONSTRAINT `fk_totp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_totp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
