ALTER TABLE `personages_buildings`
	CHANGE COLUMN `status_construction` `status_construction`
	ENUM('processing','finish','notstarted','cancel')
	NOT NULL DEFAULT 'notstarted'
	COMMENT 'Статус строительства здания' AFTER `finish_time_construction`;
	
ALTER TABLE `personages_building_improve`
	CHANGE COLUMN `status` `status`
	ENUM('process','finish','notstarted','cancel')
	NOT NULL DEFAULT 'notstarted'
	COMMENT 'Статус изучения улучшения.' AFTER `finish_time_improve`;