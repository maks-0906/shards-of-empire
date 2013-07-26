<?php
/**
 * Файл содержит логику вычисления счастья для города в момент запуска скрипта и записи в город.
 * Алгоритм проходит по всем городам в БД.
 * После подсчета счастья, обновление поля таблицы происходит полностью без сложения или вычитания
 *
 * @author Greg
 * @package cron
 */
//cron/happiness/happiness.php
include_once('../bootstrap.php');

try {
    // 1. Получение списка городов в системе из таблицы personages_cities
    $allCities = personage_City::model()->findAllCity();

    foreach ($allCities as $city) {

        $result = personage_BuildingBonus::model()->findCurrentBonusesForBuildingsProductionHappiness($city->id,
                                                                                                      $city->id_personage);


        $numberHappiness = personage_parameters_Happiness::model()->calculateTotalNumberOfParameterHappiness($result, $city->tax);

        //Обновляем количество счастья для города
        if ($numberHappiness != $city->happiness) {
            personage_parameters_Happiness::model()->updateHappinessCity($numberHappiness, $city->id);
        }
    }

} catch (Exception $e) {
    e1("Upgrade parameter happiness is not satisfied (CRON): ", $e->getMessage());
}

