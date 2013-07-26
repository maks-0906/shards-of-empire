--
-- 18.04.2013 15:21 Greg
--
ALTER TABLE `personages_locations`
	ADD COLUMN `pattern` TINYINT(2) NOT NULL COMMENT 'паттерн которому соответствует локация' AFTER `y_l`;