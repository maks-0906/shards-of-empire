<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class chat_Mapper extends Mapper {

	const DEFAULT_COUNT_GET_MESSAGES = 10;

	const MAIN_TYPE_MESSAGE = 'main';
	const TRADE_TYPE_MESSAGE = 'trade';

	const NORMAL_STATUS = 'normal';
	const PRIVATE_STATUS = 'private';

	/**
	 * Идентификатор текущего пользователя.
	 *
	 * @var integer
	 */
	private $idCurrentUser;

	/**
	 * Идентификатор текущего персонажа пользователя.
	 *
	 * @var int
	 */
	private $idPersonage;

	/**
	 * Инициализация первоначальных параметров сущности.
	 */
	public function init()
	{
		$this->idCurrentUser = Auth::getInstance()->GetSessionVar('current_id_user');
		$this->idPersonage = Auth::getInstance()->GetSessionVar(SESSION_PERSONAGE_ID);
	}

	/**
	 * Получение экземпляра сущности.
	 *
	 * @param string $className
	 * @return chat_Mapper
	 */
	public static function model($className=__CLASS__)
	{
		return new $className();
	}

	/**
	 * Имя таблицы в БД для отображения.
	 * @return string
	 */
	public function tableName()
	{
		return 'chat_messages';
	}

	/**
	 * Поиск сообщений с возможностью ограничения выборки.
	 *
	 * @param integer $idWorld
	 * @param integer $limit
	 * @return array - ChatMessageMapper
	 */
	public function findMessagesWithLimit($idWorld, $limit = self::DEFAULT_COUNT_GET_MESSAGES)
	{
		$sql = "SELECT *,
				  (SELECT `nick` FROM `personages` WHERE `cm`.`sender_id`=`personages`.`id`) AS `nick_sender`,
			      (SELECT `nick` FROM `personages` WHERE `cm`.`recipient_id`=`personages`.`id`) AS `nick_recipient`
				FROM %s as cm
				WHERE
					`cm`.`world_id` = %d
					AND `cm`.`channel_id` IN('%s', '%s', (SELECT `guild_id` FROM `personages_state` WHERE `id_personage`=%d))
					AND ((`cm`.`status`='%s') OR (`cm`.`status`='%s' AND (`cm`.`recipient_id`=%d OR `cm`.`sender_id`=%d)))
				ORDER BY`cm`.`id` DESC LIMIT %d";

		$result = $this->findAll(
			$sql,
			$this->tableName(),
			$idWorld,
			self::MAIN_TYPE_MESSAGE, self::TRADE_TYPE_MESSAGE, $this->idPersonage,
			self::NORMAL_STATUS, self::PRIVATE_STATUS, $this->idPersonage, $this->idPersonage,
			$limit
		);

		return $result;
	}

	/**
	 * Выборка последних сообщений по идентификатору записи с возможностью ограничения списка.
	 *
	 * @param integer $lastIdMessage
	 * @param integer $idWorld
	 * @return array - ChatMessage_Mapper
	 */
	public function findLastMessages($lastIdMessage, $idWorld)
	{
		// Где идентификатор больше заданного И идентификатор мира равен заданному
		// И идентификатор комнаты равен Общему или Торговли или входящему пользователю в гильдию
		// И статус равен нормальному или статус приватному и получателем является текущий пользователь и я отправитель.

		//AND `personages`.`guild_id`=`cm`.`channel_id`
		$sql = "SELECT *,
				  (SELECT `nick` FROM `personages` WHERE `cm`.`sender_id`=`personages`.`id`) AS `nick_sender`,
				  (SELECT `nick` FROM `personages` WHERE `cm`.`recipient_id`=`personages`.`id`) AS `nick_recipient`
				FROM %s as cm
				WHERE
					`cm`.`id` > %d
					AND `cm`.`world_id` = %d
					AND `cm`.`channel_id` IN('%s', '%s', (SELECT `guild_id` FROM `personages_state` WHERE `id_personage`=%d))
					AND ((`cm`.`status`='%s') OR (`cm`.`status`='%s' AND (`cm`.`recipient_id`=%d OR `cm`.`sender_id`=%d)))
				ORDER BY `cm`.`id` DESC";

		$result = $this->findAll(
			$sql,
			$this->tableName(),
			$lastIdMessage,
			$idWorld,
			self::MAIN_TYPE_MESSAGE, self::TRADE_TYPE_MESSAGE, $this->idPersonage,
			self::NORMAL_STATUS, self::PRIVATE_STATUS, $this->idPersonage, $this->idPersonage);

		return $result;
	}

	/**
	 * Создание нового сообщения в чате.
	 *
	 * @param int $idWorld
	 * @param int $idChannel
	 * @param string $text
	 * @param bool|int $idRecipient
	 * @param bool $private
	 * @return chat_Mapper
	 */
	public function createNewMessage($idWorld, $idChannel, $text, $idRecipient = false, $private = false)
	{
		$this->sender_id =  $this->idPersonage;
		if($idRecipient !== false) $this->recipient_id = (int) $idRecipient;
		$this->world_id = $idWorld;
		$this->channel_id = $idChannel;
		$this->text = $text;
		if($private !== false) $this->status = self::PRIVATE_STATUS;

		return $this->save();
	}

	/**
	 * Получение списка каналов для чата.
	 *
	 * @return array
	 */
	public function getListChannel()
	{
		$channels = array(
			self::MAIN_TYPE_MESSAGE => 'Общий',
			self::TRADE_TYPE_MESSAGE => 'Торговля',
		);

		$idGuild = personage_State::model()->getGuildId($this->idPersonage);
		if($idGuild !== false) $channels = array_merge($channels, array($idGuild => 'Гильдия'));

		return $channels;
	}
}
