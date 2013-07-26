-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Май 22 2013 г., 11:14
-- Версия сервера: 5.1.66-0+squeeze1
-- Версия PHP: 5.3.3-7+squeeze15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `shards`
--

-- --------------------------------------------------------

--
-- Структура таблицы `fights`
--

CREATE TABLE IF NOT EXISTS `fights` (
  `id_fight` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный ИД боя',
  `id_initiator` int(11) NOT NULL COMMENT 'ИД персонажа инициатора боя',
  `id_defender` int(11) NOT NULL COMMENT 'ИД персонажа, который защищает локацию',
  `id_location` int(11) NOT NULL COMMENT 'ИД локации, в которой происходит бой',
  `finish_time` datetime NOT NULL COMMENT 'Время окончания текущего статуса',
  `status` enum('notstarted','waitingallied','started','finished') NOT NULL DEFAULT 'notstarted' COMMENT 'Статус боя (waitingallied - ожидание союзников)',
  `target_fight` enum('attack','protection','attack_tacking') NOT NULL COMMENT 'Цель боя',
  PRIMARY KEY (`id_fight`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Бои' AUTO_INCREMENT=4 ;
