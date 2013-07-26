<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mapper
 *
 * @author al
 */
class pattern_Mapper extends Mapper {

	const RUIN_ID_PATTERN = 8;
	const CITY_PATTERN = 9;

	/**
	 * Получение экземпляра сущности.
	 *
	 * @param string $className
	 * @return pattern_Mapper
	 */
	public static function model($className=__CLASS__)
	{
		return new $className();
	}

	/**
	 * Имя таблицы в БД для отображения.
	 * @return string
	 */
	public function tableName()
	{
		return 'patternlist';
	}

    public function findPatternList() {
        return $this->findAll('SELECT * FROM `patternlist`');
	}

	/**
	 * Форматирование списка паттернов в обычный массив для ответа в JSON.
	 *
	 * @param pattern_Mapper[] $patterns
	 * @return array
	 */
	public static function formatJSONResponsePatterns(array $patterns)
	{
		$response = array();
		foreach($patterns as $pattern)
			array_push($response, $pattern->properties);

		return $response;
	}
}
