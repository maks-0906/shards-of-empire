-- phpMyAdmin SQL Dump
-- version 3.5.3
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 08 2013 г., 09:25
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
-- Структура таблицы `personages_state`
--

CREATE TABLE IF NOT EXISTS `personages_state` (
  `id_personage_state` int(11) NOT NULL AUTO_INCREMENT,
  `id_personage` int(11) NOT NULL COMMENT 'Идентификатор персонажа',
  `id_dignity` int(2) NOT NULL DEFAULT '1' COMMENT 'ID - таблицы (dignity), титулы',
  `religion_id` int(11) NOT NULL COMMENT 'Идентификатор выбранной религии',
  `fraction_id` int(11) NOT NULL COMMENT 'Идентификатор выбранной фракции',
  `type_id` int(11) NOT NULL COMMENT 'Идентификатор выбранного образа',
  `guild_id` int(11) DEFAULT NULL COMMENT 'Идентификатор гильдии петрсонажа',
  `role_in_guild` enum('owner','moder','member') NOT NULL DEFAULT 'member' COMMENT 'Роль персонажа в гильдии',
  `fame` int(11) NOT NULL DEFAULT '0' COMMENT 'Слава',
  `attack` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметры персонажа. Атака',
  `life` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметры персонажа. Жизнь',
  `reaction` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметры персонажа. Реакция',
  `agility` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметры персонажа.Ловкость',
  `force` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметры персонажа. Сила',
  `luck` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметры персонажа. Удача',
  `sympathy` int(11) NOT NULL DEFAULT '0' COMMENT 'Параметр персонажа Симпатия',
  `x_l` int(5) NOT NULL DEFAULT '0' COMMENT 'Последняя координата персонажа на карте по X',
  `y_l` int(5) NOT NULL DEFAULT '0' COMMENT 'Последняя координата персонажа на карте по Y',
  `x_c` int(5) NOT NULL DEFAULT '0' COMMENT 'Координата (x) локации прибытия',
  `y_c` int(5) NOT NULL DEFAULT '0' COMMENT 'Координата (y) локации прибытия',
  `status_move_personage` enum('arrival','transit') DEFAULT NULL COMMENT 'Статус перемещения персонажа (arrival - прибыл, transit - в пути)',
  `total_level` int(5) NOT NULL DEFAULT '1' COMMENT 'Текущий общий уровень персонажа',
  `privilege_last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата последнего добавления привилегий',
  `finishing_move_personage` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Дата окончания передвижения персонажа',
  PRIMARY KEY (`id_personage_state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Состояние персонажа в мире.' AUTO_INCREMENT=2 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
