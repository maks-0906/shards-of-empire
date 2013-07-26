<?php
/**
 * Файл содержит запросы к базе данных
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
class research_Mapper extends Mapper
{
	const TABLE_NAME = 'research';

    /**
     * Начальный уровень исследований
     */
    const INITIAL_LEVEL = 1;

    /**
     * Значение сессии (personage_id)
     * @var int
     */
    public $idPersonage;

    /**
     * Объект класса (research_Costs)
     * @var object
     */
    public $oCosts;

    /**
     * Объект класса (personage_ResearchState)
     * @var object
     */
    public $oResearchState;

    /**
     * Объект класса (personage_ResearchUpgrade)
     * @var object
     */
    public $oResearchUpgrade;

    /**
     * объект класса (building_Upgrade)
     * @var object
     */
    public $oBuildingUpgrade;

    /**
     * объект класса (resource_Mapper)
     * @var object
     */
    public $oResource;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return research_Mapper
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
     * Инициализация первоначальных параметров сущности.
     */
    public function init()
    {
        $this->oCosts = research_Costs::model();
        $this->oResearchState = personage_ResearchState::model();
        $this->oResearchUpgrade = research_ResearchUpgrade::model();
        $this->oBuildingUpgrade = building_Upgrade::model();
        $this->oResource = resource_Mapper::model();
        // $this->oPersonageResourceState = personage_ResourceState::model();
        $this->idPersonage = Auth::getIdPersonage();
    }

    /**
     * Получаем начальные данные исследований
     */
    public function findInitialResearch()
    {
        $sql = "SELECT %s.*,`%s`.*,
			        (SELECT `name_upgrade`
			         FROM %s
			         WHERE `%s`.`id_building_upgrade`=`%s`.`id_building_upgrade`) AS `name_upgrade`
			    FROM %s
			    LEFT JOIN %s
			    ON `%s`.`id` = `%s`.`research_id`
			    WHERE `%s`.`level_for_upgrade` = %d";

        return $this->findAll($sql,
            $this->tableName(),
            $this->oResearchUpgrade->tableName(),
            $this->oBuildingUpgrade->tableName(),
            $this->oBuildingUpgrade->tableName(),
            $this->oResearchUpgrade->tableName(),
            $this->tableName(),
            $this->oResearchUpgrade->tableName(),
            $this->tableName(),
            $this->oResearchUpgrade->tableName(),
            $this->oResearchUpgrade->tableName(),
            self::INITIAL_LEVEL
        );
    }

    /**
     * Поиск всех исследований с включением текущего уровня персонажа.
     *
     * @return array
     */
    public function findAllResearch()
    {
        $sql = "SELECT `research`.*,
                (SELECT `current_level` FROM `%1\$s` as psr WHERE `psr`.`id_personage`=%2\$d AND `research`.id=`psr`.current_research_id) as current_level
                 FROM %3\$s as research";

        return $this->findAll($sql, personage_ResearchState::TABLE_NAME, $this->idPersonage, $this->tableName());
    }

    /**
     * @param int $idResearch
     * @param int $level
     * @return array
     */
    public function findPropertiesResearch($idResearch, $level)
    {
        $level = ($level == 0) ? 1 : $level + 1;
        $sql = "SELECT `ru`.level_for_upgrade as level_upgrade, `ru`.research_id, `rc`.resources_id ,`rc`.price, `rc`.time,
				(SELECT `name_upgrade` FROM %1\$s as bu WHERE `bu`.id_building_upgrade=`ru`.id_building_upgrade) as name_upgrade,
				(SELECT `name_resource` FROM %2\$s as resource WHERE `resource`.id=`rc`.resources_id) as name_resource
				 FROM %3\$s as ru, %4\$s as rc
				 WHERE `ru`.research_id=%5\$d AND `ru`.level_for_upgrade=%6\$d
				 AND `rc`.research_id=%5\$s AND `rc`.level_for_costs=%6\$d";

        return $this->findAll(
            $sql,
            building_Upgrade::model()->tableName(),
            resource_Mapper::model()->tableName(),
            research_ResearchUpgrade::model()->tableName(),
            research_Costs::model()->tableName(),
            intval($idResearch), intval($level)
        );
    }

    /**
     * Получаем все индефикаторы исследований
     *
     * @return resource_Mapper
     */
    public function  findIdAllResearch()
    {
        $sql = "SELECT `id` FROM %s";
        $result = $this->find($sql, $this->tableName());
        if (!empty($result->__DBResult)) {
            return $result->__DBResult;
        } else {
            return false;
        }
    }

    /**
     * Получаем данные для конкретого исследования пользователя на один уровень выше
     *
     * @param $idResearch
     * @param $x
     * @param $y
     * @return mixed
     */
    public function findPropertiesNextLevel($idResearch, $x, $y)
    {
        $sql = "SELECT DISTINCT `ru`.level_for_upgrade as level_upgrade, `ru`.research_id,
                   `pb`.current_level, `prs`.research_finish_time, `prs`.research_status,
                   `prs`.current_level as research_current_level, `rc`.time as time_research,
                  (SELECT `name_upgrade` FROM %1\$s as bu WHERE `bu`.id_building_upgrade=`ru`.id_building_upgrade) as name_upgrade,
                  (SELECT `name_research` FROM %5\$s WHERE `id` = %6\$d) as name_research
            FROM
               %2\$s as ru,
               %4\$s as prs,
               %8\$s as building,
               %3\$s as rc,
               %9\$s as pb
            WHERE `rc`.level_for_costs =`prs`.current_level + 1
            AND `prs`.`current_research_id` = `rc`.research_id
            AND `ru`.research_id = `rc`.research_id
            AND `ru`.level_for_upgrade = `rc`.level_for_costs
            AND `rc`.research_id = %6\$d
            AND `prs`.`id_personage` = %7\$d
            AND `building`.id = `pb`.building_id
            AND `building`.name = '%10\$s'
            AND `rc`.resources_id = %11\$d
            AND `pb`.city_id = (SELECT `id`
							    FROM %12\$s
								WHERE `x_c` =%13\$d AND `y_c` = %14\$d AND `id_personage` = %7\$d)";

        return $this->findAll(
            $sql,
            building_Upgrade::TABLE_NAME,
            research_ResearchUpgrade::TABLE_NAME,
            research_Costs::TABLE_NAME,
            personage_ResearchState::TABLE_NAME,
            self::TABLE_NAME,
            intval($idResearch),
            $this->idPersonage,
            building_Mapper::TABLE_NAME,
            personage_Building::TABLE_NAME,
            building_Mapper::KEY_BUILDING_LIBRARY,
            resource_Mapper::GOLD_ID,
            personage_City::TABLE_NAME, $x, $y
        );
    }
}
