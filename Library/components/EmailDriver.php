<?php
/**
 * Description content file
 *
 * @author Greg
 * @package components
 */

/**
 * Драйвер "Gateway" к почтовым серверам.
 * Нас
 *
 * @property PHPMailer $mail
 * @author Greg
 * @version 1.0.0
 * @package components
 */
class EmailDriver {

	/**
	 * Расширение - шлюз к почтовым серверам для отправки писем.
	 * @var null|PHPMailer
	 */
	protected  $mail = null;

	/**
	 * Message error.
	 *
	 * @var string
	 */
	public $error = '';

	/**
	 * @var bool
	 */
	private $debug = true;

	/**
	 * Initialize driver for send mail.
	 */
	public function __construct()
	{
		/* @var $config Config */
		$config = Config::getInstance();
		$this->mail = new PHPMailer($config->oConfig->system['debug']);

		$this->mail->IsSMTP();
		$this->mail->SMTPAuth = true;
		$this->mail->Port = 25;
		$this->mail->Host = $config->oConfig->system['mail_host'];
		$this->mail->Username = $config->oConfig->system['mail_username'];
		$this->mail->Password = $config->oConfig->system['mail_password'];

		$this->mail->IsSendmail();
		//$this->mail->AddReplyTo("name@domain.com","First Last");
	}

	/**
	 * Send mail.
	 *
	 * @param string $body
	 * @param string $subject
	 * @param string $to
	 * @param string $from
	 * @param int $wordWrap
	 * @return bool
	 */
	public function send($body, $subject, $to, $from = '', $wordWrap = 80)
	{
        //Очищяем предыдущие адреса
        $this->mail->ClearAddresses();

        $this->mail->CharSet = 'utf-8';
		$this->mail->From = ($from !== '') ? $from : "name@domain.com";
		$this->mail->FromName = "First Last";
		$this->mail->AddAddress($to);
		$this->mail->Subject  = $subject;
		$this->mail->WordWrap = $wordWrap;
		$this->mail->MsgHTML(preg_replace('/\\\\/','', $body));

		// send as HTML
		$this->mail->IsHTML(true);
		$this->mail->Send();

		if($this->mail->isError() === true)
		{
			$this->error = $this->mail->ErrorInfo;
			return false;
		}
		else
			return true;
	}
}
