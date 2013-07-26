<?php

class personage_Religion extends Mapper
{
    const TABLE_NAME = 'religion';

    /**
     * Величина бонуса  от религии 5% и не изменяется в течение игры.
     */
    const BONUS_RELIGION = 5;
    const NO_VALUE = 0;

    const RELIGION_TREE_LIMIT = 'tree_limit'; //Древо предела
    const RELIGION_FERTILITY_GODDESS = 'fertility_goddess'; //Богиня плодородия Арианрод
    const RELIGION_SACRED_SUN = 'sacred_sun'; //Священное солнце
    const RELIGION_SPIRITS_EARTH = 'spirits_earth'; //Духи земли

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Religion
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Отображение на таблицу в БД.
     * @return string
     */
    public function tableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * Получаем все религии
     * @param $sql
     * @return array {collection personage_Religion}
     */
    public function getAttributesReligionPersonage($sql)
    {
        return $this->findAll($sql, $this->tableName());
    }

    /**
     * Получить ключ перевод названия религии для персонажа
     *
     * @param $idPersonage
     * @return personage_Fraction
     */
    public function findReligionPersonage($idPersonage)
    {
        $sql = "SELECT `r`.name
                    FROM %1\$s as r
                    INNER JOIN %2\$s as ps
                      ON (`r`.id = `ps`.religion_id)
                    WHERE `ps`.id_personage = %3\$d";

        return $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idPersonage);
    }

    /**
     * Определяем принадлежность персонажа к религии, а также принадлежность ресурса к религии, для религии "Древо предела"
     *
     * @param $nameReligion
     * @param $nameResources
     * @return bool
     */
    public function isReligionTreeLimitAndResources($nameReligion, $nameResources)
    {
        if ($nameReligion == self::RELIGION_TREE_LIMIT AND
            $nameResources == resource_Mapper::KEY_RESOURCE_TREE
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Определяем принадлежность персонажа к религии, а также принадлежность ресурса к религии, для религии "Богиня плодородия Арианрод"
     *
     * @param $nameReligion
     * @param $nameResources
     * @return bool
     */
    public function isReligionFertilityGoddessAndResources($nameReligion, $nameResources)
    {
        if ($nameReligion == self::RELIGION_FERTILITY_GODDESS AND
            $nameResources == resource_Mapper::KEY_RESOURCE_FOOD
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Определяем принадлежность персонажа к религии, а также принадлежность ресурса к религии, для религии "Священное солнце"
     *
     * @param $nameReligion
     * @param $nameResources
     * @return bool
     */
    public function isReligionSacredSunAndResources($nameReligion, $nameResources)
    {
        if ($nameReligion == self::RELIGION_SACRED_SUN AND
            $nameResources == resource_Mapper::KEY_RESOURCE_IRON
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Определяем принадлежность персонажа к религии, а также принадлежность ресурса к религии, для религии "Духи земли"
     *
     * @param $nameReligion
     * @param $nameResources
     * @return bool
     */
    public function isReligionSpiritsEarthAndResources($nameReligion, $nameResources)
    {
        if ($nameReligion == self::RELIGION_SPIRITS_EARTH AND
            $nameResources == resource_Mapper::KEY_RESOURCE_STONE
        ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Подсчитать бонусы для ресурсов в зависимости от религии персонажа
     *
     * @param $numberProducts
     * @return mixed
     */
    public function calculateBonusesForResourcesDependingOnPersonageOfReligion($numberProducts)
    {
        return ceil(($numberProducts / 100) * self::BONUS_RELIGION);
    }

    /**
     * Подсчитать ресурсы с бонусами от религии персонажа
     *
     * @param $numberProducts
     * @param $nameReligion
     * @param $nameResources
     * @return mixed
     */
    public function calculateNumberResourcesOfReligion($numberProducts, $nameReligion, $nameResources)
    {
        $doneReligionTreeLimit = $this->isReligionTreeLimitAndResources($nameReligion, $nameResources);

        if ($doneReligionTreeLimit === true) {
            return $this->calculateBonusesForResourcesDependingOnPersonageOfReligion($numberProducts);
        }

        $doneReligionFertilityGoddess = $this->isReligionFertilityGoddessAndResources($nameReligion, $nameResources);

        if ($doneReligionFertilityGoddess === true) {
            return $this->calculateBonusesForResourcesDependingOnPersonageOfReligion($numberProducts);
        }

        $doneReligionSacredSun = $this->isReligionSacredSunAndResources($nameReligion, $nameResources);

        if ($doneReligionSacredSun === true) {
            return $this->calculateBonusesForResourcesDependingOnPersonageOfReligion($numberProducts);
        }

        $doneReligionSpiritsEarth = $this->isReligionSpiritsEarthAndResources($nameReligion, $nameResources);

        if ($doneReligionSpiritsEarth === true) {
            return $this->calculateBonusesForResourcesDependingOnPersonageOfReligion($numberProducts);
        }

        return self::NO_VALUE;
    }
}
