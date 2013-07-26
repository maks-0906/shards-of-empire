<?php
/**
 * Файл содержит запросы к базе данных связанные с ресурсами для улучшения зданий
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

class building_UpgradeResource extends Mapper
{
    const TABLE_NAME = 'building_upgrade_resource_value';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_UpgradeResource
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
     * Первичный ключ для текущей таблицы.
     * @return string
     */
    public function pk()
    {
        return 'id_building_upgrade_resource_value';
    }

    /**
     * Получить значение ресурсов необходимые для внутренних улучшений
     *
     * Запрос послучает значение ресурсов необходмого для внутреннего улучшения, так же текущие ресурсы в городе персонажаб
     * название здания которое улучшается, уровни зданий необходимые и текущие.
     * @param $idUpgrade
     * @param $idPersonage
     * @param $x
     * @param $y
     * @param $idPersonageBuilding
     * @return array|building_UpgradeResource
     */
    public function findValueResourcesNeededUpgrade($idUpgrade, $idPersonage, $x, $y, $idPersonageBuilding)
    {
        $sql = "SELECT `burv`.upgrade_resource_value as required_resource,
                       `r`.name_resource, FLOOR(`prs`.personage_resource_value) as has_resource,
                       `blu`.max_level as max_access_level, `bpb`.current_level as current_level_building,
                       `bpb`.name as name_building
                FROM %1\$s as burv
                INNER JOIN %2\$s as prs
                   ON (`prs`.resource_id = `burv`.resource_id)
                INNER JOIN %3\$s as bu
                   ON (`burv`.id_building_upgrade = `bu`.id_building_upgrade)
                INNER JOIN %4\$s as r
                   ON (`r`.id = `burv`.resource_id)
                INNER JOIN %5\$s as blu
                   ON (`blu`.id_level_upgrade = `bu`.id_building_level_upgrade)
                INNER JOIN

                      /*Получить уровень и название здания*/
                      (SELECT `pb`.current_level, `b`.name
                       FROM  %6\$s as b
                       INNER JOIN %12\$s as pb
                          ON (`b`.id = `pb`.building_id)
                       WHERE `pb`.id_building_personage = %13\$d
                       AND `pb`.city_id = (SELECT `id` FROM %9\$s WHERE x_c=%10\$d AND y_c=%11\$d AND `id_personage` = %8\$d)
                       ) as bpb

                 WHERE `bu`.id_building_upgrade = %7\$d
                 AND `prs`.id_personage = %8\$d
                 AND (`prs`.personages_cities_id = (SELECT `id` FROM %9\$s WHERE x_c=%10\$d AND y_c=%11\$d AND `id_personage` = %8\$d)
                      OR (`prs`.personages_cities_id IS NULL AND `prs`.id_personage = %8\$d))";

        return $this->findAll($sql, self::TABLE_NAME, personage_ResourceState::TABLE_NAME, building_Upgrade::TABLE_NAME,
                                 resource_Mapper::TABLE_NAME, building_LevelUpgrade::TABLE_NAME, building_Mapper::TABLE_NAME,
                                 $idUpgrade, $idPersonage, personage_City::TABLE_NAME, $x, $y, personage_Building::TABLE_NAME,
                                 $idPersonageBuilding);
    }

}
