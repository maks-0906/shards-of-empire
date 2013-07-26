<?php
/**
 * Файл содержит класс управления логикой обработки данных GUI чата пользователей.
 *
 * @author Greg
 * @package actions
 */

/**
 * Класс управления логикой обработки данных GUI чата пользователей.
 *
 * @author Greg
 * @version 1.0.0
 * @package actions
 */
class chat_Action extends JSONAction {

	const TIME_LAST_MESSAGE_CHAT = 'time_last_msg';
	const MESSAGE_LAST_ID = 'last_id';
	const ACCESS_TIME_FOR_ONLINE = 600;

	/**
	 * Register request uri for action method.
	 */
	public function RegisterEvent()
	{
		if($this->oConfig->system['debug'] === true)
		{
			$this->AddEvent('clear_messages', 'actionClearChatMessages');
		}
            $this->AddEvent('chat.json', 'index', Auth::AUTH_USER);
			$this->AddEvent('messages.json', 'getMessages', Auth::AUTH_USER);
			$this->AddEvent('send.json', 'sendMessage', Auth::AUTH_USER);
			$this->SetDefaultEvent('chat.json');
        }

	/**
	 *
	 */
	public function actionClearChatMessages()
	{
		try
		{
			chat_Mapper::model()->clearTable();
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(E1Exception $e)
		{
			e1('Action `index` error: ', $e->getMessage());
		}
	}

	/**
	 * Действие инициализации чата для пользователя.
	 * Является действием по умолчанию.
	 */
	public function index()
	{
		try
		{
			$this->detectExistsPersonageForCurrentUser();

			$idWorld = $this->GetVar('world_id');
			if($idWorld === null) throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);

			$messages = array();
			$chat = chat_Mapper::model();
            $personages = personage_Mapper::model();

			$messages = $chat->findMessagesWithLimit($idWorld);
			$this->setLastTimeAndIDMessage($messages);
            $personagesOnline = $personages->findOnlinePersonagesForRequiredWorld($idWorld, self::ACCESS_TIME_FOR_ONLINE);

			$this->Viewer_Assign('chatRefreshTime', 3);
			$this->Viewer_Assign('chatMessageMaxLength', 255);
			$this->Viewer_Assign('channels', $chat->getListChannel());
			$this->Viewer_Assign('messages', $this->formatJSONResponse($messages));
			$this->Viewer_Assign('personages_online', $this->formatJSONResponse($personagesOnline));
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action init chat validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `index` error: ', $e->getMessage());
		}
	}

	/**
	 * Действие управляет логикой получения сообщений разных типов,
	 * в зависимости от заданных в запросе параметров. Является дефолтным по умолчанию.
	 */
	public function getMessages()
	{
		try
		{
			$this->detectExistsPersonageForCurrentUser();

			$idWorld = $this->GetVar('world_id');
			$chatMessageLastId = $this->GetVar('chatMessageLastId');
			if($idWorld === null) throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);
			if($chatMessageLastId === null) throw new StatusErrorException('Parameter `chatMessageLastId` not defined', $this->status->main_errors);

			//TODO: Добавить логику с серверным состоянием $messages = $chat->findLastMessages($this->oAuth->GetSessionVar(self::MESSAGE_LAST_ID), $idWorld);
			$messages = chat_Mapper::model()->findLastMessages($chatMessageLastId, $idWorld);
			$this->setLastTimeAndIDMessage($messages);
			$personagesOnline = personage_Mapper::model()->findOnlinePersonagesForRequiredWorld($idWorld, self::ACCESS_TIME_FOR_ONLINE);

			personage_Mapper::model()->setTimeOnline(time(), Auth::getIdPersonage());

			$this->Viewer_Assign('personages_online', $this->formatJSONResponse($personagesOnline));
			$this->Viewer_Assign('messages', $this->formatJSONResponse($messages));
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action get message validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `get list message` error: ', $e->getMessage());
		}
	}

	/**
	 * Фиксация времени последнего прочитанного сообщения пользователем.
	 *
	 * @param array $messages
	 * @return void
	 */
	private function setLastTimeAndIDMessage(array $messages)
	{
		$timeLastMessage = time();
		if(!empty($messages) && $messages[0] instanceof chat_Mapper)
		{
			$timeLastMessage = $messages[0]->create_time;
			$this->oAuth->SetSessionVar(self::MESSAGE_LAST_ID, $messages[0]->id);
		}
		$this->oAuth->SetSessionVar(self::TIME_LAST_MESSAGE_CHAT, $timeLastMessage);
	}

	/**
	 * Действие реагирует на событие отправки сообщения пользователем
	 * для сохранения в БД с последующим выводом пользователям.
	 */
	public function sendMessage()
	{
		try
		{
			$this->detectExistsPersonageForCurrentUser();

			$idWorld = $this->GetVar('world_id');
			$idChannel = $this->GetVar('channel_id');
			$text = $this->GetVar('text');
			$idRecipient = $this->GetVar('recipient_id');
			$private = ($this->GetVar('private') === null) ? false : $this->GetVar('private');

			if($idWorld === null) throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);
			if($idChannel === null) throw new StatusErrorException('Parameter `channel_id` not defined', $this->status->main_errors);
			if($text === null) throw new StatusErrorException('Parameter `text` not defined', $this->status->main_errors);
			if($private && $idRecipient === null)
				throw new StatusErrorException('Parameter `recipient_id` not defined for private message', $this->status->main_errors);

			$result = chat_Mapper::model()->createNewMessage($idWorld, $idChannel, $text, $idRecipient, $private);

			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action send message validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `send message (send)` error: ', $e->getMessage());
		}
	}

	/**
	 * Определение существования персонажа для текущего пользователя и требуемого мира.
	 *
	 * @throws StatusErrorException
	 */
	private function detectExistsPersonageForCurrentUser()
	{
		$idWorld = $this->GetVar('world_id');
		if($idWorld === null) throw new StatusErrorException('Parameter `world_id` not defined', $this->status->main_errors);
		$model = personage_Mapper::model();

		$currentPersonage = $model->findPersonageForCurrentUserAndWorld($idWorld);
		if($currentPersonage == null)
			throw new StatusErrorException(
				'For current user personage not found in require world ',
				$this->status->user_not_found
			);
	}
}
