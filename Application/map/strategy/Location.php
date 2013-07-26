<?php
/**
 * Файл содержит класс стратегию реализующий бизнес логику в области определения информации по локациям,
 * а так же бизнес логику, касающейся локаций карты.
 *
 * @author Greg
 * @package map_strategy
 */

/**
 * Класс стратегия реализующий бизнес логику в области определения информации по локациям,
 * а так же бизнес логику, касающейся локаций карты.
 *
 * @author Greg
 * @version 1.0.0
 * @package map_strategy
 */
class map_strategy_Location {

	/**
	 * Бизнес логика получения локаций персонажа где размещены боевые юниты.
	 * Так же учитываются локации, владельцами которых являются союзные персонажи.
	 *
	 * @return array
	 */
	public static function getLocationsWithCombatUnitsPersonages()
	{
		// Получение городов персонажа с юнитами
		$citiesWithUnits = personage_City::model()->findCitiesPersonageWithUnits(Auth::getIdPersonage());
		//fb($citiesWithUnits, 'citiesWithUnits', FirePHP::ERROR);

		// Получение локаций с юнитами для персонажа
		$locationsWithUnits = personage_Location::model()->findLocationsWithCombatUnitsPersonage(Auth::getIdPersonage());
		//fb($locationsWithUnits, 'locationsWithUnits', FirePHP::ERROR);

		$locations = array();
		foreach($citiesWithUnits as $city)
			array_push($locations, $city->properties);

		// TODO: Когда будет тестовые таблицы заполнены проверить наверное нужно будет форматирования по локациям
		foreach($locationsWithUnits as $location)
			array_push($locations, $location->proprties);

		return $locations;
	}

	/**
	 * Поиск и формирование ответа информации по городу с владельцем города.
	 *
	 * @param adminworld_Cell $cell
	 * @return array
	 */
	public static function getInfoLocationCityPersonage(adminworld_Cell $cell)
	{
		$city = personage_City::model()->findCityByCoordinatesWithOwner($cell->x, $cell->y);

		$responseCity = array();
		$responseCity['sympathy'] = personage_Mapper::model()->detectSympathyBetweenPersonages(0, 0);
		$responseCity['level'] = personage_City::model()->detectLevelCity($city);
		$responseCity['total_level'] = $city->total_level;
		$responseCity['city_name'] = $city->city_name;
		$responseCity['my_city'] = ($cell->personage_id_city == Auth::getIdPersonage());
		$responseCity['nick'] = $city->nick;
		// TODO: требуется название союза
		$responseCity['guild'] = $city->guild_id;

		return $responseCity;
	}

	/**
	 * Поиск и формирование ответа информации по локации карты с владельцем этой локации.
	 *
	 * @param adminworld_Cell $cell
	 * @return personage_Location
	 */
	public static function getInfoLocationPersonage(adminworld_Cell $cell)
	{
		$location = personage_Location::model()->findLocationByCoordinatesWithOwner($cell->x, $cell->y);

		$responseLocation = array();
		$responseLocation['sympathy'] = personage_Mapper::model()->detectSympathyBetweenPersonages(0, 0);
		$responseLocation['level'] = $cell->id_level_cell;
		$responseCity['my_location'] = ($cell->personage_id_location == Auth::getIdPersonage());
		$responseLocation['total_level'] = $location->total_level;
		$responseLocation['nick'] = $location->nick;
		$responseLocation['production_bonus'] = $cell->production_bonus;
		$responseLocation['time_of_bonus'] = $cell->time_of_bonus;
		// TODO: требуется название союза
		$responseLocation['guild'] = $location->guild_id;

		return $responseLocation;
	}

	/**
	 * Поиск и формирование ответа информации по локации карты с разбойниками для локации.
	 * Количество и типы разбойников зависят от уровня локации.
	 *
	 * @param adminworld_Cell $cell
	 * @return personage_Location
	 */
	public static function getInfoLocationForRobbers(adminworld_Cell $cell)
	{
		$responseLocation = array();
		$responseLocation['level'] = $cell->id_level_cell;
		$responseLocation['production_bonus'] = $cell->production_bonus;
		$responseLocation['time_of_bonus'] = $cell->time_of_bonus;

		$responseLocation['army'] = map_FeatureRobber::model()->detectLevelArmyRobbers($cell->id_level_cell);

		return $responseLocation;
	}

	/**
	 * Получение отфарматированного ответа из запроса от БД информации о боевых юнитах для локации-города.
	 *
	 * @param adminworld_Cell $cell
	 * @return array
	 */
	public static function getUnitsInLocationCity(adminworld_Cell $cell)
	{
		$units = personage_City::model()->findCombatUnitsForCity($cell->x, $cell->y, $cell->personage_id_city);
		fb($units, 'getUnitsInLocationCity units: ', FirePHP::ERROR);
		$responseUnits = array();
		foreach($units as $unit)
			array_push($responseUnits, $unit->properties);

		return $responseUnits;
	}

	/**
	 * Получение отфарматированного ответа из запроса от БД информации о боевых юнитах для обычной локации.
	 * Локация может быть как своя так и союзная.
	 *
	 * @param adminworld_Cell $cell
	 * @return array
	 */
	public static function getUnitsInLocation(adminworld_Cell $cell)
	{
		$units = personage_Location::model()->findCombatUnitsForLocation($cell->x, $cell->y, Auth::getIdPersonage());
		fb($units, 'getUnitsInLocation units: ', FirePHP::ERROR);
		$responseUnits = array();
		foreach($units as $unit)
			array_push($responseUnits, $unit->properties);

		return $responseUnits;
	}
}