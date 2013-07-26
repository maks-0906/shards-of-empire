<?php
/**
 * Class Provide Authontification to Action
 */
final class Auth extends Singleton {
	const AUTH_NONE = 0;
	const AUTH_USER = 1;
	const AUTH_ADMIN = 255;
	private $iCurrentAuthLevel;

	protected function __construct()
	{
		//TODO: Если бы язык хранить только зарегистрированным можно не стартовать сессию до логина
		session_start();
		// Get User level with stored session data
		$this->SetCurAuthLevel($this->GetSessionVar('e1_autorized_level') ? $this->GetSessionVar('e1_autorized_level') : 0);
		// create CRUD-matrix
	}

	/**
	 * Return Session var
	 *
	 * @param string $sName
	 * @param null|string $default
	 * @return mixed
	 */
	public static function GetSessionVar($sName, $default = null)
	{
		return isset($_SESSION[$sName]) ? $_SESSION[$sName] : $default;
	}

	/**
	 * Store Var in Session
	 *
	 * @param string $sName
	 * @param mixed $data
	 */
	public static function SetSessionVar($sName, $data)
	{
		if(is_array($data) && is_array($_SESSION[$sName]))
			$_SESSION[$sName] = array_merge($_SESSION[$sName], $data);
		else
			$_SESSION[$sName] = $data;
	}

	public function GetCurAuthLevel()
	{
		//TODO: Сделано для реализации хуков
		return $this->iCurrentAuthLevel;
	}

	/**
	 * @param $iLevel
	 */
	private function SetCurAuthLevel($iLevel)
	{
		//TODO: Сделано для реализации хуков
		$this->iCurrentAuthLevel = (int)$iLevel;
		$this->SetSessionVar('e1_autorized_level', $iLevel);
	}

	/**
	 * Do User Login
	 */
	public function Login($access_level = self::AUTH_USER)
	{
		$this->SetCurAuthLevel($access_level);
	}

	/**
	 * @return bool
	 */
	public function isLogin()
	{
		//TODO: переделать
		return ($this->iCurrentAuthLevel > self::AUTH_NONE) ? true : false;
	}

	/**
	 * Do Logout
	 */
	public function Logout()
	{
		session_destroy();
	}

	/**
	 * Очистка сессии.
	 */
	public static function clearSession()
	{
		$_SESSION = array();
	}

	/**
	 * Check Action(Script) Can Run?
	 *
	 * @param integer $iLevel - check level
	 * @return boolean
	 */
	public function CanRun($iLevel)
	{
		//eg: Current 255 - is Admin >= FromAction 1 - Authorized
		if($this->GetCurAuthLevel() >= $iLevel)
		{
			return true;
		}
		return false;
	}

	/**
	 * Generate seckey for advanced check send form
	 *
	 * @return string
	 */
	public function GenerateSecurityKey()
	{
		$k1 = rand(1, 7);
		$k2 = rand(1, 7);
		$digest = md5(($k1 + $k2) . session_id() . ($k2 - $k1) . time() . ($k1 - $k2));
		$this->SetSessionVar('e1_security_key', $digest);
		return $digest;
	}

	/**
	 * @return mixed
	 */
	public function GetSecurityKey()
	{
		return $this->GetSessionVar('e1_security_key');
	}

	/**
	 * check the security key
	 *
	 * @param string $digest
	 * @return boolean
	 */
	public function CheckSecurityKey($digest)
	{
		if($digest == $this->GetSessionVar('e1_security_key'))
		{
			return true;
		}
		return false;
	}

	/**
	 * @param $lang
	 */
	public function SetLang($lang)
	{
		self::SetSessionVar(user_Action::LANG_USER, basename($lang));
	}

	/**
	 * @return mixed
	 */
	public static function GetStoreLang()
	{
		return self::GetSessionVar(user_Action::LANG_USER);
	}

	/**
	 * Получение идентификатора пользователя из сессии.
	 * @return int
	 */
	public static function id()
	{
		return self::GetSessionVar('current_id_user');
	}

	/**
	 * Сохрание идентификатора пользователя в сессии.
	 * @param int $idUser
	 * @return bool
	 */
	public static function setId($idUser)
	{
		self::SetSessionVar('current_id_user', $idUser);
		return true;
	}

	/**
	 * Сохранение идентификатора персонажа в сессии.
	 * @param int $idPersonage
	 * @return bool
	 */
	public static function setIdPersonage($idPersonage)
	{
		self::SetSessionVar('current_id_personage', $idPersonage);
		return true;
	}

	/**
	 * Получение идентификатора текущего персонажа.
	 * @return int
	 */
	public static function getIdPersonage()
	{
		return self::GetSessionVar('current_id_personage');
	}


	/**
	 * Сохранение текущей локации персонажа в сессии.
	 * @param int $idLocation
	 * @return bool
	 */
	public static function setCurrentIdLocation($idLocation)
	{
		$_SESSION['location']['id'] = $idLocation;
		return true;
	}

	/**
	 * Получение идентификатора текущей локации персонажа.
	 * @return int
	 */
	public static function getCurrentIdLocation()
	{
		return $_SESSION['location']['id'];
	}

	/**
	 * Сохранение текущей локации персонажа в сессии.
	 * @param int $x
	 * @param int $y
	 * @return bool
	 */
	public static function setCurrentLocationCoordinates($x, $y)
	{
		$_SESSION['location']['x'] = $x;
		$_SESSION['location']['y'] = $y;
		return true;
	}

	/**
	 * Получение идентификатора текущей локации персонажа.
	 * @return int
	 */
	public static function getCurrentLocationCoordinates()
	{
		return array('x' => $_SESSION['location']['x'], 'y' => $_SESSION['location']['y']);
	}

	/**
	 * Сохранение паттерна локации в сессии.
	 * @param int $pattern
	 * @return bool
	 */
	public static function setPatternCurrentLocation($pattern)
	{
		self::SetSessionVar('location', array('pattern' => $pattern));
		return true;
	}

	/**
	 * Получение паттерна текущей локации персонажа.
	 * @return int
	 */
	public static function getPatternCurrentLocation()
	{
		return $_SESSION['location']['pattern'];
	}

    /**
     * Определяем принадлежность города
     *
     * @return bool
     */
    public static function isCurrentLocationCity(){
        if(pattern_Mapper::CITY_PATTERN == Auth::getPatternCurrentLocation())
            return true;
        else
            return false;
    }

	/**
	 * Получение текущего идентификатора миира персонажа.
	 * @return mixed
	 */
	public static function getCurrentWorldId()
	{
		return $_SESSION['location']['world_id'];
	}

	/**
	 * Сохранение идентификатора мира, в который вошёл пользователь.
	 * @param int $idWorld
	 * @return bool
	 */
	public static function setCurrentWorldId($idWorld)
	{
		$_SESSION['location']['world_id'] = $idWorld;
		return true;
	}
}