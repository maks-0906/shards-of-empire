<?php
/**
 * Файл содержит расчеты по формулам для различных зданий
 */
class models_CalculationFormulasBuilding
{
    /**
       * Получение экземпляра сущности.
       *
       * @param string $className
       * @return models_CalculationFormulasBuilding
       */
      public static function model($className = __CLASS__)
      {
          return new $className();
      }

    /**
     * Расчитать по формуле количество очков благословения
     * Расчет применяется при внутренних улучшениях.
     * Формула расчитывается (Кол-во благославления: базовый уровень + уровень здания + значение улучшения.)
     *
     * @param $currentBasic
     * @param $buildingLevel
     * @param $bonusImprove
     * @return mixed
     */
    public function calculationBlessing($currentBasic, $buildingLevel, $bonusImprove)
    {
       return $currentBasic + $buildingLevel + $bonusImprove;
    }

}
