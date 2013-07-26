<?php
/**
 * Файл содержит класс модель, отображающую на таблицу юнитов, находящихся в определённой локации.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель, отображающаяся на таблицу юнитов, находящихся в определённой локации.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_UnitLocation extends Mapper
{

    const TABLE_NAME = 'personages_units_locations';

    const NOT_UNIT = 0;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_UnitLocation
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
     * Изменение количества определённых юнитов в определённой локации.
     *
     * @param array $units
     * @param $idPersonage
     * @return bool
     * @throws StatusErrorException
     * @throws DBException
     */
    public function writeDownCountUnitsInLocation(array $units, $idPersonage)
    {
        $result = null;
        $sql = "UPDATE %s SET `count` = `count` - %d WHERE `location_id`=%d AND `unit_id`=%d";
        foreach ($units as $u) {
            if ($u->location_id == null)
                throw new StatusErrorException('Parameter `location` not defined!', $this->oStatus->main_errors);

            if ($u->unit_id == null)
                throw new StatusErrorException('Parameter `unit_id` not defined!', $this->oStatus->main_errors);

            if ($u->count == null)
                throw new StatusErrorException('Parameter `count` not defined!', $this->oStatus->main_errors);

            $unitLocation = $this->findUnitPersonageOLocationOnById($u->unit_id, $u->location_id, $idPersonage);

            //Проверяем достаточное количество юнитов в локации
            if (($unitLocation->count - $u->count) < self::NOT_UNIT) {
                throw new StatusErrorException('Insufficient number of units', $this->oStatus->main_errors);
            }

            $result = $this->query($sql, self::TABLE_NAME, $u->count, $u->location, $u->unit_id);
            if ($result->isError())
                throw new DBException('Same error in query in function `writeDownCountUnitsInLocation`');
        }

        if ($result != NULL) {
            $affected_rows = $this->getAffectedRows($result);
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    /**
     * Удаляем проигравший отряд с локации и добавляем отряд который выиграл бой
     *
     * @param array $units
     * @param $idPersonageInitiation - ID персонажа выигравшего бой
     * @param $idLocation
     * @param $x
     * @param $y
     * @return bool
     * @throws StatusErrorException
     */
    public function addUnitsToLocationAfterFight(array $units, $idPersonageInitiation, $idLocation, $x, $y)
    {
        //Уничтожаем отряд находящийся в локации
        $doneDeleteUnitsOfLocation = personage_UnitLocation::model()->deleteUnitsOnIdLocation($idLocation);

        if ($doneDeleteUnitsOfLocation === true) {

            //Добавляем отряд на локацию
            return $this->placePersonagesInLocationOfUnits($units, $idPersonageInitiation, $idLocation, $x, $y);
        }

        return false;
    }


    /**
     * Добавляем юнитов персонажа в локацию
     *
     * @param array $units
     * @param $idPersonage
     * @param $idLocation
     * @param $x
     * @param $y
     * @return bool
     * @throws StatusErrorException
     * @throws DBException
     */
    public function placePersonagesInLocationOfUnits(array $units, $idPersonage, $idLocation, $x, $y)
    {
        if ($idLocation == NULL)
            throw new StatusErrorException('Parameter `location` not defined!', $this->oStatus->main_errors);

        if ($x == NULL)
            throw new StatusErrorException('Parameter `x` not defined!', $this->oStatus->main_errors);

        if ($y == NULL)
            throw new StatusErrorException('Parameter `y` not defined!', $this->oStatus->main_errors);

        try {
            $resultBegin = $this->begin();

            $result = NULL;
            $sql = "INSERT INTO %s SET `unit_id` = %d, `personage_id` = %d, `location_id`=%d, `count` = %d, `x_l` = %d, `y_l` = %d";

            foreach ($units as $u) {

                if ($u->unit_id == NULL)
                    throw new StatusErrorException('Parameter `unit_id` not defined!', $this->oStatus->main_errors);

                if ($u->count == NULL)
                    throw new StatusErrorException('Parameter `count` not defined!', $this->oStatus->main_errors);

                $result = $this->query($sql, self::TABLE_NAME, $u->unit_id, $idPersonage, $idLocation, $u->count, $x, $y);

                if ($result->isError())
                    throw new DBException('Same error in query in function `placePersonagesInLocationOfUnits`');
            }

            $resultCommit = $this->commit();
        } catch (DBException $e) {
            $this->rollback();
            throw new DBException(
                'placePersonagesInLocationOfUnits : ' . $e->getMessage()
            );
        } catch (StatusErrorException $e) {
            $this->rollback();
            throw new StatusErrorException(
                'placePersonagesInLocationOfUnits : ' . $e->getMessage()
            );
        }

        return $resultCommit;
    }

    /**
     * Изменение количества определённых юнитов в определённой локации.
     *
     * @param array $units
     * @return bool
     * @throws StatusErrorException
     * @throws DBException
     */
    public function writeUpCountUnitsInLocation(array $units)
    {
        $result = null;
        $sql = "UPDATE %s SET `count` = `count` + %d WHERE `location_id`=%d AND `unit_id`=%d";
        foreach ($units as $u) {
            if ($u->location_id == null)
                throw new StatusErrorException('Parameter `location` not defined!', $this->oStatus->main_errors);

            if ($u->unit_id == null)
                throw new StatusErrorException('Parameter `unit_id` not defined!', $this->oStatus->main_errors);

            if ($u->count == null)
                throw new StatusErrorException('Parameter `count` not defined!', $this->oStatus->main_errors);

            $result = $this->query($sql, self::TABLE_NAME, $u->count, $u->location, $u->unit_id);
            if ($result->IsError())
                throw new DBException('Same error in query in function `writeDownCountUnitsInLocation`');
        }

        if ($result != null) {
            $affected_rows = $this->getAffectedRows($result);
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    /**
     * Получение всех юнитов в локации по ИД персонажа и ИД локации
     * в том числе и союзных
     *
     * @param int $idPersonage
     * @param int $idLocation
     * @return personage_UnitLocation
     */
    public function findAllBattleUnitsInLocation($idPersonage, $idLocation)
    {
        $sql = "SELECT pul.`id` as `personage_unit_location_id`, pul.*, uc.*
			    FROM %1\$s as pul, %5\$s as uc
			    WHERE (pul.`personage_id` = %2\$d
				OR pul.`personage_id` IN (
					SELECT `id_personage` FROM `%3\$s` WHERE `guild_id` = (
						SELECT `guild_id` FROM `%3\$s` WHERE `id_personage` = %2\$d AND `guild_id` IS NOT NULL
					)
				)
				)
				AND pul.`location_id` = '%4\$d'
				AND pul.`unit_id` = uc.`unit_id`
			    ORDER BY uc.`life`";

        return $this->findAll($sql, self::TABLE_NAME, $idPersonage, personage_State::TABLE_NAME, $idLocation,
            unit_Characteristic::TABLE_NAME);
    }

    /**
     * Изменение количества определённых юнитов в определённой локации.
     *
     * @param int $id
     * @param int $count
     * @return bool
     * @throws StatusErrorException
     * @throws DBException
     */
    public function setCountUnitsById($id, $count)
    {
        $sql = "UPDATE %s SET `count` = %d WHERE `id` = %d";

        $result = $this->query($sql, self::TABLE_NAME, $count, $id);

        if ($result->isError())
            throw new DBException('Same error in query in function `setCountUnitsById`');

        if ($result != null) {
            $affected_rows = $this->getAffectedRows($result);
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }


    /**
     * Удаление записи о определённых юнитах в определённой локации.
     *
     * @param $id
     * @return bool
     * @throws DBException
     */
    public function deleteUnitsById($id)
    {
        $sql = "DELETE FROM %s WHERE `id`=%d";

        $result = $this->query($sql, self::TABLE_NAME, $id);

        if ($result->isError())
            throw new DBException('Same error in query in function `deleteUnitsById`');

        if ($result != null) {
            $affected_rows = $this->getAffectedRows($result);
            if ($affected_rows > 0) {
                return true;
            } else {
                return false;
            }
        } else
            return false;
    }

    /**
     * Удаление записи о определённых юнитах в определённой локации.
     *
     * @param $idLocation
     * @return bool
     * @throws DBException
     */
    public function deleteUnitsOnIdLocation($idLocation)
    {
        $sql = "DELETE FROM %s WHERE `location_id`=%d";

        $result = $this->query($sql, self::TABLE_NAME, $idLocation);
        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить юнитов по текущему ИД юнита
     *
     * @param int $idUnit- ИД юнита
     * @param int $idLocation
     * @param int $idPersonage
     * @return boolean
     */
    public function findUnitPersonageOLocationOnById($idUnit, $idLocation, $idPersonage)
    {
        $sql = "SELECT `pul`.*
                FROM %1\$s as pul
                LEFT JOIN %2\$s as pl
                  ON (`pul`.location_id  = `pl`.id)
                WHERE `pul`.unit_id = %3\$d
                AND `pl`.id = %4\$d
                AND `pl`.personage_id = %5\$d";

        return $this->find($sql, self::TABLE_NAME, personage_Location::TABLE_NAME,
            $idUnit, $idLocation, $idPersonage);
    }
    
    /**
     * changeUnitsCountInLocation
     * Function changed count of personage unit in location. If after changes 
     * count is `0` than we remove row from table
     *
     * @param int $unitId
     * @param int $personageId
     * @param int $x
     * @param int $y
     * @param int $changeCount
     * @return boolean
     */
    public function changeUnitsCountInLocation($unitId, $personageId, $x, $y, $changeCount) 
    {
        $sql = "
            INSERT INTO " . self::TABLE_NAME . "
            SET `unit_id` = %d,
                `personage_id` = %d,
                `x_l` = %d,
                `y_l` = %d,
                `count` = %d
            ON DUPLICATE KEY UPDATE 
                `count` = `count` + %d
        ";
        $result = $this->query($sql, $unitId, $personageId, $x, $y, $changeCount, $changeCount);
        if ($result->IsError())
            throw new DBException('Some error in query in function `changeUnitsCountByUnitId`');
        
        if ($result != null) {
            $affected_rows = $this->getAffectedRows($result);
            if ($affected_rows > 0)
            {
                $sql = "
                    DELETE FROM " . self::TABLE_NAME . "
                    WHERE `unit_id` = %d
                        AND `personage_id` = %d
                        AND `x_l` = %d
                        AND `y_l` = %d
                        AND `count` = 0
                    LIMIT 1
                ";
                $result = $this->query($sql, $unitId, $personageId, $x, $y);
                if ($result->IsError())
                    throw new DBException('Some error in query in function `changeUnitsCountByUnitId`');
            }
            return true;
        }
        return false;
    }

    /**
     * getUnitsInLocation
     * Get units info in location. If $personageId != -1, than return units which
     * belong to personage. If $unitId !=- -1, than return info about single unit.
     *
     * @param int $x
     * @param int $y
     * @param int $personageId, default -1
     * @param int $unitId, default -1
     * @return object
     */
    public function getUnitsInLocation($x, $y, $personageId = -1, $unitId = -1) 
    {
        $sql = "
            SELECT pul.*, u.`name_unit`, u.`combat_type`, uc.`number_transported_cargo` AS carrying
            FROM " . self::TABLE_NAME . " AS pul
            JOIN " . unit_Mapper::TABLE_NAME . " AS u
                ON pul.`unit_id` = u.`id`
            JOIN " . unit_Characteristic::TABLE_NAME . " AS uc
                ON u.`id` = uc.`unit_id`
            WHERE pul.`x_l` = %d
                AND pul.`y_l` = %d
        ";
        
        if ($personageId != -1) 
        {
            $sql .= " AND pul.`personage_id` = " . $personageId;
        }
        
        if ($unitId != -1)
        {
            $sql .= " AND pul.`unit_id` = " . $unitId;
        }

        $result = $this->query($sql, $x, $y);
        if ($result->IsError())
            throw new DBException('Some error in query in function `getUnitsInLocation`');
        
        return $result->__DBResult;
    }
    
    /**
     * getPersonageUnitsByLocations
     * Get personage units info by locations
     *
     * @param int $personageId
     * @return object
     */
    public function getPersonageUnitsByLocations($personageId)
    {
        $sql = "
            SELECT pul.`x_l` AS x, 
                pul.`y_l` AS y, 
                SUM(pul.`count`) AS count,
                pc.`city_name`
            FROM " . self::TABLE_NAME . " AS pul
            LEFT JOIN " . personage_City::TABLE_NAME . " AS pc
                ON pc.`x_c` = pul.`x_l`
                AND pc.`y_c` = pul.`y_l`
            WHERE pul.`personage_id` = %d
            GROUP BY x, y
        ";
        $result = $this->query($sql, $personageId);
        if ($result->IsError())
            throw new DBException('Some error in query in function `getPersonageUnitsByLocations`');
        
        return $result->__DBResult;
    }
    
    /**
     * getPersonageUnits
     * Get personage units sum count in all locations
     *
     * @param int $personageId
     * @return object
     */
    public function getPersonageUnits($personageId)
    {
        $sql = "
            SELECT `unit_id`,
                SUM(`count`) AS count
            FROM " . self::TABLE_NAME . "
            WHERE `personage_id` = %d
            GROUP BY `unit_id`
        ";
        
        $result = $this->query($sql, $personageId);
        if ($result->IsError())
            throw new DBException('Some error in query in function `getPersonageUnits`');
        
        return $result->__DBResult;
    }
}
