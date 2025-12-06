-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 06, 2025 at 11:17 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db(dummy)`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `author` varchar(100) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_borrowed` tinyint(1) DEFAULT '0',
  `copies` int NOT NULL DEFAULT '1',
  `isbn` varchar(20) DEFAULT NULL,
  `description` text,
  `genre` varchar(100) DEFAULT NULL,
  `readable_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `cover_image`, `image`, `is_borrowed`, `copies`, `isbn`, `description`, `genre`, `readable_file`) VALUES
(11, 'Twisted Lies', 'Ana Huang', NULL, 'images/book_68b451cece68a_1756647886.jpg', 0, 3, '', 'He&amp;#039;ll do anything to have her...including lie.\r\n\r\nCharming, deadly, and smart enough to hide it, Christian Harper is a monster dressed in the perfectly tailored suits of a gentleman.\r\n\r\nHe has little use for morals and even less use for love, but he can’t deny the strange pull he feels toward the woman living just one floor below him.\r\n\r\nShe’s the object of his darkest desires, the only puzzle he can’t solve. And when the opportunity to get closer to her arises, he breaks his own rules to offer her a deal she can’t refuse.\r\n\r\nEvery monster has their weakness. She’s his.\r\n\r\nHis obsession.\r\n\r\nHis addiction.\r\n\r\nHis only exception.\r\n\r\nSweet, shy, and introverted despite her social media fame, Stella Alonso is a romantic who keeps her heart in a cage.\r\n\r\nBetween her two jobs, she has little time or desire for a relationship.\r\n\r\nBut when a threat from her past drives her into the arms—and house—of the most dangerous man she’s ever met, she’s tempted to let herself feel something for the first time in a long time.\r\n\r\nBecause despite Christian’s cold nature, he makes her feel everything when she’s with him.\r\n\r\nPassionate.\r\n\r\nProtected.\r\n\r\nTruly wanted.\r\n\r\nTheirs is a love twisted with secrets and tainted by lies…and when the truths are finally revealed, they could shatter everything.\r\n\r\nTwisted Lies is a steamy fake dating romance. It&amp;amp;#039;s the fourth and final book in the Twisted series but can be read as a standalone.\r\n\r\nWarning: The story contains explicit content, violence, profanity, and topics that may be sensitive to some readers. Please see inside the book for a detailed list. Recommended for 18+.', 'Romance', 'books/datastructures.pdf'),
(6, 'The Alchemist', 'Paulo Coelho', NULL, 'images/book_68b451f4e1a70_1756647924.jpg', 0, 4, '', '0', 'Fantasy', 'pdfs/book_68b4557908f38_1756648825.pdf'),
(3, 'Rich Dad Poor Dad', 'Robert Kiyosaki', 'images/3.jpg', 'images/3.jpg', 0, 4, NULL, NULL, NULL, NULL),
(4, 'Grumpy Darling', 'Alexandra Moody', NULL, 'images/book_68b45560d79ab_1756648800.jpg', 0, 5, '', 'She&amp;amp;#039;s never been kissed. He&amp;amp;#039;s never felt this way about anyone.\r\n\r\nPaige has ticked off everything on her senior year bucket list except one tiny thing — she’s never kissed anyone. And her best friend, Grayson Darling, is to blame.\r\n\r\nGrayson is the school hockey team’s notorious enforcer, and he’s been scaring away any eligible bachelors that so much as look in Paige’s direction. With time running out, she demands that Grayson stop defending her honor. Instead, he’ll become her dating coach, training her to win the guy of her dreams.\r\n\r\nBut Grayson has plans of his own. He’s been in love with Paige since they were kids, and his clock is running, too. Coaching Paige might be his last chance to show her how good they’d be together. After all, practice makes perfect.', 'Romance', NULL),
(5, 'Fifty Shades of Grey', 'E. L. James', NULL, 'images/book_68b45219142f8_1756647961.jpg', 0, 5, '', '0', 'Romance', NULL),
(12, 'The Templars', 'Régine Pernoud', NULL, 'images/book_68bf74b4cb663_1757377716.jpg', 0, 3, '', '', 'History', NULL),
(10, 'Control Your Mind and Master Your Feelings', 'Eric Robertson', NULL, 'images/book_68b451e080b02_1756647904.jpg', 0, 4, '', '0', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `book_ratings`
--

DROP TABLE IF EXISTS `book_ratings`;
CREATE TABLE IF NOT EXISTS `book_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `book_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_book` (`book_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `book_ratings`
--

INSERT INTO `book_ratings` (`id`, `book_id`, `user_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 11, 1, 4, '2025-08-21 22:04:34', '2025-08-22 00:23:21'),
(2, 6, 1, 5, '2025-08-21 23:11:15', '2025-08-21 23:11:15'),
(3, 12, 1, 4, '2025-09-09 16:14:25', '2025-09-09 16:14:25');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

DROP TABLE IF EXISTS `borrowed_books`;
CREATE TABLE IF NOT EXISTS `borrowed_books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT '0.00',
  `borrowed_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(255) NOT NULL,
  `returned` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `borrowed_books`
--

INSERT INTO `borrowed_books` (`id`, `user_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `late_fee`, `borrowed_at`, `username`, `returned`) VALUES
(55, 1, 11, '2025-06-11', '2025-06-25', '2025-06-12', 0.00, '2025-06-11 23:55:08', 'admin', 1),
(56, 2, 11, '2025-06-12', '2025-06-01', '2025-06-12', 11.00, '2025-06-12 01:11:55', 'user', 1),
(57, 2, 11, '2025-08-10', '2025-08-24', NULL, 0.00, '2025-08-10 02:55:47', 'user', 0),
(58, 1, 11, '2025-08-16', '2025-08-30', '2025-08-31', 1.00, '2025-08-16 01:28:02', 'admin', 1),
(59, 1, 6, '2025-08-21', '2025-09-04', NULL, 0.00, '2025-08-21 23:07:35', 'admin', 0),
(60, 2, 11, '2025-08-22', '2025-09-05', NULL, 0.00, '2025-08-22 00:33:53', 'user', 0),
(61, 1, 10, '2025-09-08', '2025-09-22', NULL, 0.00, '2025-09-08 17:03:33', 'admin', 0),
(62, 1, 12, '2025-09-09', '2025-09-23', '2025-09-09', 0.00, '2025-09-09 14:29:43', 'admin', 1),
(63, 1, 12, '2025-09-09', '2025-09-23', NULL, 0.00, '2025-09-09 14:31:26', 'admin', 0),
(64, 1, 12, '2025-09-09', '2025-09-23', NULL, 0.00, '2025-09-09 16:15:25', 'admin', 0);

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
CREATE TABLE IF NOT EXISTS `search_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `search_term` varchar(255) NOT NULL,
  `search_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(10) DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`, `avatar`) VALUES
(1, 'admin', 'admin@yahoo.com', '$2y$10$P/1HRGUz/mN40kzbTQfBheX.eMXVcMtqTSblbppMJwV99hMmY0TAq', '2025-06-02 16:19:51', 'admin', 'uploads/avatars/admin/avatar_1756813794.jpg'),
(2, 'user', 'user01@gmail.com', '$2y$10$NK8dT6el/n0B2d84o/Pzk.j9sAs3BnjD5pvF4JIlOwFgApuf0OA9O', '2025-06-07 13:45:43', 'user', 'uploads/avatars/user/avatar_1754765784.jpg'),
(3, 'user2', 'user02@gmail.com', '$2y$10$VQS3i.42mqVT8XAs6julZOFelUjbGafKLDtPT8brtjCEO13.rf34m', '2025-06-12 01:20:45', 'user', NULL),
(4, 'se22d028', 'user03@gmail.com', '$2y$10$cEeHuDpEeMaQHyTOBdfWAezajSTZ7hxCj9Xgm2N7VEsWZb.k2DiQS', '2025-06-12 01:25:53', 'user', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
