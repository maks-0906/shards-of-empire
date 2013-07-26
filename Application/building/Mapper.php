<?php
/**
 * Класс (модель) собеспечивает запросы к таблице (building)
 * Таблица содержит название, описание, классификацию зданий.
 *
 * @author Greg
 * @version 1.0.0
 * @package building
 */
class building_Mapper extends Mapper
{
    const TABLE_NAME = 'building';

    const BASIC_CLASSIFIER = 'basic'; //Основные здания
    const RESOURCE_CLASSIFIER = 'resource'; //Ресурсные здания

    const IMPOSSIBLE_CONSTRUCT_BUILDING = 0; //Не возможно построить здание

    const DEFAULT_TYPE = 'default'; //Здания даются по умолчанию в самом начале игры
    const DEVELOPMENT_TYPE = 'development'; //Здания открываются только при достижении определённого уровня развития строения крепость.
    const INITIAL_LEVEL = 1; //Начальный уровень

    const KEY_BUILDING_HOUSE = 'house'; //Ключ здания "ДОМ"
    const KEY_BUILDING_CASTLE = 'castle'; //Ключ здания "ЗАМОК ЛОРДА"
    const KEY_BUILDING_WAREHOUSE  = 'warehouse'; //Ключ здания "СКЛАД"
    const KEY_BUILDING_FORTRESS = 'fortress'; //Ключ здания "КРЕПОСТЬ"
    const KEY_BUILDING_SMITHY = 'smithy'; //Ключ здания "КУЗНИЦА"
    const KEY_BUILDING_QUARRY = 'quarry'; //Ключ здания "КАМЕНОЛОМНЯ"
    const KEY_BUILDING_SAWMILL = 'sawmill'; //Ключ здания "ЛЕСОПИЛКА"
    const KEY_BUILDING_WEAVING_WORKSHOP = 'weaving_workshop'; //Ключ здания "ТКАЦКАЯ МАСТЕРСКАЯ"
    const KEY_BUILDING_BREWERY = 'brewery'; //Ключ здания "ПИВОВАРНЯ"
    const KEY_BUILDING_FARM = 'farm'; //Ключ здания "ФЕРМА"
    const KEY_BUILDING_WINERY = 'winery'; //Ключ здания "ВИНОДЕЛЬНЯ"
    const KEY_BUILDING_APIARY = 'apiary'; //Ключ здания "ПАСЕКА"
    const KEY_BUILDING_BARD_COLLEGE = 'bard_college'; //Ключ здания "КОЛЛЕГИЯ БАРДОВ"
    const KEY_BUILDING_MARKET = 'market'; //Ключ здания "РЫНОК"
    const KEY_BUILDING_TOURNAMENT_FIELD = 'tournament_field'; //Ключ здания "ТУРНИРНОЕ ПОЛЕ"
    const KEY_BUILDING_HOUSE_LEKAR = 'house_lekar'; //Ключ здания "ДОМ ЛЕКАРЯ"
    const KEY_BUILDING_TAVERN = 'tavern'; //Ключ здания "ТАВЕРНА"
    const KEY_BUILDING_LIBRARY = 'library'; //Ключ здания "БИБЛИОТЕКА"
    const KEY_BUILDING_SACRED_GROVE = 'sacred_grove'; //Ключ здания "СВЯЩЕННАЯ РОЩА"
    const KEY_BUILDING_BARRACKS = 'barracks'; //Ключ здания "КАЗАРМА"
    const KEY_BUILDING_STABLE = 'stable'; //Ключ здания "КОНЮШНЯ"


    /**
     * Получение экземпляра сущности.
     * @param string $className
     * @return building_Mapper
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
     * Поиск здания по его идентификатору.
     * @param int $idBuilding
     * @return personage_Mapper
     */
    public function findBuildingById($idBuilding)
    {
        $sql = "SELECT * FROM %s WHERE `id`=%d";
        return $this->find($sql, self::TABLE_NAME, $idBuilding);
    }

    /**
     * @return array|Mapper[]
     */
    public function findAllBuildings()
    {
        $sql = "SELECT * FROM %s";
        return $this->findAll($sql, self::TABLE_NAME);
    }

    /**
     * Поиск здания с информацией о его улучшениях, бонусах базовых и списком юнитов.
     * TODO: Добваить в запрос список юнитов.
     *
     * @param int $idBuilding
     * @param int $idPersonage
     * @param int $idCity
     * @return building_Mapper|null
     */
    public function findBuildingWithImproveAndUnits($idBuilding, $idPersonage, $idCity)
    {
        /*		$sql = "SELECT `b`.name, `pb`.current_level, `bbl`.data_bonus,
                               `bd`.resource_tree, `bd`.resource_iron, `bd`.resource_silver,
                               `bd`.resource_stone, `bd`.resource_food, `bd`.resource_tissue,
                               `bu`.*
                        FROM %s as b
                        LEFT JOIN %s as pb ON `b`.id=`pb`.building_id AND `pb`.personage_id=%d

                        LEFT OUTER JOIN %s as bbl ON `bbl`.building_id=%d
                        LEFT OUTER JOIN %s as bd ON `bd`.building_id=%d AND `pb`.current_level+1=`bd`.level

                        LEFT JOIN %s as blu ON `blu`.id_building=%d AND (`pb`.current_level+1)<=`blu`.max_level
                        LEFT JOIN %s as `bu` ON `blu`.id_level_upgrade=`bu`.id_building_level_upgrade
                        WHERE `b`.`id`=%d;";*/

        $sql = "SELECT `b`.name as name_building,  `bu`.*, `bu`.name_upgrade as name_improve,
					   `blu`.name_upgrade as name_level_building, `blu`.max_level as max_access_level,
					   `r`.name_resource, `prs`.personage_resource_value as count_current_resource,
					   `pb`.current_level as current_level_building, `bbl`.data_bonus as base_bonus,
					   `bd`.resource_tree as tree, `bd`.resource_iron as iron, `bd`.resource_silver as silver,
					   `bd`.resource_stone as stone, `bd`.resource_food as food, `bd`.resource_tissue as tissue,
					   `bd`.time_building

		FROM %1\$s as b, %2\$s as pb, %3\$s as bbl, %4\$s as bd,
		 	 %5\$s as r, %6\$s as prs, %7\$s as blu, %8\$s as bu

		WHERE `b`.`id`=%9\$d AND `pb`.building_id=%9\$d AND `pb`.personage_id=%10\$d AND `pb`.city_id=%11\$d
		 	  AND `bbl`.building_id=%9\$d
		      AND `bd`.building_id=%9\$d AND (`pb`.current_level+1)=`bd`.level AND `prs`.resource_id=`r`.id
		      AND `prs`.id_personage=%10\$d
			  AND `prs`.personages_cities_id=%11\$d AND `blu`.id_building=%9\$d AND `pb`.building_id=%9\$d
			  AND `bu`.id_building_level_upgrade=`blu`.id_level_upgrade";

        $result = $this->findAll(
            $sql,
            self::TABLE_NAME,
            personage_Building::TABLE_NAME, building_BasicLevel::TABLE_NAME, building_Development::TABLE_NAME,
            resource_Mapper::TABLE_NAME, personage_ResourceState::TABLE_NAME, building_LevelUpgrade::TABLE_NAME,
            building_Upgrade::TABLE_NAME,
            $idBuilding, $idPersonage, $idCity
        );
        return $result;
    }

    /**
     * Поиск здания с включением в набор данных о его обновлении и требуемых ресурсах для улучшения.
     * В набор данных так же входит текущее количество ресурсов персонажа в городе, определяемом по координатам.
     *
     * @param int $idBuilding
     * @param int $idPersonage
     * @param int $x
     * @param int $y
     * @param bool|int $idBuildingPersonage
     * @return array|building_Mapper
     * @throws ErrorException
     */
    public function findBuildingWithResourceAndUpgrade($idBuilding, $idPersonage, $x, $y, $idBuildingPersonage = false)
    {
        if ($idPersonage == null || $x === null || $y === null)
            throw new ErrorException('One of the parameters for the search is set to null');

        $sql = 'SELECT
					`b`.name as name_building, `b`.classifier, r.name_resource, `b`.unit,
			   		`pbblu`.name_upgrade as name_level_building, `pbblu`.max_level as max_access_level,
			    	`pbblu`.current_level as current_level_building, `pbblu`.finish_time_construction, `pbblu`.status_construction,
			    	`pbblu`.out_time, `pbblu`.id_building_personage, `pbblu`.status_production,
			    	`pbblu`.unix_finish_time_construction, `bbl`.data_bonus as base_bonus,

					`bd`.time_building, `bd`.level, `r`.name_resource, FLOOR(`prs`.personage_resource_value) as has_resource,
					`bdr`.value_development_resource as required_resource, `bu`.bonus_number_products,
					`bu`.bonus_time_production, `bu`.bonus_capacity, `bu`.bonus_population_growth, `bu`.bonus_health,
					`pbb`.current_level as current_level_building_castle

				FROM %1$s as b, %2$s as bbl, %3$s as bd, %4$s as prs, %5$s as r, %13$s as bdr, %14$s as bu
					INNER JOIN
					(
						SELECT `blu`.name_upgrade, `blu`.max_level,`blu`.id_level_upgrade, `pb`.current_level,
						        `pb`.finish_time_construction,
						       TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP, `pb`.finish_time_construction) as out_time,
						       UNIX_TIMESTAMP(`pb`.finish_time_construction) as unix_finish_time_construction,
						       `pb`.status_construction,`pb`.id_building_personage, `pb`.status_production,

						       /*Проверка для последнего уровня*/
						       IF (`pb`.current_level+1 > (SELECT max(max_level) FROM %6$s WHERE `id_building` = %8$d),
						                                    `blu`.max_level,
						                                       `pb`.current_level+1) next_level
						FROM %6$s as blu, %7$s as pb
						WHERE `blu`.id_building=%8$d
						AND `pb`.building_id=%8$d';

        //Первичный ключ, если несколько одинаковых зданий
        if ($idBuildingPersonage !== false) {
            $sql .= ' AND `pb`.id_building_personage=%12$d';
        }

        $sql .= ' AND `pb`.personage_id=%9$d
					    AND `pb`.current_level<=`blu`.max_level
					    ORDER BY `blu`.max_level ASC LIMIT 1
					) as pbblu

                    /*Получаем данные для здания "ЗАМОК" в текущем городе*/
					INNER JOIN
					 (SELECT `pb`.current_level
					  FROM %7$s as pb, %1$s as b
					  WHERE `b`.id = `pb`.building_id
					  AND `b`.name = "%15$s"
					  AND `pb`.city_id = (SELECT id FROM personages_cities WHERE x_c=%10$d AND y_c=%11$d)
					  ) as pbb

				WHERE `b`.`id`=%8$d
				AND `bbl`.building_id=%8$d
				AND `r`.id = `bdr`.resource_id
				AND `bd`.id = `bdr`.building_development_id
				AND `bd`.building_id=%8$d
			    AND `bd`.level = `pbblu`.next_level
			    AND `pbblu`.id_level_upgrade =`bu`.id_building_level_upgrade
			    AND `prs`.id_personage=%9$d
					  AND (`prs`.personages_cities_id=(SELECT id FROM personages_cities WHERE x_c=%10$d AND y_c=%11$d)
					  OR `prs`.personages_cities_id IS NULL)
					  AND `r`.id=`prs`.resource_id';

        $result = $this->findAll(
            $sql,
            self::TABLE_NAME,
            building_BasicLevel::TABLE_NAME, building_Development::TABLE_NAME,
            personage_ResourceState::TABLE_NAME, resource_Mapper::TABLE_NAME,
            building_LevelUpgrade::TABLE_NAME, personage_Building::TABLE_NAME,
            $idBuilding, $idPersonage, intval($x), intval($y), $idBuildingPersonage,
            building_DevelopmentResource::TABLE_NAME, building_Upgrade::TABLE_NAME,
            building_Mapper::KEY_BUILDING_CASTLE
        );

        return $result;
    }

    /**
     * Запрос на поиск по типу зданий
     * @param $type
     * @return building_Mapper
     */
    public function findBuildingByType($type)
    {
        $sql = "SELECT `b`.*, `bd`.*, `b`.id as id, `bd`.id as dev_id
			    FROM %1\$s as b
			    LEFT JOIN %2\$s as bd
			    ON `bd`.`id`=`bd`.`building_id`
			    WHERE  `b`.`type`='%3\$s'";

        return $this->findAll($sql,
            self::TABLE_NAME,
            building_Development::TABLE_NAME,
            $type
        );
    }

    /**
     * Получить здания по их классификации
     *
     * @param $classifier
     * @return array|building_Mapper
     */
    public function findAllClassifierBuilding($classifier)
    {
        $sql = "SELECT * FROM %1\$s WHERE `classifier`='%2\$s'";
        return $this->findAll($sql, self::TABLE_NAME, $classifier);
    }

    /**
     * Поиск по классификации зданий.
     *
     * @param string $classifier
     * @param bool|string $type
     * @return building_Mapper
     */
    public function findBuildingByClassifierAndType($classifier, $type = false)
    {
        $sql = 'SELECT `b`.*, `bd`.*, `b`.id as building_id
                   FROM %1$s as b
                   LEFT JOIN %2$s as bd ON `bd`.`id` = `bd`.`building_id`
                   WHERE  `b`.`classifier`="%3$s"';

        if ($type !== false) $sql .= ' AND `b`.type="%4$s"';

        return $this->findAll($sql, self::TABLE_NAME, building_Development::TABLE_NAME, $classifier, $type);
    }

    /**
     * Поиск зданий по типу (умолчание)
     * @return building_Mapper
     */
    public function findTypeDefaultBuilding()
    {
        return $this->findBuildingByType(self::DEFAULT_TYPE);
    }

    /**
     * Поиск зданий по типу (в прцессе развития)
     * @return building_Mapper
     */
    public function findTypeDevelopmentBuilding()
    {
        return $this->findBuildingByType(self::DEVELOPMENT_TYPE);
    }

    /**
     * Поиск зданий по классификации (основные)
     * @return building_Mapper
     */
    public function findBasicClassifierBuilding()
    {
        return $this->findBuildingByClassifierAndType(self::BASIC_CLASSIFIER);
    }

    /**
     * Поиск зданий по классификации (ресурсные)
     * @return building_Mapper
     */
    public function findResourceClassifierBuilding()
    {
        return $this->findBuildingByClassifierAndType(self::RESOURCE_CLASSIFIER);
    }
}
