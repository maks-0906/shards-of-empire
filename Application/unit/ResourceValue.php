<?php
/**
 * Файл содержит класс модель, управляющая необходимыми ресурсами для найма юнитов-воинов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */
 
/**
 * Класс модель, управляющая необходимыми ресурсами для найма юнитов-воинов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package unit
 */ 
class unit_ResourceValue extends Mapper {

	const TABLE_NAME = 'units_resources_value';

	const TYPE_ACTION_COST = 'cost_unit';
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_ResourceValue
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
     * Запрос на поиск необходимых ресурсов для найма юнитов-воинов
     * 
     * @param $idUnit - ИД юнита
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return unit_ResourceValue
     */
    public function findAllById($idUnit, $idPersonageBuilding)
    {
        $sql = "
			SELECT 
				`N`.`name_resource` as `resource_name`,
				`V`.`value` as `required`,
				FLOOR(`PRS`.`personage_resource_value`) as `has`
			FROM `%1\$s` as V
			LEFT JOIN `%2\$s` as N ON `N`.`id` = `V`.`resource_id`
			LEFT JOIN `%3\$s` as PRS ON `PRS`.`resource_id` = `V`.`resource_id`
			LEFT JOIN `%4\$s` as PB ON (`PB`.`city_id` = `PRS`.`personages_cities_id` 
				OR `PRS`.`personages_cities_id` IS NULL)
			AND `PB`.`personage_id` = `PRS`.`id_personage` 
			WHERE 
				`V`.`units_characteristics_id` = (SELECT `id` FROM `%7\$s` WHERE `unit_id` = '%5\$d')
				AND `PB`.`id_building_personage` = '%6\$d'
				AND `V`.`type` = '%8\$s'";

        return $this->findAll($sql,
            self::TABLE_NAME,
            resource_Mapper::TABLE_NAME,
            personage_ResourceState::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idUnit,
            $idPersonageBuilding,
            unit_Characteristic::TABLE_NAME,
            self::TYPE_ACTION_COST
        );
    }
    
    /**
     * Списание ресурсов за найм юнитов-воинов
     * 
     * @param $idUnit - ИД юнита
     * @param $unitCount - Количество юнитов
     * @param $idPersonage - ИД персонажа
     * @param $idPersonageCity - ИД города персонажа
     * @return boolean
     */
    public function writeOffResourceForHiring($unitId, $unitCount, $idPersonage, $idPersonageCity)
    {
		$sql = "
			UPDATE 
				`%1\$s` PRS
			INNER JOIN 
				`%2\$s` URV  ON PRS.`resource_id` = URV.`resource_id`
			SET 
				PRS.`personage_resource_value` = PRS.`personage_resource_value` - URV.`value` * '%3\$d'
			WHERE 
				PRS.`id_personage` = '%4\$d' AND
				(PRS.`personages_cities_id` = '%5\$d' 
				OR PRS.`personages_cities_id` IS NULL) AND
				URV.`units_characteristics_id` IN (SELECT `id` FROM `%8\$s` WHERE `unit_id` = '%6\$d') AND
				URV.`type` = '%7\$s'";
		
		$result = $this->query(
			$sql,
			personage_ResourceState::TABLE_NAME,
			self::TABLE_NAME,
			$unitCount,
			$idPersonage,
			$idPersonageCity,
			$unitId,
			self::TYPE_ACTION_COST,
			unit_Characteristic::TABLE_NAME
		);
		
		if ($result->isError()) {
            return false;
        } else {
            return true;
        }
	}
    
    /**
     * Проверка наличия необходимых ресурсов для найма юнитов-воинов
     * 
     * @param $idUnit - ИД юнита
     * @param $unitCount - Количество юнитов
     * @param $idPersonage - ИД персонажа
     * @param $idPersonageCity - ИД города персонажа
     * @return boolean
     */
    public function checkAvailabilityResources($unitId, $unitCount, $idPersonage, $idPersonageCity)
    {
		$result = true;
		
		$sql = "
			SELECT 
				PRS.`personage_resource_value` as `has`,
				URV.`value` *  '%3\$d' `require`
			FROM
				`%1\$s` PRS
			INNER JOIN 
				`%2\$s` URV  ON PRS.`resource_id` = URV.`resource_id`
			WHERE 
				PRS.`id_personage` = '%4\$d'
				AND (PRS.`personages_cities_id` = '%5\$d'
				OR PRS.`personages_cities_id` IS NULL) 
				AND URV.`units_characteristics_id` IN (SELECT `id` FROM `%8\$s` WHERE `unit_id` = '%6\$d') 
				AND URV.`type` = '%7\$s'";
		
		$resources = $this->findAll(
			$sql,
			personage_ResourceState::TABLE_NAME,
			self::TABLE_NAME,
			$unitCount,
			$idPersonage,
			$idPersonageCity,
			$unitId,
			self::TYPE_ACTION_COST,
			unit_Characteristic::TABLE_NAME
		);
		
		foreach($resources as $resource)
		{
			$result = $result & ($resource->has > $resource->require);
		}
		
		return !$this->isEmptyResult() & $result;
	}
}
