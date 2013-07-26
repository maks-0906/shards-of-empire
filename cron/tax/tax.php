<?php
/**
 * Файл содержит програмную логику получения ресурса персонажа ЗОЛОТО.
 * Золото насчитывается из налоговой ставки
 *
 * Налоговая ставка – это кол-во единиц серебра, которое можно собрать с населения в час.
 * Базовая величина налоговой ставки 10 ед. серебра с человека.
 *
 * @author Greg
 * @package cron
 */

//cron/tax/tax.php
include_once('../bootstrap.php');
try {
    $seconds = models_Time::model()->getCountNumberOfSecondsInMinute(personage_parameters_Tax::LAST_COLLECTION_TAXES);

    // 1. Получение списка городов в системе из таблицы personages_cities
    $allCities = personage_City::model()->findAllCity();

    foreach ($allCities as $city) {

        //Определяем подошло ли время для сбора налогов с населения
        if (($city->unix_last_collection_taxes + $seconds) <= time()) {


            if ($city->tax >= personage_parameters_Tax::MAX_TAX) {
                $city->tax = personage_parameters_Tax::MAX_TAX;
            }

            if ($city->tax <= personage_parameters_Tax::MIN_TAX) {
                $city->tax = personage_parameters_Tax::MIN_TAX;
            }

            $numberResourcesGold = personage_parameters_Tax::model()->formulaObtainingResourcesGoldFromPopulation($city->population,
                                                                                                                  $city->tax);

            //Добавляем полученное количество ресурса
            if ($numberResourcesGold > personage_parameters_Tax::MIN_TAX) {
                $doneUpdateResourcesGold = personage_ResourceState::model()->upgradePersonageResourceValueIncrease($city->id_personage,
                                                                                                              resource_Mapper::GOLD_ID,
                                                                                                                  $numberResourcesGold);

                if ($doneUpdateResourcesGold === true) {
                    $sqlPart = ' last_collection_taxes = NOW()';
                    personage_City::model()->updateFieldCity($sqlPart, $city->id);
                }
            }
        }
    }
} catch (Exception $e) {
    e1("Upgrade resources SILVER is not satisfied (CRON): ", $e->getMessage());
}

unset($allCities, $city, $seconds, $numberResourcesGold, $doneUpdateResourcesGold);