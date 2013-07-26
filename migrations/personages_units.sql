-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 09 2013 г., 11:10
-- Версия сервера: 5.1.66-0+squeeze1
-- Версия PHP: 5.3.3-7+squeeze15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `shards`
--

-- --------------------------------------------------------

--
-- Структура таблицы `personages_units`
--

CREATE TABLE IF NOT EXISTS `personages_units` (
  `id_unit_personage` int(11) NOT NULL AUTO_INCREMENT,
  `id_building_personage` int(11) NOT NULL COMMENT 'Идентификатор здания персонажа',
  `unit_id` int(11) NOT NULL COMMENT 'Идентификатор юнита',
  `count` int(11) NOT NULL COMMENT 'Количество юнитов',
  `time_rent` int(11) NOT NULL DEFAULT '0' COMMENT 'Время найма единицы юнита в секундах',
  `finish_time_rent` timestamp NULL DEFAULT NULL COMMENT 'Время окончания найма',
  `status` enum('hired','hiring','cancel','notstarted') NOT NULL COMMENT 'Статус найма юнита',
  PRIMARY KEY (`id_unit_personage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Юниты персонажа' AUTO_INCREMENT=203 ;
