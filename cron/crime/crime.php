<?php
/**
 * Файл подсчитывает уровень преступности в текущем городе
 */

//cron/crime/crime.php
include_once('../bootstrap.php');
try {
    // 1. Получение списка городов в системе из таблицы personages_cities
    $allCities = personage_City::model()->findAllCity();

    foreach ($allCities as $city) {

        //Получаем бонусы здания ТАВЕРНА
        $bonusBuilding = personage_BuildingBonus::model()->findCurrentBonusesForSpecificBuilding(building_Mapper::KEY_BUILDING_TAVERN,
                                                                                                 $city->id);
        $bonusBuildingTavern = unserialize($bonusBuilding->current_data_bonus);

        //Получаем количество выпитого бочек пива
        $numberBarrelsBeer = $bonusBuildingTavern['number_barrels_beer']['basic'];

        //Получаем уровень преступности от количества выпитого бочек пива
        $crimeFromBarrelsBeer = resource_resources_Beer::model()->calculationCrimeAndHappinessInConsumptionOfBeer($numberBarrelsBeer);

        if ($city->crime != $crimeFromBarrelsBeer['crime']) {

            $doneUpdateCrime = personage_parameters_Crime::model()->updateValueCrimeCity($crimeFromBarrelsBeer['crime'], $city->id);

            if ($doneUpdateCrime === false) {
                e1('Not added crime: ', $city->id);
            }
        }
    }
} catch (Exception $e) {
    e1("Upgrade (CRIME) is not satisfied (CRON): ", $e->getMessage());
}

unset($allCities, $city, $bonusBuilding, $bonusBuildingTavern, $crimeFromBarrelsBeer, $doneUpdateCrime);