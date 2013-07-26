<?php

/**
 * Крон просматривает все отряды, которые закончили передвиение
 *
 * @author Oleg Goroun
 * @package cron
 */

include_once('../bootstrap.php');

$squads = unit_UnitsMoving::model()->getSquads();
if (!empty($squads))
{
	foreach ($squads as $squad)
	{
		$process = $squad['end_time'] - time();
		if ($squad['cancel_time'] != null)
		{
			$process = ($squad['cancel_time'] - $squad['start_time']) - (time() - $squad['cancel_time']);
		}
		if ($process <= 0 && $squad['status'] == "moving")
		{
			// Если цель отряда - защита, или локация является мирной
			if ($squad["target"] == unit_UnitsMoving::MOVE_PROTECTION || personage_Location::model()->isLocationIsPeaceful($squad['x_d'], $squad['y_d'], $squad['personage_id']))
			{
				// Находим конечные координаты
				$endX = $squad['x_d'];
				$endY = $squad['y_d'];
				// Если была отмена передвижения, то конечными ставим начальные
				if ($squad['cancel_time'] != null)
				{
					$endX = $squad['x_s'];
					$endY = $squad['y_s'];
				}

				// Добавляем войска отряда на локацию
				$units = unserialize($squad['units']);
				foreach ($units as $unit) {
					$unit = (array)$unit;
					personage_UnitLocation::model()->changeUnitsCountInLocation($unit['unit_id'], $squad['personage_id'], $endX, $endY, $unit['count']);
				}

				// TODO: Списываем ресурсы отряда на локацию(?/, если локация город/?)


				// Расформировываем отряд
				unit_UnitsMoving::model()->removeSquad($squad['id']);
			}
			// Если цель отряда - атака
			elseif (in_array($squad["target"], array(unit_UnitsMoving::MOVE_ATTACK, unit_UnitsMoving::MOVE_ATTACK_TACKING)))
			{
				$fight = fight_Mapper::model()->getFightByLocation($squad['x_d'], $squad['y_d']);
				// Если сражения не найдено, то создаем новое сражение
				if (!$fight)
				{
					fight_Mapper::model()->initFight($squad['id'], $squad['x_d'], $squad['y_d']);
				}

				// Меняем статус отряда на "ожидание сражения"
				unit_UnitsMoving::model()->updateSquadStatus($squad['id'], "waiting_for_battle");
			}
		}
	}
}

?>
