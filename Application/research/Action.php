<?php
/**
 * Файл содержит API по запросам получения данных для элементов игры "Глобальные исследования"
 *
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
class research_Action extends JSONAction
{
    public function RegisterEvent()
    {
        $this->AddEvent('index.json', 'index', Auth::AUTH_USER);
        $this->AddEvent('current.json', 'getAllCurrentResearch', Auth::AUTH_USER);
        $this->AddEvent('properties.json', 'getPropertiesResearch', Auth::AUTH_USER);
        $this->AddEvent('immediately.json', 'studyResearchImmediately', Auth::AUTH_USER);
        $this->AddEvent('slow.json', 'slowLearningResearch', Auth::AUTH_USER);
        $this->AddEvent('finish_learn.json', 'actionFinishStudyResearch', Auth::AUTH_USER);
        $this->AddEvent('cancel_learn.json', 'actionCancelStudyResearch', Auth::AUTH_USER);
    }

    /**
     * Предоставляем начальные данные по глобальным исследованиям
     * Так же функция предоставляется по умолчанию
     * @throws StatusErrorException
     */
    public function index()
    {
        try {
            $this->detectExistsCurrentPersonage();
            $researchModel = research_Mapper::model();

            $research = $researchModel->findAllResearch();
            if (!empty($research)) {
                $this->Viewer_Assign('list_research', $this->formatJSONResponse($research));
            } else {
                throw new StatusErrorException('No data on initial research', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `getInitialResearch` validate: ');
        }
        catch (Exception $e) {
            e1('Action `getInitialResearch` error: ', $e->getMessage());
        }
    }

    /**
     * Предоставляем текущие данные глобальных исследований пользователя
     *
     * @throws StatusErrorException
     */
    public function getAllCurrentResearch()
    {
        try {
            $this->detectExistsCurrentPersonage();
            $research = research_Mapper::model()->findAllCurrentResearch();

            if (!empty($research)) {
                $this->Viewer_Assign('current', $this->formatJSONResponse($research));
            } else {
                throw new StatusErrorException('No data on current research', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `getAllCurrentResearch` validate: ');
        }
        catch (Exception $e) {
            e1('Action `getAllCurrentResearch` error: ', $e->getMessage());
        }
    }

    /**
     * Действие изучение исследования мгновенно
     *
     * @throws StatusErrorException
     */
    public function studyResearchImmediately()
    {
        try {
            $personage = $this->detectExistsCurrentPersonage();
            $idResearch = $this->GetVar('research_id');

            if ($idResearch == NULL)
                throw new StatusErrorException('Parameter `research_id` not defined', $this->status->main_errors);

            $coordinates = Auth::getCurrentLocationCoordinates();

            //Проверяем не идет внутреннее улучшение здания БИБЛИОТЕКА в текущем городе
            $this->checkStatusOfLibraryBuilding($personage->id, $coordinates['x'], $coordinates['у']);

            $access_next_level = $this->determineAvailabilityLibraryBuilding($idResearch);

            if ($access_next_level !== true) {
                throw new StatusErrorException('Insufficient level of the library building', $this->status->insufficient_level_library_building);
            }

            $idLibrary = personage_Building::ID_BUILDING_LIBRARY;

            //Получаем значения ресурсов города
            $access_resource_city = personage_ResourceState::model()->findResourcesCityForResearch(
                $personage->id, $idResearch, $idLibrary
            );

            $sufficientResources = $this->sufficientResourcesCityToResearch($access_resource_city);
            if ($sufficientResources === false || count($access_resource_city) == 0)
                throw new StatusErrorException('No subtract made resources', $this->status->no_resources);

            $amber = null;
            foreach ($access_resource_city as $resource) {
                if ($resource->resource_id == resource_Mapper::AMBER_ID) {
                    $amber = $resource;
                    break;
                }
            }

            if ($amber == null)
                throw new StatusErrorException('Resource gold not found for learn slow', $this->status->main_errors);

            $doneSubtractResources =
                personage_ResourceState::model()->updateAmountResourcePersonageInCurrentYourCity(
                    $amber->price, $amber->resource_id
                );

            if ($doneSubtractResources !== true) {
                throw new ErrorException('Bad update amount resource', $this->status->main_errors);
            }

            //Подымаем уровень исследования на шаг выше
            $updateLevelPersonage = personage_ResearchState::model()->updateCurrentLevelPersonage($idResearch);
            if ($updateLevelPersonage === true) {
                e1('Action `studyResearchImmediately` начало быстрого изучения: research_id: '
                    . $idResearch . ' - id_personage: ' . Auth::getIdPersonage());
                $this->Viewer_Assign('status', $this->status->successfully);
            } else {
                throw new StatusErrorException('Study did not examine', $this->status->main_errors);
            }


        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action studyResearchImmediately validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `studyResearchImmediately` error: ', $e->getMessage());
        }
    }

    /**
     * Действие медленное изучение исследования
     *
     * @throws StatusErrorException
     */
    public function slowLearningResearch()
    {
        try {
            $personage = $this->detectExistsCurrentPersonage();
            $idResearch = $this->GetVar('research_id');

            if ($idResearch == NULL)
                throw new StatusErrorException('Parameter `research_id` not defined', $this->status->main_errors);

            $coordinates = Auth::getCurrentLocationCoordinates();

            //Проверяем не идет внутреннее улучшение здания БИБЛИОТЕКА в текущем городе
            $this->checkStatusOfLibraryBuilding($personage->id, $coordinates['x'], $coordinates['у']);

            // Определение доступности изучения исследования от уровня библиотеки
            $access_next_level = $this->determineAvailabilityLibraryBuilding($idResearch);

            if ($access_next_level !== true) {
                throw new StatusErrorException('Insufficient level of the library building', $this->status->insufficient_level_library_building);
            }

            //Определяем относится ли персонаж к фракции "ФРАНКИ"
            $isFractionsFranks = personage_Fraction::model()->isFractionsFranks($personage->id);

            $idLibrary = personage_Building::ID_BUILDING_LIBRARY;

            // Получаем значения ресурсов города
            $access_resource_city = personage_ResourceState::model()->findResourcesCityForResearch(
                $personage->id, $idResearch, $idLibrary
            );

            $sufficientResources = $this->sufficientResourcesCityToResearch($access_resource_city);
            if ($sufficientResources === false || count($access_resource_city) == 0)
                throw new StatusErrorException('No subtract made resources', $this->status->no_resources);


            $gold = null;
            foreach ($access_resource_city as $resource) {
                if ($resource->resource_id == resource_Mapper::GOLD_ID) {
                    $gold = $resource;
                    break;
                }
            }

            if ($gold == null)
                throw new StatusErrorException('Resource gold not found for learn slow', $this->status->main_errors);

            $doneSubtractResources =
                personage_ResourceState::model()->updateAmountResourcePersonageInCurrentYourCity(
                    $gold->price, $gold->resource_id
                );

            if ($doneSubtractResources !== true) {
                throw new ErrorException('Bad update amount resource', $this->status->main_errors);
            }

            //Получаем время для исследования
            $minute = research_Costs::model()->findTimeResearchCosts($idResearch, resource_Mapper::GOLD_ID, $personage->id);

            //$timestamp = personage_ResearchState::model()->getTimestampEndStudy($minute->time_research);
            if ($minute == NULL) {
                throw new ErrorException('No time research', $this->status->main_errors);
            }

            $secondsTimeResearch = models_Time::model()->getCountNumberOfSecondsInMinute($minute->time_research);

            //Если персонаж принадлежит фракции "ФРАНКИ" уменьшаем время изучения на 5%
            if ($isFractionsFranks === true) {
                $secondsTimeResearch = ceil($secondsTimeResearch - ($secondsTimeResearch / 100) * personage_Fraction::BONUS_FRACTIONS);
            }

            //Получаем бонусы здания "БИБЛИОТЕКА" текущего города
            $bonusBuildingLibrary = personage_BuildingBonus::model()->findCurrentBonusesForSpecificBuildingOnCoordinatesCity(building_Mapper::KEY_BUILDING_LIBRARY,
                $coordinates['x'], $coordinates['у'], $personage->id);

            $currentBonusBuildingLibrary = unserialize($bonusBuildingLibrary->current_data_bonus);
            $bonusStudyTechnology = $currentBonusBuildingLibrary["study_technology"]["basic"];

            if (!empty($currentBonusBuildingLibrary)) {
                $secondsTimeResearch = $secondsTimeResearch - $bonusStudyTechnology;
            }

            //Проводим валидацию времени на наличие отрицательного числа
            if ($secondsTimeResearch > personage_ResearchState::NO_VALUE) {
                $timestamp = time() + $secondsTimeResearch;
            } else {
                $timestamp = time();
            }

            // Добавлям метку окончания изучения исследования
            $updateLevelPersonage = personage_ResearchState::model()->updateSlowResearch($idResearch, $timestamp);

            $currentTimestamp = personage_ResearchState::model()->getCurrentTimestamp();
            $finish_time = personage_ResearchState::model()->getRestTimestamp($timestamp, $currentTimestamp);

            if ($updateLevelPersonage === true) {
                e1('Action `slowLearningResearch` начало медленного изучения: research_id: '
                    . $idResearch . ' - id_personage: ' . Auth::getIdPersonage());
                $this->Viewer_Assign('status', $this->status->successfully);
                $this->Viewer_Assign('finish_time', $finish_time);
            } else {
                throw new StatusErrorException('Slow learning not examine', $this->status->main_errors);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action slowLearningResearch validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `slowLearningResearch` error: ', $e->getMessage());
        }
    }

    /**
     * Действие реагирует на событие завершения изучения исследования,
     * при этом проверяет на существование персонажа для пользователя в текущем мире.
     *
     * @throws E1Exception
     * @throws StatusErrorException
     */
    public function actionFinishStudyResearch()
    {
        try {
            $personage = $this->detectExistsCurrentPersonage();
            $idResearch = $this->GetVar('research_id');

            if ($idResearch == NULL)
                throw new StatusErrorException('Parameter `research_id` not defined', $this->status->main_errors);

            /* @var $research personage_ResearchState */
            $research = personage_ResearchState::model()->findResearchWithFinishTime($idResearch);
            if (count($research) == 0)
                throw new StatusErrorException('Research for finish not found', $this->status->main_errors);

            $isLearn = $research->updateCurrentLevelPersonage($research->current_research_id);
            if ($isLearn == false)
                throw new E1Exception("Not finished research with ID: " . $idResearch);
            else {
                e1('Action `actionFinishStudyResearch` Завершилось исследование с `current_research_id`: ' . $research->current_research_id);
                $this->Viewer_Assign('status', $this->status->successfully);
            }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionFinishStudyResearch validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionFinishStudyResearch` error: ', $e->getMessage());
        }
    }

    /**
     * Действие отменяет текущее изучени исследования.
     * Производится проверка на существование персонажа в текущем мире, т.е. что бы пользователь имел персонажа в мире.
     *
     * @throws E1Exception
     * @throws StatusErrorException
     */
    public function actionCancelStudyResearch()
    {
        try {
            $personage = $this->detectExistsCurrentPersonage();
            $idResearch = $this->GetVar('research_id');

            if ($idResearch == NULL)
                throw new StatusErrorException('Parameter `research_id` not defined', $this->status->main_errors);

            $isCancel = personage_ResearchState::model()->cancelResearchById($idResearch);
            if ($isCancel == false)
                throw new E1Exception("Not cancel research for ID: " . $idResearch);
            else
                $this->Viewer_Assign('status', $this->status->successfully);

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action actionCancelStudyResearch validate: ');
        }
        catch (E1Exception $e) {
            e1('Action `actionCancelStudyResearch` error: ', $e->getMessage());
        }
    }

    /**
     * Действие является API для заполнения данными правой стороны модального окна
     *
     * @throws StatusErrorException
     */
    public function getPropertiesResearch()
    {
        try {

            $personage = $this->detectExistsCurrentPersonage();
            $idResearch = $this->GetVar('research_id');

            if ($idResearch == NULL)
                throw new StatusErrorException('Parameter `research_id` not defined', $this->status->main_errors);

            $idLibrary = personage_Building::ID_BUILDING_LIBRARY;
            $coordinates = Auth::getCurrentLocationCoordinates();
            $researchExamined = false;

            $maxLevelBuildingLibrary = building_LevelUpgrade::model()->findMaxLevelBuilding(building_Mapper::KEY_BUILDING_LIBRARY);
            $personageResearch = personage_ResearchState::model()->findResearchByIDResearchForPersonage($idResearch, $personage->id);

            //Проверяем не окончено ли изучение всех уровней
            if ($personageResearch->current_level >= $maxLevelBuildingLibrary->max_level_building) {
                // throw new StatusErrorException('The research is examine', $this->status->main_errors);
                $researchExamined = true;
            }

            $properties = research_Mapper::model()->findPropertiesNextLevel($idResearch, $coordinates['x'],
                $coordinates['y']);
            $formedData = $this->generateDataForResearch($properties);

            //Получаем значения ресурсов города
            $resourceCity = personage_ResourceState::model()->findResourcesCityForResearch(
                $personage->id, $idResearch, $idLibrary
            );


            //Определяем если пройдены все уровни исследования, то передаем клиенту флаг об окончании для вывода сообщения,
            //об окончании исследований, иначе выводим правую сторону модального окна продолжения исследования
            if ($researchExamined === true) {
                $this->Viewer_Assign('properties', array('research_examined'=> $this->status->research_examined));
            } else
                if (!empty($properties) AND !empty($resourceCity)) {

                    //Проверяем наличие временной метки течения исследования
                    if ($properties[0]->research_finish_time != 0) {
                        $researchState = personage_ResearchState::model();
                        $current_timestamp = $researchState->getCurrentTimestamp();
                        $finish_time = $researchState->getRestTimestamp(
                            (int)$properties[0]->research_finish_time, $current_timestamp
                        );
                        $properties[0]->finish_time = $this->controlDifferenceFinishTime($finish_time, $idResearch);
                    }

                    //Передаем клиентской части статус (исследовано) в случае отсутствия временной метки окончания исследования
                    if ($properties[0]->finish_time == false) {
                        $properties[0]->research_status = personage_ResearchState::STATUS_INVESTIGATED;
                    }

                    $this->Viewer_Assign('resource', $this->formatJSONResponse($resourceCity));
                    $this->Viewer_Assign('properties', $formedData);
                } else {
                    throw new StatusErrorException('Not properties personage research', $this->status->main_errors);
                }

        } catch (JSONResponseErrorException $e) {
            $e->sendResponse($this, 'Action `getPropertiesResearch` validate: ');
        }
        catch (Exception $e) {
            e1('Action `getPropertiesResearch` error: ', $e->getMessage());
        }

    }

    /**
     * Определение существования персонажа для текущего пользователя и требуемого мира.
     *
     * @throws StatusErrorException
     * @return personage_Mapper
     */
    private function detectExistsCurrentPersonage()
    {
        $idPersonage = Auth::getIdPersonage();
        fb($_SESSION, 'sess', FirePHP::ERROR);
        if ($idPersonage == NULL)
            throw new StatusErrorException('Parameter `id_personage` not defined', $this->status->main_errors);

        $currentPersonage = personage_Mapper::model()->findPersonageById($idPersonage);
        //if($this->oConfig->system['debug'] == true) fb($currentPersonage, 'p', FirePHP::ERROR);
        if ($currentPersonage == NULL)
            throw new StatusErrorException(
                'For current user personage not found in require world ',
                $this->status->user_not_found
            );

        return $currentPersonage;
    }

    /**
     * Действие напрвлено на контроль наличие времени исследования.
     *
     * Метод проверяет наличие временной метки у текущего исследования, в случает существования данной метки передает ее
     * клиентскому скрипту, а в случае ее отсутствия происходит изменение статуса на (исследовано) и
     * повышение текущего уровня.
     *
     * @param $finish_time
     * @param $idResearch
     * @return mixed
     * @throws StatusErrorException
     */
    public function controlDifferenceFinishTime($finish_time, $idResearch)
    {
        if ($finish_time === false) {
            $result = personage_ResearchState::model()->updateCurrentLevelPersonage($idResearch);
        }

        if (!empty($result->errors)) {
            throw new StatusErrorException('Study did not examine', $this->status->main_errors);
        } else {
            return $finish_time;
        }
    }

    /**
     * Опеределяем возможность проведения исследования связанное с уровнем библиотеки
     *
     * @param int $idResearch
     * @return bool
     * @throws StatusErrorException
     */
    protected function determineAvailabilityLibraryBuilding($idResearch)
    {
        $idCastle = personage_Building::ID_BUILDING_CASTLE;
        $idLibrary = personage_Building::ID_BUILDING_LIBRARY;

        //Проверяем существует ли уже процесс исследования
        $currentResearch = personage_ResearchState::model()->findResearchWithFinishTime();
        if (count($currentResearch) > 0)
            throw new StatusErrorException('There is a research study', $this->status->there_is_study);

        $research = personage_ResearchState::model()->findResearchByIDResearchForPersonage($idResearch);
        if ($research == null)
            throw new StatusErrorException('Research not found', $this->status->main_errors);

        $level = personage_Building::model()->findLevelsBuildings($idCastle, $idLibrary);

        if ($level[1]->current_level != '' AND $level[0]->current_level != '') {
            return personage_Building::model()->toCompareLevelsBuildings(
                $level[1]->current_level, $research->current_level + 1
            );
        } else {
            return false;
        }
    }


    /**
     * Определяем достаточно ли ресурсов в городе для исследования
     *
     * @param $accessResourceCity
     * @return bool
     */
    public function sufficientResourcesCityToResearch($accessResourceCity)
    {
        $done = true;
        foreach ($accessResourceCity as $resourceCity) {
            if ($resourceCity->personage_resource_value <= $resourceCity->resource_value) {
                $done = false;
                break;
            }
        }

        return $done;
    }

    /**
     * Формируем данные для отправки клиентской части (массив - properties)
     *
     * @param $input
     * @return array
     */
    public function generateDataForResearch($input)
    {
        $name_upgrade = "";
        foreach ($input as $res) {
            $name_upgrade .= $res->name_upgrade . ',';
        }

        //Удаляем последнюю запятую в части запроса
        $upgrade = substr($name_upgrade, 0, -1);
        $upgrade = array_unique(preg_split('~\s*,\s*~', $upgrade), SORT_STRING);

        // :WARNING: Время изучения даётся в минутах, приводим всё время к секундам
        $timeResearch = ($input[0]->research_status == personage_ResearchState::STATUS_RESEARCH)
            ? $input[0]->research_finish_time - time() : models_Time::model()->getCountNumberOfSecondsInMinute($input[0]->time_research);

        $response = array(
            'name_upgrade' => implode(',', $upgrade),
            'name_research' => $input[0]->name_research,
            'name_building' => building_Mapper::KEY_BUILDING_LIBRARY,
            'time_research' => $timeResearch,
            'research_status' => $input[0]->research_status,
            'level_upgrade' => $input[0]->level_upgrade,
            'current_level' => $input[0]->current_level,
            'finish_time' => $input[0]->research_finish_time
        );
        return $response;
    }


    /**
     * Определяем не идет внутреннее улучшение здания библиотека
     * Во время внутреннего улучшения невозможно изучение глобальной технологии
     * @param $idPersonage
     * @param $x
     * @param $y
     * @throws StatusErrorException
     */
    public function checkStatusOfLibraryBuilding($idPersonage, $x, $y)
    {
        $buildingLibrary = personage_Improve::model()->findImproveBuildingPersonageOfNameBuilding(building_Mapper::KEY_BUILDING_LIBRARY,
            $x, $y, $idPersonage, personage_Improve::STATUS_PROCESS);

        //Определяем не идет внутреннее улучшение зднания
        if ($buildingLibrary != NULL) {
            throw new StatusErrorException('Not over home improvement building library', $this->status->not_over_home_improvement_building_library);
        }
    }
}
