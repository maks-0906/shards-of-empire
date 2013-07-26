<?php
/**
 * Файл содержит логику связанную с ресурсом персонажа "БЛАГОСЛАВЕНИЕ"
 */
class resource_resources_Blessing extends resource_Factory
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return resource_resources_Blessing
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    /**
     * Получить доходы для модального окна ресурсов
     *
     * @return int
     */
    public function toRaiseRevenuesForModalWindowsResources()
    {
        return personage_ResourceState::NO_VALUE;
    }

}
