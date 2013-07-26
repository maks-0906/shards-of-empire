<?php
/**
 * Файл содержит класс модель, управляющий предметной и бизнес логикой к зданиям персонажа.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель отображения на таблицу зданий для персонажа.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_Building extends Mapper
{
    const TABLE_NAME = 'personages_buildings';

    const NO_LEVEL_BUILDING = 0; //Нет уровня у здания
    const LAST_BUILDING = 1; //Последнее здание
    const NOT_AVAILABLE_BUILDINGS = 0; //Нет доступных зданий
    const NO_VALUE = 0;

    const ID_BUILDING_CASTLE = 1;
    const ID_BUILDING_LIBRARY = 16;

    const STATUS_CONSTRUCTION_PROCESSING = 'processing';
    const STATUS_CONSTRUCTION_FINISH = 'finish';
    const STATUS_CONSTRUCTION_NOT_STARTED = 'notstarted';
    const STATUS_CONSTRUCTION_CANCEL = 'cancel';

    /**
     *Начало или возобновление выроботки зданием своей продукции
     */
    const STATUS_PRODUCTION = 'production';

    /**
     * Остановка выроботки зданием своей продукции
     */
    const STATUS_PRODUCTION_STOP = 'stop';

    /**
     * Значение сессии (personage_id)
     * @var int
     */
    public $idPersonage;

    /**
     * Значение принадлежности города
     *
     * @var bool
     */
    public $doneLocationCity;


    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Building
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
        return 'id_building_personage';
    }

    /**
     * Инициализация первоначальных параметров сущности.
     */
    public function init()
    {
        $this->idPersonage = Auth::getIdPersonage();
        $this->doneLocationCity = Auth::isCurrentLocationCity();
    }

    /**
     * Поиск значения здания персонажа в городе по ид здания
     *
     * @param $idCity
     * @param $idBuilding
     * @return personage_Building
     */
    public function findValueOfBuildingOnIdOfBuilding($idCity, $idBuilding)
    {
        $sql = "SELECT * FROM %s WHERE `city_id` = %d AND `building_id` = %d";
        return $this->find($sql, self::TABLE_NAME, $idCity, $idBuilding);
    }


    /**
     * Поиск значения здания персонажа в городе по ид здания персонажа
     *
     * @param $idPersonageBuilding
     * @param $x
     * @param $y
     * @param $idPersonage
     * @param $status
     * @return personage_Building
     */
    public function findValueOfBuildingPersonage($idPersonageBuilding, $x, $y, $idPersonage, $status)
    {
        $sql = "SELECT `pb`.building_id, `pb`.id_building_personage, `pb`.personage_id, `pb`.status_construction,
                       `pb`.current_level+1 as nex_level_building, `pb`.current_level,
        		       `bbl`.data_bonus, `pbbs`.current_data_bonus, `bd`.time_building,
        			   `bdrs`.value_development_resource as price, `b`.name, `prs`.resource_id,
        			   `pbb`.current_level as current_level_building_castle, `b`.classifier,

                   /*
                   * Определяем хватает ли у персонажа ресурсов для проведения улучшения здания
                   *  1 - ресурсов достаточно
                   *  0 - ресурсов не достаточно
                   */
                   IF (`prs`.personage_resource_value >= `bdrs`.value_development_resource, 1, 0) resources_personage

                FROM %1\$s as pb
                INNER JOIN %2\$s as bbl
                   ON `pb`.building_id = `bbl`.building_id
                INNER JOIN %3\$s as pbbs
                   ON `pb`.id_building_personage = `pbbs`.id_building_personage
                INNER JOIN %5\$s as bd
                   ON `pb`.building_id = `bd`.building_id
                INNER JOIN %9\$s as bdrs
                   ON `bd`.id = `bdrs`.building_development_id
                INNER JOIN  %10\$s as prs
				   ON (`prs`.resource_id = `bdrs`.resource_id)
			    INNER JOIN  %11\$s as b
			       ON (`pb`.building_id = `b`.id)

               /*Получаем уровень здания ЗАМОК*/
                INNER JOIN
                     (SELECT `pb`.current_level
                      FROM %1\$s as pb, %11\$s as b
                      WHERE `b`.id = `pb`.building_id
                      AND `b`.name = '%12\$s'
                      AND `pb`.city_id = (SELECT id FROM %6\$s WHERE x_c=%7\$d AND y_c=%8\$d)
                      ) as pbb

                WHERE `pb`.id_building_personage = %4\$d
                AND `pb`.city_id=(SELECT id FROM %6\$s WHERE x_c=%7\$d AND y_c=%8\$d)
                AND (`prs`.personages_cities_id = (SELECT id FROM %6\$s WHERE x_c=%7\$d AND y_c=%8\$d AND id_personage=%13\$d)
                    OR (`prs`.personages_cities_id IS NULL AND `prs`.id_personage = %13\$d))
                AND  `bd`.level = `pb`.current_level + 1";

        return $this->findAll($sql, self::TABLE_NAME, building_BasicLevel::TABLE_NAME, personage_BuildingBonus::TABLE_NAME,
            $idPersonageBuilding, building_Development::TABLE_NAME, personage_City::TABLE_NAME, $x, $y,
            building_DevelopmentResource::TABLE_NAME, personage_ResourceState::TABLE_NAME, building_Mapper::TABLE_NAME,
            building_Mapper::KEY_BUILDING_CASTLE, $idPersonage, $status);
    }

    /**
     * Поиск всех зданий в таблице.
     *
     * @param int $idPersonage
     * @param string $class
     * @param int $x
     * @param int $y
     * @return array|personage_Building
     */
    private function findAllBuildings($idPersonage, $class, $x, $y)
    {
        $sql = "SELECT b.name, pb.current_level, `pb`.id_building_personage, b.id
				FROM %1\$s as b
				LEFT JOIN %2\$s as pb ON b.id=pb.building_id AND `pb`.personage_id=%3\$d
				WHERE b.classifier='%4\$s'
					  AND `pb`.city_id=(SELECT id FROM %5\$s WHERE x_c=%6\$d AND y_c=%7\$d AND id_personage=%3\$d)";

        //Выводим ресурсные здания у которых нет нулевого уровня
        if ($class == building_Mapper::RESOURCE_CLASSIFIER) {
            $sql .= " AND `pb`.current_level > %8\$d";
        }

        return $this->findAll(
            $sql, building_Mapper::TABLE_NAME, self::TABLE_NAME, $idPersonage, $class, personage_City::TABLE_NAME,
            $x, $y, self::NO_LEVEL_BUILDING
        );
    }

    /**
     * Получить список задний по классификации здания
     *
     * @param int $idPersonage
     * @param int $classifier
     * @param int $x
     * @param int $y
     * @return array|personage_Building
     */
    public function findListOfAllBuildings($idPersonage, $classifier, $x, $y)
    {
        $sql = "SELECT b.name, pb.current_level, `pb`.id_building_personage, b.id,
                          UNIX_TIMESTAMP(`pb`.finish_time_construction) as unix_finish_time_construction,
                          `pb`.status_construction
   				FROM %1\$s as b
   				LEFT JOIN %2\$s as pb ON b.id=pb.building_id AND `pb`.personage_id=%3\$d
   				WHERE b.classifier='%4\$s'
   					  AND `pb`.city_id=(SELECT id FROM %5\$s WHERE x_c=%6\$d AND y_c=%7\$d AND id_personage=%3\$d)";

        return $this->findAll(
            $sql, building_Mapper::TABLE_NAME, self::TABLE_NAME, $idPersonage, $classifier, personage_City::TABLE_NAME,
            $x, $y, self::NO_LEVEL_BUILDING
        );
    }

    /**
     * Получить значения здания по его названию
     *
     * @param $nameBuilding
     * @param $x
     * @param $y
     * @param $idPersonage
     * @return personage_Building
     */
    public function findBuildingPersonageOfNameBuilding($nameBuilding, $x, $y, $idPersonage)
    {
        $sql = "SELECT `pb`.*
                FROM %1\$s as pb
                INNER JOIN %2\$s as b
                   ON (`b`.id = `pb`.building_id)
                WHERE `b`.name = '%3\$s'
                AND `pb`.city_id = (SELECT id FROM %4\$s WHERE x_c=%5\$d AND y_c=%6\$d AND id_personage=%7\$d)";

        return $this->find($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME, $nameBuilding, personage_City::TABLE_NAME,
                           $x, $y, $idPersonage);
    }

    /**
     * Специализирующий метод для выборки всех основных зданий.
     *
     * @param int $idPersonage
     * @param int $x
     * @param int $y
     * @return array|personage_Building
     */
    public function findAllBaseBuildings($idPersonage, $x, $y)
    {
        return $this->findAllBuildings($idPersonage, building_Mapper::BASIC_CLASSIFIER, $x, $y);
    }

    /**
     * Специализирующий метод для выборки всех ресурных зданий.
     *
     * @param int $idPersonage
     * @param int $x
     * @param int $y
     * @return array|personage_Building
     */
    public function findAllResourceBuildings($idPersonage, $x, $y)
    {
        return $this->findAllBuildings($idPersonage, building_Mapper::RESOURCE_CLASSIFIER, $x, $y);
    }


    /**
     *  Наполнение таблицы персонажа зданиями.
     *
     * @param $idCity
     * @param $idPersonage
     * @param $status
     * @return array|bool
     * @throws DBException
     */
    public function fillingBuildingsForPersonage($idCity, $idPersonage, $status = false)
    {
        $buildings = building_Mapper::model()->findAllBuildings();

        if (count($buildings) == 0)
            throw new DBException('Default buildings not found in system!!!');

        foreach ($buildings as $building) {
            $currentLevel = ($building->type == building_Mapper::DEFAULT_TYPE) ? 1 : 0;

            //Не добавляем ресурсные здания которые строятся персонажем
            if ($building->classifier == building_Mapper::RESOURCE_CLASSIFIER AND
                $building->type == building_Mapper::DEVELOPMENT_TYPE
            ) {
                continue;
            }

            $sql = "INSERT INTO %s
				    SET `id_building_personage` = NULL,
					    `personage_id` = %d,
						`city_id` = %d,
						`building_id` = %d,
						`current_level` = %d,
						`status_construction` = '%s'";

            $result = $this->query($sql, self::TABLE_NAME, $idPersonage, $idCity, $building->id, $currentLevel, $status);
            $affected_rows = $this->getAffectedRows($result);

            if (isset($affected_rows)) {
                $constructedBuilding[$building->id] = self::get_insert_id();
            }
        }

        if (!empty($constructedBuilding) AND is_array($constructedBuilding))
            return $constructedBuilding;
        else
            return false;
    }

    /**
     * Получить уровни двух зданий
     *
     * @param $idBuilding_1
     * @param $idBuilding_2
     * @return personage_Building
     */
    public function findLevelsBuildings($idBuilding_1, $idBuilding_2)
    {
        if ($this->doneLocationCity === true) {
            $coordinates = Auth::getCurrentLocationCoordinates();

            $sql = "SELECT `current_level`
                          FROM %1\$s
                          WHERE `building_id` IN (%2\$d, %4\$d)
                          AND `city_id` =
                                  (SELECT `id` FROM %5\$s WHERE `x_c` = %6\$d AND `y_c` = %7\$d AND `id_personage` = %3\$d)";

            return $this->findAll($sql, self::TABLE_NAME, $idBuilding_1, $this->idPersonage, $idBuilding_2,
                personage_City::TABLE_NAME, $coordinates['x'], $coordinates['y']);

        } else {
            return array();
        }
    }

    /**
     * Провести сравнение уровней зданий
     *
     * @param $idBuilding_1
     * @param $idBuilding_2
     * @return bool
     */
    public function toCompareLevelsBuildings($idBuilding_1, $idBuilding_2)
    {
        if ($idBuilding_1 >= $idBuilding_2) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Проверка классификации текущего здания и наличие статуса процесса постройки здания.
     *
     * @param $idBuilding
     * @param $idPersonage
     * @param $x
     * @param $y
     * @return personage_Building
     * @throws DBException
     */
    public function checkWhetherConstructionBuilding($idBuilding, $idPersonage, $x, $y)
    {
        $sql = "SELECT `b`.classifier
                 FROM %1\$s as pb
                 INNER JOIN %2\$s as b
                   ON (`pb`.building_id = `b`.id)
                 WHERE `city_id` = (SELECT `id` FROM %3\$s WHERE `x_c` = %4\$d AND `y_c` = %5\$d AND `id_personage` = %6\$d)
                 AND `pb`.personage_id = %6\$d
                 AND `pb`.building_id = %7\$d";

        $result = $this->find($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME, personage_City::TABLE_NAME, $x, $y,
            $idPersonage, $idBuilding);

        if ($this->isError() === true) {
            throw new DBException('Error method `checkWhetherConstructionBuilding`');
        } else {
            return $result;
        }
    }

    /**
     * Получить все здания по статусу
     *
     * @param $idPersonage
     * @param $x
     * @param $y
     * @param $status
     * @return personage_Building
     */
    public function findBuildingPersonageOnStatus($idPersonage, $x, $y, $status)
    {
        $sql = "SELECT `pb`.*, `b`.name, UNIX_TIMESTAMP(`pb`.finish_time_construction) as unix_finish_time_construction,
                       `pb`.current_level, `b`.classifier
                       FROM %1\$s as pb
                       INNER JOIN %2\$s as b
                         ON (`pb`.building_id = `b`.id)
                       WHERE `city_id` = (SELECT `id` FROM %3\$s WHERE `x_c` = %4\$d AND `y_c` = %5\$d AND `id_personage` = %6\$d)
                       AND `pb`.personage_id = %6\$d
                       AND `pb`.status_construction = '%7\$s'";

        return $this->findAll($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME, personage_City::TABLE_NAME, $x, $y,
                                                                                                  $idPersonage, $status);
    }


    /**
     * Добавляем новое здание с временной меткой окончания строительства
     *
     * @param $idBuilding
     * @param $idPersonage
     * @param $minute
     * @param $x
     * @param $y
     * @return bool
     */
    public function createNewBuildingAndTimestamp($idBuilding, $idPersonage, $minute, $x, $y)
    {
        $sql = "INSERT INTO %1\$s
                    SET `id_building_personage` = NULL,
                        `personage_id` = %2\$d,
                        `city_id` = (SELECT `id` FROM %3\$s WHERE `x_c` = %4\$d AND `y_c` = %5\$d AND `id_personage` = %2\$d),
                        `building_id` = %6\$d,
                        `finish_time_construction` = TIMESTAMP(DATE_ADD(NOW(),INTERVAL %7\$d MINUTE)),
                        `status_construction` = '%8\$s'";

        $result = $this->query($sql, self::TABLE_NAME, $idPersonage, personage_City::TABLE_NAME,
            $x, $y, $idBuilding, $minute, self::STATUS_CONSTRUCTION_PROCESSING);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return self::get_insert_id();
        } else {
            return false;
        }
    }

    /**
     * Получаем улучшения и бонусы для конкретного города
     * Данный метод используется в CRON
     *
     * @param $idCity
     * @return array|personage_Building
     */
    public function findImprovedBuildings($idCity)
    {
        $sql = "SELECT `pb`.id_building_personage, `pb`.current_level,
                        `pbs`.current_data_bonus, `pc`.growth, `pc`.population,
                        `pc`.happiness,`bbl`.data_bonus
                FROM %1\$s as pb
                INNER JOIN  %2\$s as b
                ON (`b`.id = `pb`.building_id)
                LEFT JOIN  %4\$s as pbi
                ON (`pb`.id_building_personage = `pbi`.id_building_personage)
                   AND `pbi`.status = '%7\$s'
                 LEFT  JOIN  %5\$s as pbs
                ON (`pb`.id_building_personage = `pbs`.id_building_personage)
                INNER JOIN %8\$s as pc
                   ON (`pc`.id= `pb`.city_id)
                 INNER JOIN %9\$s as bbl
                   ON (`bbl`.building_id= `pb`.building_id)
                WHERE `b`.name = '%3\$s'
                AND `pb`.city_id = %6\$d";

        return $this->findAll($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME,
            building_Mapper::KEY_BUILDING_HOUSE, personage_Improve::TABLE_NAME,
            personage_BuildingBonus::TABLE_NAME, $idCity, personage_Improve::STATUS_FINISH,
            personage_City::TABLE_NAME, building_BasicLevel::TABLE_NAME);
    }

    /**
     * Поиск записи в таблице по идентификатору первичного ключа и ограничивается идентификатором персонажа, для
     *
     * @param int $idBuilding
     * @param int $idPersonage
     * @return personage_Building|null
     */
    public function findBuildingById($idBuilding, $idPersonage)
    {
        $sql = "SELECT *, UNIX_TIMESTAMP(`pb`.finish_time_construction) as finish_time_construction
		 		FROM %s as pb WHERE `pb`.%s=%d AND `pb`.personage_id=%d";

        return $this->find($sql, self::TABLE_NAME, $this->pk(), $idBuilding, $idPersonage);
    }

    /**
     * Получить общую производительность работающих ферм для города персонажа
     *
     * @param int $idPersonage
     * @return int $performance
     */
    public function findPerformanceAllWorkingFarmInCity($idPersonage, $idCity)
    {
        $sql = "
			SELECT
				SUM(`performance`) as `performance`
		 	FROM 
				`%1\$s` PB 
			LEFT JOIN `%4\$s` B
				ON B.`id` = PB.`building_id`
			WHERE 
				PB.`personage_id` = '%2\$d' 
				AND PB.`status_production` = '%3\$s'
				AND B.`name` = '%5\$s'
				AND PB.`city_id` = '%6\$d'";

        $result = $this->find(
            $sql,
            self::TABLE_NAME,
            $idPersonage,
            self::STATUS_PRODUCTION,
            building_Mapper::TABLE_NAME,
            building_Mapper::KEY_BUILDING_FARM,
            $idCity
        );

        return $result->performance ? $result->performance : 0;
    }


    /**
     * Поиск здания для города по классификации
     *
     * Метод используется в CRON
     * @param $classifier
     * @param $idCity
     * @return array|personage_Building
     */
    public function findBuildingInCityByClassifier($classifier, $idCity)
    {
        $sql = "SELECT `pb`.id_building_personage, `pb`.current_level,
                        `pc`.id, `pc`.population, `pc`.happiness,
                        `b`.name, `pbs`.current_data_bonus,
                        `bd`.number_products,`r`.id as resource_id, `r`.name_resource,
                        UNIX_TIMESTAMP(`prs`.last_visit) as last_visit,
                        `prs`.personage_resource_value, `pbl`.data_bonus,

                          /*Получаем бонусы здания 'СКЛАД'*/
                          (SELECT `current_data_bonus`
                           FROM %7\$s as pbs
                           INNER JOIN %1\$s as pb
                              ON (`pbs`.id_building_personage = `pb`.id_building_personage)
                           INNER JOIN %3\$s as b
                             ON (`b`.id = `pb`.building_id)
                           WHERE `b`.name = '%13\$s'
                           AND `pb`.city_id = %8\$s) as bonus_building_warehouse,

                           /*Получаем вместительность здания 'СКЛАД'*/
                           (SELECT `bd`.warehouse
                            FROM %5\$s as bd
                            INNER JOIN %1\$s as pb
                              ON (`bd`.level = `pb`.current_level)
                            INNER JOIN %3\$s as b
                              ON (`b`.id = `pb`.building_id)
                            WHERE `b`.name = '%13\$s'
                            AND `pb`.city_id = %8\$s
                            AND `b`.id  = `bd`.building_id) as capacity_building_warehouse
                 FROM %1\$s as pb
                 INNER JOIN %2\$s as pc
                    ON (`pb`.city_id = `pc`.id)
                 INNER JOIN %3\$s as b
                    ON (`b`.id = `pb`.building_id)
                 INNER JOIN %5\$s as bd
                    ON (`bd`.building_id = `pb`.building_id)
                       AND `bd`.level = `pb`.current_level
                 LEFT JOIN %7\$s as pbs
                    ON (`pbs`.id_building_personage = `pb`.id_building_personage)
                 INNER JOIN %9\$s as r
                    ON (`r`.building_id = `pb`.building_id)
                 INNER JOIN %11\$s as prs
                    ON (`r`.id = `prs`.resource_id)
                       AND `prs`.personages_cities_id = `pb`.city_id
                 INNER JOIN %12\$s as pbl
                    ON (`pb`.building_id = `pbl`.building_id)
                 WHERE `b`.classifier = '%4\$s'
                 AND `b`.name != '%10\$s'
                 AND `pb`.status_construction = '%6\$s'
                 AND `pb`.status_production = '%14\$s'
                 AND `pc`.id = %8\$s
                 AND `pb`.current_level > 0";

        return $this->findAll($sql, self::TABLE_NAME, personage_City::TABLE_NAME, building_Mapper::TABLE_NAME, $classifier,
            building_Development::TABLE_NAME, self::STATUS_CONSTRUCTION_FINISH,
            personage_BuildingBonus::TABLE_NAME, $idCity, resource_Mapper::TABLE_NAME,
            building_Mapper::KEY_BUILDING_HOUSE, personage_ResourceState::TABLE_NAME,
            building_BasicLevel::TABLE_NAME, building_Mapper::KEY_BUILDING_WAREHOUSE, self::STATUS_PRODUCTION);
    }


    /**
     * Обновляем текущее значение производительности здания
     *
     * @param $idBuildingPersonage ИД здания персонажа
     * @param $value значение производительности
     * @return bool
     */
    public function upgradeBuildingPerformance($idBuildingPersonage, $value)
    {
        $sql = "
			UPDATE  
				`%1\$s`
            SET 
				%3\$s
			WHERE 
				`id_building_personage` = '%2\$d'";

        $fields .= '`performance` = ' . $value . '';

        $result = $this->query(
            $sql,
            self::TABLE_NAME,
            $idBuildingPersonage,
            $fields
        );

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Поиск зданий, у которых закончено выделенное время для постройки или улучшения.
     *
     * @param bool $idPersonageBuilding
     * @return array|personage_Building
     */
    public function findBuildingsWithFinishCreateAndImprove($idPersonageBuilding = false)
    {
        $sql = "SELECT `pb`.building_id, `pb`.id_building_personage, `pb`.personage_id,
					   `bbl`.data_bonus, `pbbs`.current_data_bonus, `bd`.count_fame as fame
				FROM  %1\$s as pb
				LEFT JOIN %2\$s as bbl
				   ON `pb`.building_id = `bbl`.building_id
				LEFT JOIN %3\$s as pbbs
				   ON `pb`.id_building_personage = `pbbs`.id_building_personage
				LEFT JOIN %7\$s as bd
				   ON `pb`.building_id = `bd`.building_id
				     AND  `bd`.level = `pb`.current_level + 1
				WHERE";

        if ($idPersonageBuilding === false) {
            $sql .= " `pb`.`finish_time_construction` <= '%4\$s'
					    AND `pb`.`status_construction` = '%5\$s'";
        } else {
            $sql .= " `pb`.`finish_time_construction` <= '%4\$s'
					    AND `pb`.`status_construction` = '%5\$s'
					    AND `pb`.id_building_personage = %6\$d";
        }

        return $this->findAll(
            $sql, self::TABLE_NAME, building_BasicLevel::TABLE_NAME, personage_BuildingBonus::TABLE_NAME,
            date("Y-m-d H:i:s"), self::STATUS_CONSTRUCTION_PROCESSING, $idPersonageBuilding,
            building_Development::TABLE_NAME);
    }

    /**
     * Завершение создания или улучшения уровня здания вместе с сохранением бонусов.
     *
     * @param array $bonuses
     * @throws ErrorException|DBException
     * @return personage_Building
     */
    public function finishConstructionOrImproveBuildingWithSaveCalculationBonuses(array $bonuses)
    {
        $pk = $this->pk();
        $idBuildingPersonage = $this->$pk;
        if ($idBuildingPersonage == null)
            throw new ErrorException('Personage building model must be init!');

        $packCurrentBonuses = serialize($bonuses);

        try {
            $resultBegin = $this->begin();

            // TODO: Добавить ограничение для максимального уровня при подъёме
            $sqlUpdatePersonageBuildings =
                "UPDATE %s SET `current_level` = `current_level` + 1, `status_construction`='%s' WHERE `%s`=%d";
            $updatePersonagesBuildings = $this->query(
                $sqlUpdatePersonageBuildings, self::TABLE_NAME, self::STATUS_CONSTRUCTION_FINISH, $this->pk(), $idBuildingPersonage
            );
            if ($updatePersonagesBuildings->isError())
                throw new DBException(implode(' : ', $updatePersonagesBuildings->getErrors()));

            $sqlUpdateBonuses = "UPDATE %s SET `current_data_bonus`='%s' WHERE `%s`=%d";
            $updateBonuses = $this->query(
                $sqlUpdateBonuses, personage_BuildingBonus::TABLE_NAME, $packCurrentBonuses, $this->pk(), $idBuildingPersonage
            );
            if ($updateBonuses->isError()) throw new DBException(implode(' : ', $updateBonuses->getErrors()));

            $resultCommit = $this->commit();
        } catch (DBException $e) {
            $this->rollback();
            throw new DBException(
                'finishConstructionOrImproveBuildingWithSaveCalculationBonuses : ' . $e->getMessage()
            );
        }

        return $resultCommit;
    }


    /**
     * Завершение постройки или улучшений зданий, с добовлением бонусом
     * @param $buildings модели personage_Building но в них сложены и улучшения для этих зданий как в методе findBuildingsWithFinishCreateAndImprove
     * @param array $buildings
     * @return bool
     * @throws ErrorException
     */
    public function finishConstructOrImproveBuildings(array $buildings)
    {

        /* @var $personageBuilding personage_Building */
        foreach ($buildings as $personageBuilding) {
            $currentIdBuilding = $personageBuilding->id_building_personage;

            // Если нет ещё бонусов у здания (здание только строится, но произошла ошибка на клиенте при создании здания)
            // добавляем в таблицу бонусов personages_building_bonus_state
            // Блок является подстраховкой от ошибок с клиента при событии создания здания.
            if ($personageBuilding->current_data_bonus == null) {
                $result = personage_BuildingBonus::model()->saveNewBonusesByIdBuildingPersonage(
                    $personageBuilding->id_building_personage,
                    $personageBuilding->data_bonus
                );

                if ($result === false) throw new ErrorException("Bad save new row bonuses in cron");

                // Задаём текущим бонусам здания начальные.
                $personageBuilding->current_data_bonus = $personageBuilding->data_bonus;
            }

            // Распаковка текущих бонусов для здания
            $currentBonusesForBuildings = unserialize($personageBuilding->current_data_bonus);
            if (!is_array($currentBonusesForBuildings))
                throw new ErrorException('Unserialize current bonuses not array!');

            $baseBonusesForBuildings = unserialize($personageBuilding->data_bonus);
            if (!is_array($baseBonusesForBuildings))
                throw new ErrorException('Unserialize base bonuses not array!');

            // Калькуляция бонусов для здания
            $changeCurrentBonuses = personage_BuildingBonus::calculationCurrentBonusesForBuilding(
                $baseBonusesForBuildings, $currentBonusesForBuildings
            );

            //Добавляем очки славы персонажа за постройку здания
            if ($personageBuilding->fame != 0 AND $personageBuilding->personage_id != NULL) {
                $doneStatePersonage = personage_State::model()->formPartOfFame($personageBuilding->fame * 10,
                    $personageBuilding->personage_id);
            }

            if ($doneStatePersonage === false)
                throw new ErrorException('Do not have changed the personage ' . $personageBuilding->personage_id);

            $isFinished =
                $personageBuilding->finishConstructionOrImproveBuildingWithSaveCalculationBonuses($changeCurrentBonuses);
        }

        if (isset($isFinished)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Изменть текущий статус выроботки производства зданием персонажа
     *
     * @param $status
     * @param $idBuildingPersonage
     * @return bool
     */
    public function changeCurrentStatusProductionBuildingPersonage($status, $idBuildingPersonage)
    {
        $sql = "UPDATE %s SET `status_production`='%s' WHERE `id_building_personage` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $status, $idBuildingPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Изменть текущий статус постройки зданиея персонажа
     *
     * @param $status
     * @param $idBuildingPersonage
     * @return bool
     */
    public function changeCurrentStatusConstructionBuildingPersonage($status, $idBuildingPersonage)
    {
        $sql = "UPDATE %s SET `status_construction`='%s' WHERE `id_building_personage` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $status, $idBuildingPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Начать улечшение здания
     * При запросе устанавливается временная метка окончания улучения здания,
     * а так же устанавливается статус процесса улучшения
     *
     * @param $idBuildingPersonage
     * @param $minute
     * @param $status
     * @return bool
     */
    public function beginImprovingBuildingPersonage($idBuildingPersonage, $minute, $status)
    {
        $sql = "UPDATE %s
                SET `finish_time_construction` = TIMESTAMP(DATE_ADD(NOW(),INTERVAL %d MINUTE)),
                    `status_construction` = '%s'
                WHERE `id_building_personage` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $minute, $status, $idBuildingPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить необходимое количество населения в городе
     * для [ресурсных] работающих зданий
     *
     * @param ind $idCity
     * @return int
     */
    public function getNeedPeopleForBuilding($idCity)
    {
        $sql = "
			SELECT
				SUM(BD.`number_staff`) as `number_staff`
			FROM
				`%s` PB
			LEFT JOIN 
				`%s` BD ON PB.`building_id` = BD.`building_id` 
				AND PB.`current_level` = BD.`level`
			WHERE
				PB.`city_id` = %d
				AND PB.`status_production` = '%s'";

        $result = $this->find(
            $sql,
            self::TABLE_NAME,
            building_Development::TABLE_NAME,
            $idCity,
            personage_Building::STATUS_PRODUCTION
        );

        if ($this->isError())
            throw new DBException('Same error in query on `getNeedPeopleForBuilding`');

        return $result->number_staff;
    }

    /**
     * Удаляем здание, бонусы и внутренние улучшения зданий
     * @param $idBuildingPersonage
     * @return bool
     * @throws DBException
     */
    public function ruinBuildingPersonage($idBuildingPersonage)
    {
        try {
            $resultBegin = $this->begin();

            //Удаляем здание
            $sqlDeletePersonageBuildings = "DELETE FROM %s WHERE `%s`=%d";
            $deletePersonagesBuildings = $this->query($sqlDeletePersonageBuildings, self::TABLE_NAME,
                $this->pk(), $idBuildingPersonage);
            if ($deletePersonagesBuildings->isError())
                throw new DBException(implode(' : ', $deletePersonagesBuildings->getErrors()));

            //Удаляем бонусы здания
            $sqlDeleteBonuses = "DELETE FROM %s WHERE `%s`=%d";
            $deleteBonuses = $this->query($sqlDeleteBonuses, personage_BuildingBonus::TABLE_NAME,
                $this->pk(), $idBuildingPersonage);
            if ($deleteBonuses->isError()) throw new DBException(implode(' : ', $deleteBonuses->getErrors()));

            //Получаем все внутренние улучшения здания
            $buildingImprove = personage_Improve::model()->findImproveOnIdBuildingPersonage($idBuildingPersonage);

            if ($buildingImprove != NULL) {

                //Удаляем внутренние улучшения здания
                personage_Improve::model()->deleteAllImproveSpecificallyBuilding($buildingImprove);
            }

            $resultCommit = $this->commit();
        } catch (DBException $e) {
            $this->rollback();
            throw new DBException(
                'ruinBuildingPersonage : ' . $e->getMessage()
            );
        }

        return $resultCommit;
    }


    /**
     * Остановить производство здания, где необходимо большое кол-во
     * рабочих из каждой категории. Первыми закрываются здания
     * производящие ресурсы, затем основные здания.
     *
     * @param $idCity
     * @return personage_Building
     * @throws DBException
     */
    public function stopProductionBuildingWhereBiggestCountPeople($idCity)
    {
        $sql = "
			UPDATE 
				`%s` 
			SET 
				`status_production` = '%s'
			WHERE 
				`id_building_personage` = (
					SELECT * FROM (
						SELECT
							PB.`id_building_personage`
						FROM
							`%s` PB
						LEFT JOIN 
							`%s` BD ON PB.`building_id` = BD.`building_id` 
							AND PB.`current_level` = BD.`level`
						LEFT JOIN
							`%s` B ON B.`id` = BD.`building_id`
						WHERE
							PB.`city_id` = %d
							AND PB.`status_production` = '%s'
							AND BD.`number_staff` IS NOT NULL
						ORDER BY 
							B.`classifier` DESC, 
							BD.`number_staff` DESC
						LIMIT 1
					) AS `id`
				)";

        $result = $this->query(
            $sql,
            self::TABLE_NAME,
            personage_Building::STATUS_PRODUCTION_STOP,
            self::TABLE_NAME,
            building_Development::TABLE_NAME,
            building_Mapper::TABLE_NAME,
            $idCity,
            personage_Building::STATUS_PRODUCTION
        );

        if ($this->isError())
            throw new DBException('Same error in query on `stopProductionBuildingWhereBiggestCountPeople`');

        return $result;
    }

    /**
     * Определяем количество зданий одного типа
     *
     * @param $idBuilding
     * @param $idCity
     * @return personage_Building
     */
    public function determineCountOfBuildingsOnIdOfBuilding($idBuilding, $idCity)
    {
        $sql = "SELECT count(`pb`.building_id) as total_number_building
               FROM %1\$s as pb
               INNER JOIN %2\$s as b
                 ON (`pb`.building_id = `b`.id)
               WHERE `pb`.building_id = %3\$d
               AND `pb`.city_id = %4\$d
               AND `pb`.current_level > %5\$d
               AND `pb`.status_construction != '%6\$s'";

        return $this->find($sql, self::TABLE_NAME, building_Mapper::TABLE_NAME, $idBuilding, $idCity,
            self::NO_LEVEL_BUILDING, self::STATUS_CONSTRUCTION_PROCESSING);
    }
}
