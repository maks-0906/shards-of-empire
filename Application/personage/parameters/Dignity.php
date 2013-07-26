<?php
/**
 * Файл является моделью таблицы и содерщит запросы связанные с титулом персонажа
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
class personage_parameters_Dignity extends Mapper
{
    /**
     * Начальный уровень титула
     */
    const INITIAL_LEVEL_DIGNITY = 1;

    const TABLE_NAME = 'dignity';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_parameters_Dignity
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
     * Получить все текущие и последующие данныя титула персонажа
     *
     * @param $idPersonage
     * @return personage_parameters_Dignity
     */
    public function findAllStateDignity($idPersonage)
    {
        $sql = "SELECT `pd`.name_dignity, `ps`.fame as personage_state_fame,
                       `pdps`.amount_fame as necessary_amount_fame, `pdps`.name_dignity as next_name_dignity,
                       `pdps`.id_dignity as next_id_dignity
                    FROM %1\$s as pd
                    INNER JOIN %2\$s as ps
                      ON (`pd`.id_dignity = `ps`.id_dignity)
                    INNER JOIN

                          /*Запрос на получение последующих данных для сравнения и изменения титула персонажа*/
                           (SELECT `pd`.amount_fame, `pd`.name_dignity, `pd`.id_dignity
                            FROM %1\$s as pd
                            INNER JOIN %2\$s as ps
                               ON (`pd`.id_dignity = `ps`.id_dignity + 1)
                            WHERE `ps`.id_personage = %3\$d) as pdps

                    WHERE `ps`.id_personage = %3\$d";

        return $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idPersonage);
    }

    /**
     * Получить все привилегии для титула
     *
     * @param $idDignity
     * @return personage_parameters_Dignity
     */
    public function findPrivilegeDignity($idDignity)
    {
        $sql = "SELECT * FROM %s WHERE `id_dignity` = %d";
        return $this->find($sql, self::TABLE_NAME, $idDignity);
    }


    /**
     * Получить персонажа с его привелегиями
     *
     * @return personage_parameters_Dignity
     */
    public function findDignityOnPersonageState()
    {
        $sql = "SELECT `pd`.*, `ps`.id_personage, `ps`.privilege_last_visit
                FROM %1\$s as pd
                INNER JOIN %2\$s as ps
                    ON (`pd`.id_dignity = `ps`.id_dignity)";

        return $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME);
    }
}
