<?php
require_once ROOT . '/Application/personage/Religion.php';

/**
 * Файл содержит фикстуры для таблицы `personages_religion`
 *
 * @author Greg
 * @package tests
 */
return array(
    'religion1' => array(
        'id' => 1,
		'name' => 'Древо предела',
		'description' => 'Поклоняются дереву',
		'img' => 'img/img/',
		'bonus' => 5
    ),
    'religion2' => array(
        'id' => 2,
  		'name' => 'Богиня плодородия',
  		'description' => 'Поклоняются плодородию',
  		'img' => 'img/img/',
  		'bonus' => 5
      ),
    'religion3' => array(
        'id' => 3,
  		'name' => 'Дети Солнца',
  		'description' => 'Поклоняются солнцу',
  		'img' => 'img/img/',
  		'bonus' => 5
      ),
    'religion4' => array(
        'id' => 4,
  		'name' => 'Разлив Нила',
  		'description' => 'Поклоняются реке Нил',
  		'img' => 'img/img/',
  		'bonus' => 5
      ),
);