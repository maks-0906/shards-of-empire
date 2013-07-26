<?php
/**
 * Файл представляет собою фабрику объектов
 */
abstract class building_BonusStrategy
{
    static public function buildingFactory($buildingName)
    {
        switch ($buildingName) {
            case building_Mapper::KEY_BUILDING_CASTLE:
            case building_Mapper::KEY_BUILDING_FORTRESS:
            case building_Mapper::KEY_BUILDING_HOUSE:
            case building_Mapper::KEY_BUILDING_BARD_COLLEGE:
            case building_Mapper::KEY_BUILDING_TOURNAMENT_FIELD:
                return building_BonusStrategy_CommonFunctionality::model();
                break;

            case building_Mapper::KEY_BUILDING_QUARRY:
            case building_Mapper::KEY_BUILDING_SMITHY:
            case building_Mapper::KEY_BUILDING_SAWMILL:
            case building_Mapper::KEY_BUILDING_WINERY:
            case building_Mapper::KEY_BUILDING_WEAVING_WORKSHOP:
            case building_Mapper::KEY_BUILDING_FARM:
            case building_Mapper::KEY_BUILDING_BREWERY:
            case building_Mapper::KEY_BUILDING_APIARY:
                return building_BonusStrategy_CommonResource::model();
                break;

            case building_Mapper::KEY_BUILDING_WAREHOUSE:
                return building_BonusStrategy_Warehouse::model();
                break;

            case building_Mapper::KEY_BUILDING_HOUSE_LEKAR:
                return building_BonusStrategy_HouseLekar::model();
                break;

            case building_Mapper::KEY_BUILDING_TAVERN:
                return building_BonusStrategy_Tavern::model();
                break;

            case building_Mapper::KEY_BUILDING_SACRED_GROVE:
                return building_BonusStrategy_SacredGrove::model();
                break;
        }

    }

    //Метод возвращает расчеты бонусов с внутренними улучшениями
    public function runImprove($measure, $fieldName, $fieldValue, $currentFieldBasic, $currentFieldValue, &$bonusRepeat)
    {
        return $this->calculateBonusesImprove($measure, $fieldName, $fieldValue, $currentFieldBasic, $currentFieldValue, $bonusRepeat);
    }

    //Добовляем к бонусам значения  внутреннего исследования
    abstract public function calculateBonusesImprove($measure, $fieldName, $fieldValue, $currentFieldBasic, $currentFieldValue, &$bonusRepeat);

}
