<?php
/**
 * Файл содержит логику связанную с ресурсом персонажа "ЗОЛОТО"
 */
class resource_resources_Gold extends resource_Factory
{
    /**
     * Значение сессии (personage_id)
     * @var int
     */
    public $idPersonage;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return resource_resources_Gold
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Инициализация первоначальных параметров сущности.
     */
    public function init()
    {
        $this->idPersonage = Auth::getIdPersonage();
    }

    /**
     * Получить доходы для модального окна ресурсов
     *
     * @return int
     */
    public function toRaiseRevenuesForModalWindowsResources()
    {
        $this->init();
        return ceil(personage_parameters_Tax::model()->calculateAmountSilverTaxRate($this->idPersonage));
    }

}
