<?php
/**
 * Файл содержит запросы к базе данных связанные с образом персонажа.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель отображающая на таблицу в БД, содержащую информацию об образах в системе для создания персонажей.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_Type extends Mapper
{
	const TABLE_NAME = 'type';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Type
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

    /**
     * Получаем все атрибуты для образа персонажа
     * @param $sql
     * @return array {collection personage_Type}
     */
    public function getAttributesTypePersonage($sql)
    {
        return $this->findAll($sql, $this->tableName());
    }

}
