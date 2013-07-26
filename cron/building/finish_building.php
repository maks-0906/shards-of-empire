<?php
/**
 * Файл содержит логику для обеспечения действий по улучшению и построке зданий после окончания временной метки.
 * Логика является подстраховкой, если с клиента не был послан запрос
 * (событие окончания времени постройки/улучшения здания) на завршение постройки/улучшения.
 *
 * @author Greg
 * @package cron
 */

include_once('../bootstrap.php');

try
{
	// Поиск зданий, которые ещё в процессе постройки или улучшения, но уже время процесса вышло.
	$buildings = personage_Building::model()->findBuildingsWithFinishCreateAndImprove();
    personage_Building::model()->finishConstructOrImproveBuildings($buildings);

}
catch (Exception $e)
{
	e1("Buildings finish process create or improve (CRON): ", $e->getMessage());
}

