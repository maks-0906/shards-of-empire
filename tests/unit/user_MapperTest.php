<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';
require_once ROOT . '/Application/user/Mapper.php';
require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/personage/Mapper.php';
require_once ROOT . '/Application/personage/State.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class user_MapperTest extends CDbTestCase {

	/**
	 * Фикстуры
	 *
	 * @var array
	 */
	protected $fixtures = array(
		'users' => 'user_Mapper',
		'personages' => 'personage_Mapper',
		'personages_state' => 'personage_State',
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
	 * Тестирование возможности определять существование пользователя в системе.
	 */
	public function testUserExists()
	{
	    try
	    {
			$user = new user_Mapper();
			$password = md5('123'); // 202cb962ac59075b964b07152d234b70
			$result = $user->createNewUser('user1@t.ru', md5('123'), $_SERVER['REMOTE_ADDR']);
			$this->assertTrue($user->isExists(), 'Пользователь должен быть в системе!');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Поиск пользователей по IP регистрации.
	 */
	public function testFindUserByIP()
	{
	    try
	    {
			/* @var $users array */
			$users = user_Mapper::model()->findUsersByIP('127.0.0.100');
			$this->assertTrue(count($users) > 0, 'Пользователь c таким IP должен быть в системе!');

			foreach($users as $key => $user)
				$concreteUser = $user;

			$users = user_Mapper::model()->findUsersByIP('127.0.0.10');
			$this->assertTrue(count($users) === 0, 'Пользователь c таким IP в системе не должен быть!');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testFindUserByEmail()
	{
	    try
	    {
			$user = user_Mapper::model()->findUserByEmail('user1@t.ru');
			$this->assertEquals(1, $user->id, 'Пользователь c таким email должен быть в системе!');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Тест на получение информации о исследованных и неисследованных мирах.
	 */
	public function testObtainingInformationExploredWorlds()
	{
	    try
	    {
			$worlds = user_Mapper::model()->findExploredWorldsUser(14);
			$this->assertInternalType('array', $worlds, 'Возращаемый параметр должен быть массивом');
			$this->assertCount(1, $worlds, 'Должен быть найден один исследованный мир');
			// Название может меняться так как фикстуры для миров нет, слишком нагружено будет на тесты
			$this->assertEquals(
				'Test world 1', $worlds[0]->name_world,
				'Мир должен иметь название Test world 1'
			);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testObtainingInformationUnexploredWorlds()
	{
	    try
	    {
			$worlds = user_Mapper::model()->findUnexploredWorldsUser(14);
			$this->assertInternalType('array', $worlds, 'Возращаемый параметр должен быть массивом');
			$this->assertCount(1, $worlds, 'Должен быть найден один исследованный мир');
			// Название может меняться так как фикстуры для миров нет, слишком нагружено будет на тесты
			$this->assertEquals(
				'Test world 2', $worlds[0]->name_world,
				'Мир должен иметь название Test world 2'
			);
			$this->assertEquals(
				'en', $worlds[0]->lang,
				'Параметр языка мира должен быть аббревиатурой `en`'
			);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}