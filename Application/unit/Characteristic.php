<?php
/**
 * Файл содержит класс модель, управляющая характеристиками юнитов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @package unit
 */

/**
 * Класс модель, управляющая характеристиками юнитов.
 *
 * @author vetalrakitin  <vetalrakitin@gmail.com>
 * @version 1.0.0
 * @package unit
 */
class unit_Characteristic extends Mapper
{

    const TABLE_NAME = 'units_characteristics';

    /**
     * Получение экземпляра сущности.
     *
     * @param string $className
     * @return unit_Characteristic
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
     * Определение минимальной скорости перемещения по самому медленному юниту.
     *
     * @param array $units
     * @return unit_Characteristic|null
     */
    public function detectUnitWithMinSpeed($units)
    {
        $ids = implode(',', $units);
        $sql = "SELECT min(speed) as speed FROM %s WHERE unit_id IN(" . $ids . ")";

        return $this->find($sql, self::TABLE_NAME);
    }

    /**
     * Получить характеристики юнита по ИД юнита
     *
     * @param int $idUnit
     * @return unit_Characteristic|null
     */
    public function findUnitCharacteristicByIdUnit($idUnit)
    {
        $sql = "SELECT * FROM %s WHERE unit_id = %d";
        return $this->find($sql, self::TABLE_NAME, $idUnit);
    }

    /**
     * Получить все характеристики юнита
     *
     * @return unit_Characteristic|null
     */
    public function findAllUnitCharacteristic()
    {
        $sql = "SELECT * FROM %s as uc INNER JOIN %s as u ON (uc.`unit_id` = u.`id`)";
        return $this->query($sql, self::TABLE_NAME, unit_Mapper::TABLE_NAME);
    }

    /**
     * Получить индикаторы (атака, защита, жизнь)
     * с учетом всех возможных бонусов для юнита по ИД юнита
     *
     * @param int $idUnit - ИД юнита
     * @param int $idPersonage - ИД персонажа
     * @return array(
     *         "attack" => атака,
     *         "life" => жизнь,
     *         "protected" => защита
     *    )
     */
    public function getUnitIndicatorsWithAllBonusByIdUnitAndIdPersonage($idUnit, $idPersonage)
    {
        // Получить базовые показатели
        $result = $this->findUnitCharacteristicByIdUnit($idUnit);

        /* Источники бонусов:
        * Вещи для персонажа и их свойства
        * Рыцари советники
        */

        // TODO Получить бонусы за вещи персонажа

        // TODO Получить бонусы за назначенных на должность рыцарей-советников

        // TODO Получить другие бонусы

        return array("attack" => $result->attack, "life" => $result->life, "protection" => $result->protection);
    }

	public function getAllUnitCharacteristic()
	{
		$sql = "
			SELECT uc.`unit_id`,
				uc.`attack`,
				uc.`protection`,
				uc.`life`,
				uc.`number_transported_cargo` AS cargo,
				uc.`number_points_construction_fame` AS fame,
				urv1.`value` AS stone,
				urv2.`value` AS tissue,
				urv3.`value` AS beer
			FROM " . self::TABLE_NAME . " AS uc
			LEFT JOIN `units_resources_value` AS urv1
				ON urv1.`resource_id` = 4
				AND urv1.`units_characteristics_id` = uc.`unit_id`
				AND urv1.`type` = 'balance_resources'
			LEFT JOIN `units_resources_value` AS urv2
				ON urv2.`resource_id` = 6
				AND urv2.`units_characteristics_id` = uc.`unit_id`
				AND urv2.`type` = 'balance_resources'
			LEFT JOIN `units_resources_value` AS urv3
				ON urv3.`resource_id` = 7
				AND urv3.`units_characteristics_id` = uc.`unit_id`
				AND urv3.`type` = 'balance_resources'
			ORDER BY uc.`unit_id`
		";
		$result = $this->query($sql);
		return $result->__DBResult;
	}
}
