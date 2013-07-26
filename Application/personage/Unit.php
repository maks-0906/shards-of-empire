<?php
/**
 * Файл содержит класс модель, управляющий предметной и бизнес логикой юнитами персонажа.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package personage
 */

/**
 * Класс модель отображения на таблицу юнитов для персонажа.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package personage
 */
class personage_Unit extends Mapper
{
    const TABLE_NAME = 'personages_units';


    const STATUS_HIRE_PROCESSING = 'hiring';
    const STATUS_HIRE_FINISH = 'hired';
    const STATUS_HIRE_NOT_STARTED = 'notstarted';
    const STATUS_HIRE_CANCEL = 'cancel';

    const NOT_UNIT = 0;

    /**
     * Значение сессии (personage_id)
     * @var int
     */
    public $idPersonage;


    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Unit
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Имя таблицы в БД для отображения.
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
    public function pk()
    {
        return 'id_unit_personage';
    }

    /**
     * Инициализация первоначальных параметров сущности.
     */
    public function init()
    {
        $this->idPersonage = Auth::getIdPersonage();
    }

    /**
     * Добавить персонажу гарнизон из первоначальных боевых юнитов
     *
     * @param $idCity
     * @return bool
     */
    public function insertPrimaryUnit($idCity)
    {
        $allUnit = unit_Mapper::model()->findAllBattleUnit();

        if (empty($allUnit)) {
            return false;
        }

        foreach ($allUnit as $unit) {

            $countUnit = unit_Mapper::NO_UNIT;
            $idPersonageBuilding = unit_Mapper::NO_VALUE;

            $initializeUnit = data_InitializePersonage::model()->getInitializeUnits();

            if (array_key_exists($unit->name_unit, $initializeUnit)) {
                $countUnit = $initializeUnit[$unit->name_unit];
            }

            if ($countUnit > 0) {
                $building = personage_Building::model()->findValueOfBuildingOnIdOfBuilding($idCity, $unit->building_id);
                $idPersonageBuilding = $building->id_building_personage;
            }

            if ($idPersonageBuilding != NULL) {
                $donePrimaryUnit = $this->hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unit->id,
                    $countUnit, self::STATUS_HIRE_FINISH);
            }
        }

        return $donePrimaryUnit;
    }


    /**
     * Отмена найма последнего поставленного в очередь юнита
     *
     * @param $idPersonageBuilding - ИД персонажа
     * @return boolean
     */
    public function cancelLast($idPersonageBuilding)
    {
        try {
            $this->begin();

            $sql = "
				UPDATE 
					`%1\$s` 
				SET 
					`status` = '%2\$s'
				WHERE 
					`id_building_personage` = '%3\$d'
					AND (`status` = '%4\$s' OR `status` = '%5\$s')
				ORDER BY 
					`id_unit_personage` DESC 
                LIMIT 
					1";

            $result = $this->query(
                $sql,
                self::TABLE_NAME,
                self::STATUS_HIRE_CANCEL,
                $idPersonageBuilding,
                self::STATUS_HIRE_PROCESSING,
                self::STATUS_HIRE_NOT_STARTED
            );

            if ($result->isError())
                throw new DBException('Failed cancel last hiring unit');

            $this->commit();
            return true;
        } catch (DBException $e) {
            $this->rollback();
            if ($e->getModel() instanceof Mapper)
                $errors = $e->getModel();
            else
                $errors = $this->getErrors();

            ob_start();
            print_r($errors);
            $err = ob_end_clean();

            e1($e->getMessage(), $err);
            if (DEBUG === true)
                throw new StatusErrorException($e->getMessage(), $this->oStatus->main_errors);
            return false;
        }
    }

    /**
     * Найм юнита
     *
     * @param $unitId - ИД юнита
     * @param $unitCount - Количество юнитов
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return boolean
     */
    public function hiringUnit($unitId, $unitCount, $idPersonageBuilding)
    {
        try {
            // Идентифицируем персонажа
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->oStatus->personage_not_exists);

            // Получаем информацию о здании персонажа
            $personageBuilding = personage_Building::model()->findBuildingById($idPersonageBuilding, $idPersonage);
            if (empty($personageBuilding))
                throw new DBException('Personage building not found');

            $this->begin();

            // Проверяем, есть ли такое здание и доступны ли юниты в здании
            $building = building_Mapper::model()->findBuildingById($personageBuilding->building_id);
            if (!empty($building) && $building->unit == "y") {
                switch ($building->name) {
                    case building_Mapper::KEY_BUILDING_TAVERN:
                    {
                        // Проверяем доступность юнита для найма (по уровню или/и улучшению здания)
                        if (!unit_Spy::model()->checkAvailabilityUnitsForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonageBuilding
                        )
                        )
                            throw new StatusErrorException('Unable to hire a number of units', $this->oStatus->units_not_available);

                        // Проверяем наличие необходимых ресурсов
                        if (!unit_SpyResourceValue::model()->checkAvailabilityResources(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        )
                        )
                            throw new StatusErrorException('No resources available', $this->oStatus->no_resources);

                        // Списываем ресурсы за юнита-шпиона
                        $result = unit_SpyResourceValue::model()->writeOffResourceForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        );

                        if (!$result)
                            throw new DBException('Failed to write off resources for spy unit');

                        // Добавляем юнита в таблицу юнитов персонажа
                        unit_Spy::model()->hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount);

                        if (!$result)
                            throw new DBException('Failed hiring spy unit');

                        break;
                    }
                    case building_Mapper::KEY_BUILDING_STABLE:
                    case building_Mapper::KEY_BUILDING_BARRACKS:
                    {
                        // Проверяем доступность юнита для найма (по уровню или/и улучшению здания)
                        if (!unit_Mapper::model()->checkAvailabilityUnitsForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonageBuilding
                        )
                        )
                            throw new StatusErrorException('Unable to hire a number of units', $this->oStatus->units_not_available);

                        // Проверяем наличие необходимых ресурсов
                        if (!unit_ResourceValue::model()->checkAvailabilityResources(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        )
                        )
                            throw new StatusErrorException('No resources available', $this->oStatus->no_resources);

                        // Списываем ресурсы за юнита-воина
                        $result = unit_ResourceValue::model()->writeOffResourceForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        );

                        // Списываем свободное население за найм юнита-воина
                        $result = unit_Mapper::model()->writeOffFreePeopleForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        );

                        if (!$result)
                            throw new DBException('Failed to write off resources for warrior unit');

                        // Добавляем юнита в таблицу юнитов персонажа
                        unit_Mapper::model()->hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount);

                        if (!$result)
                            throw new DBException('Failed hiring warrior unit');

                        break;
                    }
                    case building_Mapper::KEY_BUILDING_SACRED_GROVE:
                    {
                        // Проверяем доступность юнита для найма (по уровню или/и улучшению здания)
                        if (!unit_Religion::model()->checkAvailabilityUnitsForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonageBuilding
                        )
                        )
                            throw new StatusErrorException('Unable to hire a number of units', $this->oStatus->units_not_available);

                        // Проверяем наличие необходимых ресурсов
                        if (!unit_ReligionResourceValue::model()->checkAvailabilityResources(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        )
                        )
                            throw new StatusErrorException('No resources available', $this->oStatus->no_resources);

                        // Списываем ресурсы за юнита-религиозника
                        $result = unit_ReligionResourceValue::model()->writeOffResourceForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        );

                        if (!$result)
                            throw new DBException('Failed to write off resources for religion unit');

                        // Добавляем юнита в таблицу юнитов персонажа
                        unit_Religion::model()->hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount);

                        if (!$result)
                            throw new DBException('Failed hiring religion unit');

                        break;
                    }
                    case building_Mapper::KEY_BUILDING_BARD_COLLEGE:
                    {
                        // Проверяем доступность юнита для найма (по уровню или/и улучшению здания)
                        if (!unit_Artist::model()->checkAvailabilityUnitsForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonageBuilding
                        )
                        )
                            throw new StatusErrorException('Unable to hire a number of units', $this->oStatus->units_not_available);

                        // Проверяем наличие необходимых ресурсов
                        if (!unit_ArtistResourceValue::model()->checkAvailabilityResources(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        )
                        )
                            throw new StatusErrorException('No resources available', $this->oStatus->no_resources);

                        // Списываем ресурсы за юнита-артиста
                        $result = unit_ArtistResourceValue::model()->writeOffResourceForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        );

                        if (!$result)
                            throw new DBException('Failed to write off resources for artist unit');

                        // Добавляем юнита в таблицу юнитов персонажа
                        unit_Artist::model()->hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount);

                        if (!$result)
                            throw new DBException('Failed hiring artist unit');

                        break;
                    }
                    case building_Mapper::KEY_BUILDING_CASTLE:
                    {
                        // Проверяем доступность юнита для найма (по уровню или/и улучшению здания)
                        if (!unit_Knight::model()->checkAvailabilityUnitsForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonageBuilding
                        )
                        )
                            throw new StatusErrorException('Unable to hire a number of units', $this->oStatus->units_not_available);

                        // Проверяем наличие необходимых ресурсов
                        if (!unit_KnightResourceValue::model()->checkAvailabilityResources(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        )
                        )
                            throw new StatusErrorException('No resources available', $this->oStatus->no_resources);

                        // Списываем ресурсы за найм юнита-рыцаря
                        $result = unit_KnightResourceValue::model()->writeOffResourceForHiring(
                            $unitId,
                            $unitCount,
                            $idPersonage,
                            $personageBuilding->city_id
                        );

                        if (!$result)
                            throw new DBException('Failed to write off resources for knight unit');

                        // Добавляем юнита в таблицу юнитов персонажа
                        unit_Knight::model()->hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount);

                        if (!$result)
                            throw new DBException('Failed add to hiring knight unit');

                        break;
                    }
                }
            } else
                throw new StatusErrorException('Building is not found or does not contain units', $this->oStatus->building_exists);

            $this->commit();
        } catch (DBException $e) {
            $this->rollback();
            if ($e->getModel() instanceof Mapper)
                $errors = $e->getModel();
            else
                $errors = $this->getErrors();

            ob_start();
            print_r($errors);
            $err = ob_end_clean();

            e1($e->getMessage(), $err);
            if (DEBUG === true) throw new StatusErrorException($e->getMessage(), $this->oStatus->main_errors);
        }
    }

    /**
     * Поиск юнитов, которые находятся в очереди, по ИД здания персонажа
     *
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return personage_Unit
     */
    public function findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "SELECT *
			    FROM `%1\$s`
			    WHERE `id_building_personage` = '%2\$d'
			    AND `status` = '%3\$s'";

        return $this->findAll(
            $sql,
            self::TABLE_NAME,
            $idPersonageBuilding,
            self::STATUS_HIRE_PROCESSING
        );
    }

    /**
     * Нанятые юниты
     *
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return personage_Unit
     */
    public function hiredUnit($idPersonageBuilding)
    {
        $sql = "SELECT *
			   FROM `%1\$s`
			WHERE `id_building_personage` = %2\$d";

        return $this->findAll(
            $sql,
            self::TABLE_NAME,
            $idPersonageBuilding
        );
    }

    /**
     * Расформировать юнитов
     *
     * @param int $idPersonageUnit - ИД юнита персонажа
     * @param int $idPersonage - ИД персонажа
     * @return boolean
     */
    public function disbandUnits($idPersonageUnit, $idPersonage)
    {
        $sql = "
			DELETE 
				PU
			FROM 
				`%1\$s` PU
			LEFT JOIN 
				`%2\$s` PB ON PB.`id_building_personage` = PU.`id_building_personage`
			WHERE 
				PU.`id_unit_personage` = '%3\$d'
				AND PB.`personage_id` = '%4\$d'";

        $result = $this->query(
            $sql,
            self::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idPersonageUnit,
            $idPersonage
        );

        if ($result->isError())
            throw new DBException('Same error in query in function `disbandUnits`');

        $affected_rows = $this->getAffectedRows($result);

        if ($result != null) {
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    /**
     * Поиск юнитов с просроченным временем найма
     *
     * @return personage_Unit
     */
    public function findUnitsWithFinishTimeRent()
    {
        $sql = "
			SELECT
				PU.*, PB.`personage_id`
			FROM 
				`%1\$s` PU, `%3\$s` PB
			WHERE 
				PU.`finish_time_rent` <= NOW() 
				AND PU.`status` = '%2\$s'
				AND PB.`id_building_personage` = PU.`id_building_personage`";

        return $this->findAll(
            $sql,
            self::TABLE_NAME,
            self::STATUS_HIRE_PROCESSING,
            personage_Building::TABLE_NAME
        );
    }

    /**
     * Выборка необходимых ресурсов(золото и еда) для выплаты жалования
     * отсортированных по полям ИД юнита персонажа, Золото, Еда
     *
     * @param $idPersonage - ИД персонажа
     * @return personage_Unit
     */
    public function findAllPersonagesUnitsToPaySalaryIdPersonageOrderByGoldAndFood($idPersonage)
    {
        $sql = "
			SELECT * FROM (SELECT 
				`personage_id` as `personage_id`,
				`id_unit_personage`, 
				if(UKRV.`resource_id` = '1',`resource_value` * `count`, '0') as `gold`,
				if(UKRV.`resource_id` = '7',`resource_value` * `count`, '0') as `food`
			FROM `%1\$s` PU
			LEFT JOIN `%2\$s` PB
				ON PB.`id_building_personage` = PU.`id_building_personage`
			LEFT JOIN `%3\$s` UKRV
				ON `units_knights_id` = `unit_id` AND `type_action` = 'salary'
			LEFT JOIN `%4\$s` PRS
				ON `id_personage` = `personage_id` 
				AND PRS.`resource_id` = UKRV.`resource_id`
			WHERE 
				`building_id` = '1' 
		        AND `status` = '%5\$s'
		        AND `personage_id` = '%8\$d'
			UNION
			SELECT 
				`personage_id` as `personage_id`,
				`id_unit_personage`,
				if(URV.`resource_id` = '1',`value` * `count`, '0') as `gold`,
				if(URV.`resource_id` = '7',`value` * `count`, '0') as `food`
			FROM `%1\$s` PU
			LEFT JOIN `%2\$s` PB 
				ON PB.`id_building_personage` = PU.`id_building_personage`
			LEFT JOIN `%6\$s` UC
				ON UC.`unit_id` = PU.`unit_id`
			LEFT JOIN `%7\$s` URV
				ON `units_characteristics_id` = UC.`id` AND `type` = 'cost_maintenance'
			LEFT JOIN `%4\$s` PRS 
				ON `id_personage` = `personage_id` 
				AND PRS.`resource_id` = URV.`resource_id`
			WHERE 
				(PB.`building_id` = '20' 
		        OR PB.`building_id` = '21')
		        AND `status` = '%5\$s'
		        AND `personage_id` = '%8\$d') A ORDER BY `personage_id`, `gold` DESC, `food` DESC";

        return $this->findAll(
            $sql,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            unit_KnightResourceValue::TABLE_NAME,
            personage_ResourceState::TABLE_NAME,
            self::STATUS_HIRE_FINISH,
            unit_Characteristic::TABLE_NAME,
            unit_ResourceValue::TABLE_NAME,
            $idPersonage
        );
    }

    /**
     * Завершение найма юнитов.
     *
     * @param int $idUnit
     * @return bool
     */
    public function finishUnitsRent($personageId = -1)
    {
        $sql = "
            SELECT pu.`id_unit_personage`, pu.`unit_id`, pu.`count`, pc.`x_c`, pc.`y_c`, pc.`id_personage`
            FROM " . self::TABLE_NAME . " AS pu
            JOIN " . personage_Building::TABLE_NAME . " AS pb
                ON pu.`id_building_personage` = pb.`id_building_personage`
            JOIN " . personage_City::TABLE_NAME . " AS pc
                ON pb.`city_id` = pc.`id`
            WHERE (pu.`status` = 'hiring' OR pu.`status` = 'hired')
                AND pu.`finish_time_rent` <= NOW()
        ";
        
        if ($personageId != -1) 
        {
            $sql .= " AND pc.`id_personage` = " . $personageId;
        }
        
        $result = $this->query($sql);
        if ($result->IsError())
            throw new DBException('Same error in query in function `finishUnitsRentById`');
        
        $ids = array();
        foreach($result->__DBResult as $unit) 
        {
            $ids[] = $unit['id_unit_personage'];
            personage_UnitLocation::model()->changeUnitsCountInLocation($unit['unit_id'], $unit['id_personage'], $unit['x_c'], $unit['y_c'], $unit['count']);
        }

        if (!empty($ids))
        {
            $sql = "
                DELETE FROM " . self::TABLE_NAME . " 
                WHERE `id_unit_personage` IN (" . implode(',', $ids) . ");
            ";

            $this->query($sql);
        }
//        
//        
//        $sql = "
//			UPDATE 
//				`%1\$s` PU
//			LEFT JOIN
//				`%2\$s` PB ON PB.`id_building_personage` = PU.`id_building_personage`
//			SET 
//				PU.`status` = '%3\$s' 
//			WHERE 
//				PU.`id_unit_personage` = '%4\$d'
//				AND PU.`finish_time_rent` <= NOW()";
//
//        if ($idPersonage)
//            $sql .= " AND PB.`personage_id` = '%5\$d'";
//
//        $result = $this->query(
//            $sql,
//            self::TABLE_NAME,
//            personage_Building::TABLE_NAME,
//            self::STATUS_HIRE_FINISH,
//            $idUnit,
//            $idPersonage
//        );

//        if ($result->IsError())
//            throw new DBException('Same error in query in function `finishUnitsRentById`');

        // Временное решение
        // Получаем информацию о юнитах по ИД
        if ($result->getAffectedRows($result) > 0) {
            $unit = $this->findById($idUnit);

            if ($unit)
                // Временно добавляем 10 очков славы за каждого нанятого юнита
                personage_State::model()->formPartOfFame(50000 * $unit->count, $idPersonage);
        }

        return ($this->getAffectedRows($result) > 0);
    }


    /**
     * Старт найма юнитов, которые стоят в очереди на найм.
     *
     * @return bool
     * @throws DBException
     */
    public function startNextUnitsRent()
    {
        $sql = "
			UPDATE 
				`%1\$s` PU
			JOIN (
				SELECT 
					PU_1.`id_unit_personage`,
					PU_1.`status`
				FROM `%1\$s` PU_1
                LEFT JOIN `%4\$s` PB ON `PB`.`id_building_personage` = `PU_1`.`id_building_personage`
                WHERE 
					PU_1.`status` = '%3\$s' 
					OR PU_1.`status` = '%2\$s'
                GROUP BY 
					`PB`.`personage_id`
            ) SPU ON 
				PU.`id_unit_personage` = SPU.`id_unit_personage` 
				AND SPU.`status` <> '%2\$s'
			SET 
				PU.`status` = '%2\$s',
				PU.`finish_time_rent` = TIMESTAMP(DATE_ADD(NOW(),INTERVAL PU.`time_rent` * PU.`count` SECOND))";

        $result = $this->query(
            $sql,
            self::TABLE_NAME,
            self::STATUS_HIRE_PROCESSING,
            self::STATUS_HIRE_NOT_STARTED,
            personage_Building::TABLE_NAME
        );
    }

    /**
     * Изменение количества определённых юнитов в определённом городе.
     *
     * @param array $units
     * @param $idPersonage
     * @return bool
     * @throws StatusErrorException
     * @throws DBException
     */
    public function writeDownCountUnitsInConcreteCity(array $units, $idPersonage)
    {
        $result = null;
        $affected_rows = 0;
        $sql = "UPDATE %s SET `count` = `count` - %d WHERE `id_building_personage`=%d AND `unit_id`=%d";

        foreach ($units as $u) {

            if ($u->location == null)
                throw new StatusErrorException('Parameter `location` not defined!', $this->oStatus->main_errors);

            if ($u->unit_id == null)
                throw new StatusErrorException('Parameter `unit_id` not defined!', $this->oStatus->main_errors);

            if ($u->count == null)
                throw new StatusErrorException('Parameter `count` not defined!', $this->oStatus->main_errors);

            $unitsCity = $this->findUnitPersonageOfCityOnById($u->unit_id, $u->location, $idPersonage);

            //Проверяем достаточное количество юнитов в городе
            if (($unitsCity->count - $u->count) < self::NOT_UNIT) {
                throw new StatusErrorException('Insufficient number of units city', $this->oStatus->main_errors);
            }

            $result = $this->query($sql, self::TABLE_NAME, $u->count, $u->location, $u->unit_id);
            if ($result->isError())
                throw new DBException('Same error in query in function `writeDownCountUnitsInConcreteCity`');

            $affected_rows += $this->getAffectedRows($result);
        }

        if ($result != null) {
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    /**
     * Списание определённых юнитов в определённом городе.
     *
     * @param array $units
     * @return bool
     * @throws StatusErrorException
     * @throws DBException
     */
    public function writeUpCountUnitsInConcreteCity(array $units)
    {
        $result = null;
        $affected_rows = 0;
        $sql = "UPDATE %s SET `count` = `count` + %d WHERE `id_building_personage`=%d AND `unit_id`=%d";
        foreach ($units as $u) {
            if ($u->location == null)
                throw new StatusErrorException('Parameter `location` not defined!', $this->oStatus->main_errors);

            if ($u->unit_id == null)
                throw new StatusErrorException('Parameter `unit_id` not defined!', $this->oStatus->main_errors);

            if ($u->count == null)
                throw new StatusErrorException('Parameter `count` not defined!', $this->oStatus->main_errors);

            $result = $this->query($sql, self::TABLE_NAME, $u->count, $u->location, $u->unit_id);
            if ($result->IsError())
                throw new DBException('Same error in query in function `writeUpCountUnitsInConcreteCity`');

            $affected_rows += $this->getAffectedRows($result);
        }

        if ($result != null) {
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    /**
     * Добавить юнита
     *
     * @param $idPersonageBuilding
     * @param $unitId
     * @param $unitCount
     * @param $status
     * @return bool
     */
    public function hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount, $status)
    {
        $sql = "INSERT INTO `%1\$s`
			    SET `id_building_personage` = %2\$d,
				    `unit_id` = %3\$d,
				    `count` = %4\$d,
				    `finish_time_rent` = NOW(),
				    `status` = '%5\$s'";

        $result = $this->query(
            $sql,
            personage_Unit::TABLE_NAME,
            $idPersonageBuilding,
            $unitId,
            $unitCount,
            $status
        );

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows))
            return true;
        else
            return false;
    }

    /**
     * Поиск боевых юнитов в конкретном городе
     *
     * @param int $idCity
     * @return personage_Unit
     */
    public function findAllBattleUnitsInCity($idCity)
    {
        $sql = "
			SELECT
				PU.*,
				UC.`place_barracks`
			FROM 
				`%1\$s` PU
			LEFT JOIN 
				`%2\$s` PB ON PB.`id_building_personage` = PU.`id_building_personage`
			LEFT JOIN 
				`%3\$s` B ON B.`id` = PB.`building_id`
			LEFT JOIN 
				`%4\$s` UC ON UC.`unit_id` = PU.`unit_id`
			WHERE 
				PB.`city_id` = %5\$d
				AND B.`name` IN ('%6\$s', '%7\$s')";

        return $this->findAll(
            $sql,
            self::TABLE_NAME,
            personage_Building::TABLE_NAME,
            building_Mapper::TABLE_NAME,
            unit_Characteristic::TABLE_NAME,
            $idCity,
            building_Mapper::KEY_BUILDING_BARRACKS,
            building_Mapper::KEY_BUILDING_STABLE
        );
    }

    /**
     * Получить нанятых юнитов
     *
     * @param int $idUnit - ИД юнита
     * @param int $idPersonageBuilding - ИД здания персонажа
     * @param int $idPersonage - ИД персонажа
     * @return boolean
     */
    public function findAllHiredByIdUnitAndIdPersonageBuildingAndIdPersonage($idUnit, $idPersonageBuilding, $idPersonage)
    {
        $sql = "
			SELECT 
				PU.*
			FROM 
				`%1\$s` PU
			LEFT JOIN 
				`%2\$s` PB ON PB.`id_building_personage` = PU.`id_building_personage`
			WHERE 
				PU.`unit_id` = '%3\$d'
				AND PU.`id_building_personage` = '%4\$d'
				AND PB.`personage_id` = '%5\$d'
				AND PU.`status` = '%6\$s'";

        return $this->findAll(
            $sql,
            self::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idUnit,
            $idPersonageBuilding,
            $idPersonage,
            self::STATUS_HIRE_FINISH
        );
    }

    /**
     * Получить юнитов по текущему ИД юнита
     *
     * @param int $id - ИД юнита
     * @return boolean
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM %s WHERE `id_unit_personage` = %d";
        return $this->find($sql, self::TABLE_NAME, $id);
    }

    /**
     * Получить юнитов по текущему ИД юнита
     *
     * @param int $idUnit - ИД юнита
     * @param int $idBuildingPersonage - ИД здания персонажа вырабатывающих юнитов
     * @param int $idPersonage
     * @return boolean
     */
    public function findUnitPersonageOfCityOnById($idUnit, $idBuildingPersonage, $idPersonage)
    {
        $sql = "SELECT `pu`.*
                FROM %1\$s as pu
                INNER JOIN %2\$s as pb
                  ON (`pu`.id_building_personage = `pb`.id_building_personage)
                WHERE `pu`.unit_id = %3\$d
                AND `pu`.id_building_personage = %4\$d
                AND `pb`.personage_id = %5\$d";
        return $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, $idUnit,
                                 $idBuildingPersonage, $idPersonage);
    }
}
