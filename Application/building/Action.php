<?php
/**
 * Файл содержит логику обработки и управление зданиями
 * @author Greg
 * @package
 */

/**
 * Description of Action
 *
 * @property Auth $oAuth
 * @property Viewer $oViewer
 * @property Config $oConfig
 * @property array $json
 * @author al
 */
class building_Action extends JSONAction
{
    const NO_FREE_SECTIONS = 0;
    const NO_BONUSES = 0;
    const NO_VALUE = 0;

    public function RegisterEvent()
    {
        $this->AddEvent('type_default.json', 'getTypeDefaultBuilding');
        $this->AddEvent('type_development.json', 'getTypeDevelopmentBuilding');
        $this->AddEvent('classifier_basic.json', 'actionGetBasicBuilding');
        $this->AddEvent('classifier_resource.json', 'actionGetResourceBuilding');
        $this->AddEvent('main_info.json', 'actionMainInfoBuilding');
        $this->AddEvent('improve.json', 'actionGetImproveBuilding');
        $this->AddEvent('hold_improve.json', 'actionHoldImprove');
        $this->AddEvent('building_concrete_improve.json', 'actionImprovementSpecificBuildings');
        $this->AddEvent('new.json', 'actionSetNewBuilding');
        $this->AddEvent('finish_improve.json', 'actionFinishCreateAndImproveBuilding');
        $this->AddEvent('cancel_improve.json', 'actionCancelCreateAndImproveBuilding');
        $this->AddEvent('finish_upgrade.json', 'actionFinishImproveBuilding');
        $this->AddEvent('production.json', 'actionProductionBuildingProducts');
        $this->AddEvent('stop_production.json', 'actionStopProductionBuildingProducts');
        $this->AddEvent('upgrade_info.json', 'actionGetUpgradeInfo');
        $this->AddEvent('all_resource.json', 'actionGetAllResourceBuilding');
        $this->AddEvent('ruin.json', 'actionRuinBuilding');
        $this->AddEvent('cancel_internal_improvement.json', 'actionCancelInternalImproveBuilding');

        //$this->SetDefaultEvent('default');
    }

    /**
     * Действие является API для разрушения здания и связанных с ним бонусов и улучшений
     *
     * @throws StatusErrorException
     * @throws Exception
     */
    public function actionRuinBuilding()
    {
        try {
            $idPersonage = Auth::getIdPersonage();

            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idBuilding = $this->GetVar('building_id');
            if ($idBuilding === null)
                throw new StatusErrorException('Parameter `building_id` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            $personageBuilding = personage_Building::model()->findBuildingById($idPersonageBuilding, $idPersonage);

            if ($personageBuilding->building_id != $idBuilding) {
                throw new StatusErrorException('Sneaky inquiry', $this->status->main_errors);
            }

            $infoBuilding = building_Mapper::model()->findBuildingById($idBuilding);

            //Проверяем является ли здание ресурсным
            if ($infoBuilding->classifier != building_Mapper::RESOURCE_CLASSIFIER) {
                throw new StatusErrorException('Not delete buildings', $this->status->not_delete_buildings);
            }

            //Определяем количество зданий одного типа
            $numberBuilding = personage_Building::model()->determineCountOfBuildingsOnIdOfBuilding($idBuilding, $personageBuilding->city_id);

            if ($numberBuilding == NULL) {
                throw new StatusErrorException('There are no data about the number of buildings', $this->status->main_errors);
            }

            //Запрещаем разрушение здания в случае отсутсвия последнего основного ресурсного здания
            if ($numberBuilding->total_number_building <= personage_Building::NOT_AVAILABLE_BUILDINGS AND
                $infoBuilding->type == building_Mapper::DEFAULT_TYPE
            ) {
                throw new StatusErrorException('Destroy the building temporarily banned', $this->status->destroy_building_banned);
            }

            //Запрещаем удаление последнего здания одного типа, которые даются в самом начале игры
            if (personage_Building::LAST_BUILDING == $numberBuilding->total_number_building AND
                $infoBuilding->type == building_Mapper::DEFAULT_TYPE
            ) {
                $this->Viewer_Assign('last_building', true);
            } else {
                //Проводим разрушение здания и удаление всех бонусов и улучшений
                $doneRuinBuilding = personage_Building::model()->ruinBuildingPersonage($idPersonageBuilding);

                //Передаем клиентской части статус в зависимости от результата
                if ($doneRuinBuilding === true) {
                    $this->Viewer_Assign('status', $this->status->successfully);
                } else {
                    $this->Viewer_Assign('status', $this->status->main_errors);
                }
            }
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionRuinBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionRuinBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Действие предоставляет все ресурсные здания
     *
     * @throws StatusErrorException
     */
    public function actionGetAllResourceBuilding()
    {
        try {
            $allResourceBuilding = building_Mapper::model()->findAllClassifierBuilding(building_Mapper::RESOURCE_CLASSIFIER);

            if ($allResourceBuilding == NULL) {
                throw new StatusErrorException('No details about the buildings by classifier', $this->status->main_errors);
            } else {
                $this->Viewer_Assign('buildings', $this->formatJSONResponse($allResourceBuilding));
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionGetAllResourceBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionGetAllResourceBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Действие предоставляет данные для внутреннего улучшения здания
     *
     * @throws StatusErrorException
     */
    public function actionGetUpgradeInfo()
    {
        try {
            $idUpgrade = $this->GetVar('upgrade_id');
            if ($idUpgrade == null)
                throw new StatusErrorException('Parameter `upgrade_id` not defined', $this->status->main_errors);

            $idPersonage = Auth::getIdPersonage();

            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding == null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            $allBonuses = building_Upgrade::model()->findBuildingBonusesNeededUpgrade($idUpgrade);

            $deletedNullValues = $this->formatJSONResponse($allBonuses);

            //Удалем из массива значения которые не относятся к бонусам
            $filterBonuses = array();
            foreach ($deletedNullValues as $field) {

                if (isset($field["id_building_upgrade"], $field["id_building_level_upgrade"], $field["name_upgrade"],
                $field["time_research"])
                ) {
                    unset($field["id_building_upgrade"], $field["id_building_level_upgrade"], $field["name_upgrade"],
                    $field["time_research"]);
                }

                $filterBonuses = $field;
            }

            //Удаляем из массива элементы у которого значение бонусов ноль.
            $bonus = array();
            foreach ($filterBonuses as $field => $value) {

                if ($filterBonuses[$field] == self::NO_BONUSES) {
                    unset($filterBonuses[$field]);
                }

                $bonus = $filterBonuses;
            }

            $upgradeValue = building_UpgradeResource::model()->findValueResourcesNeededUpgrade($idUpgrade, $idPersonage,
                $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y'], $idPersonageBuilding);

            if (empty($upgradeValue))
                throw new StatusErrorException('No data to improve the internal building', $this->status->main_errors);

            $formattedResponse = array();
            foreach ($upgradeValue as $resource) {
                $nameResource = $resource->name_resource;
                if ($nameResource != null)
                    $formattedResponse['resources'][$nameResource] =
                        array('required' => $resource->required_resource, 'has' => $resource->has_resource);
            }

            //Проверяем существует ли данное внутреннее улучшения, для определения его статуса и времени обратного отсчета
            $existingImprovementBuilding = personage_Improve::model()->findImproveForBuildingPersonage($idUpgrade,
                $idPersonageBuilding, $idPersonage
            );


            switch ($existingImprovementBuilding->status) {
                case NULL:
                    $statusImprovementBuilding = personage_Improve::STATUS_NOT_STARTED;
                    $remainingTime = personage_Building::NO_VALUE;
                    break;

                case personage_Improve::STATUS_NOT_STARTED:
                    $statusImprovementBuilding = personage_Improve::STATUS_NOT_STARTED;
                    $remainingTime = personage_Building::NO_VALUE;
                    break;

                case personage_Improve::STATUS_FINISH:
                    $statusImprovementBuilding = personage_Improve::STATUS_FINISH;
                    $remainingTime = personage_Building::NO_VALUE;
                    break;

                case personage_Improve::STATUS_PROCESS:
                    $statusImprovementBuilding = personage_Improve::STATUS_PROCESS;
                    $remainingTime = models_Time::model()->calculateRemainingTimeInSeconds($existingImprovementBuilding->unix_finish_time_improve);
                    break;
            }

            $personageResearch = personage_ResearchState::model()->findPersonageResearchAndUpgradeOnIdUpgrade($idUpgrade, $idPersonage);

            if ($personageResearch == NULL) {
                throw new StatusErrorException('No data personages research', $this->status->main_errors);
            }

            $formattedResponse['name_building'] = $upgradeValue[0]->name_building;
            $formattedResponse['time_improve'] = models_Time::model()->getCountNumberOfSecondsInMinute($allBonuses[0]->time_research);
            $formattedResponse['max_access_level'] = $upgradeValue[0]->max_access_level;
            $formattedResponse['current_level_building'] = $upgradeValue[0]->current_level_building;
            $formattedResponse['status_upgrade'] = $statusImprovementBuilding;
            $formattedResponse['process'] = $remainingTime;

            //Данные глобальных исследований
            $formattedResponse['name_research'] = $personageResearch->name_research;
            $formattedResponse['current_level_research_personage'] = $personageResearch->current_level;
            $formattedResponse['required_level_research'] = $personageResearch->level_for_upgrade;

            $this->Viewer_Assign('bonus', $bonus);
            $this->Viewer_Assign('upgrade', $formattedResponse);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionGetUpgradeInfo` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionGetUpgradeInfo` error: ', $e->getMessage());
        }
    }

    /**
     * Предоставляем здания по типу (по умолчания)
     */
    public function getTypeDefaultBuilding()
    {
        try {
            $buildingType = building_Mapper::model()->findTypeDefaultBuilding();

            if ($buildingType == NULL) {
                throw new StatusErrorException('No details about the buildings by type', $this->status->main_errors);
            } else {
                $this->Viewer_Assign('buildings', $this->formatJSONResponse($buildingType));
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `getTypeDefaultBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `getTypeDefaultBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Предоставляем здания по типу (в процессе развития)
     */
    public function getTypeDevelopmentBuilding()
    {
        try {
            $buildingType = building_Mapper::model()->findTypeDevelopmentBuilding();

            if ($buildingType == NULL)
                throw new StatusErrorException('No details about the buildings by type', $this->status->main_errors);
            else
                $this->Viewer_Assign('buildings', $this->formatJSONResponse($buildingType));


        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `getTypeBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `getTypeBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Предоставляем здания по классификации (основные)
     */
    public function actionGetBasicBuilding()
    {
        try {
            if (Auth::getIdPersonage() == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();
            $idPersonage = Auth::getIdPersonage();
            $buildings = personage_Building::model()->findAllBaseBuildings(
                $idPersonage, $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y']
            );

            $this->setResponseForEventInitBuildingBook($buildings, $idPersonage, $coordinatesCurrentLocation);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `actionGetBasicBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionGetBasicBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Предоставляем здания по классификации (ресурсные)
     */
    public function actionGetResourceBuilding()
    {
        try {
            fb($_SESSION, 'sess', FirePHP::ERROR);
            if (Auth::getIdPersonage() == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();
            $idPersonage = Auth::getIdPersonage();

            $buildings = personage_Building::model()->findAllResourceBuildings(
                $idPersonage, $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y']
            );

            $this->setResponseForEventInitBuildingBook($buildings, $idPersonage, $coordinatesCurrentLocation);

            //Получаем количество пустых площадок для строительства
            $sections = $this->getRemainingNumberConstructionSections($idPersonage, $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y']);

            $this->Viewer_Assign('sections', $sections);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'actionGetResourceBuilding` validate: ');
        } catch (Exception $e) {
            e1('Action `actionGetResourceBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * @param $buildings
     * @param $idPersonage
     * @param $coordinatesCurrentLocation
     * @throws StatusErrorException
     */
    private function setResponseForEventInitBuildingBook($buildings, $idPersonage, $coordinatesCurrentLocation)
    {
        if (empty($buildings))
            throw new StatusErrorException(
                'Buildings is not found or is not a personage in city',
                $this->status->main_errors
            );

        $propertiesFirstBuilding = building_Mapper::model()->findBuildingWithResourceAndUpgrade(
            $buildings[0]->id, $idPersonage,
            $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y']
        );

        if (empty($propertiesFirstBuilding))
            throw new StatusErrorException(
                'Buildings is not found or is not a personage in city',
                $this->status->main_errors
            );

        $this->Viewer_Assign('buildings', $this->formatJSONResponse($buildings));
        $this->Viewer_Assign('building', $this->formatResponseForInfoAtBuilding($propertiesFirstBuilding));
    }

    /**
     * Событие опроса информации о здании, обновлении и ресурсов требуемых и для текущего персонажа.
     * Выводится на вкладке "Общие".
     * Если персонаж находится не в своём городе или персонаж не имеет такого города, по текущим координатам,
     * возникает ошибка.
     *
     * @throws StatusErrorException
     */
    public function actionMainInfoBuilding()
    {
        try {
            $doneFinishedBuilding = false;

            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idBuilding = $this->GetVar('building_id');
            if ($idBuilding === null)
                throw new StatusErrorException('Parameter `building_id` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            //Получаем данные для конкретного здания
            $building = building_Mapper::model()->findBuildingWithResourceAndUpgrade(
                $idBuilding, Auth::getIdPersonage(), $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y'],
                $idPersonageBuilding);

            //Проверем не закончено ли время создания здания, и в случае необходимости обновляем статус
            if ($building[0]->status_construction == personage_Building::STATUS_CONSTRUCTION_PROCESSING) {
                if ($building[0]->unix_finish_time_construction - time() <= self::NO_VALUE) {
                    $finishedBuilding = personage_Building::model()->findBuildingsWithFinishCreateAndImprove($building[0]->id_building_personage);
                    $doneFinishedBuilding = personage_Building::model()->finishConstructOrImproveBuildings($finishedBuilding);
                    unset($building);
                }
            }

            //Проверяем удачно ли окончена постройка здания и в случае успеха получаем данные по зданию повторно
            if ($doneFinishedBuilding === true) {
                $building = building_Mapper::model()->findBuildingWithResourceAndUpgrade(
                    $idBuilding, Auth::getIdPersonage(), $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y'],
                    $idPersonageBuilding);
            }

            //Проверяем является ли здание "ЗАМОК"
            if ($building[0]->name_building == building_Mapper::KEY_BUILDING_CASTLE) {

                //Получаем уровень города
                $levelCity = personage_City::model()->detectLevelCity($idPersonage);
                $building[0]->current_level_building_castle = $levelCity;
            }

            //Передаем клиенту статус в зависимости от результата
            if (empty($building))
                throw new StatusErrorException(
                    'Buildings is not found or is not a personage in city',
                    $this->status->main_errors
                );

            $this->Viewer_Assign('building', $this->formatResponseForInfoAtBuilding($building));

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionInfoMainBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionInfoMainBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Форматирование результатов для ответа клиенту одного здания с ресурсами.
     * (для вкладки "Общие")
     *
     * @param array $buildingAndResources
     * @return array
     */
    private function formatResponseForInfoAtBuilding($buildingAndResources)
    {
        $formattedResponse = array('resources' => array());
        $data = $buildingAndResources[0];

        $formattedResponse['name_building'] = building_Mapper::KEY_BUILDING_CASTLE;
        $formattedResponse['current_name_building'] = $data->name_building;
        $formattedResponse['name_level_building'] = $data->name_level_building;
        $formattedResponse['max_access_level'] = $data->level;

        //Свойство передает данные для зданий уровень ЗАМКА, а для ЗАМКА уровень города
        $formattedResponse['current_level_building'] = $data->current_level_building_castle;
        $formattedResponse['real_level_building'] = $data->current_level_building;
        $formattedResponse['status_upgrade'] = $data->status_construction;
        $formattedResponse['status_production'] = $data->status_production;
        $formattedResponse['unit'] = $data->unit;
        $formattedResponse['time'] = models_Time::model()->getCountNumberOfSecondsInMinute($data->time_building);

        //Формируем оставшееся время постройки/улучшения
        if ($data->unix_finish_time_construction - time() <= self::NO_VALUE OR
            $data->status_construction == personage_Building::STATUS_CONSTRUCTION_FINISH
        ) {
            $formattedResponse['process'] = self::NO_VALUE;
        } else {
            $formattedResponse['process'] = $data->unix_finish_time_construction - time();
        }

        if ($data->classifier == building_Mapper::BASIC_CLASSIFIER) {

            //Бонусы для основных зданий
            $formattedResponse['base_bonus'] = building_BasicLevel::unpackingBonuses($data->base_bonus);
        } else {

            //Бонусы за внутреннее улучшение зданий
            $formattedBonusResourceBuilding = array('bonus_number_products' => $data->bonus_number_products,
                'bonus_time_production' => $data->bonus_time_production,
                'bonus_capacity_buildings' => $data->bonus_capacity,
                'bonus_population_growth' => $data->bonus_population_growth,
                'bonus_population_health' => $data->bonus_health);

            $sortBonusResourceBuilding = $this->clearArrayWithNoNullValue($formattedBonusResourceBuilding);
        }

        if (!empty($sortBonusResourceBuilding)) {
            $formattedResponse['base_bonus'] = $sortBonusResourceBuilding;
        }

        foreach ($buildingAndResources as $resource) {
            $nameResource = $resource->name_resource;
            if ($nameResource != null)
                $formattedResponse['resources'][$nameResource] =
                    array('required' => $resource->required_resource, 'has' => $resource->has_resource);
        }

        return $formattedResponse;
    }

    /**
     * Действие реагирующее на запрос получения улучшений для определённого здания.
     * Если персонаж находится не в своём городе или персонаж не имеет такого города, по текущим координатам,
     * возникает ошибка.
     *
     * @throws StatusErrorException
     */
    public function actionGetImproveBuilding()
    {
        try {
            $idBuilding = $this->GetVar('building_id');
            if ($idBuilding == null)
                throw new StatusErrorException('Parameter `building_id` not defined', $this->status->main_errors);

            $idBuildingPersonage = $this->GetVar('personage_building_id');
            if ($idBuildingPersonage == null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            $improve = building_Upgrade::model()->findImproveBuildingAtCurrentLevelForPersonageAndCity(
                $idBuilding, Auth::getIdPersonage(), $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y'],
                $idBuildingPersonage
            );

            if (empty($improve))
                throw new StatusErrorException(
                    'Improve is not found or is not a personage in city',
                    $this->status->main_errors
                );

            $formattedResponse = array('improve' => array());
            foreach ($improve as $i) {
                $currentImprove = array();
                $currentImprove['name_improve'] = $i->name_improve;
                $currentImprove['id_improve'] = $i->id_improve;
                $currentImprove['time_improve'] = $i->time_improve;
                $currentImprove['finish_time_improve'] = $i->finish_time_improve;
                $currentImprove['status_improve'] = $i->status_improve;
                $currentImprove['required_level_building'] = $i->required_level_building;
                $currentImprove['current_level_building'] = $i->current_level_building;

                array_push($formattedResponse['improve'], $currentImprove);
            }

            $this->Viewer_Assign($formattedResponse);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionGetImproveBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionGetImproveBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Действие является API для улучшения конкретно здания
     * Строить можно одновременно только одно здание каждого типа (основные, ресурсные), в каждом городе.
     *
     * @throws StatusErrorException
     */
    public function actionImprovementSpecificBuildings()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            //Получить данные касающиеся запрашиваемого здания персонажа
            $result = personage_Building::model()->findValueOfBuildingPersonage($idPersonageBuilding,
                $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y'],
                $idPersonage,
                personage_Building::STATUS_CONSTRUCTION_PROCESSING);

            //Проверяем нет строящихся/улучшающихся основных зданий
            if ($result[0]->classifier == building_Mapper::BASIC_CLASSIFIER) {
                $constructionBasicBuilding = personage_Building::model()->findListOfAllBuildings($idPersonage,
                    building_Mapper::BASIC_CLASSIFIER,
                    $coordinatesCurrentLocation['x'],
                    $coordinatesCurrentLocation['y']);

                foreach ($constructionBasicBuilding as $building) {
                    if ($building->status_construction == personage_Building::STATUS_CONSTRUCTION_PROCESSING) {
                        throw new StatusErrorException('The process is not finished on the construction or improvement of the main building', $this->status->is_in_progress_basic_building);
                    }
                }
            }

            //Проверяем нет строящихся/улучшающихся ресурсных зданий
            if ($result[0]->classifier == building_Mapper::RESOURCE_CLASSIFIER) {
                $constructionResourcesBuilding = personage_Building::model()->findListOfAllBuildings($idPersonage,
                    building_Mapper::RESOURCE_CLASSIFIER,
                    $coordinatesCurrentLocation['x'],
                    $coordinatesCurrentLocation['y']);

                foreach ($constructionResourcesBuilding as $building) {

                    if ($building->status_construction == personage_Building::STATUS_CONSTRUCTION_PROCESSING) {
                        throw new StatusErrorException('The process is not finished on the construction or improvement of resources building', $this->status->is_in_progress_resources_building);
                    }
                }
            }

            //Определяем является ли здание "ЗАМОК ЛОРДА", то получаем уровень города
            if ($result[0]->name == building_Mapper::KEY_BUILDING_CASTLE) {
                $levelCity = personage_City::model()->detectLevelCity($idPersonage);
            } else {
                $levelCity = false;
            }

            //Определяем достаточный ли уровень города для проведения улучшения здания "ЗАМОК"
            if ($levelCity !== false AND $result[0]->nex_level_building > $levelCity) {
                throw new StatusErrorException('Insufficient level city', $this->status->insufficient_level_city);
            }

            //Определяем достаточный ли уровень здания "ЗАМОК" для проведения улучшения текущего здания
            if ($levelCity === false) {
                if ($result[0]->nex_level_building > $result[0]->current_level_building_castle) {
                    throw new StatusErrorException('Inadequate building "CASTLE"', $this->status->insufficient_level_building_castle);
                }
            }

            //Проверяем достаточно ресурсов для проведения улучшения здания
            if (!empty($result) AND is_array($result)) {
                foreach ($result as $resource) {
                    if ($resource->resources_personage == personage_ResourceState::NO_VALUE) {
                        throw new StatusErrorException('Not enough resources to carry out improvements', $this->status->no_resources);
                    }
                }
            }

            // В случае достаточного количества ресурсов в городе, вычитаем ресурсы за улучшение
            $doneResource = $this->_calculationAndSaveResources($result);

            if ($doneResource == NULL) {
                throw new StatusErrorException('Do not produced subtraction of resources for the improvement of buildings', $this->status->main_errors);
            }

            //TODO: После тестов удалить цифру 2 и раскомментировать строку ($result[0]->time_building)
            //Добававляем временную метку окончания улучшения здания
            $doneImprovingBuilding = personage_Building::model()->beginImprovingBuildingPersonage($idPersonageBuilding,
                //$result[0]->time_building,
                2,
                personage_Building::STATUS_CONSTRUCTION_PROCESSING
            );

            //Передаем клиентской части статус в зависимости от результата
            if ($doneImprovingBuilding === true) {

                // Проверка окончания времени постройки здания, если не закончено время постройки ответ отрицательный
                $buildingPersonage = personage_Building::model()->findBuildingById($idPersonageBuilding, $idPersonage);
                $this->Viewer_Assign('finish_end_time', $buildingPersonage->finish_time_construction - time());
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionImprovementSpecificBuildings` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionImprovementSpecificBuildings` error: ', $e->getMessage());
        }
    }


    /**
     * Добавляем временную метку окончания внутренних улучшения зданий
     *
     * @throws StatusErrorException
     */
    public function actionHoldImprove()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idBuilding = $this->GetVar('building_id');
            if ($idBuilding === null)
                throw new StatusErrorException('Parameter `building_id` not defined', $this->status->main_errors);

            $idImprove = $this->GetVar('improve_id');

            if ($idImprove === null)
                throw new StatusErrorException('Parameter `improve_id` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            //Получаем данные об уровне исследования
            $research = personage_ResearchState::model()->investigatedToDetermineHomeImprovement($idImprove, $idPersonage);

            //Определяем исследовано ли внутреннее улучшение
            if ($research == NULL) {
                throw new StatusErrorException('Technology has not been studied', $this->status->main_errors);
            }

          //  $buildingLibrary = personage_Building::model()->findBuildingPersonageOfNameBuilding(building_Mapper::KEY_BUILDING_LIBRARY,
               // $coordinatesCurrentLocation['x'],
               // $coordinatesCurrentLocation['y'],
               // $idPersonage);

            //Проверяем достаточный ли уровень здания БИБЛИОТЕКА в городе
            /*
            if ($research->level_for_upgrade > $buildingLibrary->current_level) {
                throw new StatusErrorException('Insufficient level library buildings', $this->status->insufficient_level_library_buildings);
            }
            */

            //Получаем данные для запуска времени улучшения здания
            $result = building_Upgrade::model()->findNextBuildingAtCurrentLevelForPersonageAndCity($idBuilding,
                $idImprove,
                $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y'],
                $idPersonage, $idPersonageBuilding);

            //Проверяем достаточный ли уровень здания для проведения внутреннего улучшения
            if ($result[0]->say_whether_level_building == building_Upgrade::IMPOSSIBLE_UPGRADE) {
                throw new StatusErrorException('Insufficient level of the building', $this->status->insufficient_level_building);
            }

            //Проверяем сужествует ли уже данное улучшение
            if ($result[0]->is_current_upgrade == building_Upgrade::IMPOSSIBLE_UPGRADE) {
                throw new StatusErrorException('Improvement already exists', $this->status->improvement_already_exists);
            }

            //Проверяем достаточно ресурсов для проведения улучшения здания
            if (!empty($result) AND is_array($result)) {
                foreach ($result as $resource) {
                    if ($resource->resources_personage == building_Upgrade::IMPOSSIBLE_UPGRADE) {
                        throw new StatusErrorException('Not enough resources to carry out improvements', $this->status->no_resources);
                    }
                }
            }

            // В случае достаточного количества ресурсов в городе, вычитаем ресурсы за улучшение
            $doneUpdateResource = $this->_calculationAndSaveResources($result);

            //Добававляем временную метку окончания улучшения здания
            if (!empty($doneUpdateResource)) {
                $doneFinishTimeImprove = personage_Improve::model()->insertFinishTimeImprove(
                    $result[0]->id_building_personage, //ID - таблицы (personages_buildings)
                    $idImprove, //ID - таблицы (personages_buildings)
                    $result[0]->time_research //Минуты для усовершенствования, таблица (building_upgrade)
                );
            }

            //Передаем клиентской части статус в зависимости от результата
            if ($doneFinishTimeImprove === true) {
                $internalImprove = personage_Improve::model()->findImproveForBuildingPersonage($idImprove, $idPersonageBuilding, $idPersonage);
                $remainingTime = models_Time::model()->calculateRemainingTimeInSeconds($internalImprove->unix_finish_time_improve);
                $this->Viewer_Assign('finish_end_time', $remainingTime);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`holdImprove` validate: ');
        }
        catch (Exception $e) {
            e1('Action `holdImprove` error: ', $e->getMessage());
        }
    }

    /**
     * Действие происходит при окончании таймера отсчета времени для внутренних улучшений зданий
     *
     * @throws StatusErrorException
     */
    public function actionFinishImproveBuilding()
    {
        try {
            if (Auth::getIdPersonage() == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $improvePersonageBuilding = personage_Improve::model()->findFinishImproveBuildings($idPersonageBuilding);

            if (empty($improvePersonageBuilding))
                throw new ErrorException('Not over time, improvements to the building ID - ' . $idPersonageBuilding);

            foreach ($improvePersonageBuilding as $improveBuilding) {

                //Получить сериализованные бонусы
                $bonus = personage_Improve::model()->recalculateBonusesWithInternalImprovements($improveBuilding);
            }

            //Обновляем бонусы зданию и оканчиваем внутренне улучшение
            $resultCommit = personage_Improve::model()->finishImproveBuildingsAndAddingBonuses($idPersonageBuilding,
                $bonus);

            //Передаем статус клиенту о результате выполненного действия
            if ($resultCommit === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionFinishImproveBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionFinishImproveBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Действие происходит при остановке процесса внутреннего улучшения
     *
     * Замечание: При остановке внутреннего улучшения, данное улучшение удаляется полностью из базы данных.
     * @throws StatusErrorException
     */
    public function actionCancelInternalImproveBuilding()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idUpgrade = $this->GetVar('improve_id');
            if ($idUpgrade === null)
                throw new StatusErrorException('Parameter `improve_id` not defined', $this->status->main_errors);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            $internalImprove = personage_Improve::model()->findImproveForBuildingPersonage($idUpgrade, $idPersonageBuilding, $idPersonage);

            //Убеждаемся, что процесс внутреннего улучшения закончен
            if ($internalImprove->status == personage_Improve::STATUS_FINISH) {
                throw new StatusErrorException('Internal improvement building is finished', $this->status->internal_improvement_building_is_finished);
            }

            //Перед удалением внутреннего улучшения убеждаемся, что улучшение еще в процессе
            if ($internalImprove->status == personage_Improve::STATUS_PROCESS) {
                $doneDeleteInternalImprove = personage_Improve::model()->deleteImproveSpecificallyBuilding($internalImprove->improve_id);
            }

            //Передаем статус клиенту о результате выполненного действия
            if ($doneDeleteInternalImprove === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionCancelInternalImproveBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelInternalImproveBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Создаем новое здание и добавляем временную метку окончания строительства
     *
     * @throws StatusErrorException
     */
    public function actionSetNewBuilding()
    {
        try {

            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idBuilding = $this->GetVar('building_id');
            if ($idBuilding === null)
                throw new StatusErrorException('Parameter `building_id` not defined', $this->status->main_errors);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            $doneFreeSections = $this->getRemainingNumberConstructionSections($idPersonage, $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y']);

            //Проверяем на наличие свободных участков для постройки зданий
            if (!empty($doneFreeSections) AND $doneFreeSections['free_sections'] == self::NO_FREE_SECTIONS) {
                throw new StatusErrorException('No free sections', $this->status->no_free_sections);
            }

            $checkResult = personage_Building::model()->checkWhetherConstructionBuilding($idBuilding, $idPersonage,
                $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y']);

            /*
             * Проверяем не относится здание к постройке возводимые в единственном экземпляре
             */
            if ($checkResult->classifier == building_Mapper::BASIC_CLASSIFIER) {
                throw new StatusErrorException('Unacceptable for building construction', $this->status->invalid_building);
            }

            $constructionResourcesBuilding = personage_Building::model()->findListOfAllBuildings($idPersonage,
                building_Mapper::RESOURCE_CLASSIFIER,
                $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y']);

            //Проверяем не идет постройка других ресурсных зданий
            foreach ($constructionResourcesBuilding as $building) {

                if ($building->status_construction == personage_Building::STATUS_CONSTRUCTION_PROCESSING) {
                    throw new StatusErrorException('The process is not finished on the construction or improvement of resources building', $this->status->is_in_progress_resources_building);
                }
            }

            $dataCurrentLevel = building_Development::model()->findDataConstructionBuildingCurrentLevel($idBuilding, building_Mapper::INITIAL_LEVEL,
                $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y'],
                $idPersonage);

            //В случае достаточного количества ресурсов в городе, вычитаем ресурсы за постройку здания
            if (!empty($dataCurrentLevel) AND is_array($dataCurrentLevel)) {
                foreach ($dataCurrentLevel as $resource) {
                    if ($resource->resources_personage == building_Mapper::IMPOSSIBLE_CONSTRUCT_BUILDING) {
                        throw new StatusErrorException('Not enough resources to carry out improvements', $this->status->no_resources);
                    }
                }
            }

            $doneUpdateResource = $this->_calculationAndSaveResources($dataCurrentLevel);

            //TODO: После тестов удалить цифру 2 и раскомментировать строку ($dataCurrentLevel[0]->time_building)
            //Добавляем новое здание с временной меткой окнчания постройки
            if (isset($dataCurrentLevel) AND $doneUpdateResource === true) {
                $idPersonageBuilding = personage_Building::model()->createNewBuildingAndTimestamp(
                    $idBuilding,
                    $idPersonage,
                    // $dataCurrentLevel[0]->time_building,
                    2,
                    $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y']);

                $doneBonus = personage_BuildingBonus::model()->addBonusSpecificBuilding($idPersonageBuilding, $dataCurrentLevel[0]->basic_bonus);
            }

            //Передаем клиентской части статус в зависимости от результата
            if ($doneBonus === true) {

                // Проверка окончания времени постройки здания, если не закончено время постройки ответ отрицательный
                $buildingPersonage = personage_Building::model()->findBuildingById($idPersonageBuilding, $idPersonage);
                $this->Viewer_Assign('finish_end_time', $buildingPersonage->finish_time_construction - time());
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }


        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`setNewBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `setNewBuilding` error: ', $e->getMessage());
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
     * Очищаем массив от NULL значений
     *
     * @param array $input
     * @return mixed
     */
    public function clearArrayWithNoNullValue(array $input)
    {
        foreach ($input as $field => $value) {

            if ($value != NULL) {
                $response[$field] = $value;
            }
        }

        return $response;
    }

    /**
     * Подсчёт ресурсов и обновление данных.
     *
     * @param $currentResource
     * @return array|bool
     */
    private function _calculationAndSaveResources($currentResource)
    {
        return personage_ResourceState::model()->updateResourcesWhenPerformingCalculations($currentResource);
    }

    /**
     * Действие на событие фиксации окончания постройки/улучшения здания.
     */
    public function actionFinishCreateAndImproveBuilding()
    {
        try {
            $idBuildingPersonage = $this->GetVar('building');
            if ($idBuildingPersonage === null)
                throw new StatusErrorException('Parameter `building` not defined', $this->status->main_errors);

            // Проверка окончания времени постройки здания, если не закончено время постройки ответ отрицательный
            $buildingPersonage = personage_Building::model()->findBuildingById($idBuildingPersonage, Auth::getIdPersonage());
            if ($buildingPersonage === null)
                throw new Exception(
                    'Building: ' . $idBuildingPersonage . ' for personage: ' . Auth::getIdPersonage() . ' not found'
                );

            if (time() < $buildingPersonage->finish_time_construction) {
                $this->Viewer_Assign('status', $this->status->main_errors);
                $this->Viewer_Assign('finish_end_time', $buildingPersonage->finish_time_construction - time());
            } else {

                //Оканчиваем строительство здания и обновление бонусов
                $building = personage_Building::model()->findBuildingsWithFinishCreateAndImprove($idBuildingPersonage);
                $isConstrictionBuilding = $buildingPersonage->finishConstructOrImproveBuildings($building);
            }

            if ($isConstrictionBuilding === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionInfoMainBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionInfoMainBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Действие на событие отмены создания/улучшения
     */
    public function actionCancelCreateAndImproveBuilding()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idBuildingPersonage = $this->GetVar('building');
            if ($idBuildingPersonage === null)
                throw new StatusErrorException('Parameter `building` not defined', $this->status->main_errors);

            // Проверка окончания времени постройки здания, если не закончено время постройки ответ отрицательный
            $buildingPersonage = personage_Building::model()->findBuildingById($idBuildingPersonage, $idPersonage);
            if ($buildingPersonage === null)
                throw new Exception(
                    'Building: ' . $idBuildingPersonage . ' for personage: ' . $idPersonage . ' not found'
                );

            if ($buildingPersonage->status_construction == personage_Building::STATUS_CONSTRUCTION_FINISH) {
                throw new StatusErrorException('The process has already finished', $this->status->process_construction_finished);
            }

            if ($buildingPersonage->status_construction == personage_Building::STATUS_CONSTRUCTION_CANCEL) {
                throw new StatusErrorException('The process is stopped', $this->status->process_is_stopped);
            }

            $doneChangeStatus = personage_Building::model()->changeCurrentStatusConstructionBuildingPersonage(
                personage_Building::STATUS_CONSTRUCTION_CANCEL,
                $idBuildingPersonage);

            if ($doneChangeStatus === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionCancelCreateAndImproveBuilding` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelCreateAndImproveBuilding` error: ', $e->getMessage());
        }
    }

    /**
     * Действие служит для возобновления производства зданием
     */
    public function actionProductionBuildingProducts()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            // Получаем инфо по зданию персонажа
            $personageBuilding = personage_Building::model()->findBuildingById($idPersonageBuilding, $idPersonage);
            if (empty($personageBuilding))
                throw new StatusErrorException('Personage building not found by id', $this->status->main_errors);

            // Получаем количество необходимого населения для здания
            $buildingDevel = building_Development::model()->findByIdBuildingAndLevelBuilding($personageBuilding->building_id, $personageBuilding->current_level);
            if (empty($buildingDevel))
                throw new StatusErrorException('Building not found by id and level', $this->status->main_errors);

            $needPeople = $buildingDevel->number_staff ? $buildingDevel->number_staff : 0;

            // Получаем количество свободного населения в городе
            $city = personage_City::model()->findById($personageBuilding->city_id);
            if (empty($city))
                throw new StatusErrorException('Personage city not found by id', $this->status->main_errors);

            $availablePeople = $city->free_people;

            if ($needPeople > $availablePeople)
                throw new StatusErrorException('There is not enough free population', $this->status->no_free_people);

            $status = personage_Building::STATUS_PRODUCTION;
            $doneResultChangeStatusProduction = $this->getResultChangeStatusProduction($status);

            if ($doneResultChangeStatusProduction === true) {

                // Обновить количество свободного населения в городе
                personage_City::model()->updateFreePeopleInCity($city->id);

                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionProductionBuildingProducts` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionProductionBuildingProducts` error: ', $e->getMessage());
        }
    }

    /**
     * Действие служит для остановки производства зданием
     */
    public function actionStopProductionBuildingProducts()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $idPersonageBuilding = $this->GetVar('personage_building_id');
            if ($idPersonageBuilding === null)
                throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

            // Получаем инфо по зданию персонажа
            $personageBuilding = personage_Building::model()->findBuildingById($idPersonageBuilding, $idPersonage);
            if (empty($personageBuilding))
                throw new StatusErrorException('Personage building not found by id', $this->status->main_errors);


            $status = personage_Building::STATUS_PRODUCTION_STOP;
            $doneResultChangeStatusProduction = $this->getResultChangeStatusProduction($status);

            if ($doneResultChangeStatusProduction === true) {

                // Обновить количество населения в городе
                personage_City::model()->updatePopulationInCity($personageBuilding->city_id);

                // Обновить количество свободного населения в городе
                personage_City::model()->updateFreePeopleInCity($personageBuilding->city_id);

                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, '`actionStopProductionBuildingProducts` validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionStopProductionBuildingProducts` error: ', $e->getMessage());
        }
    }

    /**
     * Получить оставшееся количество свободных площадок для строительства зданий
     *
     * @param $idPersonage
     * @param $x
     * @param $y
     * @return bool|mixed
     */
    public function getRemainingNumberConstructionSections($idPersonage, $x, $y)
    {
        $result = building_DefinitionSections::model()->findNumberSectionsLevelBuildingFortress($idPersonage, $x, $y,
            building_Mapper::RESOURCE_CLASSIFIER);
        $sections = array();

        if (!empty($result)) {

            //Получить количество свободных строительных площадок
            $sections['free_sections'] = building_DefinitionSections::model()->calculateRemainingNumberConstructionSections(
                $result->number_construction_sections, //Доступное количество площадок
                $result->constructed_buildings //Построено зданий
            );

            //Получить количество закрытых строительных площадок
            $sections['closed_sections'] = building_DefinitionSections::model()->calculateRemainingNumberConstructionSections(
                $result->max_number_construction_sections, //Максимальное количество площадок
                $result->constructed_buildings + $sections['free_sections'] //Построено зданий + свободных участков
            );
        }

        if ($sections['free_sections'] < self::NO_FREE_SECTIONS OR $sections['free_sections'] == self::NO_FREE_SECTIONS) {
            $sections['free_sections'] = self::NO_FREE_SECTIONS;
        }

        if ($sections['closed_sections'] < self::NO_FREE_SECTIONS OR $sections['closed_sections'] == self::NO_FREE_SECTIONS) {
            $sections['closed_sections'] = self::NO_FREE_SECTIONS;
        }

        return $sections;
    }

    /**
     * Получить результат по смене статуса производства зданием
     *
     * @param $status
     * @return bool
     * @throws StatusErrorException
     */
    public function getResultChangeStatusProduction($status)
    {
        if (empty($status))
            throw new StatusErrorException('Status production buildings are not defined', $this->status->main_errors);

        $idPersonageBuilding = $this->GetVar('personage_building_id');
        if ($idPersonageBuilding === null)
            throw new StatusErrorException('Parameter `personage_building_id` not defined', $this->status->main_errors);

        return personage_Building::model()->changeCurrentStatusProductionBuildingPersonage($status, $idPersonageBuilding);
    }
}
