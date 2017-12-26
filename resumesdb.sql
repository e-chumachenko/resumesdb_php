-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 05, 2017 at 08:42 PM
-- Server version: 5.6.34-log
-- PHP Version: 7.1.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resumesdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `education`
--

CREATE TABLE `education` (
  `resume_id` int(11) NOT NULL,
  `institution_id` int(11) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `degree` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `education`
--

INSERT INTO `education` (`resume_id`, `institution_id`, `year`, `degree`) VALUES
(1, 1, 2012, 'Computer Science'),
(2, 2, 2015, 'Фундаментальная информатика и информационные технологии'),
(3, 3, 2013, 'Программно-технические средства информатизации'),
(4, 4, 2012, 'Mathématiques & informatique'),
(4, 5, 2015, 'Computer Science'),
(5, 6, 2014, 'Компьютерные технологии и интеллектуальный анализ данных');

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE `institutions` (
  `institution_id` int(11) NOT NULL,
  `name` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `institutions`
--

INSERT INTO `institutions` (`institution_id`, `name`) VALUES
(16, 'Brown University'),
(17, 'Columbia University'),
(18, 'Cornell University'),
(19, 'Dartmouth College'),
(5, 'Harvard University'),
(4, 'L\'université Paris 1 Panthéon-Sorbonne'),
(20, 'Princeton University'),
(23, 'University of Michigan'),
(21, 'University of Pennsylvania'),
(1, 'Yale University'),
(3, 'Московский  Государственный  Технический  Университет  имени Н.Э.Баумана'),
(15, 'Московский авиационный институт (национальный исследовательский университет)'),
(9, 'Московский государственный институт международных отношений (университет) МИД РФ'),
(2, 'Московский государственный университет имени М.В.Ломоносова'),
(6, 'Московский физико-технический институт (государственный университет)'),
(11, 'Национальный исследовательский Томский политехнический университет'),
(8, 'Национальный исследовательский университет \"Высшая школа экономики\"'),
(10, 'Национальный исследовательский ядерный университет \"МИФИ\"'),
(12, 'Новосибирский национальный исследовательский государственный университет'),
(7, 'Санкт-Петербургский государственный университет'),
(13, 'Уральский федеральный университет имени первого Президента России Б.Н. Ельцина');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `year_start` int(11) DEFAULT NULL,
  `year_finish` int(11) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `resume_id`, `year_start`, `year_finish`, `description`) VALUES
(1, 1, 2012, 2014, 'Google Inc.'),
(2, 1, 2014, 2017, 'Facebook Inc.'),
(4, 3, 2013, 2017, 'ООО Яндекс'),
(5, 4, 2015, 2017, 'Société Air France, S.A.'),
(6, 5, 2014, 2017, 'Rambler&Co, портал «Рамблер»'),
(7, 2, 2015, 2017, 'ООО Мэйл.Ру ');

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `resume_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(128) DEFAULT NULL,
  `patronymic_name` varchar(128) DEFAULT NULL,
  `last_name` varchar(128) DEFAULT NULL,
  `job_title` varchar(128) DEFAULT NULL,
  `resume_cv` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `resumes`
--

INSERT INTO `resumes` (`resume_id`, `user_id`, `first_name`, `patronymic_name`, `last_name`, `job_title`, `resume_cv`) VALUES
(1, 1, 'Catherine', '', 'Stell', ' Mobile Developer Android', '* C++\r\n* C#\r\n* Java'),
(2, 2, 'Роман', 'Евгеньевич', 'Уваров', 'Администратор Баз Данных', '+ Oracle\r\n+ MySQL\r\n+ SqlServer\r\n+ SQLite'),
(3, 3, 'Ольга', 'Алексеевна', 'Камсулёва', 'Front-End Web Developer', '+ HTML5\r\n+ CSS3 (Responsive Design and Accessibility)\r\n+ Bootstrap\r\n+ JavaScript \r\n+ JQuery'),
(4, 4, 'Robert', '', 'Noel', 'Mobile Developer iOS', '* C\r\n* C++\r\n* Objective-C\r\n* Swift'),
(5, 5, 'Галина', 'Владимировна', 'Ламкина', 'Back-End Web Developer', '+ PHP\r\n+ SQL\r\n+ JavaScript \r\n+ JQuery\r\n+ AJAX\r\n+ Python');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `passwordh` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `passwordh`) VALUES
(1, 'catherine_stell@yahoo.com', 'ee49f606a87952af832bbdf1f5927c14'),
(2, 'roman_uvarov@mail.ru', '584f79a8d1e15e706ce0d50277427864'),
(3, 'olga_kamsuleva@yandex.ru', 'dd1e37bba197f4540f9ad2d71480a01d'),
(4, 'robert_noel@gmail.com', '3e1afcb36bc6c61e69a29b61dbdab793'),
(5, 'galina_lamkina@rambler.ru', 'f5bdaa30f5e2d845a7ec30ef5fe517dd');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`resume_id`,`institution_id`),
  ADD KEY `institution_id` (`institution_id`);

--
-- Indexes for table `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`institution_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `name_2` (`name`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `resume_id` (`resume_id`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`resume_id`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `job_title` (`job_title`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`),
  ADD KEY `passwordh` (`passwordh`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `institutions`
--
ALTER TABLE `institutions`
  MODIFY `institution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `education`
--
ALTER TABLE `education`
  ADD CONSTRAINT `education_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `education_ibfk_2` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`institution_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
