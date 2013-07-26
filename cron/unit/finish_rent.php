<?php
/**
 * Файл содержит логику для завершения найма юнитов после окончания временной метки.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package cron
 */

include_once('../bootstrap.php');

try
{
    personage_Unit::model()->finishUnitsRent();
//	// Поиск юнитов, которые ещё в процессе найма (status='hiring'),
//	// но с просроченной временной меткой окончания найма.
//	$units = personage_Unit::model()->findUnitsWithFinishTimeRent();
//	/* @var $r personage_Unit */
//	foreach($units as $u) 
//	{
//		$u->finishUnitsRentById($u->personage_id);
//	}
	
	// Установка метки найм(status='hiring') на следующего юнита в очереди
	personage_Unit::model()->startNextUnitsRent();
}
catch (Exception $e)
{
	e1("Process rent finish (CRON): ", $e->getMessage());
}
