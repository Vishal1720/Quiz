-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 25, 2025 at 08:43 AM
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
-- Database: `quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `categoryname` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`categoryname`) VALUES
('Entertainment'),
('General Knowledge'),
('Geography'),
('History'),
('Mathematics'),
('Science'),
('Sports'),
('Technology');

-- --------------------------------------------------------

--
-- Table structure for table `quizdetails`
--

CREATE TABLE `quizdetails` (
  `quizid` int(5) NOT NULL,
  `category` varchar(200) NOT NULL,
  `quizname` varchar(200) NOT NULL,
  `email` varchar(222) NOT NULL,
  `timer` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizdetails`
--

INSERT INTO `quizdetails` (`quizid`, `category`, `quizname`, `email`, `timer`) VALUES
(10, 'Technology', 'JIKK', 'admin123@gmail.com', 2),
(13, 'mobiles', 'moto', 'admin123@gmail.com', 5);

-- --------------------------------------------------------

--
-- Table structure for table `quizes`
--

CREATE TABLE `quizes` (
  `ID` int(11) NOT NULL,
  `question` varchar(222) NOT NULL,
  `quizid` int(10) NOT NULL,
  `option1` varchar(222) NOT NULL,
  `option2` varchar(222) NOT NULL,
  `option3` varchar(222) NOT NULL,
  `option4` varchar(222) NOT NULL,
  `answer` varchar(222) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizes`
--

INSERT INTO `quizes` (`ID`, `question`, `quizid`, `option1`, `option2`, `option3`, `option4`, `answer`) VALUES
(12, 'Who developed C++?', 5, 'Bjarne Stroustrup', 'Dennis Ritchie', 'James Goslings', 'Guido van Rossum', 'Bjarne Stroustrup'),
(13, 'Who developed OOP?  iioiooioio', 5, 'Bjarne Stroustrup45', 'Dennis Ritchie', 'James Goslings', 'Guido van Rossum', 'Bjarne Stroustrup45'),
(20, 'OKOKHOKH9Y6KH06KH906KHYHHYH', 5, '6666666666666666666', '7777777777777777777', '88888888888888888', '99999999999999999', '6666666666666666666'),
(24, 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 10, 'yyyyyyyyyyyyyyyy', 'yyyyyyyyyyyyy9999', '99999999999999999', '55555555555555555555555', '99999999999999999'),
(25, '78888888888888888888888888888888', 10, '99999999999999999', '666666666666666666666', '4444444444444444', '4445555555555555555555', '666666666666666666666'),
(26, 'huuuuuuuuuuuuuuuuuuuuuuuuuuuuu', 13, '88888888888888888888', '9999999999999', '999999999999', '0000000000000000', '9999999999999');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attempt_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `quizid` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('completed','in-progress','abandoned') NOT NULL DEFAULT 'in-progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`attempt_id`, `user_email`, `quizid`, `schedule_id`, `score`, `total_questions`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 'chirag7@gmail.com', 10, 33, 50.00, 2, '2025-03-24 15:17:43', '2025-03-24 15:17:48', 'completed', '2025-03-24 14:17:48'),
(2, 'kotianchinnu99@gmail.com', 13, NULL, 100.00, 1, '2025-03-24 14:49:14', '2025-03-24 15:19:14', 'completed', '2025-03-24 14:19:14');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_responses`
--

CREATE TABLE `quiz_responses` (
  `response_id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_answer` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_responses`
--

INSERT INTO `quiz_responses` (`response_id`, `attempt_id`, `question_id`, `user_answer`, `is_correct`, `created_at`) VALUES
(1, 1, 24, 'yyyyyyyyyyyyyyyy', 0, '2025-03-24 14:17:48'),
(2, 1, 25, '666666666666666666666', 1, '2025-03-24 14:17:48'),
(3, 2, 26, '9999999999999', 1, '2025-03-24 14:19:14');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `quizid` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total` int(11) NOT NULL DEFAULT 100,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `access_code` varchar(64) DEFAULT NULL,
  `answers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results_backup`
--

CREATE TABLE `quiz_results_backup` (
  `id` int(11) NOT NULL,
  `quizid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `completion_time` datetime NOT NULL DEFAULT current_timestamp(),
  `access_code` varchar(64) DEFAULT NULL,
  `answers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_quizzes`
--

CREATE TABLE `scheduled_quizzes` (
  `schedule_id` int(11) NOT NULL,
  `quizid` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `access_code` varchar(32) NOT NULL,
  `status` enum('pending','active','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shared_quiz_access`
--

CREATE TABLE `shared_quiz_access` (
  `id` int(11) NOT NULL,
  `quiz_schedule_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `email` varchar(222) NOT NULL,
  `name` text NOT NULL,
  `password` varchar(222) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `auth_provider` enum('password','google') DEFAULT 'password',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`email`, `name`, `password`, `contact`, `google_id`, `profile_picture`, `auth_provider`, `created_at`) VALUES
('aneesh@gmail.com', 'Aneesh Bhat', 'aneesh123', '9145314131', NULL, NULL, 'password', '2025-03-19 12:18:53'),
('chirag.mca.2024@pim.ac.in', 'Chirag S Kotian', '$2y$10$Ee6us6qYUhexselsdLH8YOTvE0Iowzj2vm4kt2H.EnL4lBP27vEn2', '6639653998', '111170575816716044976', 'https://lh3.googleusercontent.com/a/ACg8ocIBfm7X2sUhA04KX08PocLcs11jaST6JutpUZG1EUBztLlzLg=s96-c', 'google', '2025-03-21 12:18:57'),
('chirag7@gmail.com', 'chirag s', '$2y$10$kt2Mh1cA2n2L3YpkQEo3p.fJT7TJcKGtq3/./kxOxoc3w5me/NfrC', '8978686868', NULL, NULL, 'password', '2025-03-19 12:18:53'),
('cjj22@gmail.com', 'ckyhyhyh', 'chirag123', '8978686868', NULL, NULL, 'password', '2025-03-19 12:18:53'),
('ck222@gmail.com', 'chirag', '$2y$10$FhZU85PmzyMRhC4rgsmMkejO7spZuaOpgYLDpY.UqHkmupbfhItCG', '8978686868', NULL, NULL, 'password', '2025-03-19 12:18:53'),
('ckotian770@gmail.com', 'Chirag Kotian', '$2y$10$ZChApTkIcyJxHW4opUHtNuBCwrgW8ywRxLJV36GqpIzv0tXCHH1Yy', '9805348755', '110029929219380299344', 'https://lh3.googleusercontent.com/a/ACg8ocIgvF6FaaPqs9U6m19-jVCR846VGuL2Gqolaq31sMyiEpXN84o=s96-c', 'google', '2025-03-21 12:19:20'),
('kotianchinnu99@gmail.com', 'chinnu kotian', '$2y$10$xP/JI1CPGE0q/4i45qy.vO2KIufU5gALC02TjrWs0.Hz554X1Lxvy', '2266382507', '108933892810850997184', 'https://lh3.googleusercontent.com/a/ACg8ocLDVgRizCxZJMTPbcxq2riFOjhbpSFHIyK1DzeTZB_87DV-9g=s96-c', 'google', '2025-03-19 13:17:11'),
('vishal198shetty@gmail.com', 'Vishal Shetty', 'Vishal1720', '8088835539', NULL, NULL, 'password', '2025-03-19 12:18:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`categoryname`);

--
-- Indexes for table `quizdetails`
--
ALTER TABLE `quizdetails`
  ADD PRIMARY KEY (`quizid`),
  ADD UNIQUE KEY `quizname` (`quizname`),
  ADD KEY `category` (`category`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `quizes`
--
ALTER TABLE `quizes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `quizid` (`quizid`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_user_email` (`user_email`),
  ADD KEY `idx_quizid` (`quizid`),
  ADD KEY `idx_schedule_id` (`schedule_id`);

--
-- Indexes for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `idx_attempt_id` (`attempt_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizid` (`quizid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `access_code` (`access_code`);

--
-- Indexes for table `quiz_results_backup`
--
ALTER TABLE `quiz_results_backup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizid` (`quizid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `access_code` (`access_code`);

--
-- Indexes for table `scheduled_quizzes`
--
ALTER TABLE `scheduled_quizzes`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `unique_access_code` (`access_code`),
  ADD KEY `fk_scheduled_quizzes_quizid` (`quizid`);

--
-- Indexes for table `shared_quiz_access`
--
ALTER TABLE `shared_quiz_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_schedule_id` (`quiz_schedule_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quizdetails`
--
ALTER TABLE `quizdetails`
  MODIFY `quizid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `quizes`
--
ALTER TABLE `quizes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_results_backup`
--
ALTER TABLE `quiz_results_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scheduled_quizzes`
--
ALTER TABLE `scheduled_quizzes`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `shared_quiz_access`
--
ALTER TABLE `shared_quiz_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD CONSTRAINT `fk_responses_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`attempt_id`) ON DELETE CASCADE;

--
-- Constraints for table `scheduled_quizzes`
--
ALTER TABLE `scheduled_quizzes`
  ADD CONSTRAINT `fk_scheduled_quizzes_quizid` FOREIGN KEY (`quizid`) REFERENCES `quizdetails` (`quizid`) ON DELETE CASCADE;

--
-- Constraints for table `shared_quiz_access`
--
ALTER TABLE `shared_quiz_access`
  ADD CONSTRAINT `shared_quiz_access_ibfk_1` FOREIGN KEY (`quiz_schedule_id`) REFERENCES `scheduled_quizzes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
