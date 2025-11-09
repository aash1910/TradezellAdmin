-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: piqdrop.com.mysql.service.one.com:3306
-- Generation Time: Nov 08, 2025 at 07:25 PM
-- Server version: 10.6.23-MariaDB-ubu2204
-- PHP Version: 8.1.2-1ubuntu2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `piqdrop_compiqdropdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_deletion_requests`
--

CREATE TABLE `data_deletion_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `confirmation_code` varchar(255) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `facebook_user_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `request_data` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `title`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'How do I reset my password?', 'To reset your password, go to the login screen and tap on \"Forgot Password?\". Follow the instructions sent to your email.', 1, NULL, NULL),
(2, 'How can I contact support?', 'You can contact support by tapping the \"Get Support\" button at the bottom of this page or emailing support@example.com.', 1, NULL, NULL),
(3, 'Where can I find my purchase history?', 'Your purchase history is available in the \"Account\" section under \"Orders\".', 1, NULL, NULL),
(4, 'How do I update my profile information?', 'Go to the \"Account\" tab and select \"Edit Profile\" to update your information.', 1, NULL, NULL),
(5, 'Is my data secure?', 'Yes, we use industry-standard encryption to protect your data.', 1, NULL, NULL),
(6, 'Can I use the app offline?', 'Some features are available offline, but for the best experience, connect to the internet.', 1, NULL, NULL),
(7, 'How do I delete my account?', 'To delete your account, please contact support through the \"Get Support\" button.', 1, NULL, NULL),
(8, 'How do I enable notifications?', 'Enable notifications in your device settings under \"Notifications\".', 1, NULL, NULL),
(9, 'What payment methods are accepted?', 'We accept major credit cards, PayPal, and Apple Pay.', 1, NULL, NULL),
(10, 'How do I report a bug?', 'Report bugs by contacting support or using the feedback form in the app.', 1, NULL, NULL),
(11, 'How much does Rider makes per delivery?', 'Riders make 90% for every delivery. They are responsible for their taxes. Piqdrop takes 10% .', 1, '2025-10-16 07:40:51', '2025-10-29 18:26:50'),
(12, 'Who are riders?', 'Riders are freelancers and travellers who can pick up packages towards their destination and drop them off.', 1, '2025-10-24 09:23:06', '2025-10-24 09:24:55'),
(13, 'Why does it say insufficient funds when I can see money in my account?', 'When you receive your first payment as a Rider, Stripe (our payment processor) holds the funds for 7-10 days for security purposes. During this time, the money appears in your balance but is not yet available for withdrawal. You can see the exact date when your funds will be available on your wallet page. After your first successful payout, future payments will be available much faster (typically 2-3 days).', 1, '2025-11-01 13:21:07', '2025-11-01 13:21:07'),
(14, 'When will my funds be available for withdrawal?', 'Your fund availability depends on your account status:\n\n• First Payment: 7-10 days from when you completed the delivery\n• Subsequent Payments: 2-3 business days\n\nYou can check the exact availability date on your wallet page. The date will be displayed under your balance if you have pending funds.', 1, '2025-11-01 13:21:07', '2025-11-01 13:21:07'),
(15, 'How do I check when my pending funds will be available?', 'To check when your funds will be available:\n\n1. Open the Wallet page in the app\n2. Look under your balance amount\n3. You will see a notice showing when pending funds will be available\n4. The notice will show the amount and the exact date\n\nExample: \"$4.54 available on Mar 11, 2025\"', 1, '2025-11-01 13:21:07', '2025-11-01 13:21:07'),
(16, 'Why does Stripe hold my first payment?', 'Stripe holds the first payment for new Riders as a security measure to:\n\n• Verify your identity and account\n• Protect against fraud\n• Ensure compliance with financial regulations\n• Build trust in the payment system\n\nThis is a standard practice for all payment platforms and only applies to your first payout. Once you have a successful payout history, future payments are processed much faster.', 1, '2025-11-01 13:21:07', '2025-11-01 13:21:07'),
(17, 'Can I speed up the fund availability process?', 'Unfortunately, the initial 7-10 day holding period is set by Stripe for security reasons and cannot be bypassed. However, you can ensure faster processing by:\n\n• Completing your Stripe Connect account verification\n• Adding your bank account details correctly\n• Ensuring your account information is accurate\n\nAfter your first successful payout, all future payments will be available within 2-3 business days.', 1, '2025-11-01 13:21:07', '2025-11-01 13:21:07'),
(18, 'What should I do if my funds are not available after the scheduled date?', 'If your funds are not available after the scheduled date:\n\n1. Check your Stripe Connect account for any verification requirements\n2. Ensure your bank account is properly linked\n3. Contact support through the app message center\n4. Provide your transaction details and the expected availability date\n\nOur support team will investigate and help resolve the issue promptly.', 1, '2025-11-01 13:21:07', '2025-11-01 13:21:07'),
(19, 'Tips for Senders and Riders', '\r\nAlways check the identity of the Rider and the sender. If necessary ask them to open their app for confirmation or verification. This will greatly minimise the risk for imposters. ', 1, '2025-11-02 18:01:14', '2025-11-02 18:01:14'),
(20, 'Taxes ', 'Earnings may be taxable in your country. Kindly consult your local guidance.', 1, '2025-11-03 11:04:46', '2025-11-03 11:04:46'),
(21, 'Can I bring multiple items?', 'Yes, within your airline’s baggage allowance and our policy. The app shows total volume and earnings.', 1, '2025-11-03 11:08:16', '2025-11-03 11:08:16'),
(22, 'What if the item or items are heavier than promised? ', 'You control acceptance. If details change, you can decline or renegotiate in chat before meeting.', 1, '2025-11-03 11:11:10', '2025-11-03 11:11:10'),
(23, 'Do I need to meet senders at their addresses?', 'You can always talk to senders and ask them if you can meet in public spaces.  The app has an in massaging system. ', 1, '2025-11-03 11:15:51', '2025-11-03 11:15:51'),
(24, 'What if my flight gets delayed.', 'If your flight gets delayed you can massage the sender. ', 1, '2025-11-03 11:17:28', '2025-11-03 11:17:28'),
(25, 'How can I send a package?', 'To send a package, simply register your request on the sender’s app, specifying the package details, destination, and desired time frame. Travellers and freelancers can view your request and respond if interested.', 1, '2025-11-03 11:39:19', '2025-11-03 11:39:19'),
(26, 'Why is no one taking my package/items?', '\r\nIt can be due to the fact that your offer is low, your location and destination of the package/items.', 1, '2025-11-03 12:24:25', '2025-11-03 12:24:25'),
(27, 'Why is my payment delayed?', 'First time payment from stripes might take time because stripes is connecting the system to work smoothly. After that everything will move faster. ', 1, '2025-11-03 21:37:07', '2025-11-03 21:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `landing_pages`
--

CREATE TABLE `landing_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `topbar_logo` varchar(255) DEFAULT NULL,
  `topbar_menu_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `social_media_menu_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `topbar_telephone` varchar(255) DEFAULT NULL,
  `topbar_email` varchar(255) DEFAULT NULL,
  `mega_menu_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_top_hero_title` varchar(255) DEFAULT NULL,
  `home_top_hero_text` varchar(255) DEFAULT NULL,
  `home_top_hero_image` varchar(255) DEFAULT NULL,
  `home_top_hero_video_url` varchar(255) DEFAULT NULL,
  `home_exploring_title` varchar(255) DEFAULT NULL,
  `home_exploring_text` text DEFAULT NULL,
  `home_exploring_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_statistics_total_haors` varchar(255) DEFAULT NULL,
  `home_statistics_total_area` varchar(255) DEFAULT NULL,
  `home_statistics_total_projects` varchar(255) DEFAULT NULL,
  `home_featured_haors_title` varchar(255) DEFAULT NULL,
  `home_featured_haors_sub_title` varchar(255) DEFAULT NULL,
  `home_featured_haors_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_featured_haors_view_all_url` varchar(255) DEFAULT NULL,
  `home_haor_map_title` varchar(255) DEFAULT NULL,
  `home_haor_map_text` text DEFAULT NULL,
  `home_haor_map_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_conservation_effects_title` varchar(255) DEFAULT NULL,
  `home_conservation_effects_text` text DEFAULT NULL,
  `home_conservation_effects_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_summary_report_title` varchar(255) DEFAULT NULL,
  `home_summary_report_sub_title` varchar(255) DEFAULT NULL,
  `home_summary_report_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_summary_report_view_all_url` varchar(255) DEFAULT NULL,
  `home_recreation_tourism_title` varchar(255) DEFAULT NULL,
  `home_recreation_tourism_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `home_gallery_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `statistics_page_title` varchar(255) DEFAULT NULL,
  `statistics_page_header_image` varchar(255) DEFAULT NULL,
  `statistics_page_overview` varchar(255) DEFAULT NULL,
  `statistics_page_content` text DEFAULT NULL,
  `statistics_page_right_content` text DEFAULT NULL,
  `travel_page_title` varchar(255) DEFAULT NULL,
  `travel_page_header_image` varchar(255) DEFAULT NULL,
  `travel_page_how_to_go_content` text DEFAULT NULL,
  `travel_page_how_to_go_image` varchar(255) DEFAULT NULL,
  `travel_page_where_to_stay_content` text DEFAULT NULL,
  `travel_page_where_to_stay_image` varchar(255) DEFAULT NULL,
  `resort_page_title` varchar(255) DEFAULT NULL,
  `resort_page_header_image` varchar(255) DEFAULT NULL,
  `resort_page_hotel_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `bird_page_title` varchar(255) DEFAULT NULL,
  `bird_page_header_image` varchar(255) DEFAULT NULL,
  `bird_page_overview` varchar(255) DEFAULT NULL,
  `bird_page_content` text DEFAULT NULL,
  `fish_page_title` varchar(255) DEFAULT NULL,
  `fish_page_header_image` varchar(255) DEFAULT NULL,
  `fish_page_overview` varchar(255) DEFAULT NULL,
  `fish_page_content` text DEFAULT NULL,
  `cookie_policy_title` varchar(255) DEFAULT NULL,
  `cookie_policy_content` text DEFAULT NULL,
  `privacy_policy_title` varchar(255) DEFAULT NULL,
  `privacy_policy_content` text DEFAULT NULL,
  `terms_conditions_title` varchar(255) DEFAULT NULL,
  `terms_conditions_content` text DEFAULT NULL,
  `footer_logo` varchar(255) DEFAULT NULL,
  `footer_text` varchar(255) DEFAULT NULL,
  `footer_contact_address` varchar(255) DEFAULT NULL,
  `footer_link_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `footer_link_items_section2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `footer_copyright_text` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(14, 16, 17, 'I receive the package', 1, '2025-10-26 17:11:11', '2025-10-26 17:12:03'),
(15, 17, 16, 'Very good!', 1, '2025-10-26 17:12:08', '2025-10-26 17:14:12'),
(16, 16, 17, 'I have delivered the package', 1, '2025-11-06 08:38:58', '2025-11-06 08:39:08'),
(17, 17, 16, 'Well done', 1, '2025-11-06 08:39:13', '2025-11-06 08:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(62, '2014_10_12_000000_create_users_table', 1),
(63, '2014_10_12_100000_create_password_resets_table', 1),
(64, '2019_08_19_000000_create_failed_jobs_table', 1),
(65, '2021_04_12_153329_create_auth_logs_table', 1),
(66, '2021_12_02_054840_create_landing_pages_table', 1),
(67, '2023_11_02_115758_create_pages_table', 1),
(68, '2024_03_21_000000_add_fields_to_users_table', 1),
(70, '2018_12_12_075319_create_permission_tables', 2),
(71, '2025_05_21_091908_create_packages_table', 3),
(72, '2025_05_24_101113_create_orders_table', 4),
(73, '2025_05_24_123424_create_reviews_table', 5),
(74, '2025_05_24_135031_create_faqs_table', 6),
(75, '2019_12_14_000001_create_personal_access_tokens_table', 7),
(76, '2024_03_26_add_status_to_packages_table', 8),
(77, '2024_03_21_create_messages_table', 9),
(78, '2024_03_21_create_notifications_table', 9),
(79, '2025_06_14_085925_add_facebook_id_to_users_table', 10),
(80, '2024_03_19_create_data_deletion_requests_table', 11),
(81, '2024_03_15_000000_make_email_nullable_in_users_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(5, 'App\\User', 1),
(6, 'App\\User', 14),
(6, 'App\\User', 17),
(6, 'App\\User', 18),
(7, 'App\\User', 15),
(7, 'App\\User', 16),
(7, 'App\\User', 19);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `type` varchar(255) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `description`, `is_read`, `type`, `data`, `created_at`, `updated_at`, `deleted_at`) VALUES
(51, 16, 'New Package Available', 'Package nearby - $5.00 (Distance: 0.32km)', 1, 'package_available', '{\"package_id\":33,\"price\":\"5.00\",\"pickup_distance\":0.320000000000000006661338147750939242541790008544921875,\"dropoff_distance\":null,\"pickup_address\":\"Godv\\u00e4dersgatan 59, Godv\\u00e4dersgatan, G\\u00f6teborg, V\\u00e4stra G\\u00f6taland County, 418 38, Sweden\",\"dropoff_address\":\"7471 Keith Rd, Keith Rd, Warrenton, VA, 20186, United States\"}', '2025-11-06 08:37:48', '2025-11-06 08:41:40', '2025-11-06 08:41:40'),
(82, 18, 'Delivery Status Update', 'Your order is now completed. Accept delivery!', 1, 'delivery_status', '{\"delivery_id\":33,\"status\":\"completed\",\"estimated_time\":\"2025-11-08T17:18:56.951417Z\"}', '2025-11-08 17:18:56', '2025-11-08 18:49:50', '2025-11-08 18:49:50'),
(83, 19, 'New Package Available', 'Package nearby - $45.00 (Distance: 0.22km)', 0, 'package_available', '{\"package_id\":49,\"price\":\"45.00\",\"pickup_distance\":0.2200000000000000011102230246251565404236316680908203125,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Balughat Road, Dhaka, Bangladesh\"}', '2025-11-08 17:28:46', '2025-11-08 17:28:46', NULL),
(84, 19, 'New Package Available', 'Package nearby - $50.00 (Distance: 0.22km)', 0, 'package_available', '{\"package_id\":50,\"price\":\"50.00\",\"pickup_distance\":0.2200000000000000011102230246251565404236316680908203125,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Balughat Road, Dhaka, Bangladesh\"}', '2025-11-08 17:28:46', '2025-11-08 17:28:46', NULL),
(85, 19, 'New Package Available', 'Package nearby - $55.00 (Distance: 0.22km)', 0, 'package_available', '{\"package_id\":51,\"price\":\"55.00\",\"pickup_distance\":0.2200000000000000011102230246251565404236316680908203125,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Balughat Road, Dhaka, Bangladesh\"}', '2025-11-08 17:28:46', '2025-11-08 17:28:46', NULL),
(86, 18, 'Delivery Status Update', 'Your order is now completed. Accept delivery!', 1, 'delivery_status', '{\"delivery_id\":34,\"status\":\"completed\",\"estimated_time\":\"2025-11-08T17:29:03.444958Z\"}', '2025-11-08 17:29:03', '2025-11-08 18:49:51', '2025-11-08 18:49:51'),
(87, 18, 'Delivery Status Update', 'Your order is now completed. Accept delivery!', 1, 'delivery_status', '{\"delivery_id\":35,\"status\":\"completed\",\"estimated_time\":\"2025-11-08T17:29:20.204400Z\"}', '2025-11-08 17:29:20', '2025-11-08 18:49:52', '2025-11-08 18:49:52'),
(88, 18, 'Delivery Status Update', 'Your order is now completed. Accept delivery!', 1, 'delivery_status', '{\"delivery_id\":36,\"status\":\"completed\",\"estimated_time\":\"2025-11-08T17:29:35.501290Z\"}', '2025-11-08 17:29:35', '2025-11-08 18:19:44', NULL),
(89, 19, 'New Package Available', 'Package nearby - $120.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":52,\"price\":\"120.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 18:16:06', '2025-11-08 18:16:06', NULL),
(90, 19, 'New Package Available', 'Package nearby - $125.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":53,\"price\":\"125.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 18:16:06', '2025-11-08 18:16:06', NULL),
(91, 19, 'New Package Available', 'Package nearby - $130.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":54,\"price\":\"130.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 18:16:06', '2025-11-08 18:16:06', NULL),
(92, 19, 'New Package Available', 'Package nearby - $135.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":55,\"price\":\"135.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 18:16:06', '2025-11-08 18:16:06', NULL),
(93, 19, 'New Package Available', 'Package nearby - $140.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":56,\"price\":\"140.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 18:16:06', '2025-11-08 18:16:06', NULL),
(94, 18, 'Package Picked Up', 'Your package has been picked up by the rider.', 1, 'pickup_status', '{\"delivery_id\":37,\"pickup_time\":\"2025-11-08T18:48:25.673528Z\"}', '2025-11-08 18:48:25', '2025-11-08 18:50:07', '2025-11-08 18:50:07'),
(95, 18, 'Delivery Status Update', 'Your order is now completed. Accept delivery!', 1, 'delivery_status', '{\"delivery_id\":37,\"status\":\"completed\",\"estimated_time\":\"2025-11-08T18:50:43.327511Z\"}', '2025-11-08 18:50:43', '2025-11-08 18:51:06', NULL),
(96, 16, 'New Package Available', 'Package nearby - $125.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":53,\"price\":\"125.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 19:18:42', '2025-11-08 19:18:42', NULL),
(97, 16, 'New Package Available', 'Package nearby - $130.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":54,\"price\":\"130.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 19:18:42', '2025-11-08 19:18:42', NULL),
(98, 16, 'New Package Available', 'Package nearby - $135.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":55,\"price\":\"135.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 19:18:42', '2025-11-08 19:18:42', NULL),
(99, 16, 'New Package Available', 'Package nearby - $140.00 (Distance: 0.56km)', 0, 'package_available', '{\"package_id\":56,\"price\":\"140.00\",\"pickup_distance\":0.560000000000000053290705182007513940334320068359375,\"dropoff_distance\":null,\"pickup_address\":\"Dhaka, Bangladesh\",\"dropoff_address\":\"Dhaka, Bangladesh\"}', '2025-11-08 19:18:42', '2025-11-08 19:18:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `dropper_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('ongoing','active','canceled','completed') NOT NULL DEFAULT 'ongoing',
  `delivery_status` int(11) NOT NULL DEFAULT 0,
  `pickup_status` tinyint(4) NOT NULL DEFAULT 0,
  `delivery_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `package_id`, `dropper_id`, `status`, `delivery_status`, `pickup_status`, `delivery_date`, `created_at`, `updated_at`) VALUES
(20, 33, 16, 'completed', 1, 0, '2025-11-06 08:38:41', '2025-11-06 08:38:19', '2025-11-06 08:41:13'),
(33, 48, 19, 'completed', 1, 0, '2025-11-08 17:18:56', '2025-11-08 17:18:50', '2025-11-08 17:19:11'),
(34, 49, 19, 'completed', 1, 0, '2025-11-08 17:29:03', '2025-11-08 17:28:56', '2025-11-08 17:29:56'),
(35, 50, 19, 'completed', 1, 0, '2025-11-08 17:29:20', '2025-11-08 17:29:10', '2025-11-08 17:29:50'),
(36, 51, 19, 'completed', 1, 0, '2025-11-08 17:29:35', '2025-11-08 17:29:29', '2025-11-08 17:29:44'),
(37, 52, 19, 'active', 1, 1, '2025-11-08 18:50:43', '2025-11-08 18:16:18', '2025-11-08 18:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pickup_name` varchar(255) NOT NULL,
  `pickup_mobile` varchar(255) NOT NULL,
  `pickup_address` varchar(255) NOT NULL,
  `pickup_details` text DEFAULT NULL,
  `weight` double(8,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','inactive','delivered') NOT NULL DEFAULT 'active',
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `pickup_image` varchar(100) DEFAULT NULL,
  `drop_name` varchar(255) NOT NULL,
  `drop_mobile` varchar(255) NOT NULL,
  `drop_address` varchar(255) NOT NULL,
  `drop_details` text DEFAULT NULL,
  `pickup_lat` decimal(10,7) DEFAULT NULL,
  `pickup_lng` decimal(10,7) DEFAULT NULL,
  `drop_lat` decimal(10,7) DEFAULT NULL,
  `drop_lng` decimal(10,7) DEFAULT NULL,
  `pickup_address2` varchar(255) DEFAULT NULL,
  `pickup_address3` varchar(255) DEFAULT NULL,
  `drop_address2` varchar(255) DEFAULT NULL,
  `drop_address3` varchar(255) DEFAULT NULL,
  `pickup_lat2` decimal(10,7) DEFAULT NULL,
  `pickup_lng2` decimal(10,7) DEFAULT NULL,
  `pickup_lat3` decimal(10,7) DEFAULT NULL,
  `pickup_lng3` decimal(10,7) DEFAULT NULL,
  `drop_lat2` decimal(10,7) DEFAULT NULL,
  `drop_lng2` decimal(10,7) DEFAULT NULL,
  `drop_lat3` decimal(10,7) DEFAULT NULL,
  `drop_lng3` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `sender_id`, `pickup_name`, `pickup_mobile`, `pickup_address`, `pickup_details`, `weight`, `price`, `status`, `pickup_date`, `pickup_time`, `pickup_image`, `drop_name`, `drop_mobile`, `drop_address`, `drop_details`, `pickup_lat`, `pickup_lng`, `drop_lat`, `drop_lng`, `pickup_address2`, `pickup_address3`, `drop_address2`, `drop_address3`, `pickup_lat2`, `pickup_lng2`, `pickup_lat3`, `pickup_lng3`, `drop_lat2`, `drop_lng2`, `drop_lat3`, `drop_lng3`, `created_at`, `updated_at`) VALUES
(33, 17, 'King Fonjah', '+46700671992', 'Godvädersgatan 59, Godvädersgatan, Göteborg, Västra Götaland County, 418 38, Sweden', 'Come 12:00', 21.00, 5.00, 'delivered', '2025-11-06', '00:00:00', 'uploads/packages/48adb135bcb0820afe65b4a61f0571da.jpeg', 'Kenny Adeshigbin', '+14048052063', '7471 Keith Rd, Keith Rd, Warrenton, VA, 20186, United States', 'Call in the morning', 57.7242515, 11.8901165, 38.7505177, -77.8214442, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-06 08:37:22', '2025-11-06 08:41:13'),
(48, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 12.00, 40.00, 'delivered', '2025-11-11', '23:15:00', NULL, 'Kon', '+12345678900', 'Balughat Road, Dhaka, Bangladesh', NULL, 23.8179408, 90.3943429, 23.8279224, 90.3934314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 17:16:54', '2025-11-08 17:19:11'),
(49, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 12.00, 45.00, 'delivered', '2025-11-11', '23:15:00', NULL, 'Kon', '+12345678900', 'Balughat Road, Dhaka, Bangladesh', NULL, 23.8179408, 90.3943429, 23.8279224, 90.3934314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 17:28:01', '2025-11-08 17:29:56'),
(50, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 12.00, 50.00, 'delivered', '2025-11-11', '23:15:00', NULL, 'Kon', '+12345678900', 'Balughat Road, Dhaka, Bangladesh', NULL, 23.8179408, 90.3943429, 23.8279224, 90.3934314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 17:28:21', '2025-11-08 17:29:50'),
(51, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 12.00, 55.00, 'delivered', '2025-11-11', '23:15:00', NULL, 'Kon', '+12345678900', 'Balughat Road, Dhaka, Bangladesh', NULL, 23.8179408, 90.3943429, 23.8279224, 90.3934314, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 17:28:38', '2025-11-08 17:29:44'),
(52, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 11.00, 120.00, 'active', '2025-11-12', '00:12:00', NULL, 'Kon', '+12345678900', 'Dhaka, Bangladesh', NULL, 23.8132195, 90.3913525, 23.8247765, 90.3964146, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:12:54', '2025-11-08 18:12:54'),
(53, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 11.00, 125.00, 'active', '2025-11-12', '00:12:00', NULL, 'Kon', '+12345678900', 'Dhaka, Bangladesh', NULL, 23.8132195, 90.3913525, 23.8247765, 90.3964146, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:13:16', '2025-11-08 18:13:16'),
(54, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 11.00, 130.00, 'active', '2025-11-12', '00:12:00', NULL, 'Kon', '+12345678900', 'Dhaka, Bangladesh', NULL, 23.8132195, 90.3913525, 23.8247765, 90.3964146, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:13:35', '2025-11-08 18:13:35'),
(55, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 11.00, 135.00, 'active', '2025-11-12', '00:12:00', NULL, 'Kon', '+12345678900', 'Dhaka, Bangladesh', NULL, 23.8132195, 90.3913525, 23.8247765, 90.3964146, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:13:51', '2025-11-08 18:13:51'),
(56, 18, 'John Doe', '+12345678900', 'Dhaka, Bangladesh', NULL, 11.00, 140.00, 'active', '2025-11-12', '00:12:00', NULL, 'Kon', '+12345678900', 'Dhaka, Bangladesh', NULL, 23.8132195, 90.3913525, 23.8247765, 90.3964146, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-08 18:14:12', '2025-11-08 18:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `url_title` varchar(255) DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('admin@piqdrop.com', '$2y$10$2MksTqd7ZUJ.NEWeHcNfUuuGwAAFJVu9m9F6T3qkDhHyJyTi5fZmC', '2025-11-01 18:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `stripe_payment_intent_id` varchar(255) NOT NULL,
  `stripe_payment_method_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'usd',
  `status` enum('pending','processing','succeeded','failed','canceled') NOT NULL DEFAULT 'pending',
  `payment_type` enum('escrow','release','refund','withdrawal','commission') NOT NULL DEFAULT 'escrow',
  `refund_reason` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `available_on` timestamp NULL DEFAULT NULL,
  `stripe_fee` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `package_id`, `user_id`, `stripe_payment_intent_id`, `stripe_payment_method_id`, `amount`, `currency`, `status`, `payment_type`, `refund_reason`, `processed_at`, `available_on`, `stripe_fee`, `created_at`, `updated_at`) VALUES
(77, 33, 17, 'pi_3SQOfzB0tPiQuh3C0MYwNGam', NULL, 5.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-06 08:37:21', '2025-11-11 00:00:00', NULL, '2025-11-06 08:36:15', '2025-11-06 08:37:22'),
(78, 33, 16, 'release_pi_3SQOfzB0tPiQuh3C0MYwNGam', NULL, 4.50, 'usd', 'succeeded', 'release', NULL, '2025-11-06 08:41:13', '2025-11-11 00:00:00', 0.36, '2025-11-06 08:41:13', '2025-11-06 08:41:13'),
(79, 33, 1, 'commission_pi_3SQOfzB0tPiQuh3C0MYwNGam', NULL, 0.50, 'usd', 'succeeded', 'commission', NULL, '2025-11-06 08:41:13', NULL, NULL, '2025-11-06 08:41:13', '2025-11-06 08:41:13'),
(116, 48, 18, 'pi_3SRFkjBQsHpfCUCm1QF607Iq', NULL, 40.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 17:16:52', '2025-11-15 00:00:00', 1.60, '2025-11-08 17:16:41', '2025-11-08 17:16:54'),
(117, 48, 19, 'release_pi_3SRFkjBQsHpfCUCm1QF607Iq', NULL, 36.00, 'usd', 'succeeded', 'release', NULL, '2025-11-08 17:19:13', '2025-11-15 00:00:00', 1.60, '2025-11-08 17:19:13', '2025-11-08 17:19:13'),
(118, 48, 1, 'commission_pi_3SRFkjBQsHpfCUCm1QF607Iq', NULL, 4.00, 'usd', 'succeeded', 'commission', NULL, '2025-11-08 17:19:13', NULL, NULL, '2025-11-08 17:19:13', '2025-11-08 17:19:13'),
(119, NULL, 18, 'pi_3SRFvUBQsHpfCUCm1ssLga8B', NULL, 45.00, 'usd', '', 'escrow', NULL, NULL, NULL, NULL, '2025-11-08 17:27:48', '2025-11-08 17:27:48'),
(120, 49, 18, 'pi_3SRFvWBQsHpfCUCm2b3cfDfA', NULL, 45.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 17:27:58', '2025-11-15 00:00:00', 1.76, '2025-11-08 17:27:50', '2025-11-08 17:29:58'),
(121, 50, 18, 'pi_3SRFvpBQsHpfCUCm1xRFZ6M6', NULL, 50.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 17:28:19', '2025-11-15 00:00:00', 1.93, '2025-11-08 17:28:10', '2025-11-08 17:29:51'),
(122, 51, 18, 'pi_3SRFw7BQsHpfCUCm0VOTw5wb', NULL, 55.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 17:28:36', '2025-11-15 00:00:00', 2.09, '2025-11-08 17:28:27', '2025-11-08 17:28:38'),
(123, 51, 19, 'release_pi_3SRFw7BQsHpfCUCm0VOTw5wb', NULL, 49.50, 'usd', 'succeeded', 'release', NULL, '2025-11-08 17:29:45', '2025-11-08 00:00:00', 2.09, '2025-11-08 17:29:45', '2025-11-08 17:29:45'),
(124, 51, 1, 'commission_pi_3SRFw7BQsHpfCUCm0VOTw5wb', NULL, 5.50, 'usd', 'succeeded', 'commission', NULL, '2025-11-08 17:29:45', NULL, NULL, '2025-11-08 17:29:45', '2025-11-08 17:29:45'),
(125, 50, 19, 'release_pi_3SRFvpBQsHpfCUCm1xRFZ6M6', NULL, 45.00, 'usd', 'succeeded', 'release', NULL, '2025-11-08 17:29:51', '2025-11-15 00:00:00', 1.93, '2025-11-08 17:29:51', '2025-11-08 17:29:51'),
(126, 50, 1, 'commission_pi_3SRFvpBQsHpfCUCm1xRFZ6M6', NULL, 5.00, 'usd', 'succeeded', 'commission', NULL, '2025-11-08 17:29:51', NULL, NULL, '2025-11-08 17:29:51', '2025-11-08 17:29:51'),
(127, 49, 19, 'release_pi_3SRFvWBQsHpfCUCm2b3cfDfA', NULL, 40.50, 'usd', 'succeeded', 'release', NULL, '2025-11-08 17:29:58', '2025-11-08 00:00:00', 1.76, '2025-11-08 17:29:58', '2025-11-08 17:29:58'),
(128, 49, 1, 'commission_pi_3SRFvWBQsHpfCUCm2b3cfDfA', NULL, 4.50, 'usd', 'succeeded', 'commission', NULL, '2025-11-08 17:29:58', NULL, NULL, '2025-11-08 17:29:58', '2025-11-08 17:29:58'),
(129, NULL, 18, 'pi_3SRGZtBQsHpfCUCm2QnMCcaq', NULL, 50.00, 'usd', '', 'escrow', NULL, NULL, NULL, NULL, '2025-11-08 18:09:33', '2025-11-08 18:09:33'),
(130, 52, 18, 'pi_3SRGcvBQsHpfCUCm2PfR5mXC', NULL, 120.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 18:12:51', NULL, NULL, '2025-11-08 18:12:41', '2025-11-08 18:12:54'),
(131, 53, 18, 'pi_3SRGdIBQsHpfCUCm2lDg14UK', NULL, 125.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 18:13:13', NULL, NULL, '2025-11-08 18:13:04', '2025-11-08 18:13:16'),
(132, 54, 18, 'pi_3SRGdbBQsHpfCUCm11vauCKp', NULL, 130.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 18:13:32', NULL, NULL, '2025-11-08 18:13:23', '2025-11-08 18:13:35'),
(133, 55, 18, 'pi_3SRGdsBQsHpfCUCm0ECYnWkj', NULL, 135.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 18:13:49', NULL, NULL, '2025-11-08 18:13:40', '2025-11-08 18:13:51'),
(134, 56, 18, 'pi_3SRGeDBQsHpfCUCm0DqDNjKo', NULL, 140.00, 'usd', 'succeeded', 'escrow', NULL, '2025-11-08 18:14:09', NULL, NULL, '2025-11-08 18:14:01', '2025-11-08 18:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\User', 2, 'auth_token', '751e147bfbc2ed7ef511f3177c1ef061cda7e8aac61f9c53f0e8b2dd01203a3d', '[\"*\"]', '2025-10-08 05:17:05', '2025-10-08 05:16:26', '2025-10-08 05:17:05'),
(2, 'App\\User', 3, 'auth_token', '7ca8b015b46d0c99f491f49d8f10bc0ff1bffa5785b85646aac10965e874ee77', '[\"*\"]', '2025-10-08 20:50:29', '2025-10-08 20:31:48', '2025-10-08 20:50:29'),
(3, 'App\\User', 4, 'auth_token', '57e636029b12cb884fd99eacf2822da7398d1ca20dfcfd1b932c6c52e98eed71', '[\"*\"]', '2025-10-12 05:51:46', '2025-10-08 21:06:32', '2025-10-12 05:51:46'),
(6, 'App\\User', 7, 'auth_token', '00df6e8c66ec9b3bd5f9cf8008c747dcc84856c7a66c93d0be06a490422bd030', '[\"*\"]', '2025-10-09 21:00:36', '2025-10-09 20:58:46', '2025-10-09 21:00:36'),
(8, 'App\\User', 5, 'auth_token', '9ea37e63b0527323a27dc218c5fac5a7cf661f1a990f61cf93bfdec9ff14bc45', '[\"*\"]', '2025-10-14 00:54:40', '2025-10-10 06:20:16', '2025-10-14 00:54:40'),
(13, 'App\\User', 10, 'auth_token', 'd2be9b0da55eaaaf533391c2e4778e57730e01eba6fdd204c486ce10eafb6546', '[\"*\"]', '2025-10-10 15:54:23', '2025-10-10 15:53:24', '2025-10-10 15:54:23'),
(15, 'App\\User', 10, 'auth_token', '3500ba828f26bd534cab638fff73354e90627d059a61d1590cf37943988305a6', '[\"*\"]', '2025-10-10 16:19:30', '2025-10-10 16:16:51', '2025-10-10 16:19:30'),
(16, 'App\\User', 10, 'auth_token', '39397b3607ea19b7ce2751815fdf79dc3dad93802df83b21c53263f9e81d892f', '[\"*\"]', '2025-10-10 17:09:21', '2025-10-10 17:06:40', '2025-10-10 17:09:21'),
(17, 'App\\User', 7, 'auth_token', '629ff4a05b5a0f6f9d2236d33a67217fd6d0caffa27d9e459fbb80ab032c0a05', '[\"*\"]', '2025-10-12 05:56:26', '2025-10-12 05:35:57', '2025-10-12 05:56:26'),
(18, 'App\\User', 11, 'auth_token', 'db7ea914d0497aad693667928296e4ddb9826b015229da6c16509785e0dce53a', '[\"*\"]', '2025-10-12 07:00:38', '2025-10-12 05:51:47', '2025-10-12 07:00:38'),
(19, 'App\\User', 7, 'auth_token', 'd66a7777f74c15f5adec27c3756d2f0229222532d1a56994d0505126afcc46ed', '[\"*\"]', '2025-10-12 07:10:04', '2025-10-12 06:48:47', '2025-10-12 07:10:04'),
(20, 'App\\User', 11, 'auth_token', 'c935d9ac14e05408664729bb9f34d9572e7f46e72f619be0835392dd4e163a58', '[\"*\"]', '2025-10-12 12:49:00', '2025-10-12 07:00:38', '2025-10-12 12:49:00'),
(24, 'App\\User', 11, 'auth_token', '624d960808a43cbd5c52650e7288c04b8e109a779ddfb04387116a0ad237da48', '[\"*\"]', '2025-10-12 22:11:41', '2025-10-12 12:49:01', '2025-10-12 22:11:41'),
(25, 'App\\User', 7, 'auth_token', 'dfe9d8768f633a5c03b1ca6297f267e3770bbc3bdb452ce980fb96db18ebbbed', '[\"*\"]', '2025-10-12 13:03:15', '2025-10-12 12:50:18', '2025-10-12 13:03:15'),
(27, 'App\\User', 11, 'auth_token', '4cc00d4dff3da6e6812c7296fdc2688f719b082ce405962697b28cb1a494a29d', '[\"*\"]', '2025-10-13 03:14:25', '2025-10-12 22:11:41', '2025-10-13 03:14:25'),
(28, 'App\\User', 7, 'auth_token', '589351a109bc588ca0b4230272964f40b062fc74b3b1e2c37895855bb6ef0bbc', '[\"*\"]', '2025-10-12 22:15:47', '2025-10-12 22:14:41', '2025-10-12 22:15:47'),
(30, 'App\\User', 11, 'auth_token', 'e2a19d3d865dae5f30e7365991fb2bdc50ee26d733f10c6e48815150531f1314', '[\"*\"]', '2025-10-13 06:01:39', '2025-10-13 03:14:25', '2025-10-13 06:01:39'),
(31, 'App\\User', 7, 'auth_token', '94201c971f708042456b1c1460dbdd71819470b9184153731da443a8aa29b9c4', '[\"*\"]', '2025-10-13 04:04:59', '2025-10-13 03:15:03', '2025-10-13 04:04:59'),
(32, 'App\\User', 7, 'auth_token', 'f0e7b197fbfba5f6210e15c5f7ea599ce30fc1943bd1bf7de26246cd90e7e203', '[\"*\"]', '2025-10-13 06:10:11', '2025-10-13 06:00:38', '2025-10-13 06:10:11'),
(33, 'App\\User', 11, 'auth_token', '2ad6eeda252003eca33afbfee1da94a3342d834059983aa864ee649ff0e13da5', '[\"*\"]', '2025-10-16 13:46:30', '2025-10-13 06:01:39', '2025-10-16 13:46:30'),
(34, 'App\\User', 5, 'auth_token', 'cfedcd668db74ff726edf4f238f892c7a013eb486444bef86382746ea4fb184e', '[\"*\"]', '2025-10-16 07:42:28', '2025-10-14 00:54:40', '2025-10-16 07:42:28'),
(43, 'App\\User', 7, 'auth_token', '39d6925724be00c28414204b29c62ca18db2df8e363fb8f104aedb66dcaa2988', '[\"*\"]', '2025-10-16 14:03:35', '2025-10-16 13:40:59', '2025-10-16 14:03:35'),
(44, 'App\\User', 11, 'auth_token', '6795e6cca26a66ecd74cf6c3ff0a5919f733e9dfcf2c613c3c0a48d465bf7be8', '[\"*\"]', '2025-10-16 14:39:09', '2025-10-16 13:46:30', '2025-10-16 14:39:09'),
(45, 'App\\User', 7, 'auth_token', '93af2177dcea09d69962f052b1fc0a96643c1b14930f4169079e246e3ea414f8', '[\"*\"]', '2025-10-16 14:13:38', '2025-10-16 14:13:33', '2025-10-16 14:13:38'),
(46, 'App\\User', 7, 'auth_token', 'afa962c14fe50ea0b8637db6552cf8896f2c48ba00b2dfec7d2ce77f8a248ec1', '[\"*\"]', '2025-10-16 14:43:27', '2025-10-16 14:35:59', '2025-10-16 14:43:27'),
(47, 'App\\User', 11, 'auth_token', 'd939ae8d854b03ff9c49193c0de2b0c86bb397d0f228259bfe3b19af594769ab', '[\"*\"]', '2025-10-17 04:57:42', '2025-10-16 14:39:09', '2025-10-17 04:57:42'),
(51, 'App\\User', 11, 'auth_token', '4eb7050b3928ee7a7e380dbfdda7b72185f055b7fe1e60252dabd74e0a3e63a7', '[\"*\"]', '2025-10-17 07:09:35', '2025-10-17 04:57:42', '2025-10-17 07:09:35'),
(52, 'App\\User', 7, 'auth_token', 'db1f26165fa69d5ed0d0ed3ec725d471ad8b3d910a9f1646db9b28da5b2ae20d', '[\"*\"]', '2025-10-17 07:13:23', '2025-10-17 04:59:00', '2025-10-17 07:13:23'),
(54, 'App\\User', 11, 'auth_token', '5104666af8639f54513b9241b4004d755bdb37479997f1d91b72631c05c11c39', '[\"*\"]', '2025-10-18 07:46:44', '2025-10-17 07:09:35', '2025-10-18 07:46:44'),
(55, 'App\\User', 7, 'auth_token', 'ab1ac26291e20e5a3859af027167c08357bd4c8ad1884617fc86e323dc471508', '[\"*\"]', '2025-10-17 07:46:34', '2025-10-17 07:46:32', '2025-10-17 07:46:34'),
(56, 'App\\User', 11, 'auth_token', 'a59e1e115970adc385c2c205c22121195fac94bbe7734df7d75e06baea7474be', '[\"*\"]', '2025-10-20 01:33:28', '2025-10-18 07:46:45', '2025-10-20 01:33:28'),
(57, 'App\\User', 11, 'auth_token', '326913c5a6460af86847655de85b873710a92aabf18611b35c77ac8009f48321', '[\"*\"]', '2025-10-22 14:30:22', '2025-10-20 01:33:29', '2025-10-22 14:30:22'),
(58, 'App\\User', 7, 'auth_token', 'daf2d2e8a5944a50c0f6567dd7639ca2318348478a5527620fe8614e00acfa18', '[\"*\"]', '2025-10-20 01:40:13', '2025-10-20 01:34:45', '2025-10-20 01:40:13'),
(59, 'App\\User', 7, 'auth_token', '2245018a534ea0bc85dc381ae5afd2d32f4306ef643503a03eccd5a1712f599f', '[\"*\"]', '2025-10-20 08:20:27', '2025-10-20 08:08:06', '2025-10-20 08:20:27'),
(60, 'App\\User', 7, 'auth_token', '07a16da765cbb684405b745226923e8704660d8ecef86724670a814e726ca070', '[\"*\"]', '2025-10-21 00:45:07', '2025-10-21 00:35:04', '2025-10-21 00:45:07'),
(63, 'App\\User', 12, 'auth_token', '1f85b7bde0155980ec6f0de90d19fc79b53f34f9ed7fba78e0a9db054e5c3f34', '[\"*\"]', '2025-10-22 05:33:38', '2025-10-21 15:37:45', '2025-10-22 05:33:38'),
(64, 'App\\User', 13, 'auth_token', 'aaa9b7937bd049a20d4bfc8087525900e71b73b19afe6a5b9fd7b1941775701e', '[\"*\"]', '2025-10-21 16:21:11', '2025-10-21 15:47:51', '2025-10-21 16:21:11'),
(65, 'App\\User', 7, 'auth_token', 'cf9b1c632f4e49159a85bee685efe577eaee8ec2373e169c6644b64064a447af', '[\"*\"]', '2025-10-22 14:28:04', '2025-10-22 14:28:02', '2025-10-22 14:28:04'),
(66, 'App\\User', 11, 'auth_token', '9731914acfbcf097f148e81f292c424a5cad4b16b1c363ed3a4f244007e241ce', '[\"*\"]', '2025-10-24 09:17:45', '2025-10-22 14:30:22', '2025-10-24 09:17:45'),
(67, 'App\\User', 7, 'auth_token', 'fd85900300703bee69ed19739921878770fea1cc5308310ccd6e9d250dc75e1d', '[\"*\"]', '2025-10-22 23:07:01', '2025-10-22 22:58:46', '2025-10-22 23:07:01'),
(71, 'App\\User', 17, 'auth_token', '4bd46834ba79bac743544434777b29799b54c85a3262061d4b3fb73bc05fca70', '[\"*\"]', '2025-10-26 16:48:41', '2025-10-26 16:18:42', '2025-10-26 16:48:41'),
(73, 'App\\User', 17, 'auth_token', '92fe80ae4e4eda8db98d8d997cccd649007524f423687267566ad2c8762e5843', '[\"*\"]', '2025-10-26 17:16:10', '2025-10-26 17:12:00', '2025-10-26 17:16:10'),
(75, 'App\\User', 17, 'auth_token', 'db70436d0733c40af7ec68f03612ab75df3701625ef371cb301247ce71f8b647', '[\"*\"]', '2025-10-26 23:42:45', '2025-10-26 23:33:28', '2025-10-26 23:42:45'),
(77, 'App\\User', 17, 'auth_token', '4647213804e5cf3fcd5353f1ee92f90fc95b83291d572f646d7a58a581454255', '[\"*\"]', '2025-10-26 23:56:15', '2025-10-26 23:47:54', '2025-10-26 23:56:15'),
(84, 'App\\User', 17, 'auth_token', '9d65ca45fe4f5599245a47e8bb15cfd58a60fb174b05a7e2d1e28975db901ec2', '[\"*\"]', '2025-10-29 11:45:40', '2025-10-29 11:44:43', '2025-10-29 11:45:40'),
(90, 'App\\User', 17, 'auth_token', '2417692a230d81e144806af71af2041fc152e655bbc101a97fde6621c6d247c7', '[\"*\"]', '2025-11-02 20:03:09', '2025-11-02 20:02:04', '2025-11-02 20:03:09'),
(91, 'App\\User', 17, 'auth_token', 'bfc4542ae4243eecd961cb08c505c21a6fceec687caa5de65dd273a50240a9a9', '[\"*\"]', '2025-11-02 21:15:44', '2025-11-02 21:01:59', '2025-11-02 21:15:44'),
(95, 'App\\User', 17, 'auth_token', 'd96ff9a4bbb0daf6d01fc35c1d681ecbaecbccf4cb3766c299d29513f678d695', '[\"*\"]', '2025-11-05 14:27:03', '2025-11-05 14:13:35', '2025-11-05 14:27:03'),
(97, 'App\\User', 17, 'auth_token', '95ddf3aa11f76867716816098b84a12fbf1916f6b6e0dae287126b7a6d35b014', '[\"*\"]', '2025-11-06 08:41:21', '2025-11-06 08:32:51', '2025-11-06 08:41:21'),
(106, 'App\\User', 19, 'auth_token', 'f3ccb39414d7cc3d692781cc4d51d78907a2b13f16be403a1b7bd9acaea59812', '[\"*\"]', '2025-11-08 18:51:05', '2025-11-08 17:44:22', '2025-11-08 18:51:05'),
(109, 'App\\User', 18, 'auth_token', '68e79961fd5ff1a18eef31025658864da9beade862bfb8b6580d698accfb8874', '[\"*\"]', '2025-11-08 19:04:51', '2025-11-08 18:19:40', '2025-11-08 19:04:51'),
(110, 'App\\User', 16, 'auth_token', 'ccf8960521676a5c1b85b4a1ff22979af79988a3472d5a9ef0b893c2b962b516', '[\"*\"]', '2025-11-08 19:25:13', '2025-11-08 19:18:41', '2025-11-08 19:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `reviewee_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(5, 'admin', 'api', '2025-10-26 08:08:22', '2025-10-26 08:08:22'),
(6, 'sender', 'api', '2025-10-26 08:08:38', '2025-10-26 08:08:38'),
(7, 'dropper', 'api', '2025-10-26 08:08:49', '2025-10-26 08:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `stripe_account_id` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `date_of_birth` varchar(20) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(32) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL,
  `document` varchar(100) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `otp` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `facebook_id`, `stripe_account_id`, `first_name`, `last_name`, `mobile`, `address`, `latitude`, `longitude`, `date_of_birth`, `gender`, `nationality`, `image`, `document`, `status`, `email`, `email_verified_at`, `password`, `remember_token`, `otp`, `is_verified`, `otp_expires_at`, `settings`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, 'Super', 'Admin', '+46700671992', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'admin@piqdrop.com', NULL, '$2y$10$sNYy.EMeVwo7kwLRXjOHDOXYnVl6QFzkOluHH5hHI1r2d1Sn1Y1uu', 'AtG3JNH5VJxL4qsywrRQWKGw45rcIjCn15UshsI4gXio8PrM0Oz2ztt1BtN2', NULL, 0, NULL, NULL, NULL, '2025-08-30 17:59:12'),
(14, NULL, NULL, 'Demo', 'Sender', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/images/a10d52c71957ff94bcf32c17baa9753d.png', 'uploads/documents/d19917e689507ee03f12c66d0d9cd778.png', 'active', 'demo@piqdrop.com', NULL, '$2y$10$KgpmQGjkRe.KspVXJy6R7u5p7t2OUAhuigo6rTW4Ti53quyO0XTPy', NULL, NULL, 1, NULL, NULL, '2025-10-26 08:10:33', '2025-10-26 12:56:44'),
(15, NULL, 'acct_1SN9xXPkt8vpma97', 'Demo', 'Rider', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'uploads/images/00bb62b9e3b52d62cbe5356797fc2d16.png', 'uploads/documents/4de0651c3378c8bd22f03bf1792cdd03.png', 'active', 'demorider@piqdrop.com', NULL, '$2y$10$JQIq1ukz6VJnYYuXk6kf.ucJD6pCE89zCfwTP5dm0O.g.nptft5ne', NULL, NULL, 1, NULL, NULL, '2025-10-26 12:58:17', '2025-10-28 10:17:02'),
(16, NULL, 'acct_1SQXzdBs4wLunC1Y', 'Fon', 'Fonjah', NULL, NULL, NULL, NULL, NULL, 'male', 'Sweden', 'uploads/images/84210d89209dc291eab6e951d88cb0b7.jpeg', 'uploads/documents/1531aedc535ceaad7581646ec5293fa4.jpeg', 'active', 'moneysinvest@gmail.com', NULL, '$2y$10$FOHfJtOUhKyEFC.twKNcBeEB6w9L/wPSnR0MwM543NjLtCGe3CxQO', NULL, NULL, 1, NULL, NULL, '2025-10-26 16:13:32', '2025-11-06 18:33:12'),
(17, NULL, NULL, 'King', 'Fonjah', NULL, NULL, NULL, NULL, NULL, 'male', 'Sweden', 'uploads/images/2fdcd25899dcf8863ac1925149ad0e7e.jpeg', 'uploads/documents/6918c1cd1ee45dea3d6f6e0f918d467b.jpeg', 'active', 'Amahfon10@gmail.com', NULL, '$2y$10$601jS/nOirs4TyoZvAfFcO9i19vU4LM0DsnD17y0rPKpxCG2EgX1K', NULL, NULL, 1, NULL, NULL, '2025-10-26 16:18:42', '2025-10-26 16:21:24'),
(18, NULL, NULL, 'John', 'Doe', NULL, NULL, NULL, NULL, NULL, 'male', 'Bangladesh', 'uploads/images/4d76fc462a753afe1e41be1493988713.jpeg', 'uploads/documents/5ddd46ea5eeae08c1b84ac70ad2de523.jpeg', 'active', 'se@gmail.com', NULL, '$2y$10$NGzJne5B2/7iVE6ucHFWIOEoq7O5D3w8uXsBtpCIw4hdA2EhzrDYG', NULL, NULL, 1, NULL, NULL, '2025-11-01 13:52:56', '2025-11-01 13:54:02'),
(19, NULL, 'acct_1SRAdmAzlInhFpY5', 'John', 'Rider', NULL, NULL, NULL, NULL, NULL, 'male', 'Bangladesh', 'uploads/images/eb91b2609e3b67607f99497a323554b0.jpeg', 'uploads/documents/5a076321550c2c2f4141a17f709964fa.jpeg', 'active', 'de@gmail.com', NULL, '$2y$10$U04KIKblwgfHVF5ww6S5I.VqNWALZyFSaak2xMiwb2dBR8eKphvqK', NULL, NULL, 1, NULL, NULL, '2025-11-01 13:59:00', '2025-11-08 11:49:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_deletion_requests`
--
ALTER TABLE `data_deletion_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `data_deletion_requests_confirmation_code_unique` (`confirmation_code`),
  ADD KEY `data_deletion_requests_confirmation_code_status_index` (`confirmation_code`,`status`),
  ADD KEY `data_deletion_requests_facebook_user_id_index` (`facebook_user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_pages`
--
ALTER TABLE `landing_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_receiver_id_foreign` (`receiver_id`);

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
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_package_id_unique` (`package_id`),
  ADD KEY `orders_dropper_id_foreign` (`dropper_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_stripe_payment_intent_id_unique` (`stripe_payment_intent_id`),
  ADD KEY `payments_package_id_payment_type_index` (`package_id`,`payment_type`),
  ADD KEY `payments_user_id_status_index` (`user_id`,`status`),
  ADD KEY `payments_stripe_payment_intent_id_index` (`stripe_payment_intent_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`),
  ADD KEY `reviews_reviewer_id_foreign` (`reviewer_id`),
  ADD KEY `reviews_reviewee_id_foreign` (`reviewee_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_users_stripe_account_id` (`stripe_account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_deletion_requests`
--
ALTER TABLE `data_deletion_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `landing_pages`
--
ALTER TABLE `landing_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_dropper_id_foreign` FOREIGN KEY (`dropper_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_reviewee_id_foreign` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
