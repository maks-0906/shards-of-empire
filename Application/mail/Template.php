<?php
/**
 * Класс формирует и вставляет данные в шаблон для внутренней почты и отправки на E-mail
 */

class mail_Template
{

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return mail_Template
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    /**
     * Создание письма результата боя
     *
     * @param $resultFight
     * @return array
     */
    public function makeMailFightResult($resultFight)
    {

        $body = json_encode($resultFight);
        $subject = 'result_fight';

        return array('body' => $body, 'subject' => $subject);
    }

    /**
     * Формирование письма для предупреждения пользователя об намерении нападения на его локацию
     *
     * @param $nick
     * @return array
     */
    public function makeMailFightNotificationOfAttack($nick)
    {
        /* @var $view Viewer */
        $view = Viewer::getInstance();
        $view->setSkinName('default');

        $view->Assign('nick', $nick);

        $body = $view->Draw('mail/' . Auth::GetStoreLang() . '/fight/notification_of_attack_body', true);

        $subject = $view->Draw('mail/' . Auth::GetStoreLang() . '/fight/notification_of_attack_subject', true);

        return array('body' => $body, 'subject' => $subject);
    }


    /**
     * Формируем письмо об распуске юнитов связи с нехваткой еды
     *
     * @param $lang
     * @return array
     */
    public function makeMailUnitsDisband($lang)
    {
        /* @var $view Viewer */
        $view = Viewer::getInstance();
        $view->setSkinName('default');

        $body = $view->Draw('mail/' . $lang . '/units/disband_body', true);
        $subject = $view->Draw('mail/' . $lang . '/units/disband_subject', true);

        return array('body' => $body, 'subject' => $subject);
    }

    /**
     * Создание письма с сылкой подтверждения операции восстановления.
     *
     * @param user_Mapper $user
     * @return string
     */
    public function makeMailWithLinkCode(user_Mapper $user)
    {
        /* @var $view Viewer */
        $view = Viewer::getInstance();
        $view->setSkinName('default');
        $view->Assign('host', $_SERVER['HTTP_HOST']);
        $view->Assign('code', $user->recovery_code);
        $view->Assign('email', $user->email);
        $body = $view->Draw('mail/' . Auth::GetStoreLang() . '/auth/mail_recovery_password_body', true);
        $subject = $view->Draw('mail/' . Auth::GetStoreLang() . '/auth/mail_recovery_password_subject', true);

        return array('body' => $body, 'subject' => $subject);
    }

    /**
     * Создание письма новыми данными для входа
     *
     * @param user_Mapper $user
     * @param string $textPassword
     * @return array
     */
    public function makeMailWithNewPassword(user_Mapper $user, $textPassword)
    {
        /* @var $view Viewer */
        $view = Viewer::getInstance();
        $view->setSkinName('default');
        $view->Assign('login', $user->email);
        $view->Assign('newPassword', $textPassword);
        $body = $view->Draw('mail/' . Auth::GetStoreLang() . '/auth/mail_new_password_body', true);
        $subject = $view->Draw('mail/' . Auth::GetStoreLang() . '/auth/mail_new_password_subject', true);

        return array('body' => $body, 'subject' => $subject);
    }
}