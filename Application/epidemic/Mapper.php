<?php
/**
 * Файл содержит запросы к базе данных связанные с болезнями и эпидемиями
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
class epidemic_Mapper extends Mapper
{
	const TABLE_NAME = 'epidemic';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return epidemic_Mapper
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Отображение на таблицу в БД.
     * @return string
     */
    public function tableName()
    {
        return self::TABLE_NAME;
    }
}
