-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.1.65-community-log - MySQL Community Server (GPL)
-- ОС Сервера:                   Win32
-- HeidiSQL Версия:              7.0.0.4364
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица game_test.units_moving
DROP TABLE IF EXISTS `units_moving`;
CREATE TABLE IF NOT EXISTS `units_moving` (
  `id` int(11) NOT NULL COMMENT 'Текущий идентификатор отряда юнитов на рвемя перемещения',
  `personage_id` int(11) NOT NULL COMMENT 'Владелец отряда юнитов',
  `x_s` smallint(5) NOT NULL COMMENT 'Координата расположения локации отправки отряда по X',
  `y_s` smallint(5) NOT NULL COMMENT 'Координата расположения локации отправки отряда по Y',
  `x_d` smallint(5) NOT NULL COMMENT 'Координата расположения локации назначения отряда по X',
  `y_d` smallint(5) NOT NULL COMMENT 'Координата расположения локации назначения отряда по Y',
  `distance` int(11) NOT NULL COMMENT 'Расстояние между локациями',
  `speed` smallint(3) NOT NULL COMMENT 'Скорость передвижения',
  `units` text NOT NULL COMMENT 'Сериализованный массив с данными по перемещаемым юнитам',
  `resources` text COMMENT 'Сериализованный масив с данными по ресурсам, прикреплённым к отряду',
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Стартовое время передвижения отряда',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Конечное время передвижения отряда',
  `status` enum('notstarted','moving','cancel','finish') NOT NULL DEFAULT 'notstarted' COMMENT 'Статус состояния передвижения отряда',
  `target` enum('attack','protection') NOT NULL DEFAULT 'protection' COMMENT 'Цель перемедвижения отряда',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Сохранение состояния перемещения отрядов юнитов';

-- Дамп данных таблицы game_test.units_moving: ~0 rows (приблизительно)
DELETE FROM `units_moving`;
/*!40000 ALTER TABLE `units_moving` DISABLE KEYS */;
/*!40000 ALTER TABLE `units_moving` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
