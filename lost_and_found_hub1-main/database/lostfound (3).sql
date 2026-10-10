-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2026 at 09:19 AM
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
-- Database: `lostfound`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `avatar`, `phone`, `is_active`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'esthernisui2', 'esthernisui2@gmail.com', '$2y$10$QuOQMtwnaUWV9AEni7UYmuejcWCdv7rr4nZAFDd14vHwZHZsgnA7e', 'uploads/avatars/admin_1_1791368767.jpg', '', 1, '2026-10-07 10:25:50', '2026-10-07 10:25:31', '2026-10-07 10:26:07'),
(2, 'qianrou586', 'qianrou586@gmail.com', '$2y$10$ChXSMZ8Y9H7B/pupdJIRiOecw/ytLejN5gYFZ4z6yaBEEcNWRPthy', NULL, NULL, 1, NULL, '2026-10-08 07:18:16', '2026-10-08 07:18:16');

-- --------------------------------------------------------

--
-- Table structure for table `admin_log`
--

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `position_title` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `state_region` varchar(100) NOT NULL DEFAULT '',
  `city_township` varchar(100) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `placeholder_bg` varchar(7) DEFAULT '#F5F5F4',
  `placeholder_icon` varchar(7) DEFAULT '#78716C',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `placeholder_bg`, `placeholder_icon`, `created_at`) VALUES
(1, 'Electronics & Gadgets', 'fa-microchip', '#EEF2FF', '#6366F1', '2026-10-07 05:41:26'),
(2, 'Wallets, Bags & Purses', 'fa-key', '#FFF7ED', '#EA580C', '2026-10-07 05:41:26'),
(3, 'Keys & Cards', 'fa-shirt', '#FDF2F8', '#DB2777', '2026-10-07 05:41:26'),
(4, 'Documents & IDs', 'fa-laptop', '#EEF2FF', '#6366F1', '2026-10-07 05:41:26'),
(5, 'Clothing & Accessories', 'fa-headphones', '#FFF1F2', '#E11D48', '2026-10-07 05:41:26'),
(6, 'Personal Belongings', 'fa-file-lines', '#FFFBEB', '#D97706', '2026-10-07 05:41:26'),
(7, 'Bags & Luggage', 'fa-suitcase', '#F0FDFA', '#0D9488', '2026-10-07 05:41:26'),
(8, 'Jewelry & Watches', 'fa-gem', '#FDF4FF', '#7C3AED', '2026-10-07 05:41:26'),
(9, 'Water Bottles', 'fa-bottle-water', '#ECFDF5', '#0284C7', '2026-10-07 05:41:26'),
(10, 'Stationery', 'fa-pen-fancy', '#FFFBEB', '#D97706', '2026-10-07 05:41:26'),
(11, 'Personal Items', 'fa-bag-shopping', '#F0FDFA', '#0D9488', '2026-10-07 05:41:26'),
(12, 'Pets & Animals', 'fa-paw', '#FEF3C7', '#D97706', '2026-10-07 05:41:26'),
(13, 'Sports Equipment', 'fa-basketball', '#FEF3C7', '#16A34A', '2026-10-07 05:41:26'),
(14, 'Books', 'fa-book', '#FFFBEB', '#D97706', '2026-10-07 05:41:26'),
(15, 'Money & Wallets', 'fa-wallet', '#FFFBEB', '#D97706', '2026-10-07 05:41:26'),
(16, 'Other', 'fa-box-open', '#F5F5F4', '#78716C', '2026-10-07 05:41:26');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `region_id`, `name`, `latitude`, `longitude`, `created_at`) VALUES
(1, 1, 'Yangon', 16.8661000, 96.1951000, '2026-10-08 03:02:11'),
(2, 1, 'Mandalay', 16.7450000, 96.2550000, '2026-10-08 03:02:11'),
(3, 1, 'Naypyidaw', 17.1060000, 96.0040000, '2026-10-08 03:02:11'),
(4, 1, 'Taikkyi', 17.3200000, 95.9650000, '2026-10-08 03:02:11'),
(5, 2, 'Mandalay', 21.9588000, 96.0891000, '2026-10-08 03:02:11'),
(6, 2, 'Pyin Oo Lwin', 22.0360000, 96.4600000, '2026-10-08 03:02:11'),
(7, 2, 'Meiktila', 20.8780000, 95.8600000, '2026-10-08 03:02:11'),
(8, 2, 'Myingyan', 21.4600000, 95.3900000, '2026-10-08 03:02:11'),
(9, 2, 'Kyaukse', 21.6050000, 96.1320000, '2026-10-08 03:02:11'),
(10, 2, 'Bagan / Nyaung-U', 21.1960000, 94.9080000, '2026-10-08 03:02:11'),
(11, 2, 'Yamethin', 20.4300000, 96.1400000, '2026-10-08 03:02:11'),
(12, 5, 'Bago', 17.3220000, 96.4784000, '2026-10-08 03:02:11'),
(13, 5, 'Pyay', 18.8240000, 95.2210000, '2026-10-08 03:02:11'),
(14, 5, 'Taungoo', 18.9430000, 96.4350000, '2026-10-08 03:02:11'),
(15, 5, 'Nyaunglebin', 17.9540000, 96.7210000, '2026-10-08 03:02:11'),
(16, 6, 'Pathein', 16.7792000, 94.7321000, '2026-10-08 03:02:11'),
(17, 6, 'Hinthada', 17.6520000, 95.4560000, '2026-10-08 03:02:11'),
(18, 6, 'Maubin', 16.7320000, 95.6540000, '2026-10-08 03:02:11'),
(19, 6, 'Myaungmya', 16.6020000, 94.9280000, '2026-10-08 03:02:11'),
(20, 6, 'Pyapon', 16.2840000, 95.6810000, '2026-10-08 03:02:11'),
(21, 8, 'Monywa', 22.1086000, 95.1359000, '2026-10-08 03:02:11'),
(22, 8, 'Sagaing', 21.8790000, 95.9790000, '2026-10-08 03:02:11'),
(23, 8, 'Shwebo', 22.5680000, 95.6980000, '2026-10-08 03:02:11'),
(24, 8, 'Kale (Kalaymyo)', 23.1850000, 94.0580000, '2026-10-08 03:02:11'),
(25, 8, 'Katha', 24.1750000, 96.3310000, '2026-10-08 03:02:11'),
(26, 9, 'Dawei', 14.0828000, 98.1940000, '2026-10-08 03:02:11'),
(27, 9, 'Myeik', 12.4410000, 98.6010000, '2026-10-08 03:02:11'),
(28, 9, 'Kawthaung', 9.9820000, 98.5480000, '2026-10-08 03:02:11'),
(29, 10, 'Magway', 20.1544000, 94.9455000, '2026-10-08 03:02:11'),
(30, 10, 'Pakokku', 21.3320000, 95.0820000, '2026-10-08 03:02:11'),
(31, 10, 'Thayet', 19.3170000, 95.1840000, '2026-10-08 03:02:11'),
(32, 10, 'Minbu', 20.1810000, 94.8760000, '2026-10-08 03:02:11'),
(33, 10, 'Chauk', 20.8930000, 94.8210000, '2026-10-08 03:02:11'),
(34, 11, 'Myitkyina', 25.3832000, 97.3964000, '2026-10-08 03:02:11'),
(35, 11, 'Bhamo', 24.2630000, 97.2340000, '2026-10-08 03:02:11'),
(36, 11, 'Mohnyin', 24.7890000, 96.3680000, '2026-10-08 03:02:11'),
(37, 12, 'Hpa-An', 16.8895000, 97.6338000, '2026-10-08 03:02:11'),
(38, 12, 'Myawaddy', 16.6910000, 98.5120000, '2026-10-08 03:02:11'),
(39, 12, 'Kawkareik', 16.5540000, 98.2410000, '2026-10-08 03:02:11'),
(40, 13, 'Loikaw', 19.6742000, 97.2093000, '2026-10-08 03:02:11'),
(41, 13, 'Demoso', 19.5480000, 97.1610000, '2026-10-08 03:02:11'),
(42, 14, 'Hakha', 22.6425000, 93.6067000, '2026-10-08 03:02:11'),
(43, 14, 'Falam', 22.9120000, 93.7250000, '2026-10-08 03:02:11'),
(44, 14, 'Tedim', 23.3640000, 93.6620000, '2026-10-08 03:02:11'),
(45, 7, 'Mawlamyine', 16.4916000, 97.6258000, '2026-10-08 03:02:11'),
(46, 7, 'Thaton', 16.9240000, 97.3710000, '2026-10-08 03:02:11'),
(47, 7, 'Mudon', 16.2580000, 97.7180000, '2026-10-08 03:02:11'),
(48, 15, 'Sittwe', 20.1462000, 92.8984000, '2026-10-08 03:02:11'),
(49, 15, 'Kyaukpyu', 19.4280000, 93.5510000, '2026-10-08 03:02:11'),
(50, 15, 'Thandwe (Ngapali)', 18.4620000, 94.3610000, '2026-10-08 03:02:11'),
(51, 3, 'Taunggyi', 20.7827000, 97.0381000, '2026-10-08 03:02:11'),
(52, 3, 'Lashio', 22.9360000, 97.7490000, '2026-10-08 03:02:11'),
(53, 3, 'Kalaw', 20.6280000, 96.5640000, '2026-10-08 03:02:11'),
(54, 3, 'Muse', 23.9910000, 97.9020000, '2026-10-08 03:02:11'),
(55, 3, 'Kengtung', 21.2910000, 99.6050000, '2026-10-08 03:02:11'),
(56, 4, 'Naypyidaw', 19.7633000, 96.0785000, '2026-10-08 03:02:11');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `claimant_user_id` int(11) NOT NULL,
  `lost_item_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `response_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `flag_emoji` varchar(10) DEFAULT '??',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `flag_emoji`, `created_at`) VALUES
(1, 'Myanmar', 'MMR', '🇲🇲', '2026-10-07 05:41:26');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('lost','found') NOT NULL,
  `category_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `specific_location` varchar(300) DEFAULT NULL,
  `date_occurred` date NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('open','claimed','resolved','expired') DEFAULT 'open',
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `user_id`, `type`, `category_id`, `location_id`, `title`, `description`, `specific_location`, `date_occurred`, `photo_path`, `status`, `is_featured`, `views`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'lost', 16, 2, 'key', 'အဖြူ', NULL, '0000-00-00', 'uploads/items/item_2_1791441740_557bbcc2.jpg', 'open', 0, 0, NULL, '2026-10-08 06:42:20', '2026-10-08 06:42:20'),
(2, 2, 'found', 5, 30, 'jacket', 'အမဲ', NULL, '0000-00-00', 'uploads/items/item_2_1791441861_05afb116.jpg', 'open', 0, 0, NULL, '2026-10-08 06:44:21', '2026-10-08 06:44:21'),
(3, 1, 'found', 16, 2, 'key', '', NULL, '0000-00-00', 'uploads/items/item_1_1791441969_35e85bac.jpg', 'open', 0, 0, NULL, '2026-10-08 06:46:09', '2026-10-08 06:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` varchar(300) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `city_id`, `name`, `address`, `latitude`, `longitude`, `created_at`) VALUES
(1, 1, 'Hledan Center', 'Corner of Bogyoke Aung San Rd & Shwedagon Pagoda Rd, Pabedan, Yangon', 16.7788000, 96.1539000, '2026-10-08 03:02:11'),
(2, 1, 'Junction City', 'Pyay Road, Kamayut Township, Yangon', 16.8184000, 96.1306000, '2026-10-08 03:02:11'),
(3, 1, 'Yangon University Campus', 'Kabar Aye Pagoda Road, Bahan Township, Yangon', 16.8272000, 96.1558000, '2026-10-08 03:02:11'),
(4, 1, 'Diamond Plaza', 'Corner of Hanthawaddy & Kyun Taw Rd, Kamayut, Yangon', 16.8122000, 96.1265000, '2026-10-08 03:02:11'),
(5, 1, 'Mandalay University Campus', 'Corner of Pyay Road & Hledan Road, Kamayut, Yangon', 16.8256000, 96.1293000, '2026-10-08 03:02:11'),
(6, 1, 'Junction Naypyitaw', 'Sule Pagoda Road, Kyauktada Township, Yangon', 16.7766000, 96.1594000, '2026-10-08 03:02:11'),
(7, 1, 'Taw Win Centre', 'Pyay Road, Dagon Township, Yangon', 16.7869000, 96.1422000, '2026-10-08 03:02:11'),
(8, 1, 'City Mall St. John', 'Corner of Pyay Rd & Min Ye Kyaw Zwa Rd, Lanmadaw, Yangon', 16.7865000, 96.1455000, '2026-10-08 03:02:11'),
(9, 1, 'Junction 8', 'Kyaik Waing Pagoda Road, 8 Mile, Mayangone, Yangon', 16.8694000, 96.1458000, '2026-10-08 03:02:11'),
(10, 1, 'Terminal M Leisure Mall', 'No. 3 Main Road, Mingaladon, Yangon', 16.9458000, 96.1542000, '2026-10-08 03:02:11'),
(11, 1, 'University of Yangon (Main Campus)', 'University Avenue Road, Kamayut, Yangon', 16.8286000, 96.1352000, '2026-10-08 03:02:11'),
(12, 1, 'Yangon Technological University (YTU)', 'Gyogone, Insein Township, Yangon', 16.8837000, 96.1154000, '2026-10-08 03:02:11'),
(13, 1, 'Dagon University', 'North Dagon Township, Yangon', 16.8924000, 96.2238000, '2026-10-08 03:02:11'),
(14, 1, 'Yangon Institute of Economics (Hlaing)', 'Parami Road, Hlaing Township, Yangon', 16.8553000, 96.1338000, '2026-10-08 03:02:11'),
(15, 1, 'University of Computer Studies, Yangon (UCSY)', 'Shwe Pyi Thar Township, Yangon', 17.0069000, 96.1102000, '2026-10-08 03:02:11'),
(16, 1, 'Yangon Central Railway Station', 'Kun Chan Road, Mingalar Taung Nyunt, Yangon', 16.7828000, 96.1627000, '2026-10-08 03:02:11'),
(17, 1, 'Yangon International Airport (T1/T3)', 'Airport Road, Mingaladon, Yangon', 16.9073000, 96.1332000, '2026-10-08 03:02:11'),
(18, 1, 'Aung Mingalar Highway Bus Terminal', 'North Okkalapa Township, Yangon', 16.9298000, 96.1685000, '2026-10-08 03:02:11'),
(19, 1, 'Dagon Ayeyar Highway Bus Terminal', 'Hlaing Thar Yar Township, Yangon', 16.8436000, 96.0621000, '2026-10-08 03:02:11'),
(20, 1, 'Yangon General Hospital (YGH)', 'Bogyoke Aung San Road, Latha Township, Yangon', 16.7761000, 96.1531000, '2026-10-08 03:02:11'),
(21, 2, 'Myanmar Maritime University (MMU)', 'Kamayut/Thanlyin Campus, Thanlyin', 16.7328000, 96.2570000, '2026-10-08 03:02:11'),
(22, 2, 'Thanlyin Technological University (TTU)', 'Thanlyin-Kyauktan Highway, Thanlyin', 16.7450000, 96.2750000, '2026-10-08 03:02:11'),
(23, 2, 'Thanlyin Market (Myoma Zay)', 'Main Street, Thanlyin', 16.7620000, 96.2510000, '2026-10-08 03:02:11'),
(24, 3, 'Hmawbi Technology University (TU Hmawbi)', 'Yangon-Pyay Road, Hmawbi', 17.1120000, 96.0120000, '2026-10-08 03:02:11'),
(25, 3, 'Hmawbi Central Bus Stop', 'Yangon-Pyay Highway, Hmawbi', 17.1040000, 96.0020000, '2026-10-08 03:02:11'),
(26, 4, 'Taikkyi Railway Station', 'Station Road, Taikkyi', 17.3210000, 95.9620000, '2026-10-08 03:02:11'),
(27, 4, 'Taikkyi General Hospital', 'Main Road, Taikkyi', 17.3180000, 95.9680000, '2026-10-08 03:02:11'),
(28, 5, 'Diamond Plaza Mandalay', 'Corner of 78th & 34th Street, Chanayethazan, Mandalay', 21.9686000, 96.0847000, '2026-10-08 03:02:11'),
(29, 5, 'Mingalar Mandalay Complex', '73rd Street, Chanmyathazi Township, Mandalay', 21.9421000, 96.0914000, '2026-10-08 03:02:11'),
(30, 5, 'Ocean Supercenter (Mandalay 73rd)', 'Corner of 73rd & 31st Street, Mandalay', 21.9702000, 96.0963000, '2026-10-08 03:02:11'),
(31, 5, 'Man Myanmar Plaza (Zay Cho)', '84th Street, between 26th & 28th, Mandalay', 21.9825000, 96.0782000, '2026-10-08 03:02:11'),
(32, 5, 'Mandalay University', 'University Road, Maha Aungmye, Mandalay', 21.9567000, 96.0954000, '2026-10-08 03:02:11'),
(33, 5, 'Mandalay Technological University (MTU)', 'Patheingyi Township, Mandalay', 21.9965000, 96.1754000, '2026-10-08 03:02:11'),
(34, 5, 'University of Computer Studies, Mandalay (UCSM)', 'Patheingyi Township, Mandalay', 22.0125000, 96.1820000, '2026-10-08 03:02:11'),
(35, 5, 'Mandalay Central Railway Station', '78th Street, between 30th & 31st, Mandalay', 21.9744000, 96.0858000, '2026-10-08 03:02:11'),
(36, 5, 'Kywe Ser Kan Highway Bus Terminal', 'Pyigyidagun Township, Mandalay', 21.9056000, 96.0825000, '2026-10-08 03:02:11'),
(37, 5, 'Mandalay General Hospital (MGH)', '30th Street, between 74th & 77th, Mandalay', 21.9710000, 96.0880000, '2026-10-08 03:02:11'),
(38, 6, 'National Kandawgyi Gardens', 'Nanda Road, Pyin Oo Lwin', 21.9980000, 96.4710000, '2026-10-08 03:02:11'),
(39, 6, 'Ocean Supercenter (Pyin Oo Lwin)', 'Mandalay-Lashio Road, Pyin Oo Lwin', 22.0280000, 96.4520000, '2026-10-08 03:02:11'),
(40, 6, 'Pyin Oo Lwin Railway Station', 'Station Road, Pyin Oo Lwin', 22.0320000, 96.4650000, '2026-10-08 03:02:11'),
(41, 6, 'Defense Services Academy (DSA Area)', 'Pyin Oo Lwin', 22.0520000, 96.4850000, '2026-10-08 03:02:11'),
(42, 7, 'Meiktila University', 'Meiktila-Kyaukpadaung Road, Meiktila', 20.8920000, 95.8350000, '2026-10-08 03:02:11'),
(43, 7, 'Meiktila Central Market', 'Bogyoke Road, Meiktila', 20.8810000, 95.8640000, '2026-10-08 03:02:11'),
(44, 7, 'Meiktila General Hospital', 'Hospital Road, Meiktila', 20.8750000, 95.8580000, '2026-10-08 03:02:11'),
(45, 8, 'Myingyan Degree College', 'Myingyan-Mandalay Road, Myingyan', 21.4720000, 95.4050000, '2026-10-08 03:02:11'),
(46, 8, 'Myingyan Central Bus Station', 'Main Road, Myingyan', 21.4580000, 95.3850000, '2026-10-08 03:02:11'),
(47, 9, 'Kyaukse Technological University', 'Kyaukse-Mandalay Road, Kyaukse', 21.6150000, 96.1420000, '2026-10-08 03:02:11'),
(48, 9, 'Kyaukse Central Market', 'Min Road, Kyaukse', 21.6020000, 96.1280000, '2026-10-08 03:02:11'),
(49, 10, 'Nyaung-U Airport', 'Airport Road, Nyaung-U, Bagan', 21.1780000, 94.9310000, '2026-10-08 03:02:11'),
(50, 10, 'Mani-Sithu Market (Nyaung-U)', 'Main Road, Nyaung-U', 21.1980000, 94.9050000, '2026-10-08 03:02:11'),
(51, 10, 'Bagan Archaeological Zone Entrance', 'Old Bagan Main Road, Bagan', 21.1710000, 94.8580000, '2026-10-08 03:02:11'),
(52, 11, 'Yamethin Degree College', 'Highway Road, Yamethin', 20.4420000, 96.1480000, '2026-10-08 03:02:11'),
(53, 11, 'Yamethin Railway Station', 'Station Road, Yamethin', 20.4280000, 96.1380000, '2026-10-08 03:02:11'),
(54, 12, 'Ocean Supercenter (Bago)', 'Yangon-Mandalay Highway, Bago', 17.3085000, 96.4712000, '2026-10-08 03:02:11'),
(55, 12, 'Bago University', 'Magway Road, Bago', 17.2954000, 96.4635000, '2026-10-08 03:02:11'),
(56, 12, 'Bago Railway Station', 'Station Road, Bago', 17.3278000, 96.4795000, '2026-10-08 03:02:11'),
(57, 12, 'Bago General Hospital', 'Shew Thar Lyaung Quarter, Bago', 17.3340000, 96.4750000, '2026-10-08 03:02:11'),
(58, 13, 'Pyay University', 'Pyay-Aunglan Road, Pyay', 18.8450000, 95.2420000, '2026-10-08 03:02:11'),
(59, 13, 'Pyay Technological University (PTU)', 'Hmawza, Pyay', 18.8120000, 95.2750000, '2026-10-08 03:02:11'),
(60, 13, 'Pyay Central Market (Myoma)', 'Strand Road, Pyay', 18.8250000, 95.2150000, '2026-10-08 03:02:11'),
(61, 13, 'Pyay Highway Bus Station', 'Bypass Road, Pyay', 18.8350000, 95.2350000, '2026-10-08 03:02:11'),
(62, 14, 'Taungoo University', 'Taungoo Outskirt, Taungoo', 18.9620000, 96.4520000, '2026-10-08 03:02:11'),
(63, 14, 'Taungoo Railway Station', 'Station Road, Taungoo', 18.9380000, 96.4320000, '2026-10-08 03:02:11'),
(64, 14, 'Taungoo Central Market', 'Bogyoke Road, Taungoo', 18.9450000, 96.4390000, '2026-10-08 03:02:11'),
(65, 15, 'Nyaunglebin Railway Station', 'Station Road, Nyaunglebin', 17.9560000, 96.7240000, '2026-10-08 03:02:11'),
(66, 15, 'Nyaunglebin Central Market', 'Main Road, Nyaunglebin', 17.9510000, 96.7190000, '2026-10-08 03:02:11'),
(67, 16, 'Ocean Supercenter (Pathein)', 'Pathein-Kyangin Road, Pathein', 16.7885000, 94.7410000, '2026-10-08 03:02:11'),
(68, 16, 'Pathein University', 'Kandawgyi Road, Pathein', 16.7925000, 94.7550000, '2026-10-08 03:02:11'),
(69, 16, 'Pathein Highway Bus Terminal', 'Pathein Bypass Road, Pathein', 16.7960000, 94.7480000, '2026-10-08 03:02:11'),
(70, 16, 'Pathein General Hospital', 'Merchant Street, Pathein', 16.7760000, 94.7350000, '2026-10-08 03:02:11'),
(71, 17, 'Hinthada University', 'Hinthada-Zalun Road, Hinthada', 17.6680000, 95.4720000, '2026-10-08 03:02:11'),
(72, 17, 'Hinthada Central Market', 'Bogyoke Road, Hinthada', 17.6490000, 95.4520000, '2026-10-08 03:02:11'),
(73, 18, 'Maubin University', 'Maubin Outskirt, Maubin', 16.7450000, 95.6680000, '2026-10-08 03:02:11'),
(74, 18, 'Maubin Clock Tower & Market', 'Main Road, Maubin', 16.7280000, 95.6480000, '2026-10-08 03:02:11'),
(75, 19, 'Myaungmya Degree College', 'College Road, Myaungmya', 16.6120000, 94.9350000, '2026-10-08 03:02:11'),
(76, 19, 'Myaungmya General Hospital', 'Hospital Road, Myaungmya', 16.5980000, 94.9220000, '2026-10-08 03:02:11'),
(77, 20, 'Pyapon Central Market', 'Strand Road, Pyapon', 16.2880000, 95.6850000, '2026-10-08 03:02:11'),
(78, 20, 'Pyapon Bus Station', 'Main Road, Pyapon', 16.2810000, 95.6760000, '2026-10-08 03:02:11'),
(79, 21, 'Ocean Supercenter (Monywa)', 'Bogyoke Aung San Road, Monywa', 22.1150000, 95.1420000, '2026-10-08 03:02:11'),
(80, 21, 'Monywa University', 'Monywa-Mandalar Road, Monywa', 22.0890000, 95.1550000, '2026-10-08 03:02:11'),
(81, 21, 'Monywa Highway Bus Terminal', 'Kyaukka Road, Monywa', 22.1180000, 95.1510000, '2026-10-08 03:02:11'),
(82, 21, 'Monywa General Hospital', 'Hospital Road, Monywa', 22.1120000, 95.1380000, '2026-10-08 03:02:11'),
(83, 22, 'Sagaing University of Education', 'Sagaing Hill Road, Sagaing', 21.8920000, 95.9650000, '2026-10-08 03:02:11'),
(84, 22, 'Sagaing Central Market', 'Myoma Road, Sagaing', 21.8750000, 95.9810000, '2026-10-08 03:02:11'),
(85, 23, 'Shwebo University', 'Shwebo-Mandalay Road, Shwebo', 22.5820000, 95.7120000, '2026-10-08 03:02:11'),
(86, 23, 'Shwebo Central Market', 'Bogyoke Road, Shwebo', 22.5620000, 95.6940000, '2026-10-08 03:02:11'),
(87, 24, 'Kalay University', 'Kalay-Pinlong Road, Kalaymyo', 23.1650000, 94.0450000, '2026-10-08 03:02:11'),
(88, 24, 'Kalay Airport', 'Airport Road, Kalaymyo', 23.1820000, 94.0520000, '2026-10-08 03:02:11'),
(89, 24, 'Tahan Market (Kalay)', 'Bogyoke Road, Tahan, Kalaymyo', 23.2050000, 94.0620000, '2026-10-08 03:02:11'),
(90, 25, 'Katha Railway Station', 'Station Road, Katha', 24.1780000, 96.3350000, '2026-10-08 03:02:11'),
(91, 25, 'Katha River Port', 'Strand Road, Katha', 24.1720000, 96.3280000, '2026-10-08 03:02:11'),
(92, 26, 'Dawei Central Shopping Mall', 'Arzarni Road, Dawei', 14.0880000, 98.1980000, '2026-10-08 03:02:11'),
(93, 26, 'Dawei University', 'Dawei-Myeik Road, Dawei', 14.1350000, 98.2250000, '2026-10-08 03:02:11'),
(94, 26, 'Dawei Airport', 'Airport Road, Dawei', 14.1010000, 98.2040000, '2026-10-08 03:02:11'),
(95, 27, 'Myeik Shopping Center', 'Bogyoke Road, Myeik', 12.4480000, 98.6080000, '2026-10-08 03:02:11'),
(96, 27, 'Myeik University', 'Myeik Outskirt, Myeik', 12.4650000, 98.6250000, '2026-10-08 03:02:11'),
(97, 27, 'Myeik Airport', 'Airport Road, Myeik', 12.4380000, 98.6180000, '2026-10-08 03:02:11'),
(98, 28, 'Kawthaung Myoma Market & Jetty', 'Strand Road, Kawthaung', 9.9780000, 98.5420000, '2026-10-08 03:02:11'),
(99, 28, 'Kawthaung Airport', 'Airport Road, Kawthaung', 10.0450000, 98.5350000, '2026-10-08 03:02:11'),
(100, 29, 'Magway City Mart & Plaza', 'Pyidaungsu Road, Magway', 20.1510000, 94.9510000, '2026-10-08 03:02:11'),
(101, 29, 'Magway University', 'Taungdwingyi Road, Magway', 20.1380000, 94.9620000, '2026-10-08 03:02:11'),
(102, 29, 'University of Medicine, Magway', 'Hospital Road, Magway', 20.1600000, 94.9480000, '2026-10-08 03:02:11'),
(103, 29, 'Magway Highway Bus Terminal', 'Natmauk Road, Magway', 20.1620000, 94.9580000, '2026-10-08 03:02:11'),
(104, 30, 'Pakokku University', 'Pakokku-Mandalay Road, Pakokku', 21.3450000, 95.1050000, '2026-10-08 03:02:11'),
(105, 30, 'Pakokku Central Market (Myoma)', 'Bogyoke Road, Pakokku', 21.3280000, 95.0780000, '2026-10-08 03:02:11'),
(106, 30, 'Pakokku Highway Bus Station', 'Bypass Road, Pakokku', 21.3380000, 95.0920000, '2026-10-08 03:02:11'),
(107, 31, 'Thayet Degree College', 'Main Road, Thayet', 19.3220000, 95.1880000, '2026-10-08 03:02:11'),
(108, 31, 'Thayet General Hospital', 'Hospital Road, Thayet', 19.3120000, 95.1790000, '2026-10-08 03:02:11'),
(109, 32, 'Minbu Central Bus Terminal', 'Pyidaungsu Highway, Minbu', 20.1850000, 94.8790000, '2026-10-08 03:02:11'),
(110, 32, 'Minbu Myoma Market', 'Market Street, Minbu', 20.1780000, 94.8720000, '2026-10-08 03:02:11'),
(111, 33, 'Chauk Central Market', 'Bogyoke Road, Chauk', 20.8980000, 94.8250000, '2026-10-08 03:02:11'),
(112, 33, 'Chauk General Hospital', 'Hospital Road, Chauk', 20.8890000, 94.8160000, '2026-10-08 03:02:11'),
(113, 34, 'Myitkyina Central Plaza & Market', 'Bogyoke Road, Myitkyina', 25.3850000, 97.3940000, '2026-10-08 03:02:11'),
(114, 34, 'Myitkyina University', 'Sitarpu Quarter, Myitkyina', 25.4220000, 97.4080000, '2026-10-08 03:02:11'),
(115, 34, 'Myitkyina Railway Station', 'Station Road, Myitkyina', 25.3810000, 97.3910000, '2026-10-08 03:02:11'),
(116, 34, 'Myitkyina Airport', 'Airport Road, Myitkyina', 25.3780000, 97.3510000, '2026-10-08 03:02:11'),
(117, 35, 'Bhamo University', 'Bhamo Outskirt, Bhamo', 24.2750000, 97.2480000, '2026-10-08 03:02:11'),
(118, 35, 'Bhamo Airport', 'Airport Road, Bhamo', 24.2680000, 97.2410000, '2026-10-08 03:02:11'),
(119, 36, 'Mohnyin Degree College', 'College Road, Mohnyin', 24.7950000, 96.3750000, '2026-10-08 03:02:11'),
(120, 36, 'Mohnyin Railway Station', 'Station Road, Mohnyin', 24.7820000, 96.3620000, '2026-10-08 03:02:11'),
(121, 37, 'Zwekapin Plaza (Hpa-An)', 'Thiri Road, Hpa-An', 16.8860000, 97.6320000, '2026-10-08 03:02:11'),
(122, 37, 'Hpa-An University', 'Hpa-An-Zathabyin Road, Hpa-An', 16.8650000, 97.6520000, '2026-10-08 03:02:11'),
(123, 37, 'Hpa-An Clock Tower & Bus Station', 'Main Road, Hpa-An', 16.8880000, 97.6340000, '2026-10-08 03:02:11'),
(124, 38, 'Myawaddy Border Trade Zone & Bridge 1', 'Friendship Bridge Road, Myawaddy', 16.6950000, 98.5150000, '2026-10-08 03:02:11'),
(125, 38, 'Myawaddy Central Market (Bayintnaung)', 'Bogyoke Road, Myawaddy', 16.6890000, 98.5080000, '2026-10-08 03:02:11'),
(126, 39, 'Kawkareik Highway Bus Station', 'Asian Highway, Kawkareik', 16.5580000, 98.2450000, '2026-10-08 03:02:11'),
(127, 40, 'Thiri Mingalar Market (Loikaw)', 'Main Road, Loikaw', 19.6780000, 97.2120000, '2026-10-08 03:02:11'),
(128, 40, 'Loikaw University', 'Loikaw-Demoso Road, Loikaw', 19.6520000, 97.2150000, '2026-10-08 03:02:11'),
(129, 40, 'Loikaw Airport', 'Airport Road, Loikaw', 19.6910000, 97.2140000, '2026-10-08 03:02:11'),
(130, 41, 'Demoso Central Market', 'Highway Road, Demoso', 19.5520000, 97.1650000, '2026-10-08 03:02:11'),
(131, 42, 'Hakha Central Market', 'Main Street, Hakha', 22.6450000, 93.6080000, '2026-10-08 03:02:11'),
(132, 42, 'Hakha University', 'Hakha-Falam Road, Hakha', 22.6680000, 93.6120000, '2026-10-08 03:02:11'),
(133, 42, 'Hakha General Hospital', 'Hospital Road, Hakha', 22.6410000, 93.6110000, '2026-10-08 03:02:11'),
(134, 43, 'Falam Central Market', 'Bogyoke Road, Falam', 22.9150000, 93.7280000, '2026-10-08 03:02:11'),
(135, 44, 'Tedim Myoma Market', 'Main Street, Tedim', 23.3680000, 93.6650000, '2026-10-08 03:02:11'),
(136, 45, 'Ocean Supercenter (Mawlamyine)', 'Lower Main Road, Mawlamyine', 16.4820000, 97.6210000, '2026-10-08 03:02:11'),
(137, 45, 'Mawlamyine University', 'Main Campus Road, Mawlamyine', 16.4480000, 97.6390000, '2026-10-08 03:02:11'),
(138, 45, 'Mawlamyine Central Railway Station', 'Station Road, Mawlamyine', 16.4950000, 97.6320000, '2026-10-08 03:02:11'),
(139, 45, 'Mawlamyine Highway Bus Terminal', 'Zayar Thiri Quarter, Mawlamyine', 16.4680000, 97.6450000, '2026-10-08 03:02:11'),
(140, 46, 'Thaton Computer University (UCST)', 'Thaton-Hpa-An Road, Thaton', 16.9420000, 97.3850000, '2026-10-08 03:02:11'),
(141, 46, 'Thaton Central Market', 'Bogyoke Road, Thaton', 16.9210000, 97.3680000, '2026-10-08 03:02:11'),
(142, 47, 'Mudon Central Market', 'Main Road, Mudon', 16.2550000, 97.7150000, '2026-10-08 03:02:11'),
(143, 48, 'Sittwe Central Market (Myoma Zay)', 'Merchant Street, Sittwe', 20.1440000, 92.8950000, '2026-10-08 03:02:11'),
(144, 48, 'Sittwe University', 'Main Road, Sittwe', 20.1620000, 92.8750000, '2026-10-08 03:02:11'),
(145, 48, 'Sittwe Airport', 'Airport Road, Sittwe', 20.1480000, 92.8720000, '2026-10-08 03:02:11'),
(146, 49, 'Kyaukpyu Degree College', 'Main Road, Kyaukpyu', 19.4350000, 93.5580000, '2026-10-08 03:02:11'),
(147, 49, 'Kyaukpyu Airport', 'Airport Road, Kyaukpyu', 19.4250000, 93.5350000, '2026-10-08 03:02:11'),
(148, 50, 'Thandwe Airport (Mazun)', 'Ngapali Beach Road, Thandwe', 18.4550000, 94.3010000, '2026-10-08 03:02:11'),
(149, 50, 'Thandwe Central Market', 'Bogyoke Road, Thandwe', 18.4680000, 94.3650000, '2026-10-08 03:02:11'),
(150, 51, 'City Square Shopping Center (Taunggyi)', 'Ye Htwet Oo Street, Taunggyi', 20.7845000, 97.0348000, '2026-10-08 03:02:11'),
(151, 51, 'Taunggyi Central Market (Myoma Zay)', 'Bogyoke Aung San Road, Taunggyi', 20.7815000, 97.0360000, '2026-10-08 03:02:11'),
(152, 51, 'Taunggyi University', 'Taunggyi-Hopong Road, Taunggyi', 20.7650000, 97.0650000, '2026-10-08 03:02:11'),
(153, 51, 'Taunggyi Main Bus Terminal', 'Bogyoke Road, Taunggyi', 20.7827000, 97.0381000, '2026-10-08 03:02:11'),
(154, 51, 'Sao San Tun General Hospital', 'Sao San Tun Road, Taunggyi', 20.7950000, 97.0420000, '2026-10-08 03:02:11'),
(155, 52, 'Lashio Central Market (Zay Gyi)', 'Bogyoke Road, Lashio', 22.9380000, 97.7520000, '2026-10-08 03:02:11'),
(156, 52, 'Lashio University', 'Lashio Outskirt, Lashio', 22.9550000, 97.7680000, '2026-10-08 03:02:11'),
(157, 52, 'Lashio Airport', 'Airport Road, Lashio', 22.9780000, 97.7510000, '2026-10-08 03:02:11'),
(158, 53, 'Kalaw Central Market', 'Merchant Road, Kalaw', 20.6310000, 96.5680000, '2026-10-08 03:02:11'),
(159, 53, 'Kalaw Railway Station', 'Station Road, Kalaw', 20.6250000, 96.5590000, '2026-10-08 03:02:11'),
(160, 54, 'Muse Border Trade Zone (105-Mile)', 'Asian Highway, Muse', 23.9850000, 97.8950000, '2026-10-08 03:02:11'),
(161, 54, 'Muse Central Market', 'Main Road, Muse', 23.9950000, 97.9050000, '2026-10-08 03:02:11'),
(162, 55, 'Kengtung University', 'Airport Road, Kengtung', 21.2850000, 99.6150000, '2026-10-08 03:02:11'),
(163, 55, 'Kengtung Airport', 'Airport Road, Kengtung', 21.3010000, 99.6350000, '2026-10-08 03:02:11'),
(164, 56, 'Junction Centre Naypyitaw', 'Dekkhina Thiri Township, Naypyidaw', 19.7285000, 96.1150000, '2026-10-08 03:02:11'),
(165, 56, 'Ocean Supercenter (Naypyitaw)', 'Zabu Thiri Township, Naypyidaw', 19.7420000, 96.1280000, '2026-10-08 03:02:11'),
(166, 56, 'Capital Hypermarket (Naypyitaw)', 'Dekkhina Thiri Township, Naypyidaw', 19.7310000, 96.1120000, '2026-10-08 03:02:11'),
(167, 56, 'Naypyitaw Central Railway Station', 'Pobbathiri Township, Naypyidaw', 19.8320000, 96.1950000, '2026-10-08 03:02:11'),
(168, 56, 'Naypyitaw International Airport', 'Ela, Lewe Township, Naypyidaw', 19.6230000, 96.2020000, '2026-10-08 03:02:11'),
(169, 56, 'Naypyitaw 1000-Bed General Hospital', 'Zabu Thiri Township, Naypyidaw', 19.7510000, 96.1210000, '2026-10-08 03:02:11');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `lost_item_id` int(11) NOT NULL,
  `found_item_id` int(11) NOT NULL,
  `score` decimal(4,2) NOT NULL,
  `status` enum('pending','confirmed','dismissed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `lost_item_id`, `found_item_id`, `score`, `status`, `created_at`) VALUES
(1, 1, 3, 90.00, 'pending', '2026-10-08 06:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('match','claim','claim_response','system','item_expired','item_resolved','welcome') NOT NULL DEFAULT 'system',
  `title` varchar(200) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `purpose` enum('user_login','admin_login') NOT NULL,
  `code_hash` char(64) NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `purpose`, `code_hash`, `attempts`, `expires_at`, `used_at`, `ip_address`, `created_at`) VALUES
(1, 1, 'user_login', '694f9a2a4ff8594ca5e6887b394dcbfde958d2796cc4f97cc609e12945bbaa64', 0, '2026-10-07 22:37:00', '2026-10-07 22:32:16', '::1', '2026-10-07 16:02:00'),
(2, 1, 'user_login', 'ab547adf49727d1f126aa4dbab114115516396b64475ee7d77444feb6649397f', 0, '2026-10-08 12:46:50', '2026-10-08 12:42:08', '::1', '2026-10-08 06:11:50'),
(3, 1, 'user_login', '6513c02c3dd175899cbe471cf2358be530eef9aa2dc8cc0895899fa2f13a9bcf', 0, '2026-10-08 12:52:34', '2026-10-08 12:47:56', '::1', '2026-10-08 06:17:34'),
(4, 1, 'user_login', '554296d117988070dba8db466d219913fdc2f1e730a644f788aba1efe35d7188', 0, '2026-10-08 12:55:52', '2026-10-08 12:51:08', '::1', '2026-10-08 06:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `country_id`, `name`, `created_at`) VALUES
(1, 1, 'Yangon Region', '2026-10-07 05:41:26'),
(2, 1, 'Mandalay Region', '2026-10-07 05:41:26'),
(3, 1, 'Shan State', '2026-10-07 05:41:26'),
(4, 1, 'Naypyidaw Union Territory', '2026-10-07 05:41:26'),
(5, 1, 'Bago Region', '2026-10-07 05:41:26'),
(6, 1, 'Ayeyarwady Region', '2026-10-07 05:41:26'),
(7, 1, 'Mon State', '2026-10-07 05:41:26'),
(8, 1, 'Sagaing Region', '2026-10-07 05:41:26'),
(9, 1, 'Tanintharyi Region', '2026-10-07 05:41:26'),
(10, 1, 'Magway Region', '2026-10-07 05:41:26'),
(11, 1, 'Kachin State', '2026-10-07 05:41:26'),
(12, 1, 'Kayin State', '2026-10-07 05:41:26'),
(13, 1, 'Kayah State', '2026-10-07 05:41:26'),
(14, 1, 'Chin State', '2026-10-07 05:41:26'),
(15, 1, 'Rakhine State', '2026-10-07 05:41:26');

-- --------------------------------------------------------

--
-- Table structure for table `saves`
--

CREATE TABLE `saves` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_suspended` tinyint(1) DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `avatar`, `bio`, `phone`, `is_suspended`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 'terrybeen', 'terrybeen1@gmail.com', '$2y$10$NjepxF.m1EMT8HrqPwVtZumikhfLPGz.iTylrSjMY0xQaFSIsaoFu', NULL, NULL, NULL, 0, '2026-10-08 06:21:08', '2026-10-07 16:01:54', '2026-10-08 06:21:08'),
(2, 'esthernisui', 'esthernisui2@gmail.com', '$2y$10$8P0ZIjyyfr7bfjCuryPHXulZy9eMuPbStCvKzXarxJLQnyy/xa2Cy', NULL, NULL, NULL, 0, NULL, '2026-10-08 06:33:36', '2026-10-08 06:33:36');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `state_region` varchar(100) NOT NULL DEFAULT '',
  `city_township` varchar(100) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`user_id`, `full_name`, `phone`, `state_region`, `city_township`, `address`, `updated_at`, `avatar`) VALUES
(1, 'terrybeen', '09425867189', '', '', '', '2026-10-07 16:04:47', 'uploads/avatars/avatar_1_1791389087.jpg'),
(2, 'esthernisui', '09420000000', '', '', '', '2026-10-08 06:34:19', 'uploads/avatars/avatar_2_1791441259.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_log`
--
ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region_id` (`region_id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `found_item_id` (`found_item_id`),
  ADD KEY `claimant_user_id` (`claimant_user_id`),
  ADD KEY `lost_item_id` (`lost_item_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_type_status` (`type`,`status`),
  ADD KEY `idx_items_category` (`category_id`),
  ADD KEY `items_location` (`location_id`),
  ADD KEY `idx_items_date` (`date_occurred`),
  ADD KEY `idx_items_user` (`user_id`),
  ADD KEY `idx_items_expires` (`expires_at`,`status`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pair` (`lost_item_id`,`found_item_id`),
  ADD KEY `found_item_id` (`found_item_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_otp_lookup` (`user_id`,`purpose`,`used_at`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `saves`
--
ALTER TABLE `saves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`item_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `idx_saves_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_log`
--
ALTER TABLE `admin_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `saves`
--
ALTER TABLE `saves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_log`
--
ALTER TABLE `admin_log`
  ADD CONSTRAINT `admin_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD CONSTRAINT `admin_profiles_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`found_item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_3` FOREIGN KEY (`lost_item_id`) REFERENCES `items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `items_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`lost_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`found_item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `regions`
--
ALTER TABLE `regions`
  ADD CONSTRAINT `regions_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saves`
--
ALTER TABLE `saves`
  ADD CONSTRAINT `saves_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saves_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
