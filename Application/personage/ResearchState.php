<?php
/**
 * Файл содержит запросы к базе данных по текущему статусу исследований персонажем
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
class personage_ResearchState extends Mapper
{
    const TABLE_NAME = 'personages_research_state';

    /**
     * Статус исследования - исследуется
     */
    const STATUS_RESEARCH = 'research';

    /**
     * Статус исследования - исследовано
     */
    const STATUS_INVESTIGATED = 'investigated';

    /**
     * Статус исследования по умолчанию.
     * Может применяться как только начинающийся так и отменённым действием (cancel).
     */
    const STATUS_DEFAULT = 'default';

    /**
     *Значение для сброса отсчета времени
     */
    const VALUE_RESET = 0;


    /**
     * Нет значения
     */
    const NO_VALUE = 0;

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
     * @return personage_ResearchState
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
        return 'id_personages_research_state';
    }

    /**
     * Проводим запрос на наличие в базе проверяемых данных
     *
     * @param $level
     * @param $idResearch
     * @return bool
     */
    public function isExistsResearchPersonage($level, $idResearch)
    {
        $sql = "SELECT `id`
                FROM %s
                WHERE `current_research_id` = %d
                AND `current_level_id` = %d
                AND `personages_id` = %d";

        $done = $this->find($sql, $this->tableName(), (int)$idResearch, (int)$level, $this->idPersonage);

        if ($done->id != NULL) {
            return true;
        }

        return false;
    }

    /**
     * Заполняем таблицу исследований персонажа первоначальными данными со значением (0) ноль
     *
     * @param $idPersonage
     * @return bool
     */
    public function insertPrimaryDataResearch($idPersonage)
    {
        $idAllResearch = research_Mapper::model()->findIdAllResearch();

        if ($idAllResearch === false) {
            return false;
        }

        $formed_part = '';
        foreach ($idAllResearch as $idResearch) {
            $formed_part .= '(' . 'NULL' . ',' . $idPersonage . ',' . $idResearch['id'] . '),';
        }

        //Удаляем последнюю запятую в части запроса
        $part_sql = substr($formed_part, 0, -1);

        $sql = "INSERT INTO %s (id_personages_research_state,
								  id_personage,
								  current_research_id)
				   VALUE $part_sql";

        $result = $this->query($sql, $this->tableName());
        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows != 0)
            return true;
        else
            return false;
    }

    /**
     * Обновляем при быстром изучении исследовани
     *
     * @param $idResearch
     * @return personage_ResearchState
     */
    public function updateCurrentLevelPersonage($idResearch)
    {
        if ($this->doneLocationCity === true) {

            $sql = "UPDATE %s SET `current_level`=
                         IF (`current_level`+1 < (SELECT `level_for_upgrade`-1 FROM %s ORDER BY `level_for_upgrade` DESC LIMIT 1),
                                 `current_level`+1,
                                     (SELECT `level_for_upgrade` FROM %s ORDER BY `level_for_upgrade` DESC LIMIT 1)),
                             `research_status` = '%s', `research_finish_time` = %d
               WHERE `current_research_id`=%d AND `id_personage` = %d";

            $result = $this->query($sql, $this->tableName(),
                research_ResearchUpgrade::TABLE_NAME,
                research_ResearchUpgrade::TABLE_NAME, self::STATUS_INVESTIGATED, models_Time::model()->getCurrentTimestamp(),
                $idResearch, $this->idPersonage
            );

            $affected_rows = $this->getAffectedRows($result);

            if (isset($affected_rows)) {
                return true;
            } else {
                return false;
            }
        } else {
            return array();
        }
    }

    /**
     * Обновляем данные при медленном изучении исследования
     *
     * @param $idResearch
     * @param $timestamp
     * @return personage_ResearchState
     */
    public function updateSlowResearch($idResearch, $timestamp)
    {
        if ($this->doneLocationCity === true) {
            $sql = "UPDATE %s as prs SET `current_level`=
                       IF (`current_level` < (SELECT `level_for_upgrade`-1 FROM %s ORDER BY `level_for_upgrade` DESC LIMIT 1),
                               `current_level`,
                                   (SELECT `level_for_upgrade` FROM %s ORDER BY `level_for_upgrade` DESC LIMIT 1)),
                           `research_status` = '%s',
                           `research_finish_time` = %d
                  WHERE `prs`.`current_research_id`=%d AND `prs`.`id_personage`=%d";

            $result = $this->query($sql, $this->tableName(), research_ResearchUpgrade::TABLE_NAME,
                research_ResearchUpgrade::TABLE_NAME,
                self::STATUS_RESEARCH, $timestamp,
                (int)$idResearch, (int)$this->idPersonage
            );

            $affected_rows = $this->getAffectedRows($result);

            if (isset($affected_rows)) {
                return true;
            } else {
                return false;
            }
        } else {
            return array();
        }
    }

    /**
     * Получить временную метку окончания исследования
     *
     * @param $minute
     * @return int
     */
    public function getTimestampEndStudy($minute)
    {
        return mktime(date("H"), date("i") + $minute, date("s"), date("m"), date("d"), date("Y"));
    }

    /**
     * Получить оставшуюся временную метку в секундах
     *
     * @param $original_timestamp
     * @param $current_timestamp
     * @return bool
     */
    public function getRestTimestamp($original_timestamp, $current_timestamp)
    {
        $difference = $original_timestamp - $current_timestamp;

        if ((int)$difference < 0) {
            return false;
        } else {
            return $difference;
        }
    }

    /**
     * Текущая временная метка
     *
     * @return int
     */
    public function getCurrentTimestamp()
    {
        return mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
    }

    /**
     * Поиск исследований с просроченным временем изучения с
     * возможностью уникальной проверки по идентификатору исследования для персонажа.
     *
     * @param bool|int $idResearch
     * @return array|personage_ResearchState[]
     */
    public function findResearchWithFinishTime($idResearch = false)
    {
        $sql = "SELECT *
				FROM %s
				WHERE `research_finish_time` < %d AND `research_status`='%s'";

        if ($idResearch !== false) {
            $sql .= " AND `id_personages_research_state`=%d";
            return $this->findAll($sql, self::TABLE_NAME, time(), personage_ResearchState::STATUS_RESEARCH, $idResearch);
        } else
            return $this->findAll($sql, self::TABLE_NAME, time(), personage_ResearchState::STATUS_RESEARCH);
    }

    /**
     * Поиск текущего исследования для персонажа.
     * Если персонаж не указан в параметре, будет взят текущий персонаж.
     *
     * @param int $idResearch
     * @param bool|int $idPersonage
     * @return personage_ResearchState|null
     */
    public function findResearchByIDResearchForPersonage($idResearch, $idPersonage = false)
    {
        if ($idPersonage == false) $idPersonage = Auth::getIdPersonage();

        $sql = "SELECT * FROM %s WHERE `current_research_id`=%d AND `id_personage`=%d";

        return $this->find($sql, self::TABLE_NAME, $idResearch, $idPersonage);
    }

    /**
     * Завершение изучения исследования на CRON
     *
     * @param int $idPersonagesResearchState
     * @param int $current_level
     * @param int $max_level_for_upgrade
     * @return bool
     */
    public function finishResearchById($idPersonagesResearchState, $current_level, $max_level_for_upgrade)
    {
        if ($max_level_for_upgrade <= $current_level) {
            $validLevel =  $max_level_for_upgrade;
        }else{
            $validLevel = $current_level + 1;
        }

        $sql = "UPDATE %s SET `research_status` = '%s', `current_level` = %d WHERE `id_personages_research_state`=%d";
        $result = $this->query($sql, self::TABLE_NAME, personage_ResearchState::STATUS_INVESTIGATED, $validLevel, $idPersonagesResearchState);

        $affected_rows = $this->getAffectedRows($result);
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Отмена изучения исследования для теущего персонажа.
     *
     * @param $idResearch
     * @return bool
     * @throws ErrorException
     */
    public function cancelResearchById($idResearch)
    {
        if (!$this->idPersonage)
            throw new ErrorException('ID personage for cancel study research not defined!');

        $sql = "UPDATE %s as prs
				SET `research_status`='%s', `research_finish_time`=%d
			  	WHERE `prs`.`current_research_id`=%d AND `prs`.`id_personage`=%d";

        $result = $this->query(
            $sql, self::TABLE_NAME,
            self::STATUS_DEFAULT, self::VALUE_RESET,
            (int)$idResearch, (int)$this->idPersonage
        );

        $affected_rows = $this->getAffectedRows($result);
        if (isset($affected_rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить исследования персонажа по статусу
     *
     * @param $idPersonage
     * @param $status
     * @return personage_ResearchState
     */
    public function findResearchOnStatus($idPersonage, $status)
    {
        $sql = "SELECT `prs`.current_level, `prs`.research_finish_time, `prs`.id_personages_research_state,
                       `r`.name_research
                FROM %1\$s as prs
                INNER JOIN %2\$s as r
                 ON (`prs`.current_research_id = `r`.id)
                WHERE `prs`.id_personage = %3\$d
                AND `prs`.research_status = '%4\$s'";

        return $this->findAll($sql, self::TABLE_NAME, research_Mapper::TABLE_NAME, $idPersonage, $status);
    }


    /**
     * Определить исследовано ли внутреннее улучшение по уровню исследования
     *
     * @param $idImprove
     * @param $idPersonage
     * @return personage_ResearchState
     */
    public function investigatedToDetermineHomeImprovement($idImprove, $idPersonage)
    {
        $sql = "SELECT `prs`.id_personages_research_state, `ru`.level_for_upgrade
                FROM %1\$s as prs, %2\$s as ru
                WHERE  `ru`.research_id = `prs`.current_research_id
                AND `ru`.id_building_upgrade = %3\$d
                AND `prs`.id_personage = %4\$d
                AND `prs`.current_level >= `ru`.level_for_upgrade";

        return $this->find($sql, self::TABLE_NAME, research_ResearchUpgrade::TABLE_NAME, $idImprove, $idPersonage);
    }

    /**
     * Получить данныя об исследовании персонажа по идентификатору внутреннего исследования здания
     *
     * @param $idUpgrade
     * @param $idPersonage
     * @return personage_ResearchState
     */
    public function findPersonageResearchAndUpgradeOnIdUpgrade($idUpgrade, $idPersonage)
    {
        $sql = "SELECT `prs`.current_level, `ru`.level_for_upgrade, `r`.name_research
                    FROM %1\$s as prs, %2\$s as ru, %3\$s as r
                    WHERE  `ru`.research_id = `prs`.current_research_id
                    AND `r`.id = `prs`.current_research_id
                    AND `ru`.id_building_upgrade = %4\$d
                    AND `prs`.id_personage = %5\$d";

        return $this->find($sql, self::TABLE_NAME, research_ResearchUpgrade::TABLE_NAME, research_Mapper::TABLE_NAME,
                                $idUpgrade, $idPersonage);
    }
}
