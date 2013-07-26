--
-- Структура таблицы `units_artists`
--

CREATE TABLE IF NOT EXISTS `units_artists` (
  `id_units_artists` int(11) NOT NULL AUTO_INCREMENT,
  `id_building_upgrade` int(11) DEFAULT NULL COMMENT 'ID - таблицы building_upgrade',
  `number_points_happiness` int(11) NOT NULL COMMENT 'Количествово очков счастья производимого юнитом ',
  `name_units_artists` varchar(100) NOT NULL COMMENT 'Название - ключ дляюнитов артистов для переводов',
  `level_building` int(11) DEFAULT NULL COMMENT 'Требуемый уровень здания',
  PRIMARY KEY (`id_units_artists`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Характеристика юнитов-артистов' AUTO_INCREMENT=11 ;

--
-- Дамп данных таблицы `units_artists`
--

INSERT INTO `units_artists` (`id_units_artists`, `id_building_upgrade`, `number_points_happiness`, `name_units_artists`, `level_building`) VALUES
(1, NULL, 1, ' jester', 1),
(2, 133, 4, 'flutist', NULL),
(3, NULL, 3, 'clowns', 5),
(4, 134, 6, 'reciter_poetry', NULL),
(5, NULL, 4, 'animal_handlers', 9),
(6, 135, 8, 'thespian', NULL),
(7, NULL, 5, 'bard', 13),
(8, 136, 10, 'puppet_theatre', NULL),
(9, NULL, 8, 'orchestra', 17),
(10, 137, 12, 'theater_company', NULL);
