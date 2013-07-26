<?php
/**
 * Файл содержит класс модель, управляющий боями.
 *
 * @author vetalrakitin  <vetalrakitin@gfight.com>
 * @package fight
 */

/**
 * Класс модель, управляющая боями юнитов.
 *
 * @author vetalrakitin  <vetalrakitin@gfight.com>
 * @package fight
 */
class fight_Mapper extends Mapper
{

    const TABLE_NAME = 'personages_units_fights';

    // Константы статуса боя
    const FIGHT_STATUS_NOTSTARTED = 'notstarted'; // Не начался
    const FIGHT_STATUS_WAITING_ALLIED = 'waitingallied'; // Ожидание союзников
    const FIGHT_STATUS_STARTED = 'started'; // Проходит
    const FIGHT_STATUS_FINISHED = 'finished'; // Завершен

    // Статус сражения - ничья
    const FIGHT_RESULT_DEAD_HEAT = 'dead_heat';

    // Максимальное количество раундов
    const FIGHT_MAX_NUMBER_ROUNDS = 5;

    // Разница остатка единиц жизни у игроков в %
    const FIGHT_LIFE_DIFFERENCE = 5;

    // Время ожидания войск союзников в секундах
    const TIME_WAITING_ALLIED = 10;

    // Цель боя
    const TARGET_FIGHT_ATTACK = 'attack'; // Атака
    const TARGET_FIGHT_ATTACK_TACKING = 'attack_tacking'; // Атака с защитой
    const TARGET_FIGHT_PROTECTION = 'protection'; // Защита

    const NO_VALUE = 0;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return fight_Mapper
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
     * Найти бой по его ИД
     *
     * @param int $idFight - ИД боя
     * @return fight_Mapper
     */
    public function findFightById($idFight)
    {
        $sql = "SELECT * FROM %1\$s WHERE `id_personages_units_fight` = %2\$d";
        return $this->find($sql, self::TABLE_NAME, $idFight);
    }

    /**
     * Найти все бои по статусу
     *
     * @param string $statusFight - статус боя
     * @return fight_Mapper
     */
    public function findAllFightsByStatus($statusFight)
    {
        $sql = "SELECT * FROM %1\$s WHERE `status` = '%2\$s'";
        return $this->findAll($sql, self::TABLE_NAME, $statusFight);
    }


    /**
     * Создание нового боя и установка статуса - ожидание союзников
     *
     * @param int $idLocation - ИД локации, где происходит бой
     * @param string $targetFight - цель боя
     * @param string $accessoryLocation - принадлежность локации на текущий момент (personage - персонажу, robbers - разбойникам)
     * @param int $idPersonageDefender - ID владельца локации
     * @return int id inserted row
     * @return mixed|null
     * @throws DBException
     */
    public function createFightAndWaitingAllied($idLocation, $targetFight, $accessoryLocation, $idPersonageDefender)
    {
//        $timestamp = models_Time::model()->getTimestampProlongedBySeconds(self::TIME_WAITING_ALLIED);
//        // $this->id_initiator = $idPersonageInitiator;
//        $this->id_defender = $idPersonageDefender;
//        $this->id_location = $idLocation;
//        $this->accessory = $accessoryLocation;
//
//        // Устанавливаем статус боя - ожидание союзников и время ожидания - 10 сек
//        $this->finish_time = models_Time::model()->convertTimestampToDateAndTime($timestamp);
//        $this->status = self::FIGHT_STATUS_WAITING_ALLIED;
//        $this->target_fight = $targetFight;
//        $result = $this->save();
//
//        if ($this->isError())
//            throw new DBException('Failed insert row in ' . self::TABLE_NAME);
//
//        return $result->id;
    }


    /**Установить статус боя
     *
     * @param int $idFight - ИД боя
     * @param string $status - статус боя
     * @param bool $activeTime - время активности статуса в секундах
     * @return bool
     * @throws DBException
     */
    public function setFightStatus($idFight, $status, $activeTime = false)
    {
//        $sql = "UPDATE %1\$s
//			    SET";
//        if ($activeTime === false) {
//            $sql .= " `status` = '%2\$s'";
//        } else {
//            $sql .= " `status` = '%2\$s', `finish_time` = '%3\$s'";
//        }
//        $sql .= " WHERE `id_personages_units_fight` = %4\$d";
//
//        $result = $this->query($sql, self::TABLE_NAME, $status, $activeTime, $idFight);
//
//        if ($this->isError())
//            throw new DBException('Failed update row in ' . self::TABLE_NAME);
//
//        return true;
    }


    /**
     * Проводим бой в зависимости от различных сложившихся условий
     *
     * Внимание если в массиве передаваемого для подсчета боя (idPersonage) ноль значит оно принадлежит разбойникам.
     * За каждого убитого юнита противника игрок получает по стольку очков славы сколько занимает места в казарме данный юнит.
     *
     * @param $idFight
     * @throws StatusErrorException
     */
    public function leadFight($idFight)
    {
//        $defenderFightRobber = array();
//
//        if (empty($idFight)) {
//            throw new DBException('Not parameter ID fight', $this->oStatus->main_errors);
//        }
//
//        // Получить информацию о бое
//        $fightInfo = $this->findFightById($idFight);
//
//        if ($fightInfo->status != fight_Mapper::FIGHT_STATUS_WAITING_ALLIED) {
//            throw new DBException('The battle has already started', $this->oStatus->main_errors);
//        }
//
//        //Получаем данные отряда первого прибывшего в локацию
//        $initiatorFight = unit_UnitsMoving::model()->findSquadOnStatusArrivedAndIdPersonageFight(
//            unit_UnitsMoving::STATUS_ARRIVED_FIRST, $idFight);
//
//        if ($initiatorFight == NULL) {
//            throw new DBException('Not data initiation fight', $this->oStatus->main_errors);
//        }
//
//        //Проверяем если в локации разбойники проводим подсчет данных для разбойников
//        if ($fightInfo->accessory == unit_UnitsMoving::ACCESSORY_LOCATIONS_ROBBERS) {
//            $defenderFightRobber = map_FeatureRobber::model()->findLevelRobberLocations($fightInfo->id_location);
//            $arDefender = fight_Calculation::model()->calculationIndicatorsRobber($defenderFightRobber);
//        }
//
//        //Проверяем если в локации персонаж то возможно нападение как других персонажей так и разбойников
//        if ($fightInfo->accessory == unit_UnitsMoving::ACCESSORY_LOCATIONS_PERSONAGES) {
//
//            // Получаем список юнитов отряда и союзников в локации
//            $units = personage_UnitLocation::model()->findAllBattleUnitsInLocation($fightInfo->id_defender, $fightInfo->id_location);
//
//            if (empty($units)) {
//                throw new DBException('No data on the units for the fight', $this->status->main_errors);
//            }
//
//            // Расчёт показателей обороняющейся стороны
//            $arDefender = fight_Calculation::model()->calculationIndicatorsPersonages($units, $fightInfo->id_defender);
//        }
//
//        // Установить статус боя - проходит
//        $this->setFightStatus($idFight, self::FIGHT_STATUS_STARTED);
//
//        //TODO:Необходимо реализовать логику по определению нападающих разбойников
//        // Расчёт показателей нападающей стороны
//        if ($initiatorFight->protection != unit_UnitsMoving::MOVE_PROTECTION) {
//            $arInitiator = fight_Calculation::model()->calculationIndicatorsPersonages(
//                unserialize($initiatorFight->units), $initiatorFight->personage_id
//            );
//        }
//
//        if (empty($arInitiator) OR empty($arDefender)) {
//            throw new DBException('These are not all the participants of fight', $this->status->main_errors);
//        }
//
//        // Провести расчет боя
//        $fightResult = fight_Calculation::model()->calculationFightMechanics($arInitiator, $arDefender);
//        if (empty($fightResult)) {
//            throw new DBException('Not result fight', $this->status->main_errors);
//        }
//
//        /*
//         * Подсчёт результатов боя
//         * Сражения в игре осуществляются между:
//         * 1) разбойник (компьютер) – игрок;
//         * 2) игрок – игрок;
//         */
//
//        //В бою победили разбойники
//        if ($fightResult['squad'] == unit_UnitsMoving::ACCESSORY_LOCATIONS_ROBBERS) {
//
//            //Получить оставшееся количество жизни юнитов защищавшихся разбойников
//            $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                $arDefender["life"], $fightResult["life"][unit_UnitsMoving::NOT_PERSONAGES]);
//
//            //Получить данные об защищавшихся разбойниках
//            $unitsAfterFight = fight_Calculation::model()->writeOffUnitsRobberAfterFight($defenderFightRobber, $lifeDelta);
//            $fameForInitiator = $unitsAfterFight['place_killed_units'];
//            $fightResultOpponent = array('opponent' => $unitsAfterFight);
//
//            //Получить оставшееся количество жизни юнитов нападавшего персонажа
//            $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                $arInitiator["life"], $fightResult["life"][$initiatorFight->personage_id]);
//
//            //Получить данные об нападавшего персонажа
//            $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight(
//                unserialize($initiatorFight->units), $lifeDelta
//            );
//            $fightResultMy = array('my' => $unitsAfterFight);
//
//            $resultProcessFight = array_merge($fightResultOpponent, $fightResultMy);
//
//            //Отправляем сообщение на внутреннюю почту пользователя об результате боя проигравшему
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//
//            $mailAttribute = array('from' => 0,
//                'to' => $initiatorFight->personage_id,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//
//            //Уничтожаем отряд и союзников прибывших для захвата локации с разбойниками
//            $doneDestructionSquad = unit_UnitsMoving::model()->destructionSquad($initiatorFight);
//
//            //Удаляем бой
//            $doneDeleteFight = $this->deleteFight($idFight);
//        }
//
//        //В бою победил персонаж
//        if ($fightResult['squad'] == unit_UnitsMoving::ACCESSORY_LOCATIONS_PERSONAGES) {
//
//            /********************************** БОИ ЮНИТОВ ПЕРСОНАЖА С ЮНИТАМИ ДРУГОГО ПЕРСОНАЖА *******************************/
//
//            //Бой выиграл защитник
//            if ($fightResult["status"] == $fightInfo->id_defender) {
//
//                //Получить оставшееся количество жизни юнитов после боя для защищающихся
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$fightInfo->id_defender]);
//
//                // Получаем список юнитов персонажа и союзников в локации отсортированные по уровню жизни
//                $units = personage_UnitLocation::model()->findAllBattleUnitsInLocation($fightInfo->id_defender, $fightInfo->id_location);
//
//                //Получить данные для защищавшегося персонажа
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight($units, $lifeDelta);
//                $fameForInitiator = $unitsAfterFight['place_killed_units'];
//                $fightResultOpponent = array('opponent' => $unitsAfterFight);
//
//                //Получить оставшееся количество жизни юнитов после боя для нападавшего
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$initiatorFight->personage_id]);
//
//                //Получить данные для нападавшего персонажа
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight(unserialize($initiatorFight->units), $lifeDelta);
//                $fameForDefender = $unitsAfterFight['place_killed_units'];
//                $fightResultMy = array('my' => $unitsAfterFight);
//
//                $resultProcessFight = array_merge($fightResultOpponent, $fightResultMy);
//
//                //Уничтожаем отряд и союзников прибывших для захвата локации с разбойниками
//                $doneDestructionSquad = unit_UnitsMoving::model()->destructionSquad($initiatorFight);
//            }
//
//            //Бой выиграл нападающий c целью (АТАКА С ЗАХВАТОМ) противник которого тоже персонаж, после удаления противника добавляем юнитов в локацию
//            if ($fightResult["status"] == $initiatorFight->personage_id AND
//                $initiatorFight->target == unit_UnitsMoving::MOVE_ATTACK_TACKING AND
//                    $arDefender['idPersonage'] != unit_UnitsMoving::NOT_PERSONAGES
//            ) {
//
//                //Получить оставшееся количество жизни юнитов после боя для нападающих
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$initiatorFight->personage_id]);
//
//                //Получить получить данные об юнитах нападающего персонажа
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight(
//                    unserialize($initiatorFight->units), $lifeDelta
//                );
//                $fameForDefender = $unitsAfterFight['place_killed_units'];
//                $fightResultMy = array('my' => $unitsAfterFight);
//
//                //Получить оставшееся количество жизни юнитов после боя для защищающихся
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$fightInfo->id_defender]);
//
//                // Получаем список юнитов персонажа и союзников в локации отсортированные по уровню жизни
//                $units = personage_UnitLocation::model()->findAllBattleUnitsInLocation($fightInfo->id_defender, $fightInfo->id_location);
//
//                //Получить данные об юнитах нападающего персонажа
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight($units, $lifeDelta);
//                $fameForInitiator = $unitsAfterFight['place_killed_units'];
//                $fightResultOpponent = array('opponent' => $unitsAfterFight);
//
//                $resultProcessFight = array_merge($fightResultOpponent, $fightResultMy);
//
//                /*
//                 * Если нет оставшихся юнитов значит отряд разбит, удаляем прибывший отряд для атаки,
//                 * удаляем отряд противника в локации, а так же текущий бой
//                 */
//                if (empty($unitsAfterFight['surviving_units'])) {
//
//                    //TODO: Необходимо реализация количества погибших юнитов, определить сколько ресурсов в возращается за погибших юнитов
//                    //TODO: Передать все ресурсы в локацию или в игре поле обломков
//
//                    //Уничтожаем отряд нападавшего прибывший для захвата локации
//                    $doneDestructionSquad = unit_UnitsMoving::model()->destructionSquad($initiatorFight);
//
//                    //Уничтожаем юнитов защищающегося, находившегося в локации
//                    $doneDeleteUnitsOfLocation = personage_UnitLocation::model()->deleteUnitsOnIdLocation($fightInfo->id_location);
//
//                    //Удаляем бой
//                    $doneDeleteFight = $this->deleteFight($idFight);
//                } else {
//
//                    //TODO: Необходимо реализация количества погибших юнитов, определить сколько ресурсов в возращается за погибших юнитов
//                    //TODO: Передать все ресурсы в локацию или в игре поле обломков
//                    //Размещаем выигравший бой отряд на локации
//                    $doneAddUnitsLocation = personage_UnitLocation::model()->addUnitsToLocationAfterFight(
//                        $unitsAfterFight['surviving_units'], $initiatorFight->personage_id, $fightInfo->id_location,
//                        $initiatorFight->x_d, $initiatorFight->y_d
//                    );
//                }
//            }
//
//            //Бой выиграл нападающий c целью (АТАКА) противник которого тоже персонаж, после удаления противника забираем ресурсы
//            if ($fightResult["status"] == $initiatorFight->personage_id AND
//                $initiatorFight->target == unit_UnitsMoving::MOVE_ATTACK AND
//                    $arDefender['idPersonage'] != unit_UnitsMoving::NOT_PERSONAGES
//            ) {
//
//                //Получить оставшееся количество жизни юнитов после боя для нападающих
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$initiatorFight->personage_id]);
//
//                //Получить даннеы об защищающемся персонаже
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight(
//                    unserialize($initiatorFight->units), $lifeDelta
//                );
//                $fameForDefender = $unitsAfterFight['place_killed_units'];
//                $fightResultMy = array('my' => $unitsAfterFight);
//
//                //Получить оставшееся количество жизни юнитов после боя для защищающихся
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$fightInfo->id_defender]);
//
//                // Получаем список юнитов персонажа и союзников в локации отсортированные по уровню жизни
//                $units = personage_UnitLocation::model()->findAllBattleUnitsInLocation($fightInfo->id_defender, $fightInfo->id_location);
//
//                //Получить данные об нападающем персонаже
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight($units, $lifeDelta);
//                $fameForInitiator = $unitsAfterFight['place_killed_units'];
//                $fightResultOpponent = array('opponent' => $unitsAfterFight);
//
//                $resultProcessFight = array_merge($fightResultOpponent, $fightResultMy);
//
//                //TODO: Необходимо реализация количества погибших юнитов, определить сколько ресурсов в возращается за погибших юнитов
//                //TODO: Передать все ресурсы в локацию или в игре поле обломков
//
//                //Уничтожаем отряд и союзников находившихся в локации
//                $doneDeleteUnitsOfLocation = personage_UnitLocation::model()->deleteUnitsOnIdLocation($fightInfo->id_location);
//
//                //Забрать ресурсы
//            }
//
//            /********************************** БОИ ЮНИТОВ ПЕРСОНАЖА С РАЗБОЙНИКАМИ **********************************/
//
//            //Бой выиграл нападающий c целью (АТАКА С ЗАХВАТОМ) противник которого разбойники, после боя добавляем юнитов в локацию
//            if ($fightResult["status"] == $initiatorFight->personage_id AND
//                $initiatorFight->target == unit_UnitsMoving::MOVE_ATTACK_TACKING AND
//                    $arDefender['idPersonage'] == unit_UnitsMoving::NOT_PERSONAGES
//            ) {
//                $doneAddUnitsLocation = false;
//
//                //Получить оставшееся количество жизни юнитов после боя для разбойников
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][unit_UnitsMoving::NOT_PERSONAGES]);
//
//                //Получить данные об юнитах разбойников
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsRobberAfterFight($defenderFightRobber, $lifeDelta);
//                $fameForInitiator = $unitsAfterFight['place_killed_units'];
//                $fightResultOpponent = array('opponent' => $unitsAfterFight);
//
//                //Получить оставшееся количество жизни юнитов после боя для нападавшего
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$initiatorFight->personage_id]);
//
//                //Получить данные об юнитах нададающего персонажа
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight(unserialize($initiatorFight->units), $lifeDelta);
//                $fightResultMy = array('my' => $unitsAfterFight);
//
//                $resultProcessFight = array_merge($fightResultOpponent, $fightResultMy);
//
//                //Если пустой массив с выжившими юнитами значит юнитов в нем нет и отряд разбит
//                if (empty($unitsAfterFight['surviving_units'])) {
//
//                    //Уничтожаем отряд прибывший для захвата локации с разбойниками
//                    $doneDestructionSquad = unit_UnitsMoving::model()->destructionSquad($initiatorFight);
//
//                    //Удаляем бой
//                    $doneDeleteFight = $this->deleteFight($idFight);
//                } else {
//
//                    //Размещаем выигравший бой отряд нападавшего персонажа на локации
//                    $doneAddUnitsLocation = personage_UnitLocation::model()->placePersonagesInLocationOfUnits(
//                        $unitsAfterFight['surviving_units'], $initiatorFight->personage_id, $fightInfo->id_location,
//                        $initiatorFight->x_d, $initiatorFight->y_d
//                    );
//                }
//
//                //Удаляем отряд перемещения юнитов и текущий бой, после добавления юнитов в локацию
//                if ($doneAddUnitsLocation === true) {
//
//                    //Уничтожаем отряд и союзников прибывших для захвата локации с разбойниками
//                    $doneDestructionSquad = unit_UnitsMoving::model()->destructionSquad($initiatorFight);
//
//                    //Удаляем бой
//                    $doneDeleteFight = $this->deleteFight($idFight);
//                }
//            }
//
//            //Бой выиграл нападающий c целью (АТАКА) противник которого разбойники
//            if ($fightResult["status"] == $initiatorFight->personage_id AND
//                $initiatorFight->target == unit_UnitsMoving::MOVE_ATTACK AND
//                    $arDefender['idPersonage'] == unit_UnitsMoving::NOT_PERSONAGES
//            ) {
//
//                //Получить оставшееся количество жизни юнитов после боя для разбойников
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][unit_UnitsMoving::NOT_PERSONAGES]);
//
//                //Получить данные об юнитах разбойников
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsRobberAfterFight($defenderFightRobber, $lifeDelta);
//                $fameForInitiator = $unitsAfterFight['place_killed_units'];
//                $fightResultOpponent = array('opponent' => $unitsAfterFight);
//
//                //Получить оставшееся количество жизни юнитов после боя для нападавшего
//                $lifeDelta = fight_Calculation::model()->calculateDifferenceOfLifeAfterFightUnits(
//                    $arInitiator["life"], $fightResult["life"][$initiatorFight->personage_id]);
//
//                //Получить юнитов нападавшего пероснажа оставшихся после боя
//                $unitsAfterFight = fight_Calculation::model()->writeOffUnitsAfterFight(unserialize($initiatorFight->units), $lifeDelta);
//                $fightResultMy = array('my' => $unitsAfterFight);
//
//                $resultProcessFight = array_merge($fightResultOpponent, $fightResultMy);
//
//                //Если пустой массив с выжившими юнитами значит юнитов в нем нет и отряд разбит
//                if (empty($unitsAfterFight['surviving_units'])) {
//
//                    //Уничтожаем отряд прибывший для захвата локации с разбойниками
//                    $doneDestructionSquad = unit_UnitsMoving::model()->destructionSquad($initiatorFight);
//
//                    //Удаляем бой
//                    $doneDeleteFight = $this->deleteFight($idFight);
//                } else {
//                    //TODO:Решить что делать им дальше
//                }
//            }
//        }
//
//        /*
//         * Распределить ресурсы после боя
//         */
//        if (personage_Location::model()->isCity($fightInfo->id_location)) {
//            if ($fightResult["status"] == $fightInfo->id_defender) {
//                /*Если было нападение на город, и защитник города победил то
//                ресурсы, оставшиеся от убитых юнитов (своих и противника) попадают
//                в «поле обломков», которое отображается ввиду значка над городом.
//                */
//                // TODO Отправить ресурсы от убитых в поле обломков
//
//            } elseif ($fightResult["status"] == $fightInfo->id_initiator) {
//                /*Если победил нападавший, то ресурсы, оставшиеся от убитых
//                юнитов (своих и противника) попадают в «поле обломков», которое
//                отображается ввиду значка над городом. А так же войска «разграбля-
//                ют город».
//                */
//                /*«Ограбление города» Игрок может напасть и разорить город
//                противника. Кол-во награбленного – трофеев зависит от общего кол-
//                ва переносимого груза и уровня защиты крепости. (Чем выше уровень
//                защиты склада, тем меньше ресурсов достаётся победителю). Кол-во
//                трофейных ресурсов распределяется равномерно. В случае невоз-
//                можности равномерного распределения (малое кол-во ресурса),
//                оставшееся место распределятся между доступными ресурсами. Если
//                город атаковало несколько игроков/армий, то ресурсы распределяют-
//                ся равномерно.
//                */
//                // TODO Распределить ресурсы
//
//            }
//        } else {
//            /*Если бой был не в городе, то ресурсы забирает победившая сто-
//            рона, но не более чем может унести оставшаяся в живых армия. За
//            каждого убитого юнита противника игрок получает по стольку очков
//            славы сколько занимает места в казарме данный юнит.
//            */
//            // TODO Зачислить ресурсы победившей стороне
//
//
//            //Зачислить количество славы сколько занимает места в казарме  юнит ИНИЦИАТОРУ боя
//            if ($fameForInitiator > self::NO_VALUE) {
//                $doneFameForInitiator = personage_State::model()->formPartOfFame($fameForInitiator, $initiatorFight->personage_id);
//            }
//
//            //Зачислить количество славы сколько занимает места в казарме юнит ЗАЩИТНИКУ в бою
//            if ($fameForDefender > self::NO_VALUE) {
//                $doneFameForDefender = personage_State::model()->formPartOfFame($fameForDefender, $fightInfo->id_defender);
//            }
//
//            if ($doneFameForInitiator === false) {
//                throw new StatusErrorException('Not credited to the initiator battlefield glory', $this->status->main_errors);
//            }
//
//            if ($doneFameForDefender === false) {
//                throw new StatusErrorException('Not credited fame defender', $this->status->main_errors);
//            }
//
//            /*Если бой происходит за локацию и на поле боя остаётся более
//            10 000 ед. железа, 10 000 ед. дерева, 10 000 ед. ткани. То эта локация
//            с вероятностью 10% (регулируется в админке) получает строение
//            "обелиск".
//            */
//        }
//
//        // Установить статус боя - завершен
//        $this->setFightStatus($idFight, self::FIGHT_STATUS_FINISHED);
//
//        // Захват локации нападающим
//        //  if ($fightResult["status"] == $fightInfo->id_initiator) {
//        // Если победил инициатор (нападающий)
//        // Изменяем владельца локации
//        //personage_Location::model()->setLocationOwner($fightInfo->id_location, $fightInfo->id_initiator);
//        //}
//
//        // Перемещения после боя
//
//        // Отправить уведомление участникам боя о результатах сражения
//        if ($fightResult["status"] == self::FIGHT_RESULT_DEAD_HEAT) {
//
//            // Результат боя - ничья
//            //Отправка сообщения об результате сражения нападающему
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//            $mailAttribute = array('from' => 0,
//                'to' => $initiatorFight->personage_id,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//
//            //Отправка сообщения об результате сражения защищающемуся
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//            $mailAttribute = array('from' => 0,
//                'to' => $fightInfo->id_defender,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//
//        } elseif ($fightResult["status"] == $initiatorFight->personage_id) {
//
//            //Отправка сообщения об результате сражения победившему
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//            $mailAttribute = array('from' => 0,
//                'to' => $initiatorFight->personage_id,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//
//            //Отправка сообщения об результате боя проигравшему
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//            $mailAttribute = array('from' => 0,
//                'to' => $fightInfo->id_defender,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//        } elseif ($fightResult["status"] == $fightInfo->id_defender) {
//
//            //Отправка сообщения об результате сражения победившему защищаемуся
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//            $mailAttribute = array('from' => 0,
//                'to' => $fightInfo->id_defender,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//
//            //Отправка сообщения об результате боя проигравшему
//            $mail = mail_Template::model()->makeMailFightResult($resultProcessFight);
//            $mailAttribute = array('from' => 0,
//                'to' => $initiatorFight->personage_id,
//                'subject' => $mail['subject'],
//                'body' => $mail['body']);
//
//            mail_Mapper::model()->createNewNotice($mailAttribute);
//        }
    }


    /**
     * Удалить бой
     *
     * @param $idFight
     * @return bool
     */
    public function deleteFight($fightId)
    {
        $sql = "DELETE FROM " . self::TABLE_NAME . " WHERE `id` = %d LIMIT 1;";
        $result = $this->query($sql, $fightId);
        return $this->getAffectedRows($result) > 0;
    }

    /**
     * Получить все бои у которых прошло время ожидания союзников
     *
     * @param $currentTime
     * @param $statusFight
     * @return fight_Mapper
     */
    public function findBeginningOfFights($currentTime, $statusFight)
    {
        $sql = "SELECT *
                FROM %s
                WHERE `finish_time` <= '%s'
                AND `status` = '%s'
                ORDER BY `id_personages_units_fight` LIMIT 20";

        return $this->findAll($sql, self::TABLE_NAME, $currentTime, $statusFight);
    }

	public function getFightByLocation($x, $y)
	{
		$sql = "
			SELECT *
			FROM " . self::TABLE_NAME . "
			WHERE `x` = %d
				AND `y` = %d
			LIMIT 1
		";

		$result = $this->query($sql, $x, $y);
		if ($result->IsError() || !$result->__DBResult)
			return false;

		return $result->__DBResult[0];
	}

	public function initFight($attackerSquadId, $x, $y)
	{
		$sql = "
			INSERT INTO " . self::TABLE_NAME . "
			SET `attacker_squad_id` = %d,
				`x` = %d,
				`y` = %d
		";
		$result = $this->query($sql, $attackerSquadId, $x, $y);
		return !$result->IsError();
	}

	public function getStartedFights()
	{
		$sql = "
			SELECT `id`, `attacker_squad_id`, `x`, `y`, UNIX_TIMESTAMP(`init_time`) AS `init_time` 
			FROM " . self::TABLE_NAME . "
			WHERE NOW() - `init_time` > " . self::TIME_WAITING_ALLIED . "
		";
		$result = $this->query($sql);
		if ($result->IsError())
			return false;

		return $result->__DBResult;
	}
}
