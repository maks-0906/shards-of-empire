<?php
/**
 * Description content file
 *
 * @author Greg
 * @package cron
 */

include_once('../bootstrap.php');

// Псевдокод алгоритма выработки населения

/**
Отток и приток населения происходит каждый час.
В зависимости от показателя счастья идёт расчёт изменения численности  населения до максимально возможного показателя данного уровня, а так же производительность предприятий.
Например:
1) Базовый показатель роста населения 200 чел в час.
Показатель счастья 25. Итого рост населения 50.
2) Базовый показатель роста ресурса 250 ед. в час. Счастье населения 95. Итого рост: 238.
Формула: кол-во населения = кол-во населения + (базовый показатель роста * (счастье/100) ).
Расчёт по данной формуле происходит вместе с расчётом произведённых ресурсов.
Если расчёт раз в 5 минут, то величина базового прироста делится на 12.
 */


// 1. Получение общего счастья города
// 1.1 Выборка города с текущими данными, включая счастье из таблицы personages_cities
try
{
	$cities = personage_City::model()->findAllCity();

	if (!empty($cities)) {
	
	    foreach ($cities as $fieldCity) {
	       /*$city = personage_Building::model()->findImprovedBuildings($fieldCity->id);
			
			// Показатели по городу
	        $cityGrowth = 0; // Прирост
	        $cityPopulation = 0; // Популяция
	        $cityCapacity = 0; // Емкость
	
	        foreach ($city as $buildingHouse) {
	            if ($buildingHouse->current_level > 0) {
	                $bonus = unserialize($buildingHouse->current_data_bonus);
	
	                if (empty($bonus)) {
	                    $bonus = unserialize($buildingHouse->data_bonus);
	                }
	
	                $growthBonus = $bonus['bonus_population_growth']['basic'];
	                $capacityBonus = $bonus['bonus_capacity']['basic'];
	                
	                // Прирост в текущем доме инкрементируем с приростом по городу
	                $cityGrowth += $growthBonus / 20; 
	                
	                // Емкость текущего дома инкрементируем с емкостью города
	                $cityCapacity += $capacityBonus; 
	            }
	        }
	        
	        // Учитываем счастье населения на прирост
	        $cityGrowth = $cityGrowth * $fieldCity->happiness / 100;
	        
	        // Округляем прирост по городу в большую сторону
	        $cityGrowth = ceil($cityGrowth);
	        
	        // Если прирост + текущая популяция > емкости города
	        if ($cityGrowth + $fieldCity->population > $cityCapacity)
	        {
				// Популяция города равна емкости города
				$cityPopulation = $cityCapacity;
				// а прирост равен новое значение популяции - текущая популяция
				$cityGrowth = $cityPopulation - $fieldCity->population;
			}
			else
				// иначе популяция = прирост + текущая популяция
				$cityPopulation = $cityGrowth + $fieldCity->population;

	        $formed_sql_part = '`growth` = '. $cityGrowth . ', `population` = '. $cityPopulation;
	        $donePopulation = personage_City::model()->updateFieldCity($formed_sql_part, $fieldCity->id);*/

	        personage_City::model()->updatePopulationInCity($fieldCity->id);
	        
	        personage_City::model()->updateFreePeopleInCity($fieldCity->id);
	    }
	
	    unset($cities, $formed_sql_part, $cityGrowth, $buildingHouse, $capacityBonus, $growthBonus, $cityPopulation, $fieldCity, $workingPopulation);
	
	}
}
catch (Exception $e)
{
	e1("Process maintenance fee (CRON): ", $e->getMessage());
}


// 2. Получение максимального количества населения в городе.
// 2.1 Поиск количества домов для города и подсчёт максимального числа жителей для города.
// Выборка производится из personages_buildings для города количество домов и их текущие уровни

// 3. Получение базового показателя прироста населения
// !!! УЧИТЫВАТЬ (Если расчёт раз в 5 минут, то величина базового прироста делится на 12.)

// 3.1 Подсчёт коэффициента для базового прироста от количества запусков по крону в минуту. Не ясно как это получить в скрипте???

// 3.2 По построенным улучшениям домов требуется получить прирост населения из таблицы building_upgrade

// 3.3 Выборка бонусов для найденных домов города и получение бонуса прироста населения
// из таблицы personages_buildings_bonus_state

// 4. Подсчёт процента болезней в городе и желательно с учётом вакцины.

// 5. Подсчёт приращения населения по формуле

// 6. Подсчёт притока и оттока в городе (взависимости от параметров из таблицы personages_cities)

// 7. Сохранение популяции, оттока и притока в городе.


