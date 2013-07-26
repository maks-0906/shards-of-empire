-- phpMyAdmin SQL Dump
-- version 3.5.3
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1:3306
-- Время создания: Мар 30 2013 г., 20:01
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
-- Структура таблицы `building_basic_levels`
--

CREATE TABLE IF NOT EXISTS `building_basic_levels` (
  `level_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор базового уровня',
  `building_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Идентификатор здания',
  `data_bonus` text NOT NULL COMMENT 'Сериализованные данные бонусов базовых уровней и их значения увеличений по уровню здания',
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Базовые уровни зданий и их правила увеличения бонусов.' AUTO_INCREMENT=22 ;

--
-- Дамп данных таблицы `building_basic_levels`
--

INSERT INTO `building_basic_levels` (`level_id`, `building_id`, `data_bonus`) VALUES
(1, 1, 'a:4:{s:23:"bonus_protection_castle";a:3:{s:5:"basic";i:5;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:22:"bonus_happiness_people";a:3:{s:5:"basic";i:4;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:14:"bonus_sympathy";a:3:{s:5:"basic";i:3;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:23:"bonus_population_health";a:3:{s:5:"basic";i:2;s:7:"improve";i:1;s:7:"measure";s:1:"u";}}'),
(2, 2, 'a:3:{s:26:"bonus_protection_buildings";a:3:{s:5:"basic";i:100;s:7:"improve";i:20;s:7:"measure";s:1:"u";}s:12:"bonus_attack";a:3:{s:5:"basic";i:5;s:7:"improve";i:1;s:7:"measure";s:2:"pt";}s:19:"bonus_size_garrison";a:3:{s:5:"basic";i:5;s:7:"improve";i:1;s:7:"measure";s:2:"pt";}}'),
(3, 3, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:100;s:7:"improve";i:5;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(4, 4, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:100;s:7:"improve";i:5;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(5, 5, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:100;s:7:"improve";i:5;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(6, 6, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:100;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(7, 7, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:10;s:7:"improve";i:5;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(8, 8, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:100;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(9, 9, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:20;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(10, 10, 'a:2:{s:21:"bonus_number_products";a:3:{s:5:"basic";i:10;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:21:"bonus_time_production";a:3:{s:5:"basic";i:60;s:7:"improve";i:1;s:7:"measure";s:3:"min";}}'),
(11, 11, 'a:1:{s:29:"bonus_number_points_happiness";a:3:{s:5:"basic";i:1;s:7:"improve";i:1;s:7:"measure";s:1:"u";}}'),
(12, 12, 'a:2:{s:16:"bonus_for_buying";a:3:{s:5:"basic";i:5;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:14:"bonus_for_sale";a:3:{s:5:"basic";i:5;s:7:"improve";i:1;s:7:"measure";s:1:"u";}}'),
(13, 13, 'a:2:{s:29:"bonus_number_points_happiness";a:3:{s:5:"basic";i:1;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:24:"bonus_number_points_fame";a:3:{s:5:"basic";i:20;s:7:"improve";i:20;s:7:"measure";s:1:"u";}}'),
(14, 14, 'a:2:{s:23:"bonus_number_points_hit";a:3:{s:5:"basic";i:10;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:14:"mortality_rate";a:3:{s:5:"basic";i:-1;s:7:"improve";i:-1;s:7:"measure";s:1:"u";}}'),
(15, 15, 'a:4:{s:29:"bonus_number_points_happiness";a:3:{s:5:"basic";i:10;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:5:"crime";a:3:{s:5:"basic";i:10;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:19:"number_barrels_beer";a:3:{s:5:"basic";i:1;s:7:"improve";i:0;s:7:"measure";s:1:"u";}s:20:"period_number_levels";i:4;}'),
(16, 16, 'a:1:{s:16:"study_technology";a:3:{s:5:"basic";i:20;s:7:"improve";i:1;s:7:"measure";s:1:"u";}}'),
(17, 17, 'a:2:{s:28:"bonus_number_points_blessing";a:3:{s:5:"basic";i:2;s:7:"improve";i:0;s:7:"measure";s:6:"method";}s:13:"candles_count";a:3:{s:5:"basic";i:1;s:7:"improve";i:0;s:7:"measure";s:6:"method";}}'),
(18, 18, 'a:3:{s:23:"bonus_population_health";a:3:{s:5:"basic";i:5;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:24:"bonus_capacity_buildings";a:3:{s:5:"basic";i:100;s:7:"improve";i:1;s:7:"measure";s:1:"u";}s:23:"bonus_population_growth";a:3:{s:5:"basic";i:10;s:7:"improve";i:1;s:7:"measure";s:1:"u";}}'),
(19, 19, 'a:2:{s:24:"bonus_capacity_buildings";a:3:{s:5:"basic";i:1000;s:7:"improve";i:500;s:7:"measure";s:8:"resource";}s:26:"bonus_protection_buildings";a:3:{s:5:"basic";i:10;s:7:"improve";i:1;s:7:"measure";s:1:"u";}}'),
(20, 20, 'a:1:{s:23:"bonus_construction_unit";a:3:{s:5:"basic";i:0;s:7:"improve";i:-5;s:7:"measure";s:1:"u";}}'),
(21, 21, 'a:1:{s:23:"bonus_construction_unit";a:3:{s:5:"basic";i:0;s:7:"improve";i:-5;s:7:"measure";s:1:"u";}}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
