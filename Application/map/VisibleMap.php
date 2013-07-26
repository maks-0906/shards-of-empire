<?php
/**
 * Файл содержит класс, который является контроллером получения рачётов видимого окна карты.
 *
 * @author Greg
 * @package map
 */

/**
 * Класс является контроллером получения рачётов видимого окна карты.
 * Включает так же в себя вычисление типов случаев выхода за пределы карты и запускает необходимую стратегию расчёта.
 *
 * @author Greg
 * @version 1.0.0
 * @package map
 */
class map_VisibleMap {

	const HIGH_LEFT_CORNER_TYPE = 'high_left';
	const HIGH_RIGHT_CORNER_TYPE = 'high_right';
	const DOWN_RIGHT_CORNER_TYPE = 'down_right';
	const DOWN_LEFT_CORNER_TYPE = 'down_left';
	const HIGH_SIDE_TYPE = 'high_side';
	const RIGHT_SIDE_TYPE = 'right_side';
	const DOWN_SIDE_TYPE = 'down_side';
	const LEFT_SIDE_TYPE = 'left_side';

	private $countYCells = 10;
	private $countXCells = 10;

	private $centerX;
	private $centerY;

	private $MAX_X = 1000;
	private $MAX_Y = 1000;

	/**
	 * Инициализация объекта определения видимой области карты.
	 *
	 * @param int $centerY
	 * @param int $centerX
	 * @param int $countYCells
	 * @param int $countXCells
	 * @param int $MAX_Y
	 * @param int $MAX_X
	 */
	public function __construct($centerY, $centerX, $countYCells, $countXCells, $MAX_Y, $MAX_X)
	{
		$this->centerY = $centerY;
		$this->centerX = $centerX;
		$this->countYCells = $countYCells;
		$this->countXCells = $countXCells;
		$this->MAX_Y = $MAX_Y;
		$this->MAX_X = $MAX_X;

		$this->radiusX = ceil($countXCells / 2);
		$this->radiusY = ceil($countYCells / 2);
	}

	/**
	 * Определение часте видимой области карты.
	 *
	 * @return array <map_models_PartVisibleMap>
	 * @throws E1Exception
	 */
	public function detectDelimitationVisibleMap()
	{
		$strategy = null;
		if($this->isStrategyNormalCaseVisibleMap() == true)
		{
			$strategy = new map_NormalVisibleMap(
				$this->centerY, $this->centerX, $this->countYCells, $this->countXCells, $this->MAX_Y, $this->MAX_X
			);

			return $strategy->detectDelimitationVisibleMap();
		}

		$typeCorner = $this->detectStrategyCasesGoingBeyondLimitsInCorner();
		if($typeCorner != false)
		{
			$strategy = new map_CornerVisibleMap(
				$this->centerY,
				$this->centerX,
				$this->countYCells,
				$this->countXCells,
				$this->MAX_Y,
				$this->MAX_X,
				$typeCorner
			);

			return $strategy->detectDelimitationVisibleMap();
		}

		$typeSide = $this->detectStrategyCasesGoingBeyondLimitsInSide();
		if($typeSide != false)
		{
			$strategy = new map_SideMap(
				$this->centerY,
				$this->centerX,
				$this->countYCells,
				$this->countXCells,
				$this->MAX_Y,
				$this->MAX_X,
				$typeSide
			);

			return $strategy->detectDelimitationVisibleMap();
		}

		if($strategy == null)
			throw new E1Exception('No strategy for calculating apparent card did not match!!!');
	}

	/**
	 * Утверждение случая нормального расположения видимого окна карты.
	 *
	 * @return bool
	 */
	private function isStrategyNormalCaseVisibleMap()
	{
		if(($this->centerY - $this->radiusY >= 0) && ($this->centerX + $this->radiusX <= $this->MAX_X)
			&& ($this->centerY + $this->radiusY <= $this->MAX_Y) && ($this->centerX - $this->radiusX >= 0))
			return true;

		return false;
	}

	/**
	 * Определение всех случаев выхода карты за границу в углах.
	 *
	 * @return bool
	 */
	private function detectStrategyCasesGoingBeyondLimitsInCorner()
	{
		// Выход карты за границы в верхнем левом углу
		if(($this->centerX - $this->radiusX) < 0 && ($this->centerY - $this->radiusY) < 0)
			return self::HIGH_LEFT_CORNER_TYPE;

		// Выход карты за границы в верхнем правом углу
		if(($this->centerX + $this->radiusX) > $this->MAX_X && ($this->centerY - $this->radiusY) < 0)
			return self::HIGH_RIGHT_CORNER_TYPE;

		// Выход карты за границы в нижнем правом углу
		if(($this->centerX + $this->radiusX) > $this->MAX_X && ($this->centerY + $this->radiusY) > $this->MAX_Y)
			return self::DOWN_RIGHT_CORNER_TYPE;

		// Выход карты за границы в нижнем правом углу
		if(($this->centerX - $this->radiusX) < 0 && ($this->centerY + $this->radiusY) > $this->MAX_Y)
			return self::DOWN_LEFT_CORNER_TYPE;

		return false;
	}

	/**
	 * Определение всех случаев выхода карты по сторонам лево-право-верх-низ.
	 *
	 * @return bool
	 */
	private function detectStrategyCasesGoingBeyondLimitsInSide()
	{
		// Выход карты за границы на верхней границе стороны
		$y_high_point  = $this->centerY - $this->radiusY;
		if(($this->centerY - $this->radiusY) < 0) return self::HIGH_SIDE_TYPE;

		// Выход карты за границы на правой границе стороны
		if(($this->centerX + $this->radiusX) > $this->MAX_X) return self::RIGHT_SIDE_TYPE;

		// Выход карты за границы на нижней границе стороны
		if(($this->centerY + $this->radiusY) > $this->MAX_Y) return self::DOWN_SIDE_TYPE;

		// Выход карты за границы на левой границе стороны
		if(($this->centerX - $this->radiusX) < 0) return self::LEFT_SIDE_TYPE;

		return false;
	}
}
