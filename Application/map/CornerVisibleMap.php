<?php
/**
 * Файл содержит класс расчётов видимого окна карты по четырём углам.
 *
 * @author Greg
 * @package map
 */

/**
 * Класс расчётов видимого окна карты в углах.
 *
 * @author Greg
 * @version 1.0.0
 * @package map
 */
class map_CornerVisibleMap extends map_GeometryMap {

	/**
	 * Определение видимой области границ карты по углам:
	 * 1. Левый верхний;
	 * 2. Правый нижний;
	 * 3. Правый верхний;
	 * 4. Левый нижний;
	 *
	 * Видимое окно состояит из четырёх частей.
	 *
	 * @return array
	 * @throws E1Exception
	 */
	public function detectDelimitationVisibleMap()
	{
		switch($this->typeGoingBeyond)
		{
			case map_VisibleMap::HIGH_LEFT_CORNER_TYPE:
				return $this->calculationVisibleHighLeftCorner(); break;

			case map_VisibleMap::DOWN_RIGHT_CORNER_TYPE:
				return $this->calculationVisibleDownRightCorner(); break;

			case map_VisibleMap::HIGH_RIGHT_CORNER_TYPE;
				return $this->calculationVisibleHighRightCorner(); break;

			case map_VisibleMap::DOWN_LEFT_CORNER_TYPE:
				return $this->calculationVisibleDownLeftCorner(); break;

			default:
				throw new E1Exception('Type going beyond not defined!');
		}
	}

	/**
	 * Расчёт видимого окна карты в верхем левом углу.
	 * @return array <map_PartVisibleMap>
	 */
	private function calculationVisibleHighLeftCorner()
	{
		$leftHighPart = new map_PartVisibleMap();
		$rightHighPart = new map_PartVisibleMap();
		$rightDownPart = new map_PartVisibleMap();
		$leftDownPart = new map_PartVisibleMap();

		// Определяем расстояние до верхней и левой границы карты от центра
		$upperBoundDistanceY = $this->centerY;
		$leftBoundDistanceX = $this->centerX;

		// Определяем левую верхнюю часть окна
		$leftHighPart->x_0 = $this->MAX_X - $this->radiusX + $leftBoundDistanceX;
		$leftHighPart->y_0 = $this->MAX_Y - $this->radiusY + $upperBoundDistanceY;
		$leftHighPart->x_1 = $this->MAX_X;
		$leftHighPart->y_1 = $leftHighPart->y_0;
		$leftHighPart->x_2 = $leftHighPart->x_1;
		$leftHighPart->y_2 = $this->MAX_Y;
		$leftHighPart->x_3 = $leftHighPart->x_0;
		$leftHighPart->y_3 = $leftHighPart->y_2;

		// Определяем правую верхнюю часть окна
		$rightHighPart->x_0 = 0;
		$rightHighPart->y_0 = $this->MAX_Y - $this->radiusY + $upperBoundDistanceY;
		$rightHighPart->x_1 = $leftBoundDistanceX + $this->radiusX;
		$rightHighPart->y_1 = $rightHighPart->y_0;
		$rightHighPart->x_2 = $rightHighPart->x_1;
		$rightHighPart->y_2 = $this->MAX_Y;
		$rightHighPart->x_3 = 0;
		$rightHighPart->y_3 = $rightHighPart->y_2;

		// Определяем нижнюю правую часть видимого окна
		$rightDownPart->x_0 = 0;
		$rightDownPart->y_0 = 0;
		$rightDownPart->x_1 = $leftBoundDistanceX + $this->radiusX;
		$rightDownPart->y_1 = 0;
		$rightDownPart->x_2 = $rightDownPart->x_1;
		$rightDownPart->y_2 = $upperBoundDistanceY + $this->radiusY;
		$rightDownPart->x_3 = 0;
		$rightDownPart->y_3 = $rightDownPart->y_2;

		// Определение нижней левой части окна
		$leftDownPart->x_0 = $this->MAX_X - $this->radiusX + $leftBoundDistanceX;
		$leftDownPart->y_0 = 0;
		$leftDownPart->x_1 = $this->MAX_X;
		$leftDownPart->y_1 = 0;
		$leftDownPart->x_2 = $this->MAX_X;
		$leftDownPart->y_2 = $upperBoundDistanceY + $this->radiusY;
		$leftDownPart->x_3 = $leftDownPart->x_0;
		$leftDownPart->y_3 = $leftDownPart->y_2;


		return array(
			'left_high' => $leftHighPart,
			'right_high' => $rightHighPart,
			'right_down' => $rightDownPart,
			'left_down' => $leftDownPart
		);
	}

	/**
	 * Расчёт видимого окна карты в нижнем правом углу.
	 * @return array <map_PartVisibleMap>
	 */
	private function calculationVisibleDownRightCorner()
	{
		$leftHighPart = new map_PartVisibleMap();
		$rightHighPart = new map_PartVisibleMap();
		$rightDownPart = new map_PartVisibleMap();
		$leftDownPart = new map_PartVisibleMap();

		// Определяем расстояние до нижней и правой границы карты от центра
		$downBoundDistanceY = $this->MAX_Y - $this->centerY;
		$rightBoundDistanceX = $this->MAX_X - $this->centerX;

		// Определяем левую верхнюю часть окна
		$leftHighPart->x_0 = $this->MAX_X - $this->radiusX - $rightBoundDistanceX;
		$leftHighPart->y_0 = $this->MAX_Y - $this->radiusY - $downBoundDistanceY;
		$leftHighPart->x_1 = $this->MAX_X;
		$leftHighPart->y_1 = $leftHighPart->y_0;
		$leftHighPart->x_2 = $leftHighPart->x_1;
		$leftHighPart->y_2 = $this->MAX_Y;
		$leftHighPart->x_3 = $leftHighPart->x_0;
		$leftHighPart->y_3 = $leftHighPart->y_2;

		// Определяем правую верхнюю часть окна
		$rightHighPart->x_0 = 0;
		$rightHighPart->y_0 = $this->MAX_Y - $this->radiusY - $downBoundDistanceY;
		$rightHighPart->x_1 = $this->radiusX - $rightBoundDistanceX;
		$rightHighPart->y_1 = $rightHighPart->y_0;
		$rightHighPart->x_2 = $rightHighPart->x_1;
		$rightHighPart->y_2 = $this->MAX_Y;
		$rightHighPart->x_3 = 0;
		$rightHighPart->y_3 = $this->MAX_Y;

		// Определяем нижнюю правую часть видимого окна
		$rightDownPart->x_0 = 0;
		$rightDownPart->y_0 = 0;
		$rightDownPart->x_1 = $this->radiusX - $rightBoundDistanceX;
		$rightDownPart->y_1 = 0;
		$rightDownPart->x_2 = $rightDownPart->x_1;
		$rightDownPart->y_2 = $this->radiusY - $downBoundDistanceY;
		$rightDownPart->x_3 = 0;
		$rightDownPart->y_3 = $rightDownPart->y_2;

		// Определение нижней левой части окна
		$leftDownPart->x_0 = $this->MAX_X - $this->radiusX - $rightBoundDistanceX;
		$leftDownPart->y_0 = 0;
		$leftDownPart->x_1 = $this->MAX_X;
		$leftDownPart->y_1 = 0;
		$leftDownPart->x_2 = $this->MAX_X;
		$leftDownPart->y_2 = $this->radiusY - $downBoundDistanceY;
		$leftDownPart->x_3 = $leftDownPart->x_0;
		$leftDownPart->y_3 = $leftDownPart->y_2;


		return array(
			'left_high' => $leftHighPart,
			'right_high' => $rightHighPart,
			'right_down' => $rightDownPart,
			'left_down' => $leftDownPart
		);
	}

	/**
	 * Расчёт видимого окна карты в верхнем правом углу.
	 * @return array
	 */
	private function calculationVisibleHighRightCorner()
	{
		$leftHighPart = new map_PartVisibleMap();
		$rightHighPart = new map_PartVisibleMap();
		$rightDownPart = new map_PartVisibleMap();
		$leftDownPart = new map_PartVisibleMap();

		// Определяем расстояние до верхней и правой границы карты от центра
		$highBoundDistanceY = $this->centerY;
		$rightBoundDistanceX = $this->MAX_X - $this->centerX;

		// Определяем левую верхнюю часть окна
		$leftHighPart->x_0 = $this->MAX_X - $this->radiusX - $rightBoundDistanceX;
		$leftHighPart->y_0 = $this->MAX_Y - $this->radiusY + $highBoundDistanceY;
		$leftHighPart->x_1 = $this->MAX_X;
		$leftHighPart->y_1 = $leftHighPart->y_0;
		$leftHighPart->x_2 = $leftHighPart->x_1;
		$leftHighPart->y_2 = $this->MAX_Y;
		$leftHighPart->x_3 = $leftHighPart->x_0;
		$leftHighPart->y_3 = $leftHighPart->y_2;

		// Определяем правую верхнюю часть окна
		$rightHighPart->x_0 = 0;
		$rightHighPart->y_0 = $this->MAX_Y - $this->radiusY + $highBoundDistanceY;
		$rightHighPart->x_1 = $this->radiusX - $rightBoundDistanceX;
		$rightHighPart->y_1 = $rightHighPart->y_0;
		$rightHighPart->x_2 = $rightHighPart->x_1;
		$rightHighPart->y_2 = $this->MAX_Y;
		$rightHighPart->x_3 = 0;
		$rightHighPart->y_3 = $this->MAX_Y;

		// Определяем нижнюю правую часть видимого окна
		$rightDownPart->x_0 = 0;
		$rightDownPart->y_0 = 0;
		$rightDownPart->x_1 = $this->radiusX - $rightBoundDistanceX;
		$rightDownPart->y_1 = 0;
		$rightDownPart->x_2 = $rightDownPart->x_1;
		$rightDownPart->y_2 = $this->radiusY + $highBoundDistanceY;
		$rightDownPart->x_3 = 0;
		$rightDownPart->y_3 = $rightDownPart->y_2;

		// Определение нижней левой части окна
		$leftDownPart->x_0 = $this->MAX_X - $this->radiusX - $rightBoundDistanceX;
		$leftDownPart->y_0 = 0;
		$leftDownPart->x_1 = $this->MAX_X;
		$leftDownPart->y_1 = 0;
		$leftDownPart->x_2 = $this->MAX_X;
		$leftDownPart->y_2 = $this->radiusY + $highBoundDistanceY;
		$leftDownPart->x_3 = $leftDownPart->x_0;
		$leftDownPart->y_3 = $leftDownPart->y_2;


		return array(
			'left_high' => $leftHighPart,
			'right_high' => $rightHighPart,
			'right_down' => $rightDownPart,
			'left_down' => $leftDownPart
		);
	}

	/**
	 * Расчёт видимого окна карты в нижнем левом углу.
	 * @return array
	 */
	private function calculationVisibleDownLeftCorner()
	{
		$leftHighPart = new map_PartVisibleMap();
		$rightHighPart = new map_PartVisibleMap();
		$rightDownPart = new map_PartVisibleMap();
		$leftDownPart = new map_PartVisibleMap();

		// Определяем расстояние до нижней и леврой границы карты от центра
		$downBoundDistanceY = $this->MAX_Y - $this->centerY;
		$leftBoundDistanceX = $this->centerX;

		// Определяем левую верхнюю часть окна
		$leftHighPart->x_0 = $this->MAX_X - $this->radiusX + $leftBoundDistanceX;
		$leftHighPart->y_0 = $this->MAX_Y - $this->radiusY - $downBoundDistanceY;
		$leftHighPart->x_1 = $this->MAX_X;
		$leftHighPart->y_1 = $leftHighPart->y_0;
		$leftHighPart->x_2 = $leftHighPart->x_1;
		$leftHighPart->y_2 = $this->MAX_Y;
		$leftHighPart->x_3 = $leftHighPart->x_0;
		$leftHighPart->y_3 = $leftHighPart->y_2;

		// Определяем правую верхнюю часть окна
		$rightHighPart->x_0 = 0;
		$rightHighPart->y_0 = $this->MAX_Y - $this->radiusY - $downBoundDistanceY;
		$rightHighPart->x_1 = $this->radiusX + $leftBoundDistanceX;
		$rightHighPart->y_1 = $rightHighPart->y_0;
		$rightHighPart->x_2 = $rightHighPart->x_1;
		$rightHighPart->y_2 = $this->MAX_Y;
		$rightHighPart->x_3 = 0;
		$rightHighPart->y_3 = $this->MAX_Y;

		// Определяем нижнюю правую часть видимого окна
		$rightDownPart->x_0 = 0;
		$rightDownPart->y_0 = 0;
		$rightDownPart->x_1 = $this->radiusX + $leftBoundDistanceX;
		$rightDownPart->y_1 = 0;
		$rightDownPart->x_2 = $rightDownPart->x_1;
		$rightDownPart->y_2 = $this->radiusY - $downBoundDistanceY;
		$rightDownPart->x_3 = 0;
		$rightDownPart->y_3 = $rightDownPart->y_2;

		// Определение нижней левой части окна
		$leftDownPart->x_0 = $this->MAX_X - $this->radiusX + $leftBoundDistanceX;
		$leftDownPart->y_0 = 0;
		$leftDownPart->x_1 = $this->MAX_X;
		$leftDownPart->y_1 = 0;
		$leftDownPart->x_2 = $this->MAX_X;
		$leftDownPart->y_2 = $this->radiusY - $downBoundDistanceY;
		$leftDownPart->x_3 = $leftDownPart->x_0;
		$leftDownPart->y_3 = $leftDownPart->y_2;


		return array(
			'left_high' => $leftHighPart,
			'right_high' => $rightHighPart,
			'right_down' => $rightDownPart,
			'left_down' => $leftDownPart
		);
	}
}
