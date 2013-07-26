<?php
/**
 * Файл содержит класс стратегию инкапсулирующую бизнес логику предметной области перемещения юнитов.
 *
 * @author Greg
 * @package unit
 */

/**
 * Класс стратегия инкапсулирующий бизнес логику предметной области перемещения юнитов.
 *
 * @author Greg
 * @version 1.0.0
 * @package unit
 */
class unit_strategy_UnitsMoving
{

    /**
     * Завершение перемещения отряда юнитов к пункту назначения.
     *
     * @param int $idSquad
     * @param Status $status
     * @param unit_Action $controller
     * @throws StatusErrorException
     * @return bool
     */
    public static function finishMovingUnits($idSquad, Status $status = null, unit_Action $controller = null)
    {
        $unitsMoving = unit_UnitsMoving::model();

        $squad = $unitsMoving->findSquadById($idSquad);
        if ($squad == null && $status != null) throw new StatusErrorException('Squad not found', $status->main_errors);

        $squad = $unitsMoving->detectTargetMovingSquadAndDetectPersonagesLocations($squad);

        $allSquadsInAttackLocation = $unitsMoving->findAllSquadsInAttackLocationWithoutCurrentSquad($squad);

        // Если цель атака или атака с захватом
        if ($squad->target == unit_UnitsMoving::MOVE_ATTACK || $squad->target == unit_UnitsMoving::MOVE_ATTACK_TACKING) {
            $squads = self::detectPriorityAttackSquadsAndSendingBackSquadsLatecomersFight(
                $squad, $allSquadsInAttackLocation
            );
			
            // Если отряд пришёл первым в локацию для атаки
            if ($squad->priority == unit_UnitsMoving::PRIORITY_FIRST) {

                // создаём запись битвы
                $idPersonageUnitsFight = fight_Mapper::model()->createFightAndWaitingAllied(
                    $squad->destination->id_cell,
                    $squad->target,
                    $squad->accessoryLocation,
                    $squad->secondPersonage->id_personage
                );

                if (empty($idPersonageUnitsFight)) throw new StatusErrorException('Do not set up a fight', $status->main_errors);

                //Добавляем статус передвижения отряда первый и ID боя, прибытия отряда (FINISH)
                $doneFinishMove = unit_UnitsMoving::model()->updateMovingSquadInDestinationArrivedAndIdFight($squad,
                    $idPersonageUnitsFight, unit_UnitsMoving::STATUS_ARRIVED_FIRST, unit_UnitsMoving::FINISH_STATUS);

                if ($doneFinishMove === false) throw new StatusErrorException('No updated status of the first unit arrived', $status->main_errors);

                // отсылаем время для отсчёта инициализации боя
				if ($controller != null) {
					$controller->Viewer_Assign('remaining_time', time() + fight_Mapper::TIME_WAITING_ALLIED);
				}
            }
            // Иначе если отряд пришёл не первым для атаки но время ещё не прошло
          //  elseif ($squad->status != unit_UnitsMoving::CANCEL_STATUS) {
            elseif ($squad->priority == unit_UnitsMoving::PRIORITY_NEXT) {

                //Обновляем статусы прибытия отрядам на (FINISH)
                // отправляем время оставщееся до битвы
				if ($controller != null) {
					$controller->Viewer_Assign('remaining_time', time() - $squad->end_time);
				}
          //  } elseif ($squad->status == unit_UnitsMoving::CANCEL_STATUS) {
            } elseif ($squad->status == unit_UnitsMoving::PRIORITY_LATE) {

                // Отправляем назад отряд
                $doneDestinationReversal = unit_UnitsMoving::model()->updateMovingSquadInDestination(
                    $squad, unit_UnitsMoving::CANCEL_STATUS, unit_UnitsMoving::DESTINATION_REVERSAL
                );

                if ($doneDestinationReversal === true) {
					if ($controller != null) {
						$controller->Viewer_Assign('moving_time', $squad->end_time - $squad->start_time);
					}
                } elseif ($status != null) {
                    throw new StatusErrorException('Not sent reversal home', $status->main_errors);
                }

                return false;
            }
        }
        // Если цель защита
        elseif ($squad->target == unit_UnitsMoving::MOVE_PROTECTION) {

            //Добавляем юнитов персонажа в локацию
             $donePlaceUnits = personage_UnitLocation::model()->placePersonagesInLocationOfUnits(
              unserialize($squad->units), $squad->currentPersonage->id_personage, $squad->destination->id_cell,
                        $squad->x_d, $squad->y_d
             );

             if ($donePlaceUnits === false) throw new StatusErrorException('No updated status of the first unit arrived', $status->main_errors);
            //$unitsMoving->movingSquadInDestination($squad);
        } elseif ($status != null) {
            throw new StatusErrorException('Target squad not defined', $status->main_errors);
		}


        return true;
    }

    /**
     * @param unit_UnitsMoving $squad
     * @param array $squads
     * @return array
     */
    public static function detectPriorityAttackSquadsAndSendingBackSquadsLatecomersFight(unit_UnitsMoving $squad, $squads)
    {
        $participatingSquads = array();
        if (empty($squads)) {
            $squad->priority = unit_UnitsMoving::PRIORITY_FIRST;
            $participatingSquads[] = $squad;
        } else {
            $squad->priority = unit_UnitsMoving::PRIORITY_NEXT;
            // Определяем отряды которые пришли после но успели к началу боя
            //Сделать запрос на получение времени окончания подготовки к началу боя.
            //Присврить массиву данные об успевших отрядах
        }



        //Сделать проверку на не успевших отрядов и передать массиву данные об не успевших отрядах

        return $participatingSquads;
    }

    /**
     * Завершение отмены перемещения отряда юнитов.
     *
     * @param int $idSquad
     * @param Status $status
     * @param unit_Action $controller
     * @return bool
     * @throws StatusErrorException
     */
    public static function finishCancelMovingUnits($idSquad, Status $status, unit_Action $controller)
    {
        $unitsMoving = unit_UnitsMoving::model();

        $squad = $unitsMoving->findSquadById($idSquad);
        if ($squad == null) throw new StatusErrorException('Squad not found', $status->main_errors);

        if ($squad->status != unit_UnitsMoving::CANCEL_STATUS)
            throw new StatusErrorException('Status in squad not cancel', $status->main_errors);

        // Если время ещё не вышло корректируем клиент
        if (time() < $squad->cancel_time) {
            $controller->Viewer_Assign('remaining_time', $squad->cancel_time - time());
            return false;
        }

        $location = personage_City::model()->findCityByCoordinatesForPersonage(
            $squad->x_s, $squad->y_s, Auth::getIdPersonage()
        );
        if ($location == null) {
            $location = personage_Location::model()->findLocationByCoordinatesWithOwner($squad->x_s, $squad->y_s);
            if ($location == null)
                throw new StatusErrorException('Source location not found', $status->main_errors);
        }

        $result = self::writeUpCountUnitsAndResourcesInLocation(
            unserialize($squad->units),
            unserialize($squad->resources),
            $location
        );

        return $result;
    }

    /**
     * Изменение количества определённых юнитов в определённом городе
     *
     * @param array $units
     * @param array $resources
     * @param Mapper $location
     * @return bool
     * @throws DBException
     */
    private function writeUpCountUnitsAndResourcesInLocation(array $units, array $resources, Mapper $location)
    {
        $isChange = false;
        if ($location instanceof personage_City) {
            // Возвращаем юнитов по зданиям
            $isChange = personage_Unit::model()->writeUpCountUnitsInConcreteCity($units);

            // Возвращаем ресурсы если таковые есть
            foreach ($resources as $r) {
                $writeDownResources = personage_ResourceState::model()->writeDownResourceInCityPersonage(
                    $r->count,
                    $r->id,
                    $location->id
                );
            }
        } elseif ($location instanceof personage_Location) {
            // Возвращаем юнитов в локацию
            $isChange = personage_UnitLocation::model()->writeUpCountUnitsInLocation($units);
        } else
            throw new DBException('Parameter `location` must be type `personage_City` or `personage_Location`');

        return $isChange;
    }
}
