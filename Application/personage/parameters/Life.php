<?php
/**
 * Файл содержит методы связанные сжизнью персонажа
 */
class personage_parameters_Life
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Life
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    public function findLifePersonage($idPersonage)
    {
        return array('personage_life' => 20, 'max_life' => 100);
    }
}
