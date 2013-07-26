<?php
/*
 * Файл содержит первичные данные ресурсов и юнитов при создании персонажа
 */
class data_InitializePersonage
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return data_InitializePersonage
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Первичные данные ресурсов
     * @var array
     */
    private $_initializePersonageResources = array(resource_Mapper::KEY_RESOURCE_GOLD => 30000000,
                                                   resource_Mapper::KEY_RESOURCE_FOOD => 600,
                                                   resource_Mapper::KEY_RESOURCE_TISSUE => 300,
                                                   resource_Mapper::KEY_RESOURCE_STONE => 1000,
                                                   resource_Mapper::KEY_RESOURCE_IRON => 1000,
                                                   resource_Mapper::KEY_RESOURCE_TREE => 1000);

    /**
     * Первичные юниты для гарнизона
     * @var array
     */
    private $_initializeUnits = array(unit_Mapper::KEY_UNIT_MILITIAMAN => 10,
                                      unit_Mapper::KEY_UNIT_ARCHER => 4,
                                      unit_Mapper::KEY_UNIT_ARBALESTER => 5,
                                      unit_Mapper::KEY_UNIT_AMAZON => 2,
                                      unit_Mapper::KEY_UNIT_CHOSEN_SWORDSMAN => 2,
                                      unit_Mapper::KEY_UNIT_WARDEN_CASTLE => 2,
                                      unit_Mapper::KEY_UNIT_HORSE_QUARDSMAN => 3);

    /**
     * Получить первичные данные ресурсов
     *
     * @return array
     */
    public function getInitializePersonageResources()
    {
        return $this->_initializePersonageResources;
    }

    /**
     * Получить первичных юнитов для гарнизона
     *
     * @return array
     */
    public function getInitializeUnits()
    {
        return $this->_initializeUnits;
    }
}
?>