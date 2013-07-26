<?php
include_once($_SERVER['DOCUMENT_ROOT']. '/Library/components/EmailDriver.php');
include_once($_SERVER['DOCUMENT_ROOT']. '/Library/PHPMailer/PHPMailer.php');

class EmailDriverCron extends EmailDriver
{
    	public function __construct($host = false, $user_name = false, $password = false, $port = false)
    	{
    		$this->mail = new PHPMailer();

    		$this->mail->IsSMTP();
    		$this->mail->SMTPAuth = true;
            $this->mail->SMTPDebug = 2;
    		$this->mail->Port = $port;
    		$this->mail->Host = $host;
    		$this->mail->Username = $user_name;
    		$this->mail->Password = $password;

            //$this->mail->IsSendmail();
    		//$this->mail->AddReplyTo("name@domain.com","First Last");
    	}


}
