<?php
/**
 * Файл содержит абстрактный базовый класс расчёта геометрии карты.
 *
 * @author Greg
 * @package map
 */

/**
 * Абстрактный базовый класс расчёта геометрии карты.
 *
 * @author Greg
 * @version 1.0.0
 * @package map
 */
abstract class map_GeometryMap {

	protected $countYCells = 10;
	protected $countXCells = 10;

	protected $centerX;
	protected $centerY;

	protected $MAX_X = 1000;
	protected $MAX_Y = 1000;

	protected $radiusX;
	protected $radiusY;

	/**
	 * Тип выхода за пределы карты.
	 *
	 * @var string
	 */
	protected $typeGoingBeyond;

	/**
	 * Инициализация стратегии подсчёта видимой области карты.
	 *
	 * @param int $centerY
	 * @param int $centerX
	 * @param int $countYCells
	 * @param int $countXCells
	 * @param int $MAX_Y
	 * @param int $MAX_X
	 * @param string|bool $type
	 */
	public function __construct($centerY, $centerX, $countYCells, $countXCells, $MAX_Y, $MAX_X, $type = false)
	{
		$this->centerY = $centerY;
		$this->centerX = $centerX;
		$this->countYCells = $countYCells;
		$this->countXCells = $countXCells;
		$this->MAX_Y = $MAX_Y;
		$this->MAX_X = $MAX_X;

		$this->radiusX = floor($countXCells / 2);
		$this->radiusY = floor($countYCells / 2);

		$this->typeGoingBeyond = $type;
	}

	/**
	 * Определение видимой области границ карты, состоящей из частей.
	 * @return array
	 */
	abstract public function detectDelimitationVisibleMap();
}
