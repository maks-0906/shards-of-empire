<?php
/**
 * Файл содержит тесты для работы с предметной областью картой.
 *
 * @author Greg
 * @package tests
 */

require_once 'PHPUnit/Autoload.php';

require_once ROOT . '/Application/map/LevelLocation.php';
require_once ROOT . '/Application/pattern/Mapper.php';
require_once ROOT . '/Application/personage/Fraction.php';
require_once ROOT . '/Application/personage/Mapper.php';
require_once ROOT . '/Application/personage/State.php';
require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/adminworld/Comb.php';
require_once ROOT . '/Application/adminworld/Cell.php';

/**
 * Класс тестов для работы с предметной областью карты, заключают в себе тестирование методов классов,
 * содержащих только бизнес логику.
 *
 * @author Greg
 * @version 1.0.0
 * @package tests
 */
class MapTest extends CDbTestCase {


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		// Идентификатор пользователя по умолчанию
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
	 * Тестирование определения загруженности карты персонажами.
	 */
	public function testDetectLoadedMap()
	{
	    try
	    {
			$idWorld = 0;
			$mapTemplate = adminworld_Mapper::model()->findMapForCalculationLoaded($idWorld);
			$lowLoaded = 1/3;
			$loaded = $mapTemplate->current_count_users / $mapTemplate->max_users;
			$this->assertLessThan($lowLoaded, $loaded, 'Загруженность карты в тестовых данных должна быть минимальная и меньше 1/3');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Тестирование поиска сот с типом фракций с наименьшим количеством персонажей в карте
	 */
	public function testFindCellRuinInCombsWithFewestNumberOfPersonages()
	{
	    try
	    {
			$idWorld = 0;
			$idFraction = 1;
			$comb = adminworld_Cell::model()->findCellRuinInCombsWithFewestNumberOfPersonages($idWorld);
			$this->assertInstanceOf(
				'adminworld_Cell', $comb,
				'Ячейка руина должна быть найдена и быть типом модели `adminworld_Cell`'
			);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}
