<?php
/**
 * Файл содержит запросы к базе данных связанные с улучшениями зданий
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

class building_Upgrade extends Mapper
{
    const TABLE_NAME = 'building_upgrade';

    /**
     * Невозможно провести внутреннее улучшение
     */
    const IMPOSSIBLE_UPGRADE = 0;

    /**
     * Значение сессии (personage_id)
     * @var int
     */
    public $idPersonage;

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_Upgrade
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
    }


    /**
     * Первичный ключ для текущей таблицы.
     * @return string
     */
    public function pk()
    {
        return 'id_building_upgrade';
    }

	/**
	 * Поиск улучшений для определённого здания и персонажа с учётом положения персонажа.
	 * Если персонаж находится не в своём городе вернётся пустая
	 *
	 * @param int $idBuilding
	 * @param int $idPersonage
	 * @param int $x
	 * @param int $y
     * @param int $idBuildingPersonage
	 * @return building_Upgrade[]
	 */
	public function findImproveBuildingAtCurrentLevelForPersonageAndCity($idBuilding, $idPersonage, $x, $y, $idBuildingPersonage)
	{
		$sql = 'SELECT
					blu.id_building, blu.max_level as required_level_building, pb.current_level as current_level_building,
					bu.name_upgrade as name_improve, bu.id_building_upgrade as id_improve,
					bu.time_research as time_improve, pbi.finish_time_improve,
					pbi.status as status_improve
				FROM %1$s as blu
					INNER JOIN %2$s as pb ON blu.id_building=pb.building_id AND `pb`.personage_id=%3$d
					   AND `pb`.city_id=(SELECT id FROM personages_cities WHERE x_c=%4$d AND y_c=%5$d)
					LEFT JOIN %6$s as bu ON `blu`.id_level_upgrade=`bu`.id_building_level_upgrade
					LEFT OUTER JOIN %7$s as pbi ON bu.id_building_upgrade=pbi.id_building_upgrade
					   AND pbi.id_building_personage=pb.id_building_personage
				WHERE `blu`.id_building=%8$d
				AND `pb`.id_building_personage = %9$d';

		return $this->findAll(
			$sql,
			building_LevelUpgrade::TABLE_NAME, personage_Building::TABLE_NAME, $idPersonage, $x, $y,
			self::TABLE_NAME, personage_Improve::TABLE_NAME, $idBuilding, $idBuildingPersonage
		);
    }

    /**
     * Поиск улучшений для здания на уровень выше
     *
     * @param $idBuilding
     * @param $idUpgrade
     * @param $x
     * @param $y
     * @param $idPersonage
     * @param $idPersonageBuilding
     * @return building_Upgrade[]
     */
    public function findNextBuildingAtCurrentLevelForPersonageAndCity($idBuilding, $idUpgrade, $x, $y, $idPersonage, $idPersonageBuilding)
    {
         $sql = 'SELECT
                   `bu`.time_research, `burv`.resource_id, `burv`.upgrade_resource_value as price,
                   `blu`.max_level, `pb`.id_building_personage, `b`.name, `pb`.current_level,

                   /*
                   * Определяем возможно ли провести улучшение по уровню здания
                   * 1 - уровень здания позволяет провести внутреннее улучшение
                   * 0 - уровень здания не позволяет провести внутреннее улучшение
                   */
                   IF (`pb`.current_level >= `blu`.max_level, 1, 0) say_whether_level_building,

                   /*
                   * Определяем существуют ли уже у здания запрашиваемые внутренние улучшения
                   * 1 - данных внутренних улучшений у зданий нет
                   * 0 - данное внутреннее улучшение у здания уже существует
                   */
                   IF ((SELECT `pi`.status
                       FROM %6$s as pi, %4$s as pb
                       WHERE `pi`.id_building_personage = `pb`.id_building_personage
                       AND `pb`.city_id = (SELECT `id` FROM %7$s WHERE x_c=%8$d AND y_c=%9$d)
                       AND `pi`.id_building_upgrade = %10$d
                       AND `pb`.building_id = %11$d
                       AND `pb`.id_building_personage = %13$d
                       AND `pb`.personage_id = %12$d) IS NULL, 1, 0) is_current_upgrade,

                   /*
                    * Определяем хватает ли у персонажа ресурсов для проведения внутреннего улучшения
                    *  1 - ресурсов достаточно
                    *  0 - ресурсов не достаточно
                    */
                   IF (`prs`.personage_resource_value >= `burv`.upgrade_resource_value, 1, 0) resources_personage

                 FROM %1$s as bu
				 INNER JOIN %2$s as burv
					ON (`bu`.id_building_upgrade = `burv`.id_building_upgrade)
				 INNER  JOIN %3$s as blu
					ON (`bu`.id_building_level_upgrade = `blu`.id_level_upgrade)
				 INNER JOIN %4$s as pb
					ON (`pb`.building_id = `blu`.id_building)
				 INNER JOIN  %5$s as prs
					ON (`prs`.resource_id = `burv`.resource_id)
			     INNER JOIN %14$s as b
			        ON (`pb`.building_id = `b`.id)
                 WHERE `pb`.id_building_personage = %13$d
						 AND `pb`.city_id = (SELECT `id` FROM %7$s WHERE x_c=%8$d AND y_c=%9$d)
						 AND (`prs`.personages_cities_id = (SELECT `id` FROM %7$s WHERE x_c=%8$d AND y_c=%9$d)
						      OR `prs`.personages_cities_id IS NULL)
						 AND `bu`.id_building_upgrade = %10$d
						 AND `blu`.id_building = %11$d
						 AND `pb`.personage_id = %12$d';

        return $this->findAll($sql, self::TABLE_NAME, building_UpgradeResource::TABLE_NAME,
                                building_LevelUpgrade::TABLE_NAME, personage_Building::TABLE_NAME,
                                personage_ResourceState::TABLE_NAME, personage_Improve::TABLE_NAME,
                                personage_City::TABLE_NAME, $x, $y, $idUpgrade, $idBuilding, $idPersonage,
                                $idPersonageBuilding, building_Mapper::TABLE_NAME);
    }

    /**
     * Найти бонусы для определенного улучшения
     *
     * @param $idUpgrade
     * @return array|building_Upgrade
     */
    public function findBuildingBonusesNeededUpgrade($idUpgrade)
      {
          $sql = "SELECT `bu`.*
                  FROM %1\$s as bu
                  INNER JOIN %2\$s as blu
                    ON (`blu`.id_level_upgrade = `bu`.id_building_level_upgrade)
                  WHERE `bu`.id_building_upgrade = %3\$d";

          return $this->findAll($sql, self::TABLE_NAME, building_LevelUpgrade::TABLE_NAME, $idUpgrade);
      }
}
