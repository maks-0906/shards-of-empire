<?php
/**
 * Файл содержит запросы к базе данных связанные с ресурсами персонажа.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель отображающая на таблицу в БД, содержащую состояние ресурсов у персонажа.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_ResourceState extends Mapper
{
    const NO_VALUE = 0;
    const TABLE_NAME = 'personages_resources_state';

    // Константа-коэффициент расхода ресурса для приведения расхода ресурса
    // за час
    const COUNT_UPDATE_PER_HOUR = 20;

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
     * @return personage_ResourceState
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
        $this->idPersonage = Auth::getIdPersonage();
        $this->doneLocationCity = Auth::isCurrentLocationCity();
    }

    /**
     * Первичный ключ для текущей таблицы.
     * @return string
     */
    public function pk()
    {
        return 'id_personages_resources_state';
    }

    /**
     * Создание первоначального состояния ресурсов у персонажа.
     * Запись наполняется нолями при начальном состоянии.
     *
     * @param int $idPersonage
     * @param int $idCity
     * @return bool
     */
    public function insertPrimaryDataResources($idPersonage, $idCity)
    {
        $allResources = resource_Mapper::model()->findAllResources();

        if ($allResources === false) {
            return false;
        }

        foreach ($allResources as $resource) {

            $resourceValue = self::NO_VALUE;
            $initializeResource = data_InitializePersonage::model()->getInitializePersonageResources();

            if (array_key_exists($resource->name_resource, $initializeResource)) {
                $resourceValue = $initializeResource[$resource->name_resource];
            }

            $sql = "INSERT %s
                    SET `id_personages_resources_state` = NULL,
					    `id_personage` = %d,
					    `personages_cities_id` = %s,
					    `resource_id` = %d,
					    `personage_resource_value` = %d";

            $result = $this->query(
                $sql,
                $this->tableName(),
                $idPersonage,
                (($resource->name_resource == resource_Mapper::KEY_RESOURCE_GOLD) ||
                    ($resource->name_resource == resource_Mapper::KEY_RESOURCE_AMBER) ||
                    ($resource->name_resource == resource_Mapper::KEY_RESOURCE_BLESSING)) ? "NULL" : $idCity,
                $resource->id,
                $resourceValue
            );
        }

        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows))
            return true;
        else
            return false;
    }


    /**
     * Находим данные для ресурсов города, связанные с конкретным ресурсом
     *
     * @param bool $idCity
     * @param int $idResources
     * @param $idPersonage
     * @return array|personage_ResourceState
     */
    public function findPropertiesResources($idCity, $idResources = 0, $idPersonage)
    {
        if ($this->doneLocationCity === true) {

            $sql = "SELECT FLOOR(`prs`.personage_resource_value) as total_number_resource_city,
                           FLOOR(`prs`.resource_consumption) as resource_consumption,
                           `r`.name_resource,

                     /*Получаем сумму ресурса у персонажа*/
                      (SELECT FLOOR(sum(personage_resource_value))
                       FROM %1\$s as prs
                       WHERE `prs`.id_personage = %5\$d";

            $sql .= ($idResources == 0) ? " AND `prs`.resource_id = (SELECT min(id) FROM %2\$s)" : " AND `prs`.resource_id=%4\$d";
            $sql .= ") AS total_number_resources
                     /*Конец получения суммы ресурса для персонажа*/

                FROM %1\$s as prs, %2\$s as r
                WHERE `prs`.resource_id=`r`.id
                AND `prs`.personages_cities_id=%3\$d";

            $sql .= ($idResources == 0) ? " AND `prs`.resource_id = (SELECT min(id) FROM %2\$s)" : " AND `prs`.resource_id =%4\$d";

            return $this->findAll($sql, self::TABLE_NAME, resource_Mapper::TABLE_NAME, $idCity, $idResources, $idPersonage);
        } else {
            return array();
        }
    }


    /**
     * Получить суммы ресурсов у персонажа
     *
     * @param $idResources
     * @param $idPersonage
     * @param bool $comparisonResources
     * @return array|personage_ResourceState
     */
    public function findAmountResourcesPersonage($idResources, $idPersonage, $comparisonResources = false)
    {
        $sql = "SELECT FLOOR(`prs`.personage_resource_value) AS total_number_resources,
                       `prs`.resource_consumption,
                       `r`.name_resource
                FROM %1\$s as prs, %2\$s as r";

        $sql .= " WHERE `prs`.resource_id=%3\$d
                  AND `prs`.personages_cities_id IS NULL
                  AND `prs`.id_personage = %4\$d";

        if ($comparisonResources === true) {
            $sql .= " AND `prs`.resource_id =`r`.id";
        }

        return $this->findAll($sql, self::TABLE_NAME, resource_Mapper::TABLE_NAME, $idResources, $idPersonage);
    }

    /**
     * Находим текущие ресурсы пользователя по ИД ресурса
     *
     * @param $idPersonage ИД персонажа
     * @param $idResource ИД ресурса
     * @param $idCity int() | array() | string("NULL") ИД города или массив ИД городов или NULL
     * @return int текушее значение ресурса
     * @throws E1Exception
     */
    public function CurrentResourceState($idPersonage, $idCity, $idResource)
    {
        $sql = "
			SELECT
				SUM(`prs`.`personage_resource_value`) as `resource_value`
			FROM
				`%1\$s` as prs
			WHERE
				`prs`.`resource_id` = '%2\$d'
				AND `prs`.`id_personage` = '%3\$d'
				";

        if ($idCity == "NULL")
            $sql .= "AND `prs`.`personages_cities_id` IS NULL";
        elseif (is_array($idCity)) {
            $addSql = array();

            foreach ($idCity as $item)
                $addSql[] = "`prs`.`personages_cities_id` = '" . $item . "'";

            $sql .= "AND (" . implode(" OR ", $addSql) . ")";
        } else
            $sql .= "AND `prs`.`personages_cities_id` = '%4\$s'";

        $result = $this->find(
            $sql,
            self::TABLE_NAME,
            $idResource,
            $idPersonage,
            $idCity
        );

        if (!$this->isError())
            return $result->resource_value;
        else
            return 0;
    }

    /**
     * Поиск всех ресурсов для города с данными о них и данными состояния ресурсов персонажа.
     *
     * @param bool $idCity
     * @return mixed
     */
    public function findAllResourcesWithResourceStatePersonageCity($idCity)
    {
        $sql = "SELECT  FLOOR(`prs`.`personage_resource_value`) as `personage_resource_value`, `prs`.`resource_consumption`, `r`.`name_resource`,
				  (SELECT FLOOR(sum(personage_resource_value))
				   FROM %s
				   WHERE (`personages_cities_id`=%d OR `personages_cities_id` IS NULL)
				   AND `id_personage`=%d) AS `total_number_resources`
			FROM %s as prs, %s as r
			WHERE `prs`.`resource_id`=`r`.`id`
			AND (`prs`.`personages_cities_id`=%d OR `prs`.personages_cities_id IS NULL)
			AND `prs`.`id_personage`=%d";

        return $this->findAll($sql, self::TABLE_NAME, $idCity, $this->idPersonage,
            self::TABLE_NAME, resource_Mapper::TABLE_NAME, $idCity, $this->idPersonage);
    }

    /**
     * Поиск базовых ресурсов (,которые можно перевозить армией) из города.
     *
     * @param int $idCity
     * @return array|personage_ResourceState[]
     */
    public function findBaseResourcesForCity($idCity)
    {
        $sql = "SELECT
					FLOOR(`prs`.personage_resource_value) as personage_resource_value,
					FLOOR(`prs`.resource_consumption) as resource_consumption,
				    `r`.`name_resource`,
					`prs`.id_personages_resources_state as id
			FROM %s as prs, %s as r
			WHERE `prs`.`resource_id`=`r`.`id` AND `prs`.`personages_cities_id`=%d AND `prs`.id_personage=%d";

        return $this->findAll(
            $sql,
            self::TABLE_NAME, resource_Mapper::TABLE_NAME, $idCity, $this->idPersonage
        );
    }

    /**
     * Получаем доход ресурсов
     * Аналогичная система подсчетов существует на CRON (cron/building/resources.php)
     *
     * @param $building
     * @param $idCity
     * @param $nameResource
     * @return float|int|string
     */
    public function calculateIncomeResource($building, $idCity, $nameResource)
    {
        $minutes = 60;
        $currentBuildingBonus = array();
        $numberResources = '';

        if ($building->name_building != NULL) {
            $currentBuildingBonus = personage_BuildingBonus::model()->findCurrentBonusesForSpecificBuilding($building->name_building,
                $idCity);

            $city = personage_City::model()->findById($idCity);
            $religionPersonage = personage_Religion::model()->findReligionPersonage($city->id_personage);
            $doneFractionsVisigoths = personage_Fraction::model()->isFractionsVisigoths($city->id_personage);
        }

        if (is_array($currentBuildingBonus->result)) {
            foreach ($currentBuildingBonus->result as $buildingBonus) {

                $bonus = unserialize($buildingBonus['current_data_bonus']);
                if (isset($bonus["bonus_number_products"]['basic'])) {

                    $numberProduction = $bonus["bonus_number_products"]['basic'];

                    //Получаем подсчитанное количество ресурсов в зависимости от религии персонажа
                    $numberResourcesBonusesReligion = personage_Religion::model()->calculateNumberResourcesOfReligion(
                        $numberProduction,
                        $religionPersonage->name,
                        $nameResource);

                    //Добавляем значение от бонуса религии
                    if ($numberResourcesBonusesReligion != NULL) {
                        $numberProduction = $numberProduction + $numberResourcesBonusesReligion;
                    }

                    //Добавляем бонусы от фракции если персонаж состоит во фракции "Вестготы"
                    if ($doneFractionsVisigoths === true) {
                        $numberResourcesBonusesFractionsVisigoths = personage_Fraction::model()->calculateBonusesForResourcesFractionsVisigoths($numberProduction);
                        $numberProduction = $numberProduction + $numberResourcesBonusesFractionsVisigoths;
                    }

                    $numberResources += personage_ResourceState::model()->calculateProductionResources($building->number_products, false,
                        $bonus["bonus_time_production"]['basic'],
                        $numberProduction,
                        $minutes);
                }
            }
        }

        $income = $this->calculateProductionResourcesInHappiness($numberResources, $city->happiness);

        if ($income == '') {
            return self::NO_VALUE;
        } else {
            return $income;
        }
    }

    /**
     * Получение значение количество ресурсов по СЧАСТЬЮ
     *
     * На производительность всех ресурсодобывающих зданий влияет счастье населения.
     * При уровне счастье  100, производительность здания 100% + изученные бонусы.
     * При счастье населения  50 берётся только 50% от производительности здания с учётом изученных технологий.
     *
     * @param $numberResources
     * @param $happiness
     * @return float
     */
    public function calculateProductionResourcesInHappiness($numberResources, $happiness)
    {
        return ceil(($numberResources / 100) * $happiness);
    }

    /**
     * Получение данных по ресурсам для определённого исследования с учётом города и здания, в которых они изучаются.
     *
     * @param int $idPersonage
     * @param int $idResearch
     * @param int $idBuilding
     * @return array|Mapper[]
     */
    public function findResourcesCityForResearch($idPersonage, $idResearch, $idBuilding)
    {
        $coordinates = Auth::getCurrentLocationCoordinates();

        $sql = "SELECT DISTINCT
					`prs`.resource_id, FLOOR(`prs`.personage_resource_value) as `personage_resource_value`, `rs`.price,
					`r`.name_resource,
					(SELECT `pb`.current_level
					 FROM  %1\$s as pb
					 WHERE `pb`.building_id = %2\$d
						   AND `pb`.city_id = (SELECT `id`
											   FROM %3\$s
											   WHERE `x_c` =%4\$d AND `y_c` =%5\$d AND `id_personage` =%6\$d))
												as building_current_level

				FROM %7\$s as rs
				LEFT OUTER JOIN %8\$s as r ON `rs`.resources_id=r.id
				LEFT JOIN %9\$s as prs ON `rs`.resources_id=prs.resource_id AND `prs`.personages_cities_id IS NULL
				WHERE `rs`.research_id=%10\$d
					  AND `rs`.level_for_costs=(SELECT current_level
												FROM %11\$s as prst
												WHERE prst.id_personage=%6\$d AND prst.current_research_id=%10\$d)+1
					  AND `prs`.id_personage=%6\$d
					  /*AND `prs`.personage_resource_value >= `rs`.price*/";

        return $this->findAll($sql,
            personage_Building::TABLE_NAME, $idBuilding,
            personage_City::TABLE_NAME, $coordinates['x'], $coordinates['y'], $idPersonage,
            research_Costs::TABLE_NAME, resource_Mapper::TABLE_NAME, self::TABLE_NAME,
            $idResearch, personage_ResearchState::TABLE_NAME, personage_ResourceState::TABLE_NAME
        );
    }

    /**
     * Вычитаем с ресурсов необходимое значение  и обновляем поля
     * Обновление по нескольким полям одновременно.
     *
     * @param array $initialResource
     * @return array|bool
     * @throws ErrorException|DBException
     */
    public function updateResourcesWhenPerformingCalculations($initialResource)
    {
        if ($this->doneLocationCity === true) {
            $coordinates = Auth::getCurrentLocationCoordinates();
            try {
                $this->begin();
                $templateSqlForSubResource =
                    "UPDATE %1\$s
					 SET `personage_resource_value` = `personage_resource_value` - %2\$d
					 WHERE (`personages_cities_id` = (SELECT `id` FROM %3\$s WHERE `x_c` = %4\$d AND `y_c` = %5\$d AND `id_personage` = %6\$d)
					       OR (`personages_cities_id` IS NULL AND `id_personage` = %6\$d))
					 AND `resource_id`=%7\$d
					 AND `id_personage` = %6\$d";

                foreach ($initialResource as $resource) {
                    $process = $this->query(
                        $templateSqlForSubResource,
                        self::TABLE_NAME, $resource->price,
                        personage_City::TABLE_NAME, $coordinates['x'], $coordinates['y'],
                        $this->idPersonage, $resource->resource_id
                    );

                    if ($process->isError())
                        throw new DBException(
                            "Bad update resource performing calculation: " . implode(',', $process->getErrors())
                        );
                }

                $affected_rows = $this->getAffectedRows($process);

                if (isset($affected_rows)) {
                    $this->commit();
                    return true;
                } else {
                    return false;
                }
            } catch (DBException $e) {
                $this->rollback();
            }
        } else {
            return array();
        }
    }

    /**
     * Обновление количества определённых ресурсов в текущем своём городе персонажа.
     * :WARNING: Метод учитывает текущее расположение персонажа по координатам с выборкой своего города.
     * Если персонаж будет не в своём городе обновления не произойдёт?!
     *
     * @param int $amountResource
     * @param int $idResource
     * @return bool
     * @throws DBException
     */
    public function updateAmountResourcePersonageInCurrentYourCity($amountResource, $idResource)
    {
        $coordinates = Auth::getCurrentLocationCoordinates();
        $templateSqlForSubResource =
            "UPDATE %1\$s
			 SET `personage_resource_value` = `personage_resource_value` - %2\$d
			 WHERE (`personages_cities_id` =
			 			(SELECT `id` FROM %3\$s WHERE `x_c` = %4\$d AND `y_c` = %5\$d AND `id_personage` = %6\$d)
			 			OR `personages_cities_id` IS NULL
			 		)
					AND `resource_id`=%7\$d AND `id_personage` = %6\$d";

        $process = $this->query(
            $templateSqlForSubResource,
            self::TABLE_NAME, $amountResource,
            personage_City::TABLE_NAME, $coordinates['x'], $coordinates['y'],
            $this->idPersonage, $idResource
        );

        if ($process->isError())
            throw new DBException(
                "Bad update resource performing calculation: " . implode(',', $process->getErrors())
            );

        $affected_rows = $this->getAffectedRows($process);
        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Списание ресурсов из города персонажа без учёта местонахождения персонажа в городе.
     *
     * @param int $amountResource
     * @param int $idResource
     * @param int $idCity
     * @return bool
     * @throws DBException
     */
    public function writeDownResourceInCityPersonage($amountResource, $idResource, $idCity)
    {
        $sql = "UPDATE %1\$s
			 	SET `personage_resource_value` = `personage_resource_value` - %2\$d
				 WHERE `personages_cities_id` = %3\$d AND `resource_id`=%4\$d AND `id_personage` = %5\$d";

        $process = $this->query(
            $sql,
            self::TABLE_NAME, $amountResource,
            $idCity, $idResource, Auth::getIdPersonage()
        );

        if ($process->isError())
            throw new DBException(
                "Bad update resource performing calculation: " . implode(',', $process->getErrors())
            );

        $affected_rows = $this->getAffectedRows($process);
        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавление ресурсов в города персонажа без учёта местонахождения персонажа в городе.
     *
     * @param int $amountResource
     * @param int $idResource
     * @param int $idCity
     * @return bool
     * @throws DBException
     */
    public function writeUpResourceInCityPersonage($amountResource, $idResource, $idCity)
    {
        $sql = "UPDATE %1\$s
				SET `personage_resource_value` = `personage_resource_value` - %2\$d
				 WHERE `personages_cities_id` = %3\$d AND `resource_id`=%4\$d AND `id_personage` = %5\$d";

        $process = $this->query(
            $sql,
            self::TABLE_NAME, $amountResource,
            $idCity, $idResource, Auth::getIdPersonage()
        );

        if ($process->isError())
            throw new DBException(
                "Bad update resource performing calculation: " . implode(',', $process->getErrors())
            );

        $affected_rows = $this->getAffectedRows($process);
        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Формируем часть запроса необходумую для вычитания ресурсов
     *
     * Для корректной работы метода в исходном объекте должны присутствовать
     * значения (resource_id - ID ресурса), (resource_value - значение ресурса)
     * которые необходимо получать в случае необходимости при помощи алиасов.
     *
     * @param $currentResource
     * @return array|bool
     */
    public function formedResource($currentResource)
    {
        $sql_part = '';
        $in = '';

        foreach ($currentResource as $res) {
            $sql_part .= "WHEN resource_id = $res->resource_id THEN $res->resource_value ";
            $in .= $res->resource_id . ',';
        }

        //Удаляем последнюю запятую в части запроса
        $in_part = substr($in, 0, -1);

        $formedSqlPart = array('sql_part' => $sql_part, 'in_part' => $in_part);

        if (!empty($formedSqlPart)) {
            return $formedSqlPart;
        } else {
            return false;
        }
    }

    /**
     * Базовый метод для обновления полей значения ресурсов
     *
     * @param $idCity
     * @param $idResource
     * @param $fields
     * @param bool $lastVisit
     * @return bool
     */
    public function upgradeFieldResourceState($idCity, $idResource, $fields, $lastVisit)
    {
        $sql = "UPDATE  %1\$s
                SET %3\$s";

        //Обновляем поле последнего посещения внешней программы
        if ($lastVisit === true) {
            $sql .= ", `last_visit` = NOW()";
        }

        $sql .= " WHERE `personages_cities_id` = %4\$d
                             AND `resource_id` = %2\$d";

        $result = $this->query($sql, self::TABLE_NAME, $idResource, $fields, $idCity);
        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Обновление ресурсов персонажа (золото, благославление, янтарь)
     *
     * @param $idPersonage
     * @param $idResource
     * @param $fields
     * @return bool
     */
    public function upgradeFieldPersonageResource($idPersonage, $idResource, $fields)
    {
        $sql = "UPDATE  %1\$s
                SET %2\$s
                WHERE `resource_id` = %3\$d
                AND (`personages_cities_id` IS NULL AND `id_personage` = %4\$d)";

        $result = $this->query($sql, self::TABLE_NAME, $fields, $idResource, $idPersonage);
        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Подготовка подзапроса для обновления ресурса персонажа путем его увеличения
     *
     * @param $idPersonage
     * @param $idResource
     * @param $resourceValue
     * @return bool
     */
    public function upgradePersonageResourceValueIncrease($idPersonage, $idResource, $resourceValue)
    {
        $fields = '`personage_resource_value` = `personage_resource_value` + ' . $resourceValue . '';

        return $this->upgradeFieldPersonageResource($idPersonage, $idResource, $fields);
    }

    /**
     * Добавляем текущее значение к ресурсам
     *
     * @param $idCity
     * @param $idResource
     * @param $resourceValue
     * @param bool $lastVisit
     * @return bool
     */
    public function upgradeResourceValue($idCity, $idResource, $resourceValue, $lastVisit = false)
    {
        $fields = '`personage_resource_value` = `personage_resource_value` + ' . $resourceValue . '';

        return $this->upgradeFieldResourceState($idCity, $idResource, $fields, $lastVisit);
    }

    /**
     * Обнулить счетчик расхода ресурса по ИД персонажа
     *
     * @param $idPersonage
     * @return bool
     */
    public function clearResourceConsumption($idPersonage)
    {
        $sql = "UPDATE %1\$s  SET `resource_consumption` = '0' WHERE `id_personage` = %2\$d";

        $result = $this->query($sql, self::TABLE_NAME, $idPersonage);

        return !$result->isError();
    }

    /**
     * Платим жалование юнитам
     *
     * @param $arSalary
     * array(
     *         "RESOURCE_ID" => array(
     *             "CITY_ID" =>
     *                 "RESOURCE_VALUE",
     *             ...
     *         ),
     *         ...
     * )
     * @return bool
     */
    public function payUnitsSalary($idPersonage, $arSalary)
    {
        try {
            $this->begin();

            foreach ($arSalary as $idResource => $arCity) {
                foreach ($arCity as $idCity => $resourceValue) {
                    $sql = "
						UPDATE 
							`%1\$s`
						SET 
							`personage_resource_value` = `personage_resource_value` - '%2\$f',
							`resource_consumption` = `resource_consumption` + '%2\$f' * '%6\$d',
							`last_visit` = NOW()
						WHERE ";

                    if ($idCity == "NULL")
                        $sql .= "`personages_cities_id` IS %3\$s";
                    else
                        $sql .= "`personages_cities_id` = '%3\$d'";

                    $sql .= " AND `resource_id` = '%4\$d'
                            AND `personage_resource_value` >= '%2\$d'
                            AND `id_personage` = '%5\$d'";

                    $result = $this->query(
                        $sql,
                        self::TABLE_NAME,
                        $resourceValue,
                        $idCity,
                        $idResource,
                        $idPersonage,
                        self::COUNT_UPDATE_PER_HOUR
                    );

                    if ($result->isError())
                        throw new DBException('Failed in `payUnitsSalary`');
                }
            }

            $this->commit();
            return true;
        } catch (DBException $e) {
            $this->rollback();
            if ($e->getModel() instanceof Mapper)
                $errors = $e->getModel();
            else
                $errors = $this->getErrors();

            ob_start();
            print_r($errors);
            $err = ob_end_clean();

            e1($e->getMessage(), $err);
            return false;
            //if(DEBUG === true) throw new StatusErrorException($e->getMessage(), $this->oStatus->main_errors);

        }
    }

    /**
     * Расчет ресурсов производимого зданием за еденицу времени
     *
     * @param $amountProduct
     * @param bool $seconds
     * @param $productionTime
     * @param $numberProduction
     * @param bool $requiredNumberMinutes
     * @return float|int
     */
    public function calculateProductionResources($amountProduct, $seconds = false, $productionTime, $numberProduction, $requiredNumberMinutes = false)
    {
        //TODO: Разобраться каким образом брать минуты
        /*
           $currentTime = time();

           if ($seconds == 0) {
               $minutes = 3;
           } else {
               $minutes = ($currentTime - $seconds) / 60;
           }
          */

        if ($requiredNumberMinutes !== false) {
            $minutes = $requiredNumberMinutes;
        } else {
            $minutes = 3;
        }

        $resourceValue = ($amountProduct + $numberProduction) / $productionTime * $minutes;

        if (isset($resourceValue)) {
            return floor($resourceValue);
        } else {
            return 0;
        }
    }
    
    public function getCityResourcesByCoordinates($x, $y) 
    {
        $sql = "
            SELECT pr.`resource_id`, pr.`personage_resource_value` AS `value`
            FROM " . self::TABLE_NAME . " AS pr
            JOIN `personages_cities` AS pc
                ON pc.x_c = %d
                AND pc.y_c = %d
                AND pr.personages_cities_id = pc.id
            WHERE pr.`resource_id` IN (3,4,5,8)
        ";
        $result = $this->query($sql, $x, $y);
        return $result->__DBResult;
    }
    
    public function setCityResourceByCoordinates($x, $y, $resourceId, $value)
    {
        if ($value != 0)
        {
            $sql = "
                UPDATE " . self::TABLE_NAME . " AS pr, `personages_cities` AS pc
                SET pr.`personage_resource_value` = %d
                WHERE pc.`x_c` = %d
                    AND pc.`y_c` = %d
                    AND pr.`personages_cities_id` = pc.`id`
                    AND pr.`resource_id` = %d
            ";
            $result = $this->query($sql, $value, $x, $y, $resourceId);
        }
        else 
        {
            
            $sql = "
                DELETE pr FROM " . self::TABLE_NAME . " AS pr, `personages_cities` AS pc
                WHERE pc.`x_c` = %d
                    AND pc.`y_c` = %d
                    AND pr.`personages_cities_id` = pc.`id`
                    AND pr.`resource_id` = %d
            ";
            $result = $this->query($sql, $x, $y, $resourceId);
        }
        return $this->getAffectedRows($result);
    }
}
