<?php
/**
 * Файл содержит калькуляцию выроботки ресурсных зданий за еденицу времени
 */
class building_ResourceStrategy_Production implements ResourceStrategy
{
    public $amountProduct;
    public $seconds;
    public $productionTime;
    public $numberProduction;

    /**
     * Расчет ресурсов производимого зданием за еденицу времени
     *
     * @return float|int
     */
    public function resourceCalculation()
 	{
     //TODO: Разобраться каким образом брать минуты
            /*
               $currentTime = time();

               if ($this->seconds == 0) {
                   $minutes = 3;
               } else {
                   $minutes = ($currentTime - $this->seconds) / 60;
               }
              */
            $minutes = 3;
            $resourceValue = ($this->amountProduct + $this->numberProduction) / $this->productionTime * $minutes;

            if (isset($resourceValue)) {
                return floor($resourceValue);
            } else {
                return 0;
            }
 	}
}
