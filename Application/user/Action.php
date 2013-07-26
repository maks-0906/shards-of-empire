<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//require_once(Config::getInstance()->site['components_dir'] . '/EmailDriver.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/Library/components/EmailDriver.php');

/**
 * Description of Action
 *
 * @property Auth $oAuth
 * @property Status $status
 * @property Viewer $oViewer
 * @property Config $oConfig
 * @property array $json
 * @author al
 */
class user_Action extends JSONAction {

	const KEY_CAPTCHA_SESSION_REGISTER = 'captcha_keystring';
	const COUNT_REQUEST_REGISTER = 'count_register';
	const LAST_TIME_REQUEST_REGISTER = 'last_time_register';
	const IP_REQUEST_REGISTER = 'ip_register';

	const KEY_CAPTCHA_SESSION_LOGIN = 'captcha_login';
	const COUNT_REQUEST_AUTH = 'count_auth';
	const LAST_TIME_REQUEST_AUTH = 'last_time_auth';
	const IP_REQUEST_AUTH = 'ip_auth';

	const KEY_CAPTCHA_SESSION_RECOVERY = 'captcha_recovery';
	const COUNT_REQUEST_RECOVERY  = 'count_recovery';
	const LAST_TIME_REQUEST_RECOVERY  = 'last_time_recovery';
	const IP_REQUEST_RECOVERY  = 'ip_recovery';
	const TIME_BLOCKED_RECOVERY  = 'time_blocked_recovery';
    const LANG_USER  = 'e1_lang';

	public function RegisterEvent()
	{
		$this->AddEvent('register.json', 'actionRegister');
		$this->AddEvent('login.json', 'actionLogin');
		$this->AddEvent('recovery.json', 'actionPasswordRecovery');
		$this->AddEvent('confirmation.json', 'actionConfirmationPasswordRecovery');
		//$this->AddEvent('default', 'actionLogin');
        $this->AddEvent('captcha', 'actionRecoveryCaptcha');
        $this->AddEvent('captcha.register', 'actionRegisterCaptcha');
        $this->AddEvent('captcha.auth', 'actionAuthCaptcha');
		//$this->SetDefaultEvent('default');
	}

	/**
	 * Действие создаёт каптчу и отдаёт клиенту Url к изображению.
	 */
	public function actionRecoveryCaptcha() {
        $captcha = new kcaptcha();
        $this->oAuth->SetSessionVar(self::KEY_CAPTCHA_SESSION_RECOVERY, $captcha->getKeyString());
		if($this->oConfig->system['debug'] == true) $captcha->writeCaptchaInTestFile();
        exit;
    }

	public function actionRegisterCaptcha()
	{
		$captcha = new kcaptcha();
		$this->oAuth->SetSessionVar(self::KEY_CAPTCHA_SESSION_REGISTER, $captcha->getKeyString());
		if($this->oConfig->system['debug'] == true) $captcha->writeCaptchaInTestFile();
		exit;
	}

	public function actionAuthCaptcha()
	{
		$captcha = new kcaptcha();
		$this->oAuth->SetSessionVar(self::KEY_CAPTCHA_SESSION_LOGIN,  $captcha->getKeyString());
		if($this->oConfig->system['debug'] == true) $captcha->writeCaptchaInTestFile();
		exit;
	}

	/**
	 * Действие управляет алгоритмом регистрации пользователя в системе.
	 *
	 * @throws ValidateErrorException
	 */
	public function actionRegister()
	{
		try
		{
			$userEmail = $this->GetVar('login');
			$userPassword = $this->GetVar('password');
			$ipRegister = $_SERVER['REMOTE_ADDR'];

			if($this->oAuth->GetSessionVar(self::KEY_CAPTCHA_SESSION_REGISTER) !== null)
			{
				$captcha = $this->GetVar('captcha');
				if($captcha != $this->oAuth->GetSessionVar(self::KEY_CAPTCHA_SESSION_REGISTER))
					throw new StatusErrorException('Captcha not matches!', $this->status->captcha_not_matches);
			}
			else
				$this->showCaptchaRegister($ipRegister);

			if(!filter_var($userEmail, FILTER_VALIDATE_EMAIL))
				throw new ValidateErrorException('Email not valid!', $this->status->email_not_valid);
			if(!$userPassword || strlen($userPassword) < user_Mapper::MIN_COUNT_SYMBOL_USER_PASSWORD)
				throw new ValidateErrorException('Password not defined or is small!', $this->status->password_empty_less_size);

			$newUser = user_Mapper::model()->createNewUser($userEmail, $userPassword, $ipRegister);
			if($newUser->isExists()) throw new ValidateErrorException('Login is exists in system!', $this->status->user_exists);


			Auth::clearSession();
			$this->oAuth->Login(Auth::AUTH_USER);
            $this->oAuth->SetLang($newUser->lang);
			$this->oAuth->SetSessionVar('current_id_user', $newUser->id);

			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action register validate: ');
		}
		catch(Exception $e)
		{
			e1('Action `register` error: ', $e->getMessage());
		}
	}

	/**
	 * Определение условия показа каптчи.
	 *
	 * @param string $ipRegister
	 * @throws StatusErrorException
	 */
	private function showCaptchaRegister($ipRegister)
	{
		if($this->oAuth->GetSessionVar(self::IP_REQUEST_REGISTER) === null)
			$this->initSessionParametersRegister($ipRegister);

		$users = user_Mapper::model()->findUsersByIP($ipRegister);
		$period = time() - $this->oAuth->GetSessionVar(self::LAST_TIME_REQUEST_REGISTER);
		$countRequest = $this->oAuth->GetSessionVar(self::COUNT_REQUEST_REGISTER);

		// Если пользователь уже регистрировлся с такого IP или за одну минуту произошло 10 запросов выводим каптчу
		if(count($users) > 0 || ($period < 60 &&  $countRequest > 10)
			&& $this->oAuth->GetSessionVar(self::IP_REQUEST_REGISTER) === $ipRegister)
		{
			throw new StatusErrorException('Captcha required!', $this->status->captcha_required);
		}
		else
		{
			// Если период между запросами меньше минуты увеличиваем счётчик, иначе "обнуляем" параметры.
			if($period < 60)
				$this->oAuth->SetSessionVar(self::COUNT_REQUEST_REGISTER, $countRequest + 1);
			else
			{
				$this->oAuth->SetSessionVar(self::LAST_TIME_REQUEST_REGISTER, time());
				$this->oAuth->SetSessionVar(self::COUNT_REQUEST_REGISTER, 1);
			}
		}
	}

	/**
	 * Инициализация первоначальных данных в сессию.
	 *
	 * @param string $ipRegister
	 */
	private function initSessionParametersRegister($ipRegister)
	{
		$this->oAuth->SetSessionVar(self::IP_REQUEST_REGISTER, $ipRegister);
		$this->oAuth->SetSessionVar(self::COUNT_REQUEST_REGISTER, 1);
		$this->oAuth->SetSessionVar(self::LAST_TIME_REQUEST_REGISTER, time());
	}

	/**
	 * Действие, управляющее алгоритмом аутентификации пользователя в системе.
	 *
	 * @throws StatusErrorException
	 */
	public function actionLogin()
	{
		try
		{
			// Инициализация количества входов для отслеживания не правильных попыток
			$this->initSessionParametersForAuth($_SERVER['REMOTE_ADDR']);
			$this->showCaptchaForAuth($_SERVER['REMOTE_ADDR']);

			$userEmail = $this->GetVar('login');
			$userPassword = $this->GetVar('password');

			if(!filter_var($userEmail, FILTER_VALIDATE_EMAIL))
				throw new ValidateErrorException('Email not valid!', $this->status->email_not_valid);

			$user = user_Mapper::findUserByEmail($userEmail);
			if($user === null) throw new StatusErrorException('User not found in system', $this->status->user_not_found);

			$user->detectAccessInSystem();
			if(!$user->isValidPassword($userPassword))
			{
				$this->oAuth->SetSessionVar(self::COUNT_REQUEST_AUTH, $this->oAuth->GetSessionVar(self::COUNT_REQUEST_AUTH) + 1);
				throw new StatusErrorException('Password not matches!', $this->status->password_not_matches);
			}

			$this->oAuth->Login(Auth::AUTH_USER);
			$this->oAuth->SetSessionVar('current_id_user', $user->id);
            $this->oAuth->SetLang($user->lang);

			$this->Viewer_Assign('status', $this->status->successfully);
			$this->Viewer_Assign('id',  $user->id);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action login validate: ');
		}
		catch(Exception $e)
		{
			e1('Action `login` error: ', $e->getMessage());
		}
	}

	/**
	 * Инициализация первоначальных данных в сессии для отслеживания частоты запросов.
	 *
	 * @params string $ipAuth
	 */
	private function initSessionParametersForAuth($ipAuth)
	{
		if($this->oAuth->GetSessionVar(self::COUNT_REQUEST_AUTH) === null)
		{
			$this->oAuth->SetSessionVar(self::IP_REQUEST_AUTH, $ipAuth);
			$this->oAuth->SetSessionVar(self::COUNT_REQUEST_AUTH, 0);
			$this->oAuth->SetSessionVar(self::LAST_TIME_REQUEST_AUTH, time());
		}
	}

	/**
	 * Алгоритм определения показа каптчи при авторизации.
	 *
	 * @params string $ipAuth
	 * @throws StatusErrorException
	 */
	private function showCaptchaForAuth($ipAuth)
	{
		$period = time() - $this->oAuth->GetSessionVar(self::LAST_TIME_REQUEST_AUTH);
		$countRequestLogin = $this->oAuth->GetSessionVar(self::COUNT_REQUEST_AUTH);
		if(($countRequestLogin >= 3 && $period < 300) && $this->oAuth->GetSessionVar(self::KEY_CAPTCHA_SESSION_LOGIN) === null)
			throw new StatusErrorException('Captcha required!', $this->status->captcha_required);
		else
			$this->initSessionParametersForAuth($ipAuth);

		if($this->oAuth->GetSessionVar(self::KEY_CAPTCHA_SESSION_LOGIN) !== null)
		{
			$captcha = $this->GetVar('captcha');
			if($captcha != $this->oAuth->GetSessionVar(self::KEY_CAPTCHA_SESSION_LOGIN))
				throw new StatusErrorException('Captcha not matches!', $this->status->captcha_not_matches);
		}
	}

	/**
	 * Действие, управляющее обработкой параметров от "клиента" для части сценария восстановления пароля пользователя.
	 * Включает в себя только обработку параметров и отсылку письма уведомления на email пользователя.
	 *
	 * @throws E1Exception|StatusErrorException
	 * @return void
	 */
	public function actionPasswordRecovery()
	{
		try
		{
			$this->detectBlockAtIPActionPasswordRecovery();

			$userEmail = $this->GetVar('login');
			$captcha = $this->GetVar('captcha');

			if(!$userEmail) throw new StatusErrorException('Login field empty!', $this->status->login_empty);

			if(!$captcha) throw new StatusErrorException('Captcha required!', $this->status->captcha_required);
			if($captcha != $this->oAuth->GetSessionVar(self::KEY_CAPTCHA_SESSION_RECOVERY))
				throw new StatusErrorException('Captcha not matches!', $this->status->captcha_not_matches);

			// Определение статуса пользователя в системе
			$concreteUser = user_Mapper::findUserByEmail($userEmail);

			$concreteUser->detectAccessInSystem();

			$this->detectBlockedAtEmailActionPasswordRecovery($concreteUser);
			$concreteUser->createRecoveryCode();

            //Получаем шаблон уведомления зависящий от языка пользователя
            $mail = mail_Template::model()->makeMailWithLinkCode($concreteUser);
			$body = $mail['body'];
            $subject = $mail['subject'];

			ob_start();
			$emailDriver = new EmailDriver();
			$success = $emailDriver->send($body, $subject, $concreteUser->email);
			ob_clean();

			if($success === false && $this->oConfig->system['debug'] === false)
				throw new StatusErrorException($emailDriver->error, $this->status->main_errors);

			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action recovery validate: ');
		}
		catch(Exception $e)
		{
			e1('Action `recovery` error: ', $e->getMessage());
		}
	}

	/**
	 * @throws StatusErrorException
	 */
	private function detectBlockAtIPActionPasswordRecovery()
	{
		// Проверка блокировки пользователя
		$timeBlockedRecovery = $this->oAuth->GetSessionVar(self::TIME_BLOCKED_RECOVERY);
		if($timeBlockedRecovery !== null && (time() - $timeBlockedRecovery) < 240)
			throw new StatusErrorException('Your blocked at 3 minutes', $this->status->blocked);

		if($this->oAuth->GetSessionVar(self::COUNT_REQUEST_RECOVERY) === null)
			$this->initSessionParametersForRecovery($_SESSION['REMOTE_ADDR']);

		// Если с одного ip-адреса, отправлено за 5 минут более 3 запросов,
		// то с данного ip восстановление в течение 3 минут не доступно.
		$period = time() - $this->oAuth->GetSessionVar(self::LAST_TIME_REQUEST_RECOVERY);
		if($period < 300)
		{
			if($this->oAuth->GetSessionVar(self::COUNT_REQUEST_RECOVERY) >= 3)
			{
				$this->oAuth->SetSessionVar(self::TIME_BLOCKED_RECOVERY, time());
				$this->oAuth->SetSessionVar(self::COUNT_REQUEST_RECOVERY, 1);
			}
			else
			{
				$this->oAuth->SetSessionVar(
					self::COUNT_REQUEST_RECOVERY,
					$this->oAuth->GetSessionVar(self::COUNT_REQUEST_RECOVERY) + 1
				);
			}
		}
		else
			$this->oAuth->SetSessionVar(self::LAST_TIME_REQUEST_RECOVERY, time());
	}

	/**
	 * @param user_Mapper $user
	 * @throws StatusErrorException
	 */
	private function detectBlockedAtEmailActionPasswordRecovery(user_Mapper $user)
	{
		// На один e-mail нельзя отправить более 3 запросов на восстановление пароля в течении 24 часов.
		if($user->last_time_recovery !== null && (time() - $user->last_time_recovery) < 86400 && $user->count_recovery >= 3)
			throw new StatusErrorException('Please try again when 24 hours', $this->status->blocked);
	}

	/**
	 * Инициализация первоначальных данных в сессии для отслеживания частоты запросов.
	 *
	 * @params string $ipRecovery
	 */
	private function initSessionParametersForRecovery($ipRecovery)
	{
		$this->oAuth->SetSessionVar(self::IP_REQUEST_RECOVERY, $ipRecovery);
		$this->oAuth->SetSessionVar(self::COUNT_REQUEST_RECOVERY, 1);
		$this->oAuth->SetSessionVar(self::LAST_TIME_REQUEST_RECOVERY, time());
	}

	/**
	 * Действие принимает подтверждение восстановления пароля по ссылке с кодом восстановления.
	 *
	 * @throws StatusErrorException|E1Exception
	 * @return void
	 */
	public function actionConfirmationPasswordRecovery()
	{
		try
		{
			$codeConfirmationFromMailLink = $this->GetVar('code');
			$userEmail = $this->GetVar('login');

			// Определение статуса пользователя в системе
			$concreteUser = user_Mapper::findUserByEmail($userEmail);
			$concreteUser->detectAccessInSystem();

			if((time() - $concreteUser->last_time_recovery) > 259200)
				throw new StatusErrorException('Link recovery password is old!', $this->status->link_old);

			if(!$codeConfirmationFromMailLink || $codeConfirmationFromMailLink != $concreteUser->recovery_code)
				throw new StatusErrorException('Recovery code: ' . $codeConfirmationFromMailLink . ' user: '
					. $concreteUser->email . ' not matches with code DB: ' . $concreteUser->recovery_code, $this->status->code_not_matches);

			$textPassword = $concreteUser->makePassword();

            //Получаем шаблон письма в зависимости от языка пользователя
			$mail = mail_Template::model()->makeMailWithNewPassword($concreteUser, $textPassword);
            $body = $mail['body'];
            $subject = $mail['subject'];

			$emailDriver = new EmailDriver();
			$success = $emailDriver->send($body, $subject, $concreteUser->email);
			if($success === false) throw new StatusErrorException($emailDriver->error, $this->status->main_errors);

			$concreteUser->clearDataRecoveryPassword();
			$concreteUser->save();

			Router::Location('/');
			$this->Viewer_Assign('status', $this->status->successfully);
		}
		catch(JSONResponseErrorException $e)
		{
			$e->sendResponse($this, 'Action recovery validate: ');
		}
		catch(Exception $e)
		{
			e1('Action `recovery` error: ', $e->getMessage());
		}
	}
}
//логин(e-mail), пароль, язык, звук, последний IP, IP регистрации, атрибут ареста(бан)
