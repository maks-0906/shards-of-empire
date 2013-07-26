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
class map_Comb extends Mapper {

	/**
	 * Returns the static model of the specified AR class.
	 *
	 * @static
	 * @param string $className
	 * @return map_Comb
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
		return 'map_group_cells';
	}

	/**
	 * @param int $idWorld
	 * @param array $map
	 * @return array
	 */
	public function findCombsOfCoordinatesMap($idWorld, array $map)
	{
		$t1 = $map['t1']; $t2 = $map['t2']; $t3 = $map['t3']; $t4 = $map['t4'];
		$countCondition = count($map);
		$condition = '';
		for($i = 0; $i < $countCondition; $i++)
		{
			$condition .= '(`y_0` <= %d AND `y_1` >= %d) AND (`x_0` <= %d AND `x_1` >= %d)';
			if($i < $countCondition) $condition .= ' OR ';
		}

		$sql = "SELECT levels_cells, y_0, x_0, y_1, x_1
				FROM %s WHERE `id_map_template`=%d
				AND ((`y_0` <= %d AND `y_1` >= %d) AND (`x_0` <= %d AND `x_1` >= %d)
				OR (`y_0` <= %d AND `y_1` >= %d) AND (`x_0` <= %d AND `x_1` >= %d)
				OR (`y_0` <= %d AND `y_1` >= %d) AND (`x_0` <= %d AND `x_1` >= %d)
				OR (`y_0` <= %d AND `y_1` >= %d) AND (`x_0` <= %d AND `x_1` >= %d)) ORDER BY `id_group_cell` DESC
				";

		return $this->findAll(
			$sql,
			$this->tableName(), (int) $idWorld,
			$t1['y_0'], $t1['y_1'], $t1['x_0'], $t1['x_1'],
			$t2['y_0'], $t2['y_1'], $t2['x_0'], $t2['x_1'],
			$t3['y_0'], $t3['y_1'], $t3['x_0'], $t3['x_1'],
			$t4['y_0'], $t4['y_1'], $t4['x_0'], $t4['x_1']
		);
	}
}