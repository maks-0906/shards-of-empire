<?php
/**
 *Класс содержит логику и запросы к базе данных связанных с передвижением персонажа
 */

class personage_parameters_Move extends personage_State
{
    const STATUS_MOVE_PERSONAGE_TRANSIT = 'transit'; //Персонаж в движении при перемещении
    const STATUS_MOVE_PERSONAGE_ARRIVAL = 'arrival'; //Персонаж прибыл при перемещении
    const TIME_MOVE_PERSONAGE = 5; //Время передвижения персонажа

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Move
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Сохранение последней позиции персонажа.
     *
     * @param int $y
     * @param int $x
     * @param int $dateFinishMovePersonage
     * @param int $statusMovePersonage
     * @return personage_State
     * @throws StatusErrorException
     */
    public function setLastPosition($y, $x, $dateFinishMovePersonage, $statusMovePersonage)
    {
        $pk = $this->pk();
        if ($this->$pk == null)
            throw new StatusErrorException('Parameter `id` not defined', $this->status->main_errors);

        $this->status_move_personage = $statusMovePersonage;
        $this->finishing_move_personage = $dateFinishMovePersonage;
        $this->y_c = $y;
        $this->x_c = $x;

        return $this->save();
    }

    /**
     * Изменение статуса передвижения персонажа
     *
     * @param $statusMovePersonage
     * @param $idPersonage
     * @return bool
     */
    public function updateStatusMovePersonage($statusMovePersonage, $idPersonage)
    {
        $sql = "UPDATE %s SET `status_move_personage`= '%s' WHERE `id_personage` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $statusMovePersonage, $idPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Обновляем координаты и статус в случае прибытия персонажа в город
     *
     * @param $statusMovePersonage
     * @param $x
     * @param $y
     * @param $idPersonage
     * @return bool
     */
    public function updateCoordinatesStatusMovePersonage($statusMovePersonage, $x, $y, $idPersonage)
    {
		$arrival = "";
		if ($statusMovePersonage == personage_parameters_Move::STATUS_MOVE_PERSONAGE_ARRIVAL) {
			$arrival = ", `x_c` = -1, `y_c` = -1, `finishing_move_personage` = '0000-00-00 00:00:00'";
		}

        $sql = "
			UPDATE %s
			SET `x_l` = %d,
				`y_l` = %d,
				`status_move_personage`= '%s'
				" . $arrival . "
			WHERE `id_personage` = %d
			LIMIT 1
		";

        $result = $this->query($sql, self::TABLE_NAME, $x, $y, $statusMovePersonage, $idPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Найти координаты пункта прибытия персонажа у которого вышло время передвижения
     *
     * @param $statusMovePersonage
     * @param bool $idPersonage
     * @return personage_parameters_Move
     */
    public function findCoordinatesPersonages($statusMovePersonage, $idPersonage = false)
    {
        $sql = "SELECT `ps`.x_c, `ps`.y_c, `ps`.id_personage, `p`.world_id
                FROM %1\$s as ps, %2\$s as p
                WHERE `ps`.id_personage = `p`.id
                AND `ps`.finishing_move_personage <= '%3\$s'
                AND `ps`.status_move_personage = '%4\$s'";

        if ($idPersonage !== false) {
            $sql .= " AND `id_personage` = %5\$d";
        }

        return $this->findAll($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME,
                                  models_Time::model()->getCurrentFormedDateAndTime(),
                                   $statusMovePersonage, $idPersonage);
    }


    /**
     * Закончить передвижение персонажа к пункту назначения
     *
     * В случае удачного перемещения создается сессия местонахождения персонажа, локации
     * @param $personageState
     * @param $statusMovePersonage
     * @param bool $idPersonage
     * @return bool
     * @throws StatusErrorException
     */
    public function finishMovePersonage($personageState, $statusMovePersonage, $idPersonage = false)
    {

        if (!empty($personageState)) {

            foreach ($personageState as $state) {

                if ($idPersonage === false) {
                    $idCurrentPersonage = $state->id_personage;
                } else {
                    $idCurrentPersonage = $idPersonage;
                }

                $location = adminworld_Cell::findCellByCoordinatesAndDetectOwnerLocation($state->x_c, $state->y_c, $state->world_id);

                //Проверяем является ли локация городом
                if ($location->city_name == NULL) {
                    throw new StatusErrorException('Location not city', $this->oStatus->main_errors);
                }

                $doneFinishMovePersonage = $this->updateCoordinatesStatusMovePersonage($statusMovePersonage,
                    (int)$state->x_c, (int)$state->y_c, (int)$idCurrentPersonage);

                if ($doneFinishMovePersonage === true) {
                    Auth::setCurrentLocationCoordinates($state->x_c, $state->y_c);
                    Auth::setCurrentIdLocation($location->id_cell);
                    Auth::setPatternCurrentLocation($location->map_pattern);
                }
            }

            return $doneFinishMovePersonage;
        }
    }
}