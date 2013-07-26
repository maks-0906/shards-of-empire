<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';

require_once ROOT . '/Application/pattern/Mapper.php';

require_once ROOT . '/Application/personage/Mapper.php';
require_once ROOT . '/Application/personage/Fraction.php';
require_once ROOT . '/Application/personage/Religion.php';
require_once ROOT . '/Application/personage/Type.php';
require_once ROOT . '/Application/personage/State.php';
require_once ROOT . '/Application/personage/Building.php';
require_once ROOT . '/Application/personage/City.php';
require_once ROOT . '/Application/personage/ResourceState.php';
require_once ROOT . '/Application/personage/ResearchState.php';
require_once ROOT . '/Application/personage/Improve.php';
require_once ROOT . '/Application/personage/BuildingBonus.php';

require_once ROOT . '/Application/resource/Mapper.php';
require_once ROOT . '/Application/epidemic/Mapper.php';

require_once ROOT . '/Application/research/Mapper.php';
require_once ROOT . '/Application/research/ResearchUpgrade.php';
require_once ROOT . '/Application/research/Costs.php';

require_once ROOT . '/Application/building/Upgrade.php';
require_once ROOT . '/Application/building/BasicLevel.php';
require_once ROOT . '/Application/building/Mapper.php';
require_once ROOT . '/Application/building/Development.php';
require_once ROOT . '/Application/building/LevelUpgrade.php';

require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/adminworld/Cell.php';
require_once ROOT . '/Application/adminworld/Comb.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class BuildingTest extends CDbTestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->initDataSession();
		parent::setUp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 *
	 */
	public function testFillingTableDataBaseLevelAndBonus()
	{
		try
		{
			$E = array(
				// Замок (castle)
				'1' => array(
					building_BasicLevel::BONUS_PROTECTION =>
						array('basic' => 5, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_PERCENT),
					building_BasicLevel::BONUS_HAPPINESS =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::SYMPATHY =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::BONUS_HEALTH =>
						array('basic' => 5, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT)
				),
				// Крепость (fortress)
				'2' => array(
					building_BasicLevel::BONUS_PROTECTION=>
						array('basic' => 5, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_PERCENT),
					building_BasicLevel::ATTACK =>
						array('basic' => 5, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_PERCENT),
					building_BasicLevel::SIZE_GARRISON =>
						array('basic' => 100, 'improve' => 20, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Кузница (smithy)
				'3' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 100, 'improve' => 5, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Каменоломня (quarry)
				'4' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 100, 'improve' => 5, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Лесопилка (swmill)
				'5' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 100, 'improve' => 5, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Ткацкая мастерская
				'6' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 100, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Пивоварня
				'7' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 10, 'improve' => 5, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Ферма
				'8' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 100, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Пасека
				'9' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 20, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Винодельня
				'10' => array(
					building_BasicLevel::COUNT_PRODUCTS =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::PRODUCTION_TIME =>
						array('basic' => 60, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Коллегия бардов
				'11' => array(
					building_BasicLevel::BONUS_HAPPINESS  =>
						array('basic' => 1, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Рынок
				'12' => array(
					building_BasicLevel::BUY =>
						array('basic' => 1, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_PERCENT),
					building_BasicLevel::SALE =>
						array('basic' => 1, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_PERCENT),
				),
				// Турннирное поле
				'13' => array(
					building_BasicLevel::BONUS_HAPPINESS =>
						array('basic' => 1, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::FAME_COUNT =>
						array('basic' => 20, 'improve' => 20, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Дом лекаря
				'14' => array(
					building_BasicLevel::BONUS_HEALTH =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::COUNT_DEATHS =>
						array('basic' => -1, 'improve' => -1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Таверна
				'15' => array(
					building_BasicLevel::BONUS_HAPPINESS =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::CRIME =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::NUMBER_BARRELS_BEER =>
						array('basic' => 1, 'improve' => 0, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Библиотека
				'16' => array(
					building_BasicLevel::SPEED_LEARNING_TECHNOLOGIES =>
						array('basic' => 13, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Священная роща
				'17' => array(
					building_BasicLevel::BLESSING_COUNT =>
						array('basic' => 2, 'improve' => 'calculationBlessing', 'measure' => building_BasicLevel::MEASURE_FORMULA),
					building_BasicLevel::CANDLES_COUNT =>
						array('basic' => 1, 'improve' => 0, 'measure' => building_BasicLevel::MEASURE_UNIT),
                    building_BasicLevel::BONUS_FAITH =>
                        array('basic' => 0, 'improve' => 0, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Дом
				'18' => array(
					building_BasicLevel::BONUS_HEALTH =>
						array('basic' => 5, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::BONUS_CAPACITY=>
						array('basic' => 100, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::POPULATION_GROWTH =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_UNIT),
				),
				// Склад
				'19' => array(
					building_BasicLevel::BONUS_CAPACITY =>
						array('basic' => 1000, 'improve' => 2000, 'measure' => building_BasicLevel::MEASURE_UNIT),
					building_BasicLevel::BONUS_PROTECTION =>
						array('basic' => 10, 'improve' => 1, 'measure' => building_BasicLevel::MEASURE_PERCENT),
				),
                // Казарма
                '20' => array(
                    building_BasicLevel::BONUS_CONSTRUCTION_UNIT =>
                    array('basic' => 0, 'improve' => -5, 'measure' => building_BasicLevel::MEASURE_UNIT),
                ),
                // Конюшня
                '21' => array(
                    building_BasicLevel::BONUS_CONSTRUCTION_UNIT =>
                    array('basic' => 0, 'improve' => -5, 'measure' => building_BasicLevel::MEASURE_UNIT),
                ),
			);

			foreach($E as $idBuilding => $bonus)
			{
				$bl = new building_BasicLevel();
				$bl->building_id = intval($idBuilding);
				$bl->data_bonus = serialize($bonus);

				$bl->save();
			}


		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 *
	 */
	public function testFindBuildingWithUnitsAndImprove()
	{
	    try
	    {
			$city = personage_City::model()->findCityByCoordinates($_SESSION['location']['x'], $_SESSION['location']['y']);
			$this->assertInstanceOf(
				'Mapper', $city,
				'Город должен быть найден по заданным в координатам и быть моделью с интерфейсом `Mapper`'
			);
			$pk = $city->pk();
			$this->assertEquals(2, $city->$pk, 'Идентификатор найденного города должен быть равен `2`');

			$building = building_Mapper::model()->findBuildingWithImproveAndUnits(
				1, $_SESSION['current_id_personage'], $city->$pk
			);
			$this->assertInternalType(
				'array', $building,
				'Записи должны быть найдены и упакованы в мсассив'
			);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Тест на поиск здания с выборкой для него ресурсов и его данных о обновлении.
	 */
	public function testFindBuildingWithResourceAndUpgrade()
	{
	    try
	    {
			$coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();
			$building = building_Mapper::model()->findBuildingWithResourceAndUpgrade(
				1, Auth::getIdPersonage(), $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y']
			);
			$this->assertInternalType(
				'array', $building,
				'Записи должны быть найдены и упакованы в мсассив'
			);
			$this->assertGreaterThan(0, count($building), 'Массив должен содержать записи с данными');

			$formattedResponse = array('resources' => array());
			$data = $building[0];

			$formattedResponse['name_building'] = $data->name_building;
			$formattedResponse['name_level_building'] = $data->name_level_building;
			$formattedResponse['max_access_level'] = $data->max_access_level;
			$formattedResponse['current_level_building'] = $data->current_level_building;
			$formattedResponse['status_upgrade'] = $data->status_upgrade;
			$formattedResponse['base_bonus'] = building_BasicLevel::unpackingBonuses($data->base_bonus);

			foreach($building as $resource)
			{
				$nameResource = $resource->name_resource;
				if($resource->$nameResource != null)
					$formattedResponse['resources'][$nameResource] =
						array('required' => $resource->$nameResource, 'has' => $resource->has_resource);
			}

			$this->assertInternalType(
				'array', $formattedResponse['base_bonus'],
				'Базовые бонусы для здания должны быть найдены и распакованы как массвив'
			);
			$this->assertNotEmpty($formattedResponse['base_bonus'], 'Массив с бонусом должен быть полным');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testFindImproveBuildingAtCurrentLevelForPersonageAndCity()
	{
	    try
	    {
			$coordinatesCurrentLocation = Auth::getCurrentLocationCoordinates();
			$improve = building_Upgrade::model()->findImproveBuildingAtCurrentLevelForPersonageAndCity(
				1, Auth::getIdPersonage(), $coordinatesCurrentLocation['x'], $coordinatesCurrentLocation['y'], 1
			);
			$this->assertInternalType(
				'array', $improve,
				'Записи должны быть найдены и упакованы в мсассив'
			);
			$this->assertGreaterThan(0, count($improve), 'Массив должен содержать записи с данными');

			$formattedResponse = array('improve'=>array());
			foreach($improve as $i)
			{
				$currentImprove = array();
				$currentImprove['name_improve'] = $i->name_improve;
				$currentImprove['time_improve'] = $i->time_improve;
				$currentImprove['finish_time_improve'] = $i->finish_time_improve;
				$currentImprove['status_improve'] = $i->status_improve;
				$currentImprove['required_level_building'] = $i->required_level_building;
				$currentImprove['current_level_building'] = $i->current_level_building;

				array_push($formattedResponse['improve'], $currentImprove);
			}

			$this->assertGreaterThan(
				0, count($formattedResponse),
				'Массив с ответами для клиента должен содержать записи с данными'
			);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Инициализация тестовых данных в сессию.
	 */
	private function initDataSession()
	{
		$_SESSION['current_id_user'] = 1;
		$_SESSION['current_id_personage']  = 1;
		$_SESSION['location']['id']  = 209;
		$_SESSION['location']['pattern']  = 9;
		$_SESSION['location']['x']  = 28;
		$_SESSION['location']['y']  = 0;
	}

	/**
	 * Тестирование поиска зданий с уже законечнной постройкой или улучшений, но находящихся на сталии процесса.
	 */
	public function testFindFinishTimeImprove()
	{
		try
		{
			$this->initDataSession();
			$buildings = personage_Building::model()->findBuildingsWithFinishCreateAndImprove();
			$this->assertInternalType(
				$buildings,
				'Должен быть получен массив со зданиями, которые требуют завершения постройки или улучшения.'
			);

			$this->assertGreaterThan(0, count($buildings), "Массив должен иметь хотя бы одно здания что бы тестировать");
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 *
	 */
	public function testFillingBuildingsForPersonage()
	{
	    try
	    {
			$idCity = 1;
			$idPersonage = 1;

			for($i = 1; $i < 10; $i++)
	        	$buildings = personage_Building::model()->fillingBuildingsForPersonage($i, $i);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}