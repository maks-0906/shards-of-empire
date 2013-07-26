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
class ImproveTest extends CDbTestCase{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
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
	 *
	 */
	public function testFindFinishTimeImprove()
	{
	    try
	    {
			$this->initDataSession();
			$improves = personage_Improve::model()->findFinishImproveBuildings();
			$this->assertInternalType(
				$improves,
				'Должен быть получен массив с зданиями, которые требуют завершения постройки или улучшения.'
			);

	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}