--
-- 2013-04-10  vetalrakitin  <vetalrakitin@gmail.com>
--
ALTER TABLE `personages_research_state`
	CHANGE COLUMN `research_finish_time` `research_finish_time` INT(11) NULL DEFAULT NULL
	COMMENT 'Окончание исследования' AFTER `current_level`;