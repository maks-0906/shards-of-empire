<?php
/**
 * Файл содержит класс модель формирующую абстрактную единицу "соту" на карте.
 *
 * @author Greg
 * @package adminworld
 */

/**
 * Класс модель формирующая абстрактную единицу "соту" на карте. Сота является группой ячеек одной фракции и имеет
 * свои граничные координаты.
 *
 * @author Greg
 * @version 1.0.0
 * @package adminworld
 */
class adminworld_Comb extends Mapper
{
	const TABLE_NAME = 'map_combs';

	const COUNT_CELLS_X = 10;
	const COUNT_CELLS_Y = 10;

	/**
	 * Идентификатор фракции для соты.
	 * @var int
	 */
	public $idFraction;

	/**
	 * Коллекция ячеек в соте.
	 * @var array <adminworld_Cell>
	 */
	private $cells = array();

	/**
	 * Идентификатор шаблона карты для соты.
	 * @var int
	 */
	public $idMapTemplate;

	/**
	 * Массив доступных уровней для присваивания ячейке (локации)
	 * @var array
	 */
	public $levels = array();

	/**
	 * Координата верхней левой точки по X
	 * @var int
	 */
	public $x_hl;

	/**
	 * Координата верхней левой точки по Y
	 * @var int
	 */
	public $y_hl;

	/**
	 * Координата нижней правой точки по X
	 * @var int
	 */
	public $x_rd;

	/**
	 * Координата нижней правой точки по Y
	 * @var int
	 */
	public $y_rd;

	/**
	 * Последний номер ячейки в соте
	 * @var int
	 */
	public $lastSerialNumber;

	/**
	 * Массив из идентификаторов паттернов для определения типа ячейки-локации.
	 * @var array
	 */
	private $patterns = array();

	/**
	 * Получение экземпляра сущности.
	 * @param string $className
	 * @return adminworld_Comb
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	/**
	 * Отношение модели к таблице.
	 * @return string
	 */
	public function tableName()
	{
		return self::TABLE_NAME;
	}

	/**
	 * Первичный ключ для текущей таблицы.
	 * @return string
	 */
	public function pk() { return 'id_comb';  }

	/**
	 * @param $mapTemplate
	 * @param $idFraction
	 * @param array $levels
	 */
	public function initialize($mapTemplate, $idFraction, array $levels)
	{
		$this->idMapTemplate = $mapTemplate->id;
		$this->patterns = $patterns = str_split($mapTemplate->map_template);
		$this->idFraction = $idFraction;
		$this->levels = $levels;
	}

	/**
	 * Поиск свободной соты.
	 *
	 * @param $idWorld
	 * @param $idFraction
	 * @return adminworld_Comb
	 */
	public function findFreeComb($idWorld, $idFraction)
	{
		$sql = "SELECT *
				FROM %s
				WHERE `id_map_template`=%d AND `id_fraction`=%d AND `current_count_personages` < `max_count_personages`";

		return $this->find($sql, self::TABLE_NAME, $idWorld, $idFraction);
	}

	/**
	 * Добавление ячейки в коллекцию соты.
	 *
	 * @param $x
	 * @param $y
	 * @param $serialNumber
	 */
	public function addCell($x, $y, $serialNumber)
	{
		$cell = new adminworld_Cell();
		$cell->id_comb = $this->id_comb;
		$cell->id_world = $this->idMapTemplate;

		// Цифра 2 Разрядность соты, в данный момент 10*10, выделяется номер соты соответственно и номер паттерна
		$numberPattern = intval(array_pop(str_split($serialNumber, 2)));
		$cell->map_pattern = $this->patterns[$numberPattern];

		$cell->id_fraction = $this->idFraction;
		$cell->id_level_cell = $this->getLevelCell($this->levels);
		$cell->x = $x;
		$cell->y = $y;
		$cell->serial_number = $serialNumber;
		$cell->save();

		$this->cells[] = $cell;
	}

	/**
	 * Сохранение соты в БД.
	 * @return adminworld_Comb|bool
	 */
	public function saveComb()
	{
		$this->id_map_template = $this->idMapTemplate;
		$this->id_fraction = $this->idFraction;
		$this->x_0 = $this->x_hl;
		$this->y_0 = $this->y_hl;
		$this->x_1 = $this->x_rd;
		$this->y_1 = $this->y_rd;

		$newComb = $this->save();
		if($newComb != null)
		{
			$this->id_comb = $newComb->id;
			return $this;
		}
		else
			return false;
	}

	/**
	 * Генерация ячеек в соте.
	 * @param $lastSerialNumber
	 */
	public function generateCells($lastSerialNumber)
	{
		$this->lastSerialNumber = $lastSerialNumber;
		for($y = $this->y_hl; $y <= $this->y_rd; $y++)
		{
			for($x = $this->x_hl; $x <= $this->x_rd; $x++)
			{
				$this->addCell($x, $y, $this->lastSerialNumber++);
			}
		}
	}

	/**
	 * Получение уровня ячейки, задаётся рандомно.
	 * @param array $levels
	 * @return int
	 */
	private function getLevelCell(array $levels)
	{
		$count = count($levels);
		$level = $levels[mt_rand(0, $count)];

		return intval($level->id_level_cell);
	}
}
