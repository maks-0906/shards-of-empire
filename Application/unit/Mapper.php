<?php
/**
 * Файл содержит класс модель, управляющая юнитами.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */

/**
 * Класс модель, управляющая юнитами.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package unit
 */
class unit_Mapper extends Mapper
{

    const TABLE_NAME = 'units';

    const NO_UNIT = 0;
    const NO_VALUE = 0;

    const KEY_UNIT_PEASANT = 'peasant'; //Крестьянин
    const KEY_UNIT_MILITIAMAN = 'militiaman'; //Ополченец
    const KEY_UNIT_ARCHER = 'archer'; //Лучник
    const KEY_UNIT_AUXILIARY = 'auxiliary'; //Вспомогатель
    const KEY_UNIT_INFANTRYMAN = 'infantryman'; //Пехотинец
    const KEY_UNIT_ARBALESTER = 'arbalester'; //Арбалетчик
    const KEY_UNIT_TOPOROMETATEL = 'toporometatel'; //Топорометатель
    const KEY_UNIT_TRAINED_SPEARMAN = 'trained_spearman'; //Обученный копейщик
    const KEY_UNIT_BERSEK = 'bersek'; //Берсек
    const KEY_UNIT_CHOSEN_SWORDSMAN = 'chosen_swordsman'; //Избранный мечник
    const KEY_UNIT_SCORPION = 'scorpion'; //Скорпион
    const KEY_UNIT_SARMATIAN_CAVALRY = 'sarmatian_cavalry'; //Сарматская кавалерия
    const KEY_UNIT_STEPPE = 'steppe'; //Степняки
    const KEY_UNIT_VARANGIAN = 'varangian'; //Варяг
    const KEY_UNIT_EASY_RIDER = 'easy_rider'; //Лёгкий рейдер
    const KEY_UNIT_ONAGER = 'onager'; //Онагры
    const KEY_UNIT_AMAZON = 'amazon'; //Амазонка
    const KEY_UNIT_CATAPHRACTS = 'cataphracts'; //Катафракты
    const KEY_UNIT_WARDEN_CASTLE = 'warden_castle'; //Страж замка
    const KEY_UNIT_WAR_ELEPHANTS = 'war_elephants'; //Боевые слоны
    const KEY_UNIT_HORSE_QUARDSMAN = 'horse_guardsman'; //Конный гвардеец
    const KEY_UNIT_TREBUCHET = 'trebuchet'; //Требушет
    const KEY_UNIT_SLAVES_PORTERS = 'slaves_porters'; //Рабы носильщики
    const KEY_UNIT_LIGHTWEIGHT_CART = 'lightweight_cart'; //Лёгкая телега
    const KEY_UNIT_MARCHING_TRAIN = 'marching_train'; //Походный обоз
    const KEY_UNIT_BALLISTA = 'ballista'; //Баллиста

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_Mapper
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
     * Найти всех боевых юнитов
     * @return array|unit_Mapper
     */
    public function findAllBattleUnit()
    {
        $sql = "SELECT * FROM %s";
        return $this->findAll($sql, self::TABLE_NAME);
    }

    /**
     * Поиск юнитов, которые находятся в очереди, по ИД здания персонажа
     *
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return unit_Mapper
     */
    public function findAllUnitsInQueryByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "
			SELECT 
				PU.`id_unit_personage`,
				`UK`.`name_unit` as `name_unit`,
				`PU`.`count` as `count_unit`,
				`UC`.`production_speed` * `PU`.`count` as `production_time`,
				TIME_TO_SEC(TIMEDIFF(`PU`.`finish_time_rent`, NOW())) as `finish_time`,
				`PU`.`status` as `status`
			FROM `%1\$s` as PU
			LEFT JOIN `%4\$s` as UK ON `UK`.`id` = `PU`.`unit_id`
			LEFT JOIN `%6\$s` as UC ON `UC`.`unit_id` = `PU`.`unit_id`
			WHERE `PU`.`id_building_personage` = '%2\$d' 
			AND (`PU`.`status` = '%3\$s' OR `PU`.`status` = '%5\$s')";

        return $this->findAll(
            $sql,
            personage_Unit::TABLE_NAME,
            $idPersonageBuilding,
            personage_Unit::STATUS_HIRE_PROCESSING,
            self::TABLE_NAME,
            personage_Unit::STATUS_HIRE_NOT_STARTED,
            unit_Characteristic::TABLE_NAME
        );
    }

    /**
     * Запрос на поиск юнитов-воинов
     *
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return unit_Mapper
     */
    public function findAllUnitsByIdPersonageBuilding($idPersonageBuilding)
    {
        $sql = "
			SELECT 
				`C`.`unit_id` as `id_unit`,
				`U`.`name_unit` as `name_unit`, 
				(
                                    SELECT ifnull(SUM(pul.`count`), '0')
                                    FROM `%8\$s` AS pul
                                    JOIN `%4\$s` AS pb
                                        ON pb.`id_building_personage` = '%5\$d'
                                    JOIN `%6\$s` AS pc
                                        ON pb.`city_id` = pc.`id`
                                        AND pc.`x_c` = pul.`x_l`
                                        AND pc.`y_c` = pul.`y_l`
                                        AND pc.`id_personage` = pul.`personage_id`
                                    WHERE pul.`unit_id` = `C`.`unit_id`
                                ) 
                                as `hired`,
				FLOOR(`PC`.`free_people` / `C`.`place_barracks`) as `available`,
				'battle' as `unit_type`,
				IF (`C`.`building_level_id` <= 
			    (SELECT `current_level` 
				FROM  `%4\$s` 
				WHERE `id_building_personage` = '%5\$d'), '1', '0') as 'is_hired'
		    FROM `%1\$s` as C
		    LEFT JOIN `%2\$s` as U ON `C`.`unit_id` = `U`.`id`
		    LEFT JOIN `%4\$s` as PB ON `PB`.`building_id` = `C`.`building_id`
		    LEFT JOIN `%6\$s` as PC ON `PC`.`id` = `PB`.`city_id`
		    WHERE
				`PB`.`id_building_personage` = '%5\$d'";

        return $this->findAll($sql,
            unit_Characteristic::TABLE_NAME,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idPersonageBuilding,
            personage_City::TABLE_NAME,
            personage_Unit::STATUS_HIRE_FINISH,
            personage_UnitLocation::TABLE_NAME
        );
    }

    /**
     * Проверка доступности юнитов-воинов для найма
     *
     * @param $unitId - ИД юнита
     * @param $unitCount - количество юнитов
     * @param $idPersonageBuilding - ИД здания персонажа
     * @return boolean
     */
    public function checkAvailabilityUnitsForHiring($unitId, $unitCount, $idPersonageBuilding)
    {
        $sql = "
			SELECT 
				`C`.`unit_id` as `id_unit`,
				
				FLOOR(`PC`.`free_people` / `C`.`place_barracks`) as `available`,

				IF (`C`.`building_level_id` <= 
			    (SELECT `current_level` 
				FROM  `%4\$s` 
				WHERE `id_building_personage` = '%5\$d'), '1', '0') as 'is_hired'
		    FROM `%1\$s` as C
		    LEFT JOIN `%2\$s` as U ON `C`.`unit_id` = `U`.`id`
		    LEFT JOIN `%4\$s` as PB ON `PB`.`building_id` = `C`.`building_id`
		    LEFT JOIN `%7\$s` as PC ON `PC`.`id` = `PB`.`city_id`
		    WHERE
				`PB`.`id_building_personage` = '%5\$d'
				AND `C`.`unit_id` = '%6\$d'";

        $result = $this->find($sql,
            unit_Characteristic::TABLE_NAME,
            self::TABLE_NAME,
            personage_Unit::TABLE_NAME,
            personage_Building::TABLE_NAME,
            $idPersonageBuilding,
            $unitId,
            personage_City::TABLE_NAME,
            personage_Unit::STATUS_HIRE_FINISH,
            personage_Unit::STATUS_HIRE_PROCESSING
        );

        return !$this->isEmptyResult() // Если результат не пустой
            & $result->is_hired // и юнит доступен для найма
            & ($unitCount <= $result->available); // и количество для найма <= доступных для найма
    }

    /**
     * Найм юнита-воина, по ИД здания персонажа, ИД юнита
     *
     * @param $idPersonageBuilding
     * @param $unitId
     * @param $unitCount
     * @return bool
     * @throws DBException
     */
    public function hiringUnitsByIdPersonageBuildingAndUnitId($idPersonageBuilding, $unitId, $unitCount)
    {
        $sql = "
			INSERT INTO 
				`%1\$s` 
			SET 
				`id_building_personage` = %2\$d,
				`unit_id` = %3\$d,
				`count` = %4\$d,
				`time_rent` = (SELECT `production_speed` FROM `%6\$s` WHERE `unit_id` = %3\$d),
				`status` = '%5\$s'";

        $personageUnit = $this->query(
            $sql,
            personage_Unit::TABLE_NAME,
            $idPersonageBuilding,
            $unitId,
            $unitCount,
            personage_Unit::STATUS_HIRE_NOT_STARTED,
            unit_Characteristic::TABLE_NAME
        );

        if ($personageUnit->isError())
            throw new DBException('Same error in query in function `startNextUnitsRent`');

        // Стимулируем постановку в очередь только что нанятой партии юнитов
        personage_Unit::model()->startNextUnitsRent();

        return true;
    }
    
    /**
     * Списываем свободное население за найм юнита-воина
     * 
     * @param $idUnit - ИД юнита
     * @param $unitCount - Количество юнитов
     * @param $idPersonage - ИД персонажа
     * @param $idPersonageCity - ИД города персонажа
     * @return boolean
     */
    public function writeOffFreePeopleForHiring($unitId, $unitCount, $idPersonage, $idPersonageCity)
    {
		$sql = "
			UPDATE 
				`%1\$s` PC
			INNER JOIN 
				`%2\$s` PB ON `PC`.`id` = `PB`.`city_id` 
			INNER JOIN 
				`%3\$s` UC ON UC.`building_id` = `PB`.`building_id` 
			SET 
				PC.`free_people` = PC.`free_people` - UC.`place_barracks` * '%4\$d',
				PC.`population` = PC.`population` - UC.`place_barracks` * '%4\$d'
			WHERE 
				PC.`id_personage` = '%5\$d' AND
				PC.`id` = '%6\$d' AND
				UC.`unit_id` = '%7\$d'";
		
		$result = $this->query(
			$sql,
			personage_City::TABLE_NAME,
			personage_Building::TABLE_NAME,
			unit_Characteristic::TABLE_NAME,
			$unitCount,
			$idPersonage,
			$idPersonageCity,
			$unitId
		);
		
		if ($result->isError()) {
            return false;
        } else {
            return true;
        }
	}
}
