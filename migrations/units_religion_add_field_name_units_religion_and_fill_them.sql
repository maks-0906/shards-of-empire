--
-- 2013-04-01  vetalrakitin  <vetalrakitin@gmail.com>
--


ALTER TABLE  `units_religion` ADD `name_units_religion` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Название - ключ для религиозных юнитов для переводов';

UPDATE  `shards`.`units_religion` SET  `name_units_religion` =  'servant' WHERE  `units_religion`.`id_units_religion` =1;
UPDATE  `shards`.`units_religion` SET  `name_units_religion` =  'preacher' WHERE  `units_religion`.`id_units_religion` =2;
UPDATE  `shards`.`units_religion` SET  `name_units_religion` =  'harbinger' WHERE  `units_religion`.`id_units_religion` =3;
UPDATE  `shards`.`units_religion` SET  `name_units_religion` =  'druid' WHERE  `units_religion`.`id_units_religion` =4;
UPDATE  `shards`.`units_religion` SET  `name_units_religion` =  'keeper_secrets' WHERE  `units_religion`.`id_units_religion` =5;
