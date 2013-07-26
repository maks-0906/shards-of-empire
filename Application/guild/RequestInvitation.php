<?php
/**
 * Файл является моделью таблицы содержащую заявки и приглашения в союз
 */
class guild_RequestInvitation extends Mapper
{
    const TABLE_NAME = 'personages_guilds_request_invitation';

    const GUILD_REQUEST = 'request'; //Заявка
    const GUILD_INVITATION = 'invitation'; // Приглашение

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return guild_RequestInvitation
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
     * Получить данные о заявках или приглашениях
     *
     * @param $statusRequestInvitation
     * @param $idPersonage
     * @return guild_RequestInvitation
     */
    public function findAllStatusRequestOrInvitation($statusRequestInvitation, $idPersonage)
    {
        $sql = "SELECT `p`.nick, `ppd`.name_dignity, `gri`.*, `g`.*,
                          `ps`.role_in_guild
                   FROM %1\$s as gri, %2\$s as p, %3\$s as ps, %4\$s as ppd, %5\$s as g
                   WHERE `gri`.status_request_invitation = '%6\$s'
                   AND `ps`.id_personage = %7\$d
                   AND `ps`.id_dignity = `ppd`.id_dignity
                   AND `gri`.guild_id = `g`.id
                   AND `gri`.id_personage = `ps`.id_personage";

        return $this->findAll($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME,
            personage_parameters_Dignity::TABLE_NAME, guild_Mapper::TABLE_NAME,
            $statusRequestInvitation, $idPersonage);
    }

    /**
     * Получаем данные о заявке или приглашении по его ID
     *
     * @param $idRequestInvitation
     * @return guild_RequestInvitation
     */
    public function findRequestOrInvitationOnId($idRequestInvitation)
    {
        $sql = "SELECT `p`.nick, `ppd`.name_dignity, `gri`.*, `g`.*,
                          `ps`.role_in_guild
                   FROM %1\$s as gri, %2\$s as p, %3\$s as ps, %4\$s as ppd, %5\$s as g
                   WHERE `gri`.id_personages_guilds_request_invitation = %6\$d
                   AND `ps`.id_dignity = `ppd`.id_dignity
                   AND `gri`.guild_id = `g`.id
                   AND `gri`.id_personage = `ps`.id_personage";

        return $this->find($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME,
            personage_parameters_Dignity::TABLE_NAME, guild_Mapper::TABLE_NAME,
            $idRequestInvitation);
    }


    /**
     * Получаем данные о заявке или приглашении по его ID союза и персонажа
     *
     * @param $idGuild
     * @param $idPersonage
     * @return guild_RequestInvitation
     */
    public function findRequestOrInvitationOnIdGuild($idGuild, $idPersonage)
     {
         $sql = "SELECT `p`.nick, `ppd`.name_dignity, `gri`.*, `g`.*,
                           `ps`.role_in_guild
                    FROM %1\$s as gri, %2\$s as p, %3\$s as ps, %4\$s as ppd, %5\$s as g
                    WHERE `gri`.guild_id = %7\$d
                    AND `gri`.id_personage = %6\$d
                    AND `ps`.id_dignity = `ppd`.id_dignity
                    AND `gri`.guild_id = `g`.id
                    AND `gri`.id_personage = `ps`.id_personage";

         return $this->find($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME,
                                  personage_parameters_Dignity::TABLE_NAME, guild_Mapper::TABLE_NAME,
                                  $idGuild, $idPersonage);
     }

    /**
     * Удаляем заявку или приглашение
     *
     * @param $idRequestInvitation
     * @return guild_RequestInvitation
     */
    public function deleteRequestOrInvitation($idRequestInvitation)
    {
        $sql = "DELETE FROM %s WHERE `id_personages_guilds_request_invitation` = %d";
        $result = $this->find($sql, self::TABLE_NAME, $idRequestInvitation);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Добавляем заявку или приглашение
     *
     * @param $idPersonage
     * @param $idGuild
     * @param $statusRequestInvitation
     * @return bool
     */
    public function insertRequestOrInvitation($idPersonage, $idGuild, $statusRequestInvitation)
     {
         $sql = "INSERT INTO %s
                 SET `id_personage` = %d,
                     `guild_id` = %d,
                     `status_request_invitation` = '%s',
                     `date_request_invitation` = NOW()";

         $result = $this->find($sql, self::TABLE_NAME, $idPersonage, $idGuild, $statusRequestInvitation);

         $affected_rows = $this->getAffectedRows($result);

         if ($affected_rows > 0) {
             return true;
         } else {
             return false;
         }
     }

}
