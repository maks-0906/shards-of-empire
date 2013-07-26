-- phpMyAdmin SQL Dump
-- version 3.5.3
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 12 2013 г., 11:19
-- Версия сервера: 5.1.65-community-log
-- Версия PHP: 5.3.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `al0_shards`
--

-- --------------------------------------------------------

--
-- Структура таблицы `personages_guilds_request_invitation`
--

CREATE TABLE IF NOT EXISTS `personages_guilds_request_invitation` (
  `id_personages_guilds_request_invitation` int(11) NOT NULL AUTO_INCREMENT,
  `id_personage` int(11) NOT NULL COMMENT 'ID - персонажа',
  `guild_id` int(11) NOT NULL COMMENT 'ID - союза',
  `status_request_invitation` enum('request','invitation') NOT NULL COMMENT 'Статус (request - заявка, invitation - приглашение)',
  `date_request_invitation` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата заявки или приглашения',
  PRIMARY KEY (`id_personages_guilds_request_invitation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
