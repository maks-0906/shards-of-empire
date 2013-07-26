<?php
/**
 * Файл содержит методы связанные с БЛАГОСЛАВЛЕНИЕМ
 */
/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */

class personage_parameters_Blessing
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Blessing
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Производство благославления
     *
     * Благославление (a) складывается из:
     *  1) Благославления полученнного в святилище-храме (s);
     *  2) Бонуса привелегии от титула (d);
     * Формула общее благославление: a=s+d
     *
     * @param $currentBonus
     * @param $countBlessing
     */
    public function calculateCurrentBonusesAndCountBlessing($currentBonus, $countBlessing)
    {
       return $currentBonus + $countBlessing;
    }

}
