<?php
/**
 * Класс является моделью для бонусов и опыта союзов
 */
class guild_BonusesExperience extends Mapper
{
    const TABLE_NAME = 'guilds_bonuses_experience';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return guild_BonusesExperience
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

    public function findBonusesAndExperienceGuildOnLevel($level)
    {
        $sql = "SELECT * FROM %s WHERE `level_guilds` = %d";
        return $this->find($sql, self::TABLE_NAME, $level);
    }
}
