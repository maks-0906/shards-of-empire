<?php

/* 2013-03-29  vetalrakitin  <vetalrakitin@gmail.com>
 *
 * Приведение файла к единому API
 */

/*
 * Файл получает  email - адреса пользователей, которые отсутствуют определенное количество времени в системе,
 * и отправляет им на электронную почту сообщение.
 */

include_once('../bootstrap.php');
include_once('../EmailDriverCron.php');

//cron/not_attend_game/not_attend_game.php

// Подключаем конфигурацию
$config = Config::getInstance();

// Получаем настройки для почтового ящика
$mail_host = $config->system['mail_host'];
$mail_username = $config->system['mail_username'];
$mail_password = $config->system['mail_password'];
$smtp_port = $config->system['smtp_port'];

// Получаем тему из настроек
$subject = $config->cron['not_attend_game']['subject'];

// Получаем количество дней с момента последнего появления
$user_last_online = $config->cron['not_attend_game']['user_last_online'];

//Получаем метку времени для заданной даты
$tmpdate = strtotime(-$user_last_online . "day");
$date = date("Y-m-d H:i:s", $tmpdate);

$sql = "SELECT `email`, `lang` FROM `%s` WHERE `last_online` <= TIMESTAMP('%s')";
$emails = user_Mapper::model()->findAll($sql, user_Mapper::TABLE_NAME, $date);

if (!empty($emails)) {

	$oEmail = new EmailDriverCron($mail_host, $mail_username, $mail_password, $smtp_port);

    //TODO:Шаблоны писем в зависимости от языка пользователя необходимо разместить в файле
    //TODO:(Templates/skin/default/mail/en/auth/message_not_attend_game_body.html и Templates/skin/default/mail/en/auth/message_not_attend_game_subject.html
    $body = file_get_contents('../../cron/not_attend_game/message_not_attend_game.html');

	foreach ($emails as $row) {
		$oEmail->send($body, $subject, $row->email, $mail_username);
	}
}
?>
