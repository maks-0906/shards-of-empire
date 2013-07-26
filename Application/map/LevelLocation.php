<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

/**
 * Description class
 *
 * @author Greg
 * @version 1.0.0
 * @package
 */
class map_LevelLocation extends Mapper {

	const TABLE_NAME = 'map_feature_levels_locations';

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @static
	 * @param string $className
	 * @return map_LevelLocation
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

	/**
	 * @return array <map_LevelLocation>
	 */
	public function getAllIdLevels()
	{
		return $this->findAll("SELECT `id_level_cell`, `count_in_comb` FROM %s ORDER BY `id_level_cell` ASC", $this->tableName());
	}
}