<?php
/*
 * Файл содержит логику по обновлению данных (привилегий) связанных с титулами.
 * Согласно ТЗ к привилегии относится (очки улучшения личных характеристик, благословление и серебро), которые добавляются
 * персонажу раз в 24 часа.
 * Привилегия (СЧАСТЬЕ НАСЕЛЕНИЯ) реализована в файле (/cron/happiness/happiness.php)
 */
include_once('../bootstrap.php');

//TODO: Количество часов взять из конфигурации или настройках
$hour = 24;
$seconds = $hour * 3600;
$currentTimestamp = time();

$numberBlessing = 0;
$countBlessing = 0;
$countSilver = 0;

//cron/privilege/privilege.php
// 1. Получение списка городов в системе из таблицы personages_cities
$AllPersonageDignity = personage_parameters_Dignity::model()->findDignityOnPersonageState();

if (empty($AllPersonageDignity)) {
    exit();
}

foreach ($AllPersonageDignity->result as $dignity) {

    $secondPrivilegeLastVisit = strtotime($dignity['privilege_last_visit']) + $seconds;
    $countBlessing = $dignity['blessing_dignity'];
    $countSilver = $dignity['additional_income_silver'];

   if ($currentTimestamp >= $secondPrivilegeLastVisit) {

        //Обновляем ресурс серебро
        $doneResourceSilver = personage_ResourceState::model()->upgradePersonageResourceValueIncrease($dignity['id_personage'],
                                                                                                      resource_Mapper::GOLD_ID,
                                                                                                      $countSilver);

        //Обновляем ресурс благословление
        $doneResourceBlessing = personage_ResourceState::model()->upgradePersonageResourceValueIncrease($dignity['id_personage'],
                                                                                                        resource_Mapper::BLESSING_ID,
                                                                                                        $countBlessing);

        if ($doneResourceSilver == NULL) {
            e1('Not added privilege silver resource: ', $dignity['id_personage']);
        }

        if ($doneResourceBlessing == NULL) {
            e1('Not added privilege blessing resource : ', $dignity['id_personage']);
        }

        $donePrivilegeLastVisit = personage_State::model()->updateFieldPrivilegeLastVisit($dignity['id_personage']);

        if ($donePrivilegeLastVisit === false) {
            e1('Do not update the field (privilege_last_visit): ', $dignity['id_personage']);
        }
   }
}
unset($hour, $seconds, $currentTimestamp, $AllPersonageDignity, $dignity, $secondPrivilegeLastVisit);
unset($countBlessing, $countSilver,  $doneResourceSilver, $doneResourceBlessing, $donePrivilegeLastVisit);
?>