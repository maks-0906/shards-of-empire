<?php
/**
 * Description content file
 *
 * @author Greg
 * @package
 */

/**
 * Description class
 *
 * @author Greg
 * @version
 * @package
 */
class personage_State extends Mapper
{
    const TABLE_NAME = 'personages_state';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_State
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
     * Первичный ключ для текущей таблицы.
     * @return string
     */
    public function pk()
    {
        return 'id_personage_state';
    }

    /**
     * Определение персонажа в составе гильдии.
     *
     * @param integer $idPersonage
     * @return bool|integer
     */
    public function getGuildId($idPersonage)
    {
        $sql = "SELECT * FROM %s as p WHERE p.id_personage = %d";

        $statePersonage = $this->find($sql, $this->tableName(), $idPersonage);
        if ($statePersonage instanceof personage_State)
            return $statePersonage->guild_id;
        else
            return false;
    }

    /**
     * Сохранение состояния персонажа.
     *
     * @param int $idPersonage
     * @param int $idFraction
     * @param int $idTypePersonage
     * @param int $idReligion
     * @param int $x
     * @param int $y
     * @return personage_State
     */
    public function saveState($idPersonage, $idFraction, $idTypePersonage, $idReligion, $x, $y)
    {
        $this->id_personage = $idPersonage;
        $this->fraction_id = $idFraction;
        $this->type_id = $idTypePersonage;
        $this->religion_id = $idReligion;
        $this->x_l = $x;
        $this->y_l = $y;

        return $this->save();
    }

    /**
     * Поиск состояния персонажа по его идентификикатору.
     *
     * @param int $idPersonage
     * @return personage_State|null
     */
    public function findStatePersonageById($idPersonage)
    {
        $sql = "SELECT * FROM %s WHERE `id_personage`=%d";
        return $this->find($sql, self::TABLE_NAME, $idPersonage);
    }

    /**
     * Изменить параметры персонажа
     *
     * @param $idPersonage
     * @param $sqlPart
     * @return bool
     */
    public function toChangeCurrentSettingsPersonage($idPersonage, $sqlPart)
    {
        $sql = "UPDATE %s SET %s WHERE `id_personage` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $sqlPart, $idPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Сформировать часть запроса для славы персонажа
     *
     * @param $fame
     * @param $idPersonage
     * @return bool
     */
    public function formPartOfFame($fame, $idPersonage)
    {
        $sqlPart = '`fame` = `fame` + ' . $fame . '';
        return personage_State::model()->toChangeCurrentSettingsPersonage($idPersonage, $sqlPart);
    }

    /**
     * Сформировать часть запроса для ID титула персонажа
     *
     * @param $idDignity
     * @param $idPersonage
     * @return bool
     */
    public function formPartOfIdDignity($idDignity, $idPersonage)
    {
        $sqlPart = '`id_dignity` =' . $idDignity;
        return personage_State::model()->toChangeCurrentSettingsPersonage($idPersonage, $sqlPart);
    }

    /**
        * Обновляем поле последнего визита по обновлению привилегий в зависимости от титула
        *
        * @param $idPersonage
        * @return bool
        */
       public function updateFieldPrivilegeLastVisit($idPersonage){
           $sqlPart = ' `privilege_last_visit` = NOW()';
           return $this->toChangeCurrentSettingsPersonage($idPersonage, $sqlPart);
       }

	public function addFameToPersonageBySquadId($squadId, $fame)
	{
		$sql = "
			UPDATE `" . self::TABLE_NAME . "` AS ps, `" . unit_UnitsMoving::TABLE_NAME . "` AS pum
			SET ps.`fame` = ps.`fame` + %d
			WHERE
				pum.`id` = %d
				AND ps.`id_personage` = pum.`personage_id`
		";
		$result = $this->query($sql, $fame, $squadId);
		return $this->getAffectedRows($result);
	}

	public function addFameToPersonage($personageId, $fame)
	{
		$sql = "
			UPDATE " . self::TABLE_NAME . "
			SET `fame` = `fame` + %d
			WHERE `id_personage` = %d
			LIMIT 1
		";
		$result = $this->query($sql, $fame, $personageId);
		return $this->getAffectedRows($result);
	}

}
