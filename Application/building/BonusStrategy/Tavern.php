<?php
/**
 * Файл содержит расчеты бонусов для здания "Таверна"
 */
class building_BonusStrategy_Tavern extends building_BonusStrategy
{
    /**
        * Получение экземпляра сущности.
        *
        * @param string $className
        * @return building_BonusStrategy_Tavern
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
           return false;
       }

    public function calculateBonusesOfMissingFields(&$bonusRepeat, $baseBonus, $currentData)
    {
       foreach($currentData as $field => $value) {//var_dump($field);
           if ($baseBonus[$field]) {
             $bonusRepeat[$field]['basic'] = $currentData[$field]['basic'] + $baseBonus[$field]['improve'];
           }
       }
    }
}
