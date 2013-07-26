<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';

require_once ROOT . '/Application/map/PartVisibleMap.php';
require_once ROOT . '/Application/map/GeometryMap.php';
require_once ROOT . '/Application/map/SideMap.php';
require_once ROOT . '/Application/map/NormalVisibleMap.php';
require_once ROOT . '/Application/map/CornerVisibleMap.php';
require_once ROOT . '/Application/map/VisibleMap.php';

require_once ROOT . '/Application/map/LevelLocation.php';
require_once ROOT . '/Application/map/FeatureRobber.php';
require_once ROOT . '/Application/map/Action.php';

require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/adminworld/Comb.php';
require_once ROOT . '/Application/adminworld/Cell.php';
require_once ROOT . '/Application/adminworld/GeneratorMap.php';
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
class adminworld_GeneratorMapTest extends CDbTestCase {

	/**
	 * Фикстуры
	 *
	 * @var array
	 */
	protected $fixtures = array(
		'personages' => 'personage_Mapper',
		'personages_state' => 'personage_State',
		/*'map_feature_levels_locations' => 'map_LevelLocation',
		'map_feature_groups_robbers' => 'map_FeatureRobber'*/
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
	public function testGenerationMap()
	{
	    try
	    {
			Auth::getInstance()->SetSessionVar('current_id_user', 1);
			$result = adminworld_GeneratorMap::generatingCellsMap(0, 1000, 1000);
			$this->assertTrue($result, 'Карта должна быть сгенерирована и получен результат true');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}