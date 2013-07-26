-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 09 2013 г., 10:54
-- Версия сервера: 5.1.66-0+squeeze1
-- Версия PHP: 5.3.3-7+squeeze15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `shards`
--

-- --------------------------------------------------------

--
-- Структура таблицы `units_characteristics`
--

CREATE TABLE IF NOT EXISTS `units_characteristics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `building_id` int(11) NOT NULL,
  `building_level_id` int(11) NOT NULL,
  `attack` int(11) NOT NULL COMMENT 'Атака',
  `protection` int(11) NOT NULL COMMENT 'Защита',
  `life` int(11) NOT NULL COMMENT 'Жизнь',
  `production_speed` int(11) NOT NULL COMMENT 'Скорость производства в секундах',
  `speed` int(11) NOT NULL COMMENT 'Скорость передвижения',
  `number_transported_cargo` int(11) NOT NULL COMMENT 'Количество переносимого груза',
  `number_points_construction_fame` int(11) NOT NULL COMMENT 'Кол-во очков славы при строительстве ',
  `place_barracks` int(11) NOT NULL COMMENT 'Место в казарме',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Технические характеристики юнита' AUTO_INCREMENT=27 ;

--
-- Дамп данных таблицы `units_characteristics`
--

INSERT INTO `units_characteristics` (`id`, `unit_id`, `building_id`, `building_level_id`, `attack`, `protection`, `life`, `production_speed`, `speed`, `number_transported_cargo`, `number_points_construction_fame`, `place_barracks`) VALUES
(1, 1, 20, 1, 2, 2, 5, 120, 14, 5, 0, 1),
(2, 2, 20, 2, 5, 4, 10, 300, 14, 5, 1, 1),
(3, 3, 20, 3, 7, 5, 20, 420, 14, 10, 2, 1),
(4, 4, 20, 4, 12, 7, 30, 600, 13, 30, 5, 1),
(5, 5, 20, 5, 14, 10, 40, 900, 13, 30, 5, 1),
(6, 6, 20, 6, 16, 10, 40, 900, 13, 30, 8, 1),
(7, 7, 20, 7, 18, 7, 45, 900, 13, 30, 6, 1),
(8, 8, 20, 9, 20, 12, 50, 1500, 12, 40, 10, 1),
(9, 9, 20, 9, 24, 7, 50, 1800, 12, 40, 12, 1),
(10, 10, 20, 10, 22, 14, 50, 2400, 12, 40, 15, 1),
(11, 11, 20, 11, 25, 10, 30, 1800, 5, 30, 15, 5),
(12, 12, 21, 1, 20, 16, 60, 2400, 24, 60, 4, 2),
(13, 23, 21, 1, 0, 10, 35, 900, 8, 200, 0, 1),
(14, 13, 21, 5, 22, 13, 60, 3600, 24, 80, 12, 2),
(15, 24, 21, 5, 0, 25, 70, 1800, 10, 350, 1, 2),
(16, 15, 21, 9, 30, 22, 80, 6600, 22, 70, 18, 3),
(17, 18, 21, 13, 35, 50, 130, 9000, 18, 80, 24, 3),
(18, 25, 21, 13, 0, 100, 150, 2700, 10, 600, 2, 3),
(19, 20, 21, 17, 70, 30, 200, 12600, 15, 150, 28, 7),
(20, 21, 21, 17, 50, 50, 220, 15000, 20, 120, 30, 5),
(21, 14, 20, 13, 30, 20, 80, 5400, 15, 70, 20, 1),
(22, 16, 20, 14, 50, 20, 60, 3600, 5, 30, 15, 10),
(23, 17, 20, 15, 40, 25, 100, 4200, 15, 80, 22, 1),
(24, 19, 20, 17, 45, 40, 150, 9000, 16, 90, 25, 2),
(25, 29, 20, 18, 70, 30, 100, 6000, 5, 30, 20, 12),
(26, 22, 20, 19, 110, 50, 150, 1200, 5, 30, 20, 15);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
