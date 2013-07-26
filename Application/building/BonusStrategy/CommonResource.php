<?php
/**
 * Класс подсчитывает бонус для ресурсных зданий с одинаковой системой подсчета
 */
class building_BonusStrategy_CommonResource extends building_BonusStrategy
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_BonusStrategy_CommonResource
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
                $bonusRepeat[$fieldName] = array($currentFieldValue + $fieldValue + $currentFieldBasic + $improve);
                $bonusRepeat[$fieldName]['basic'] = $currentFieldBasic + $improve;
                break;

            case building_BasicLevel::MEASURE_MINUTE:
                $bonusRepeat[$fieldName] = array($currentFieldBasic - $fieldValue - $improve);
                $bonusRepeat[$fieldName]['basic'] = $currentFieldBasic - $improve;
                break;
        }


        return $bonusRepeat;
    }

}
