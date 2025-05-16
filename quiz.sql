-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2025 at 04:40 PM
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
('Educational'),
('Entertainment'),
('Literature'),
('Programming');

-- --------------------------------------------------------

--
-- Table structure for table `quizdetails`
--

CREATE TABLE `quizdetails` (
  `quizid` int(5) NOT NULL,
  `category` varchar(200) NOT NULL,
  `quizname` varchar(200) NOT NULL,
  `email` varchar(222) NOT NULL,
  `timer` int(11) NOT NULL DEFAULT 30,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizdetails`
--

INSERT INTO `quizdetails` (`quizid`, `category`, `quizname`, `email`, `timer`, `is_visible`) VALUES
(21, 'Entertainment', 'Anime', 'admin123@gmail.com', 10, 1),
(27, 'Educational', 'English', 'admin123@gmail.com', 22, 1);

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
(11, 'Which anime features a notebook that can kill people when names are written in it?', 21, 'A) Attack on Titan ', 'B) Naruto', ' C) Death Note', ' D) One Piece', ' C) Death Note'),
(12, 'Who is the main protagonist in One Piece?', 21, 'Luffy', 'Naruto', 'Zoro', 'Sasuke', 'Luffy'),
(13, 'In My Hero Academia, what is Deku’s real name?', 21, 'Katsuki Bakugo', 'Shoto Todoroki', ' Izuku Midoriya', 'All Might', ' Izuku Midoriya'),
(14, 'In \\\"Tokyo Ghoul \\\" what does the protagonist turn into?', 21, 'Zombie', 'Ghoul', 'Monster', 'Human', 'Ghoul'),
(15, 'In \\\"Death Note \\\" what is the name of the Shinigami who drops the Death Note?', 21, 'Light', 'Rem', 'Ryuk', 'L', 'Ryuk'),
(18, 'Who wrote Hamlet?', 27, 'William Shakespeare', 'Robert Frost', 'Ernest Hemingway', 'No one knows', 'William Shakespeare');

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
(0, 'vishal198shetty@gmail.com', 21, 35, 0.00, 5, '2025-03-25 13:11:38', '2025-03-25 13:11:45', 'completed', '2025-03-25 12:11:45');

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
(0, 0, 11, 'A) Attack on Titan ', 0, '2025-03-25 12:11:45'),
(0, 0, 12, 'Zoro', 0, '2025-03-25 12:11:45'),
(0, 0, 13, 'Katsuki Bakugo', 0, '2025-03-25 12:11:45'),
(0, 0, 14, 'Monster', 0, '2025-03-25 12:11:45'),
(0, 0, 15, 'Light', 0, '2025-03-25 12:11:45');

-- --------------------------------------------------------

--
-- Table structure for table `scheduled_quizzes`
--

CREATE TABLE `scheduled_quizzes` (
  `schedule_id` int(11) NOT NULL,
  `quizid` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `end_time` datetime NOT NULL,
  `access_code` varchar(32) NOT NULL,
  `status` enum('pending','active','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scheduled_quizzes`
--

INSERT INTO `scheduled_quizzes` (`schedule_id`, `quizid`, `start_time`, `duration`, `end_time`, `access_code`, `status`, `created_at`) VALUES
(32, 21, '2025-03-24 17:42:00', 0, '2025-03-24 17:43:00', 'a00d41e607a0fc18d76ce36a3574b434', 'completed', '2025-03-24 12:11:44'),
(33, 21, '2025-03-24 18:59:00', 0, '2025-03-24 19:00:00', '071127a5feca3c69fe2b1ab0c5410320', 'active', '2025-03-24 13:28:29'),
(34, 21, '2025-03-25 10:30:00', 0, '2025-03-25 10:31:00', 'eba301192b822f17a0970f3f1d227d43', 'active', '2025-03-25 05:00:14'),
(35, 21, '2025-03-25 17:41:00', 0, '2025-03-25 17:42:00', '447dc8e660bd3e793f50913f8792aec2', 'active', '2025-03-25 12:11:16'),
(36, 21, '2025-03-25 20:26:00', 0, '2025-03-25 20:27:00', '3b38bc7ec30dcd031c569782fe1f3cbb', 'completed', '2025-03-25 14:55:19');

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
('admin123@gmail.com', 'admin', '$2y$10$5NdOqtSilN5OIpgEaBbF/OIEb5GfpWpf6uP6Wq.CXDpP5TMnhATlC', '7892488354', NULL, NULL, 'password', '2025-03-20 03:37:42'),
('vishal198shetty@gmail.com', 'Vishal Shetty', '$2y$10$izE5CLUnZBfEyl0jqMAP1..xD8hXQONHDLBXhtURot7Zvjy1.COHO', '8088835539', '102859453935166839635', 'https://lh3.googleusercontent.com/a/ACg8ocKBCq6OmaJlpsgc365mCp41QoUj4TdtmDgv28ML0WcuQ_Xo7eQ=s96-c', 'google', '2025-03-20 03:37:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--

-- Add difficulty level to quizes table
ALTER TABLE quizes ADD COLUMN difficulty_level ENUM('easy', 'medium', 'intermediate', 'hard') NOT NULL DEFAULT 'easy';

-- Create table for tracking user attempts
CREATE TABLE IF NOT EXISTS user_quiz_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    quizid INT,
    question_id INT,
    difficulty_level ENUM('easy', 'medium', 'intermediate', 'hard'),
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_email) REFERENCES users(email),
    FOREIGN KEY (quizid) REFERENCES quizdetails(quizid),
    FOREIGN KEY (question_id) REFERENCES quizes(ID)
);

ALTER TABLE quizes ADD COLUMN difficulty 
ENUM('easy', 'medium', 'intermediate', 'hard')NOT NULL DEFAULT 'easy';

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
-- Indexes for table `scheduled_quizzes`
--
ALTER TABLE `scheduled_quizzes`
  ADD PRIMARY KEY (`schedule_id`),
  ADD UNIQUE KEY `unique_access_code` (`access_code`),
  ADD KEY `fk_scheduled_quizzes_quizid` (`quizid`);

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
  MODIFY `quizid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `quizes`
--
ALTER TABLE `quizes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `scheduled_quizzes`
--
ALTER TABLE `scheduled_quizzes`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quizdetails`
--
ALTER TABLE `quizdetails`
  ADD CONSTRAINT `quizdetails_ibfk_1` FOREIGN KEY (`category`) REFERENCES `category` (`categoryname`),
  ADD CONSTRAINT `quizdetails_ibfk_2` FOREIGN KEY (`email`) REFERENCES `users` (`email`);

--
-- Constraints for table `quizes`
--
ALTER TABLE `quizes`
  ADD CONSTRAINT `quizes_ibfk_1` FOREIGN KEY (`quizid`) REFERENCES `quizdetails` (`quizid`);

--
-- Add difficulty level to quizes table
ALTER TABLE quizes ADD COLUMN difficulty_level ENUM('easy', 'medium', 'intermediate', 'hard') NOT NULL DEFAULT 'easy';

-- Later in the file
ALTER TABLE quizes ADD COLUMN difficulty 
ENUM('easy', 'medium', 'intermediate', 'hard')NOT NULL DEFAULT 'easy';


-- Constraints for table `scheduled_quizzes`
--
ALTER TABLE `scheduled_quizzes`
  ADD CONSTRAINT `fk_scheduled_quizzes_quizid` FOREIGN KEY (`quizid`) REFERENCES `quizdetails` (`quizid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
