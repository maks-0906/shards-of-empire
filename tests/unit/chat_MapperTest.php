<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

require_once 'PHPUnit/Autoload.php';
require_once ROOT . '/Application/user/Mapper.php';
require_once ROOT . '/Application/chat/Mapper.php';
require_once ROOT . '/Application/personage/Mapper.php';

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class chat_MapperTest extends CDbTestCase {

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
	 * Тестирование нормального добавления сообщения в чат.
	 */
	public function testNormalCreateMessage()
	{
	    try
	    {
			$newMessage = chat_Mapper::model()->createNewMessage(0, 0, 'Test message');
			$this->assertEquals(10, $newMessage->id, '');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testFindLastMessages()
	{
	    try
	    {
			// Установка идентификатора пользователя, который должен увидеть так же приватное сообщение
			Auth::getInstance()->SetSessionVar('current_id_user', 1);
			Auth::getInstance()->SetSessionVar('current_id_personage', 7);
			$idWorld = 0;
			$idLastMessage = 0;

			$messages = chat_Mapper::model()->findLastMessages($idLastMessage, $idWorld);
			$this->assertChatMessages($messages);

			$idLastMessage = 2;
			$messages = chat_Mapper::model()->findLastMessages($idLastMessage, $idWorld);
			$this->assertEquals(
				5, count($messages),
				'При последнем прочтённом сообщении с идентификаторм `2` должен быть массив с 5 найденными сообщениями'
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
	public function testInitChat()
	{
	    try
	    {
			Auth::getInstance()->SetSessionVar('current_id_user', 1);
			Auth::getInstance()->SetSessionVar('current_id_personage', 7);
			$idWorld = 0;

			$messages = chat_Mapper::model()->findMessagesWithLimit($idWorld);
			$this->assertChatMessages($messages);
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * @param array $messages
	 */
	private function assertChatMessages(array $messages)
	{
		$messagesFromMyGuild = $messages[1];
		$this->assertEquals(
			0, $messagesFromMyGuild->channel_id,
			'Сообщение должно иметь идентификатор комнаты (канала) `0` для текущего персонажа `7`'
		);
		$this->assertEquals(
			'Personage 7', $messagesFromMyGuild->nick_sender,
			'В сообщении должно быть поле `nick_sender` со значеним `Personage 7`'
		);

		$lastMessage = $messages[0];
		$this->assertEquals(
			chat_Mapper::PRIVATE_STATUS, $lastMessage->status,
			'Последнее добавленное сообщение должно быть адресовано персонажу `2` со статусом `private`'
		);

		$firstMessage = array_pop($messages);
		$this->assertEquals(
			chat_Mapper::NORMAL_STATUS, $firstMessage->status,
			'Более раннее сообщение должно быть со статусом `normal`'
		);
		$this->assertEquals(
			'Personage 1', $firstMessage->nick_sender,
			'Имя персонажа пользователя для самого раннего доступного сообщения должен быть `Personage 1`'
		);
	}
}