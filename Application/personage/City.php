<?php
/**
 * Файл содержит запросы к базе данных связанные с городом персонажа.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель отображения таблицы в БД состояния города персонажа.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_City extends Mapper
{
    const TABLE_NAME = 'personages_cities';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_City
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
     * Первичный ключ для текущей таблицы.
     * @return string
     */
    public function pk()
    {
        return 'id';
    }

    /**
     * Создание города для персонажа.
     *
     * @param int $idPersonage
     * @param int $city
     * @param int $x координата по X
     * @param int $y координата по Y
     * @return personage_City
     */
    public function saveCity($idPersonage, $city, $x, $y)
    {
        $this->id_personage = $idPersonage;
        $this->city_name = $city;
        $this->x_c = $x;
        $this->y_c = $y;

        return $this->save();
    }

    /**
     * Изменение названия города персонажа.
     * @param string $currentCityName
     * @param string $newCityName
     * @return bool
     * @throws StatusErrorException
     */
    public function updateNameCity($currentCityName, $newCityName)
    {
        $idPersonage = Auth::getInstance()->GetSessionVar(SESSION_PERSONAGE_ID);
        if ($idPersonage == null)
            throw new StatusErrorException('Personages not found', $this->user_not_found);

        $sql = "UPDATE %s SET `city_name`='%s' WHERE `id_personage`=%d AND `city_name` = '%s'";
        $result = $this->query($sql, self::TABLE_NAME, $newCityName, $idPersonage, $currentCityName);

        if ($result->__DBResult["affected_rows"] != 0)
            return true;
        else
            return false;
    }

    /**
     * Получаем (ID) города персонажа по координатам
     *
     * @param $x
     * @param $y
     * @param $idPersonage
     * @return mixed
     */
    public function findIdConcreteCityCoordinates($x, $y, $idPersonage)
    {
        $sql = "SELECT `%s` FROM %s WHERE `x_c` = %d AND `y_c` = %d AND `id_personage`=%d";
        return $this->find($sql, $this->pk(), self::TABLE_NAME, $x, $y, $idPersonage);
    }

    /**
     * Поиск города персонажа по координатам.
     *
     * @param int $x
     * @param int $y
     * @return personage_City|null
     */
    public function findCityByCoordinates($x, $y)
    {
        $sql = "SELECT * FROM %s WHERE `x_c` = %d AND `y_c` = %d";
        return $this->find($sql, self::TABLE_NAME, $x, $y);
    }

	/**
	 * Поиск города персонажа по координатам.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $idPersonage
	 * @return personage_City|null
	 */
	public function findCityByCoordinatesForPersonage($x, $y, $idPersonage)
	{
		$sql = "SELECT * FROM %s WHERE `x_c` = %d AND `y_c` = %d AND `id_personage`=%d";
		return $this->find($sql, self::TABLE_NAME, $x, $y, $idPersonage);
	}


    /**
     * Получаем все города
     *
     * @return array|personage_City
     */
    public function findAllCity()
    {
        $sql = "SELECT *, UNIX_TIMESTAMP(last_collection_taxes) as unix_last_collection_taxes
                FROM %s";
        return $this->findAll($sql, self::TABLE_NAME);
    }

    /**
     * Поиск всех городов для определённого персонажа.
     * @param int $idPersonage
     * @return array|personage_City[]
     */
    public function findCitiesForPersonage($idPersonage)
    {
        $sql = "SELECT * FROM %s WHERE id_personage=%d";
        return $this->findAll($sql, self::TABLE_NAME, $idPersonage);
    }

    /**
     * Обновить поле города
     *
     * @param $sqlPart
     * @param $idCity
     * @return bool
     */
    public function updateFieldCity($sqlPart, $idCity)
    {
        $sql = "UPDATE %s
                SET %s
                WHERE `id` = %d";

        $result = $this->query($sql, self::TABLE_NAME, $sqlPart, $idCity);
        $affected_rows = $this->getAffectedRows($result);

        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Поиск всех городов для определённого персонажа.
	 * @param int $idPersonage
	 * @return array|personage_City[]
	 * @throws DBException
	 */
	public function findCitiesPersonageWithUnits($idPersonage)
	{
		if($idPersonage == null)
			throw new DBException('ID personage not defined in find cities personage with units');

		$sql = "SELECT
					`pc`.city_name, `pc`.x_c as x, `pc`.y_c as y, `pc`.id, `pu`.count, `u`.name_unit
				FROM %1\$s as pc, %6\$s as u, %3\$s as pb
				INNER JOIN %2\$s as pu
					ON `pu`.id_building_personage=`pb`.id_building_personage AND `pu`.status='%5\$s'
				WHERE `pc`.id_personage=%4\$d AND `pb`.personage_id=%4\$d AND `u`.id=`pu`.unit_id AND
				(
					SELECT count(*)
					FROM %3\$s as pb, %2\$s as pu
					WHERE `pu`.id_building_personage=`pb`.id_building_personage
						AND `pu`.status='%5\$s' AND `pb`.personage_id=%4\$d
				) > 0
				";

		return $this->findAll(
			$sql, self::TABLE_NAME, personage_Unit::TABLE_NAME, personage_Building::TABLE_NAME,
			$idPersonage, personage_Unit::STATUS_HIRE_FINISH, unit_Mapper::TABLE_NAME
		);
	}

	/**
	 * Поиск города по координатам с информацией по его владельцу.
	 *
	 * @param int $x
	 * @param int $y
	 * @return personage_City|null
	 */
	public function findCityByCoordinatesWithOwner($x, $y)
	{
		$sql = "SELECT `ps`.sympathy, `p`.nick, `ps`.id_dignity as total_level, `ps`.guild_id, `pc`.population, `pc`.city_name,
						`pc`.happiness,	`p`.id as personage_id, `ps`.finishing_move_personage
				FROM %s as pc, %s as p, %s as ps
				WHERE `pc`.x_c=%d AND `pc`.y_c=%d AND `pc`.id_personage=`p`.id AND `p`.id=`ps`.id_personage";
		return $this->find($sql, self::TABLE_NAME, personage_Mapper::TABLE_NAME, personage_State::TABLE_NAME, $x, $y);
	}


    /**
     * Бизнес логика определения уровня города.
     *
     * @param $idPersonage
     * @return mixed|null
     */
    public function detectLevelCity($idPersonage)
	{
        $allPersonageState = personage_State::model()->findStatePersonageById($idPersonage);
        return $allPersonageState->id_dignity;
	}

	/**
	 * Поиск города с соединением запроса по боевым юнитам.
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $idPersonage
	 * @return array|personage_City[]
	 */
	public function findCombatUnitsForCity($x, $y, $idPersonage)
	{
		$sql = "SELECT `u`.name_unit, `pu`.count, `pc`.city_name, `pc`.id as city_id,
						`uc`.number_transported_cargo as carrying, `u`.combat_type,
						`pb`.id_building_personage as building_id, `u`.id as unit_id
				FROM %s as pc, %s as pu, %s as pb, %s as u, %s as uc
				WHERE `pc`.x_c=%d AND `pc`.y_c=%d AND `pc`.id_personage=%d AND `pb`.city_id=`pc`.id
					AND `pu`.id_building_personage=`pb`.id_building_personage AND `pu`.unit_id=`u`.id
					AND `u`.id=`uc`.unit_id AND `pu`.status='%s'";

		return $this->findAll(
			$sql,
			self::TABLE_NAME, personage_Unit::TABLE_NAME,
			personage_Building::TABLE_NAME, unit_Mapper::TABLE_NAME, unit_Characteristic::TABLE_NAME,
			$x, $y, $idPersonage, personage_Unit::STATUS_HIRE_FINISH
		);
	}

    /**
     * Поиск города по его ИД
     *
     * @param $idCity
     * @return bool
     */
    public function findById($idCity){

		$sql = "SELECT * FROM %s WHERE id=%d";
        return $this->find($sql, self::TABLE_NAME, $idCity);
    }


    /**
     * Обновляем количество свободного население в городе
     *
     * @param $idCity
     * @return bool
     * @throws DBException
     */
    public function updateFreePeopleInCity($idCity){

		$city = $this->findById($idCity);
		if (empty($city))
			throw new DBException('City not found by id');

		// Считаем занятое население на работающих зданиях
        $workingPopulation = personage_Building::model()->getNeedPeopleForBuilding($city->id);

        // Проверяем необходимое количество рабочих для ресурсных зданий
        // Если больше популяции
        while ($workingPopulation > $city->population)
        {
			//В случае дефицита
			//рабочей силы, первыми закрываются здания производящие
			//спец ресурсы., затем основные здания. Первыми закрываются
			//здания из каждой категории, где необходимо большое кол-во
			//рабочих.

			// Закрыть здание согласно условию
			personage_Building::model()->stopProductionBuildingWhereBiggestCountPeople($city->id);

			// Пересчитываем занятое население на работающих зданиях
	        $workingPopulation = personage_Building::model()->getNeedPeopleForBuilding($city->id);
		}

        $formed_sql_part = '`free_people` = '. ($city->population - $workingPopulation);

        return personage_City::model()->updateFieldCity($formed_sql_part, $idCity);
    }
    
    /**
     * Обновляем количество населения в городе
     *
     * @param $idCity
     * @return bool
     * @throws DBException
     */
    public function updatePopulationInCity($idCity){

		$city = personage_Building::model()->findImprovedBuildings($idCity);

		// Показатели по городу
        $cityGrowth = 0; // Прирост
        $cityPopulation = 0; // Популяция
        $cityCapacity = 0; // Емкость

        foreach ($city as $buildingHouse) {
            if ($buildingHouse->current_level > 0) {
                $bonus = unserialize($buildingHouse->current_data_bonus);

                if (empty($bonus)) {
                    $bonus = unserialize($buildingHouse->data_bonus);
                }

                $growthBonus = $bonus['bonus_population_growth']['basic'];
                $capacityBonus = $bonus['bonus_capacity']['basic'];
                
                // Прирост в текущем доме инкрементируем с приростом по городу
                $cityGrowth += $growthBonus / 20; 
                
                // Емкость текущего дома инкрементируем с емкостью города
                $cityCapacity += $capacityBonus; 
            }
        }

        // Учитываем счастье населения на прирост
        $cityGrowth = $cityGrowth * ($city[0]->happiness / 100);

        // Округляем прирост по городу в большую сторону
        $cityGrowth = ceil($cityGrowth);
        
        // Если прирост + текущая популяция > емкости города
        if ($cityGrowth + $city[0]->population > $cityCapacity)
        {
			// Популяция города равна емкости города
			$cityPopulation = $cityCapacity;
			// а прирост равен новое значение популяции - текущая популяция
			$cityGrowth = $cityPopulation - $city[0]->population;
		}
		else
			// иначе популяция = прирост + текущая популяция
			$cityPopulation = $cityGrowth + $city[0]->population;

        $formed_sql_part = '`growth` = '. $cityGrowth . ', `population` = '. $cityPopulation;

        return personage_City::model()->updateFieldCity($formed_sql_part, $idCity);
    }
}
