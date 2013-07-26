<?php
/**
 * Файл содержит методы относящиеся к "СЧАСТЬЮ"
 */
class personage_parameters_Happiness
{
    const MAX_HAPPINESS = 100;
    const NO_HAPPINESS = 0;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Happiness
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }


    /**
     * Опрееделить необходимое количество счастья, что бы не превысить максимальное значение
     *
     * @param $numberHappiness
     * @param $originalValueHappiness
     * @return int
     */
    public function toCountHappinessWithoutExceedingMaximum($numberHappiness, $originalValueHappiness)
    {
        $countHappiness = 0;

        if (self::MAX_HAPPINESS > $numberHappiness) {
            $countHappiness = $numberHappiness + $originalValueHappiness;
        }

        if ($originalValueHappiness == self::MAX_HAPPINESS) {
            return self::NO_HAPPINESS;
        }

        if ($countHappiness > self::MAX_HAPPINESS) {
            $difference = $countHappiness - self::MAX_HAPPINESS;
            return $numberHappiness - $difference;
        } else {
            return $numberHappiness;
        }
    }

    /**
     * Обновить поле счастье путем его увеличения
     *
     * @param $value
     * @param $idCity
     * @return bool
     */
    public function updateIncreaseHappinessCity($value, $idCity)
    {
        $sqlPart = '`happiness` = `happiness` + ' . $value . '';
        return personage_City::model()->updateFieldCity($sqlPart, $idCity);
    }

    /**
     * Обновить поле счастье полученным значением
     *
     * @param $value
     * @param $idCity
     * @return bool
     */
    public function updateHappinessCity($value, $idCity)
    {
        $sqlPart = '`happiness` = ' . $value . '';
        return personage_City::model()->updateFieldCity($sqlPart, $idCity);
    }


    /**
     * Посчитать общее количество параметра счастья
     *
     * На счастье население влияют:
     * Основные:
     * 1) Налоговая ставка;
     * 2) Потребление пива;
     * 3) Здоровье населения;
     * 4) Представления (барды);
     * 5) Турнирное поле.
     * Дополнительные:
     * 4) Привилегия, полученная от титула;
     * 5) Способности рыцарей-советников;
     * 6) Предметы игрока: артефакты, вещи;
     * 7) Здания;
     * 8) Место нахождение персонажа
     * @param $buildings
     * @param $tax
     * @return int
     * @throws ErrorException
     */
    public function calculateTotalNumberOfParameterHappiness($buildings, $tax)
    {
        if ($tax == NULL) throw new ErrorException('No parameters city tax');
        if ($buildings == NULL) throw new ErrorException('There is no variable buildings');

        $numberHappiness = 0;
        foreach ($buildings->result as $building) {

            //Получаем бонусы счастья со здания "ЗАМОК"
            if ($building['name'] == building_Mapper::KEY_BUILDING_CASTLE AND
                $building['current_level'] > personage_Building::NO_LEVEL_BUILDING
            ) {
                $unserializeBonus = unserialize($building['current_data_bonus']);
                $numberHappiness += $unserializeBonus['bonus_happiness']['basic'];
            }

            //Получаем бонусы счастья со здания "КОЛЛЕГИЯ БАРДОВ"
            if ($building['name'] == building_Mapper::KEY_BUILDING_BARD_COLLEGE AND
                $building['current_level'] > personage_Building::NO_LEVEL_BUILDING
            ) {
                $unserializeBonus = unserialize($building['current_data_bonus']);
                $numberHappiness += $unserializeBonus['bonus_happiness']['basic'];
            }

            //Получаем бонусы счастья со здания "ТУРНИРНОЕ ПОЛЕ"
            if ($building['name'] == building_Mapper::KEY_BUILDING_TOURNAMENT_FIELD AND
                $building['current_level'] > personage_Building::NO_LEVEL_BUILDING
            ) {
                $unserializeBonus = unserialize($building['current_data_bonus']);
                $numberHappiness += $unserializeBonus['bonus_happiness']['basic'];
            }

            //Получаем бонусы счастья со здания "ТАВЕРНА"
            if ($building['name'] == building_Mapper::KEY_BUILDING_TAVERN AND
                $building['current_level'] > personage_Building::NO_LEVEL_BUILDING
            ) {
                $unserializeBonus = unserialize($building['current_data_bonus']);
                $numberHappiness += $unserializeBonus['bonus_happiness']['basic'];

                //Получаем количество счасться от количества бочек пива
                $resultConsumptionBeer = resource_resources_Beer::model()->calculationCrimeAndHappinessInConsumptionOfBeer(
                    $unserializeBonus['number_barrels_beer']['basic']
                );
                $numberHappiness += $resultConsumptionBeer['happiness'];
            }
        }

        //Добавляем количество счасться от уровня титула персонажа
        if ($buildings->privilege_happiness > self::NO_HAPPINESS) {
            $numberHappiness += $buildings->privilege_happiness;
        }

        //Добавляем количество счастья от уровня здания "КОЛЛЕГИЯ БАРДОВ"
        if ($buildings->number_happiness_building_bard_college != NULL) {
            $numberHappiness += $buildings->number_happiness_building_bard_college;
        }

        //Добавляем количество счастья от уровня здания "ТАВЕРНА"
        if ($buildings->number_happiness_building_tavern != NULL) {
            $numberHappiness += $buildings->number_happiness_building_tavern;
        }

        //Изменяем количество счастья от налоговой ставки
        if ($tax > personage_parameters_Tax::MIN_TAX) {
            $numberHappiness -= ceil($tax * 0.5);
        }

        //Проводим сверку с допустимым минимальным количеством счастья
        if ($numberHappiness < self::NO_HAPPINESS) {
            return self::NO_HAPPINESS;
        }

        //Проводим сверку с допустимым макимальным количеством счастья
        if ($numberHappiness > self::MAX_HAPPINESS) {
            return self::MAX_HAPPINESS;
        } else {
            return $numberHappiness;
        }
    }
}
