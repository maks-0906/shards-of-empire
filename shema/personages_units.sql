--
-- Структура таблицы `personages_units`
--

CREATE TABLE IF NOT EXISTS `personages_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_building_personage` int(11) NOT NULL COMMENT 'Идентификатор здания персонажа',
  `unit_id` int(11) NOT NULL COMMENT 'Идентификатор юнита',
  `count` int(11) NOT NULL COMMENT 'Количество юнитов',
  `finish_time_rent` timestamp NULL DEFAULT NULL COMMENT 'Время окончания найма',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Юниты персонажа' AUTO_INCREMENT=1 ;
