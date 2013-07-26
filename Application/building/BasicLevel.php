<?php
/**
 * Файл содержит запросы к базе данных связанные с улучшениями зданий
 *
 * @author Greg
 * @package
 */

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class building_BasicLevel extends Mapper
{
	const TABLE_NAME = 'building_basic_levels';

	const MEASURE_UNIT = 'u';//Расчет путем присвоения
	const MEASURE_PERCENT = 'pt';//Расчет в процентах
	const MEASURE_FORMULA = 'f'; //Сериализованная формула

    const BONUS_PROTECTION = 'bonus_protection';

	const BONUS_HAPPINESS = 'bonus_happiness';
	const SYMPATHY = 'bonus_sympathy';
	const BONUS_HEALTH = 'bonus_health';
    const BONUS_FAITH = 'bonus_faith';

	const SIZE_GARRISON = 'bonus_size_garrison';
	const ATTACK = 'bonus_attack';

	const COUNT_PRODUCTS = 'bonus_number_products';
	const PRODUCTION_TIME = 'bonus_time_production';

	const FAME_COUNT = 'bonus_fame';

	const SALE = 'bonus_for_sale';
	const BUY = 'bonus_for_buying';

	const COUNT_DEATHS = 'mortality_rate';

	const CRIME = 'crime';
	const NUMBER_BARRELS_BEER = 'number_barrels_beer';

	const SPEED_LEARNING_TECHNOLOGIES = 'study_technology';

	const BLESSING_COUNT = 'bonus_blessing';
	const CANDLES_COUNT = 'candles_count';

	const BONUS_CAPACITY = 'bonus_capacity';
	const POPULATION_GROWTH = 'bonus_population_growth';

    const BONUS_CONSTRUCTION_UNIT = 'bonus_construction_unit';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_BasicLevel
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Имя таблицы в БД для отображения.
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
	public function pk() { return 'level_id'; }

	/**
	 * Распаковка бонусов из строки.
	 *
	 * @param string $bonuses
	 * @return mixed
	 * @throws ErrorException
	 */
	public static function unpackingBonuses($bonuses)
	{
		if(!is_string($bonuses))
			throw new ErrorException('Parameter `bonuses` must by string type');
		$unpackBonuses = unserialize($bonuses);
		return $unpackBonuses;
	}

    /**
     * Получить базовые бонусы для здания по ключу
     *
     * @param $keyBuilding
     * @return building_BasicLevel
     */
    public function findBasicBonusesBuilding($keyBuilding)
    {
        $sql = "SELECT `data_bonus`
               FROM %s as bbl
               INNER JOIN %s as b
                 ON (`bbl`.building_id = `b`.id)
               WHERE `b`.name = '%s'";

        return $this->find($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME, $keyBuilding);
    }
}

