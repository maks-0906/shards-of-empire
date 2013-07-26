-- Author: Greg
ALTER TABLE `personages_state`
	ADD COLUMN `sympathy` INT(11) NOT NULL DEFAULT '0' COMMENT 'Параметр персонажа Симпатия' AFTER `luck`;