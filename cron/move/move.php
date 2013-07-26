<?php
/**
 * Файл осуществляет окончание передвижения персонажа в другой город.
 * Данный вид окончания передвижения служит для подстраховки, после выполнения этого действия с клиентской части
 * приложения.
 */

//cron/move/move.php
include_once('../bootstrap.php');

try {
    $allFinishPersonageMove[] = personage_parameters_Move::model()->findCoordinatesPersonages(personage_parameters_Move::STATUS_MOVE_PERSONAGE_TRANSIT);

    foreach ($allFinishPersonageMove as $personageState) {
        personage_parameters_Move::model()->finishMovePersonage($personageState, personage_parameters_Move::STATUS_MOVE_PERSONAGE_ARRIVAL);
    }
} catch (Exception $e) {
    e1("Error finished personages movement (CRON): ", $e->getMessage());
}