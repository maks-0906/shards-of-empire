<?php
/**
 * Файл содержит специфическую функциональность здания "СКЛАД"
 */

class building_BonusStrategy_Warehouse extends building_BonusStrategy
{

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_BonusStrategy_Warehouse
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Проводим перерасчет бонусов
     *
     * @param $measure
     * @param $fieldName
     * @param $fieldValue
     * @param $improve
     * @param $currentFieldBasic
     * @param $currentFieldValue
     * @param $bonusRepeat
     * @return mixed
     */
    public function calculateBonuses($measure, $fieldName, $fieldValue, $improve, $currentFieldBasic, $currentFieldValue, &$bonusRepeat)
    {
        switch ($measure) {
            case building_BasicLevel::MEASURE_UNIT:
                $bonusRepeat[$fieldName] = array($currentFieldValue + $currentFieldBasic + $improve + round($currentFieldBasic + $improve * $fieldValue / 100));
                $bonusRepeat[$fieldName]['basic'] = $currentFieldBasic + $improve;
                break;

            case building_BasicLevel::MEASURE_RESOURCE:
                $bonusRepeat[$fieldName] = array($currentFieldValue + $currentFieldBasic + $improve + round($currentFieldBasic + $improve * $fieldValue / 100));
                $bonusRepeat[$fieldName]['basic'] = $currentFieldBasic + $improve;
                break;
        }

        return $bonusRepeat;
    }
}