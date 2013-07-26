<?php

require_once ROOT . '/Application/user/Mapper.php';

/**
 *
 */
class UserTest extends WebTestCase
{
	const KEY_CAPTCHA_SESSION_REGISTER = 'captcha_keystring';

	/**
	 * Фикстуры
	 *
	 * @var array
	 */
	protected $fixtures = array(
		'users' => 'user_Mapper',
	);

	/**
	 *
	 */
	public function testNotFoundLoginOrEmpty()
	{
		$this->open('user/recovery.json');
		$jsonResponse = $this->getResponse();
		$this->assertEquals($jsonResponse->status, $this->status->login_empty, 'Status must be 4 `login empty`');
	}

	/**
	 *
	 */
	public function testShowCaptchaIfLarge10RequestRegisterInMinutes()
	{
		try
		{
			$countRequest = 12;
			for($i = 0; $i < $countRequest; $i++)
			{
				$this->open('user/register.json?login=' . user_Mapper::model()->randGenerator(7) . '@t.ru&password=' . md5('123'));
				sleep(1);
			}
			$jsonResponse = $this->getResponse();
			$this->assertEquals($jsonResponse->status, $this->status->captcha_required, 'Captcha required');
		}
		catch(Exception $e)
		{
			$this->fail($e->getMessage());
		}
	}

	/**
	 *
	 */
	public function testShowCaptchaIfIPAddressExistsAtRegister()
	{
	    try
	    {
			$user = user_Mapper::model()->createNewUser('testtesttest@t.ru', md5(123), '127.0.0.1');
			$this->open('user/register.json?login=testtest@t.ru&password=' . md5('123'));
			$jsonResponse = $this->getResponse();
			$this->assertEquals($jsonResponse->status, $this->status->captcha_required, 'Captcha required');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 * Тестирование нормального хода регистрации без вывода каптчи.
	 */
	public function testNormalUseCaseRegisterWithNotCaptcha()
	{
	    try
	    {
			$this->open('user/register.json?login=testtest@t.ru&password=' . md5('123'));
			$jsonResponse = $this->getResponse();
			$this->assertEquals($jsonResponse->status, $this->status->successfully, 'Статус нормальной регистрации должен быть 1');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testShowCaptchaIfLargeCountBadAuthInRequiredMinutes()
	{
	    try
	    {
			$countBadRequest = 3;
			$periodForBadAuth = 300;

			for($i = 0; $i < $countBadRequest + 1; $i++)
			{
				$this->open('user/login.json?login=user1@t.ru&password=' . md5('123456789'));
				$jsonResponse = $this->getResponse();
				if($i < $countBadRequest)
					$this->assertEquals($jsonResponse->status, $this->status->password_not_matches, 'Пароль не верный');
				else
					$this->assertEquals($jsonResponse->status, $this->status->captcha_required, 'Требуется каптча для авторизации');

				sleep(1);
			}


			$this->open('user/captcha.auth');
			sleep(3);

			$captcha = kcaptcha::getTextCaptchaFromTestFile();

			// :WARNING: Логин и пароль тестовые не удалять
			$this->open('user/login.json?login=user1@t.ru&password=61b125ee490ccc185032c23a52f1ce8d&captcha=' . $captcha);
			$jsonResponse = $this->getResponse();
			$this->assertEquals($jsonResponse->status, $this->status->successfully, 'Авторизация прошла успешно');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testBlockedAtIPAddress()
	{
	    try
	    {
			$emails = array('user1@t.ru', 'user2@t.ru', 'user3@t.ru', 'user4@t.ru', 'user5@t.ru', 'user5@t.ru', 'user5@t.ru');

			$captcha = '';
			foreach($emails as $email)
			{
				$this->open('user/recovery.json?login=' . $email . '&captcha=' . $captcha);
				sleep(2);
				$this->open('user/captcha');
				sleep(2);
				$captcha = kcaptcha::getTextCaptchaFromTestFile();
			}

			$this->open('user/recovery.json?login=' . $email . '&captcha=' . $captcha);
			$jsonResponse = $this->getResponse();
			$this->assertEquals($jsonResponse->status, $this->status->blocked, 'Блокировка должна пройти успешно для IP адресса на три минуты');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}

	/**
	 *
	 */
	public function testBlockedAtEmail()
	{
	    try
	    {
			$this->open('user/captcha');
			sleep(2);
			$captcha = kcaptcha::getTextCaptchaFromTestFile();

			for($i = 0; $i < 6; $i++)
			{
				$this->open('user/recovery.json?login=user1@t.ru&captcha=' . $captcha);
				sleep(2);
				$this->open('user/captcha');
				sleep(2);
				$captcha = kcaptcha::getTextCaptchaFromTestFile();

				if($i === 3) sleep(240);
			}

			$this->open('user/recovery.json?login=user1@t.ru&captcha=' . $captcha);
			$jsonResponse = $this->getResponse();
			$this->assertEquals($jsonResponse->status, $this->status->blocked, 'Блокировка должна пройти успешно на 24 часа по email');
	    }
	    catch(Exception $e)
	    {
	        $this->fail($e->getMessage());
	    }
	}
}
