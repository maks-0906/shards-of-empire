<?php
/**
 * Файл содержит логику обработки и управление юнитами
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */

class unit_Action extends JSONAction
{

    public function RegisterEvent()
    {
        $this->AddEvent('units.json', 'actionGetUnits', Auth::AUTH_USER);
        //$this->AddEvent('hired_units.json', 'actionHiredUnits');
        $this->AddEvent('hiring_units.json', 'actionHiringUnits', Auth::AUTH_USER);
        $this->AddEvent('finish_hiring_units.json', 'actionFinishHiringUnits', Auth::AUTH_USER);
        $this->AddEvent('cancel_last.json', 'actionCancelLast', Auth::AUTH_USER);
        $this->AddEvent('start_move_units.json', 'actionStartMoveUnits', Auth::AUTH_USER);
        $this->AddEvent('start_cancel_move_units.json', 'actionStartCancelMoveUnits', Auth::AUTH_USER);
        $this->AddEvent('finish_move_units.json', 'actionFinishMovingUnits', Auth::AUTH_USER);
        $this->AddEvent('finish_cancel_move_units.json', 'actionFinishCancelMovingUnits', Auth::AUTH_USER);
        $this->AddEvent('dismiss.json', 'actionDismissUnits', Auth::AUTH_USER);
        $this->AddEvent('squads.json', 'getSquads', Auth::AUTH_USER);
    }

    /**
     * Обработчик действия события завершения (прихода) перемещения юнитов в пункт назначения.
     */
    public function actionFinishMovingUnits()
    {
        try {
            $idSquad = $this->GetVar('squad_id');
            if ($idSquad == null)
                throw new StatusErrorException('Parameter `squad_id` not defined', $this->status->main_errors);

            $result = unit_strategy_UnitsMoving::finishMovingUnits($idSquad, $this->status, $this);

            $this->Viewer_Assign('status', $result ? $this->status->successfully : $this->status->main_errors);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionCancelLast` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelLast` error: ', $e->getMessage());
        }
    }

    /**
     * Действие на обработку события перемещение юнитов.
     * @author Greg
     */
    public function actionStartMoveUnits()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            // Проверка целостности параметров от клиента
            $typeDestination = $this->GetVar('type');
            if ($typeDestination == null || $typeDestination == '')
                throw new StatusErrorException('Parameter `type` not defined', $this->status->main_errors);

            $XDestination = $this->GetVar('x_d');
            $YDestination = $this->GetVar('y_d');
            if ($XDestination == null || $YDestination == null)
                throw new StatusErrorException('Coordinates destination not defined', $this->status->main_errors);

            $idWorld = $this->GetVar('world_id');
            if ($idWorld == null || $idWorld == '')
                throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);

            $XSource = $this->GetVar('x_s');
            $YSource = $this->GetVar('y_s');
            if ($XSource == null || $YSource == null)
                throw new StatusErrorException('Coordinates point sender not defined', $this->status->main_errors);

            $unitsJSON = $this->GetVar('units');
            if ($unitsJSON == null || $unitsJSON == '')
                throw new StatusErrorException('Parameter `units` not defined', $this->status->main_errors);

            $units = json_decode($unitsJSON);
            if (!is_array($units) || empty($units))
                throw new StatusErrorException('Parameter `units` must contain data', $this->status->main_errors);

            $resourcesJSON = $this->GetVar('resources');
            $resources = array();
            if ($resourcesJSON != null) $resources = json_decode($resourcesJSON);

            $unitsMovingModel = unit_UnitsMoving::model();

            // Формируем данные и сохраняем в временную таблицу перемещения отряда
            $idSquad = $unitsMovingModel->startMovingUnitsPersonage(
                $units,
                $resources,
                $XSource,
                $YSource,
                $XDestination,
                $YDestination,
                $typeDestination,
                $idWorld,
                $idPersonage
            );

            if ($idSquad) {
                $squad = $unitsMovingModel->findSquadByIdIncompleteInformation($idSquad);
                if (empty($squad)) throw new StatusErrorException('Squad not found', $this->status->main_errors);

                $this->Viewer_Assign('status', $this->status->successfully);
                $this->Viewer_Assign('squad', $squad->properties);
            } else
                throw new StatusErrorException('New squad not create', $this->status->main_errors);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionStartMoveUnits` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionStartMoveUnits` error: ', $e->getMessage());
        }
    }

    /**
     * Действие на обработку события старта отмены перемещения юнитов.
     * @author Greg
     */
    public function actionStartCancelMoveUnits()
    {
        try {
            $idSquad = $this->GetVar('squad_id');

            $unitsMovingModel = unit_UnitsMoving::model();
            $result = $unitsMovingModel->cancelMovingUnits($idSquad, Auth::getIdPersonage());

            $squad = $unitsMovingModel->findSquadByIdIncompleteInformation($idSquad);
            if ($squad == null) throw new StatusErrorException('Squad not found', $this->status->main_errors);

            $this->Viewer_Assign('status', $result ? $this->status->successfully : $this->status->main_errors);
            $this->Viewer_Assign('squad', $squad->properties);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionCancelLast` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelLast` error: ', $e->getMessage());
        }
    }

    /**
     * Обработчик действия события завершения отмены перемещения юнитов.
     */
    public function actionFinishCancelMovingUnits()
    {
        try {
            $idSquad = $this->GetVar('squad_id');
            if ($idSquad == null)
                throw new StatusErrorException('Parameter `squad_id` not defined', $this->status->main_errors);

            $result = unit_strategy_UnitsMoving::finishCancelMovingUnits($idSquad, $this->status, $this);

            $this->Viewer_Assign('status', $result ? $this->status->successfully : $this->status->main_errors);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionCancelLast` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelLast` error: ', $e->getMessage());
        }
    }

    /**
     * Отмена найма последнего поставленного в очередь юнита
     *
     * @throws StatusErrorException
     */
    public function actionCancelLast()
    {
        try {

            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $result = personage_Unit::model()->cancelLast($idPersonageBuilding);

            $this->Viewer_Assign('status', $result ? $this->status->successfully : $this->status->main_errors);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionCancelLast` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelLast` error: ', $e->getMessage());
        }
    }


    /**
     * Нанимаем юнита и добавляем временную метку окончания найма
     *
     * @throws StatusErrorException
     */
    public function actionHiringUnits()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $unitId = $this->GetVar('unit_id');
            if ($unitId === null)
                throw new StatusErrorException('Parameter `unit_id` not defined', $this->status->main_errors);

            //$unitType = $this->GetVar('unit_type');
            //if ($unitType === null)
            //throw new StatusErrorException('Parameter `unit_type` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $unitCount = $this->GetVar('unit_count');
            if ($unitCount === null)
                throw new StatusErrorException('Parameter `unit_count` not defined', $this->status->main_errors);

            personage_Unit::model()->hiringUnit($unitId, $unitCount, $idPersonageBuilding);

            $this->Viewer_Assign('status', $this->status->successfully);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionHireUnit` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionHireUnit` error: ', $e->getMessage());
        }
    }


    /**
     * Предоставляем список юнитов для определённого здания
     */
    public function actionGetUnits()
    {
        try {
            $idBuilding = $this->GetVar('building_id');
            if ($idBuilding === null)
                throw new StatusErrorException('Parameter `building_id` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            // Получаем ИД персонажа
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            // Инициализация
            $arUnits = array();
            $arInQuery = array();

            // Проверяем, доступны ли юниты в здании
            $building = building_Mapper::model()->findBuildingById($idBuilding);
            if (!empty($building) && $building->unit == "y") {

                switch ($building->name) {
                    case building_Mapper::KEY_BUILDING_TAVERN:
                    {
                        // Отдаем юнитов для таверны
                        $units = unit_Spy::model()->findAllUnitsByIdPersonageBuilding($idPersonageBuilding);
                        // Переводим в массив
                        $arUnits = $this->formatJSONResponse($units);
                        // Для каждого юнита получаем необходимые ресурсы
                        foreach ($arUnits as $keyUnit => $arUnit) {
                            $arResources = $this->formatJSONResponse(unit_SpyResourceValue::model()->findAllById($arUnit['id_unit'], $idPersonageBuilding));
                            $arUnits[$keyUnit]['resource'] = $this->formatResource($arResources);
                        }

                        // Получаем юнитов, которые находятся в очереди на найм
                        $arInQuery = $this->formatJSONResponse(unit_Spy::model()->findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding));
                        break;
                    }
                    case building_Mapper::KEY_BUILDING_STABLE: /*{
						// Отдаем юнитов для конюшни
						$units = unit_Mapper::model()->findAllUnitsByIdPersonageBuilding($idPersonageBuilding);
						// Переводим в массив
						$arUnits = $this->formatJSONResponse($units);
						// Для каждого юнита получаем необходимые ресурсы
						foreach($arUnits as $keyUnit => $arUnit)
						{
							$arResources = $this->formatJSONResponse(unit_ResourceValue::model()->findAllById($arUnit['id_unit'], $idPersonageBuilding));
							$arUnits[$keyUnit]['resource'] = $this->formatResource($arResources);
						}
						
						// Получаем юнитов, которые находятся в очереди на найм
						$arInQuery = $this->formatJSONResponse(unit_Mapper::model()->findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding));
						break;
					}*/
                    case building_Mapper::KEY_BUILDING_BARRACKS:
                    {
                        // Отдаем юнитов для казармы
                        $units = unit_Mapper::model()->findAllUnitsByIdPersonageBuilding($idPersonageBuilding);
                        // Переводим в массив
                        $arUnits = $this->formatJSONResponse($units);
                        // Для каждого юнита получаем необходимые ресурсы
                        foreach ($arUnits as $keyUnit => $arUnit) {
                            $arResources = $this->formatJSONResponse(unit_ResourceValue::model()->findAllById($arUnit['id_unit'], $idPersonageBuilding));
                            $arUnits[$keyUnit]['resource'] = $this->formatResource($arResources);
                        }

                        // Получаем юнитов, которые находятся в очереди на найм
                        $arInQuery = $this->formatJSONResponse(unit_Mapper::model()->findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding));
                        break;
                    }
                    case building_Mapper::KEY_BUILDING_SACRED_GROVE:
                    {
                        // Отдаем юнитов для священной рощи
                        $units = unit_Religion::model()->findAllUnitsByIdPersonageBuilding($idPersonageBuilding);
                        // Переводим в массив
                        $arUnits = $this->formatJSONResponse($units);
                        // Для каждого юнита получаем необходимые ресурсы
                        foreach ($arUnits as $keyUnit => $arUnit) {
                            $arResources = $this->formatJSONResponse(unit_ReligionResourceValue::model()->findAllById($arUnit['id_unit'], $idPersonageBuilding));
                            $arUnits[$keyUnit]['resource'] = $this->formatResource($arResources);
                        }

                        // Получаем юнитов, которые находятся в очереди на найм
                        $arInQuery = $this->formatJSONResponse(unit_Religion::model()->findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding));
                        break;
                    }
                    case building_Mapper::KEY_BUILDING_BARD_COLLEGE:
                    {
                        // Отдаем юнитов для Коллегии бардов
                        $units = unit_Artist::model()->findAllUnitsByIdPersonageBuilding($idPersonageBuilding);
                        // Переводим в массив
                        $arUnits = $this->formatJSONResponse($units);
                        // Для каждого юнита получаем необходимые ресурсы
                        foreach ($arUnits as $keyUnit => $arUnit) {
                            $arResources = $this->formatJSONResponse(unit_ArtistResourceValue::model()->findAllById($arUnit['id_unit'], $idPersonageBuilding));
                            $arUnits[$keyUnit]['resource'] = $this->formatResource($arResources);
                        }

                        // Получаем юнитов, которые находятся в очереди на найм
                        $arInQuery = $this->formatJSONResponse(unit_Artist::model()->findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding));
                        break;
                    }
                    case building_Mapper::KEY_BUILDING_CASTLE:
                    {
                        // Отдаем юнитов для Крепость
                        $units = unit_Knight::model()->findAllUnitsByIdPersonageBuilding($idPersonageBuilding);
                        // Переводим в массив
                        $arUnits = $this->formatJSONResponse($units);
                        // Для каждого юнита получаем необходимые ресурсы
                        foreach ($arUnits as $keyUnit => $arUnit) {
                            $arResources = $this->formatJSONResponse(unit_KnightResourceValue::model()->findAllById($arUnit['id_unit'], $idPersonageBuilding));
                            $arUnits[$keyUnit]['resource'] = $this->formatResource($arResources);
                        }

                        // Получаем юнитов, которые находятся в очереди на найм
                        $arInQuery = $this->formatJSONResponse(unit_Knight::model()->findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding));
                        break;
                    }
                }
            }

            $this->Viewer_Assign('available_units', $arUnits);
            $this->Viewer_Assign('units_in_query', $arInQuery);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `getUnits` validate: ');
        }
        catch (Exception $e) {
            e1('Action `getUnits` error: ', $e->getMessage());
        }
    }

    /**
     * Переопределяем наследуемый метод для удаления NULL значений приходящих из базы данных
     *
     * @param array $data
     * @return array
     */
    protected function formatJSONResponse(array $data)
    {
        $response = array();

        /* @var $value Mapper */
        foreach ($data as $value) {
            $filters = array(null);
            $properties = $value->getProperties();
            array_push($response, array_diff($properties, $filters));
        }

        return $response;
    }

    /**
     * Форматируем ресурсы, в соответствии с требованиями интерфейса
     *
     * @param array $arResources
     * @return array
     */
    protected function formatResource(array $arResources)
    {
        $result = array();

        foreach ($arResources as $keyResource => $arResource) {
            $key = $arResource['resource_name'];
            unset($arResource['resource_name']);
            $result[$key] = $arResource;
        }

        return $result;
    }

    /**
     * Функция-событие роспуска юнитов
     *
     * @throws StatusErrorException
     */
    public function actionDismissUnits()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $unitId = $this->GetVar('unit_id');
            if ($unitId === null)
                throw new StatusErrorException('Parameter `unit_id` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $unitCount = $this->GetVar('unit_count');
            if ($unitCount === null)
                throw new StatusErrorException('Parameter `unit_count` not defined', $this->status->main_errors);

            // Получить текущее количество юнитов
            $unitsGroups = personage_Unit::model()->findAllHiredByIdUnitAndIdPersonageBuildingAndIdPersonage($unitId, $idPersonageBuilding, $idPersonage);

            // Считаем общее количество юнитов
            $hired = 0;
            foreach ($unitsGroups as $unitGroup)
                $hired += $unitGroup->count;

            // Проверяем есть ли достаточное количество юнитов для роспуска
            if ($hired >= $unitCount) {
                // Роспускаем
                foreach ($unitsGroups as $unitGroup) {
                    if ($unitCount > 0) {
                        // Если оставшееся не расформированное число юнитов
                        // больше чем количество юнитов в группе
                        if ($unitCount >= $unitGroup->count) {
                            // Расформировываем группу
                            if (personage_Unit::model()->disbandUnits($unitGroup->id_unit_personage, $idPersonage))
                                // Уменьшаем оставшееся число юнитов
                                $unitCount -= $unitGroup->count;
                        } else {
                            // Иначе уменьшаем число группы
                            if (personage_Unit::model()->writeDownCountUnitsInConcreteCity(
                                array((object)array(
                                   // "building_id" => $unitGroup->id_building_personage,
                                    "location" => $unitGroup->id_building_personage,
                                    "unit_id" => $unitGroup->unit_id,
                                    "count" => $unitCount,
                                )),
                                $idPersonage
                            )
                            )
                                $unitCount = 0;
                        }
                    } else // ($unitCount > 0)
                        // выходим - всех распустили
                        break;
                }
            } else
                throw new StatusErrorException('The specified number of units not available for dismiss.', $this->status->units_not_available_dismiss);

            $this->Viewer_Assign('status', $this->status->successfully);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionDisbandInits` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionDisbandInits` error: ', $e->getMessage());
        }
    }

    /**
     * Функция-событие завершения найма юнитов со стороны клиента
     *
     * @throws StatusErrorException
     */
    public function actionFinishHiringUnits()
    {
        try {
            $personageId = Auth::getIdPersonage();
            if ($personageId == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

//            $idPersonageUnit = $this->GetVar('personage_unit_id');
//            if ($idPersonageUnit === null)
//                throw new StatusErrorException('Parameter `personage_unit_id` not defined', $this->status->main_errors);

//            if (!personage_Unit::model()->finishUnitsRentById($idPersonageUnit, $idPersonage))
//                throw new StatusErrorException('Unable to complete the hiring units.', $this->status->main_errors);

            if (!personage_Unit::model()->finishUnitsRent($personageId)) 
                throw new StatusErrorException('There is no units that end their training.', $this->status->main_errors);
          
            $this->Viewer_Assign('status', $this->status->successfully);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionFinishHiringUnits` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionFinishHiringUnits` error: ', $e->getMessage());
        }
    }
    
    public function getSquads()
    {
        $squads = unit_UnitsMoving::model()->getAllPersonageSquads(Auth::getIdPersonage());
        print_r($squads);
    }
}
