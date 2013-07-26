<?php
/**
 * Файл содержит класс модель, управляющая характеристиками юнитов-шпионов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */
 
/**
 * Класс модель, управляющая характеристиками юнитов-шпионов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package unit
 */ 
class unit_Spy extends Mapper {

	const TABLE_NAME = 'units_spy';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_Spy
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
     * Запрос на поиск юнитов-шпионов
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return unit_Spy
     */
    public function findAllUnitsByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "SELECT 
				`id_units_spy` as `id_unit`,
				`name_units_spy` as `name_unit`, 
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%4\$d' 
				AND `unit_id` = `id_units_spy`) as `hired`,
				`number_available_spy` as `available`,
				'spy' as `unit_type`,
				IF (`level_building_tavern` <= 
				(SELECT `current_level` 
				FROM `%3\$s` 
				WHERE `id_building_personage` = '%4\$d'), '1', '0') as 'is_hired'
			FROM `%1\$s`";

        return $this->findAll($sql,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idPersonageBuilding
        );
    }
    
    /**
     * Проверка доступности юнитов-шпионов для найма
     * 
     * @param $unitId - ИД юнита
     * @param $unitCount - количество юнитов
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return boolean
     */
    public function checkAvailabilityUnitsForHiring($unitId, $unitCount, $idPersonageBuilding)
    {
        $sql = "
			SELECT 
				`id_units_spy` as `id_unit`,
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%4\$d' 
				AND `unit_id` = `id_units_spy`) as `hired`,
				
				`number_available_spy` as `available`,
				
				IF (`level_building_tavern` <= 
				(SELECT `current_level` 
				FROM `%3\$s` 
				WHERE `id_building_personage` = '%4\$d'), '1', '0') as 'is_hired'
			FROM 
				`%1\$s`
			WHERE
				`id_units_spy` = '%5\$d'";

        $result = $this->find($sql,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idPersonageBuilding,
            $unitId
        );

        return !$this->isEmptyResult() 
			& $result->is_hired 
			& (($result->hired + $unitCount) <= $result->available);
    }
    
    
	/**
	 * Поиск юнитов, которые находятся в очереди, по ИД здания персонажа
	 * 
	 * @param $idPersonageBuilding - ИД здания персонажа
	 * @return unit_Spy
	 */
    public function findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding)
    {
		$sql = "
			SELECT 
				PU.`id_unit_personage`,
				`UK`.`name_units_spy` as `name_unit`,
				`PU`.`count` as `count_unit`,
				`PU`.`finish_time_rent` as `finish_time`
			FROM `%1\$s` as PU
			LEFT JOIN `%4\$s` as UK ON `UK`.`id_units_spy` = `PU`.`unit_id`
			WHERE `PU`.`id_building_personage` = '%2\$d' 
			AND `PU`.`status` = '%3\$s'";

        return $this->findAll(
			$sql, 
			personage_Unit::TABLE_NAME, 
			$idPersonageBuilding,
			personage_Unit::STATUS_HIRE_PROCESSING,
			self::TABLE_NAME
		);
	}
	
	/**
	 * Найм юнита-шпиона, по ИД здания персонажа, ИД юнита
	 * 
	 * @param $idPersonageBuilding - ИД здания персонажа
	 * @param $unitId - ИД юнита
	 * @param $unitCount - Количество юнитов
	 * @return boolean
	 */
    public function hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount)
    {
		$sql = "
			INSERT INTO 
				`%1\$s` 
			SET 
				`id_building_personage` = '%2\$d',
				`unit_id` = '%3\$d',
				`count` = '%4\$d',
				`finish_time_rent` = NOW(),
				`status` = '%5\$s'";
	
        $personageUnit = $this->query(
			$sql, 
			personage_Unit::TABLE_NAME, 
			$idPersonageBuilding,
			$unitId,
			$unitCount,
			personage_Unit::STATUS_HIRE_FINISH
		);
			
		if($personageUnit->isError())
			return false;
		else
			return true;
	}
}
