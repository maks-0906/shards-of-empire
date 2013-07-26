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
class personage_Improve extends Mapper
{
    const TABLE_NAME = 'personages_building_improve';
    const STATUS_PROCESS = 'process';
    const STATUS_FINISH = 'finish';
    const STATUS_NOT_STARTED = 'notstarted';
    const STATUS_CANCEL = 'cancel';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Improve
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
        return 'improve_id';
    }

    /**
     * Получаем улучшения по ID здания персонажа
     * @param $idBuildingPersonage
     * @return personage_Improve
     */
    public function findImproveOnIdBuildingPersonage($idBuildingPersonage)
    {
        $sql = "SELECT * FROM %s WHERE `id_building_personage` = %d";
        return $this->findAll($sql, self::TABLE_NAME, $idBuildingPersonage);
    }

    /**
     * Обновляем статус для внутренних улучшений зданий
     *
     * @param $idBuildingPersonage
     * @param $status
     * @return bool
     */
    public function updateStatusImproveBuildingPersonage($idBuildingPersonage, $status)
    {
        $sql = "UPDATE %s
                SET `status` = '%s'
                WHERE `id_building_personage` = %d";

        $result = $this->query($sql, self::TABLE_NAME, $status, $idBuildingPersonage);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Получить данные об внутреннем улучшении здания
     *
     * @param $idUpgrade
     * @param $idPersonageBuilding
     * @param $idPersonage
     * @return personage_Improve
     */
    public function findImproveForBuildingPersonage($idUpgrade, $idPersonageBuilding, $idPersonage)
    {
        $sql = "SELECT `pi`.*, UNIX_TIMESTAMP(`pi`.finish_time_improve) as unix_finish_time_improve
                FROM %1\$s as pi
                INNER JOIN %2\$s as pb
                  ON (`pi`.id_building_personage = `pb`.id_building_personage)
                WHERE `pb`.id_building_personage = %3\$d
                AND `pi`.id_building_upgrade = %4\$d
                AND `pb`.personage_id =  %5\$d";

        return $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, $idPersonageBuilding,
            $idUpgrade, $idPersonage);
    }

    /**
       * Получить все внутренние улучшения по статусу
       *
       * @param $idPersonage
       * @param $status
       * @return personage_Improve
       */
      public function findImproveForBuildingPersonageOnStatus($idPersonage, $status)
      {
          $sql = "SELECT `pi`.improve_id, UNIX_TIMESTAMP(`pi`.finish_time_improve) as unix_finish_time_improve,
                         `pi`.id_building_personage, `b`.name, `bu`.name_upgrade
                  FROM %1\$s as pi
                  INNER JOIN %2\$s as pb
                    ON (`pi`.id_building_personage = `pb`.id_building_personage)
                  INNER JOIN %3\$s as b
                    ON (`b`.id = `pb`.building_id)
                  INNER JOIN %6\$s as bu
                    ON (`pi`.id_building_upgrade = `bu`.id_building_upgrade)
                  WHERE `pb`.personage_id = %4\$d
                  AND `pi`.status = '%5\$s'";

          return $this->findAll($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, building_Mapper::TABLE_NAME, $idPersonage,
                                $status, building_Upgrade::TABLE_NAME);
      }

    /**
     * Удаляем все улучшения конкретно для здания персонажа
     * @param $buildingImprove
     * @throws DBException
     */
    public function deleteAllImproveSpecificallyBuilding($buildingImprove)
    {
        foreach ($buildingImprove as $improve) {
            $sql = "DELETE FROM %s WHERE `improve_id` = %d";
            $deleteImprove = $this->query($sql, self::TABLE_NAME, $improve->improve_id);

            if ($deleteImprove->isError()) throw new DBException(implode(' : ', $deleteImprove->getErrors()));
        }
    }

    /**
     * Удалить внутреннее улучшение здания персонажа
     *
     * @param $idImprove
     * @return bool
     */
    public function deleteImproveSpecificallyBuilding($idImprove)
    {
        $sql = "DELETE FROM %s WHERE `improve_id` = %d";
        $result = $this->query($sql, self::TABLE_NAME, $idImprove);

        $affected_rows = $this->getAffectedRows($result);

        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавляем усовершенствование здания с временной меткой окнчания процесса усовершенствавания
     *
     * @param $idBuildingPersonage
     * @param $idUpgrade
     * @param $minute
     * @return personage_Improve
     */
    public function insertFinishTimeImprove($idBuildingPersonage, $idUpgrade, $minute)
    {
        $sql = "INSERT INTO %1\$s
                SET `improve_id` = NULL,
                    `id_building_personage` = %2\$d,
                    `id_building_upgrade` = %3\$d,
                    `finish_time_improve` = TIMESTAMP(DATE_ADD(NOW(),INTERVAL %4\$d MINUTE)),
                    `status` = '%5\$s'";

        $result = $this->query($sql, self::TABLE_NAME, $idBuildingPersonage, $idUpgrade, $minute, self::STATUS_PROCESS);

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
     * @return array|personage_Improve
     */
    public function findFinishImproveBuildings($idPersonageBuilding = false)
    {
        $sql = "SELECT  `pbi`.id_building_upgrade, `pbi`.id_building_personage,
                        `pbbs`.current_data_bonus, `bu`.*, `pbl`.data_bonus, `pb`.current_level
                FROM  %1\$s as pbi
                INNER JOIN %2\$s as pbbs
                    ON (`pbi`.id_building_personage = `pbbs`.id_building_personage)
                INNER JOIN %3\$s as bu
                    ON (`bu`.id_building_upgrade = `pbi`.id_building_upgrade)
                INNER JOIN %7\$s as pb
                    ON (`pb`.id_building_personage = `pbbs`.id_building_personage)
                INNER JOIN %8\$s as pbl
                    ON (`pb`.building_id = `pbl`.building_id)
                WHERE";

        if ($idPersonageBuilding === false) {
            $sql .= " `pbi`.`finish_time_improve` <= '%4\$s'
                       AND `pbi`.`status` = '%5\$s'";
        } else {
            $sql .= " `pbi`.`finish_time_improve` <= '%4\$s'
                       AND `pbi`.`status` = '%5\$s'
                       AND `pbi`.id_building_personage = %6\$d";
        }

        return $this->findAll($sql, self::TABLE_NAME, personage_BuildingBonus::TABLE_NAME,
            building_Upgrade::TABLE_NAME, date("Y-m-d H:i:s"),
            personage_Improve::STATUS_PROCESS, $idPersonageBuilding, personage_Building::TABLE_NAME,
            building_BasicLevel::TABLE_NAME);
    }


    /**
     * Проводим расчет бонусов c внутренними усовершенствованиями зданий
     *
     * Внимание! При рефакторинге метода не потерять поля бонусов у здания.
     * После подсчета поля снова проходят сериализацию. В это время должны быть сохранены все поля которые были
     * до разсериализации. Наличие полей можно проверить в таблице базовых бонусов здания (building_basic_levels)
     * базы данных.
     *
     * Начисление бонусов происходит сначала с проверки совпадения полей находящихся в сериализованных данных для
     * каждого здания хранящихся в таблице (personages_building_bonus_state) и полей в таблице (building_upgrade).
     * После в зависимости от еденицы измерения происходит соответствующее вычисление.
     * После снова происходит сериализация новых данных и обновление этих данных для текущего здания.
     *
     * @param $improveBuilding
     * @return mixed
     * @throws ErrorException
     */
    public function calculationBuildingImprovements($improveBuilding)
    {
        $allCurrentBonus = unserialize($improveBuilding->current_data_bonus);
        $allBasicBonus = unserialize($improveBuilding->data_bonus);

        foreach ($allBasicBonus as $nameBonus => $basicBonus) {

            if (isset($allCurrentBonus[$nameBonus])) {

                //Еденица измерения в базовых бонусах
                $measure = $basicBonus['measure'];

                //Значение за внутреннее улучшение здания
                $bonusImprove = (int)$improveBuilding->$nameBonus;

                //Текущий уровень здания персонажа
                $buildingLevel = $improveBuilding->current_level;

                //Значение текущего бонуса
                $currentBonus = $allCurrentBonus[$nameBonus]['basic'];

                $recalculatedBonuses[$nameBonus]['basic'] = '';

                // Значение улучшения бонуса из базовых величин может быть отрицательным числом
                // Тогда плюс на минус получаем минус и бонус вычитается.
                switch ($measure) {

                    //Подсчет в процентах
                    case 'pt':
                        $recalculatedBonuses[$nameBonus]['basic'] = floor($currentBonus + ($currentBonus / 100 * $bonusImprove));
                        break;

                    //Подсчет сложением или вычитанием
                    case 'u':
                        $recalculatedBonuses[$nameBonus]['basic'] = $currentBonus + $bonusImprove;
                        break;

                    //Подсчет по формуле. Название функции находится в сериализованных данных базовых бонусов
                    case 'f':
                        $funcName = $basicBonus['improve'];

                        if (!is_callable(array(models_CalculationFormulasBuilding::model(), $funcName))) {
                            throw new ErrorException("Method $funcName does not exist");
                        } else {
                            $recalculatedBonuses[$nameBonus]['basic'] = call_user_func(array(models_CalculationFormulasBuilding::model(), $funcName),
                                $currentBonus,
                                $buildingLevel,
                                $bonusImprove);
                        }
                        break;
                }
            }
        }

        return $recalculatedBonuses;
    }


    /**
     * Произвести перерасчет бонусов для зданий с внутренними улучшениями
     *
     * @param $improveBuilding
     * @return string
     * @throws ErrorException
     */
    public function recalculateBonusesWithInternalImprovements($improveBuilding)
    {
        //Провести перерасчет бонусов
        $recalculatedBonuses = $this->calculationBuildingImprovements($improveBuilding);

        if (!empty($recalculatedBonuses) and is_array($recalculatedBonuses)) {
            return serialize($recalculatedBonuses);
        } else {
            throw new ErrorException('Error in the calculation of bonuses to internal improvements');
        }
    }


    /**
     * Изменяем статус улучшений на улучшен, с обновлением бонусов
     *
     * @param $idPersonageBuilding
     * @param $bonus
     * @return bool
     * @throws DBException
     */
    public function finishImproveBuildingsAndAddingBonuses($idPersonageBuilding, $bonus)
    {

        try {
            $resultBegin = $this->begin();

            //Оканчивам внутреннее исследование здания
            $sql = "UPDATE %s
                    SET `status` = '%s',
                        `finish_time_improve` = NULL
                    WHERE `id_building_personage` = %d";

            $upgradeStatus = $this->query($sql, self::TABLE_NAME, self::STATUS_FINISH, $idPersonageBuilding);

            if ($upgradeStatus->isError())
                throw new DBException(implode(' : ', $upgradeStatus->getErrors()));

            //Обнавляем бонусы здания за внутреннее исследование
            $sql = "UPDATE %s
                    SET `current_data_bonus` = '%s'
                    WHERE `id_building_personage` = %d";

            $upgradeBonus = $this->query($sql, personage_BuildingBonus::TABLE_NAME, $bonus, $idPersonageBuilding);

            if ($upgradeBonus->isError())
                throw new DBException(implode(' : ', $upgradeBonus > getErrors()));

            $resultCommit = $this->commit();

        } catch (DBException $e) {
            $this->rollback();
            throw new DBException(
                'finishImproveBuildingsAndAddingBonuses : ' . $e->getMessage()
            );
        }

        return $resultCommit;
    }

    /**
       * Получить улучшения по названию здания
       *
       * @param $nameBuilding
       * @param $x
       * @param $y
       * @param $idPersonage
       * @param $status
       * @return personage_Building
       */
      public function findImproveBuildingPersonageOfNameBuilding($nameBuilding, $x, $y, $idPersonage, $status)
      {
          $sql = "SELECT `pbi`.*
                  FROM %1\$s as pbi
                  INNER JOIN %2\$s as pb
                     ON (`pbi`.id_building_personage = `pb`.id_building_personage)
                  INNER JOIN %3\$s as b
                     ON (`b`.id = `pb`.building_id)
                  WHERE `b`.name = '%4\$s'
                  AND `pb`.city_id = (SELECT id FROM %5\$s WHERE x_c=%6\$d AND y_c=%7\$d AND id_personage=%8\$d)
                  AND `pb`.personage_id = %8\$d
                  AND `pbi`.status = '%9\$s'";

          return $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME,
                                  building_Mapper::TABLE_NAME, $nameBuilding, personage_City::TABLE_NAME,
                                  $x, $y, $idPersonage, $status);
      }
}

