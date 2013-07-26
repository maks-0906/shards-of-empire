-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Апр 19 2013 г., 10:27
-- Версия сервера: 5.1.66-0+squeeze1
-- Версия PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `shards`
--

-- --------------------------------------------------------

--
-- Структура таблицы `personages_buildings`
--

CREATE TABLE IF NOT EXISTS `personages_buildings` (
  `id_building_personage` int(11) NOT NULL AUTO_INCREMENT,
  `personage_id` int(11) NOT NULL COMMENT 'Идентификатор персонажа',
  `city_id` int(11) NOT NULL COMMENT 'Идентификатор города, к которому принадлежит здание',
  `building_id` int(11) NOT NULL COMMENT 'Идентификатор здания',
  `current_level` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'Текущий уровень здания',
  `finish_time_construction` timestamp NULL DEFAULT NULL COMMENT 'Время окончания постройки повышения уровня постройки',
  `status_construction` enum('processing','finish','notstarted','cancel') NOT NULL DEFAULT 'notstarted' COMMENT 'Статус строительства здания',
  `status_production` enum('stop','production') NOT NULL DEFAULT 'production' COMMENT 'stop - остановка, production - выроботка',
  `performance` int(11) NOT NULL DEFAULT '0' COMMENT 'Производительность ресурсного здания',
  PRIMARY KEY (`id_building_personage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Здания персонажа' AUTO_INCREMENT=255 ;
