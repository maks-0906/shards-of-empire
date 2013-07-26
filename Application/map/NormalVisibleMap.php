<?php
/**
 * Файл содержит класс расчётов видимого окна в пределах карты.
 *
 * @author Greg
 * @package map
 */

/**
 * Класс расчётов видимого окна в пределах карты.
 *
 * @author Greg
 * @version 1.0.0
 * @package map
 */
class map_NormalVisibleMap extends map_GeometryMap{

	/**
	 * Расчёт видимого окна в пределах карты.
	 * @return array <map_models_PartVisibleMap>
	 */
	public function detectDelimitationVisibleMap()
	{
		$visibleMap = new map_PartVisibleMap();
		
		// Определяем нормальную область
		$visibleMap->x_0 = $this->centerX - $this->radiusX;
		$visibleMap->y_0 = $this->centerY - $this->radiusY;
		$visibleMap->x_1 = $this->centerX + $this->radiusX;
		$visibleMap->y_1 = $visibleMap->y_0;
		$visibleMap->x_2 = $visibleMap->x_1;
		$visibleMap->y_2 = $this->centerY + $this->radiusY;
		$visibleMap->x_3 = $visibleMap->x_0;
		$visibleMap->y_3 = $visibleMap->y_2;

		return array('normal' => $visibleMap);
	}
}
