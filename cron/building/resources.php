<?php
/**
 * Файл содержит програмную логику выработку ресурсов для ресурсных зданий.
 * Аналогичная система подсчетов существует в классе personage_ResourceState::calculateIncomeResource
 *
 * @author Greg
 * @package cron
 */

include_once('../bootstrap.php');

/**
Основные ресурсы
Еда	Создание, содержание юнитов.
Дерево	Создание юнитов, строительство
Камень	Строительство
Железо	Создание юнитов, строительство
Численность населения	Создание юнитов
 *
Специальные ресурсы
Ткань		Создание юнитов, одежд лорда.
Пиво	Влияет на уровень счастья
Вино	Для проведения турниров
Свечи	Влияют на уровень счастья для населения в святилище - церкви
Обереги, амулеты, украшения	Улучшение качеств лидера, подарки.
 */

/**
 * 1. Каменоломня Производство камня.
 * 2. Кузница Выплавка железа.
 * 3. Лесопилка Производство древесины. Несколько уровней развития.
 * 4. Святилище  - Священная роща.Создание религиозных юнитов. Производство благославления и очков веры. ???
 * 5. Казарма Создание пеших военных юнитов.
 * 6. Конюшня Создание конных юнитов. Юнитов перевозчиков.
 * 7. Винодельня Производство вина для турниров.
 * 8. Ферма Производство пищи.
 * 9. Пивоварня Производство пива.
 * 10. Пасека Производство воска - свечи.
 */

/**
 * !!!! Стоит ли здесь проводить этот алгоритм !!!!
 * Работоспособность ресурсного здания зависит от наличия требуемого кол-ва рабочих.
 * В случае дефицита рабочей силы, первыми закрываются здания производящие спец ресурсы, затем основные здания.
 * Первыми закрываются здания из каждой категории, где необходимо большое кол-во рабочих.
 */

/*
 * На производительность всех ресурсодобывающих зданий влияет счастье населения.
 * При уровне счастье 100, производительность здания 100% + изученные бонусы.
 * При счастье населения 50 берётся только 50% от производительности здания с учётом изученных технологий.
 */


// 1a. @TODO: Когда будет таблица с нанятыми спец юнитами рыцарями выбирать постройи из таб. units_knights_performance_buildings,
// которые входят в число бонусов по производительности (ключами будут названия зданий для которых предусмотрен бонус)


// 1. Получение списка городов в системе из таблицы personages_cities
$AllCities = personage_City::model()->findAllCity();
$religionPersonage = personage_Religion::model()->findReligionPersonage($AllCities[0]->id_personage);
$doneFractionsVisigoths = personage_Fraction::model()->isFractionsVisigoths($AllCities[0]->id_personage);

if (empty($AllCities)) {
    exit();
}

$classifier = building_Mapper::RESOURCE_CLASSIFIER;
foreach ($AllCities as $city) {
    $cityResourcesBuilding = personage_Building::model()->findBuildingInCityByClassifier($classifier, $city->id);

    $sumResourceValue = 0;
    $numberProduction = 0;
    $productionTime = 0;
    $resourceValue = 0;
    $capacityBuildingWarehouse = 0;

    $bonus = array();
    $bonusBuildingWarehouse = array();

    foreach ($cityResourcesBuilding as $building) {
        if ($building->current_level > 0) {
            $bonus = unserialize($building->current_data_bonus);

            if (isset($bonus) && !empty($bonus)) {
                $productionTime = $bonus ['bonus_time_production']['basic'];
                $numberProduction = $bonus ['bonus_number_products']['basic'];
            } else {

                //Присваиваем значения базовых бонусов
                $bonus = unserialize($building->data_bonus);
                $productionTime = $bonus ['bonus_time_production']['basic'];
                $numberProduction = $bonus ['bonus_number_products']['basic'];
            }

            //Получаем данные про вместимость склада
            $bonusBuildingWarehouse = unserialize($building->bonus_building_warehouse);

            if (isset($bonusBuildingWarehouse) AND $building->capacity_building_warehouse) {
                $capacityBuildingWarehouse = $bonusBuildingWarehouse['bonus_capacity']['basic'] + $building->capacity_building_warehouse;
            }

            //Получаем подсчитанное количество ресурсов в зависимости от религии персонажа
            $numberResourcesBonusesReligion = personage_Religion::model()->calculateNumberResourcesOfReligion(
                $numberProduction,
                $religionPersonage->name,
                $building->name_resource);

            //Добавляем бонусы от религии к выработке ресурсов
            if ($numberResourcesBonusesReligion != NULL) {
                $numberProduction = $numberProduction + $numberResourcesBonusesReligion;
            }

            //Добавляем бонусы от фракции если персонаж состоит во фракции "Вестготы"
            if ($doneFractionsVisigoths === true) {
                $numberResourcesBonusesFractionsVisigoths = personage_Fraction::model()->calculateBonusesForResourcesFractionsVisigoths($numberProduction);
                $numberProduction = $numberProduction + $numberResourcesBonusesFractionsVisigoths;
            }

            $sumResourceValue = personage_ResourceState::model()->calculateProductionResources($building->number_products,
                $building->last_visit,
                $productionTime,
                $numberProduction);

            //Получаем значение ресурсов в зависимости от счастья населения
            if (personage_parameters_Happiness::MAX_HAPPINESS > $building->happiness) {
                $resourceValue = personage_ResourceState::model()->calculateProductionResourcesInHappiness($sumResourceValue,
                    $building->happiness);
            } else {
                $resourceValue = $sumResourceValue;
            }

            $numberResource = $building->personage_resource_value + $resourceValue;

            // Обновляем значение производительности здания
            personage_Building::model()->upgradeBuildingPerformance($building->id_building_personage, $resourceValue);

            //Определяем достаточна ли вместимость здания "СКЛАД"
            if ($capacityBuildingWarehouse > $numberResource) {
               $doneUpgradeResource = personage_ResourceState::model()->upgradeResourceValue($city->id, $building->resource_id, $resourceValue, true);
            }
        }
    }
}


//Разрушаем переменные
unset($doneUpgradeResource, $resourceValue, $productionTime, $numberProduction, $capacityBuildingWarehouse, $sum);
unset($bonus, $bonusBuildingWarehouse, $sumResourceValue, $numberProductsAndBonusesReligion, $religionPersonage, $doneFractionsVisigoths);
unset($numberResourcesBonusesFractionsVisigoths);
// WHILE Пока весь список городов не пройден (не обработан)
// 2. Получить список ресурсных строений для текущего города из таблицы personages_buildings с сортировкой

// 3. Определение дефицита рабочей силы: подсчитать общее требуемое число персонала для всех ресурсных зданий в городе
// и сравнить с количеством свободного населения
// IF дефицит рабочей силы: отфильтровать здания из общего списка по условиям --
/**
 * @TODO: Возможно использовать функцию отдельно для подсчёта дефицита рабочей силы
 * Первыми закрываются здания производящие спец ресурсы, затем основные здания.
 * Первыми закрываются здания из каждой категории, где необходимо большое кол-во рабочих.
 */
// END IF

// WHILE Пока весь список зданий не пройден (не обработан)
// 4. Получить от фабрики стратегий по выработке ресурсов стратегию соответствующую
// текущему ресурсному зданию по имени здания

// 5. Получить подсчёт выработанных ресурсов от стратегии для текущего здания

// 6. Записать изменённый ресурс для здания обратно в таблицу.
// END
// END
