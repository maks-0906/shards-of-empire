<?php
/**
 * Файл содержит логику для расчёта боёв юнитов.
 *
 * @author Greg
 * @package cron
 */
//cron/unit/fighting_units.php
include_once('../bootstrap.php');

$allFight = fight_Mapper::model()->findBeginningOfFights(models_Time::model()->getCurrentFormedDateAndTime(),
                                                         fight_Mapper::FIGHT_STATUS_WAITING_ALLIED);

if (empty($allFight)) {
    exit();
}

try
{
    foreach($allFight as $fight) {
        fight_Mapper::model()->leadFight($fight->id_personages_units_fight);
    }

}
catch (DBException $e)
{
	e1("Process fighting units (CRON): ", $e->getMessage());
}