<?php
/**
 * Файл является моделью к таблице базы данных (definition_sections), которою содержит значения количества строительных
 * площадок в зависимости от уровня здания "КРЕПОСТЬ"
 */

class  building_DefinitionSections extends Mapper
{
    const TABLE_NAME = 'definition_sections';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return building_DefinitionSections
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
        return 'id_definition_sections';
    }


    /**
     * Производим выборку количества строительных площадок по уровню здания "КРЕПОСТЬ"
     * и количество построек в городе персонажа
     *
     * @param $idPersonage
     * @param $x
     * @param $y
     * @param $classifier
     * @return building_DefinitionSections
     * @throws DBException
     */
    public function findNumberSectionsLevelBuildingFortress($idPersonage, $x, $y, $classifier)
    {
        $sql = "SELECT `ds`.number_construction_sections,

                       /*Получаем количество построенных зданий*/
                       (SELECT count(`pb`.building_id)
                        FROM %2\$s as pb
                        INNER JOIN %3\$s as b
                        ON (`b`.id = `pb`.building_id)
                        WHERE `pb`.city_id =
                              (SELECT `id` FROM %5\$s WHERE x_c=%6\$d AND y_c=%7\$d AND `personage_id`=%8\$d)
                         AND `b`.classifier = '%10\$s'
                         ) as constructed_buildings,

                        /*Получаем максимальное количество строительных площадок*/
                       (SELECT max(`ds`.number_construction_sections)
                        FROM %1\$s as ds
                        )as max_number_construction_sections

                FROM %1\$s as ds
                INNER JOIN %2\$s as pb
                  ON (`ds`.level_building_fortress = `pb`.current_level)
                INNER JOIN %3\$s as b
                  ON (`b`.id = `pb`.building_id)
                WHERE `b`.name = '%4\$s'
                AND `pb`.city_id = (SELECT `id` FROM %5\$s WHERE x_c=%6\$d AND y_c=%7\$d AND `personage_id`=%8\$d)
                AND `pb`.current_level > %9\$d";

        $result = $this->find($sql, self::TABLE_NAME, personage_Building::TABLE_NAME, building_Mapper::TABLE_NAME,
                                building_Mapper::KEY_BUILDING_FORTRESS, personage_City::TABLE_NAME, $x, $y, $idPersonage,
                                personage_Building::NO_LEVEL_BUILDING, $classifier);

        if ($this->getErrors() != NULL) {
            throw new DBException('Error in getting construction sections');
        }else{
            return $result;
        }
    }

    /**
     * Произвести расчет для определения количества оставщихся строительных площадок
     *
     * @param $numberSections
     * @param $constructedBuildings
     * @return mixed
     */
    public function calculateRemainingNumberConstructionSections($numberSections, $constructedBuildings)
    {
        return $numberSections - $constructedBuildings;
    }
}