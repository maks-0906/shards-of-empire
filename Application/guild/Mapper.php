<?php
/**
 * Файл содержит класс модель, управляющий союзами.
 *
 * @author vetalrakitin  <vetalrakitin@gunion.com>
 * @package union
 */

/**
 * Класс модель, управляющая союзами.
 *
 * @author vetalrakitin  <vetalrakitin@gunion.com>
 * @package guild
 */
class guild_Mapper extends Mapper
{

    const TABLE_NAME = 'personages_guilds';

    // Константы типа союза
    const GUILD_TYPE_INTERFRACTIONAL = 'interfractional'; // межфракционный
    const GUILD_TYPE_FRACTIONAL = 'fractional'; // фракционный

    const GUILD_ROLE_OWNER = 'owner'; // Владелец
    const GUILD_ROLE_MODER = 'moder'; // Уполномоченный
    const GUILD_ROLE_MEMBER = 'member'; // Участник
    const GUILD_ROLE_NOTUNION = 'notunion'; // Не в союзах

    const NOT_GUILD = 0;

    /**
     * Идентификатор текущего персонажа пользователя.
     *
     * @var int
     */
    private $idPersonage;

    /**
     * Инициализация первоначальных параметров сущности.
     */
    public function init()
    {
        $this->idPersonage = Auth::getInstance()->GetSessionVar(SESSION_PERSONAGE_ID);
    }

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return guild_Mapper
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
     * Обновить поля для союзов
     *
     * @param int $idPersonage - ИД персонажа
     * @param int $idGuild - ИД союза
     * @param $roleGuild
     * @return boolean
     */
    public function updateRoleGuildPersonage($idPersonage, $idGuild, $roleGuild)
    {
        $sql = "UPDATE %1\$s
			    SET
				   `guild_id` = %2\$d,
				   `role_in_guild` = '%3\$s'
			    WHERE
				    `id_personage` = %4\$d";

        $result = $this->query($sql, personage_State::TABLE_NAME, $idGuild, $roleGuild, $idPersonage);

        return !$this->isError() & $this->getAffectedRows($result);
    }

    /**
     * Создание нового союза для персонажа
     *
     * @param $name
     * @param $type
     * @param $idPersonage
     * @return bool
     * @throws DBException
     */
    public function createGuild($name, $type, $idPersonage)
    {
        try {
            $this->begin();

            $this->id_personage = $idPersonage;
            $this->name = $name;
            $this->type = $type;

            //Создаем новый союз
            $result = $this->save();
            if ($result->isError())
                throw new DBException('Failed insert row in ' . self::TABLE_NAME);

            //Обновляем поля для союза персонажа в таблице (personage_state) с ID союза и ролью создателя
            $sql = "UPDATE %1\$s
				    SET
					  `guild_id` = %2\$d,
					  `role_in_guild` = '%3\$s'
				    WHERE
					  `id_personage` = %4\$d";

            $resultNewGuildPersonage = $this->query($sql, personage_State::TABLE_NAME, $this->get_insert_id(),
                                                    self::GUILD_ROLE_OWNER, $idPersonage);

            if ($resultNewGuildPersonage->isError()) throw new DBException(implode(' : ', $resultNewGuildPersonage->getErrors()));

            $resultCommit = $this->commit();
        } catch (DBException $e) {
            $this->rollback();
            throw new DBException('createGuild : ' . $e->getMessage());
        }

        return $resultCommit;
    }

    /**
     * Получить данные о всех участниках союза
     *
     * @param bool $idGuild
     * @param bool $idPersonage
     * @return guild_Mapper
     */
    public function findAllMemberGuilds($idGuild, $idPersonage)
    {
        $sql = "SELECT `p`.nick, `ppd`.name_dignity, `g`.*,
                       `ps`.role_in_guild, `ps`.guild_id, `ps`.id_personage
                FROM %1\$s as g, %2\$s as p, %3\$s as ps, %4\$s as ppd
                WHERE `ps`.guild_id = %5\$d
                AND `ps`.id_personage = %6\$d
                AND `ps`.id_dignity = `ppd`.id_dignity
                AND `ps`.guild_id = `g`.id
                AND `p`.id = `ps`.id_personage";

        return $this->findAll($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME,
                                   personage_parameters_Dignity::TABLE_NAME, $idGuild, $idPersonage);
    }

    /**
     * Получить данные о владельце/владельцах союза
     *
     * @param bool $idGuild
     * @param bool $idPersonage
     * @return guild_Mapper
     */
    public function findOwnerGuildsAndTotalPersonage($idGuild = false, $idPersonage = false)
    {
        $sql = "SELECT `p`.nick, `ppd`.name_dignity, `g`.*,
                       `ps`.role_in_guild, `ps`.guild_id
                FROM %1\$s as g
                INNER JOIN %2\$s as p
                  ON (`g`.id_personage = `p`.id)
                 INNER JOIN %3\$s as ps
                  ON (`ps`.id_personage = `g`.id_personage)
                     AND `ps`.guild_id = `g`.id
                INNER JOIN %4\$s as ppd
                  ON (`ps`.id_dignity = `ppd`.id_dignity)";

        if ($idGuild !== false AND $idPersonage !== false) {
            $sql .= " WHERE `g`.id = %5\$d
                      AND `g`.id_personage = %6\$d";
        }

        return $this->findAll($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME,
                                    personage_parameters_Dignity::TABLE_NAME, $idGuild, $idPersonage);
    }

    /**
     * Получить союз по ID перонажа
     *
     * @param $idPersonage
     * @return guild_Mapper
     */
    public function findGuildOnIdPersonage($idPersonage)
       {
           $sql = "SELECT `g`.level as level_guild_personage, `g`.name as name_guild_personage,
                          `g`.experience as current_experience_guild, `ps`.guild_id,
                    (SELECT max(level_guilds) FROM %2\$s) as max_level_guilds
                   FROM %1\$s as g
                   INNER JOIN %3\$s as ps
                     ON (`g`.id = `ps`.guild_id)
                  WHERE `ps`.id_personage = %4\$d";

           return $this->find($sql, self::TABLE_NAME, guild_BonusesExperience::TABLE_NAME,
                                           personage_State::TABLE_NAME, $idPersonage);
       }
}
