<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';

require_once ROOT . '/Application/personage/Mapper.php';
require_once ROOT . '/Application/personage/Fraction.php';
require_once ROOT . '/Application/personage/Religion.php';
require_once ROOT . '/Application/personage/Type.php';
require_once ROOT . '/Application/personage/State.php';
require_once ROOT . '/Application/personage/City.php';
require_once ROOT . '/Application/personage/ResearchState.php';
require_once ROOT . '/Application/personage/ResourceState.php';

require_once ROOT . '/Application/building/Upgrade.php';
require_once ROOT . '/Application/resource/Mapper.php';
require_once ROOT . '/Application/epidemic/Mapper.php';

require_once ROOT . '/Application/research/Mapper.php';
require_once ROOT . '/Application/research/Costs.php';
require_once ROOT . '/Application/research/ResearchUpgrade.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class research_MapperTest extends CDbTestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		Auth::getInstance()->SetSessionVar(SESSION_PERSONAGE_ID, 1);
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
	 * Тест на выборку всех исследований с включением текущего уровня персонажа.
	 */
	public function testFindAllResearch()
	{
	    try
	    {
			$research = research_Mapper::model()->findAllResearch();
			$this->assertInternalType('array', $research, 'Должен быть получен массив');
			$this->assertCount(7, $research, 'Всего должно быть 7 типов исследований для персонажа');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testFindPropertiesResearch()
	{
	    try
	    {
			$idResearch = 1;
			$level = 1;
			$research = research_Mapper::model()->findPropertiesResearch($idResearch, $level);
			$this->assertInternalType('array', $research, 'Должен быть получен массив');
			$this->assertCount(4, $research, 'Всего должно быть 4 записи для исследования 1 и требуемого уровня 1');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}