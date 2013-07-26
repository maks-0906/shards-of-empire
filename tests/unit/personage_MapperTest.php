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
require_once ROOT . '/Application/personage/BuildingStrategy.php';

require_once ROOT . '/Application/resource/Mapper.php';
require_once ROOT . '/Application/epidemic/Mapper.php';

require_once ROOT . '/Application/research/Mapper.php';
require_once ROOT . '/Application/research/ResearchUpgrade.php';
require_once ROOT . '/Application/research/Costs.php';

require_once ROOT . '/Application/building/Upgrade.php';
require_once ROOT . '/Application/building/BasicLevel.php';
require_once ROOT . '/Application/building/Mapper.php';
require_once ROOT . '/Application/building/Development.php';

require_once ROOT . '/Application/adminworld/Mapper.php';
require_once ROOT . '/Application/adminworld/Cell.php';
require_once ROOT . '/Application/adminworld/Comb.php';

/**
 * Description class
 *
 * @author Greg
 * @version 1.0.0
 * @package tests
 */
class personage_MapperTest extends CDbTestCase {

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
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		// Идентификатор пользователя по умолчанию для тестов
		Auth::getInstance()->SetSessionVar('current_id_user', 1);
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
	 * Создание нового персонажа, нормальное создание.
	 * Переработано на транзакцию.
	 */
	public function testCreateNewPersonage()
	{
	    try
	    {
			$idWorld = 0;
			$idFraction = 1;
			$idTypePersonage = 2;
			$idReligion = 2;
			$nameCity = 'My city';
			$testNick = 'Holder';

			$idNewPersonage  = personage_Mapper::model()->createNewPersonage(
				$idWorld, $idFraction, $idTypePersonage, $idReligion, $testNick, $nameCity
			);
			$isNewPersonage = personage_Mapper::model()->isExistsPersonage($testNick, $idWorld);
			$this->assertTrue($isNewPersonage, 'Новый персонаж должен быть создан и существовать');

			$personage = personage_Mapper::model()->findPersonageById($idNewPersonage);
			$this->assertInstanceOf(
				'personage_Mapper', $personage,
				'Найденный персонаж по вновь созданному идентифкатору записи должен существовать'
			);

	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Получение текущих координат для персонажа.
	 */
	public function testFindLastPositionPersonage()
	{
	    try
	    {
	        $lastPosition = personage_Mapper::model()->getLastPosition();
			$this->assertEmpty(
				$lastPosition,
				'Если персонаж не найден и не проинициализирован должен быть возвращён пустой массив'
			);

			$idWorld = 0;
			$personage = personage_Mapper::model()->findPersonageForCurrentUserAndWorld($idWorld);
			$this->assertNotNull($personage, 'Персонаж должен быть найден и не равен null');
			$this->assertEquals(1, $personage->x_l, 'Координата по X должна быть равна 1');
			$this->assertEquals(1, $personage->y_l, 'Координата по Y должна быть равна 1');
		}
		catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Поиск персонажа по идентификатору.
	 */
	public function testFindPersonageById()
	{
	    try
	    {
			$personage = personage_Mapper::model()->findPersonageById(1);
			$this->assertNotNull($personage, 'Персонаж должен быть найден и не равен NULL');
			$this->assertEquals('Personage 1', $personage->nick, 'Персонаж должен иметь имя Personage 1');
			$this->assertEquals(1, $personage->x_l, 'Координата по X должна быть равна 1');
			$this->assertEquals(1, $personage->y_l, 'Координата по Y должна быть равна 1');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Поиск персонажа для текущего пользователя (по умолчанию прнимается пользователь с идентификатором 1)
	 * и по идентификатору мира.
	 */
	public function testFindPersonageForCurrentUserAndWorld()
	{
	    try
	    {
			$idWorld = 0;
			$personage = personage_Mapper::model()->findPersonageForCurrentUserAndWorld($idWorld);
			$this->assertNotNull($personage, 'Персонаж должен быть найден и не равен NULL');
			$this->assertEquals('Personage 7', $personage->nick, 'Персонаж должен иметь имя Personage 7');
			$this->assertEquals(1, $personage->x_l, 'Координата по X должна быть равна 1');
			$this->assertEquals(1, $personage->y_l, 'Координата по Y должна быть равна 1');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testOnlinePersonagesForRequiredWorld()
	{
	    try
	    {
			$idWorld = 1;
			$idFraction = 0;
			$idTypePersonage = 2;
			$idReligion = 2;
			$nameCity = 'Test city';
			$testNick = 'Test nick new personage';

			$idNewPersonage  = personage_Mapper::model()->createNewPersonage(
				$idWorld, $idFraction, $idTypePersonage, $idReligion, $testNick, $nameCity
			);

			sleep(3);
			$personages = personage_Mapper::model()->findOnlinePersonagesForRequiredWorld($idWorld, 30);
			$this->assertEquals(
				1, count($personages),
				'Должен соответствовать условиям онлайн только вновь добавленный пользователь'
			);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

    /**
     * Тест на выборку атрибутов для создания нового персонажа
     */
    public function testGetAttributesPersonage()
    {
        $result = personage_Mapper::model()->getAllAttributes();

        $this->assertNotNull($result['types'][0]->id, 'Образ персонажа не должен быть пустым!!!');
        $this->assertNotNull($result['fractions'][0]->id, 'Фракции не должны быть пустыми!!!');
        $this->assertNotNull($result['religions'][0]->id, 'Таблица религии должна быть заполнена!!!');
    }
}