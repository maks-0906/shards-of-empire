<?php

class personage_Fraction extends Mapper
{
    const TABLE_NAME = 'fractions';

    /**
     * Бонус на все направления 5%. В процессе игры бонус изменению не подлежит.
     */
    const BONUS_FRACTIONS = 5;

    const FRACTIONS_FRANKS = 'franks'; //Франки
    const FRACTIONS_GERMANS = 'germans'; //Германцы
    const FRACTIONS_VANDALS = 'vandals'; //Вандалы
    const FRACTIONS_VISIGOTHS = 'visigoths'; //Вастготы

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Fraction
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
     * Получаем все фракции
     * @param $sql
     * @return array {collection personage_Fraction}
     */
    public function getAttributesFractionsPersonage($sql = false)
    {
        $sql = ($sql == false) ? "SELECT * FROM %s" : $sql;
        return $this->findAll($sql, $this->tableName());
    }

    /**
     * Получение всех идентификторов фракций
     * @return array {collection personage_Fraction}
     */
    public function getAllIdFrations()
    {
        return $this->findAll("SELECT `id` FROM %s ORDER BY `id` ASC", $this->tableName());
    }


    /**
     * Получить ключ перевод названия фракции для персонажа
     *
     * @param $idPersonage
     * @return personage_Fraction
     */
    public function findFractionsPersonage($idPersonage)
    {
        $sql = "SELECT `f`.name
                FROM %1\$s as f
                INNER JOIN %2\$s as ps
                  ON (`f`.id = `ps`.fraction_id)
                WHERE `ps`.id_personage = %3\$d";

        return $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idPersonage);
    }

    /**
     * Проверяем относиттся ли персонаж к фракции "ФРАНКИ"
     * @param $idPersonage
     * @return bool
     */
    public function isFractionsFranks($idPersonage)
    {
        $fractions = $this->findFractionsPersonage($idPersonage);

        if ($fractions->name == self::FRACTIONS_FRANKS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверяем относиттся ли персонаж к фракции "Германцы"
     *
     * @param $idPersonage
     * @return bool
     */
    public function isFractionsGermans($idPersonage)
    {
        $fractions = $this->findFractionsPersonage($idPersonage);

        if ($fractions->name == self::FRACTIONS_GERMANS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверяем относиттся ли персонаж к фракции "Вандалы"
     *
     * @param $idPersonage
     * @return bool
     */
    public function isFractionsVandals($idPersonage)
    {
        $fractions = $this->findFractionsPersonage($idPersonage);

        if ($fractions->name == self::FRACTIONS_VANDALS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Проверяем относиттся ли персонаж к фракции "Вастготы"
     *
     * @param $idPersonage
     * @return bool
     */
    public function isFractionsVisigoths($idPersonage)
    {
        $fractions = $this->findFractionsPersonage($idPersonage);

        if ($fractions->name == self::FRACTIONS_VISIGOTHS) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Подсчитать бонусы для ресурсов если фракция (Вастготы) персонажа
     *
     * @param $numberProducts
     * @return mixed
     */
    public function calculateBonusesForResourcesFractionsVisigoths($numberProducts)
    {
        return ceil(($numberProducts / 100) * self::BONUS_FRACTIONS);
    }
}
