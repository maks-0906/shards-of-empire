<?php

require_once ROOT . '/Application/personage/Mapper.php';

/**
 * Файл содержит фикстуры для таблицы `personages`
 *
 * @author Greg
 * @package tests
 */
return array(
    'personage1' => array(
        'user_id' => 14,
		'nick' => 'Personage 1',
		'world_id' => 1,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
    ),
	'personage2' => array(
		'user_id' => 42,
		'nick' => 'Personage 2',
		'world_id' => 0,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
	),
	'personage3' => array(
		'user_id' => 33,
        'nick' => 'Personage 3',
		'world_id' => 0,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
	),
	'personage4' => array(
		'user_id' => 1,
		'nick' => 'Personage 4',
		'world_id' => 1,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
	),
	'personage5' => array(
		'user_id' => 51,
		'nick' => 'Personage 5',
		'world_id' => 0,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
	),
	'personage6' => array(
		'user_id' => 16,
		'nick' => 'Personage 6',
		'world_id' => 1,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
	),
	'personage7' => array(
		'user_id' => 1,
		'nick' => 'Personage 7',
		'world_id' => 0,
		'finish_banned' => null,
        'time_online' => 0,
		'status' => personage_Mapper::NORMAL_STATUS
	),
);
