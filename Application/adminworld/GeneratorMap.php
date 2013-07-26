<?php
/**
 * Файл содержит класс прослойку для управления генерацией карты.
 *
 * @author Greg
 * @package map
 */

/**
 *  Класс прослойка для управления генерацией карты.
 *
 * @author Greg
 * @version 1.0.0
 * @package map
 */
class adminworld_GeneratorMap {

	/**
	 * Генерация ячеек для карты.
	 *
	 * @param int $idMapTemplate
	 * @param int $MAX_X
	 * @param int $MAX_Y
	 * @return bool
	 * @throws E1Exception
	 */
	public static function generatingCellsMap($idMapTemplate, $MAX_X, $MAX_Y)
	{
		$mapTemplate = adminworld_Mapper::model()->findWorldById($idMapTemplate);
		if($mapTemplate == null) throw new E1Exception('Template for world: '. $idMapTemplate . ' not found!');

		$fractions = array();
		foreach(personage_Fraction::model()->getAllIdFrations() as $fraction)
			$fractions[] = $fraction->id;

		$levels = map_LevelLocation::model()->getAllIdLevels();

		$combs = array();
		$step_x = adminworld_Comb::COUNT_CELLS_X;
		$step_y = adminworld_Comb::COUNT_CELLS_Y;

		// Получаем равномерное количество фракций
		$countAllCombsInMap = ceil($MAX_X / $step_x) * ceil($MAX_Y / $step_y);
		$countAllowableFractionsInMap = ceil($countAllCombsInMap / count($fractions));

		$counterNumberFractions = array();
		foreach($fractions as $fraction)
			$counterNumberFractions[$fraction] = $countAllowableFractionsInMap + 10000;

		$lastPositionOfY = $MAX_Y - $step_y;
		$lastPositionOfX = $MAX_X - $step_x;
		for($y = 0; $y <= $lastPositionOfY; $y += $step_y)
		{
			$combs[$y] = array();
			for($x = 0; $x <= $lastPositionOfX; $x += $step_x)
			{
				// Получаем идентификатор фракции правой граничной соты (первой),
				// если осталось заполнить последнюю в текущем ряду (Y)
				$notAllowableFractions = array();
				$lastComb = $combs[$y][0];
				if($lastComb instanceof adminworld_Comb && $x == $MAX_X)
					$notAllowableFractions[$lastComb->idFraction] = $lastComb->idFraction;
				// Получаем идентификатор фракции предыдущей соты
				$previousComb = $combs[$y][count($combs[$y]) - 1];
				if($previousComb instanceof adminworld_Comb)
					$notAllowableFractions[$previousComb->idFraction] = $previousComb->idFraction;
				// Получаем идентификатор фракции верхней соты
				$highComb = $combs[$y - $step_y][count($combs[$y - $step_y]) - 1];
				if($highComb instanceof adminworld_Comb)
					$notAllowableFractions[$highComb->idFraction] = $highComb->idFraction;

				// Получаем допустимые фракции
				$allowableFraction = array_diff_key($counterNumberFractions, $notAllowableFractions);

				// Формируем массив доступных фракций с учётом равномерного распределения по карте
				$maxRemainingNumberOfFractions = max($allowableFraction);
				$sortFractions = array();
				foreach($allowableFraction as $id => $counter)
				{
					if($counter >= $maxRemainingNumberOfFractions && $counter > 0)
					{
						$sortFractions[$id] = $counter;
						$maxRemainingNumberOfFractions = $counter;
					}
				}

				$keysAllowableFractions = array_keys($sortFractions);
				// Вычисляем рандомную допустимую фракцию с учётом уже использованных фракций на карте
				$keyFraction = (count($keysAllowableFractions) > 0) ? mt_rand(0, count($keysAllowableFractions)-1) : 0;
				$idFraction =  $keysAllowableFractions[$keyFraction];

				// Уменьшаем количество использованных фракций на карте
				$counterNumberFractions[$idFraction]--;

				$comb = new adminworld_Comb();
				$comb->initialize($mapTemplate, $idFraction, $levels);
				$comb->x_hl = $x;
				$comb->y_hl = $y;
				$comb->x_rd = $x + $step_x - 1;
				$comb->y_rd = $y + $step_y - 1;
				$comb->saveComb();

				$lastSerialNumber = 1;
				// Если линия только начинает заполняться сотами и уже не первая получаем последнюю на верхней линии
				if($x == 0 && $y > 0)
				{
					$keyLastYLineCombs = $y - $step_y;
					$lastComb = $combs[$keyLastYLineCombs][count($combs[$keyLastYLineCombs]) - 1];
					$lastSerialNumber = $lastComb->lastSerialNumber;
				}
				// Если линия уже заполняется получаем предыдущую соту
				elseif($x > 0)
				{
					$lastComb = $combs[$y][count($combs[$y]) -1];
					$lastSerialNumber = $lastComb->lastSerialNumber;
				}

				$comb->generateCells($lastSerialNumber);

				$combs[$y][] = $comb;
			}
		}

		return true;
	}
}
