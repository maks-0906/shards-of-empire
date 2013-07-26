-- --------------------------------------------------------
-- Хост:                         46.249.52.227
-- Версия сервера:               5.1.66-0+squeeze1 - (Debian)
-- ОС Сервера:                   debian-linux-gnu
-- HeidiSQL Версия:              7.0.0.4364
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица shards.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(11) NOT NULL,
  `recovery_code` varchar(255) DEFAULT NULL,
  `last_time_recovery` int(15) DEFAULT NULL,
  `count_recovery` int(1) NOT NULL DEFAULT '0',
  `reg_ip` varchar(80) NOT NULL,
  `last_online` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('normal','banned','delete') NOT NULL DEFAULT 'normal',
  `lang` enum('ru','en') NOT NULL DEFAULT 'ru',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Пользователи';

-- Экспортируемые данные не выделены.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
