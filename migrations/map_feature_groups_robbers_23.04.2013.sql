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

-- Дамп структуры для таблица game_test.map_feature_groups_robbers
DROP TABLE IF EXISTS `map_feature_groups_robbers`;
CREATE TABLE IF NOT EXISTS `map_feature_groups_robbers` (
  `id_feature_robber` int(11) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор характеристики группы разбойников',
  `id_level_property_location` int(11) NOT NULL COMMENT 'идентификатор свойства локации',
  `id_unit` int(11) NOT NULL COMMENT 'идентификатор юнита входящего в группу разбойников',
  `count_robbers` int(11) NOT NULL COMMENT 'количество разбойников для данной характеристики',
  PRIMARY KEY (`id_feature_robber`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='Характеристика отряда разбойников для каждого уровня локации';

-- Дамп данных таблицы game_test.map_feature_groups_robbers: ~37 rows (приблизительно)
DELETE FROM `map_feature_groups_robbers`;
/*!40000 ALTER TABLE `map_feature_groups_robbers` DISABLE KEYS */;
INSERT INTO `map_feature_groups_robbers` (`id_feature_robber`, `id_level_property_location`, `id_unit`, `count_robbers`) VALUES
	(1, 1, 1, 100),
	(2, 2, 1, 100),
	(3, 2, 2, 50),
	(4, 3, 1, 50),
	(5, 3, 2, 100),
	(6, 3, 3, 50),
	(7, 4, 2, 100),
	(8, 4, 3, 100),
	(9, 4, 4, 50),
	(10, 5, 2, 100),
	(11, 5, 3, 100),
	(12, 5, 4, 100),
	(13, 5, 5, 100),
	(14, 6, 3, 50),
	(15, 6, 4, 150),
	(16, 6, 5, 100),
	(17, 6, 6, 100),
	(18, 7, 1, 100),
	(19, 7, 4, 100),
	(20, 7, 5, 100),
	(21, 7, 6, 100),
	(22, 7, 7, 50),
	(23, 8, 1, 200),
	(24, 8, 5, 100),
	(25, 8, 6, 100),
	(26, 8, 7, 50),
	(27, 8, 8, 50),
	(28, 9, 1, 300),
	(29, 9, 5, 100),
	(30, 9, 6, 100),
	(31, 9, 7, 50),
	(32, 9, 8, 50),
	(33, 10, 1, 400),
	(34, 10, 5, 100),
	(35, 10, 6, 100),
	(36, 10, 7, 50),
	(37, 10, 8, 50);
/*!40000 ALTER TABLE `map_feature_groups_robbers` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
