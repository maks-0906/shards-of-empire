<?php
/**
 * Файл содержит запросы к базе данных связанные с болезнями в городе
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

class personage_CityEpidemic extends Mapper
{
    const TABLE_NAME = 'personages_cities_epidemic';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_CityEpidemic
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
    public function pk()
    {
        return 'id_personages_cities_epidemic';
    }

}
