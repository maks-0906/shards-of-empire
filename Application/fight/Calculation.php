<?php
/**
 * Класс содержит логику расчетов для боя юнитов
 */
class fight_Calculation extends fight_Mapper
{
    const NO_VALUES = 0;
    const NO_LIFE = 0;

    const STATUS_ROUND = 'round';


    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return fight_Calculation
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    /**
     * Расчёт показателей с учётом всех доступных бонусов сторон
     *
     * @param $units
     * @param $idPersonage
     * @return array
     */
    public function calculationIndicatorsPersonages($units, $idPersonage)
    {
        // Инициализация переменных
        $result = array(
            "squad" => unit_UnitsMoving::ACCESSORY_LOCATIONS_PERSONAGES,
            "idPersonage" => $idPersonage,
            "life" => self::NO_VALUES,
            "attack" => self::NO_VALUES,
            "defense" => self::NO_VALUES
        );

        foreach ($units as $unit) {
            $indicators = unit_Characteristic::model()->getUnitIndicatorsWithAllBonusByIdUnitAndIdPersonage($unit->unit_id, $unit->personage_id);
            $result["defense"] += $indicators["protection"] * $unit->count;
            $result["life"] += $indicators["life"] * $unit->count;
            $result["attack"] += $indicators["attack"] * $unit->count;
        }

        return $result;
    }


    /**
     * Подсчитать характеристики разбойников
     *
     * @param $robbers
     * @return mixed
     */
    public function calculationIndicatorsRobber($robbers)
    {
        // Инициализация переменных
        $result = array(
            "squad" => unit_UnitsMoving::ACCESSORY_LOCATIONS_ROBBERS,
            "idPersonage" => unit_UnitsMoving::NOT_PERSONAGES,
            "life" => self::NO_VALUES,
            "attack" => self::NO_VALUES,
            "defense" => self::NO_VALUES
        );

        foreach ($robbers as $unit) {
            $indicators = unit_Characteristic::model()->findUnitCharacteristicByIdUnit($unit->id_unit);

            $result["defense"] += $indicators->protection * $unit->count_robbers;
            $result["life"] += $indicators->life * $unit->count_robbers;
            $result["attack"] += $indicators->attack * $unit->count_robbers;
        }

        return $result;
    }

    /**
     * Расчет (механика) боя между войском отряда A и войском отряда B
     *
     * @param array $arUnitsCharacteristicSquad_A - массив характеристик войска отряда A (наносит удар первым)
     * @param array $arUnitsCharacteristicSquad_B - массив характеристик войска отряда B
     *
     *  Структура массива
     * "idPersonage" => "4" - ИД отряда, кому принадлежит войско
     * "life" => "1500" - суммарная жизнь войска
     * "attack" => "1500" - суммарная атака войска
     * "defense" => "1500" - суммарная защита войска
     *
     * @return array(
     * "status" => ИД победившего отряда || Ничья (FIGHT_RESULT_DEAD_HEAT),
     * "life" => array(
     *         "ИД перонажа A" => значение жизни, для расчета убитых,
     *         "ИД перонажа B" => значение жизни, для расчета убитых
     *     )
     * )
     */
    public function calculationFightMechanics($arUnitsCharacteristicSquad_A, $arUnitsCharacteristicSquad_B)
    {
        // Цикл раундов боя
        for ($i = 1; $i <= self::FIGHT_MAX_NUMBER_ROUNDS; $i++) {

            // Атаку наносит войско отряда A
            $arUnitsCharacteristicSquad_B["life"] =
                $arUnitsCharacteristicSquad_B["life"]
                    - ($arUnitsCharacteristicSquad_A["attack"] - $arUnitsCharacteristicSquad_B["defense"]);

            // Атаку наносит войско отряда B
            $arUnitsCharacteristicSquad_A["life"] =
                $arUnitsCharacteristicSquad_A["life"]
                    - ($arUnitsCharacteristicSquad_B["attack"] - $arUnitsCharacteristicSquad_A["defense"]);

            if ($arUnitsCharacteristicSquad_B["life"] <= self::NO_LIFE &&
                $arUnitsCharacteristicSquad_A["life"] <= self::NO_LIFE
            ) {
                // Убиты оба - статус ничья
                // Возврящаем оставшийся уровень жизни для обоих - 0
                return array(
                    "squad" => $arUnitsCharacteristicSquad_A["squad"],
                    "squad" => $arUnitsCharacteristicSquad_B["squad"],
                    "status" => self::FIGHT_RESULT_DEAD_HEAT,
                    "status" => self::FIGHT_RESULT_DEAD_HEAT,
                    "life" => array(
                        $arUnitsCharacteristicSquad_A["idPersonage"] => self::NO_LIFE,
                        $arUnitsCharacteristicSquad_B["idPersonage"] => self::NO_LIFE
                    )
                );
            } elseif ($arUnitsCharacteristicSquad_B["life"] <= self::NO_LIFE) {

                // Убито войско отряда B, победило войско отряда A
                // Возврящаем оставшийся уровень жизни для войска отряда B - 0
                // для войска отряда A - оставшийся уровень жизни
                return array(
                    "squad" => $arUnitsCharacteristicSquad_A["squad"],
                    "status" => $arUnitsCharacteristicSquad_A["idPersonage"],
                    "life" => array(
                        $arUnitsCharacteristicSquad_A["idPersonage"] => $arUnitsCharacteristicSquad_A["life"],
                        $arUnitsCharacteristicSquad_B["idPersonage"] => self::NO_LIFE
                    )
                );
            } elseif ($arUnitsCharacteristicSquad_A["life"] <= self::NO_LIFE) {

                // Убито войско отряда A, победило войско отряда B
                // Возврящаем оставшийся уровень жизни для войска отряда A - 0
                // для войска отряда B - оставшийся уровень жизни
                return array(
                    "squad" => $arUnitsCharacteristicSquad_B["squad"],
                    "status" => $arUnitsCharacteristicSquad_B["idPersonage"],
                    "life" => array(
                        $arUnitsCharacteristicSquad_A["idPersonage"] => self::NO_LIFE,
                        $arUnitsCharacteristicSquad_B["idPersonage"] => $arUnitsCharacteristicSquad_B["life"]
                    )
                );
            } else {

                // Если обе стороны живы, продолжаем сражатся
                continue;
            }
        }

        // После всех раундов, если разница между жизнью войск
        // не превышает self::FIGHT_LIFE_DIFFERENCE - то объявляется ничья
        if (
            (abs($arUnitsCharacteristicSquad_B["life"] - $arUnitsCharacteristicSquad_A["life"])
            / ($arUnitsCharacteristicSquad_B["life"] + $arUnitsCharacteristicSquad_A["life"])
            * 100) < self::FIGHT_LIFE_DIFFERENCE
        ) {
            return array(
                "squad" => $arUnitsCharacteristicSquad_A["squad"],
                "squad" => $arUnitsCharacteristicSquad_B["squad"],
                "status" => self::FIGHT_RESULT_DEAD_HEAT,
                "life" => array(
                    $arUnitsCharacteristicSquad_A["idPersonage"] => $arUnitsCharacteristicSquad_A["life"],
                    $arUnitsCharacteristicSquad_B["idPersonage"] => $arUnitsCharacteristicSquad_B["life"]
                )
            );
        } elseif ($arUnitsCharacteristicSquad_B["life"] > $arUnitsCharacteristicSquad_A["life"]) {

            // Победило войско отряда B
            return array(
                "squad" => $arUnitsCharacteristicSquad_B["squad"],
                "status" => $arUnitsCharacteristicSquad_B["idPersonage"],
                "life" => array(
                    $arUnitsCharacteristicSquad_A["idPersonage"] => $arUnitsCharacteristicSquad_A["life"],
                    $arUnitsCharacteristicSquad_B["idPersonage"] => $arUnitsCharacteristicSquad_B["life"]
                )
            );
        } else {

            // Победило войско отряда A
            return array(
                "squad" => $arUnitsCharacteristicSquad_A["squad"],
                "status" => $arUnitsCharacteristicSquad_A["idPersonage"],
                "life" => array(
                    $arUnitsCharacteristicSquad_A["idPersonage"] => $arUnitsCharacteristicSquad_A["life"],
                    $arUnitsCharacteristicSquad_B["idPersonage"] => $arUnitsCharacteristicSquad_B["life"]
                )
            );
        }
    }


    /**
     *  Подсчет количества занимаемого места в казарме юнитами для юнитов персонажа
     * Подсчёт убитых начинается с юнитов с наименьшей жизнью
     * @param array $units - юниты
     * @param $lifeDelta - оставшаяся жизнь
     *
     * @return int - количество очков славы за убитых юнитов
     */
    public function writeOffUnitsAfterFight($units, $lifeDelta)
    {
        if ($lifeDelta == self::NO_VALUES) {
            return self::NO_VALUES;
        }

        //Количество места занимаемое убитыми юнитамю в барраке
        $placeKilledUnits = array();

        //Убито юнитов
        $killing_units = array();

        //Количество юнитов перед началом боя
        $all_units = array();

        //Оставшиеся в живых юниты
        $living_units = array();

        //Оставшихся в живых юнитов для сериализации
        $survivingUnits = array();

        //Если это юниты прибывшего отряда то добавляем юнитам их характеристики
        if ($units[0] instanceof stdClass) {
            $allUnitsCharacteristic = unit_Characteristic::model()->findAllUnitCharacteristic();

            //TODO:Необходимо разработать более оптимизированный скрипт, уменьшить число итераций
            foreach ($allUnitsCharacteristic as $unitsCharacteristic) {

                $i = 0;
                foreach ($units as $unit) {

                    if ($unitsCharacteristic->unit_id == $unit->unit_id) {

                        //Добавляем недостоющие данные для отряда прибывшего отряда
                        $units[$i]->place_barracks = $unitsCharacteristic->place_barracks;
                        $units[$i]->life = $unitsCharacteristic->life;
                        $units[$i]->name_unit = $unitsCharacteristic->name_unit;
                    }

                    $i++;
                }
            }
        }

        //Получаем данные об юнитах
        $i = 0;
        foreach ($units as $unit) {

            // Считаем сколько юнитов нужно списать
            $count = ceil($lifeDelta / $unit->life);

            $all_units[$unit->name_unit] = $unit->count;

            if ($count > $unit->count) {
                $count = $unit->count;
            }

            // Обновляем количество занимаемого места в бараке убитыми юнитами
            $placeKilledUnits[$unit->unit_id] = $count * $unit->place_barracks;

            //Получаем количество юнитов осташихся в живых
            if (($unit->count - $count) > self::NO_VALUES) {
                $survivingUnits[$i] = new stdClass();
                $survivingUnits[$i]->unit_id = $unit->unit_id;
                $survivingUnits[$i]->count = $unit->count - $count;
            }

            $i++;

            $killing_units[$unit->name_unit] = $count;
            $living_units[$unit->name_unit] = $all_units[$unit->name_unit] - $killing_units[$unit->name_unit];

            // Обновляем количество несписанных жизней
            $lifeDelta -= $unit->life * $count;
        }


        //return array('place_killed_units' => array_sum($placeKilledUnits), 'survivingUnits' => $survivingUnits);

        return array('place_killed_units' => array_sum($placeKilledUnits),
                     'list_killing_units' => $killing_units,
                     'sum_killing_units' => array_sum($killing_units),
                     'list_total_units_to_fight' => $all_units,
                     'sum_total_units_to_fight' => array_sum($all_units),
                     'living_units' => $living_units,
                     'surviving_units' => $survivingUnits);
    }

    /**
     * Подсчет количества занимаемого места в казарме юнитами для юнитов разбойников
     * Подсчёт убитых начинается с юнитов с наименьшей жизнью
     *
     * @param array $units - юниты
     * @param $lifeDelta - оставшаяся жизнь
     *
     * @return int - количество очков славы за убитых юнитов
     */
    public function writeOffUnitsRobberAfterFight($units, $lifeDelta)
    {
        if ($lifeDelta == self::NO_VALUES) {
            return array(0 => self::NO_VALUES);
        }

        //TODO:Необходимо разработать более оптимизированный скрипт, уменьшить число итераций
        //Количество места занимаемое убитыми юнитамю в барраке
        $placeKilledUnits = array();

        //Убито юнитов
        $killing_units = array();

        //Количество юнитов перед началом боя
        $all_units = array();

        //Оставшиеся в живых юниты
        $living_units = array();

        $allUnitsCharacteristic = unit_Characteristic::model()->findAllUnitCharacteristic();

        foreach ($allUnitsCharacteristic as $unitsCharacteristic) {
            foreach ($units as $unit) {

                if ($unitsCharacteristic->unit_id == $unit->id_unit) {

                    $all_units[$unitsCharacteristic->name_unit] = $unit->count_robbers;

                    // Считаем сколько юнитов нужно списать
                    $count = ceil($lifeDelta / $unitsCharacteristic->life);

                    if ($count > $unit->count_robbers) {
                        $count = $unit->count_robbers;
                    }

                  $placeKilledUnits[$unit->id_unit] = $count * $unitsCharacteristic->place_barracks;
                  $killing_units[$unitsCharacteristic->name_unit] = $count;
                  $living_units[$unitsCharacteristic->name_unit] = $all_units[$unitsCharacteristic->name_unit] -
                                                                   $killing_units[$unitsCharacteristic->name_unit];

                    // Обновляем количество несписанных жизней
                    $lifeDelta -= $unit->life * $count;
                }
            }
        }

        return array('place_killed_units' => array_sum($placeKilledUnits),
                     'list_killing_units' => $killing_units,
                     'sum_killing_units' => array_sum($killing_units),
                     'list_total_units_to_fight' => $all_units,
                     'sum_total_units_to_fight' => array_sum($all_units),
                     'living_units' => $living_units);
    }

    /**
     * Посчитать оставшееся количество жизни после боя
     *
     * @param int $lifeBefore - уровень жизни армии до сражения
     * @param int $lifeAfter - уровень жизни армии после сражения
     * @return bool
     */
    public function calculateDifferenceOfLifeAfterFightUnits($lifeBefore, $lifeAfter)
    {
        // Разница жизни, которую необходимо списать
        $lifeDelta = $lifeBefore - $lifeAfter;

        // Если армию не повредило - выходим
        if ($lifeDelta == self::NO_VALUES) {
            return self::NO_VALUES;
        } else {
            return $lifeDelta;
        }
    }

}
