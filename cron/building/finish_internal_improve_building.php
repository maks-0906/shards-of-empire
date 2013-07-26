<?php
/**
 * Файл содержит логику завершения постройки внутреннего улучшения здания.
 * Логика является подстраховкой, если с клиента не был послан запрос
 * (событие окончания времени улучшения) на завршение улучшения.
 *
 * @author Greg
 * @package cron
 */
//cron/building/finish_internal_improve_building.php

include_once('../bootstrap.php');

//Получить все внутренние улучшения к которых окончено выделенное для этого время
$allImprove = personage_Improve::model()->findFinishImproveBuildings();

if (empty($allImprove)) {
    exit();
}

foreach ($allImprove as $improveBuilding) {
    if ($improveBuilding->current_data_bonus == NULL) {
        e1('No bonuses for building personage ID: ', $improveBuilding->id_building_personage);
    } else {

        //Получить сериализованные бонусы
        $bonus = personage_Improve::model()->recalculateBonusesWithInternalImprovements($improveBuilding);

        //Обновляем бонусы здниям и оканчиваем внутреннее улучшение
        $resultCommit = personage_Improve::model()->finishImproveBuildingsAndAddingBonuses($improveBuilding->id_building_personage,
                                                                                           $bonus);
    }

    if ($resultCommit === false) {
        e1("Failed to upgrade improvements (CRON): ", $e->getMessage());
    }
}

unset($allImprove, $recalculatedBonuses, $bonus, $resultCommit);



