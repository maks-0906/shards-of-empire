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

-- Дамп структуры для таблица game_test.personages_capital
DROP TABLE IF EXISTS `personages_capital`;
CREATE TABLE IF NOT EXISTS `personages_capital` (
  `id` int(11) NOT NULL COMMENT 'идентификатор записи',
  `resource_id` int(11) NOT NULL COMMENT 'идентификатор ресурса, который будет применяться в денежном эквиваленте',
  `personage_id` int(11) NOT NULL COMMENT 'идентификатор владельца',
  `count` int(11) NOT NULL COMMENT 'количество ресурсов',
  `last_visit` timestamp NULL DEFAULT NULL COMMENT 'последнее обновление для CRON',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ресурсы, являющиеся деньгами в игре для персонажа';

-- Дамп данных таблицы game_test.personages_capital: ~0 rows (приблизительно)
DELETE FROM `personages_capital`;
/*!40000 ALTER TABLE `personages_capital` DISABLE KEYS */;
/*!40000 ALTER TABLE `personages_capital` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
