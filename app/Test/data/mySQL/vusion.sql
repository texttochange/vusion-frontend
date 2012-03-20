-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 19, 2012 at 12:32 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cake_poc`
--

/*CREATE DATABASE IF NOT EXISTS vusion
	CHARACTER SET = 'utf8';*/

USE vusion;

--
-- Table structure for table `acos`
--

CREATE TABLE IF NOT EXISTS `acos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(11) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rght` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=72 ;

--
-- Dumping data for table `acos`
--

INSERT INTO `acos` (`id`, `parent_id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES
(31, 28, '', 0, 'add', 59, 60),
(3, 2, '', 0, 'index', 3, 4),
(4, 2, '', 0, 'view', 5, 6),
(32, 28, '', 0, 'edit', 61, 62),
(5, 2, '', 0, 'add', 7, 8),
(6, 2, '', 0, 'edit', 9, 10),
(2, 1, '', 0, 'Groups', 2, 13),
(7, 2, '', 0, 'delete', 11, 12),
(33, 28, '', 0, 'delete', 63, 64),
(8, 1, '', 0, 'Home', 14, 17),
(9, 8, '', 0, 'index', 15, 16),
(34, 28, '', 0, 'login', 65, 66),
(43, 42, '', 0, 'index', 97, 98),
(11, 10, '', 0, 'index', 19, 20),
(35, 28, '', 0, 'logout', 67, 68),
(12, 10, '', 0, 'view', 21, 22),
(13, 10, '', 0, 'add', 23, 24),
(45, 42, '', 0, 'add', 99, 100),
(14, 10, '', 0, 'edit', 25, 26),
(10, 1, '', 0, 'ProgramDocuments', 18, 29),
(15, 10, '', 0, 'delete', 27, 28),
(17, 16, '', 0, 'index', 31, 32),
(18, 16, '', 0, 'view', 33, 34),
(38, 1, '', 0, 'Participants', 78, 95),
(19, 16, '', 0, 'add', 35, 36),
(52, 38, '', 0, 'view', 87, 88),
(20, 16, '', 0, 'edit', 37, 38),
(16, 1, '', 0, 'Programs', 30, 41),
(21, 16, '', 0, 'delete', 39, 40),
(23, 22, '', 0, 'index', 43, 44),
(24, 22, '', 0, 'view', 45, 46),
(25, 22, '', 0, 'add', 47, 48),
(53, 42, '', 0, 'draft', 101, 102),
(26, 22, '', 0, 'edit', 49, 50),
(22, 1, '', 0, 'ProgramsUsers', 42, 53),
(27, 22, '', 0, 'delete', 51, 52),
(29, 28, '', 0, 'index', 55, 56),
(30, 28, '', 0, 'view', 57, 58),
(54, 42, '', 0, 'active', 103, 104),
(28, 1, '', 0, 'Users', 54, 73),
(36, 1, '', 0, 'AclExtras', 74, 75),
(37, 1, '', 0, 'Mongodb', 76, 77),
(39, 38, '', 0, 'index', 79, 80),
(42, 1, '', 0, 'Scripts', 96, 111),
(44, 28, '', 0, 'initDB', 69, 70),
(47, 46, '', 0, 'index', 113, 114),
(55, 42, '', 0, 'activate_draft', 105, 106),
(1, 0, '', 0, 'controllers', 1, 136),
(46, 1, '', 0, 'Status', 112, 117),
(49, 38, '', 0, 'add', 81, 82),
(50, 38, '', 0, 'edit', 83, 84),
(51, 38, '', 0, 'delete', 85, 86),
(56, 38, NULL, NULL, 'import', 89, 90),
(57, 38, NULL, NULL, 'importBad', 91, 92),
(58, 38, NULL, NULL, 'checkPhoneNumber', 93, 94),
(59, 1, NULL, NULL, 'ProgramSettings', 118, 121),
(60, 59, NULL, NULL, 'edit', 119, 120),
(61, 1, NULL, NULL, 'ShortCodes', 122, 131),
(62, 61, NULL, NULL, 'index', 123, 124),
(63, 61, NULL, NULL, 'add', 125, 126),
(64, 61, NULL, NULL, 'edit', 127, 128),
(65, 61, NULL, NULL, 'delete', 129, 130),
(66, 28, NULL, NULL, 'changePassword', 71, 72),
(67, 42, NULL, NULL, 'activateDraft', 107, 108),
(68, 42, NULL, NULL, 'validateKeyword', 109, 110),
(69, 1, NULL, NULL, 'Admin', 132, 135),
(70, 69, NULL, NULL, 'index', 133, 134),
(71, 46, NULL, NULL, 'export', 115, 116);

-- --------------------------------------------------------

--
-- Table structure for table `aros`
--

CREATE TABLE IF NOT EXISTS `aros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `foreign_key` int(11) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(11) DEFAULT NULL,
  `rght` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Dumping data for table `aros`
--

INSERT INTO `aros` (`id`, `parent_id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES
(1, 0, 'Group', 1, '', 1, 10),
(8, 1, 'User', 8, '', 2, 3),
(5, 0, 'Group', 2, '', 11, 16),
(9, 5, 'User', 9, '', 12, 13),
(10, 6, 'User', 10, '', 18, 19),
(11, 7, 'User', 11, '', 22, 23),
(12, 7, 'User', 12, '', 24, 25),
(6, 0, 'Group', 3, '', 17, 20),
(7, 0, 'Group', 4, '', 21, 26),
(13, 1, 'User', 13, NULL, 4, 5),
(14, 1, 'User', 14, NULL, 6, 7),
(15, 1, 'User', 1, NULL, 8, 9),
(17, 5, 'User', 16, NULL, 14, 15);

-- --------------------------------------------------------

--
-- Table structure for table `aros_acos`
--

CREATE TABLE IF NOT EXISTS `aros_acos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aro_id` int(11) NOT NULL,
  `aco_id` int(11) NOT NULL,
  `_create` varchar(2) NOT NULL DEFAULT '0',
  `_read` varchar(2) NOT NULL DEFAULT '0',
  `_update` varchar(2) NOT NULL DEFAULT '0',
  `_delete` varchar(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `aro_aco_key` (`aro_id`,`aco_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=62 ;

--
-- Dumping data for table `aros_acos`
--

INSERT INTO `aros_acos` (`id`, `aro_id`, `aco_id`, `_create`, `_read`, `_update`, `_delete`) VALUES
(34, 1, 1, '1', '1', '1', '1'),
(35, 5, 1, '-1', '-1', '-1', '-1'),
(36, 5, 28, '1', '1', '1', '1'),
(37, 5, 16, '1', '1', '1', '1'),
(38, 5, 22, '1', '1', '1', '1'),
(39, 5, 8, '1', '1', '1', '1'),
(40, 5, 38, '1', '1', '1', '1'),
(41, 5, 42, '1', '1', '1', '1'),
(42, 5, 46, '1', '1', '1', '1'),
(43, 6, 1, '-1', '-1', '-1', '-1'),
(44, 6, 16, '1', '1', '1', '1'),
(45, 6, 8, '1', '1', '1', '1'),
(46, 6, 38, '1', '1', '1', '1'),
(47, 6, 42, '1', '1', '1', '1'),
(48, 6, 46, '1', '1', '1', '1'),
(49, 7, 1, '-1', '-1', '-1', '-1'),
(50, 7, 17, '1', '1', '1', '1'),
(51, 7, 18, '1', '1', '1', '1'),
(52, 7, 8, '1', '1', '1', '1'),
(53, 7, 50, '-1', '-1', '-1', '-1'),
(54, 7, 49, '-1', '-1', '-1', '-1'),
(55, 7, 39, '1', '1', '1', '1'),
(56, 7, 52, '1', '1', '1', '1'),
(57, 7, 46, '1', '1', '1', '1'),
(58, 5, 59, '1', '1', '1', '1'),
(59, 5, 61, '1', '1', '1', '1'),
(60, 6, 59, '1', '1', '1', '1'),
(61, 6, 61, '1', '1', '1', '1');

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `specific_program_access` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `specific_program_access`, `created`, `modified`) VALUES
(1, 'administrator', 0, '2012-01-30 20:48:19', '2012-01-30 20:48:19'),
(2, 'manager', 0, '2012-01-30 20:49:52', '2012-01-30 20:49:52'),
(3, 'program manager', 1, '2012-01-30 20:50:00', '2012-01-31 08:03:07'),
(4, 'customer', 1, '2012-01-30 20:50:08', '2012-01-31 08:03:18');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE IF NOT EXISTS `programs` (
  `id` varchar(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `url` varchar(50) DEFAULT NULL,
  `database` varchar(50) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `name`, `country`, `url`, `database`, `created`, `modified`) VALUES
('4f59dee9-b4b0-48fa-bb14-1c713745968f', 'M4H', '', 'm4h', 'm4h', '2012-03-09 10:43:53', '2012-03-09 10:43:53'),
('4f26a450-f4f4-44fa-b391-0a123745968f', 'Mother Reminder System', 'congo', 'mrs', 'mrs', '2012-01-30 15:08:16', '2012-01-30 15:08:16'),
('4f337849-65d8-4849-9038-11963745968f', 'wikipedia', 'kenya', 'wiki', 'wiki', '2012-02-09 07:39:53', '2012-02-09 07:39:53'),
('4f62f303-576c-4d08-b70f-0c6c3745968f', 'AMREF', NULL, 'amref', 'amref', '2012-03-16 08:00:03', '2012-03-16 08:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `programs_users`
--

CREATE TABLE IF NOT EXISTS `programs_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` varchar(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `programs_users`
--

INSERT INTO `programs_users` (`id`, `program_id`, `user_id`) VALUES
(7, '4f337849-65d8-4849-9038-11963745968f', 10),
(6, '4f26a450-f4f4-44fa-b391-0a123745968f', 12);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `email` varchar(30) NOT NULL,
  `group_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  UNIQUE KEY `users_username_key` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `group_id`, `created`, `modified`) VALUES
(9, 'jan', 'a47dc5b657cbdd4a961835a6f7e9caa5ee9ab1ac', 'jan@texttochange.com', 2, '2012-01-30 20:57:17', '2012-01-30 20:57:17'),
(10, 'maureen', 'c2260807724f3796957651b60b5bd99eaba9c3ec', 'maureen@texttochange.com', 3, '2012-01-30 20:57:40', '2012-03-15 11:22:13'),
(11, 'unicef', 'edcd5da41fb73b732af57a5c810ea7735fef646f', 'unicef@texttochange.com', 4, '2012-01-30 20:58:11', '2012-01-30 20:58:11'),
(12, 'unilever', '5fa3c44a0dbeb76daafe1bbb62d1954c4d556621', 'unilever@texttochange.com', 4, '2012-01-30 20:58:38', '2012-01-30 20:58:38'),
(8, 'marcus', 'e8d58c12a82e4471319b6fb5ec8610807d6cda98', 'marcus@texttochange.com', 1, '2012-01-30 20:56:54', '2012-01-30 20:56:54');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
