<?php

/**
 * Общий класс исключений.
 */
class E1Exception extends Exception {}

/**
 * Класс исключений для ошибок предметной области БД.
 */
class DBException extends Exception {

	/**
	 * Модель, вызвавшая ошибку.
	 * @var null|Mapper
	 */
	private $model = null;

	/**
	 * Перегрузка конструктора для сохранения модели, вызывавшей ошибку.
	 *
	 * @param string $message
	 * @param null|Mapper $model - Модель, вызвавшая ошибку.
	 * @param int $code
	 * @param Exception $previous
	 */
	public function __construct($message = "", $model = null, $code = 0, Exception $previous = null)
	{
		$this->model = $model;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Сохранение модели, вызвавщей ошибку.
	 * @param $model
	 */
	public function setModel($model)
	{
		$this->model = $model;
	}

	/**
	 * Получение модели, вызвавщей ошибку.
	 * @return null|Mapper
	 */
	public function getModel()
	{
		return $this->model;
	}
}

/**
 * Родительский класс обработки ошибок и статусов ошибок ответа JSON формата.
 */
class JSONResponseErrorException extends Exception
{
	/**
	 * Статус ошибки, отправляемый клиенту.
	 *
	 * @TODO: возможность создать пакетную отправку ошибок, если $status будет массивом
	 * @var mixed
	 */
	public $status;

	/**
	 * Перегрузка конструктора для обработки статусов ошибок.
	 *
	 * @param string $message
	 * @param int $status - по умолчанию общего назначения ошибки
	 * @param int $code
	 * @param Exception $previous
	 */
	public function __construct($message = "", $status = 0, $code = 0, Exception $previous = null)
	{
		$this->status = $status;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Отправка клиенту статусов ошибок и запись в журнал, если система находится в отладке.
	 *
	 * @param JSONAction $controller
	 * @param string  $titleLogMessage
	 */
	public function sendResponse(JSONAction $controller, $titleLogMessage)
	{
		if($this->status == 0) header("HTTP/1.0 300 Error");

		$controller->Viewer_Assign(array('status' => $this->status));
		if(Config::getInstance()->system['debug'] === true)
		{
			$controller->Viewer_Assign('error_text', $this->message);
			e1($titleLogMessage . $this->getMessage());
		}
	}
}

/**
 * Класс ошибок исключений для валидации значений.
 */
class ValidateErrorException extends JSONResponseErrorException {}

/**
 * Класс исключений для ошибок возращаемых пользователю в виде статусов;
 */
class StatusErrorException extends JSONResponseErrorException {}

/**
 *
 */
abstract class Object {} // base for Hook
/* из массива $oInstance вынести в переменные соответственного класса $oInstance?
 * public static function getInstance(){
    return (isset(self::$oInstance))?self::$oInstance:self::$oInstance = new self();
}
 */
/**
 *
 */
final class Factory {
    static public function Get($sClass, $aArgs = null) {
        return new $sClass($aArgs);
    }
}

/**
 *
 */
abstract class Singleton extends Object{
    static protected $oInstance = array();
    protected function __construct() {}
    private function __clone() {}

	/**
	 * @return Auth
	 */
	public final static function getInstance() {
        $sClass = get_called_class(); // Wait 5.4+ for static::static
        if (!isset(static::$oInstance[$sClass])) {
            static::$oInstance[$sClass] = new static();
        }
        return static::$oInstance[$sClass];
    }
} // Singleton

/**
 *
 */
class Status extends Singleton {

	private $status = array(
		'main_errors' => 0,
		'successfully' => 1,
		'user_not_found' => 2,
		'user_exists' => 3,
		'user_banned' => 4,
		'captcha_required' => 5,
		'captcha_not_matches' => 6,
		'login_password_not_matches' => 7,
		'password_empty_less_size' => 8,
		'password_not_matches' => 9,
		'login_empty' => 10,
		'email_not_valid' => 11,
		'blocked' => 12,
		'personage_exists' => 13,
		'link_old' => 14,
		'code_not_matches' => 15,
		'personage_not_exists' => 16,
		'no_resources' => 17,
		'units_not_available' => 18,
		'building_exists' => 19,
		'city_not_personage' => 20,
		'overload' => 21,
        'no_free_sections' => 22,
        'insufficient_level_city' => 23,
        'insufficient_level_building' => 24,
        'exists' => 25,
        'insufficient_level_building_castle' => 26,
        'is_in_progress_basic_building' => 27,
        'is_in_progress_resources_building' => 28,
        'invalid_building' =>29,
        'no_free_people' => 30,
        'process_construction_finished' => 31,
        'process_is_stopped' => 32,
        'not_delete_buildings' => 33,
        'destroy_building_banned' => 34,
        'insufficient_level_library_buildings' => 35,
        'improvement_already_exists' => 36,
        'insufficient_level_library_building' => 37,
        'not_over_home_improvement_building_library' => 38,
        'units_not_available_dismiss' => 39,
        'internal_improvement_building_is_finished'=> 40,
        'there_is_study'=> 41,
        'research_examined'=> 42
	);

	/**
	 * @param string $nameStatus
	 * @return int
	 */
	public function __get($nameStatus)
	{
		if(isset($this->status[$nameStatus]))
			return $this->status[$nameStatus];
		return -1;
	}
}

/**
 *
 */
final class Config extends Singleton {

	/**
	 * @var array
	 */
	private $aConfig = array();

	/**
	 *
	 */
	protected function __construct() {
        //TODO: maybe include self file index.php?
        //try-catch
		// TODO: При разворачивании на рабочем сервере возможно объеденить конфигурацинные файлы.
        $this->aConfig = $this->LoadConfig(Engine::GetRootDir().'config.php');
    }
    public function __get($name) {
        if (array_key_exists($name, $this->aConfig)) {
            return $this->aConfig[$name];
        }
        return null;
    }
    /**
     * Load config file
     * @param string $sFile
     * @return boolean - success flag
	 * @throws E1Exception
     */
    private function LoadConfig($sFile) {
            // Check if file exists
            if (!file_exists($sFile)) {
                throw new  E1Exception('Config load error. File:'. $sFile. ' do not exist');
            }
            // Get config from file
            include($sFile);

            if (!isset($config)) {
                throw new  E1Exception('Config parse error. File:'. $sFile. ' does not contain $config variable');
            }
            return $config;
    }
} // Config

final class Engine {
    const  VER = '2.9.15';
    static private $Engine;
    static private $sRootDir;
    static private $sApplicationDir;
    static private $sLibrayDir;
    static private $sModulesDir;
    static private $sTemplatesDir;
	static private $sComponentsDir;

    public function __construct() {
        if (!empty(self::$Engine)) {
            throw new E1Exception('Double init E1');
        }
            self::$Engine = 1;
            self::Init();
    }

    /**
     *  Clean Magic Quotes is enabled (core)
     */
    static private function MagicQClean() {
        if (get_magic_quotes_gpc()) {
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)) {
                foreach ($val as $k => $v) {
                    unset($process[$key][$k]);
                    if (is_array($v)) {
                        $process[$key][stripslashes($k)] = $v;
                        $process[] = &$process[$key][stripslashes($k)];
                    } else {
                        $process[$key][stripslashes($k)] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }
    }

    /**
     * E1 Crash callback
     */
    static private function Crash() {
        //show nice crash logo
        e1(print_r(func_get_args(), true));
        //contact(mail to admin)
        die();
    }

	/**
     * E1 Main autoloader (core)
     * @param string $class_name
	 * @return null
     */
    static private function AutoLoader($class_name) {
        $class_path = str_replace('_', DIRECTORY_SEPARATOR, $class_name, $count);
        // Attention $sFile required!!!
        /*
        self::$sTemplatesDir
         */
        switch ($count) {
            case 0: 
                //Engine Library
                $sFile = self::$sLibrayDir . 'E1' . DIRECTORY_SEPARATOR . $class_name .'.php';
                if (!file_exists($sFile)) {
                    //or external Library
                    $sFile = self::$sLibrayDir . $class_name . DIRECTORY_SEPARATOR . $class_name .'.php';
                }
                break;
            case 1:
                // Application
                $sFile = self::$sApplicationDir . $class_path . '.php';
                break;

			default:
				$sFile = self::$sApplicationDir . $class_path . '.php';
            /*case 2:
                //Plugins
                $sFile = self::$sModulesDir . $class_path . '.php';
                break;*/
        }

        if (!file_exists($sFile)) {
            return null;
        }
        
        require_once($sFile);
    }

    /**
     *
     * @return string - path to SiteRoot
     */
    static public function GetRootDir() {
        return self::$sRootDir;
    }

    /**
     * Main Init system routine
     */
    static private function Init() {
        // set rootdir and load config
        self::$sRootDir = __DIR__ . DIRECTORY_SEPARATOR;
        // loadconfig
        Config::getInstance();
        // check required config params
        // get path from config
		include_once(self::$sRootDir . 'debug/FirePHPCore/FirePHP.class.php');
		include_once(self::$sRootDir . '/debug/FirePHPCore/fb.php');

        self::$sApplicationDir = self::$sRootDir . 'Application' . DIRECTORY_SEPARATOR;
        self::$sLibrayDir      = self::$sRootDir . 'Library'     . DIRECTORY_SEPARATOR;
        self::$sTemplatesDir   = self::$sRootDir . 'Templates'   . DIRECTORY_SEPARATOR;
        self::$sModulesDir     = self::$sRootDir . 'Modules'     . DIRECTORY_SEPARATOR;
		self::$sComponentsDir = self::$sLibrayDir . 'components' . DIRECTORY_SEPARATOR;

        //preload modules
        //
        // Set error handler
        set_error_handler('self::Crash');
        // Set autoloader
        spl_autoload_register('self::AutoLoader');
        // comportable stuff
        self::MagicQClean();
    }

	/**
	 *
	 */
	public function Run()
	{
		// to Route
		Router::getInstance()->Exec();
	}

	/**
	 * Загрузка файла-компонента.
	 *
	 * @param string $nameComponent
	 * @return bool
	 * @author Greg
	 */
	public static function loadComponent($nameComponent)
	{
		$pathToFileComponent = self::$sComponentsDir . $nameComponent . '.php';
		if (!file_exists($pathToFileComponent))
			return false;
		else
			require_once($pathToFileComponent);

		return true;
	}
} //Engine

function e1() {
    $aParam = func_get_args();
    $aMsg = '['.date('d-M-Y H:i:s',time()).'] ';
    foreach ($aParam as $param) {
        if (is_string($param)) {$aMsg .=$param;} else {$aMsg .= var_export($param, true);};
    }
    file_put_contents('server.log',$aMsg.PHP_EOL, FILE_APPEND);
}



//Secure issue

ini_set("session.use_only_cookies", true);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', './server.log');

//Developer profile
//error_reporting(E_ALL);
//ini_set('display_errors', 1);


//E1 Go
header('X-Powered-By: E1 Engine '.Engine::VER);
// return this to viewer
header('Content-Type: text/html; charset=utf-8');

// TODO: Version check replace to CRC config.php and installer.php
if (PHP_MAJOR_VERSION < 5 || PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION < 3) {
    //Show core error page
    throw new  E1Exception('PHP version too old!');
}

$E1 = new Engine();
$E1->Run();
