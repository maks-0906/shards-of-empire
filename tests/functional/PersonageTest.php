<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once ROOT . '/Application/personage/Mapper.php';
require_once ROOT . '/Application/personage/Fraction.php';
require_once ROOT . '/Application/personage/Religion.php';
require_once ROOT . '/Application/personage/Type.php';
require_once ROOT . '/Application/personage/State.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class PersonageTest extends WebTestCase {

	/**
	 * Фикстуры
	 *
	 * @var array
	 */
	protected $fixtures = array(
		'personages' => 'personage_Mapper',
		'personages_state' => 'personage_State',
		'create_personages_religion' => 'personage_Religion',
		'create_personages_type' => 'personage_Type'
	);

	/**
	 *
	 */
	public function testBadCreateNewPersonage()
	{
	    try
	    {

	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}
