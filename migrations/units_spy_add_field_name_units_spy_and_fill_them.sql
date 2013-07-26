--
-- 2013-04-03  vetalrakitin  <vetalrakitin@gmail.com>
--


ALTER TABLE  `units_spy` ADD  `name_units_spy` VARCHAR( 100 ) NOT NULL COMMENT  'Название - ключ для юнитов-шпионов для переводов';

UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_1' WHERE  `units_spy`.`id_units_spy` =1;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_2' WHERE  `units_spy`.`id_units_spy` =2;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_3' WHERE  `units_spy`.`id_units_spy` =3;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_4' WHERE  `units_spy`.`id_units_spy` =4;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_5' WHERE  `units_spy`.`id_units_spy` =5;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_6' WHERE  `units_spy`.`id_units_spy` =6;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_7' WHERE  `units_spy`.`id_units_spy` =7;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_8' WHERE  `units_spy`.`id_units_spy` =8;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_9' WHERE  `units_spy`.`id_units_spy` =9;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_10' WHERE  `units_spy`.`id_units_spy` =10;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_11' WHERE  `units_spy`.`id_units_spy` =11;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_12' WHERE  `units_spy`.`id_units_spy` =12;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_13' WHERE  `units_spy`.`id_units_spy` =13;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_14' WHERE  `units_spy`.`id_units_spy` =14;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_15' WHERE  `units_spy`.`id_units_spy` =15;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_16' WHERE  `units_spy`.`id_units_spy` =16;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_17' WHERE  `units_spy`.`id_units_spy` =17;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_18' WHERE  `units_spy`.`id_units_spy` =18;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_19' WHERE  `units_spy`.`id_units_spy` =19;
UPDATE  `shards`.`units_spy` SET  `name_units_spy` =  'spy_lev_20' WHERE  `units_spy`.`id_units_spy` =20;

