<?php
/**
 * Класс является моделью к таблице содержащую текущие сериализованные данные бонусов зданий
 */
class personage_BuildingBonus extends Mapper
{
    const TABLE_NAME = 'personages_building_bonus_state';

    const NO_VALUE = 0;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_BuildingBonus
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Имя таблицы в БД для отображения.
     * @return string
     */
    public function tableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * Первичный ключ для текущей таблицы.
     * @return string
     */
    public function pk()
    {
        return 'id_personage_building_bonus_state';
    }

    /**
     * Сохранение новой записи для здания с бонусами.
     *
     * @param int $idBuildingPersonage
     * @param string $bonuses
     * @return bool
     * @throws DBException
     */
    public function saveNewBonusesByIdBuildingPersonage($idBuildingPersonage, $bonuses)
    {
        $sql = "INSERT INTO %s
					   (id_building_personage,
						current_data_bonus)
				VALUES (%d, '%s')";

        $result = $this->query($sql,
            self::TABLE_NAME, $idBuildingPersonage, $this->prepareBonusesBeforeSaveInNewRecord($bonuses));

        if ($this->isError())
            throw new DBException('Save new bonuses for building: ' . implode(" : ", $this->getErrors()));

        $affected_rows = $this->getAffectedRows($result);
        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Подготовка массива бонусов к записи в новую строку в БД при создании нового здания.
     * В основном удаляется из массива значение улучшения
     *
     * @param string $bonuses
     * @return string
     */
    private function prepareBonusesBeforeSaveInNewRecord($bonuses)
    {
        $unpackBonuses = unserialize($bonuses);
        foreach ($unpackBonuses as $nameBonus => $bonus)
            unset($unpackBonuses[$nameBonus]['improve']);

        return serialize($unpackBonuses);
    }

    /**
     * Перерасчёт текущих бонусов здания.
     *
     * @param array $baseBonuses
     * @param array $currentBonuses
     * @return array
     */
    public static function calculationCurrentBonusesForBuilding(array $baseBonuses, array $currentBonuses)
    {
        foreach ($currentBonuses as $nameBonus => $dataBonus) {
            // Значение улучшения бонуса из базовых велечин может быть отрицательным числом
            // Тогда плюс на минус получаем минус и бонус вычитается.
            // TODO: проверить правильность утверждения вычитания и сложения бонусов.
            $currentBonuses[$nameBonus]['basic'] = $dataBonus['basic'] + $baseBonuses[$nameBonus]['improve'];
        }

        return $currentBonuses;
    }

    /**
     * Добавить бонусы зданиям, добавляются одновременно различным зданиям
     *
     * @param $bonusInsert
     * @return bool
     */
    public function addBuildingBonuses($bonusInsert)
    {
        $sql_part = '';
        foreach ($bonusInsert as $part) {
            $sql_part .= $part["sql_part"] . ',';
        }

        $partSqlRequest = substr($sql_part, 0, -1);

        $sql = 'INSERT INTO %s
                           (id_personage_building_bonus_state,
    						id_building_personage,
    						current_data_bonus)
    				VALUES' . $partSqlRequest;

        $result = $this->query($sql, self::TABLE_NAME);

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Добавление бонуса для конкретного здания персонажа
     *
     * @param $idBuildingPersonage
     * @param $bonus
     * @return bool
     */
    public function addBonusSpecificBuilding($idBuildingPersonage, $bonus)
    {
        $sql = "INSERT INTO %s
                SET `%s` = NULL,
                     `id_building_personage` = %d,
                     `current_data_bonus` = '%s'";

        $result = $this->query($sql, self::TABLE_NAME, $this->pk(), $idBuildingPersonage, $bonus);

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Обновляем текущие данные бонусов зданий, обновление происходит одновременно по нескольким полям
     *
     * @param $bonus
     * @return bool
     */
    public function upgradeBuildingBonus($bonus)
    {
        $sql_part = '';

        foreach ($bonus as $idPersonageBuilding => $value) {
            $sql_part .= "WHEN id_building_personage = $idPersonageBuilding THEN '" . serialize($value) . "' ";
            $in_formed[] = $idPersonageBuilding;
        }

        $in = implode(',', $in_formed);

        $sql = "UPDATE %s
                SET `current_data_bonus` =  CASE $sql_part END
                WHERE `id_building_personage`
                IN ($in)";

        $result = $this->query($sql, self::TABLE_NAME);

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Поиск всех городов для определённого персонажа.
     *
     * @param int $idPersonage
     * @return array|personage_City[]
     */
    public function findCitiesForPersonage($idPersonage)
    {
        $sql = "SELECT * FROM %s WHERE id_personage=%d";
        return $this->findAll($sql, self::TABLE_NAME, $idPersonage);
    }

    /**
     * Добавление базовых бонусов для зданий
     *
     * @param $constructedBuilding
     * @throws DBException
     */
    public function addNewBuildingBonuses($constructedBuilding)
    {
        foreach ($constructedBuilding as $idBuilding => $idPersonageBuilding) {

            $sql = "INSERT INTO %1\$s
                    SET `%2\$s` = NULL,
                         `id_building_personage` = %3\$d,
                         `current_data_bonus` = (SELECT `data_bonus` FROM %4\$s WHERE `building_id` = %5\$d)";

            $result = $this->query($sql, self::TABLE_NAME, $this->pk(), $idPersonageBuilding,
                building_BasicLevel::TABLE_NAME, $idBuilding);

            $affected_rows = $this->getAffectedRows($result);
            if (empty($affected_rows)) {
                throw new DBException('Not added bonus of building personage ' . $idPersonageBuilding);
            }
        }
    }

    /**
     * Удаление записи бонусов для здания персонажа.
     *
     * @param $idBuildingPersonage
     * @return personage_BuildingBonus
     */
    public function removeBonusesByIdBuildingPersonage($idBuildingPersonage)
    {
        $sql = "DELETE FROM %s WHERE id_building_personage=%d";
        return $this->query($sql, self::TABLE_NAME, $idBuildingPersonage);
    }

    /**
     * Получить текущие бонусы для здания по ключу
     *
     * @param $keyBuilding
     * @param $idCity
     * @return personage_BuildingBonus
     */
    public function findCurrentBonusesForSpecificBuilding($keyBuilding, $idCity)
    {
        $sql = "SELECT `pbbs`.current_data_bonus, `pbbs`.id_building_personage
                FROM %1\$s as pbbs
                INNER JOIN %2\$s as pb
                  ON (`pb`.id_building_personage = `pbbs`.id_building_personage)
                INNER JOIN %3\$s as b
                  ON (`b`.id = `pb`.building_id)
                WHERE `pb`.`city_id` = %4\$d
                AND `b`.name = '%5\$s'
                AND `pb`.status_production = '%6\$s'
                AND `pb`.status_construction = '%7\$s'";

        return $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, building_Mapper::TABLE_NAME,
            $idCity, $keyBuilding, personage_Building::STATUS_PRODUCTION,
            personage_Building::STATUS_CONSTRUCTION_FINISH);
    }

    /**
     * Получить текущие бонусы для здания по ключу с использованием координат города
     *
     * @param $keyBuilding
     * @param $x
     * @param $y
     * @param $idPersonage
     * @return personage_BuildingBonus
     */
    public function findCurrentBonusesForSpecificBuildingOnCoordinatesCity($keyBuilding, $x, $y, $idPersonage)
    {
        $sql = "SELECT `pbbs`.current_data_bonus, `pbbs`.id_building_personage
                FROM %1\$s as pbbs
                INNER JOIN %2\$s as pb
                  ON (`pb`.id_building_personage = `pbbs`.id_building_personage)
                INNER JOIN %3\$s as b
                  ON (`b`.id = `pb`.building_id)
                WHERE `pb`.`city_id` = (SELECT `id` FROM %4\$s WHERE `x_c` = %5\$d AND `y_c` = %6\$d AND `id_personage` = %7\$d)
                AND `b`.name = '%8\$s'
                AND `pb`.status_production = '%9\$s'
                AND `pb`.status_construction = '%10\$s'";

        return $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, building_Mapper::TABLE_NAME,
                                personage_City::TABLE_NAME, $x, $y, $idPersonage, $keyBuilding, personage_Building::STATUS_PRODUCTION,
                                personage_Building::STATUS_CONSTRUCTION_FINISH);
    }

    /**
     * Получить текущие бонусы со зданий производящих счастье
     *
     * @param $idCity
     * @param $idPersonage
     * @return personage_BuildingBonus
     */
    public function findCurrentBonusesForBuildingsProductionHappiness($idCity, $idPersonage)
    {
        $sql = "SELECT `pbbs`.current_data_bonus, `pbbs`.id_building_personage,
                       `pb`.current_level, `b`.name,

                       /*Получаем счастье население для привилегии от уровня титула персонажа*/
                       (SELECT `ppd`.happiness_people_dignity
                        FROM %9\$s as ppd
                        INNER JOIN %10\$s as ps
                          ON (`ppd`.id_dignity = `ps`.id_dignity)
                        WHERE `ps`.id_personage = %11\$d) as privilege_happiness,

                     /*Получаем количество счастья от уровня здания 'КОЛЛЕГИЯ БАРДОВ'*/
                       (SELECT `bd`.number_happiness
                        FROM %12\$s as bd
                        INNER JOIN %2\$s as pb
                          ON (`bd`.level = `pb`.current_level)
                        INNER JOIN %3\$s as b
                          ON (`b`.id = `pb`.building_id)
                        WHERE `b`.name = '%6\$s'
                        AND `pb`.city_id = %4\$d
                        AND `b`.id = `bd`.building_id) as number_happiness_building_bard_college,

                     /*Получаем количество счастья от уровня здания 'ТАВЕРНА'*/
                       (SELECT `bd`.number_happiness
                        FROM %12\$s as bd
                        INNER JOIN %2\$s as pb
                          ON (`bd`.level = `pb`.current_level)
                        INNER JOIN %3\$s as b
                          ON (`b`.id = `pb`.building_id)
                        WHERE `b`.name = '%8\$s'
                        AND `pb`.city_id = %4\$d
                        AND `b`.id = `bd`.building_id) as number_happiness_building_tavern
                 FROM %1\$s as pbbs
                 INNER JOIN %2\$s as pb
                   ON (`pb`.id_building_personage = `pbbs`.id_building_personage)
                 INNER JOIN %3\$s as b
                   ON (`b`.id = `pb`.building_id)
                 WHERE `b`.name IN ('%5\$s', '%6\$s', '%7\$s', '%8\$s')
                 AND `pb`.city_id = %4\$d";

        return $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, building_Mapper::TABLE_NAME,
                           $idCity, building_Mapper::KEY_BUILDING_CASTLE, building_Mapper::KEY_BUILDING_BARD_COLLEGE,
                            building_Mapper::KEY_BUILDING_TOURNAMENT_FIELD, building_Mapper::KEY_BUILDING_TAVERN,
                            personage_parameters_Dignity::TABLE_NAME, personage_State::TABLE_NAME, $idPersonage,
                           building_Development::TABLE_NAME);
    }
}
