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
class building_LevelUpgrade extends Mapper
{
	const TABLE_NAME = 'building_level_upgrade';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_LevelUpgrade
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
	public function pk() { return 'id_level_upgrade'; }

    /**
     * Получить максимальный уровень здания по имени здания
     *
     * @param $nameBuilding
     * @return building_LevelUpgrade
     */
    public function findMaxLevelBuilding($nameBuilding)
    {
        $sql = "SELECT max(`blu`.max_level) as max_level_building
               FROM %1\$s as blu
               INNER JOIN %2\$s as b
                 ON(`b`.id = `blu`.id_building)
               WHERE `b`.name = '%3\$s'";

        return $this->find($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME, $nameBuilding);
    }
}