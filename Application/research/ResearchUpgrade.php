<?php
/**
 * Файл содержит запросы к базе данных связанные с улучшениями в глобальных исследованиях
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
class research_ResearchUpgrade extends Mapper
{

    const TABLE_NAME = 'research_upgrade';

    /**
     * Получение экземпляра сущности.
     * @param string $className
     * @return research_ResearchUpgrade
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
     * Получить максимальный уровень исследований
     *
     * @return research_ResearchUpgrade
     */
    public function findMaxLevelBuildingOnUpgrade()
    {
        $sql = "SELECT max(level_for_upgrade) as max_level_for_upgrade FROM %s";
        return $this->find($sql, self::TABLE_NAME);
    }
}
