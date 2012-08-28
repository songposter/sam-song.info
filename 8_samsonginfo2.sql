-- phpMyAdmin SQL Dump
-- version 3.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Erstellungszeit: 19. März 2012 um 11:11
-- Server Version: 5.1.61
-- PHP-Version: 5.3.2-1ubuntu4.14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `8_samsonginfo2`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `facebook`
--

DROP TABLE IF EXISTS `facebook`;
CREATE TABLE IF NOT EXISTS `facebook` (
  `ID` bigint(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `expires` int(11) NOT NULL,
  `ispage` tinyint(1) NOT NULL DEFAULT '0',
  `use_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `songtypes` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `timing` enum('WaitForTime','WaitForPlayCount') COLLATE utf8_unicode_ci NOT NULL,
  `timing_value` int(3) NOT NULL,
  `action_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `action_link` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `prefix` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `postfix` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `field_order` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `website_title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `website_link` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `website_description` text COLLATE utf8_unicode_ci NOT NULL,
  `picture_dir` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `limit_reached` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `twitter`
--

DROP TABLE IF EXISTS `twitter`;
CREATE TABLE IF NOT EXISTS `twitter` (
  `ID` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `screenname` varchar(255) NOT NULL,
  `timing` enum('WaitForTime','WaitForPlayCount') NOT NULL DEFAULT 'WaitForTime',
  `timing_value` int(11) NOT NULL DEFAULT '10',
  `prefix` varchar(50) NOT NULL,
  `postfix` varchar(50) NOT NULL,
  `songtypes` varchar(20) NOT NULL,
  `field_order` varchar(10) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `idx_name` (`screenname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
