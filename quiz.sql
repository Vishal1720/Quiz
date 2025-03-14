-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2025 at 04:58 AM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizdetails`
--

INSERT INTO `quizdetails` (`quizid`, `category`, `quizname`, `email`) VALUES
(2, 'Entertainment', 'English', 'vishal198shetty@gmail.com'),
(3, 'Educational', 'Maths Basics', 'aneesh@gmail.com'),
(5, 'Programming', 'CPP', 'vishal198shetty@gmail.com');

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
(18, 'What is the correct way to declare an integer variable in C++?', 5, ' int num', ' integer num', 'num int', 'var num', ' int num'),
(19, 'Which of the following is used to output text in C++?', 5, ' print()', 'echo', 'cout <<', ' display()', 'cout <<'),
(20, 'Which symbol is used for single-line comments in C++?', 5, '# ', '// ', ' /* */ ', ' --', '// '),
(21, 'What is the output of 5 / 2 in C++ (assuming both are integers)?', 5, '2.5 ', ' 2 ', '3 ', 'Error', ' 2 '),
(22, 'Which header file is needed for input and output operations in C++?', 5, 'a) <stdio.h>  ', '<iostream>', '<conio.h> ', '<fstream>', '<iostream>'),
(23, 'What does new do in C++?', 5, 'Declares a new variable', ' Allocates memory dynamically', 'Deletes a variable', 'Creates a new function', ' Allocates memory dynamically'),
(24, 'Which keyword is used to define a constant variable?', 5, ' constant', 'const', 'define', 'let', 'const'),
(25, 'Which of these is NOT a valid C++ data type?', 5, 'double', 'char', ' real', 'bool', ' real'),
(27, 'Which movie features the famous line \"I’m the king of the world!\"?', 2, ' The Lion King', 'Titanic', 'Gladiator', 'Avatar', 'Titanic'),
(28, 'Who played the role of Iron Man in the Marvel Cinematic Universe?', 2, 'Chris Evans', 'Mark Ruffalo', 'Chris Hemsworth', 'Robert Downey Jr. ', 'Robert Downey Jr. '),
(29, 'What is the name of the coffee shop in the TV series \"Friends\"?', 2, 'Central Perk ', 'Cafe Perk', 'Coffee Central', 'The Coffee House', 'Cafe Perk'),
(30, 'Which animated movie features a talking snowman named Olaf', 2, 'Brave', 'Frozen', 'Moana', 'Tangled', 'Frozen'),
(31, 'Which actor played Jack Sparrow in the \"Pirates of the Caribbean\" series?', 2, 'Brad Pitt', ' Johnny Depp ', 'Leonardo DiCaprio', 'Tom Cruise', ' Johnny Depp ');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `email` varchar(222) NOT NULL,
  `name` text NOT NULL,
  `password` varchar(222) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`email`, `name`, `password`, `contact`, `role`) VALUES
('aneesh@gmail.com', 'Aneesh Bhat', 'aneesh123', '9145314131', 'user'),
('vishal198shetty@gmail.com', 'Vishal Shetty', 'Vishal1720', '8088835539', 'admin');

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
  MODIFY `quizid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quizes`
--
ALTER TABLE `quizes`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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



CREATE TABLE `quiz_attempts` (
  `attempt_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(222) NOT NULL,
  `quizid` int(5) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `attempt_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`attempt_id`),
  KEY `email` (`email`),
  KEY `quizid` (`quizid`),
  CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`email`) REFERENCES `users` (`email`),
  CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quizid`) REFERENCES `quizdetails` (`quizid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
