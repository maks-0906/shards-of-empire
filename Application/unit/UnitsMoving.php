<?php
/**
 * Файл содержит класс модель отображающую таблицу `units_moving`
 * и включает некоторую бизнес логику в предметной области перемещения отрядов юнитов персонажа.
 *
 * @author Greg
 * @package 1.0.0
 */

/**
 * Класс модель отображающую таблицу `units_moving`
 * и включает некоторую бизнес логику в предметной области перемещения отрядов юнитов персонажа.
 *
 * @author Greg
 * @version 1.0.0
 * @package unit
 */
class unit_UnitsMoving extends Mapper
{
    const TABLE_NAME = 'personages_units_moving';

    const MOVE_ATTACK = 'attack';
    const MOVE_ATTACK_TACKING = 'attack_tacking';
    const MOVE_PROTECTION = 'protection';
    const MOVE_BACK_HOUSE = 'back_house'; //Назад домой

    const NOTSTARTED_STATUS = 'notstarted';
    const MOVING_STATUS = 'moving';
    const CANCEL_STATUS = 'cancel';
    const FINISH_STATUS = 'finish';

    const DESTINATION_FORVARD = 'forvard'; //К пункту назначения
    const DESTINATION_REVERSAL = 'reversal'; //Обратный путь

    const ACCESSORY_LOCATIONS_PERSONAGES = 'personages';
    const ACCESSORY_LOCATIONS_ROBBERS = 'robbers';

    const STATUS_ARRIVED_FIRST = 'first'; //Статус прибытия отряда первый
    const STATUS_ARRIVED_AFTER = 'after'; //Статус прибытия отряда последующий

    const NOT_PERSONAGES = 0;

    const PRIORITY_FIRST = 1; //Отряд прибывший первый и является инициатором боя
    const PRIORITY_NEXT = 2; //Отряд прибывший не первый но успел к началу боя
    const PRIORITY_LATE = 3; //Отряд прибывший не успел к началу боя (опоздал)

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_UnitsMoving
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
     * Старт перемещения юнитов персонажа вместе с ресурсами.
     *
     * @param array $units
     * @param array $resources
     * @param int $x_s
     * @param int $y_s
     * @param int $x_d
     * @param int $y_d
     * @param string $typeDestination
     * @param int $idWorld
     * @param int $idPersonage
     * @return bool
     * @throws DBException|Exception|StatusErrorException
     */
    public function startMovingUnitsPersonage($units, $resources, $x_s, $y_s, $x_d, $y_d, $typeDestination, $idWorld, $idPersonage)
    {
        try {
            $this->begin();
            $location = personage_City::model()->findCityByCoordinatesForPersonage($x_s, $y_s, Auth::getIdPersonage());
            if ($location == null) {
                $location = personage_Location::model()->findLocationByCoordinatesWithOwner($x_s, $y_s);
                if ($location == null)
                    throw new StatusErrorException('Source location not found', $this->oStatus->main_errors);
            }

            // Если тип отправки атака на локацию:
            if ($typeDestination == unit_UnitsMoving::MOVE_ATTACK) {
                $destination = adminworld_Cell::model()->detectLocationAndHireOwner($x_d, $y_d, $idWorld);
                fb($destination, 'sc', FirePHP::ERROR);
                if ($destination == null)
                    throw new StatusErrorException('Destination not found', $this->oStatus->main_errors);

                $idPersonage = ($destination->personage_id_city == null)
                    ? $destination->personage_id_location : $destination->personage_id_city;

                //Получаем данные об персонаже который атакует локацию
                if (isset($destination->$idPersonage))
                {
                    $currentPersonage = personage_Mapper::model()->findPersonageById(Auth::getIdPersonage());

                    $mail = mail_Template::model()->makeMailFightNotificationOfAttack($currentPersonage->nick);
                
                    
                    $mailAttribute = array('from' => 0,
                                           'to' => $destination->$idPersonage,
                                           'subject' => $mail['subject'],
                                           'body' => $mail['body']);
                    mail_Mapper::model()->createNewNotice($mailAttribute);
                }
            }

            // Если есть ресурсы и пункт отправления город
            if (!empty($resources) && $location->city_name != null) {
                // TODO: Ввести проверку на грузоподъёмность отряда и количество ресурсов
                $isOverload = false;
                if ($isOverload == false) {
                    foreach ($resources as $r) {
                        $writeDownResources = personage_ResourceState::model()->writeDownResourceInCityPersonage(
                            $r->count,
                            $r->id,
                            $location->id
                        );
                    }
                } // иначе выводим ошибку о перегрузке отряда
                else
                    throw new StatusErrorException('Overload squad', $this->oStatus->overload);
            }

            // Is there are units to move
            $unitsInLocation = personage_UnitLocation::model()->getUnitsInLocation($x_s, $y_s, AUTH::getIdPersonage());
            foreach ($units as $unit) 
            {
                $isUnitInLocation = false;
                foreach ($unitsInLocation as $unitInLocation)
                {
                   if ($unit->unit_id == $unitInLocation['unit_id'])
                   {
                       $isUnitInLocation = true;
                       if ($unit->count > $unitInLocation['count']) 
                       {
                           throw new StatusErrorException('Wrong units amount', $this->oStatus->main_errors);
                       }
                   }
                }
                
                if (!$isUnitInLocation)
                {
                    throw new StatusErrorException('Wrong units amount', $this->oStatus->main_errors);
                }
            }
            
            // Списываем юнитов из локации/города
            foreach ($units as $unit) 
            {
                personage_UnitLocation::model()->changeUnitsCountInLocation($unit->unit_id, AUTH::getIdPersonage(), $x_s, $y_s, -$unit->count);
            }
//            $isMoveUnitsFromPersonagesUnits = $this->writeDownCountUnitsInLocation($units, $location, $idPersonage);
//            if ($isMoveUnitsFromPersonagesUnits == false)
//                throw new DBException('Error update count units in city_id: ' . $location->id);

            $distance = $this->detectDistanceBetweenLocation($x_s, $y_s, $x_d, $y_d);
            $speed = $this->detectSpeedSquad($units);

            // Сохраняем в таблицу перемещения юнитов и ресурсов отряд для перемещения
            $squadId = $this->saveMovingSquad(
                $x_s, $y_s, $x_d, $y_d,
                $distance, $speed,
                serialize($units), serialize($resources),
                $this->detectTimeMovingInSecond($distance, $speed), $typeDestination
            );

            if (!$squadId) 
                throw new DBException('Error save moving squad');
            
            $this->commit();
            
            return $squadId;
        } catch (DBException $e) {
            $this->rollback();
            throw $e;
        }
        catch (StatusErrorException $e) {
            $this->rollback();
            throw new StatusErrorException($e->getMessage(), $e->status);
        }
    }

    /**
     * Определения скорости отряда.
     *
     * @param array $units
     * @return int
     */
    private function detectSpeedSquad($units)
    {
        $ids = $this->selectIDsUnits($units);
        $characteristic = unit_Characteristic::model()->detectUnitWithMinSpeed($ids);

        return (int)$characteristic->speed;
    }

    /**
     * Выборка идентификаторов юнитов, требуемых для запроса.
     *
     * @param array $units
     * @return array
     */
    private function selectIDsUnits(array $units)
    {
        $ids = array();
        foreach ($units as $u) $ids[] = $u->unit_id;

        return $ids;
    }

    /**
     * Определение расстояния между локациями.
     * Определение учитывает оптимальный подсчёт расстояния с зацикливанием карты,
     * то есть если путь в обратную сторону будет короче тогда выбирается путь через границы карты.
     *
     * @param int $x_s
     * @param int $y_s
     * @param int $x_d
     * @param int $y_d
     * @return int
     */
    private function detectDistanceBetweenLocation($x_s, $y_s, $x_d, $y_d)
    {
        if (abs($x_d - $x_s) < 500 && abs($y_d - $y_s) < 500)
            return abs((pow($x_d - $x_s, 2) + pow($y_d - $y_s, 2)) * 0.5);
        elseif (abs($x_d - $x_s) > 500 && abs($y_d - $y_s) < 500)
            return abs((pow($x_d - (1000 - $x_s), 2) + pow($y_s - $y_d, 2)) * 0.5); elseif (abs($x_d - $x_s) < 500 && abs($y_d - $y_s) > 500)
            return abs((pow($x_d - $x_s, 2) + pow($y_s - (1000 - $y_d), 2)) * 0.5); else
            return abs((pow($x_d - (1000 - $x_s), 2) + pow($y_s - (1000 - $y_d), 2)) * 0.5);
    }

    /**
     * Определение времени передвижения в секундах
     *
     * @param int $distance - км
     * @param int $speed - км/сек
     * @return float - сек
     */
    private function detectTimeMovingInSecond($distance, $speed)
    {

        return ($distance / $speed) * 3600;
    }

    /**
     * Изменение количества определённых юнитов в определённом городе
     *
     * @param array $units
     * @param Mapper $location
     * @param $idPersonage
     * @return bool
     * @throws DBException
     */
    private function writeDownCountUnitsInLocation(array $units, Mapper $location, $idPersonage)
    {
        $isChange = false;
        if ($location instanceof personage_City) {
            $isChange = personage_Unit::model()->writeDownCountUnitsInConcreteCity($units, $idPersonage);
        } elseif ($location instanceof personage_Location) {
            $isChange = personage_UnitLocation::model()->writeDownCountUnitsInLocation($units, $idPersonage);
        } else
            throw new DBException('Parameter `location` must be type `personage_City` or `personage_Location`');

        return $isChange;
    }

    /**
     * Сохранение информации и состояния перемещения отряда.
     *
     * @param int $x_s
     * @param int $y_s
     * @param int $x_d
     * @param int $y_d
     * @param int $distance
     * @param int $speed
     * @param string $units - serialize array
     * @param string $resources - serialize array
     * @param int $countSecondForEndTime
     * @param string $target
     * @return bool
     */
    private function saveMovingSquad(
        $x_s, $y_s, $x_d, $y_d, $distance, $speed, $units, $resources, $countSecondForEndTime, $target
    )
    {
        $sql = "
				INSERT INTO %s
				SET `personage_id`=%d, `x_s`=%d, `y_s`=%d, `x_d`=%d, `y_d`=%d, `distance`=%d, `speed`=%d, `units`='%s',
					`resources`='%s', `end_time`=TIMESTAMPADD(SECOND,%d,NOW()), `status`='%s', `target`='%s'";

        $result = $this->query(
            $sql,
            self::TABLE_NAME,
            Auth::getIdPersonage(), $x_s, $y_s, $x_d, $y_d, $distance, $speed, $units,
            $resources, $countSecondForEndTime, self::MOVING_STATUS, $target
        );

        if ($this->isError()) {
            return false;
        } else {
            return $result->id;
        }
    }

    /**
     * Отмена перемещения отряда юнитов.
     *
     * @param int $idSquad
     * @param int $idPersonage
     * @return bool
     */
    public function cancelMovingUnits($idSquad, $idPersonage)
    {
        $sql = "UPDATE %s SET `target` = %s, `status`='%s', `cancel_time`=NOW() WHERE `id`=%d AND `personage_id`=%d";

        $result = $this->query($sql, self::TABLE_NAME, self::MOVE_PROTECTION, self::CANCEL_STATUS, $idSquad, $idPersonage);

        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Поиск отряда по идентификатору записи в БД с частичной информацией.
     *
     * @param int $idSquad
     * @return unit_UnitsMoving|null
     */
    public function findSquadByIdIncompleteInformation($idSquad)
    {
        $sql = "SELECT
					`id`, `distance`, `speed`,
					UNIX_TIMESTAMP(`start_time`), UNIX_TIMESTAMP(`end_time`), UNIX_TIMESTAMP(`cancel_time`), `status`
				FROM %s WHERE `id`=%d";

        return $this->find($sql, self::TABLE_NAME, $idSquad);
    }

    /**
     * Поиск отряда по идентификатору записи в БД с полной информацией об отряде.
     *
     * @param int $idSquad
     * @return unit_UnitsMoving|null
     */
    public function findSquadById($idSquad)
    {
        $sql = "SELECT
					`id`, `distance`, `speed`, `units`, `resources`, `x_s`, `y_s`, `x_d`, `y_d`, `target`,
					UNIX_TIMESTAMP(`start_time`), UNIX_TIMESTAMP(`end_time`), UNIX_TIMESTAMP(`cancel_time`), `status`
				FROM %s WHERE `id`=%d";

        return $this->find($sql, self::TABLE_NAME, $idSquad);
    }

    /**
     * Определение конечной цели отряда взависимости от изменившегося владельца локации.
     *
     * @param unit_UnitsMoving $squad
     * @return unit_UnitsMoving
     * @throws StatusErrorException
     */
    public function detectTargetMovingSquadAndDetectPersonagesLocations(unit_UnitsMoving $squad)
    {
        $secondPersonage = self::NOT_PERSONAGES;

        $currentPersonage = personage_Mapper::model()->findPersonageById(Auth::getIdPersonage());
        if ($currentPersonage == NULL)
            throw new StatusErrorException('Current personage not found', $this->oStatus->main_errors);

        $destination = adminworld_Cell::model()->detectLocationAndHireOwner($squad->x_d, $squad->y_d, $currentPersonage->world_id);

        if ($destination == NULL)
            throw new StatusErrorException('Location and second personage not found', $this->oStatus->main_errors);

        $idSecondPersonage = ($destination->personage_id_city == NULL)
            ? $destination->personage_id_location : $destination->personage_id_city;

        //Если локация не занята ни каким персонажем, тогда в ней находятся РАЗБОЙНИКИ
        if ($idSecondPersonage == NULL) {
            $accessoryLocation = self::ACCESSORY_LOCATIONS_ROBBERS;
            $squad->target = self::MOVE_ATTACK_TACKING;
        } else {
            $accessoryLocation = self::ACCESSORY_LOCATIONS_PERSONAGES;
            $secondPersonage = personage_Mapper::model()->findPersonageById($idSecondPersonage);

            if ($secondPersonage == NULL)
                throw new StatusErrorException('Second personage not found', $this->oStatus->main_errors);

            //Определить союзники находятся на локации или нет
            if ($currentPersonage->guild_id != $secondPersonage->guild_id) {
              //  $isChange = $squad->changeTargetMoveAndStatusSquad(self::MOVE_ATTACK_TACKING, self::FINISH_STATUS, $squad);
                $isChange = $squad->changeTargetMoveAndStatusSquad(self::MOVE_ATTACK_TACKING, $squad);
                if ($isChange === false)
                    throw new StatusErrorException('Bad change target squad in `attack_tacking`', $this->oStatus->main_errors);
                $squad->target = self::MOVE_ATTACK_TACKING;
            } else {
                //$isChange = $squad->changeTargetMoveAndStatusSquad(self::MOVE_PROTECTION, self::FINISH_STATUS, $squad);
                $isChange = $squad->changeTargetMoveAndStatusSquad(self::MOVE_PROTECTION, $squad);
                if ($isChange === false)
                    throw new StatusErrorException('Bad change target squad in `protection`', $this->oStatus->main_errors);
                $squad->target = self::MOVE_PROTECTION;
            }
        }

        $squad->status = self::FINISH_STATUS;
        $squad->currentPersonage = $currentPersonage;
        $squad->destination = $destination;
        $squad->secondPersonage = $secondPersonage;
        $squad->accessoryLocation = $accessoryLocation;

        return $squad;
    }


    /**
     * Изменение цели перемещения отряда.
     *
     * @param string $target
     * @param string $status
     * @param $squad
     * @return bool

    public function changeTargetMoveAndStatusSquad($target, $status, unit_UnitsMoving $squad)
    {
        $sql = "UPDATE %s SET `target`='%s', `status`='%s' WHERE `id`=%d AND `personage_id`=%d";

        $result = $this->query($sql, self::TABLE_NAME, $target, $status, $squad->id, $squad->personage_id);

        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    } */

    /**
         * Изменение цели перемещения отряда.
         *
         * @param string $target
         * @param $squad
         * @return bool
     */
        public function changeTargetMoveAndStatusSquad($target, unit_UnitsMoving $squad)
        {
            $sql = "UPDATE %s SET `target`='%s' WHERE `id`=%d AND `personage_id`=%d";

            $result = $this->query($sql, self::TABLE_NAME, $target, $squad->id, $squad->personage_id);

            $affected_rows = $this->getAffectedRows($result);
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        }



    /**
     * Поиск отрядов с целью атаки локации. Атака любого типа.
     * Метод не выбирает отряд текущего персонажа так как он уже найден и находится в параметре $squad
     *
     * @param unit_UnitsMoving $squad
     * @return array|unit_UnitsMoving[]
     */
    public function findAllSquadsInAttackLocationWithoutCurrentSquad(unit_UnitsMoving $squad)
    {
        $sql = "SELECT `id`, `distance`, `speed`, `units`, `resources`, `x_s`, `y_s`, `x_d`, `y_d`, `target`,
						UNIX_TIMESTAMP(`start_time`), UNIX_TIMESTAMP(`end_time`), UNIX_TIMESTAMP(`cancel_time`), `status`
		 		FROM %s
		 		WHERE `x_d` = %d
		 		AND `y_d` = %d
		 		AND `status` = '%s'
		 		AND (`target` = '%s' OR `target` = '%s')
		 		AND `id` != %d";

        return $this->findAll($sql, self::TABLE_NAME, $squad->x_d, $squad->y_d, self::FINISH_STATUS, self::MOVE_ATTACK,
                                    self::MOVE_ATTACK_TACKING, $squad->id);
    }


    /**
     * Найти отряды персонажа по ID боя и статусу прибытия в локацию
     *
     * @param $statusArrived
     * @param $idPersonageFight
     * @return unit_UnitsMoving
     */
    public function findSquadOnStatusArrivedAndIdPersonageFight($statusArrived, $idPersonageFight)
    {
        $sql = "SELECT uum.*, u.`lang`
                FROM %s as uum, %s as u
                WHERE uum.`id_personages_units_fight` = %d
                AND uum.`arrived` = '%s'
                AND uum.`personage_id` = u.`id`";

        return $this->find($sql, self::TABLE_NAME, user_Mapper::TABLE_NAME, $idPersonageFight, $statusArrived);
    }

    public function movingSquadInDestination(unit_UnitsMoving $squad)
    {

    }

    /**
     * Обновляем направление передвижения юнитов со сменой статусов
     *
     * @param unit_UnitsMoving $squad
     * @param $status
     * @param $typeDirection
     * @return bool
     */
    public function updateMovingSquadInDestination(unit_UnitsMoving $squad, $status, $typeDirection)
    {
        $sql = "UPDATE %s SET `status` = '%s', `type_direction` = '%s' WHERE `id` = %d AND `personage_id` = %d";

        $result = $this->query($sql, self::TABLE_NAME, $status, $typeDirection, $squad->id, $squad->personage_id);

        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Обновляем  передвижения отряда юнитов со сменой статусов прибытия и добавлением боя
     *
     * @param unit_UnitsMoving $squad
     * @param $idPersonagesUnitsFight
     * @param $statusArrived
     * @param $status
     * @return bool
     */
    public function updateMovingSquadInDestinationArrivedAndIdFight(unit_UnitsMoving $squad, $idPersonagesUnitsFight, $statusArrived, $status)
    {
        $sql = "UPDATE %s SET `id_personages_units_fight` = %d, `arrived` = '%s', `status` = '%s' WHERE `id` = %d";

        $result = $this->query($sql, self::TABLE_NAME, $idPersonagesUnitsFight, $statusArrived, $status, $squad->id);

        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Уничтожить отряды
     *
     * @param unit_UnitsMoving $squad
     * @return bool
     */
    public function destructionSquad(unit_UnitsMoving $squad)
    {
        $sql = "DELETE FROM %s WHERE `id` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $squad->id);

        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }



    
    /**
     * Get squads. If $personageId != -1 - get only personage squads
     *
     * @param int $personageId = -1
     * @return squads
     */
    public function getSquads($personageId = -1)
    {
        $sql = "
            SELECT `id`,                
                `personage_id`,
                `x_s`,
                `y_s`,
                `x_d`,
                `y_d`,
                `units`,
                `resources`,
                UNIX_TIMESTAMP(`start_time`) AS start_time, 
                UNIX_TIMESTAMP(`end_time`) AS end_time, 
                UNIX_TIMESTAMP(`cancel_time`) AS cancel_time,
                `status`,
                `target`
            FROM " . self::TABLE_NAME . "
        ";

        if ($personageId != -1)
        {
            $sql .= " WHERE `personage_id` = %d";
        }

        $result = $this->query($sql, $personageId);
        return $result->__DBResult;
    }

    /**
     * Remove squad by id
     *
     * @param int $squadId
     * @return bool
     */
    public function removeSquad($squadId)
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE `id` = %d";
        $result = $this->query($sql, $squadId);
        return $this->getAffectedRows($result);
    }

	public function updateSquadStatus($squadId, $status)
	{
		$sql = "
			UPDATE " . self::TABLE_NAME . "
			SET `status` = '%s'
			WHERE `id` = %d
			LIMIT 1
		";
		$result = $this->query($sql, $status, $squadId);
        return $this->getAffectedRows($result);
	}

	public function updateSquadUnits($squadId, $units)
	{
		$sql = "
			UPDATE " . self::TABLE_NAME . "
			SET `units` = '%s'
			WHERE `id` = %d
			LIMIT 1
		";
		$result = $this->query($sql, $units, $squadId);
        return $this->getAffectedRows($result);
	}
        
        public function updateSquadResources($squadId, $resources)
	{
		$sql = "
			UPDATE " . self::TABLE_NAME . "
			SET `resources` = '%s'
			WHERE `id` = %d
			LIMIT 1
		";
		$result = $this->query($sql, $resources, $squadId);
        return $this->getAffectedRows($result);
	}

	public function getWaitingForBattleSquadsInLocation($x, $y)
	{
		$sql = "
			SELECT `id`,
                `personage_id`,
				`units`
			FROM " . self::TABLE_NAME . "
			WHERE `x_d` = %d
				AND `y_d` = %d
				AND `status` = 'waiting_for_battle'
		";
		$result = $this->query($sql, $x, $y);
		return $result->__DBResult;
	}

	public function returnAllAttackersSquadsBack($x, $y)
	{
		$sql = "
                    UPDATE " . self::TABLE_NAME . "
                    SET `x_d` = `x_s`,
                        `y_d` = `y_s`,
                        `x_s` = ".$x.",
                        `y_s` = ".$y.",
                        `start_time` = NOW(),
                        `end_time` = TIMESTAMPADD(SECOND, distance/speed*3600, NOW()),
                        `status` = 'moving',
                        `target` = 'protection'
                    WHERE `x_d` = ".$x."
                        AND `y_d` = ".$y."
		";
                $result = $this->query($sql);
	}
        
        public function getSquadById($squadId)
        {
            $sql = "SELECT * FROM " . self::TABLE_NAME . " WHERE `id` = %d LIMIT 1";
            $result = $this->query($sql, $squadId);
            if ($result->__DBResult)
            {
                return $result->__DBResult[0];
            }
            return false;
        }
}