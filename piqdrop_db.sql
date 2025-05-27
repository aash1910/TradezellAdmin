-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 26, 2025 at 09:35 PM
-- Server version: 5.7.39
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `piqdrop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
(10, 'How do I report a bug?', 'Report bugs by contacting support or using the feedback form in the app.', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `landing_pages`
--

CREATE TABLE `landing_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `topbar_logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `topbar_menu_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `social_media_menu_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `topbar_telephone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `topbar_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mega_menu_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_top_hero_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_top_hero_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_top_hero_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_top_hero_video_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_exploring_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_exploring_text` text COLLATE utf8_unicode_ci,
  `home_exploring_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_statistics_total_haors` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_statistics_total_area` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_statistics_total_projects` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_featured_haors_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_featured_haors_sub_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_featured_haors_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_featured_haors_view_all_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_haor_map_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_haor_map_text` text COLLATE utf8_unicode_ci,
  `home_haor_map_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_conservation_effects_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_conservation_effects_text` text COLLATE utf8_unicode_ci,
  `home_conservation_effects_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_summary_report_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_summary_report_sub_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_summary_report_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_summary_report_view_all_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_recreation_tourism_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `home_recreation_tourism_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `home_gallery_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `statistics_page_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `statistics_page_header_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `statistics_page_overview` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `statistics_page_content` text COLLATE utf8_unicode_ci,
  `statistics_page_right_content` text COLLATE utf8_unicode_ci,
  `travel_page_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `travel_page_header_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `travel_page_how_to_go_content` text COLLATE utf8_unicode_ci,
  `travel_page_how_to_go_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `travel_page_where_to_stay_content` text COLLATE utf8_unicode_ci,
  `travel_page_where_to_stay_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resort_page_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resort_page_header_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resort_page_hotel_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `bird_page_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bird_page_header_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bird_page_overview` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bird_page_content` text COLLATE utf8_unicode_ci,
  `fish_page_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fish_page_header_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fish_page_overview` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fish_page_content` text COLLATE utf8_unicode_ci,
  `cookie_policy_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cookie_policy_content` text COLLATE utf8_unicode_ci,
  `privacy_policy_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `privacy_policy_content` text COLLATE utf8_unicode_ci,
  `terms_conditions_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `terms_conditions_content` text COLLATE utf8_unicode_ci,
  `footer_logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `footer_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `footer_contact_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `footer_link_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `footer_link_items_section2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `footer_copyright_text` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
(76, '2024_03_26_add_status_to_packages_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\User', 1),
(2, 'App\\User', 20),
(3, 'App\\User', 21),
(3, 'App\\User', 22);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `dropper_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('ongoing','active','canceled','completed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ongoing',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `package_id`, `dropper_id`, `status`, `created_at`, `updated_at`) VALUES
(6, 1, 21, 'ongoing', '2025-05-26 04:21:43', '2025-05-26 04:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pickup_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pickup_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pickup_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pickup_details` text COLLATE utf8_unicode_ci,
  `weight` double(8,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','inactive','delivered') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `drop_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `drop_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `drop_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `drop_details` text COLLATE utf8_unicode_ci,
  `pickup_lat` decimal(10,7) DEFAULT NULL,
  `pickup_lng` decimal(10,7) DEFAULT NULL,
  `drop_lat` decimal(10,7) DEFAULT NULL,
  `drop_lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `sender_id`, `pickup_name`, `pickup_mobile`, `pickup_address`, `pickup_details`, `weight`, `price`, `status`, `pickup_date`, `pickup_time`, `drop_name`, `drop_mobile`, `drop_address`, `drop_details`, `pickup_lat`, `pickup_lng`, `drop_lat`, `drop_lng`, `created_at`, `updated_at`) VALUES
(1, 20, 'John Doe', '+8801712501289', 'road 12, dhaka', 'call me before come', 5.50, 500.00, 'active', '2025-05-27', '22:20:00', 'Greame smith', '+9112341234123', 'road 13, delhi, india', 'come in day time', NULL, NULL, NULL, NULL, '2025-05-26 04:20:32', '2025-05-26 04:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `header_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\User', 22, 'auth_token', 'b481a18ec2d10618f4579116cbf0112552ef102292898ad4d1edebe74428e8fb', '[\"*\"]', '2025-05-26 04:30:33', '2025-05-26 04:28:55', '2025-05-26 04:30:33');

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
  `review_text` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-05-20 02:20:15', '2025-05-20 02:21:37'),
(2, 'sender', 'web', '2025-05-20 02:21:48', '2025-05-20 02:21:48'),
(3, 'dropper', 'web', '2025-05-20 02:21:56', '2025-05-20 02:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_of_birth` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nationality` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `document` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otp` text COLLATE utf8_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `mobile`, `address`, `date_of_birth`, `gender`, `nationality`, `image`, `document`, `status`, `email`, `email_verified_at`, `password`, `remember_token`, `otp`, `is_verified`, `otp_expires_at`, `created_at`, `updated_at`) VALUES
(1, 'Super', 'Admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', 'admin@piqdrop.com', NULL, '$2y$10$VJJmtpHHd26i6Y3aCybjuOJNxJxphDijUf1EUSZ1qOacelKW5jsnG', 'xRttSLXXTEwara20yv12krj9mAj1gTIEErkJfQSCrbJ0cNnnK7bdJLYwJMWe', NULL, 0, NULL, NULL, '2025-05-21 05:06:57'),
(20, 'John', 'Doe', '+4901712501289', 'road 7, north way, dhaka', '1995-06-06', 'male', 'Bangladesh', 'uploads/images/b12398f0d42e13840094af3d6a0e8028.jpeg', NULL, 'active', 'john.doe@gmail.com', NULL, '$2y$10$VKx/II68CUCCnTUwNpSgLuwj4QcV/ePDtcx5V6IOsXz79yJ6s5SYm', NULL, NULL, 1, NULL, '2025-05-26 04:14:52', '2025-05-26 04:17:45'),
(21, 'Greame', 'Smith', '01712501289', 'HNS, floor 7 , Tower 1, Police plaza', NULL, NULL, NULL, 'uploads/images/c18fcc79d87ec357e8f79789ceed1321.jpeg', NULL, 'active', 'ashraful1910@gmail.com', NULL, '$2y$10$DjBGNRBoIF6RSh6gCGFs7ue1/nS0gnZvI0dJmiw12OlfeZaiNPTz2', NULL, NULL, 1, NULL, '2025-05-26 04:16:04', '2025-05-26 04:17:35'),
(22, 'john', 'doe', NULL, NULL, NULL, 'male', 'Sweden', 'uploads/images/da3d3f191fb536d07cf2dac95fcf744a.jpeg', NULL, 'active', 'john.doe.3@gmail.com', NULL, '$2y$10$kFITj3iHpkDDp3S0upvgk.bD0V9zcIjuHYCS6IPCCmg91ymJlBcIS', NULL, NULL, 1, NULL, '2025-05-26 04:25:27', '2025-05-26 04:30:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `landing_pages`
--
ALTER TABLE `landing_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

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
