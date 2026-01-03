-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2026 at 07:33 PM
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
-- Table structure for table `marketplace`
--

CREATE TABLE `marketplace` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','sold','inactive') DEFAULT 'active',
  `views` int(11) DEFAULT 0,
  `messages` int(11) DEFAULT 0,
  `posted_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Location` varchar(255) DEFAULT NULL,
  `Condition` varchar(100) DEFAULT NULL,
  `seller_name` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketplace`
--

INSERT INTO `marketplace` (`id`, `user_id`, `title`, `description`, `price`, `image`, `status`, `views`, `messages`, `posted_date`, `Location`, `Condition`, `seller_name`, `category`) VALUES
(2, 10, 'uhh', '...............', 500.00, 'default_item.png', 'active', 0, 0, '2025-12-28 20:05:38', 'sarobar', 'good', NULL, 'books');

-- --------------------------------------------------------

--
-- Table structure for table `mental_wellness_messages`
--

CREATE TABLE `mental_wellness_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `keywords` text DEFAULT NULL,
  `severity_score` int(11) DEFAULT 0,
  `ai_analysis` text DEFAULT NULL,
  `department_referred` varchar(100) DEFAULT NULL,
  `status` enum('pending','reviewed','escalated','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `signup_details`
--

CREATE TABLE `signup_details` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'User',
  `email` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `major` varchar(100) NOT NULL DEFAULT 'Undeclared',
  `dob` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `college` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'db.png',
  `joined_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('student','mentor','admin') NOT NULL DEFAULT 'student',
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('pending','active','rejected') NOT NULL DEFAULT 'active',
  `mentor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signup_details`
--

INSERT INTO `signup_details` (`id`, `name`, `email`, `password`, `major`, `dob`, `gender`, `phone`, `college`, `profile_pic`, `joined_date`, `created_at`, `role`, `last_login`, `status`, `mentor_id`) VALUES
(10, 'debadrita', 'it2024006@rcciit.org.in', '$2y$10$1UzXG2TjPzzB35D9cQbkXuay3NK7gDax8pvL1YRG8/dmhe8kW6FTi', 'Undeclared', '2025-12-24', 'Female', '6291933912', 'RCCIIT', 'uploads/profile_pics/user_10_1767460290.jpg', '2025-12-23 20:23:15', '2025-12-23 20:23:15', 'student', '2026-01-03 17:05:40', 'active', NULL),
(11, 'JADU', 'arj@rcciit.org.in', '$2y$10$SW.iEWZ9mESZSIzO2wwZOePARCii4XPvdqpaioHVGKaI0qIJ46mH.', 'Undeclared', '1999-05-04', 'Male', '9330079483', 'RCCIIT', 'db.png', '2025-12-29 19:27:13', '2025-12-29 19:27:13', 'student', '2025-12-29 19:27:31', 'active', NULL),
(12, 'JADU', 'it2024008@rcciit.org.in', '$2y$10$tg2KQcBlhirXt8tcgqR5.upHdQU0oqD/NCCDs5jc/HGoB8KzQasJ2', 'Undeclared', '1996-01-03', 'Male', '9330079483', 'RCCIIT', 'db.png', '2026-01-03 18:18:21', '2026-01-03 18:18:21', 'student', '2026-01-03 18:18:37', 'active', NULL),
(13, 'debadrita', 'mentor@rcciit.org.in', '$2y$10$O/OGaoWLDxaaQLGMlOQHjeq/ISKwPt6TXey8uE4ffBPDelk1Xify2', 'Undeclared', '1999-01-03', 'Male', '9330079483', 'RCCIIT', 'db.png', '2026-01-03 18:25:55', '2026-01-03 18:25:55', 'student', '2026-01-03 18:26:17', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `signup_requests`
--

CREATE TABLE `signup_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `request_data` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `marketplace`
--
ALTER TABLE `marketplace`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `signup_requests`
--
ALTER TABLE `signup_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `support_sessions`
--
ALTER TABLE `support_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fu` (`user_id`);

--
-- Indexes for table `wellness_checks`
--
ALTER TABLE `wellness_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `f_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_analysis_reports`
--
ALTER TABLE `ai_analysis_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `anti_ragging_committee`
--
ALTER TABLE `anti_ragging_committee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department_keywords`
--
ALTER TABLE `department_keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `marketplace`
--
ALTER TABLE `marketplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mental_wellness_messages`
--
ALTER TABLE `mental_wellness_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mentors`
--
ALTER TABLE `mentors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mentorship_assignments`
--
ALTER TABLE `mentorship_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mentor_alerts`
--
ALTER TABLE `mentor_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `negotiation`
--
ALTER TABLE `negotiation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `signup_details`
--
ALTER TABLE `signup_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `signup_requests`
--
ALTER TABLE `signup_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_sessions`
--
ALTER TABLE `support_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wellness_checks`
--
ALTER TABLE `wellness_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_analysis_reports`
--
ALTER TABLE `ai_analysis_reports`
  ADD CONSTRAINT `ai_analysis_reports_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `mental_wellness_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `anti_ragging_committee`
--
ALTER TABLE `anti_ragging_committee`
  ADD CONSTRAINT `anti_ragging_committee_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketplace`
--
ALTER TABLE `marketplace`
  ADD CONSTRAINT `marketplace_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mental_wellness_messages`
--
ALTER TABLE `mental_wellness_messages`
  ADD CONSTRAINT `mental_wellness_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentors`
--
ALTER TABLE `mentors`
  ADD CONSTRAINT `mentors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mentorship_assignments`
--
ALTER TABLE `mentorship_assignments`
  ADD CONSTRAINT `mentorship_assignments_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`id`),
  ADD CONSTRAINT `mentorship_assignments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `negotiation`
--
ALTER TABLE `negotiation`
  ADD CONSTRAINT `pi` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `signup_requests`
--
ALTER TABLE `signup_requests`
  ADD CONSTRAINT `signup_requests_ibfk_1` FOREIGN KEY (`reviewed_by`) REFERENCES `signup_details` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_sessions`
--
ALTER TABLE `support_sessions`
  ADD CONSTRAINT `fu` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wellness_checks`
--
ALTER TABLE `wellness_checks`
  ADD CONSTRAINT `f_user_id` FOREIGN KEY (`user_id`) REFERENCES `signup_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
