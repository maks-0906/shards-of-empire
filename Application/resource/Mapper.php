<?php
/**
 * Файл содержит запросы к базе данных связанные с ресурсами
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

class resource_Mapper extends Mapper
{
    const TABLE_NAME = 'resources';

	const GOLD_ID = 1;
	const AMBER_ID = 2;
    const BLESSING_ID = 3; //ID ресурса благославение
    const FOOD_ID = 7;

    const KEY_RESOURCE_AMBER = 'amber';
    const KEY_RESOURCE_GOLD = 'silver';
    const KEY_RESOURCE_BLESSING = 'blessing';
    const KEY_RESOURCE_TREE = 'tree';
    const KEY_RESOURCE_STONE = 'stone';
    const KEY_RESOURCE_IRON = 'iron';
    const KEY_RESOURCE_FOOD = 'food';
    const KEY_RESOURCE_BEER = 'beer';
    const KEY_RESOURCE_WINE = 'wine';
    const KEY_RESOURCE_TISSUE = 'tissue';
    const KEY_RESOURCE_CANDLES = 'candles';

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
     * @return resource_Mapper
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
        $this->doneLocationCity = Auth::isCurrentLocationCity();
    }

    /**
     * Получаем все индефикаторы ресурсов
     *
     * @return resource_Mapper
     */
    public function findAllResources()
    {
        $sql = "SELECT * FROM %s";
        $result = $this->findAll($sql, self::TABLE_NAME);
        return $result;
    }


    /**
     * Получаем данные для заполнения модального окна ресурсов города
     *
     * @param $idPersonage
     * @return array|resource_Mapper
     */
    public function findEverythingRelatedToResources($idPersonage)
    {
        if ($this->doneLocationCity === true) {
            $coordinates = Auth::getCurrentLocationCoordinates();

            $sql = "SELECT `r`.name_resource, `r`.type, `r`.id AS id_resource,
                           `pc`.*, `pc`.id AS id_city,
					   (SELECT count(id_personages_cities_epidemic) FROM %2\$s as pce
						WHERE `pce`.`personages_cities_id` =
						      (SELECT `id` FROM %3\$s as pc WHERE `pc`.x_c = %4\$d AND `pc`.y_c = %5\$d AND `pc`.id_personage = %6\$d)) AS number_epidemic
					FROM %1\$s AS r, %3\$s AS pc
					WHERE `pc`.x_c = %4\$d
					AND `pc`.y_c = %5\$d
					AND `pc`.id_personage = %6\$d";

            return $this->findAll($sql, self::TABLE_NAME, personage_CityEpidemic::TABLE_NAME,
                                        personage_City::TABLE_NAME, $coordinates['x'], $coordinates['y'],
                                        $idPersonage);

        } else
            return array();
    }
}
