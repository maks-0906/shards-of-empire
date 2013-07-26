-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 14 2013 г., 14:38
-- Версия сервера: 5.1.66-0+squeeze1
-- Версия PHP: 5.3.3-7+squeeze15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `shards`
--

-- --------------------------------------------------------

--
-- Структура таблицы `personages_resources_state`
--

CREATE TABLE IF NOT EXISTS `personages_resources_state` (
  `id_personages_resources_state` int(11) NOT NULL AUTO_INCREMENT,
  `id_personage` int(11) NOT NULL COMMENT 'ID персонажа',
  `personages_cities_id` int(11) DEFAULT NULL COMMENT 'ID города',
  `resource_id` int(11) NOT NULL COMMENT 'ID ресурса',
  `personage_resource_value` double NOT NULL COMMENT 'Текущие значения ресурсов у персонажа',
  `resource_consumption` float NOT NULL DEFAULT '0' COMMENT 'Расход ресурсов за час',
  `last_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Последний визит внешней программы',
  PRIMARY KEY (`id_personages_resources_state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Данные о ресурсах у персонажа' AUTO_INCREMENT=331 ;
