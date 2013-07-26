<?php
/**
 * Файл проводит расчет бонусов для здания "Священная роща"
 */
class building_BonusStrategy_SacredGrove extends building_BonusStrategy
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

      public function calculateBonusesOfMissingFields(&$bonusRepeat, $fieldValue, $levelBuilding, $currentData)
      {var_dump($levelBuilding);

          $field = key($currentData);
          $bonusRepeat[$field] = $currentData[$field]['basic'] + $levelBuilding * ($fieldValue/100);var_dump($bonusRepeat);
     // return hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh;
      }

}
