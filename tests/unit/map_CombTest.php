<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';

require_once ROOT . '/Application/map/LevelLocation.php';
require_once ROOT . '/Application/map/FeatureRobber.php';
require_once ROOT . '/Application/map/Action.php';

require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/pattern/Mapper.php';

require_once ROOT . '/Application/personage/Mapper.php';
require_once ROOT . '/Application/personage/State.php';
require_once ROOT . '/Application/personage/Fraction.php';
require_once ROOT . '/Application/personage/Religion.php';
require_once ROOT . '/Application/personage/Type.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class map_CombTest extends CDbTestCase {

	/**
	 * Фикстуры
	 *
	 * @var array
	 */
	protected $fixtures = array(
		'personages' => 'personage_Mapper',
		'map_group_cells' => 'map_Comb',
		'personages_state' => 'personage_State',
		'map_feature_levels_locations' => 'map_LevelLocation',
		'map_feature_groups_robbers' => 'map_FeatureRobber'
	);

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
	 *
	 */
	public function testInit()
	{
	    try
	    {
			$_REQUEST['world_id'] = 1;
			$_REQUEST['y_cnt'] = 10;
			$_REQUEST['x_cnt'] = 10;

			Auth::getInstance()->SetSessionVar('current_id_user', 1);
			$controller = new map_Action();
			$controller->actionInitMap();
			$r = '';
			$this->assertEquals('', $r);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}