<?php
/**
 * Файл содержит логику для платы за содержание юнитов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package cron
 */

include_once('../bootstrap.php');

// Количество запусков скрипта за сутки
const COUNT_UPDATE_PER_DAY = 480; // 24 * 20 каждую 3-ю минуту

// Получить персонажей
$personages = personage_Mapper::model()->findAllPersonages();

if (empty($personages)) {
    exit();
}

try {
    foreach ($personages as $personage) {

        // Очистить счетчик расхода ресурсов
        if (!personage_ResourceState::model()->clearResourceConsumption($personage->id))
            throw new ErrorException('Clear resource consumption is failed');

        // Массив состояния ресурса "Еда" по городам
        $resourceStateByCity = array();

        // Общее количество производимого ресурса для персонажа
        $totalPerformance = 0;

        // Все города персонажа
        $personageCities = personage_City::model()->findCitiesForPersonage($personage->id);
        //print_r($personageCities);
        foreach ($personageCities as $city) {
            // Получить производительность ферм в городе
            $performanceFarms = personage_Building::model()->findPerformanceAllWorkingFarmInCity($personage->id, $city->id);

            // Заносим в массив
            $resourceStateByCity[$city->id]["value"] = $performanceFarms;

            // Инкрементируем количество производимого ресурса для персонажа
            $totalPerformance += $performanceFarms;
        }

        // Пересчитываем процент производительности по городам
        foreach ($resourceStateByCity as $cityId => $prop) {
            $resourceStateByCity[$cityId]["percent"] = $totalPerformance == 0 ? 1 : $prop["value"] / $totalPerformance;
        }

        // Получить юнитов по ИД персонажа отсортированных по Полю Золото и Еда
        $personagesUnits = personage_Unit::model()->findAllPersonagesUnitsToPaySalaryIdPersonageOrderByGoldAndFood($personage->id);
        print_r($personagesUnits);
        // Для каждого юнита персонажа
        foreach ($personagesUnits as $personagesUnit) {
            // Проверяем доступность ресурсов для оплаты жалования юнитам
            $goldResourceState = personage_ResourceState::model()->CurrentResourceState($personage->id, "NULL", resource_Mapper::GOLD_ID);

            // Доступность ресурса "Еда" по всем городам персонажа
            $foodResourceState = personage_ResourceState::model()->CurrentResourceState($personage->id, array_keys($resourceStateByCity), resource_Mapper::FOOD_ID);

            // Массив списываемых средств
            $arSalary = array();

            // Количество единиц списываемого ресурса за 1 итерацию
            $currentPersonagesUnitGold = $personagesUnit->gold / COUNT_UPDATE_PER_DAY;
            $currentPersonagesUnitFood = $personagesUnit->food / COUNT_UPDATE_PER_DAY;

            if (
                $currentPersonagesUnitGold <= $goldResourceState
                && $currentPersonagesUnitFood <= $foodResourceState
            ) {
                // Списуем ресурс "Золото"
                if ($currentPersonagesUnitGold)
                    $arSalary[resource_Mapper::GOLD_ID]["NULL"] = $currentPersonagesUnitGold;

                // Массив списываемого ресурса Еда по городам
                $arUnitFoodSalary = array();

                // пересчитываем массив списываемых средств по городам
                do {
                    foreach ($resourceStateByCity as $cityID => $prop) {
                        // Получаем текущее значение ресурса "Еда" для города $cityID
                        $foodResourceState = personage_ResourceState::model()->CurrentResourceState($personage->id, $cityID, resource_Mapper::FOOD_ID);

                        if ($foodResourceState > 0) {
                            // Количество еды, для списания с текущего города
                            $unitFoodSalaryForThisCity = $currentPersonagesUnitFood * $prop['percent'];

                            // Если достаточно ресурса "Еда" для списания
                            if ($unitFoodSalaryForThisCity <= $foodResourceState) {
                                $arUnitFoodSalary[$cityID] = $unitFoodSalaryForThisCity;
                            } // Если недостаточно, списываем сколько есть
                            else {
                                $arUnitFoodSalary[$cityID] = $foodResourceState;

                                if (count($resourceStateByCity) > 1) {
                                    // Пересчитываем процент для текущего города
                                    $percentAvailable = $prop['percent'] * $foodResourceState / $unitFoodSalaryForThisCity;

                                    $percentNoAvailable = ($resourceStateByCity[$cityID]['percent'] - $percentAvailable) / (count($resourceStateByCity) - 1);
                                    $resourceStateByCity[$cityID]['percent'] = $percentAvailable;

                                    // Пересчитываем массив процентов для остальных городов
                                    foreach ($resourceStateByCity as $_cityID => $propCity) {
                                        if ($_cityID != $cityID)
                                            $resourceStateByCity[$_cityID]['percent'] += $percentNoAvailable;
                                    }
                                }
                            }
                        }
                    }
                    // Пока сумма списываемого ресурса Еда по городам меньше необходимой суммы списания
                } while (
                    array_sum($arUnitFoodSalary) < $currentPersonagesUnitFood
                );

                foreach ($arUnitFoodSalary as $cityId => $foodSalary) {
                    if ($foodSalary)
                        $arSalary[resource_Mapper::FOOD_ID][$cityId] = $foodSalary;
                }

                if (count($arSalary)) {
                    if (!personage_ResourceState::model()->payUnitsSalary($personage->id, $arSalary)) {
                        // Обработать ошибку
                    }
                }
            } else {
                // Расформировываем юнитов
                personage_Unit::model()->disbandUnits($personagesUnit->id_unit_personage, $personage->id);

                $mail = mail_Template::model()->makeMailUnitsDisband($personage->lang);
                $mailAttribute = array('from' => 0,
                    'to' => $personage->id,
                    'subject' => $mail['subject'],
                    'body' => $mail['body']);

                //sendNotice
                mail_Mapper::model()->createNewNotice($mailAttribute);
            }
        }
    }
} catch (Exception $e) {
    e1("Process maintenance fee (CRON): ", $e->getMessage());
}
