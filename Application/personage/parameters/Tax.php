<?php
/**
 * Файл содержит логику сязанную с параметром "НАЛОГ"
 */
class personage_parameters_Tax
{
    /**
     * Базовая величина налоговой ставки 10 ед. серебра с человека
     */
    const BASE_VALUE_TAX_RATE = 10;

    const MAX_TAX = 100;
    const MIN_TAX = 0;

    /**
     * Налоговую ставку можно изменять 1 раз в час
     */
    const MINUTE_CHANGE_TAX_RATE = 60;


    /**
     * Раз в час можно проводить сбор налога
     */
    const LAST_COLLECTION_TAXES = 60;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Tax
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    /**
     * Подсчитать получение количества сербра с налогов у персонажа
     *
     * @param $idPersonage
     * @return mixed|null|string
     */
    public function calculateAmountSilverTaxRate($idPersonage)
    {
        $allCityPersonage = personage_City::model()->findCitiesForPersonage($idPersonage);

        $silverResource = '';
        foreach ($allCityPersonage as $city) {

            if ($city->tax >= self::MAX_TAX) {
                $city->tax = self::MAX_TAX;
            }

            $silverResource += $this->formulaObtainingResourcesGoldFromPopulation($city->population, $city->tax);
        }

        return $silverResource;
    }

    /**
     * Формула получения ЗОЛОТО по налогам с населения
     *
     * @param $population
     * @param $tax
     * @return mixed
     */
    public function formulaObtainingResourcesGoldFromPopulation($population, $tax)
    {
      return  ceil($population * (self::BASE_VALUE_TAX_RATE / 100 * $tax));
    }

    /**
     * Валидация корректности налоговой ставки
     *
     * @param $taxRate
     * @return bool
     */
    public function validationValueTaxRate($taxRate)
    {
        if ($taxRate < self::MIN_TAX  OR $taxRate > self::MAX_TAX) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Опредляем можно ли изменять налоговою ставку по времени
     * Налоговую ставку можно изменять 1 раз в час.
     * @param $lastChangeTaxRate
     * @return bool
     */
    public function youCanDetermineTaxRateChange($lastChangeTaxRate)
    {
        $commonTime = 0;
        $currentTime = time();
        $seconds = models_Time::model()->getCountNumberOfSecondsInMinute(self::MINUTE_CHANGE_TAX_RATE);
        $lastTime = strtotime($lastChangeTaxRate);

        if ($lastTime !== false) {
            $commonTime = $lastTime + $seconds;
        }

        //TODO: Необходимо корректное сравнение секунд
        if ($currentTime >= $commonTime) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Обновить поле "НАЛОГ"
     *
     * @param $value
     * @param $idCity
     * @return bool
     */
    public function updateTaxCity($value, $idCity)
    {
        $sqlPart = '`tax` = ' . $value . ', `last_changes_tax_rates` = NOW()';
        return personage_City::model()->updateFieldCity($sqlPart, $idCity);
    }
}
