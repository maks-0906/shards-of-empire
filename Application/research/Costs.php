<?php
/**
 * Файл содержит запросы к базе данных для получения расчетов для глобальных исследований
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
class research_Costs extends Mapper
{
    const TABLE_NAME = 'research_costs';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return research_Costs
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
     * Найти время иследования здания по максимальному уровню здания
     *
     * @param $idResearch
     * @param $idResources
     * @param $idPersonage
     * @return research_Costs
     */
    public function findTimeResearchCosts($idResearch, $idResources, $idPersonage)
    {
        $sql = "SELECT `time` as time_research FROM %1\$s as rc
                WHERE `rc`.level_for_costs =
                       (SELECT `current_level` FROM %2\$s as prs WHERE `prs`.current_research_id = %4\$d AND `prs`.`id_personage`=%3\$d) + 1
                AND `rc`.research_id = %4\$d
                AND `rc`.resources_id = %5\$d";

        return $this->find($sql, self::TABLE_NAME, personage_ResearchState::TABLE_NAME,
                                 $idPersonage, $idResearch, $idResources);
    }

}
