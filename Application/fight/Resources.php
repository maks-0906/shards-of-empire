<?php
/**
 * Класс содержит логику по подсчету и распределению ресурсов
 */
class fight_Resources extends fight_Mapper
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return fight_Resources
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

}
