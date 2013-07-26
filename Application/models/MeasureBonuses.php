<?php
/**
 * Класс является моделью к таблице (measure_bonuses), содержащий еденицы измерений для полей
 */
class models_MeasureBonuses extends Mapper
{
    const TABLE_NAME = 'measure_bonuses';
    const ESSENCE_BUILDING = 'building';

    /**
       * Получение экземпляра сущности.
       *
       * @param string $className
       * @return models_MeasureBonuses
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
          return 'id_measure_bonuses';
      }

    /**
     * Получить значение по имени бонуса
     *
     * @param $allField
     * @param $essence
     * @return array|models_MeasureBonuses
     */
    public function findValueNameBonus($allField, $essence)
    {
        $in = '';
        foreach ($allField as $nameBonus) {
            $in .= "'" . $nameBonus ."'" . ',';
        }

        //Удаляем последнюю запятую в части запроса
        $in_part = substr($in, 0, -1);

        $sql = "SELECT * FROM %s WHERE `essence` = '%s' AND `name_bonus` IN ($in_part)";
        return $this->findAll($sql, self::TABLE_NAME, $essence);
    }
}
