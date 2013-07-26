<?php

require_once ROOT . '/Application/chat/Mapper.php';
require_once ROOT . '/Application/user/Mapper.php';
require_once ROOT . '/Application/personage/Mapper.php';

/**
 *
 */
class ChatTest extends WebTestCase
{


	/**
		 * Фикстуры
		 *
		 * @var array
		 */
	protected $fixtures = array(
		'chat_messages' => 'chat_Mapper',
		'personages' => 'personage_Mapper'
	);

	/**
	 * @var int
	 */
	private $world_id = 0;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->world_id = 0;
		parent::setUp();
	}

	/**
	 * Тестирование нормального сценария инициализации чата.
	 */
	public function testNormalUseCaseInitChat()
	{
		try
		{

			$this->login();
			$this->open('chat/chat.json?world_id=' . $this->world_id);
			$jsonResponse = $this->getResponse();
			$this->assertEquals(
				$jsonResponse->status, $this->status->successfully,
				'Статус нормальной инициализации чата должен быть 1'
			);
			$this->assertEquals(
				3, count($jsonResponse->messages),
				'Должно быть получено ' . count($jsonResponse->messages) . ' сообщения для текущего пользователя и текущего мира'
			);
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 * Тестирование отправки сообщения всем персонажам в общей комнате.
	 */
	public function testSendMainMessageAllPersonages()
	{
	    try
	    {
			$this->login();
			$this->open('chat/chat.json?world_id=' . $this->world_id);
			$jsonResponse = $this->getResponse();
			$this->assertEquals(
				$jsonResponse->status, $this->status->successfully,
				'Статус нормальной инициализации чата должен быть 1'
			);

			$testText = 'TESTTEST';
			$this->open('chat/send.json?world_id='. $this->world_id . '&channel_id=' . chat_Mapper::MAIN_TYPE_MESSAGE . '&text=' . $testText);
			$this->open('chat/messages.json?world_id='. $this->world_id
				. '&channel_id=' . chat_Mapper::MAIN_TYPE_MESSAGE . '&chatMessageLastId=0');
			$jsonResponse = $this->getResponse();
			$this->assertEquals(
				4, count($jsonResponse->messages),
				'Должно быть получено ' . count($jsonResponse->messages) . ' сообщения для текущего пользователя и текущего мира'
			);

			$lastMessages = $jsonResponse->messages[0];
			$this->assertEquals(
				$testText, $lastMessages->text,
				'Последнее добавленное сообщение должно быть с текстом: ' . $testText
			);
			$this->assertEquals(
				chat_Mapper::MAIN_TYPE_MESSAGE, $lastMessages->channel_id,
				'Последнее добавленное сообщение должно быть комнаты (канала): ' . chat_Mapper::MAIN_TYPE_MESSAGE
			);
			$this->assertEquals(
				2, $lastMessages->sender_id,
				''
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
	public function testSendMainMessageToRecipientWithAllShow()
	{
	    try
	    {
			$this->login();

			$testText = 'TESTTEST';
			$this->open('chat/send.json?world_id='
				. $this->world_id . '&channel_id=' . chat_Mapper::MAIN_TYPE_MESSAGE . '&text=' . $testText . '&recipient_id');
			$this->open('chat/messages.json?world_id='. $this->world_id
				. '&channel_id=' . chat_Mapper::MAIN_TYPE_MESSAGE . '&chatMessageLastId=0');

			$jsonResponse = $this->getResponse();
			$this->assertEquals(
				4, count($jsonResponse->messages),
				'Должно быть получено ' . count($jsonResponse->messages) . ' сообщения для текущего пользователя и текущего мира'
			);

			$lastMessages = $jsonResponse->messages[0];
			$this->assertEquals(
				$testText, $lastMessages->text,
				'Последнее добавленное сообщение должно быть с текстом: ' . $testText
			);
			$this->assertEquals(
				chat_Mapper::MAIN_TYPE_MESSAGE, $lastMessages->channel_id,
				'Последнее добавленное сообщение должно быть комнаты (канала): ' . chat_Mapper::MAIN_TYPE_MESSAGE
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
	public function testSendMainMessageToRecipientPrivate()
	{
	    try
	    {

	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * @param string $email
	 * @param string $md5Password
	 */
	private function login($email = 'user1@t.ru', $md5Password = '61b125ee490ccc185032c23a52f1ce8d')
	{
		$this->open('user/login.json?login=' . $email . '&password=' . $md5Password);
		$jsonResponse = $this->getResponse();
		$this->assertEquals($jsonResponse->status, $this->status->successfully, 'Авторизация прошла успешно');
	}

	/*public function testLoginLogout()
	{
		$this->open('');
		// ensure the user is logged out
		if($this->isTextPresent('Logout'))
			$this->clickAndWait('link=Logout (demo)');

		// test login process, including validation
		$this->clickAndWait('link=Login');
		$this->assertElementPresent('name=LoginForm[username]');
		$this->type('name=LoginForm[username]','demo');
		$this->click("//input[@value='Login']");
		$this->waitForTextPresent('Password cannot be blank.');
		$this->type('name=LoginForm[password]','demo');
		$this->clickAndWait("//input[@value='Login']");
		$this->assertTextNotPresent('Password cannot be blank.');
		$this->assertTextPresent('Logout');

		// test logout process
		$this->assertTextNotPresent('Login');
		$this->clickAndWait('link=Logout (demo)');
		$this->assertTextPresent('Login');
	}*/
}
