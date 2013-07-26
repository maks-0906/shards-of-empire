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

-- Дамп структуры для таблица game_test.personages_locations
DROP TABLE IF EXISTS `personages_locations`;
CREATE TABLE IF NOT EXISTS `personages_locations` (
  `id` int(11) NOT NULL COMMENT 'идентификатор записи',
  `personage_id` int(11) NOT NULL COMMENT 'владелец локации',
  `x_l` smallint(5) NOT NULL COMMENT 'координата локации по X',
  `y_l` smallint(6) NOT NULL COMMENT 'координата локации по Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица сохраняет локации и их информацию для персонажа';

-- Дамп данных таблицы game_test.personages_locations: ~0 rows (приблизительно)
DELETE FROM `personages_locations`;
/*!40000 ALTER TABLE `personages_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `personages_locations` ENABLE KEYS */;


-- Дамп структуры для таблица game_test.personages_units_locations
DROP TABLE IF EXISTS `personages_units_locations`;
CREATE TABLE IF NOT EXISTS `personages_units_locations` (
  `id` int(11) NOT NULL COMMENT 'идентификатор записи',
  `unit_id` int(11) NOT NULL COMMENT 'идентификатор юнита из таблицы units',
  `personage_id` int(11) NOT NULL COMMENT 'идентификатор владельца юнитов',
  `location_id` smallint(11) DEFAULT NULL COMMENT 'идентификатор принадлежности к локации таб. personages_locations',
  `count` smallint(5) NOT NULL COMMENT 'численность юнитов',
  `garrison` int(11) DEFAULT NULL COMMENT 'гарнизон, к которому привязан юнит ??? (требуется ли)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Привязка юнитов к локации на карте к (personages_locations)';

-- Дамп данных таблицы game_test.personages_units_locations: ~0 rows (приблизительно)
DELETE FROM `personages_units_locations`;
/*!40000 ALTER TABLE `personages_units_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `personages_units_locations` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
