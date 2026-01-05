-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 07:51 AM
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
-- Database: `campuscare`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `faculty_id` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `access_level` enum('basic','full') DEFAULT 'basic'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_analysis_reports`
--

CREATE TABLE `ai_analysis_reports` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `keyword_scores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keyword_scores`)),
  `overall_score` int(11) DEFAULT NULL,
  `risk_level` enum('low','medium','high','critical') DEFAULT NULL,
  `suggested_actions` text DEFAULT NULL,
  `analyzed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_analysis_reports`
--

INSERT INTO `ai_analysis_reports` (`id`, `message_id`, `keyword_scores`, `overall_score`, `risk_level`, `suggested_actions`, `analyzed_at`) VALUES
(7, 7, 'null', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:00:09'),
(8, 8, 'null', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:01:06'),
(9, 9, 'null', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:01:20'),
(10, 10, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:03:34'),
(11, 11, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:03:59'),
(12, 12, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:21:38'),
(13, 13, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:23:15'),
(14, 14, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:24:25'),
(15, 15, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:35:41'),
(16, 16, '0', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:36:33'),
(17, 17, '[]', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:42:14'),
(18, 18, '[]', 0, 'low', 'Talk to someone you trust; Schedule counselling support; Avoid isolation; Seek immediate help if unsafe', '2026-01-05 04:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `alert_type` enum('mental_health','anti_ragging','proposal','system') DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `severity` enum('info','warning','danger','critical') DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `anti_ragging_committee`
--

CREATE TABLE `anti_ragging_committee` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `faculty_id` varchar(50) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department_keywords`
--

CREATE TABLE `department_keywords` (
  `id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `keywords` text NOT NULL,
  `priority` int(11) DEFAULT 1,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_keywords`
--

INSERT INTO `department_keywords` (`id`, `department`, `keywords`, `priority`, `contact_person`, `contact_email`) VALUES
(1, 'Counselling Center', 'depressed,suicidal,sad,hopeless,worthless', 1, 'Dr. Anjali Sharma', 'counselling@rcciit.org.in'),
(2, 'Academic Support', 'stress,overwhelmed,grades,failing,pressure', 2, 'Prof. Rajesh Kumar', 'academics@rcciit.org.in'),
(3, 'Anti-Ragging Committee', 'bully,harassment,threat,abuse,forced', 3, 'Prof. S. Mukherjee', 'antiragging@rcciit.org.in'),
(4, 'Health Center', 'anxiety,panic,insomnia,eating disorder', 2, 'Dr. Priya Singh', 'health@rcciit.org.in'),
(5, 'Career Guidance', 'future,career,confused,direction,aimless', 3, 'Prof. Meena Das', 'career@rcciit.org.in');

-- --------------------------------------------------------

--
-- Table structure for table `mental_wellness_messages`
--

CREATE TABLE `mental_wellness_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `keywords` text DEFAULT NULL,
  `severity_score` int(11) DEFAULT 0,
  `ai_analysis` text DEFAULT NULL,
  `department_referred` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `anonymous` enum('yes','no') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mental_wellness_messages`
--

INSERT INTO `mental_wellness_messages` (`id`, `user_id`, `message`, `keywords`, `severity_score`, `ai_analysis`, `department_referred`, `created_at`, `anonymous`) VALUES
(7, NULL, 'i am being ragged constantly but am afraid to speak up', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:00:09', 'yes'),
(8, NULL, 'i am being ragged constantly but am afraid to speak up', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:01:05', 'yes'),
(9, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:01:19', 'yes'),
(10, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:03:34', 'yes'),
(11, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:03:59', 'yes'),
(12, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:21:38', 'yes'),
(13, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:23:15', 'yes'),
(14, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:24:24', 'yes'),
(15, NULL, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:35:41', 'yes'),
(16, 16, 'i am feeling low', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:36:33', ''),
(17, 16, 'i am feeling low', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:42:14', ''),
(18, 16, 'i am being ragged', '[]', 0, '{\"overall_score\":0,\"risk_level\":\"low\",\"found_keywords\":[],\"department\":\"General Support\",\"suggested_actions\":[\"Talk to someone you trust\",\"Schedule counselling support\",\"Avoid isolation\",\"Seek immediate help if unsafe\"]}', 'General Support', '2026-01-05 04:43:33', '');

-- --------------------------------------------------------

--
-- Table structure for table `mentors`
--

CREATE TABLE `mentors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `qualification` varchar(200) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `available_slots` text DEFAULT NULL,
  `max_students` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentorship_assignments`
--

CREATE TABLE `mentorship_assignments` (
  `id` int(11) NOT NULL,
  `mentor_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` enum('active','completed','terminated') DEFAULT 'active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentor_alerts`
--

CREATE TABLE `mentor_alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `mentor_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `risk_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `negotiation`
--

CREATE TABLE `negotiation` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('buyer','seller') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `asking_price` decimal(10,2) DEFAULT NULL,
  `min_price` decimal(10,2) DEFAULT NULL,
  `max_price` decimal(10,2) DEFAULT NULL,
  `bidding_enabled` tinyint(1) DEFAULT 1,
  `Condition` varchar(50) DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('available','sold','negotiating','expired') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `buyer_name` varchar(50) NOT NULL,
  `proposed_price` decimal(10,2) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','countered','withdrawn') DEFAULT 'pending',
  `seller_response` text DEFAULT NULL,
  `counter_price` decimal(10,2) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_type` varchar(50) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `max_score` int(11) DEFAULT NULL,
  `result_category` varchar(50) DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `user_id`, `quiz_type`, `score`, `max_score`, `result_category`, `recommendations`, `created_at`) VALUES
(1, 14, 'mental_wellness', 4, 9, 'moderate', NULL, '2026-01-05 03:25:23');

-- --------------------------------------------------------

--
-- Table structure for table `signup_details`
--

CREATE TABLE `signup_details` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `joined_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signup_details`
--

INSERT INTO `signup_details` (`id`, `email`, `password`, `name`, `dob`, `gender`, `phone`, `college`, `joined_date`) VALUES
(1, 'test@rcciit.org.in', '$2y$10$i7/JT0BpWqhK3NRa5zj0euAKu2VZ9Kp9aftIuYDCRQ3/PhUGlJlle', 'Anisha Acharya', '2026-01-07', 'Female', '7892694685', 'IIT', '2026-01-05 06:20:44'),
(2, 'amit@rcciit.org.in', '$2y$10$KpXucehlr5zMJoKj/WPFzOluvh2bTKLyM88104zV0Eo6EQSFtP4q6', 'Amit', '2023-07-12', 'Male', '9645782314', 'IIT', '2026-01-05 06:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `parent_contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_sessions`
--

CREATE TABLE `support_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mentor_name` varchar(100) NOT NULL,
  `last_message` text NOT NULL,
  `status` enum('active','ended') NOT NULL DEFAULT 'active',
  `last_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wellness_checks`
--

CREATE TABLE `wellness_checks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `check_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wellness_checks`
--

INSERT INTO `wellness_checks` (`id`, `user_id`, `score`, `check_date`) VALUES
(1, 14, 4, '2026-01-05 03:25:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `ai_analysis_reports`
--
ALTER TABLE `ai_analysis_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `anti_ragging_committee`
--
ALTER TABLE `anti_ragging_committee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `department_keywords`
--
ALTER TABLE `department_keywords`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mental_wellness_messages`
--
ALTER TABLE `mental_wellness_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `mentorship_assignments`
--
ALTER TABLE `mentorship_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mentor_id` (`mentor_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `mentor_alerts`
--
ALTER TABLE `mentor_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `negotiation`
--
ALTER TABLE `negotiation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pi` (`proposal_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `signup_details`
--
ALTER TABLE `signup_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `signup_details`
--
ALTER TABLE `signup_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
