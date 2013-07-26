<?php
/**
 * Класс представляет собою фабрику ресурсов
 */
abstract class resource_Factory
{
    static public function getResourceFactory($resourceName)
    {
        switch ($resourceName) {
            case resource_Mapper::KEY_RESOURCE_AMBER:
                return resource_resources_Amber::model();
                break;

            case resource_Mapper::KEY_RESOURCE_GOLD:
                return resource_resources_Gold::model();
                break;

            case resource_Mapper::KEY_RESOURCE_BLESSING:
                return resource_resources_Blessing::model();
                break;

            case resource_Mapper::KEY_RESOURCE_TREE:
                return resource_resources_Tree::model();
                break;

            case resource_Mapper::KEY_RESOURCE_STONE:
                return resource_resources_Stone::model();
                break;

            case resource_Mapper::KEY_RESOURCE_IRON:
                return resource_resources_Iron::model();
                break;

            case resource_Mapper::KEY_RESOURCE_FOOD:
                return resource_resources_Food::model();
                break;

            case resource_Mapper::KEY_RESOURCE_BEER:
                return resource_resources_Beer::model();
                break;

            case resource_Mapper::KEY_RESOURCE_WINE:
                return resource_resources_Wine::model();
                break;

            case resource_Mapper::KEY_RESOURCE_TISSUE:
                return resource_resources_Tissue::model();
                break;

            case resource_Mapper::KEY_RESOURCE_CANDLES:
                return resource_resources_Candles::model();
                break;
        }
    }

    /**
     * Получить доходы для модального окна ресурсов
     * @return int
     */
    abstract function toRaiseRevenuesForModalWindowsResources();

}
