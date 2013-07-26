<?php

/**
 * Description of Action
 *
 * @author al
 */
class admin_Action extends AdminAction {

	/**
	 *
	 */
	public function RegisterEvent()
	{
		$this->AddEvent('auth', 'DoAuth');
		$this->AddEvent('login', 'login');
		$this->AddEvent('logout', 'logout', 1);
		$this->SetDefaultEvent('login');
		$this->SetTemplateName('login');
	}

	/**
	 *
	 */
	public function login()
	{
		if($this->oAuth->isLogin())
		{
			$view = Viewer::getInstance();
			$view->setSkinName('admin');
			$view->Draw('index');
			die;
			//return $this->callback();
		}
		$this->Viewer_Assign('callback', $_SERVER["REQUEST_URI"]);
	}

	/**
	 *
	 */
	public function logout()
	{
		$this->oAuth->Logout();
		return $this->callback();
	}

	/**
	 *
	 */
	public function DoAuth()
	{
		//check
		$email = $this->GetVar('email');
		$password = md5($this->GetVar('password'));
		$mAdmin = new admin_Mapper;
		if(!$mAdmin->CheckAdmin($email, $password))
		{
			$this->Viewer_Assign('error', true);
			return;
		}
		//do

		$this->oAuth->Login();
		return $this->callback();
	}

	/**
	 *
	 */
	private function callback()
	{
		//XSS check
		$aLocation = array_filter(parse_url($this->GetVar('callback')));
		$sLocation = array_key_exists('path', $aLocation) ? $aLocation['path'] : '/admin';
		Router::Location($sLocation);
		die();
	}
}
