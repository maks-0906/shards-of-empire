<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

/**
 * Description of Action
 *
 * @property Auth $oAuth
 * @property Viewer $oViewer
 * @property Status $status
 * @property Config $oConfig
 * @property array $json
 * @author al
 */
class personage_Action extends JSONAction
{

    public function RegisterEvent()
    {
        $this->AddEvent('info.json', 'getPersonage', Auth::AUTH_USER);
        $this->AddEvent('create.json', 'createNewPersonage', Auth::AUTH_USER);
        $this->AddEvent('set_ban.json', 'setBanPersonage', Auth::AUTH_USER);
        $this->AddEvent('worlds_explored.json', 'actionGetInfoSelectExploredWorlds', Auth::AUTH_USER);
        $this->AddEvent('worlds_unexplored.json', 'actionGetInfoSelectUnexploredWorlds', Auth::AUTH_USER);
        $this->AddEvent('attributes.json', 'getAllAttributesNewPersonage', Auth::AUTH_USER);
        $this->AddEvent('update_name_city.json', 'setUpdateNameCity', Auth::AUTH_USER);
        $this->AddEvent('move.json', 'actionMovePersonage', Auth::AUTH_USER);
        $this->AddEvent('init.json', 'actionInitPersonage', Auth::AUTH_USER);
        $this->AddEvent('change_tax_rate.json', 'actionChangeTaxRate', Auth::AUTH_USER);
        $this->AddEvent('current_processes.json', 'actionAllCurrentProcesses', Auth::AUTH_USER);
        $this->AddEvent('get_list.json', 'getList', Auth::AUTH_USER);
        $this->AddEvent('last_position.json', 'actionLastPositionPersonage', Auth::AUTH_USER);
        $this->AddEvent('cancel_move.json', 'actionCancelMovementOfPersonage', Auth::AUTH_USER);
        $this->AddEvent('finish_move.json', 'actionFinishMovePersonage', Auth::AUTH_USER);
        $this->AddEvent('test.json', 'test');
        /*$this->AddEvent('default', 'edefault');
        $this->SetDefaultEvent('default');*/
    }

    public function test() {
        //print_r(personage_UnitLocation::model()->getPersonageUnits(16));
//		$result = personage_Location::model()->isCity(78, 0);
        //personage_Location::model()->removeLocationOwner(5, 1);
        //$result = unit_UnitsMoving::model()->getSquadById(58);
        //$result = personage_ResourceState::model()->getCityResourcesByCoordinates(78, 1);
        personage_ResourceState::model()->setCityResourceByCoordinates(78, 0, 4, 2680);
	//print_r($result);
    }
    
    /**
     * Действие позволяет инициализировать персонажа в системе и сохранять состояние в сессии.
     * @throws StatusErrorException
     * @throws Exception
     */
    public function actionInitPersonage()
    {
        try {
            $personage = $this->detectExistsAndGetPersonage();
            $location = adminworld_Cell::findCellByCoordinatesAndDetectOwnerLocation(
                $personage->x_l, $personage->y_l, $personage->world_id
            );

            if ($location == null)
                throw new StatusErrorException('Location is not found in world: ' . $personage->world_id);

            Auth::setIdPersonage($personage->id);
            Auth::setCurrentIdLocation($location->id_cell);
            Auth::setPatternCurrentLocation($location->map_pattern);
            Auth::setCurrentLocationCoordinates($location->x, $location->y);
            Auth::setCurrentWorldId($location->id_world);

            if ($personage->id == $location->id_personage)
                $personage->my_location = true;
            else
                $personage->my_location = false;

            // Получение списка своих городов
            $cities = personage_City::model()->findCitiesForPersonage($personage->id);
            if (empty($cities))
                throw new StatusErrorException('Cities for personage not found', $this->status->personage_not_exists);

            $personage->setTimeOnline(time());


            $this->Viewer_Assign('personage', $personage->properties);
            $this->Viewer_Assign('cities', $this->formatJSONResponse($cities));
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionAvailabilityDeterminationLocation validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionInitPersonage` error: ', $e->getMessage());
            if ($this->oConfig->system['debug'] === true)
                throw new Exception('Action `actionInitPersonage` error: ', $e->getMessage());
        }
    }

    /**
     *Набор исходных атрибутов персонажа для предоставления пользователю
     */
    public function getAllAttributesNewPersonage()
    {
        try {
            $allAttributes = personage_Mapper::model()->getAllAttributes();

            foreach ($allAttributes as $key => $value) {
                $this->Viewer_Assign($key, $this->formatJSONResponse($value));
            }
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `getAllAttributes` error: ', $e->getMessage());
        }
    }

    /**
     * Событие получения данных всех миров системы с учавствованием в них свои персонажей.
     * Информация о своих персонажах так же привязывается к миру, если пользователь имеет его.
     */
    public function actionGetInfoSelectExploredWorlds()
    {
        try {
            $worlds = user_Mapper::model()->findExploredWorldsUser(Auth::id());
            $this->Viewer_Assign('worlds', $this->formatJSONResponse($worlds));
            $this->Viewer_Assign('clock', time());
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `register` error: ', $e->getMessage());
        }
    }

    /**
     * Событие получения данных неисследованных миров системы.
     */
    public function actionGetInfoSelectUnexploredWorlds()
    {
        try {
            $worlds = user_Mapper::model()->findUnexploredWorldsUser(Auth::id());
            $this->Viewer_Assign('worlds', $this->formatJSONResponse($worlds));
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `register` error: ', $e->getMessage());
        }
    }

    /**
     * Действие является API клинтской части для создания персонажа.
     */
    public function createNewPersonage()
    {
        try {
            $idWorld = $this->GetVar('world_id');
            $idFraction = $this->GetVar('fraction_id');
            $idTypePersonage = $this->GetVar('type_personage_id');
            $idReligion = $this->GetVar('religion_id');
            $nick = $this->GetVar('nick');
            $city = $this->GetVar('city');

            Auth::setIdPersonage(NULL);
            if ($idWorld == NULL)
                throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);
            if ($idFraction == NULL)
                throw new StatusErrorException('Parameter `fraction_id` not defined', $this->status->main_errors);
            if ($idTypePersonage == NULL)
                throw new StatusErrorException('Parameter `type_personage_id` not defined', $this->status->main_errors);
            if ($idReligion == NULL)
                throw new StatusErrorException('Parameter `religion_id` not defined', $this->status->main_errors);
            if ($nick == NULL)
                throw new StatusErrorException('Parameter `nick` not defined', $this->status->main_errors);
            if ($city == NULL)
                throw new StatusErrorException('Parameter `city` not defined', $this->status->main_errors);

            //Проверяем не отсутствует в нике буквы
            if (!preg_match("/[a-zA-Zа-яА-Я]+/u", $nick)) {
                throw new StatusErrorException('Nick should contain letters', $this->status->main_errors);
            }

            //Проверяем не отсутствует в названии города буквы
            if (!preg_match("/[a-zA-Zа-яА-Я]+/u", $city)) {
                throw new StatusErrorException('City should contain letters', $this->status->main_errors);
            }

            $existsPersonageForRequiredWorldUser = personage_Mapper::model()->findPersonageForCurrentUserAndWorld($idWorld);
            if ($existsPersonageForRequiredWorldUser != NULL)
                throw new StatusErrorException('Personage exists for current user', $this->status->personage_exists);

            //Проверяем на наличие существующего имени (nick) персонажа в текущем мире
            $existsNamePersonage = personage_Mapper::model()->isExistsPersonage($nick, $idWorld);
            if ($existsNamePersonage == true)
                throw new StatusErrorException('Personage name exists for current world', $this->status->personage_exists);

            // Проверка размещения персонажа на карте
            $newPersonage = personage_Mapper::model()->createNewPersonage($idWorld, //ID - мира
                $idFraction, //ID - фракции
                $idTypePersonage, //ID - образа
                $idReligion, //ID - религии
                $nick, //Ник персонажа
                $city);
            //Город персонажа

            //Убеждаемся, что новый персонаж действительно создан успешно
            if ($newPersonage != false) {
                Auth::setIdPersonage($newPersonage);
                $this->Viewer_Assign('status', $this->status->successfully);
                if ($this->oConfig->system['debug'] === true) $this->Viewer_Assign('id', $newPersonage);
            } else {
                throw new StatusErrorException('Personage not create read log', $this->status->main_errors);
            }
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `createNewPersonage` error: ', $e->getMessage());
        }
    }

    /**
     * Получение персонажа в ответе по его идентификатору.
     *
     * @throws JSONResponseErrorException
     */
    public function getPersonage()
    {
        try {
            $idPersonage = $this->GetVar('id');
            if (!$idPersonage) throw new JSONResponseErrorException('ID not defined for request', $this->status->main_errors);

            $personage = personage_Mapper::model()->findPersonageById($idPersonage);
            if (!$personage) throw new JSONResponseErrorException('Personage not found', $this->status->personage_not_exists);

            $this->Viewer_Assign('personage', $personage);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `register` error: ', $e->getMessage());
        }
    }

    /**
     * Действие определяет персонажа пользователя в бан.
     *
     * @throws JSONResponseErrorException
     */
    public function setBanPersonage()
    {
        try {
            $idPersonage = $this->GetVar('id');
            if (!$idPersonage) throw new JSONResponseErrorException('ID not defined in request', $this->status->main_errors);

            $timeBan = $this->GetVar('time');
            if ($timeBan) throw new JSONResponseErrorException('TIME not defined in request', $this->status->main_errors);

            $personage = personage_Mapper::model()->setBanned($idPersonage, $timeBan);
            if (!$personage) throw new JSONResponseErrorException('Personage not found', $this->status->main_errors);

            $this->Viewer_Assign(array('personage' => $personage));
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `register` error: ', $e->getMessage());
        }
    }

    /**
     * Обновляем название города
     *
     * Изменить название возможно лишь с помощью артефакта «Городская ведомость».
     * Кол-во изменений названий не лимитировано и зависит лишь от наличия артефакта.
     *
     * @throws StatusErrorException
     */
    public function setUpdateNameCity()
    {
        try {

            $currentNameCity = $this->GetVar('current_name_city');
            $newNameCity = $this->GetVar('new_name_city');

            if ($currentNameCity == NULL)
                throw new StatusErrorException('Parameter `current_name_city` not defined', $this->status->main_errors);

            if ($newNameCity == NULL)
                throw new StatusErrorException('Parameter `new_name_city` not defined', $this->status->main_errors);

            //TODO:Необходима еще логика и запрос на артефакт «Городская ведомость»

            $done = personage_City::model()->updateNameCity($currentNameCity, $newNameCity);

            if ($done === false) {
                throw new StatusErrorException('City exists', $this->status->user_exists);
            } else {
                $this->Viewer_Assign('status', $this->status->successfully);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action getPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `register` error: ', $e->getMessage());
        }
    }

    /**
     * Событие перемещение персонажа на другую локацию.
     */
    public function actionMovePersonage()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $x = $this->GetVar('x');
            $y = $this->GetVar('y');
            if ($x == NULL || $y == NULL)
                throw new StatusErrorException('Parameter X or Y not defined', $this->status->main_errors);

            $personage = personage_parameters_Move::model()->findStatePersonageById($idPersonage);
            if ($personage == NULL)
                throw new StatusErrorException('Personage for system not found', $this->status->personage_not_exists);

            $location = adminworld_Cell::findCellByCoordinatesAndDetectOwnerLocation($x, $y, Auth::getCurrentWorldId());

            //Проверяем является ли локация городом
            if ($location->city_name == NULL) {
                throw new StatusErrorException('Location not city', $this->status->main_errors);
            }

            //Проверяем окончено ли предыдущее передвижение персонажа
            if ($personage->status_move_personage == personage_parameters_Move::STATUS_MOVE_PERSONAGE_TRANSIT) {
                throw new StatusErrorException('Personages movement is not finished', $this->status->main_errors);
            }

			// Проверяем, не находится ли персонаж уже в данной локации
			if ($personage->x_l == $x && $personage->y_l == $y)
			{
				throw new StatusErrorException('Personage already in this city', $this->status->main_errors);
			}

            $timestamp = models_Time::model()->getTimestampProlongedByMinute(personage_parameters_Move::TIME_MOVE_PERSONAGE);
            $dateFinishMovePersonage = models_Time::model()->convertTimestampToDateAndTime($timestamp);

            $changeLocation = $personage->setLastPosition($y, $x, $dateFinishMovePersonage,
                personage_parameters_Move::STATUS_MOVE_PERSONAGE_TRANSIT);

            if (!$changeLocation->isError()) {

                /*
                Auth::setCurrentLocationCoordinates($x, $y);
                Auth::setCurrentIdLocation($location->id_cell);
                Auth::setPatternCurrentLocation($location->map_pattern);
               */

                if ($idPersonage == $location->id_personage)
                    $location->my_city = true;
                else
                    $location->my_city = false;

                //fb($_SESSION, 'move personage session', FirePHP::ERROR);
                $this->Viewer_Assign('status', $this->status->successfully);
                $this->Viewer_Assign('location', $this->formatJSONResponse(array($location)));
            } else
                $this->Viewer_Assign('Bad move personage', $this->status->main_errors);
        } catch (StatusErrorException $e) {
            $e->sendResponse($this, 'Action move personage validate: ');
        }
    }

    /**
     * Действие оканчивает передвижение персонажа при перемещении если окончено время передвижения в город
     */
    public function actionFinishMovePersonage()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $statePersonageMove = personage_parameters_Move::model()->findCoordinatesPersonages(
                personage_parameters_Move::STATUS_MOVE_PERSONAGE_TRANSIT);

            $doneFinishMovePersonage = personage_parameters_Move::model()->finishMovePersonage($statePersonageMove,
                personage_parameters_Move::STATUS_MOVE_PERSONAGE_ARRIVAL, $idPersonage
            );

            if ($doneFinishMovePersonage === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionFinishMovePersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionFinishMovePersonage` error: ', $e->getMessage());
        }
    }

    /**
     * Действие для получения данных о последнем положении персонажа при перемещении
     */
    public function actionLastPositionPersonage()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $formedCityLastPosition = array();
            $myCity = false;
            $personageCity = true;

            $personageState = personage_State::model()->findStatePersonageById($idPersonage);
            if ($personageState == NULL) {
                throw new StatusErrorException('Not data personage state of coordinates', $this->status->personage_not_exists);
            }

            $cityAndCurrentPersonageState = personage_City::model()->findCityByCoordinatesWithOwner($personageState->x_l, $personageState->y_l);
            if ($cityAndCurrentPersonageState == NULL) {
                throw new StatusErrorException('Not data city last position personage', $this->status->personage_not_exists);
            }

            //Определяем мой это город
            if ($idPersonage == $cityAndCurrentPersonageState->personage_id) {
                $myCity = true;
            }

            $formedCityLastPosition['my_city'] = $myCity;
            $formedCityLastPosition['personage_city'] = $personageCity;
            $formedCityLastPosition['city_name'] = $cityAndCurrentPersonageState->city_name;
            $formedCityLastPosition['total_level'] = $cityAndCurrentPersonageState->total_level;
            $formedCityLastPosition['nick'] = $cityAndCurrentPersonageState->nick;
            $formedCityLastPosition['x_l'] = $personageState->x_l;
            $formedCityLastPosition['y_l'] = $personageState->y_l;
            $formedCityLastPosition['x_c'] = $personageState->x_c;
            $formedCityLastPosition['y_c'] = $personageState->y_c;
            $formedCityLastPosition['time'] = strtotime($personageState->finishing_move_personage);
            $formedCityLastPosition['process'] = strtotime($personageState->finishing_move_personage) - time();
            $formedCityLastPosition['status_move_personage'] = $personageState->status_move_personage;

            $this->Viewer_Assign('last_position', $formedCityLastPosition);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionLastPositionPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionLastPositionPersonage` error: ', $e->getMessage());
        }
    }

    /**
     * Действие отменяет перемещение персонажа
     * В случае отмены пользователь остается в локации из которой собирался уйти.
     *
     * @throws StatusErrorException
     */
    public function actionCancelMovementOfPersonage()
    {
        try {
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $personageState = personage_parameters_Move::model()->findStatePersonageById($idPersonage);
            $unixSecondsMovePersonage = strtotime($personageState->finishing_move_personage);
            $unixCurrentDateTime = time();

            //Проверяем завершил персонаж свое передвижение в город
            if ($personageState->status_move_personage == personage_parameters_Move::STATUS_MOVE_PERSONAGE_ARRIVAL AND
                $unixCurrentDateTime >= $unixSecondsMovePersonage
            ) {
                throw new StatusErrorException('Personages movement completed', $this->status->main_errors);
            }

            $doneStatusMovePersonage = personage_parameters_Move::model()->updateCoordinatesStatusMovePersonage(
                personage_parameters_Move::STATUS_MOVE_PERSONAGE_ARRIVAL, 
				$personageState->x_l,
				$personageState->y_l,
				$idPersonage
			);

            if ($doneStatusMovePersonage === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionCancelMovementOfPersonage validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionCancelMovementOfPersonage` error: ', $e->getMessage());
        }
    }

    /**
     * API представляет пользователю возможность изменить налоговою ставку
     *
     * @throws StatusErrorException
     */
    public function actionChangeTaxRate()
    {
        try {

            $idPersonage = Auth::getIdPersonage();

            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $taxRate = $this->GetVar('tax_rate');
            if ($taxRate == NULL)
                throw new StatusErrorException('Parameter `tax_rate` not defined', $this->status->main_errors);

            $doneValidationTaxRate = personage_parameters_Tax::model()->validationValueTaxRate($taxRate);

            if ($doneValidationTaxRate === false) {
                throw new StatusErrorException('Incorrect tax rate', $this->status->main_errors);
            }

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();
            $city = personage_City::model()->findCityByCoordinates($coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y']);

            $doneTimeChangesTaxRate = personage_parameters_Tax::model()->youCanDetermineTaxRateChange($city->last_changes_tax_rates);

            //Обновляем налоговою ставку
            if ($doneTimeChangesTaxRate === true) {
                $doneUpgradeTaxRate = personage_parameters_Tax::model()->updateTaxCity($taxRate, $city->id, false);
            } else {
                throw new StatusErrorException('Not over the recent change in tax rate', $this->status->main_errors);
            }

            if ($doneUpgradeTaxRate === true) {
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                $this->Viewer_Assign('status', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionChangeTaxRate validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionChangeTaxRate` error: ', $e->getMessage());
        }
    }


    /**
     * API предоставляет все текущие процессы игры для персонажа
     * - постройка/улучшение здания
     * - внутренние улучшения здания
     * - исследования
     * @throws StatusErrorException
     */
    public function actionAllCurrentProcesses()
    {
        try {

            $idPersonage = Auth::getIdPersonage();

            if ($idPersonage == NULL)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

            $coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();

            $formattedDataBuilding = array();
            $formattedDataImproveBuilding = array();
            $formattedDataResearch = array();
            $formattedDataBuildingConstruction = array();
            $formattedDataSquads = array();

            /* var $time models_Time*/
            $time = models_Time::model();

            $buildings = personage_Building::model()->findBuildingPersonageOnStatus($idPersonage, $coordinatesCurrentLocation['x'],
                $coordinatesCurrentLocation['y'],
                personage_Building::STATUS_CONSTRUCTION_PROCESSING);

            //Формируем данные для здания, во время формировки строящиеся ресурсные здания выделяем в отдельный массив
            if ($buildings != NULL) {
                $i = 0;
                foreach ($buildings as $building) {
                    $formattedDataBuilding[$i]['id_building_personage'] = $building->id_building_personage;
                    $formattedDataBuilding[$i]['name_building'] = $building->name;
                    $formattedDataBuilding[$i]['process'] = $time->calculateRemainingTimeInSeconds($building->unix_finish_time_construction);
                    $formattedDataBuilding[$i]['time'] = $building->unix_finish_time_construction;

                    //Проверяем если уровень ноль и классификация здания ресурное, создаем массив для строящегося здания
                    if ($building->current_level == personage_Building::NO_LEVEL_BUILDING AND
                        $building->classifier == building_Mapper::RESOURCE_CLASSIFIER
                    ) {
                        $formattedDataBuildingConstruction[0] = $formattedDataBuilding[$i];
                        unset($formattedDataBuilding[$i]);
                    }

                    $i++;
                }
            } else {
                $formattedDataBuilding = personage_Building::NO_VALUE;
            }

            if (empty($formattedDataBuildingConstruction)) {
                $formattedDataBuildingConstruction = personage_Building::NO_VALUE;
            }

            $improveBuilding = personage_Improve::model()->findImproveForBuildingPersonageOnStatus($idPersonage,
                personage_Improve::STATUS_PROCESS);

            //Формируем данные для внутреннего улучшения здания
            if ($improveBuilding != NULL) {
                $i = 0;
                foreach ($improveBuilding as $improve) {
                    $formattedDataImproveBuilding[$i]['id_building_improve'] = $improve->improve_id;
                    $formattedDataImproveBuilding[$i]['id_building_personage'] = $improve->id_building_personage;
                    $formattedDataImproveBuilding[$i]['name_building'] = $improve->name;
                    $formattedDataImproveBuilding[$i]['name_improve'] = $improve->name_upgrade;
                    $formattedDataImproveBuilding[$i]['process'] = $time->calculateRemainingTimeInSeconds($improve->unix_finish_time_improve);
                    $formattedDataImproveBuilding[$i]['time'] = $improve->unix_finish_time_improve;
                    $i++;
                }
            } else {
                $formattedDataImproveBuilding = personage_Building::NO_VALUE;
            }

            $AllResearch = personage_ResearchState::model()->findResearchOnStatus($idPersonage, personage_ResearchState::STATUS_RESEARCH);

            //Формируем данные для исследований
            if ($AllResearch != NULL) {
                $i = 0;
                foreach ($AllResearch as $research) {
                    $formattedDataResearch[$i]['id_personages_research_state'] = $research->id_personages_research_state;
                    $formattedDataResearch[$i]['name_research'] = $research->name_research;
                    $formattedDataResearch[$i]['next_level'] = $research->current_level + 1;
                    $formattedDataResearch[$i]['process'] = $time->calculateRemainingTimeInSeconds($research->research_finish_time);
                    $formattedDataResearch[$i]['time'] = $research->research_finish_time;
                    $i++;
                }
            } else {
                $formattedDataResearch = personage_Building::NO_VALUE;
            }

            $squads = unit_UnitsMoving::model()->getSquads(Auth::getIdPersonage());
            if (!empty($squads))
            {
                foreach ($squads as $squad)
                {
					$process = $squad['end_time'] - time();
					if ($squad['cancel_time'] != null) {
						$process = ($squad['cancel_time'] - $squad['start_time']) - (time() - $squad['cancel_time']);
					}

                    $formattedDataSquads[] = array(
                        'squad_id' => $squad['id'],
                        'start_time' => $squad['start_time'],
                        'end_time' => $squad['end_time'],
                        'cancel_time' => $squad['cancel_time'],
						'process' => $process,
						'target' => $squad['target']
                    );
                }
            }
            

            $this->Viewer_Assign('improving_building', $formattedDataBuilding);
            $this->Viewer_Assign('construction_building', $formattedDataBuildingConstruction);
            $this->Viewer_Assign('internal_improvements', $formattedDataImproveBuilding);
            $this->Viewer_Assign('research', $formattedDataResearch);
            $this->Viewer_Assign('squads', $formattedDataSquads);
        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionAllCurrentProcesses validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionAllCurrentProcesses` error: ', $e->getMessage());
        }
    }

    /**
     * API представляет пользователю список всех персонажей
     *
     * @throws StatusErrorException
     */
    public function getList()
    {
        try {
            $personageList = personage_Mapper::model()->findAllPersonages(array("id", "nick"));

            $this->Viewer_Assign('personages_list', $this->formatJSONResponse($personageList));

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionAllCurrentProcesses validate: ');
        }
        catch (Exception $e) {
            e1('Action `actionAllCurrentProcesses` error: ', $e->getMessage());
        }
    }
}

