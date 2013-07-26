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
 * @version 1.0.0
 * @package
 */
class map_FeatureRobber extends Mapper
{

    const TABLE_NAME = 'map_feature_groups_robbers';

    const SMALL_ARMY = 'small_army';
    const MEDIUM_ARMY = 'medium_army';
    const GREAT_ARMY = 'great_army';

    /**
     * Returns the static model of the specified AR class.
     *
     * @static
     * @param string $className
     * @return map_FeatureRobber
     */
    public static function model($className = __CLASS__)
    {
        return new $className();
    }

    /**
     * Ассоциация с реальной таблицей в БД.
     *
     * @return string
     */
    public function tableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @param int $idLevelLocation
     * @return null|map_FeatureRobber
     */
    public function calculationCountRobbersByLevelLocation($idLevelLocation)
    {
        $sql = "SELECT sum(count_robbers) as count_robbers,
					(
						SELECT sum(count_robbers)
						FROM %1\$s
						WHERE `id_level_property_location` =
							(SELECT max(`id_level_property_location`) FROM %1\$s)
					) as max_count_robbers
				FROM %1\$s
				WHERE id_level_property_location=%2\$d";

        return $this->find($sql, self::TABLE_NAME, $idLevelLocation);
    }

    /**
     * Бизнес функция определения численности войска разбойников.
     *
     * @param int $idLevelLocation
     * @return string
     */
    public function detectLevelArmyRobbers($idLevelLocation)
    {
        $robber = $this->calculationCountRobbersByLevelLocation($idLevelLocation);
        fb($robber, 'robber', FirePHP::ERROR);
        $part = $robber->max_count_robbers / 3;

        if ($robber->count_robbers <= $part)
            return self::SMALL_ARMY;
        elseif ($robber->count_robbers > $part && $robber->count_robbers <= $part * 2)
            return self::MEDIUM_ARMY; else
            return self::GREAT_ARMY;
    }


    /**
     * Поиск данных об разбойниках на локации
     *
     * @param $idLocation
     * @return map_FeatureRobber
     */
    public function findLevelRobberLocations($x, $y)
    {
        $sql = "
			SELECT `mfgr`.`id_unit` AS unit_id, `mfgr`.`count_robbers` AS count
			FROM ". self::TABLE_NAME . " AS mfgr,
				". map_Cell::TABLE_NAME . " AS mc
			WHERE `mc`.id_level_cell = `mfgr`.id_level_property_location
				AND `mc`.x= %d
				AND `mc`.y = %d
		";

        $result = $this->query($sql, $x, $y);
		return $result->__DBResult;
    }
}