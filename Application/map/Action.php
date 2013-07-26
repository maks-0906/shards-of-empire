<?php
/**
 * Файл содержит контроллер, управляющий событиями от клиента в области карты.
 *
 * @author Greg
 * @package map
 */

/**
 * Контроллер, управляющий событиями от клиента в области карты и запросах о локациях,
 * и их информации: размещения войск, стутса локации ...
 *
 * @property Status $status
 * @author Greg
 * @version 1.0.0
 * @package map
 */
class map_Action extends JSONAction {

	/**
	 * Register request uri for action method.
	 */
	public function RegisterEvent()
	{
		$this->AddEvent('init.json', 'actionInitMap', Auth::AUTH_USER);
		$this->AddEvent('get.json', 'actionGetMap', Auth::AUTH_USER);
		$this->AddEvent('location_my_units.json', 'actionGetInfoLocationWhereCombatUnitsPersonage', Auth::AUTH_USER);
		$this->AddEvent('locations_with_units.json', 'actionGetLocationsWhereUnitsPersonage', Auth::AUTH_USER);
		$this->AddEvent('location.json', 'actionGetLocation', Auth::AUTH_USER);
		$this->AddEvent('my_locations.json', 'actionGetLocation', Auth::AUTH_USER);
		$this->AddEvent('detect_access.json', 'actionAvailabilityDeterminationLocation');
		$this->SetDefaultEvent('get.json');
	}

	/**
	 * Действие формирует ответ в JSON со всей доступной информацией по своей конкретной локации.
	 *
	 * @throws StatusErrorException
	 * @throws Exception
	 */
	public function actionGetInfoLocationWhereCombatUnitsPersonage()
	{
		try
		{
			$x = $this->GetVar('x');
			$y = $this->GetVar('y');
			if($x == null)
				throw new StatusErrorException('Parameter `x` not defined', $this->status->main_errors);

			if($y == null)
				throw new StatusErrorException('Parameter `y` not defined', $this->status->main_errors);

			$idWorld = $this->GetVar('world_id');
			if($idWorld === null)
				throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);

			$personage = $this->detectExistsPersonageForCurrentUser();

			/* @var $cell adminworld_Cell */
			// Получение информации о локации и владельце
			$cell = adminworld_Cell::model()->detectLocationAndHireOwner($x, $y, $idWorld);
			if($cell === null)
				throw new StatusErrorException('Location not found', $this->status->main_errors);

			if($cell->personage_id_city && $cell->personage_id_location)
				throw new StatusErrorException(
					'Location can not be right, and the city and the usual locations',
					$this->status->main_errors
				);

			$units = personage_UnitLocation::model()->getUnitsInLocation($x, $y, AUTH::getIdPersonage());
                        foreach($units as &$unit)
                        {
                            unset($unit['personage_id']);
                            unset($unit['id']);
                        }
                        
			// Если локация город, получаем данные о городе с текущими боевыми юнитами
//			if($cell->personage_id_city != null)
//			{
//				$units = map_strategy_Location::getUnitsInLocationCity($cell);
//			}
//			// иначе просматриваем есть ли такая локация в наличии у персонажа,
//			// и если есть получаем информацию по юнитам в локации
//			elseif($cell->personage_id_location != null)
//			{
//				$units = map_strategy_Location::getUnitsInLocation($cell);
//			}
			if (empty($units))
                        {
                            throw new StatusErrorException('Location does not have the personage of combat units',  $this->status->main_errors);
                        }

			$this->Viewer_Assign('units', $units);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `actionGetInfoLocationWhereCombatUnitsPersonage` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionGetInfoLocationWhereCombatUnitsPersonage` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionGetInfoLocationWhereCombatUnitsPersonage` error: ', $e->getMessage());
		}
	}

	/**
	 * Действие формирует ответ в JSON со всеми локациями с боевыми юнитами, принадлежащими персонажу,
	 * а так же на союзных территориях.
	 * Локации содержат в себе информацию о войсках, ...
	 *
	 * @throws StatusErrorException
	 * @throws Exception
	 */
	public function actionGetLocationsWhereUnitsPersonage()
	{
		try
		{
			$personage = $this->detectExistsPersonageForCurrentUser();

			// Получение списка всех локаций с боевыми юнитами персонажа вместе с городами
			// c учётом существования юнитов на локациях союзников.
			//$locations = map_strategy_Location::getLocationsWithCombatUnitsPersonages();
                        $locations = personage_UnitLocation::model()->getPersonageUnitsByLocations(AUTH::getIdPersonage());
			if(!is_array($locations)) throw new E1Exception('Variable `locations` must be type array');

			$this->Viewer_Assign('locations', $locations);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `actionGetLocationsWhereUnitsPersonage` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionGetLocationsWhereUnitsPersonage` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionGetLocationsWhereUnitsPersonage` error: ', $e->getMessage());
		}
	}

	/**
	 * Действие формирует ответ в JSON со всей доступной информацией по любой конкретной локации на карте.
	 *
	 * @throws StatusErrorException
	 * @throws Exception
	 */
	public function actionGetLocation()
	{
		try
		{
			$x = $this->GetVar('x');
			$y = $this->GetVar('y');
			if($x == null)
				throw new StatusErrorException('Parameter `x` not defined', $this->status->main_errors);

			if($y == null)
				throw new StatusErrorException('Parameter `y` not defined', $this->status->main_errors);

			$idWorld = $this->GetVar('world_id');
			if($idWorld === null)
				throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);

			// Блокируем, если персонажа нет в этом мире.
			$personage = $this->detectExistsPersonageForCurrentUser();

			/* @var $cell adminworld_Cell */
			// Получение информации о локации и владельце
			$cell = adminworld_Cell::model()->detectLocationAndHireOwner($x, $y, $idWorld);
			if($cell === null)
				throw new StatusErrorException('Location not found', $this->status->main_errors);

			if($cell->personage_id_city && $cell->personage_id_location)
				throw new StatusErrorException(
					'Location can not be right, and the city and the usual locations',
					$this->status->main_errors
				);

			$location = array();
			// Если локация город, получаем данные:
				// имя города, уровень города, титул персонажа, имя персонажа, союз: название
			if($cell->personage_id_city != null)
			{
				$location = map_strategy_Location::getInfoLocationCityPersonage($cell);
			}
			// иначе просматриваем есть ли такая локация в наличии у персонажа, получаем данные для локации:
				// паттерн локации, уровень локации, титул владельца, имя владельца, союз: название, бонус производства, время производства
			elseif($cell->personage_id_location != null)
			{
				$location = map_strategy_Location::getInfoLocationPersonage($cell);
			}
			// иначе вывод локации с населяющими разбойниками, получаем данные:
				// паттерн; уровень; войск: мало, средне, много; бонус производителя для локации, время действия
			else
			{
				$location = map_strategy_Location::getInfoLocationForRobbers($cell);
			}

			$this->Viewer_Assign('location', $location);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `actionGetLocation` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionGetLocation` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionGetLocation` error: ', $e->getMessage());
		}
	}

	/**
	 * Событие проверки доступности расположения персонажа на карте.
	 * @throws Exception
	 * @deprecated
	 */
	public function actionAvailabilityDeterminationLocation()
	{
		try
		{
			// Определяем загруженность мира на сервере
			$personage = $this->detectExistsPersonageForCurrentUser();
			if($personage instanceof personage_Mapper)
				throw new StatusErrorException('Personage is exists in world', $this->status->personage_exists);

			// Если персонажа нет проверяем доступность размещения на карте

		//	$this->Viewer_Assign('pattern_list', $pattern->formatJSONResponsePatterns($pattern_list));
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action actionAvailabilityDeterminationLocation validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionAvailabilityDeterminationLocation` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionAvailabilityDeterminationLocation` error: ', $e->getMessage());
		}
	}

	/**
	 * Событие инициализации карты при первом запросе от клиента.
	 * @throws Exception
	 */
	public function actionInitMap()
	{
		try
		{
			$pointPersonage = new stdClass();
			$personage = $this->detectExistsPersonageForCurrentUser();

			//$this->oAuth->SetSessionVar(SESSION_PERSONAGE_ID,  $personage->id);
			$lastPosition = $personage->getLastPosition();
			if(empty($lastPosition))
			{
				$pointPersonage->x = 0;
				$pointPersonage->y = 0;
			}
			else
			{
				$pointPersonage->y = $lastPosition[0];
				$pointPersonage->x = $lastPosition[1];
			}

			$this->Viewer_Assign('point', $pointPersonage);
			$this->Viewer_Assign(
				'pattern_list',
				pattern_Mapper::formatJSONResponsePatterns(pattern_Mapper::model()->findPatternList())
			);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action actionInitMap validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionInitMap` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionInitMap` error: ', $e->getMessage());
		}
	}

	/**
	 * Событие получение информации по карте в ячейках от клиента в процессе игры.
	 * @throws Exception
	 */
	public function actionGetMap()
	{
		try
		{
			$center_x = $this->GetVar('center_x');
			$center_y = $this->GetVar('center_y');

			if($center_x === null)
				throw new StatusErrorException('Parameter `center_x` not defined', $this->status->main_errors);
			if($center_y === null)
				throw new StatusErrorException('Parameter `center_y` not defined', $this->status->main_errors);
			// TODO: После тестирования расскоментировать !!!!
			//$this->detectExistsPersonageForCurrentUser();

			$this->responseJSONMap($center_x, $center_y);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action actionInitMap validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionInitMap` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionInitMap` error: ', $e->getMessage());
		}
	}

	/**
	 * @param $coordinatesVisibleMap
	 * @param $idWorld
	 * @return array
	 */
	private function initMap($coordinatesVisibleMap, $idWorld)
	{
		$cellsFromDB = array();
		$cellModel = adminworld_Cell::model();
		// TODO: Оптимизировать четыре запроса, возможно в один но с форматированием в PHP ответа??? требуется ли, взависимости от производительности
		foreach($coordinatesVisibleMap as $part)
			$cellsFromDB[] = $cellModel->findCellsOfCoordinatesMap($idWorld, array($part));
		// Полная выборка одним запросом всех ячеек но без распределения по частям
		//$cells = adminworld_Cell::model()->findCellsOfCoordinatesMap($idWorld, $coordinatesVisibleMap);

		$visibleMap = array();
		foreach($cellsFromDB as $mapPart)
		{
			foreach($mapPart as $cell)
			{
				$point = new stdClass();
				$point->p = $cell->map_pattern;
				$point->x = $cell->x;
				$point->y = $cell->y;
				$point->fraction = $cell->id_fraction;
				$point->level = $cell->id_level_cell;

				// Определение своей локации
				if($cell->id_location != null) $point->my_location = true;

				if($cell->id_city != null)
				{
					$point->my_city = true;
					$point->level = $cell->level_city;
				}
				$visibleMap[$cell->y][] = $point;
			}
		}

		return $visibleMap;
	}

	/**
	 * Получение информации и формирование ответа для клиента видимой области карты с заполненными информацией ячейками.
	 * @throws StatusErrorException
	 * @throws Exception
	 */
	private function responseJSONMap($center_x, $center_y)
	{
		try
		{
			$idWorld = $this->GetVar('world_id');
			$countYCells = $this->GetVar('y_cnt');
			$countXCells = $this->GetVar('x_cnt');

			if($idWorld === null) throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);
			if($countYCells === null) throw new StatusErrorException('Parameter `y_cnt` not defined', $this->status->main_errors);
			if($countXCells === null) throw new StatusErrorException('Parameter `x_cnt` not defined', $this->status->main_errors);

			$mapVisible = new map_VisibleMap($center_y, $center_x, $countYCells, $countXCells, 1000, 1000);
			$coordinatesVisibleMap = $mapVisible->detectDelimitationVisibleMap();

			$visibleMap = $this->initMap($coordinatesVisibleMap, $idWorld);

			$this->Viewer_Assign('map', $visibleMap);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action getInitMap validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `actionGetMatrixWithPattern` error: ', $e->getMessage());
			if($this->oConfig->system['debug'] === true)
				throw new Exception('Action `actionGetMatrixWithPattern` error: ', $e->getMessage());
		}
	}

	/**
	 * Определение существования персонажа для текущего пользователя и требуемого мира.
	 * :WARNING: Для всех действий требуется <b>обязательно</b> в запросе параметр идентификатора мира `world_id`.
	 *
	 * @throws StatusErrorException
	 * @return personage_Mapper
	 */
	private function detectExistsPersonageForCurrentUser()
	{
		$idWorld = $this->GetVar('world_id');
		if($idWorld === null)
			throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);

		$currentPersonage = personage_Mapper::model()->findPersonageForCurrentUserAndWorld($idWorld);
		if($currentPersonage == null)
			throw new StatusErrorException(
				'For current user personage not found in require world ',
				$this->status->personage_not_exists
			);

		return $currentPersonage;
	}
}
