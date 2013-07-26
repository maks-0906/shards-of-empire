<?php
/**
 * Файл содержит логику обработки и управление почтой
 * 
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package mail
 */

/**
 * Класс управления логикой обработки данных GUI почты пользователей.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package mail
 */
class mail_Action extends JSONAction {

	/**
	 * Register request uri for action method.
	 */
	public function RegisterEvent()
	{
			$this->AddEvent('income_messages.json', 'incomeMessages', Auth::AUTH_USER);
			$this->AddEvent('outcome_messages.json', 'outcomeMessages', Auth::AUTH_USER);
			$this->AddEvent('notices.json', 'notices', Auth::AUTH_USER);
			$this->AddEvent('send_message.json', 'sendMessage', Auth::AUTH_USER);
			$this->AddEvent('mark_is_read.json', 'markIsRead', Auth::AUTH_USER);
			$this->AddEvent('count_not_read_messages.json', 'countNotReadMessages', Auth::AUTH_USER);
			
			//$this->SetDefaultEvent('income_messages.json');
	}

	/**
	 * Функция-событие получения списка входящих сообщений
	 * 
	 */
	public function incomeMessages()
	{
		try
		{
			// Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);
                
			$page = $this->GetVar('page');

			$page = intval($page);
			$countPages = mail_Mapper::model()->countIncomeMessagesPages();
			if ($page < 1 || $page > $countPages)
				$page = 1;
			
			$messages = array();
			$messages = mail_Mapper::model()->findIncomeMessagesByPage($page);

			$this->Viewer_Assign('currentPage', $page);
			$this->Viewer_Assign('countPages', $countPages);
			$this->Viewer_Assign('messages', $this->formatJSONResponse($messages));
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `incomeMessages` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `incomeMessages` error: ', $e->getMessage());
		}
	}

	/**
	 * Функция-событие получения списка исходящих сообщений
	 * 
	 */
	public function outcomeMessages()
	{
		try
		{
			// Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);
                
			$page = $this->GetVar('page');

			$page = intval($page);
			$countPages = mail_Mapper::model()->countOutcomeMessagesPages();
			if ($page < 1 || $page > $countPages)
				$page = 1;
			
			$messages = array();
			$messages = mail_Mapper::model()->findOutcomeMessagesByPage($page);

			$this->Viewer_Assign('currentPage', $page);
			$this->Viewer_Assign('countPages', $countPages);
			$this->Viewer_Assign('messages', $this->formatJSONResponse($messages));
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `outcomeMessages` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `outcomeMessages` error: ', $e->getMessage());
		}
	}

	/**
	 * Функция-событие получения списка уведомлений
	 * 
	 */
	public function notices()
	{
		try
		{
			// Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);
			
			$page = $this->GetVar('page');

			$page = intval($page);
			$countPages = mail_Mapper::model()->countNoticesPages();
			if ($page < 1 || $page > $countPages)
				$page = 1;
			
			$messages = array();
			$messages = mail_Mapper::model()->findNoticesByPage($page);

			$this->Viewer_Assign('currentPage', $page);
			$this->Viewer_Assign('countPages', $countPages);
			$this->Viewer_Assign('messages', $this->formatJSONResponse($messages));
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `notices` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `notices` error: ', $e->getMessage());
		}
	}

	/**
	 * Функция-событие отправки сообщения
	 * 
	 */
	public function sendMessage()
	{
		try
		{
			// Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);
			
			$idTo = $this->GetVar('id_to');
			if($idTo  === null) 
				throw new StatusErrorException('Parameter `id_to` not defined', $this->status->main_errors);

			$subject = $this->GetVar('subject');
			if($subject  === null) 
				throw new StatusErrorException('Parameter `subject` not defined', $this->status->main_errors);

			$body = $this->GetVar('body');
			if($body  === null) 
				throw new StatusErrorException('Parameter `body` not defined', $this->status->main_errors);
				
			// Проверяем, существует ли такой получатель
			if (!personage_Mapper::model()->findPersonageById($idTo))
				throw new StatusErrorException('Personage not found', $this->status->main_errors);

			if (!mail_Mapper::model()->createNewMessage($idTo, $subject, $body))
				throw new StatusErrorException('Same error in send message', $this->status->main_errors);
			
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `sendMessage` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `sendMessage` error: ', $e->getMessage());
		}
	}

	/**
	 * Функция-событие отметки о прочтении письма
	 * 
	 */
	public function markIsRead()
	{
		try
		{
			// Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);
			
			$idMessage = $this->GetVar('id_message');
			if($idMessage  === null) 
				throw new StatusErrorException('Parameter `id_message` not defined', $this->status->main_errors);

			if (!mail_Mapper::model()->markIsRead($idMessage))
				throw new StatusErrorException('Failed mark message is read', $this->status->main_errors);
			
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `markIsRead` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `markIsRead` error: ', $e->getMessage());
		}
	}
	
	/**
	 * Функция-событие получения количества непрочитанных сообщений/уведомлений
	 * 
	 */
	public function countNotReadMessages()
	{
		try
		{
			// Проверяем, чтобы персонаж был залогинен
            $idPersonage = Auth::getIdPersonage();
            if ($idPersonage == null)
                throw new StatusErrorException('Personage in world not defined', $this->status->personage_not_exists);

			$count = mail_Mapper::model()->countNotReadMessages();
			
			if ($count == null)
				throw new StatusErrorException('Failed in function `countNotReadMessages`', $this->status->main_errors);
			
			$this->Viewer_Assign('count', $count);
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action `countNotReadMessages` validate: ');
		}
		catch(E1Exception $e)
		{
			e1('Action `countNotReadMessages` error: ', $e->getMessage());
		}
	}
}
