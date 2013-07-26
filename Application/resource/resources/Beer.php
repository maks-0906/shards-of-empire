<?php
/**
 * Файл содержит логику связанную с ресурсом персонажа "ПИВО"
 */
class resource_resources_Beer extends resource_Factory
{
    const NO_BARRELS_BEER = 0;

    private $_building;
    private $_idCity;
    private $_nameResource;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return resource_resources_Beer
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

    /**
     * Расчет преступности, счастья при употреблении пива
     *
     * Механика развития преступности на примере пива:
     * Потребление пива:
     * 1 бочка в день – преступность 2, счастье 4
     * 3 бочки в день – преступность 6, счастье 12
     *
     * @param $numberBarrelsBeer
     * @return array|int
     */
    public function calculationCrimeAndHappinessInConsumptionOfBeer($numberBarrelsBeer)
    {
        $numberCrime = 0;
        $numberHappiness = 0;

        if ($numberBarrelsBeer > self::NO_BARRELS_BEER) {

            $numberCrime = $numberBarrelsBeer * 2;
            $numberHappiness = $numberBarrelsBeer * 4;

        } else {
            return self::NO_BARRELS_BEER;
        }

        return array('happiness' => $numberHappiness, 'crime' => $numberCrime);
    }
}
