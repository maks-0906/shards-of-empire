<?php
/**
 * Файл представляет собою фабрику объектов
 */
abstract class building_Strategy
{
    static public function buildingFactory($buildingName)
    {
        switch($buildingName){
            case building_Mapper::KEY_BUILDING_CASTLE:
            case building_Mapper::KEY_BUILDING_FORTRESS:
            case building_Mapper::KEY_BUILDING_HOUSE:
            case building_Mapper::KEY_BUILDING_BARD_COLLEGE:
            case building_Mapper::KEY_BUILDING_TOURNAMENT_FIELD:
                return building_CommonFunctionality::model();
                break;

            case building_Mapper::KEY_BUILDING_QUARRY:
            case building_Mapper::KEY_BUILDING_SMITHY:
            case building_Mapper::KEY_BUILDING_SAWMILL:
            case building_Mapper::KEY_BUILDING_WINERY:
            case building_Mapper::KEY_BUILDING_WEAVING_WORKSHOP:
            case building_Mapper::KEY_BUILDING_FARM:
            case building_Mapper::KEY_BUILDING_BREWERY:
            case building_Mapper::KEY_BUILDING_APIARY:
                return building_CommonResource::model();
            break;

            case building_Mapper::KEY_BUILDING_WAREHOUSE:
                return building_Warehouse::model();
            break;
        }

    }

    //Метод возвращает расчеты бонусов
    abstract public function run($flag, $measure, $fieldName, $fieldValue, $basic, $improve, $currentFieldBasic = false, $currentFieldValue = false);

    //Повторный расчет бонусов при наличии существующих бонусов
    abstract public function formedSqlPartInitialBonusBuildingRepeat($measure, $fieldName, $fieldValue, $improve, $currentFieldBasic, $currentFieldValue);

   //Первичный расчет при отсутствии у здания бонусов
    abstract public function formedSqlPartInitialBonusBuildingPrimary($measure, $fieldName, $fieldValue, $basic, $improve);
}
