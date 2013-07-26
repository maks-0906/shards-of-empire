-- --------------------------------------------------------
-- Хост:                         46.249.52.227
-- Версия сервера:               5.1.66-0+squeeze1 - (Debian)
-- ОС Сервера:                   debian-linux-gnu
-- HeidiSQL Версия:              7.0.0.4359
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for таблица shards.personages_guilds
DROP TABLE IF EXISTS `personages_guilds`;
CREATE TABLE IF NOT EXISTS `personages_guilds` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный ИД союза',
  `id_personage` int(11) NOT NULL DEFAULT '0' COMMENT 'ID - персонажа',
  `name` varchar(255) NOT NULL COMMENT 'Наименование союза',
  `type` enum('interfractional','fractional') NOT NULL COMMENT 'Тип союза ''interfractional'' - межфракционный,''fractional'' - фракционный',
  `experience` int(11) NOT NULL COMMENT 'Опыт',
  `level` int(11) NOT NULL COMMENT 'Уровень союза',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Союзы персонажа';

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
