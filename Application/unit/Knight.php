<?php
/**
 * Файл содержит класс модель, управляющая юнитами-рыцарями.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */

/**
 * Класс модель, управляющая юнитами-рыцарями.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package unit
 */
class unit_Knight extends Mapper
{

    const TABLE_NAME = 'units_knights';

    // Время найма юнита рыцаря в секундах
    const TIME_HIRING_KNIGHT = 86400; //24*60*60;


    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_Knight
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
     * Запрос на поиск юнитов рыцарями.
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return unit_Knight
     */
    public function findAllUnitsByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "SELECT 
				`id` as `id_unit`,
				`name_knights` as `name_unit`, 
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%3\$d' 
				AND `unit_id` = `id`) as `hired`,
				'1' as `available`,
				'knight' as `unit_type`,
				'1' as 'is_hired'
			FROM `%1\$s`";

        return $this->findAll($sql,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            $idPersonageBuilding
        );
    }

    /**
     * Проверка доступности юнитов-рыцарей для найма
     *
     * @param $unitId - ИД юнита
     * @param $unitCount - количество юнитов
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return boolean
     */
    public function checkAvailabilityUnitsForHiring($unitId, $unitCount, $idPersonageBuilding)
    {
        $sql = "SELECT 
				`id` as `id_unit`,
 
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%3\$d' 
				AND `unit_id` = `id`) as `hired`,
				
				(SELECT ifnull(SUM(`count`), '0')
				FROM `%2\$s` 
				WHERE `id_building_personage` = '%3\$d') as `total_hired`,
				
				'1' as `available`,

				'1' as 'is_hired'
			FROM 
				`%1\$s`
			WHERE
				`id` = '%4\$d'";

        $result = $this->find($sql,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
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
     * @return unit_Knight
     */
    public function findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "
			SELECT 
				PU.`id_unit_personage`,
				`UK`.`name_knights` as `name_unit`,
				`PU`.`count` as `count_unit`,
				'%5\$s' as `production_time`,
				`PU`.`finish_time_rent` as `finish_time`,
				`PU`.`status` as `status`
			FROM `%1\$s` as PU
			LEFT JOIN `%4\$s` as UK ON `UK`.`id` = `PU`.`unit_id`
			WHERE `PU`.`id_building_personage` = '%2\$d' 
			AND (`PU`.`status` = '%3\$s' 
			OR `PU`.`status` = '%6\$s')";

        return $this->findAll(
            $sql,
            personage_Unit::TABLE_NAME,
            $idPersonageBuilding,
            personage_Unit::STATUS_HIRE_PROCESSING,
            self::TABLE_NAME,
            self::TIME_HIRING_KNIGHT,
            personage_Unit::STATUS_HIRE_NOT_STARTED
        );
    }

    /**
     * Найм (постановка в очередь) юнита-рыцаря, по ИД здания персонажа, ИД юнита
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
				`time_rent` = '%5\$d',
				`status` = '%6\$s'";

        $personageUnit = $this->query(
            $sql,
            personage_Unit::TABLE_NAME,
            $idPersonageBuilding,
            $unitId,
            $unitCount,
            self::TIME_HIRING_KNIGHT,
            personage_Unit::STATUS_HIRE_NOT_STARTED
        );

        if ($personageUnit->isError())
            return false;
        else
            return true;
    }

    public function findInfoParametersUnitKnightsForIdPersonage($idPersonage)
    {
        $sql = "SELECT `uk`.*
                  FROM %1\$s as uk
                  INNER JOIN %2\$s as pu
                    ON (`uk`.id = `pu`.id_unit_personage)
                  INNER JOIN %3\$s as pb
                    ON (`pb`.id_building_personage = `pu`.id_building_personage)
                  INNER JOIN %4\$s as b
                    ON (`b`.id = `pb`.building_id)
                  WHERE `b`.name = '%5\$s'
                  AND `pb`.personage_id = %6\$d
                  AND `pu`.status = '%7\$s'";

        return $this->find($sql, self::TABLE_NAME, personage_Unit::TABLE_NAME, personage_Building::TABLE_NAME,
                             building_Mapper::TABLE_NAME, building_Mapper::KEY_BUILDING_CASTLE, $idPersonage,
                             personage_Unit::STATUS_HIRE_FINISH);
    }
}
