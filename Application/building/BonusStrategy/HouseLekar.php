<?php
/**
 * Файл содержит расчеты бонусов для "Дом лекаря"
 */
class building_BonusStrategy_HouseLekar extends building_BonusStrategy
{
    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_BonusStrategy_CommonFunctionality
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
                $bonusRepeat[$fieldName] = array($currentFieldBasic + $fieldValue + $improve);
                $bonusRepeat[$fieldName]['basic'] = $currentFieldBasic;
                break;
        }
        return $bonusRepeat;
    }
}

