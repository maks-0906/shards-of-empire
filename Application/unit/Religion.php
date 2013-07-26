<?php
/**
 * Файл содержит класс модель, управляющая характеристиками Религиозные юниты.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */
 
/**
 * Класс модель, управляющая характеристиками Религиозные юниты.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package unit
 */ 
class unit_Religion extends Mapper {

	const TABLE_NAME = 'units_religion';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_Religion
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Отображение на таблицу в БД.
     * 
     * @return string
     */
    public function tableName()
    {
        return self::TABLE_NAME;
    }
    
    /**
     * Запрос на поиск доступных религиозных юнитов 
     * 
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return unit_Religion
     */
    public function findAllUnitsByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "SELECT 
				`id_units_religion` as `id_unit`,
				`name_units_religion` as `name_unit`, 
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%5\$d' 
				AND `unit_id` = `id_units_religion`) as `hired`,
				(SELECT `current_level` 
				FROM  `%3\$s` 
				WHERE `id_building_personage` = '%5\$d')  as `available`,
				'religion' as `unit_type`,
				IF (`id_building_upgrade` IN 
				(SELECT `id_building_upgrade` 
				FROM  `%4\$s` 
				WHERE `status` = 'finish' AND
				`id_building_personage` = '%5\$d'), '1', '0') as 'is_hired'
			FROM `%1\$s`";
		
        return $this->findAll($sql,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            personage_Improve::TABLE_NAME,
            $idPersonageBuilding
        );
	}
    
    /**
     * Проверка доступности юнитов-религиозников для найма
     * 
     * @param $unitId - ИД юнита
     * @param $unitCount - количество юнитов
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return boolean
     */
    public function checkAvailabilityUnitsForHiring($unitId, $unitCount, $idPersonageBuilding)
    {
        $sql = "SELECT 
				`id_units_religion` as `id_unit`,
 
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%5\$d' 
				AND `unit_id` = `id_units_religion`) as `hired`,
				
				(SELECT `current_level` 
				FROM  `%3\$s` 
				WHERE `id_building_personage` = '%5\$d')  as `available`,

				IF (`id_building_upgrade` IN 
				(SELECT `id_building_upgrade` 
				FROM  `%4\$s` 
				WHERE `status` = 'finish' AND
				`id_building_personage` = '%5\$d'), '1', '0') as 'is_hired'
			FROM 
				`%1\$s`
			WHERE
				`id_units_religion` = '%6\$d'";
		
        $result = $this->find($sql,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            personage_Improve::TABLE_NAME,
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
	 * @return unit_Religion
	 */
    public function findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding)
    {
		$sql = "
			SELECT 
				PU.`id_unit_personage`,
				`UK`.`name_units_religion` as `name_unit`,
				`PU`.`count` as `count_unit`,
				`PU`.`finish_time_rent` as `finish_time`
			FROM `%1\$s` as PU
			LEFT JOIN `%4\$s` as UK ON `UK`.`id_units_religion` = `PU`.`unit_id`
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
	 * Найм юнита-религиозника, по ИД здания персонажа, ИД юнита
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
