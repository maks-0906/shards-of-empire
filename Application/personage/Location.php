<?php
/**
 * Файл содержит класс модель, отображающую на таблицу владения локациями персонажем в БД.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель, отображающаяся на таблицу владения локациями персонажем в БД.
 * В таблице хранится информация по локации, владеемой персонажем.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_Location extends Mapper {

	const TABLE_NAME = 'personages_locations';

	/**
	 * Получение экземпляра сущности.
	 *
	 * @param string $className
	 * @return personage_Location
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	/**
	 * Отображение на таблицу в БД.
	 * @return string
	 */
	public function tableName()
	{
		return self::TABLE_NAME;
	}

	/**
	 * Поиск локаций, в которых находятся свои юниты.
	 *
	 * @param int $idPersonage
	 * @return array|personage_Location[]
	 */
	public function findLocationsWithCombatUnitsPersonage($idPersonage)
	{
		$sql = "SELECT `pul`.count, `u`.name_unit, `pl`.x_l as x, `pl`.y_l as y,  `pl`.id
				FROM %s as pul, %s as pl, %s as u
				WHERE `pul`.personage_id=%d AND `pul`.location_id=`pl`.id AND `pul`.unit_id=`u`.id
				GROUP BY `pul`.unit_id, `pl`.x_l, `pl`.y_l";

		return $this->findAll(
			$sql,
			personage_UnitLocation::TABLE_NAME, personage_Location::TABLE_NAME, unit_Mapper::TABLE_NAME,
			$idPersonage
		);
	}

	/**
	 * Поиск локации и информации о локации
	 * @param int $x
	 * @param int $y
	 * @return personage_Location|null
	 */
	public function findLocationByCoordinatesWithOwner($x, $y)
	{
		$sql = "SELECT `ps`.sympathy, `p`.nick, `ps`.id_dignity as total_level, `ps`.guild_id, `pl`.id
				FROM %s as pl, %s as p, %s as ps
				WHERE `pl`.x_l=%d AND `pl`.y_l=%d AND `pl`.personage_id=`p`.id AND `p`.id=`ps`.id_personage";
		return $this->find($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME, $x, $y);
	}

	/**
	 * Поиск локации с соединением запроса по боевым юнитам для персонажа.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $idPersonage
	 * @return array|personage_Location[]
	 */
	public function findCombatUnitsForLocation($x, $y, $idPersonage)
	{
		$sql = "SELECT
					`u`.name_unit, `plu`.count, `uc`.number_transported_cargo as carrying, `u`.combat_type,
					`u`.id as unit_id, `plu`.id as location_id
				FROM %s as pl, %s as plu, %s as u, %s as uc
				WHERE `pl`.x_l=%d AND `pl`.y_l=%d AND `plu`.personage_id=%d AND `u`.id=`uc`.unit_id
					AND `plu`.location_id=`pl`.id AND `plu`.unit_id=`u`.id";

		return $this->findAll(
			$sql,
			self::TABLE_NAME, personage_UnitLocation::TABLE_NAME,
			unit_Mapper::TABLE_NAME, unit_Characteristic::TABLE_NAME,
			$x, $y, $idPersonage
		);
	}
	
	/**
	 * Получение показателей боевых юнитов по ИД персонажа и ИД локации.
	 *
	 * @param int $idPersonage - ИД персонажа
	 * @param int $idLocation - ИД локации
	 * @return array|personage_Location[]
	 */
	public function getIndicatorsCombatUnitsByIdPersonageAndIdLocation($idPersonage, $idLocation)
	{
		$sql = "SELECT
					SUM(`uc`.`attack` * `plu`.`count`) as `attack`,
					SUM(`uc`.`life` * `plu`.`count`) as `life`,
					SUM(`uc`.`protection` * `plu`.`count`) as `protection`
				FROM 
					%s as pl, 
					%s as plu, 
					%s as u, 
					%s as uc
				WHERE 
					`plu`.personage_id=%d 
					AND `u`.id=`uc`.unit_id
					AND `plu`.location_id=`pl`.id 
					AND `plu`.unit_id=`u`.id 
					AND `pl`.id=%d
				GROUP BY
					`u`.id";

		return $this->find(
			$sql,
			self::TABLE_NAME, 
			personage_UnitLocation::TABLE_NAME,
			unit_Mapper::TABLE_NAME, 
			unit_Characteristic::TABLE_NAME,
			$idPersonage,
			$idLocation
		);
	}
	
	
	
	
	/**
	 * Определить, является ли локация городом
	 *
	 * @param int $idLocation - ИД локации
	 * @return boolean
	 */
	public function isCity($x, $y)
	{
		$sql = "
			SELECT `id`
			FROM `personages_cities`
			WHERE `x_c` = %d
				AND `y_c` = %d
			LIMIT 1
		";
		$result = $this->query($sql, $x, $y);
		return(!empty($result->__DBResult));
	}

	public function getLocationOwner($x, $y)
	{
		$sql = "
			SELECT `personage_id`
			FROM " . self::TABLE_NAME . "
			WHERE `x_l` = %d
				AND `y_l` = %d
			LIMIT 1
		";
		$result = $this->query($sql, $x, $y);
		if (!$result->__DBResult)
			return -1;
		
		return $result->__DBResult[0]['personage_id'];
	}

	/** TODO
	 * Проверяет, является ли локация мирной для персонажа
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $personageId
	 * @return bool
	 */
	public function isLocationIsPeaceful($x, $y, $personageId)
	{
		return false;
	}
        
        
        public function setLocationOwner($personageId, $x, $y)
	{
            $sql = "
                INSERT INTO " . self::TABLE_NAME . "
                SET `personage_id` = %d,
                    `x_l` = %d,
                    `y_l` = %d
                ON DUPLICATE KEY UPDATE
                    `personage_id` = %d
            ";
            $result = $this->query($sql, $personageId, $x, $y, $personageId);

            if ($this->isError())
                    throw new DBException('Failed update row in '.self::TABLE_NAME);

            return $this->getAffectedRows($result) > 0;
	}
        
        public function removeLocationOwner($x, $y)
        {
            $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE `x_l` = %d AND `y_l` = %d LIMIT 1;";
            $result = $this->query($sql, $x, $y);
            return $this->getAffectedRows($result) > 0;
        }
}
