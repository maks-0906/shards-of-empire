<?php
/**
 * Файл содержит логику связаную с преступностью
 */
class personage_parameters_Crime
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Crime
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Обновить поле преступность путем его обновления
     *
     * @param $value
     * @param $idCity
     * @return bool
     */
    public function updateValueCrimeCity($value, $idCity)
    {
        $sqlPart = '`crime` = ' . $value . '';
        return personage_City::model()->updateFieldCity($sqlPart, $idCity);
    }
}
