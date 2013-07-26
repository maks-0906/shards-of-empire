<?php
/**
 * Файл содержит стратегию расчёта видимых частей карты в случаях выхода за границу её по сторонам:
 * верх-справа-низ-слева, без выхода в угол.
 *
 * @author Greg
 * @package map
 */

/**
* Класс стратегия расчёта видимых частей карты в случаях выхода за границу её по сторонам:
* верх-справа-низ-слева, без выхода в угол.
*
* @author Greg
* @version 1.0.0
* @package map
*/
class map_SideMap extends map_GeometryMap {

	/**
	 * Определение видимой области границ карты по сторонам лево-право-верх-низ, состоящей из двух частей.
	 *
	 * @return array
	 * @throws E1Exception
	 */
	public function detectDelimitationVisibleMap()
	{
		switch($this->typeGoingBeyond)
		{
			case map_VisibleMap::HIGH_SIDE_TYPE:
				return $this->calculationVisibleHighSide(); break;

			case map_VisibleMap::RIGHT_SIDE_TYPE:
				return $this->calculationVisibleRightSide(); break;

			case map_VisibleMap::DOWN_SIDE_TYPE;
				return $this->calculationVisibleDownSide(); break;

			case map_VisibleMap::LEFT_SIDE_TYPE:
				return $this->calculationVisibleLeftSide(); break;

			default:
				throw new E1Exception('Type going beyond not defined!');
		}
	}

	/**
	 * Расчёт координат видимых частей за границей верхней сторны карты.
	 */
	private function calculationVisibleHighSide()
	{
		$highPart = new map_PartVisibleMap();
		$downPart = new map_PartVisibleMap();

		$highBoundDistanceY = $this->centerY;
		$highPart->x_0 = $this->centerX - $this->radiusX;
		$highPart->y_0 = $this->MAX_Y - $this->radiusY + $highBoundDistanceY;
		$highPart->x_1 = $this->centerX + $this->radiusX;
		$highPart->y_1 = $highPart->y_0;
		$highPart->x_2 = $highPart->x_1;
		$highPart->y_2 = $this->MAX_Y;
		$highPart->x_3 = $highPart->x_0;
		$highPart->y_3 = $this->MAX_Y;

		$downPart->x_0 = $this->centerX - $this->radiusX;
		$downPart->y_0 = 0;
		$downPart->x_1 = $this->centerX + $this->radiusX;
		$downPart->y_1 = $downPart->y_0;
		$downPart->x_2 = $downPart->x_1;
		$downPart->y_2 = $this->radiusY + $highBoundDistanceY;
		$downPart->x_3 = $downPart->x_0;
		$downPart->y_3 = $downPart->y_2;

		return array('high_part' => $highPart, 'down_part' => $downPart);
	}

	/**
	 * Расчёт координат видимых частей за границей нижней стороны карты.
	 * @TODO !!!! Оптимизировать, два метода calculationVisibleDownSide и calculationVisibleHighSide являются почти одинаковыми за исключением расчёта по Y если центр не на максимальном и не на 0
	 * @return array
	 */
	private function calculationVisibleDownSide()
	{
		$highPart = new map_PartVisibleMap();
		$downPart = new map_PartVisibleMap();


		$downBoundDistanceY = $this->MAX_Y - $this->centerY;
		$highPart->x_0 = $this->centerX - $this->radiusX;
		$highPart->y_0 = $this->MAX_Y - $this->radiusY - $downBoundDistanceY;
		$highPart->x_1 = $this->centerX + $this->radiusX;
		$highPart->y_1 = $highPart->y_0;
		$highPart->x_2 = $highPart->x_1;
		$highPart->y_2 = $this->MAX_Y;
		$highPart->x_3 = $highPart->x_0;
		$highPart->y_3 = $this->MAX_Y;

		$downPart->x_0 = $this->centerX - $this->radiusX;
		$downPart->y_0 = 0;
		$downPart->x_1 = $this->centerX + $this->radiusX;
		$downPart->y_1 = $downPart->y_0;
		$downPart->x_2 = $downPart->x_1;
		$downPart->y_2 = $this->radiusY - $downBoundDistanceY;
		$downPart->x_3 = $downPart->x_0;
		$downPart->y_3 = $downPart->y_2;

		return array('high_part' => $highPart, 'down_part' => $downPart);
	}

	/**
	 * Расчёт координат видимых частей за границей правой стороны карты.
	 * @TODO !!!! Оптимизировать, два метода calculationVisibleRightSide и calculationVisibleLeftSide являются почти одинаковыми за исключением расчёта по X если центр не на максимальном и не на 0
	 * @return array
	 */
	private function calculationVisibleRightSide()
	{
		$rightPart = new map_PartVisibleMap();
		$leftPart = new map_PartVisibleMap();

		$rightBoundDistanceX = $this->MAX_X - $this->centerX;
		$leftPart->x_0 = $this->MAX_X - $this->radiusX - $rightBoundDistanceX;
		$leftPart->y_0 = $this->centerY - $this->radiusY;
		$leftPart->x_1 = $this->MAX_X;
		$leftPart->y_1 = $leftPart->y_0;
		$leftPart->x_2 = $this->MAX_X;
		$leftPart->y_2 = $this->centerY + $this->radiusY;
		$leftPart->x_3 = $leftPart->x_0;
		$leftPart->y_3 = $leftPart->y_2;

		$rightPart->x_0 = 0;
		$rightPart->y_0 = $this->centerY - $this->radiusY;
		$rightPart->x_1 = $this->radiusX - $rightBoundDistanceX;
		$rightPart->y_1 = $rightPart->y_0;
		$rightPart->x_2 = $rightPart->x_1;
		$rightPart->y_2 = $this->centerY + $this->radiusY;
		$rightPart->x_3 = $rightPart->x_0 ;
		$rightPart->y_3 = $rightPart->y_2;

		return array('left_part' => $leftPart, 'right_part' => $rightPart);
	}

	/**
	 * Расчёт координат видимых частей за границей левой стороны карты.
	 * @return array
	 */
	private function calculationVisibleLeftSide()
	{
		$rightPart = new map_PartVisibleMap();
		$leftPart = new map_PartVisibleMap();

		$leftBoundDistanceX = $this->centerX;
		$leftPart->x_0 = $this->MAX_X - $this->radiusX + $leftBoundDistanceX;
		$leftPart->y_0 = $this->centerY - $this->radiusY;
		$leftPart->x_1 = $this->MAX_X;
		$leftPart->y_1 = $leftPart->y_0;
		$leftPart->x_2 = $this->MAX_X;
		$leftPart->y_2 = $this->centerY + $this->radiusY;
		$leftPart->x_3 = $leftPart->x_0;
		$leftPart->y_3 = $leftPart->y_2;

		$rightPart->x_0 = 0;
		$rightPart->y_0 = $this->centerY - $this->radiusY;
		$rightPart->x_1 = $this->radiusX + $leftBoundDistanceX;
		$rightPart->y_1 = $rightPart->y_0;
		$rightPart->x_2 = $rightPart->x_1;
		$rightPart->y_2 = $this->centerY + $this->radiusY;
		$rightPart->x_3 = $rightPart->x_0 ;
		$rightPart->y_3 = $rightPart->y_2;

		return array( 'left_part' => $leftPart, 'right_part' => $rightPart);
	}
}
