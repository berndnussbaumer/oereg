-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 29. Sep 2020 um 18:13
-- Server-Version: 10.4.11-MariaDB
-- PHP-Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `oereg`
--
CREATE DATABASE IF NOT EXISTS `oereg` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `oereg`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `regs`
--

DROP TABLE IF EXISTS `regs`;
CREATE TABLE `regs` (
  `id` int(11) NOT NULL,
  `staette_nr` int(11) NOT NULL,
  `name` blob NOT NULL,
  `telnr` blob NOT NULL,
  `email` blob NOT NULL,
  `tischnr` varchar(10) NOT NULL,
  `reg_code` varchar(100) NOT NULL,
  `zeit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `staette`
--

DROP TABLE IF EXISTS `staette`;
CREATE TABLE `staette` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `email` varchar(150) NOT NULL,
  `adresse` varchar(500) NOT NULL,
  `plz` varchar(10) NOT NULL,
  `ort` varchar(150) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `dsvgo` varchar(250) NOT NULL,
  `crypt_key` varchar(150) NOT NULL,
  `public_key` blob NOT NULL,
  `private_key` blob NOT NULL,
  `aktiv_code` varchar(250) NOT NULL,
  `aktiviert` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `regs`
--
ALTER TABLE `regs`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `staette`
--
ALTER TABLE `staette`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `regs`
--
ALTER TABLE `regs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT für Tabelle `staette`
--
ALTER TABLE `staette`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

DELIMITER $$
--
-- Ereignisse
--
DROP EVENT `4WochenDelete`$$
CREATE DEFINER=`root`@`localhost` EVENT `4WochenDelete` ON SCHEDULE EVERY 1 HOUR STARTS '2020-09-29 17:30:35' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM regs WHERE TIMESTAMPDIFF(WEEK, NOW(), zeit) > 4$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
