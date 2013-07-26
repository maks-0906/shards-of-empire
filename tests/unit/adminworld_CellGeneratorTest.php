<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';

require_once ROOT . '/Application/map/LevelLocation.php';
require_once ROOT . '/Application/pattern/Mapper.php';
require_once ROOT . '/Application/personage/Fraction.php';
require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/adminworld/Comb.php';
require_once ROOT . '/Application/adminworld/Cell.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class adminworld_CellGeneratorTest extends CDbTestCase {

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
	 * Генерация для требуемого шаблона карты.
	 */
	public function testGenerationCellsForRequireTemplateMap()
	{
	    try
	    {
			$mapTemplate = adminworld_Cell::model()->generatingCellsMap(0, 1000, 1000);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}