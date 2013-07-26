<?php
/**
 * Файл содержит класс модель с логикой и запросами к БД в предметной области персонажа.
 *
 * @author Greg
 * @package personage
 */

/**
 * Класс модель отображения таблицы персонажей, а так же является AR включающим в себя бизнес-логику
 * и запросы группирующими всю концепцию по персонажу.
 * Запросы так же включают бизнес логику предметной области с персонажа.
 *
 * @author Greg
 * @version 1.0.0
 * @package personage
 */
class personage_Mapper extends Mapper
{

    /**
     * Имя отображаемой таблицы
     */
    const TABLE_NAME = 'personages';

    const NORMAL_STATUS = 'normal';
    const BANNED_STATUS = 'banned';
    const DELETE_STATUS = 'delete';

    /**
     * Идентификатор текущего пользователя.
     *
     * @var integer
     */
    private $idCurrentUser;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return personage_Mapper
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
     * Инициализация первоначальных параметров сущности.
     */
    public function init()
    {
        $this->idCurrentUser = Auth::getInstance()->GetSessionVar(SESSION_USER_ID);
    }

    /**
     * Получаем все атрибуты персонажа, для создания нового персонажа пользователем
     * @return mixed
     */
    public function getAllAttributes()
    {
        $sql = "SELECT * FROM %s";
        $type_personage = personage_Type::model()->getAttributesTypePersonage($sql);
        $fraction_personage = personage_Fraction::model()->getAttributesFractionsPersonage($sql);
        $religion_personage = personage_Religion::model()->getAttributesReligionPersonage($sql);

        return array('fractions' => $fraction_personage,
            'types' => $type_personage,
            'religions' => $religion_personage);
    }

    /**
     * Проверка существования персонажа в мире по его нику.
     *
     * @param string $nick
     * @param int $idWorld
     * @return bool
     */
    public function isExistsPersonage($nick, $idWorld)
    {
        $sql = "SELECT `id` FROM %s WHERE `nick` = '%s' AND `world_id` = %d";
        $done = $this->find($sql, $this->tableName(), $nick, (int)$idWorld);

        if ($done->id != NULL) {
            return true;
        }

        return false;
    }

    /**
     * Поиск персонажа по его идентификатору.
     *
     * @param int $idPersonage
     * @return personage_Mapper
     */
    public function findPersonageById($idPersonage)
    {
        $sql = "SELECT * FROM %s, %s WHERE `id`=%d";
        return $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idPersonage);
    }

    /**
      * Поиск персонажа по его идентификатору и уровнем мира.
      *
      * @param $idPersonage
      * @param $idWorld
      * @return personage_Mapper
      */
     public function findPersonageByIdAndIdWorld($idPersonage, $idWorld)
     {
         $sql = "SELECT * FROM %s, %s WHERE `id`=%d AND `world_id` = %d";
         return $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idPersonage, $idWorld);
     }

    
    /**
     * Поиск всех персонажей
     *
     * @param array $fields - список полей к выдаче, по умолчанию - все
     * @return personage_Mapper
     */
    public function findAllPersonages($fields = array())
    {
        $sql = "SELECT p.%s, u.`lang` FROM %s as p, %s as u WHERE p.`user_id` = u.`id`";
        
        return $this->findAll($sql, count($fields) ? implode(",", $fields) : "*",
			                       self::TABLE_NAME, user_Mapper::TABLE_NAME
		);
    }

    /**
     * Поиск персонажа текущего пользователя в определённом мире.
     *
     * @param int $idWorld
     * @return personage_Mapper|null
     */
    public function findPersonageForCurrentUserAndWorld($idWorld)
    {
        $sql = "SELECT `p`.*, `ps`.*
				FROM %s as p
				LEFT OUTER JOIN %s as ps ON `p`.id=`ps`.id_personage
				WHERE `p`.`world_id`=%d AND `p`.`user_id`=%d";

        $personage = $this->find($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idWorld, Auth::id());

        return $personage;
    }

    /**
     * Поиск персонажей для определённого мира.
     *
     * @param int $idWorld
     * @return personage_Mapper
     */
    public function findPersonagesForRequiredWorld($idWorld)
    {
        $sql = "SELECT * FROM %s, %s WHERE `world_id`=%d";
        return $this->findAll($sql, self::TABLE_NAME, personage_State::TABLE_NAME, $idWorld);
    }

    /**
     * Поиск персонажей для определённого мира.
     *
     * @param int $idWorld
     * @param int $timeOnline
     * @return personage_Mapper[]
     */
    public function findOnlinePersonagesForRequiredWorld($idWorld, $timeOnline)
    {
        $sql = "SELECT `p`.*, `d`.name_dignity
        		FROM %s as p, %s as ps, %s as d
        		WHERE `p`.world_id=%d AND (%d - `p`.time_online) < %d
        			AND `p`.id=`ps`.id_personage AND `ps`.id_dignity=`d`.id_dignity";

		// TODO: создать модель для таблицы титулов и вставить вместо строки константу TABLE_NAME
        return $this->findAll(
			$sql,
			self::TABLE_NAME, personage_State::TABLE_NAME, 'dignity',
			$idWorld, time(), $timeOnline
		);
    }

    /**
     * Получение последних координат персонажа.
     *
     * @return array|bool
     */
    public function getLastPosition()
    {
        if ($this->x_l == null || $this->y_l == null) return array();
        return array($this->y_l, $this->x_l);
    }

    /**
     * Создание нового персонажа.
     * Инициализируем сессию персонажа.
     *
     * @param int $idWorld
     * @param int $idFraction
     * @param int $idTypePersonage
     * @param int $idReligion
     * @param string $nick
     * @param string $city
     * @param bool|int $idUser
     * @return bool|personage_Mapper
     * @throws DBException|StatusErrorException
     */
    public function createNewPersonage($idWorld, $idFraction, $idTypePersonage, $idReligion, $nick, $city, $idUser = false)
    {
        $currentUser = ($idUser === false) ? $this->idCurrentUser : $idUser;

        try {
            $this->begin();

            $this->user_id = (int)$currentUser;
            $this->nick = $nick;
            $this->world_id = $idWorld;
            $this->time_online = time();
            $newPersonage = $this->save();
            if ($newPersonage->isError()) throw new DBException('Personages not create');

            $location = $this->definingLocationCastleForCreatePersonage($idWorld, $idFraction);
            if (!($location instanceof adminworld_Cell)) throw new DBException('Location not defined', $location);
            /* @var integer $x */
            /* @var integer $y */
            $x = $location->x;
            $y = $location->y;


            /* @var $state personage_State */
            $newState = personage_State::model()->saveState(
                $newPersonage->id,
                $idFraction,
                $idTypePersonage,
                $idReligion,
                $x,
                $y
            );
            if ($newState->isError()) throw new DBException('Personages state not save', $newState);

            /* @var $newCity personage_City */
            $doneCity = personage_City::model()->saveCity(
                $newPersonage->id,
                $city,
                $x, $y
            );
            if ($doneCity->isError()) throw new DBException('Personages city not create', $doneCity);

            // Наполнение дефолтными зданиями города
            $status = personage_Building::STATUS_CONSTRUCTION_FINISH;
            $constructedBuilding = personage_Building::model()->fillingBuildingsForPersonage($doneCity->id,
                $newPersonage->id,
                $status);
            if ($constructedBuilding == false) {
                throw new DBException('Default building not create for city');
            } else {

                //Добавление базовых бонусов для зданий
                personage_BuildingBonus::model()->addNewBuildingBonuses($constructedBuilding);
            }

            //Заполняем таблицу ресурсов перснажа начальными значениями ресурсов
            $doneResource = personage_ResourceState::model()->insertPrimaryDataResources($newPersonage->id, $doneCity->id);
            if ($doneResource == false) throw new DBException('Personages state resources not create');

            //Создаем гарнизон из первичных боевых юнитов
            $donePrimaryUnit = personage_Unit::model()->insertPrimaryUnit($doneCity->id);
            if ($donePrimaryUnit == false) throw new DBException('Primary garrison not create');

            //Заполняем таблицу исследований перснажа существующими исследованиями со значением уровня (0) ноль
            $doneResearch = personage_ResearchState::model()->insertPrimaryDataResearch($newPersonage->id);
            if ($doneResearch == false) throw new DBException('Personages research not create');

            $createSuccessfully = $this->commit();
            //В случае успешных добавлений в базу данных создаем сессию персонажа
            if ($createSuccessfully == true) {
                Auth::getInstance()->SetSessionVar(SESSION_PERSONAGE_ID, $newPersonage->id);
                return $newPersonage->id;
            } else
                return false;
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
            if (DEBUG === true) throw new StatusErrorException($e->getMessage(), $this->oStatus->main_errors);
        }
    }

    /**
     * Размещение замка персонажа и определение начальных координат при создании персонажа.
     *
     * @param int $idWorld
     * @param int $idFraction
     * @return adminworld_Cell
     * @throws DBException
     */
    public function definingLocationCastleForCreatePersonage($idWorld, $idFraction)
    {
        // Определение текущей загрузки карты
        $mapTemplate = adminworld_Mapper::model()->findMapForCalculationLoaded($idWorld);

        $loaded = $mapTemplate->current_count_users / $mapTemplate->max_users;

        /* @var $cell adminworld_Cell */
        $cell = null;
        // Если загрузка максимальная
        if ($loaded == 1) {
            // размещаем рандомно в любой руине,
            // выбираем ячейку с идентификатором фракции, которая находится в соте с наименьшим количество игроков в этой фракции
            $cell = adminworld_Cell::model()->findCellRuinInCombsWithFewestNumberOfPersonages($idWorld);
            // из первой же соты получаем ячейку-руину и преобразовываем её фракцию идентификатор
            $cell->id_fraction = intval($idFraction);
        } // Иначе, загрузка позволяет размещение персонажа
        else {
            // производим поиск свободной соты (менее 6 персонажей) требуемого мира и требуемой фракции
            $comb = adminworld_Comb::model()->findFreeComb($idWorld, $idFraction);
            // Если сота найдена
            if ($comb instanceof adminworld_Comb) {
                // размещаем персонажа (получаем координаты ячейки руины) и обновляем таблицы соту и мир
                $cell = adminworld_Cell::model()->findFreeCellRequiredComb($comb);

                $comb->current_count_personages = $comb->current_count_personages + 1;
                $comb->save();
                if (count($comb->getErrors()) > 0) throw new DBException('Comb not save', $comb);
            } // Иначе сот больше нет доступных по фракции
            else {
                // выбираем соту с идентификатором фракции, которая имеет наименьшее количество игроков в этой фракции
                $cell = adminworld_Cell::model()->findCellRuinInCombsWithFewestNumberOfPersonages($idWorld);
                // из первой же соты получаем ячейку-руину и преобразовываем её фракцию идентификатор
                $cell->id_fraction = intval($idFraction);
            }
        }

        if ($cell === null || $cell instanceof stdClass) throw new DBException('Not defined location', $cell);

        $cell->map_pattern = pattern_Mapper::CITY_PATTERN;
        $cell->save();

        if (count($cell->getErrors()) > 0) throw new DBException('Pattern for cell-ruin is not changed', $cell);

        return $cell;
    }

    /**
     * Определение существования персонажа и получение, если существует, для текущего пользователя и требуемого мира.
     *
     * @param null|int $idWorld
     * @throws StatusErrorException
     * @return personage_Mapper
     */
    public static function detectExistsAndGetPersonageForCurrentUser($idWorld)
    {
        $currentPersonage = null;
        $self = self::model();
        $idPersonage = Auth::getIdPersonage();
        if ($idWorld != null)
            $currentPersonage = $self->findPersonageForCurrentUserAndWorld($idWorld);
        elseif ($idPersonage != null)
            $currentPersonage = $self->findPersonageById($idPersonage); else
            throw new StatusErrorException(
                'Not defined any parameters to search for personage',
                self::model()->oStatus->main_errors
            );

        if ($currentPersonage == null)
            throw new StatusErrorException(
                'For current user personage not found in require world ',
                self::model()->oStatus->personage_not_exists
            );

        return $currentPersonage;
    }

    /**
     * Обновление время онлайн персонажа.
     *
     * @param int $time
     * @param bool|int $idPersonage
     * @return personage_Mapper
     * @throws E1Exception
     */
    public function setTimeOnline($time, $idPersonage = false)
    {
		$id = null;
        if ($this->id == null)
		{
			if($idPersonage == false) throw new E1Exception('Parameter `id` not defined');
			$id = $idPersonage;
		}
		else
			$id = $this->id;

        $sql = "UPDATE %s SET `time_online`= %d WHERE `id`=%d";
        return $this->query($sql, $this->tableName(), $this->formatTime($time), $id);
    }

    /**
     * Запрет персонажа на определённый срок.
     * TODO: Возможно потребуется использовать unix метку и всё, вместо TIME_STAMP
     * @param int $idPersonage
     * @param string $time
     * @return personage_Mapper
     * @throws Exception
     */
    public function setBanned($idPersonage, $time)
    {
        $sql = "UPDATE %s SET `status`='%s', `finish_banned`= NOW()+%s WHERE `id`=%d";
        return $this->query($sql, $this->tableName(), self::BANNED_STATUS, $this->formatTime($time), $idPersonage);
    }

    /**
     * @param $time
     * @return mixed
     */
    private function formatTime($time)
    {
        return $time;
    }

	/**
	 * Бизнес логика определения симпатии между двумя персонажами.
	 *
	 * @param int $mySympathy
	 * @param int $hireSympathy
	 * @return int
	 */
	public function detectSympathyBetweenPersonages($mySympathy, $hireSympathy = 0)
	{
		return $mySympathy + $hireSympathy;
	}
}
