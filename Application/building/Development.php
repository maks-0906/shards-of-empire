<?php
/**
 * Класс (модель) обеспечивает запросы к таблице (building_development)
 * Таблица содержит в себе все данные по стоимости, развитию и улучшению зданий.
 * В ней находится внешний ключ на таблицу (building).
 *
 * @author Greg
 * @version 1.0.0
 * @package building
 */
class building_Development extends Mapper
{
	const TABLE_NAME = 'building_development';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_Development
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
     * Получаем данные для строительства здания текущего уровня
     *
     * @param $idBuilding
     * @param $level
     * @param $x
     * @param $y
     * @param $idPersonage
     * @return building_Development
     */
    public function findDataConstructionBuildingCurrentLevel($idBuilding, $level, $x, $y, $idPersonage)
   {
        $sql = "SELECT `bd`.time_building, `bdr`.resource_id, `bdr`.value_development_resource as price,

                  /*Получаем базовые бонусы для зданий*/
                  (SELECT `data_bonus` FROM %6\$s as bbs WHERE `building_id` = %2\$d) as basic_bonus,

                 /*Определяем достаточно ли ресурсов уперсонажа для постройки здания
                 * 1 - ресурсов достаточно
                 * 0 - ресурсов не достаточно
                 */
                 IF (`prs`.personage_resource_value >= `bdr`.value_development_resource, 1, 0) resources_personage

                FROM %1\$s as bd
                INNER JOIN %4\$s as bdr
                    ON (`bd`.id = `bdr`.building_development_id)
                INNER JOIN %5\$s as prs
                    ON (`bdr`.resource_id = `prs`.resource_id)
                WHERE `bd`.`building_id` = %2\$d
                AND `bd`.`level` = %3\$d
                AND (`prs`.personages_cities_id = (SELECT id FROM %7\$s WHERE x_c=%8\$d AND y_c=%9\$d AND id_personage=%10\$d)
                    OR (`prs`.personages_cities_id IS NULL AND `prs`.id_personage = %10\$d))";

        return $this->findAll($sql, self::TABLE_NAME, $idBuilding, $level, building_DevelopmentResource::TABLE_NAME,
                                 personage_ResourceState::TABLE_NAME, building_BasicLevel::TABLE_NAME,  personage_City::TABLE_NAME,
                                 $x, $y, $idPersonage);
   }

    /**
     * Получить бонусы выработки ресурсов в городе по ID ресурса
     *
     * @param $idResources
     * @param $idCity
     * @return building_Development
     */
    public function findDataDependingOnResources($idResources, $idCity)
   {
       $sql = "SELECT `bd`.*, `b`.name as name_building
               FROM %1\$s as bd
               LEFT JOIN %2\$s as r
                 ON (`bd`.building_id = `r`.building_id)
               INNER JOIN %3\$s as b
                 ON (`b`.id = `r`.building_id)
               INNER JOIN %4\$s as pb
                ON (`pb`.building_id = `r`.building_id)
                      AND `bd`.level = `pb`.current_level
               WHERE `r`.id = %5\$d
               AND `pb`.city_id = %6\$d";

       return $this->find($sql, self::TABLE_NAME, resource_Mapper::TABLE_NAME, building_Mapper::TABLE_NAME,
                               personage_Building::TABLE_NAME, $idResources, $idCity);
   }

    /**
     * Получить характеристики здания по ИД и уровню здания
     *
     * @param $idBuilding
     * @param $levelBuilding
     * @return building_Development
     */
    public function findByIdBuildingAndLevelBuilding($idBuilding, $levelBuilding)
   {
        $sql = "
			SELECT 
				*
            FROM 
				`%1\$s`
            WHERE 
				`building_id` = %2\$d
                AND `level` = %3\$d";

        return $this->find(
			$sql, 
			self::TABLE_NAME,
			$idBuilding,
			$levelBuilding
		);
   }
}
