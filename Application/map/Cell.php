<?php
/**
 * Файл содержит класс модель, управляющая ячейками карты.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package
 */

/**
 * Класс модель, управляющая ячейками карты.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package
 */
class map_Cell extends Mapper {


	const TABLE_NAME = 'map_cells';
	
	// ИД типа локации "Город"
	const KEY_PATTERN_CITY = "9";
	
	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @static
	 * @param string $className
	 * @return map_Cell
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	/**
	 * Ассоциация с реальной таблицей в БД.
	 *
	 * @return string
	 */
	public function tableName()
	{
		return self::TABLE_NAME;
	}
}
