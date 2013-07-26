<?php
/**
 * Файл содержит класс модель, управляющая почтой.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package mail
 */

/**
 * Класс модель, управляющая почтой.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package mail
 */
class mail_Mapper extends Mapper {

	const TABLE_NAME = 'personages_mail';
	
	// Количество сообщений на странице
	const COUNT_MESSAGES_PER_PAGE = 5;

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
		$this->idPersonage = Auth::getInstance()->GetSessionVar(SESSION_PERSONAGE_ID);
	}

	/**
	 * Получение экземпляра сущности.
	 *
	 * @param string $className
	 * @return mail_Mapper
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
		return self::TABLE_NAME;
	}
	
	/**
	 * Возвращаем список входящих сообщений
	 *
	 * @param int $page - текущая страница
	 * @return int - countPages
	 */
	public function findIncomeMessagesByPage($page = 1)
	{
		$sql = "SELECT *
                FROM %1\$s
				WHERE
					`to` = '%2\$d'
					AND `is_notice` = 0
				ORDER BY
					`create_date` DESC
				LIMIT
					%3\$d, %4\$d";

		return $this->findAll($sql, self::TABLE_NAME, $this->idPersonage, ($page - 1) * self::COUNT_MESSAGES_PER_PAGE,
			                        self::COUNT_MESSAGES_PER_PAGE
		);
	}

	/**
	 * Возвращаем количество страниц входящих сообщений
	 *
	 * @return int - countPages
	 */
	public function countIncomeMessagesPages()
	{
		$sql = "SELECT 
					count(*) as `count`
				FROM 
					`%1\$s`
				WHERE
					`to` = '%2\$d'
					AND `is_notice` = '0'";

		$result = $this->find($sql, self::TABLE_NAME, $this->idPersonage);
		
		if (!$this->isError())
		{
			$countPages = ceil($result->count / self::COUNT_MESSAGES_PER_PAGE);
			return !$countPages ? 1 : $countPages;
		}
		else
			return 1;
	}

	/**
	 * Возвращаем список исходящих сообщений
	 *
	 * @param int $page - текущая страница
	 * @return int - countPages
	 */
	public function findOutcomeMessagesByPage($page = 1)
	{
		$sql = "SELECT 
					*
				FROM 
					`%1\$s`
				WHERE
					`from` = '%2\$d'
					AND `is_notice` = '0'
				ORDER BY
					`create_date` DESC
				LIMIT
					%3\$d, %4\$d";

		return $this->findAll(
			$sql,
			self::TABLE_NAME,
			$this->idPersonage,
			($page - 1) * self::COUNT_MESSAGES_PER_PAGE,
			self::COUNT_MESSAGES_PER_PAGE
		);
	}

	/**
	 * Возвращаем количество страниц исходящих сообщений
	 *
	 * @return int - countPages
	 */
	public function countOutcomeMessagesPages()
	{
		$sql = "SELECT 
					count(*) as `count`
				FROM 
					`%1\$s`
				WHERE
					`from` = '%2\$d'
					AND `is_notice` = '0'";

		$result = $this->find($sql, self::TABLE_NAME, $this->idPersonage);

		if (!$this->isError())
		{
			$countPages = ceil($result->count / self::COUNT_MESSAGES_PER_PAGE);
			return !$countPages ? 1 : $countPages;
		}
		else
			return 1;
	}

	/**
	 * Возвращаем количество страниц уведомлений
	 *
	 * @return int - countPages
	 */
	public function countNoticesPages()
	{
		$sql = "SELECT 
					count(*) as `count`
				FROM 
					`%1\$s`
				WHERE
					`to` = '%2\$d'
					AND `is_notice` = '1'";

		$result = $this->find($sql, self::TABLE_NAME, $this->idPersonage);

		if (!$this->isError())
		{
			$countPages = ceil($result->count / self::COUNT_MESSAGES_PER_PAGE);
			return !$countPages ? 1 : $countPages;
		}
		else
			return 1;
	}

	/**
	 * Возвращаем список уведомлений
	 *
	 * @param int $page - текущая страница
	 * @return int - countPages
	 */
	public function findNoticesByPage($page = 1)
	{
		$sql = "SELECT 
					*
				FROM 
					`%1\$s`
				WHERE
					`to` = '%2\$d'
					AND `is_notice` = '1'
				ORDER BY
					`create_date` DESC
				LIMIT
					%3\$d, %4\$d";

		return $this->findAll($sql, self::TABLE_NAME, $this->idPersonage, ($page - 1) * self::COUNT_MESSAGES_PER_PAGE,
			                       self::COUNT_MESSAGES_PER_PAGE
		);
	}

	/**
	 * Отмечает письмо прочитанным
	 *
	 * @param int $idMessage - ИД письма
	 * @return int - countPages
	 */
	public function markIsRead($idMessage)
	{
		$sql = "UPDATE 
					`%1\$s`
				SET
					`is_read` = '1'
				WHERE
					`to` = '%2\$d'
					AND `id` = '%3\$d'";

		$result = $this->query(
			$sql,
			self::TABLE_NAME,
			$this->idPersonage,
			$idMessage
		);

		return !$this->isError() & $this->getAffectedRows($result);
	}

	/**
	 * Создание нового сообщения
	 *
	 * @param int $idTo - ИД персонажа получателя
	 * @param int $idFrom - ИД персонажа отправителя (по умолчанию текущий персонаж)
	 * @param string $subject - Тема сообщения
	 * @param string $body - Тело сообщения
	 * @return mail_Mapper
	 */
	public function createNewMessage($idTo, $subject, $body, $idFrom = null)
	{
		$this->from = $idFrom ? $idFrom : $this->idPersonage;
		$this->to = (int) $idTo;
		$this->subject = $subject;
		$this->body = $body;

		return $this->save();
	}
	
	/**
	 * Создание нового уведомления
	 *
	 * @param $mailAttribute
	 * @return mail_Mapper
	 */
    public function createNewNotice($mailAttribute)
	{
		$this->from = $mailAttribute['from'];
		$this->to = (int) $mailAttribute['to'];
		$this->subject = $mailAttribute['subject'];
		$this->body = $mailAttribute['body'];
		$this->is_notice = true;

		return $this->save();
	}
	
	/**
	 * Получение количества непрочитанных сообщений/уведомлений
	 *
	 * @return mail_Mapper
	 */
	public function countNotReadMessages()
	{
		$sql = "SELECT 
					count(*) as `count`
				FROM 
					`%1\$s`
				WHERE
					`to` = '%2\$d'
					AND `is_read` = '0'";

		$result = $this->find($sql, self::TABLE_NAME, $this->idPersonage);

		if (!$this->isError())
			return $result->count;
		else
			return 0;
	}
}
