<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mapper
 *
 * @author al
 */
class user_Mapper extends Mapper {

    const TABLE_NAME = 'users';

	const COUNT_SYMBOL_GENERATE_SELF_PASSWORD = 7;
	const MIN_COUNT_SYMBOL_USER_PASSWORD = 3;

	const NORMAL_STATUS = 'normal';
	const BANNED_STATUS = 'banned';
	const DELETE_STATUS = 'delete';

    const LANG_RU = 'ru';
    const LANG_EN = 'en';

	/**
	 * Получение экземпляра сущности.
	 *
	 * @param string $className
	 * @return user_Mapper
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
	 * Поиск пользователя по логину.
	 *
	 * @param string $login
	 * @deprecated
	 * @return array
	 */
	public function GetUserbyLogin($login) {
        return $this->query('select * from ' . $this->tableName() . ' where login="%s" limit 1',$login)->valid();
    }

	/**
	 * Find user at field email for table in DB.
	 *
	 * @param string $email
	 * @return user_Mapper|null
	 */
	public static function findUserByEmail($email)
	{
		$user = new user_Mapper();
		return $user->find("select * from " . $user->tableName() . " where email='%s' limit 1", $email);
	}

	/**
	 * Поиск пользователя по IP регистрации.
	 *
	 * @param string $ip
	 * @return user_Mapper
	 */
	public function findUsersByIP($ip)
	{
		$user = new user_Mapper();
		/* @var $result user_Mapper*/
		$result = $user->findAll('select * from ' . $user->tableName() . ' where reg_ip="%s"', $ip);

		return $result;
	}

	/**
	 * Регистрация нового пользователя в системе.
	 *
	 * @param string $email
	 * @param string $password
	 * @param string $reg_ip
	 * @return user_Mapper
	 */
	public function createNewUser($email, $password, $reg_ip)
	{
		/* @var $this->email string */
		$this->email = $email;
		// В методе уже заполнены поля с зашифрованным паролем и солью ($this->password и $this->salt)
		$this->makePassword($password);
		$this->reg_ip = $reg_ip;

		return $this->save();
	}

	/**
	 * Поиск неисследованных миров для пользователя с идентификатором параметра, передвааемого в метод.
	 * @param integer $idUser
	 * @return user_Mapper|null
	 */
	public function findUnexploredWorldsUser($idUser)
	{
		$sql = "SELECT DISTINCT
					`map`.id as map_id,
					`map`.`name` as name_world,
					(SELECT abbreviation FROM `langlist` WHERE id=`map`.lang_id) as lang,
					(SELECT count(*) FROM `personages` WHERE `personages`.world_id=`map`.id) as current_count_users,
					`map`.max_users
				FROM %s as map
				LEFT OUTER JOIN `personages` as p
				ON `p`.user_id=%d AND `map`.id=`p`.world_id WHERE p.id IS NULL
				ORDER BY lang DESC";

		return $this->findAll(
			$sql,
			adminworld_Mapper::model()->tableName(),
			$idUser
		);

	}

	/**
	 * Поиск исследованных миров для пользователя с идентификатором параметра, передвааемого в метод.
	 * @param int $idUser
	 * @return array
	 */
	public function findExploredWorldsUser($idUser)
	{
		$sql = "SELECT
					`map`.id as map_id,
					`per_state`.id_dignity as total_level,
					`map`.name as name_world,
					`per`.time_online, `per`.nick,
					`per`.id as id_personage,
					`per`.status,
					`dig`.name_dignity,
					(SELECT abbreviation FROM `langlist` WHERE id=`map`.lang_id) as lang,
					(SELECT count(*) FROM `personages` WHERE `personages`.world_id=`map`.id) as current_count_users,
					`map`.max_users
				FROM %s as map
				LEFT OUTER JOIN %s as per
				     ON (`per`.world_id = `map`.id)
				         AND `per`.user_id = %d
				LEFT OUTER JOIN %s as per_state
				     ON (`per_state`.id_personage = `per`.id)
				INNER JOIN %s as dig
				     ON (`dig`.id_dignity = `per_state`.id_dignity)
				WHERE `per`.user_id = %d
				AND `per`.world_id = `map`.id
				ORDER BY `per`.time_online DESC";

		return $this->findAll(
			$sql,
			adminworld_Mapper::model()->tableName(),
			personage_Mapper::model()->tableName(), intval($idUser),
			personage_State::model()->tableName(),
            personage_parameters_Dignity::TABLE_NAME,
			$idUser
		);
	}

	/**
	 * Проверка существования пользователя в системе.
	 * TODO: Создать обычный запрос на существование пользователя в системе. Отвязаться от ошибки INSERT запроса!!!
	 * @return mixed
	 */
	public function isExists()
	{
		return $this->hasError('unique');
	}

	/**
	 * Создание кода восстановления пароля.
	 *
	 * @return user_Mapper
	 * @throws DBException
	 */
	public function createRecoveryCode()
	{
		if($this->id === null) throw new DBException('Create recovery code undefined ID user');

		$this->recovery_code = $this->randGenerator(16);

		// Бизнес логика не допуска использования восстановления пароля в течении 24 часов
		// при более 3 неудачных попыток восстановлений
		if((time() - $this->last_time_recovery) > 86400)
			$this->count_recovery = 0;
		else
			$this->count_recovery = $this->count_recovery + 1;

		$this->last_time_recovery = time();

		$this->save();

		return $this;
	}

	/**
	 * @return user_Mapper
	 */
	public function clearDataRecoveryPassword()
	{
		$this->recovery_code = '';
		$this->last_time_recovery = 0;
		$this->count_recovery = 0;

		return $this->save();
	}

	/**
	 * Создание пароля с возможностью генерации собственного пароля.
	 * Хэш пароля сохраняется в свойство записи. "соль" так же записывается в свойство записи.
	 *
	 * @TODO: Оптимизировать алгоритм создания пароля как собственного так и из указанных символов, не читаемый код !!!
	 * @param bool $password
	 * @return string пароль текстом
	 * @throws E1Exception
	 */
	public function makePassword($password = false)
	{
		$textPassword = '';
		if($password !== false)
		{
			if(!is_string($password))
				throw new E1Exception('Parameter `textPassword` must be string! textPassword = ' . $textPassword);
			$textPassword = $password;
			$this->password = $this->makeHashPassword($textPassword);
		}
		else
		{
			$textPassword = $this->randGenerator(self::COUNT_SYMBOL_GENERATE_SELF_PASSWORD);
			$passwordAfterClientAlgorithmEncryption = md5($textPassword);
			$this->password = $this->makeHashPassword($passwordAfterClientAlgorithmEncryption);
		}

		return $textPassword;
	}

	/**
	 * Генерация пароля.
	 *
	 * @param string $password
	 * @return string
	 */
	private function makeHashPassword($password)
	{
		$this->salt = $this->randGenerator(3);
		return md5($password . $this->salt);
	}

	/**
	 * Проверка корректности пароля введённого пользователем с паролем БД.
	 *
	 * @param string $userPassword
	 * @return bool
	 */
	public function isValidPassword($userPassword)
	{
		return $this->password == md5($userPassword . $this->salt);
	}

	/**
	 * Проверка доступности входа в систему.
	 *
	 * @return bool
	 * @throws StatusErrorException
	 */
	public function detectAccessInSystem()
	{
		fb($this, 'user--', FirePHP::ERROR);
		if($this->id == null || $this->status == user_Mapper::DELETE_STATUS)
			throw new StatusErrorException('User not found in system', $this->oStatus->user_not_found);

		if($this->status === user_Mapper::BANNED_STATUS) throw new StatusErrorException(
			'Sorry you banned!', $this->oStatus->user_banned
		);

		return true;
	}

	/**
	 * Генерация собственного набора символов. Используется как для генерации пароля,
	 * так может использоваться и для генерации "соли".
	 *
	 * @param integer $countNumbers
	 * @return string
	 */
	public function randGenerator($countNumbers)
	{
		$arr = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
			'v', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R',
			'S', 'T', 'U', 'V', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
		$pass = "";
		for ($i = 0; $i < $countNumbers; $i++)
		{
			$index = rand(0, count($arr) - 1);
			$pass .= $arr[$index];
		}

		return $pass;
	}
}
