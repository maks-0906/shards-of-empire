<?php

//Developer profile
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Крон просматривает все отряды, которые закончили передвиение
 *
 * @author Oleg Goroun
 * @package cron
 */
include_once('../bootstrap.php');

$unitsInfo = array();
$unsortedUnitsInfo = unit_Characteristic::model()->getAllUnitCharacteristic();
foreach ($unsortedUnitsInfo as $unitInfo) {
    $unitsInfo[$unitInfo['unit_id']] = $unitInfo;
    unset($unitsInfo[$unitInfo['unit_id']]['unit_id']);
}

$unitsByHp = sortUnitsByHp($unitsInfo);

// Получаем все битвы, которые должны начаться
$fights = fight_Mapper::model()->getStartedFights();
foreach ($fights as $fight) {
    $battle = array(
        'max_rounds' => 5,
        'attackers' => array(
            'attack' => 0,
            'protection' => 0,
            'life' => 0,
            'start_units' => array(),
            'current_units' => array(),
            'squad_units' => array(),
        ),
        'defenders' => array(
            'is_robber' => false,
            'attack' => 0,
            'protection' => 0,
            'life' => 0,
            'start_units' => array(),
            'current_units' => array(),
            'personage_units' => array()
        ),
        'result' => array()
    );


    // Получаем все отряды атакующей стороны и считаем параметры атакующей стороны
    $squads = unit_UnitsMoving::model()->getWaitingForBattleSquadsInLocation($fight['x'], $fight['y']);
    // Если отрядов нет(с хера ли только?), то переходим к следующему сражению
    if (empty($squads))
        continue;

    foreach ($squads as $squad) {
        $units = unserialize($squad['units']);
        foreach ($units as $unit) {
            $unit = (array) $unit;
            if ($unit['count'] != 0) {
                isset($battle['attackers']['current_units'][$unit['unit_id']]) ? $battle['attackers']['current_units'][$unit['unit_id']] += $unit['count'] : $battle['attackers']['current_units'][$unit['unit_id']] = $unit['count'];
                $battle['attackers']['squad_units'][$squad['id']][$unit['unit_id']] = $unit['count'];
            }
        }
    }

    // Если в обороне - разбойники
    if (!personage_Location::model()->isCity($fight['x'], $fight['y']) && personage_Location::model()->getLocationOwner($fight['x'], $fight['y']) == -1) {
        $units = map_FeatureRobber::model()->findLevelRobberLocations($fight['x'], $fight['y']);
        foreach ($units as $unit) {
            if ($unit['count'] != 0) {
                $battle['defenders']['current_units'][$unit['unit_id']] = $unit['count'];
            }
        }
        $battle['defenders']['is_robber'] = true;
    }
    // Если в обороне - игрок
    else {
        // Получаем все войска, находящиеся в этой локации
        $units = personage_UnitLocation::model()->getUnitsInLocation($fight['x'], $fight['y']);
        foreach ($units as $unit) {
            if ($unit['count'] != 0) {
                isset($battle['defenders']['current_units'][$unit['unit_id']]) ? $battle['defenders']['current_units'][$unit['unit_id']] += $unit['count'] : $battle['defenders']['current_units'][$unit['unit_id']] = $unit['count'];
                $battle['defenders']['personage_units'][$unit['personage_id']][$unit['unit_id']] = $unit['count'];
            }
        }
    }

    $battle['attackers']['start_units'] = $battle['attackers']['current_units'];
    $battle['defenders']['start_units'] = $battle['defenders']['current_units'];

    // Если есть защита, то проводим сражение
    if (!empty($battle['defenders']['start_units'])) {
        // Расчет боя
        $round = 1;
        calcParams(&$battle, $unitsInfo);
        while ($round <= $battle['max_rounds']) {
            $damageToAttackers = $battle['defenders']['attack'] - $battle['attackers']['protection'];
            if ($damageToAttackers < 0)
                $damageToAttackers = 0;

            $damageToDefenders = $battle['attackers']['attack'] - $battle['defenders']['protection'];
            if ($damageToDefenders < 0)
                $damageToDefenders = 0;

            // Получаем потери за раунд
            $attackersLosses = calcLosses($battle['attackers']['current_units'], $damageToAttackers, $unitsByHp);
            foreach ($attackersLosses as $unitId => $count) {
                $battle['attackers']['current_units'][$unitId] -= $count;
            }

            $defendersLosses = calcLosses($battle['defenders']['current_units'], $damageToDefenders, $unitsByHp);
            foreach ($defendersLosses as $unitId => $count) {
                $battle['defenders']['current_units'][$unitId] -= $count;
            }

            calcParams(&$battle, $unitsInfo);

            if ($battle['attackers']['life'] == 0 || $battle['defenders']['life'] == 0)
                break;

            $round++;
        }

        calcParams(&$battle, $unitsInfo);

        // Считаем итог сражения
        $battle['result'] = array(
            'winner' => '',
            'attackers' => array(
                'losses' => array(
                    'total' => array(),
                )
            ),
            'defenders' => array(
                'losses' => array(
                    'total' => array(),
                )
            ),
        );

        // Находим победителя
        if ($battle['attackers']['life'] == 0 && $battle['defenders']['life'] == 0) {
            $battle['result']['winner'] = '-';
        } elseif ($battle['attackers']['life'] == 0) {
            $battle['result']['winner'] = 'defenders';
        } elseif ($battle['defenders']['life'] == 0) {
            $battle['result']['winner'] = 'attackers';
        } elseif ($battle['attackers']['life'] / 20 >= $attackersLife && $battle['defenders']['life'] / 20 >= $defendersLife) {
            $battle['result']['winner'] = '-';
        } elseif ($battle['attackers']['life'] > $battle['result']['winner']) {
            $battle['result']['winner'] = 'attackers';
        } else {
            $battle['result']['winner'] = 'defenders';
        }

        // Считаем общие потери атакующих
        foreach ($battle['attackers']['current_units'] as $unitId => $lossesCount) {
            $battle['result']['attackers']['losses']['total'][$unitId] = $battle['attackers']['start_units'][$unitId] - $lossesCount;
        }

        // Считаем общие потери защитников
        foreach ($battle['defenders']['current_units'] as $unitId => $lossesCount) {
            $battle['result']['defenders']['losses']['total'][$unitId] = $battle['defenders']['start_units'][$unitId] - $lossesCount;
        }

        // Считаем потери по каждому игроку атаки
        $battle['result']['attackers']['rest_units']['by_squads'] = calcRestOfUnitsWithLosses($battle['attackers']['start_units'], $battle['attackers']['squad_units'], $battle['result']['attackers']['losses']['total']);
        // Если в защите не разбойники
        if (!$battle['defenders']['is_robber']) {
            $battle['result']['defenders']['rest_units']['by_personages'] = calcRestOfUnitsWithLosses($battle['defenders']['start_units'], $battle['defenders']['personage_units'], $battle['result']['defenders']['losses']['total']);
        }

        // Обновляем инфу по потерям нападающей стороны
//        updateAttackersUnitsInSquads($battle['result']['attackers']['rest_units']['by_squads']);
//        if (!$battle['defenders']['is_robber']) {
//            updateDefendersUnitsInLocation($fight, $battle['defenders']['personage_units'], $battle['result']['attackers']['rest_units']['by_personages']);
//        }
    }

    
    if ($battle['result']['winner'] == 'attackers') {
        // TODO: При победе нападения - просчет награбленных ресурсов
        //$cityResources = personage_ResourceState::model()->getCityResourcesByCoordinates($fight['x'], $fight['y']);
        $cityResources = personage_ResourceState::model()->getCityResourcesByCoordinates(78, 0);
        if (!empty($cityResources))
        {
            $resources = array(
                'city_resources' => $cityResources,
                'total_resources' => 0,
                'attackers_total_cargo' => 0,
                'attackers_cargo_by_squads' => array()
            );
            
            foreach ($cityResources as $id => $resource) 
            {
                $resources['total_resources'] += $resource['value'];
            }
            
            foreach ($cityResources as $id => $resource) 
            {
                $resources['city_resources'][$id]['percent'] = $resource['value'] / $resources['total_resources'] * 100; 
            }
            
            foreach ($battle['result']['attackers']['rest_units']['by_squads'] as $squadId => $units)
            {
                $resources['attackers_cargo_by_squads'][$squadId] = array(
                    'cargo' => 0,
                    'percent' => 0
                );
                foreach ($units as $unitId => $unitCount)
                {
                    $resources['attackers_total_cargo'] += $unitCount * $unitsInfo[$unitId]['cargo'];
                    $resources['attackers_cargo_by_squads'][$squadId]['cargo'] += $unitCount * $unitsInfo[$unitId]['cargo'];
                }
            }
            
            foreach ($resources['attackers_cargo_by_squads'] as $squadId => $squadCargo)
            {
                $resources['attackers_cargo_by_squads'][$squadId]['percent'] = $squadCargo['cargo'] / $resources['attackers_total_cargo'] * 100;
                if ($resources['attackers_total_cargo'] >= $resources['total_resources'])
                {
                    $resources['attackers_cargo_by_squads'][$squadId]['get_cargo'] = round($resources['total_resources'] / 100 * $resources['attackers_cargo_by_squads'][$squadId]['percent']);
                }    
                else 
                {
                    $resources['attackers_cargo_by_squads'][$squadId]['get_cargo'] = round($resources['total_resources'] / 100 * $resources['attackers_cargo_by_squads'][$squadId]['percent'] * $resources['attackers_total_cargo'] / $resources['total_resources']);
                }
            }
          
            foreach ($resources['city_resources'] as $cityResourceInfo)
            {
                $resources['new_city_resources'][$cityResourceInfo['resource_id']] = $cityResourceInfo['value'];
            }
            
            $resources['squad_resources'] = array();
            foreach ($resources['attackers_cargo_by_squads'] as $squadId => $squadCargoInfo)
            {
                foreach ($resources['city_resources'] as $cityResourceInfo)
                {
                    $resourceCount = round($squadCargoInfo['get_cargo'] * $cityResourceInfo['percent'] / 100);
                    $resources['squad_resources'][$squadId][$cityResourceInfo['resource_id']] = $resourceCount;
                    $resources['new_city_resources'][$cityResourceInfo['resource_id']] -= $resourceCount;
                }
            }

            // Update squads resources
            foreach ($resources['squad_resources'] as $squadId => $squadResources)
            {
                unit_UnitsMoving::model()->updateSquadResources($squadId, serialize($squadResources));
            }
            
            // Update city resources
            foreach ($resources['new_city_resources'] as $resourceId => $newResourceValue)
            {
                personage_ResourceState::model()->setCityResourceByCoordinates(78, 0, $resourceId, $newResourceValue);
                personage_ResourceState::model()->setCityResourceByCoordinates($fight['x'], $fight['y'], $resourceId, $newResourceValue);
            }
            
            $battle['result']['attackers']['squads_resources'] = $resources['squad_resources'];
        }
        
        // Conquere location
        $mainAttackerSquadId = $fight['attacker_squad_id'];
        $initAttackSquad = unit_UnitsMoving::model()->getSquadById($mainAttackerSquadId);
        if ($initAttackSquad['target'] == "attack_tacking")
        {
            personage_Location::model()->setLocationOwner($initAttackSquad['personage_id'], $fight['x'], $fight['y']);
        }
    }

    // Считаем и распределяем славу атакующим отрядам
    $fameToAttackers = 0;
    foreach ($battle['result']['defenders']['losses']['total'] as $unitId => $count) {
        $fameToAttackers += $unitsInfo[$unitId]['fame'] * $count;
    }

    $fameToAttackers = 20;
    if ($fameToAttackers != 0) {
        $totalFame = 0;
        $famePerSquads = array();
        foreach ($battle['attackers']['squad_units'] as $squadId => $units) {
            $famePerSquads[$squadId] = 0;
            foreach ($units as $unitId => $count) {
                $famePerSquads[$squadId] += $unitsInfo[$unitId]['fame'] * $count;
                $totalFame += $unitsInfo[$unitId]['fame'] * $count;
            }
        }

        foreach ($famePerSquads as $squadId => $squadFame) {
            $fameToPersonage = floor($squadFame / $totalFame * $fameToAttackers);
            personage_State::model()->addFameToPersonageBySquadId($squadId, $fameToPersonage);
        }
        
        $battle['result']['attackers']['squads_fame'] = $famePerSquads;
    }
    
    // Считаем и распределяем славу защищающимся персонажам
    $fameToDefenders = 0;
    if (!$battle['defenders']['is_robber']) {
        foreach ($battle['result']['attackers']['losses']['total'] as $unitId => $count) {
            $fameToDefenders += $unitsInfo[$unitId]['fame'] * $count;
        }
    }

    $fameToDefenders = 20;
    if ($fameToDefenders != 0) {
        $totalFame = 0;
        $famePerPersonage = array();
        foreach ($battle['defenders']['personage_units'] as $personageId => $units) {
            $famePerPersonage[$personageId] = 0;
            foreach ($units as $unitId => $count) {
                $famePerPersonage[$personageId] += $unitsInfo[$unitId]['fame'] * $count;
                $totalFame += $unitsInfo[$unitId]['fame'] * $count;
            }
        }

        foreach ($famePerPersonage as $personageId => $personageFame) {
            $fameToPersonage = floor($personageFame / $totalFame * $fameToDefenders);
            personage_State::model()->addFameToPersonage($personageId, $fameToPersonage);
        }
        
        $battle['result']['defenders']['personges_fame'] = $famePerPersonage;
    }


    // Get resources from destroyed units
    $resources = array(
        'stone' => 0,
        'tissue' => 0,
        'beer' => 0
    );

    foreach ($battle['result']['attackers']['losses']['total'] as $unitId => $count) {
        $resources['stone'] += $unitsInfo[$unitId]['stone'] * $count;
        $resources['tissue'] += $unitsInfo[$unitId]['tissue'] * $count;
        $resources['beer'] += $unitsInfo[$unitId]['beer'] * $count;
    }
    foreach ($battle['result']['defenders']['losses']['total'] as $unitId => $count) {
        $resources['stone'] += $unitsInfo[$unitId]['stone'] * $count;
        $resources['tissue'] += $unitsInfo[$unitId]['tissue'] * $count;
        $resources['beer'] += $unitsInfo[$unitId]['beer'] * $count;
    }

    // TODO: Создаем новое поле осколков
    
    
     
    // Отправялем все оставшиеся атакующие отряды назад
    unit_UnitsMoving::model()->returnAllAttackersSquadsBack($fight['x'], $fight['y']);

    // Создаем соообщения о результате сражения игрокам
    $personageIds = array();
    foreach ($squads as $squad) {
        $personageIds[] = $squad['personage_id'];
    }
    foreach ($battle['defenders']['personage_units'] as $personageId => $units) {
        $personageIds[] = $personageId;
    }
    foreach ($personageIds as $personageId) {
        $mailAttribute = array(
            'from' => 0,
            'to' => $personageId,
            'subject' => "Battle result",
            'body' => "Winner: " . $battle['result']['winner']
        );
        mail_Mapper::model()->createNewNotice($mailAttribute);
    }

    // Удаляем сражения с базы данных
    fight_Mapper::model()->deleteFight($fight['id']);

    print_r($battle);
}

function sortUnitsByHp($unitsInfo) {
    $unitsByHp = array();
    foreach ($unitsInfo as $unitId => $unitInfo) {
        $unitsByHp[$unitInfo['life']][] = $unitId;
    }
    ksort($unitsByHp);
    return $unitsByHp;
}

function calcLosses($units, $damage, $unitsByHp) {
    $totalLosses = array();
    foreach ($unitsByHp as $unitHp => $unitsId) {
        if ($damage <= 0)
            break;

        $lossesUnits = array();
        $total = 0;
        foreach ($unitsId as $unitId) {
            if (isset($units[$unitId])) {
                $lossesUnits[$unitId] = $units[$unitId];
                $total += $units[$unitId];
            }
        }

        if ($damage - $total * $unitHp < 0) {
            $totalLossesCount = floor($damage / $unitHp);
            $notCalcLossesCount = $totalLossesCount;
            foreach ($lossesUnits as $key => $lossesCount) {
                $losses = round($lossesCount / $total * $totalLossesCount);
                $notCalcLossesCount -= $losses;
                $lossesUnits[$key] = $losses;
            }

            if ($notCalcLossesCount != 0) {
                $sign = 1;
                if ($notCalcLossesCount < 0) {
                    $notCalcLossesCount *= -1;
                    $sign = -1;
                }

                while ($notCalcLossesCount != 0) {
                    $randomUnitId = $unitsId[rand(0, count($unitsId) - 1)];

                    if ($sign == -1 && $lossesUnits[$randomUnitId] == 0)
                        continue;

                    if ($sign == 1 && $lossesUnits[$randomUnitId] == $units[$randomUnitId])
                        continue;

                    $lossesUnits[$randomUnitId] += $sign;
                    $notCalcLossesCount--;
                }
            }
            $damage = 0;
        }
        else {
            $damage -= $total * $unitHp;
        }

        $totalLosses += $lossesUnits;
    }

    return $totalLosses;
}

function calcRestOfUnitsWithLosses($totalStartUnits, $personageUnits, $totalLosses) {
    $personageUnitsWithLoses = $personageUnits;
    foreach ($totalLosses as $unitId => $lossesCount) {
        if ($lossesCount == 0)
            continue;

        $squadIds = array();
        $notCountLosses = $lossesCount;
        foreach ($personageUnits as $squadId => $squad) {
            if (!isset($squad[$unitId])) {
                continue;
            }

            $losses = round($squad[$unitId] / $totalStartUnits[$unitId] * $lossesCount);
            $notCountLosses -= $losses;
            $personageUnitsWithLoses[$squadId][$unitId] -= $losses;
            $squadIds[] = $squadId;
        }


        if ($notCountLosses != 0) {
            $sign = 1;
            if ($notCountLosses < 0) {
                $sign = -1;
                $notCountLosses *= -1;
            }

            while ($notCountLosses != 0) {
                $randomSquadId = $squadIds[rand(0, count($squadIds) - 1)];
                if ($sign == -1 && $personageUnitsWithLoses[$randomSquadId][$unitId] == 0)
                    continue;

                if ($sign == 1 && $personageUnitsWithLoses[$randomSquadId][$unitId] == $personageUnits[$randomUnitId][$unitId])
                    continue;

                $personageUnitsWithLoses[$randomSquadId][$unitId] += $sign;
                $notCountLosses--;
            }
        }
    }

    return $personageUnitsWithLoses;
}

function calcParams($battle, $unitsInfo) {
    calcSideParams(&$battle, 'defenders', $unitsInfo);
    calcSideParams(&$battle, 'attackers', $unitsInfo);
}

function calcSideParams($battle, $side, $unitsInfo) {
    $battle[$side]['attack'] = 0;
    $battle[$side]['protection'] = 0;
    $battle[$side]['life'] = 0;
    foreach ($battle[$side]['current_units'] as $unitId => $count) {
        $battle[$side]['attack'] += $count * $unitsInfo[$unitId]['attack'];
        $battle[$side]['protection'] += $count * $unitsInfo[$unitId]['protection'];
        $battle[$side]['life'] += $count * $unitsInfo[$unitId]['life'];
    }
}

function updateAttackersUnitsInSquads($endBattleUnits) {
    foreach ($endBattleUnits as $squadId => $units) {
        $unitsArray = array();
        foreach ($units as $unitId => $count) {
            if ($count != 0) {
                $unitsArray[] = array(
                    'unit_id' => $unitId,
                    'count' => $count
                );
            }
        }

        // Если войска остались в отряде, то изменяем их
        if (!empty($unitsArray)) {
            unit_UnitsMoving::model()->updateSquadUnits($squadId, serialize($unitsArray));
        }
        // Если войск не осталось, то удаляем отряд
        else {
            unit_UnitsMoving::model()->removeSquad($squadId);
        }
    }
}

function updateDefendersUnitsInLocation($fight, $startBattleUnits, $endBattleUnits) {
    foreach ($startBattleUnits as $personageId => $units) {
        foreach ($units as $unitId => $count) {
            if ($endBattleUnits[$squadId][$unitId] == $count)
                continue;

            $losses = $count - $endBattleUnits[$id][$unitId];
            personage_UnitLocation::model()->changeUnitsCountInLocation(
                    $unitId, $personageId, $fight['x'], $fight['y'], -$losses
            );
        }
    }
}

?>