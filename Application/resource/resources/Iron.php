<?php
/**
 * Файл содержит логику связанную с ресурсом персонажа "ЖЕЛЕЗО"
 */
class resource_resources_Iron extends resource_Factory
{
    private $_building;
    private $_idCity;
    private $_nameResource;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return resource_resources_Iron
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    public function setBuilding($building)
    {
        $this->_building = $building;
    }

    public function getBuilding()
    {
        return $this->_building;
    }

    public function setCity($idCity)
    {
        $this->_idCity = $idCity;
    }

    public function setNameResource($nameResource)
    {
        $this->_nameResource = $nameResource;
    }

    public function getCity()
    {
        return $this->_idCity;
    }

    public function getNameResource()
    {
        return $this->_nameResource;
    }

    /**
     * Получить доходы для модального окна ресурсов
     *
     * @return int
     */
    public function toRaiseRevenuesForModalWindowsResources()
    {
        return personage_ResourceState::model()->calculateIncomeResource($this->getBuilding(),
            $this->getCity(),
            $this->getNameResource());
    }
}
