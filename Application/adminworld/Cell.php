<?php
/**
 * Файл содержит класс модели к таблице БД хранящей ячейки к определённому шаблону карты.
 *
 * @author Greg
 * @package adminworld
 */

/**
 * Класс модель к таблице БД хранящей ячейки к определённому шаблону карты.
 * Таблица в БД содержит полную группу ячеек состовляющую единую карту.
 *
 * @author Greg
 * @version 1.0.0
 * @package adminworld
 */
class adminworld_Cell extends Mapper
{

	const TABLE_NAME = 'map_cells';

	/**
	 * Получение экземпляра сущности.
	 * @param string $className
	 * @return adminworld_Cell
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	/**
	 * Отношение модели к таблице.
	 * @return string
	 */
	public function tableName()
	{
		return self::TABLE_NAME;
	}

	/**
	 * Первичный ключ для текущей таблицы.
	 * @return string
	 */
	public function pk() { return 'id_cell';  }

	/**
	 * Поиск ячеек для частей видимой области карты по их координатам.
	 *
	 * @param int $idWorld
	 * @param array $map <map_PartVisibleMap>
	 * @return array
	 */
	public function findCellsOfCoordinatesMap($idWorld, array $map)
	{
		$countCondition = count($map);
		$condition = '';
		$i = 1;
		foreach($map as $part)
		{
			// Корректировка для BETWEEN, принимает только от меньшего к большему диапазоны
			if($part->y_0 <  $part->y_2){
				$y_0 = $part->y_0;
				$y_2 = $part->y_2;
			} else {
				$y_0 = $part->y_2;
				$y_2 = $part->y_0;
			}

			if($part->x_0 < $part->x_2) {
				$x_0 = $part->x_0;
				$x_2 = $part->x_2;
			} else {
				$x_0 = $part->x_2;
				$x_2 = $part->x_0;
			}

			$condition .= '(`mc`.y BETWEEN ' . $y_0 . ' AND ' . $y_2 . ')
							AND (`mc`.x BETWEEN ' . $x_0 . ' AND ' . $x_2 . ')';
			// Если условие ещё не последнее
			if($i < $countCondition)
			{
				$condition .= ' OR ';
				$i++;
			}
		}

		$sql = "SELECT
					`mc`.id_cell, `mc`.map_pattern, `mc`.id_fraction,
					`mc`.id_level_cell, `mc`.x, `mc`.y, `mc`.serial_number,
					`pc`.id as id_city, `pl`.id as id_location,
					`pb`.current_level as level_city
				FROM %s as mc
				LEFT OUTER JOIN %s as pc ON `pc`.x_c=`mc`.x AND `pc`.y_c=`mc`.y AND `pc`.id_personage=%d
				LEFT OUTER JOIN %s as pb ON `pb`.personage_id=%d AND `pb`.city_id=`pc`.id AND `pb`.building_id=%d
				LEFT OUTER JOIN %s as pl ON `pl`.x_l=`mc`.x AND `pl`.y_l=`mc`.y AND `pl`.personage_id=%d
				WHERE `mc`.id_world=%d AND (" . $condition . ")
				ORDER BY `mc`.y ASC, `mc`.x ASC";

		return $this->findAll(
			$sql,
			self::TABLE_NAME,
			personage_City::TABLE_NAME, Auth::getIdPersonage(),
			personage_Building::TABLE_NAME, Auth::getIdPersonage(), personage_Building::ID_BUILDING_CASTLE,
			personage_Location::TABLE_NAME, Auth::getIdPersonage(),
			(int) $idWorld
		);
	}

	/**
	 * Поиск руины в сотах с наименьшим количеством игроков с ограничением по типу определённого мира.
	 *
	 * @param int $idWorld
	 * @return adminworld_Cell
	 */
	public function findCellRuinInCombsWithFewestNumberOfPersonages($idWorld)
	{
		$sql = "SELECT mc.id_cell, mc.x, mc.y, mc.serial_number, mc.map_pattern FROM %1\$s as mc
				WHERE mc.id_world=%2\$d AND mc.map_pattern=%3\$d
					AND mc.id_fraction=(SELECT f.id
						FROM %4\$s f
						LEFT OUTER JOIN
						  (SELECT ps.fraction_id, count(ps.id_personage) as ct
						  FROM %5\$s ps, %6\$s p
						  WHERE ps.id_personage = p.id AND p.world_id=%2\$d
						  GROUP BY 1
						  ORDER BY 2
						  ) pps
						ON (f.id=pps.fraction_id)
						ORDER BY pps.ct
						LIMIT 1)
				ORDER BY mc.serial_number ASC
				LIMIT 1";

		$ruin = $this->find(
			$sql,
			$this->tableName(),
			$idWorld, pattern_Mapper::RUIN_ID_PATTERN,
			personage_Fraction::model()->tableName(),
			personage_State::model()->tableName(), personage_Mapper::model()->tableName()
		);

		return $ruin;
	}

	/**
	 * @param adminworld_Comb $comb
	 * @return adminworld_Cell|null
	 */
	public function findFreeCellRequiredComb(adminworld_Comb $comb)
	{
		$sql = "SELECT * FROM %s WHERE `id_comb`=%d AND `id_world`=%d AND `map_pattern`=%d";

		return $this->find(
			$sql,
			self::TABLE_NAME, $comb->id_comb, $comb->id_map_template, pattern_Mapper::RUIN_ID_PATTERN);
	}

	/**
	 * Поиск локации в определённом мире по координатам и определение "хозяина" локации.
	 * @TODO Добавить определение локации из будущей таблицы локаций для персонажа. Может просто изменить таблицу personages_cities и в ней хранит и локации?
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $idWorld
	 * @return adminworld_Cell
	 */
	public static function findCellByCoordinatesAndDetectOwnerLocation($x, $y, $idWorld)
	{
		$sql = "SELECT `mc`.*, `pc`.id_personage, `pc`.city_name, `mc`.map_pattern as pattern
				FROM %1\$s as mc
				LEFT OUTER JOIN %5\$s as pc ON `pc`.x_c=%2\$d AND `pc`.y_c=%3\$d
				WHERE `x`=%2\$d AND `y`=%3\$d AND `id_world`=%4\$d";

		return self::model()->find($sql, self::TABLE_NAME, $x, $y, $idWorld, personage_City::TABLE_NAME);
	}

	/**
	 * Определение локации типа её и хозяина локации.
	 * Отличие от предыдущего метода, производится поиск и в таблице personages_locations
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $idWorld
	 * @return adminworld_Cell
	 */
	public function detectLocationAndHireOwner($x, $y, $idWorld)
	{
		$sql = "SELECT
					`mc`.*, `pc`.id_personage as personage_id_city,
				    `mc`.map_pattern as pattern, `pl`.personage_id as personage_id_location,
				    `mll`.production_bonus, `mll`.time_of_bonus
				FROM %1\$s as mc
				LEFT OUTER JOIN %5\$s as pc ON `pc`.x_c=%2\$d AND `pc`.y_c=%3\$d
				LEFT OUTER JOIN %6\$s as pl ON `pl`.x_l=%2\$d AND `pl`.y_l=%3\$d
				LEFT OUTER JOIN %7\$s as mll ON `mc`.id_level_cell=`mll`.id_level_cell
				WHERE `x`=%2\$d AND `y`=%3\$d AND `id_world`=%4\$d";

		return self::model()->find($sql, self::TABLE_NAME, $x, $y, $idWorld, personage_City::TABLE_NAME,
			                             personage_Location::TABLE_NAME, map_LevelLocation::TABLE_NAME
		);
	}
}
