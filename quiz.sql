-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2025 at 06:39 AM
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
  `timer` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizdetails`
--

INSERT INTO `quizdetails` (`quizid`, `category`, `quizname`, `email`, `timer`) VALUES
(9, 'Educational', 'IT Quiz', 'vishal198shetty@gmail.com', 0),
(14, 'Educational', 'Maths Basics', 'vishal198shetty@gmail.com', 0),
(15, 'Programming', 'Python', 'admin123@gmail.com', 25),
(16, 'Literature', 'Test', 'admin123@gmail.com', 5);

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
(2, 'Which of the following is a structured data format?', 9, 'JSON', 'XML', ' CSV', 'All of the above', 'All of the above');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `email` varchar(222) NOT NULL,
  `name` text NOT NULL,
  `password` varchar(222) NOT NULL,
  `contact` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`email`, `name`, `password`, `contact`) VALUES
('admin123@gmail.com', 'admin123', '$2y$10$5NdOqtSilN5OIpgEaBbF/OIEb5GfpWpf6uP6Wq.CXDpP5TMnhATlC', '7892488354'),
('vishal198shetty@gmail.com', 'Vishal Shetty', '$2y$10$izE5CLUnZBfEyl0jqMAP1..xD8hXQONHDLBXhtURot7Zvjy1.COHO', '8088835539');

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
  MODIFY `quizid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `quizes`
--
ALTER TABLE `quizes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Table structure for table `scheduled_quizzes`
--

CREATE TABLE `scheduled_quizzes` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `quizid` int(5) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `access_code` varchar(32) NOT NULL,
  `status` enum('pending','active','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`schedule_id`),
  KEY `quizid` (`quizid`),
  CONSTRAINT `scheduled_quizzes_ibfk_1` FOREIGN KEY (`quizid`) REFERENCES `quizdetails` (`quizid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
