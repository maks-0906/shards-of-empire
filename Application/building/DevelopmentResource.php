<?php
/**
 * Файл содержит запросы к базе данных связанные с ресурсами для стоимости зданий
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

class building_DevelopmentResource extends Mapper
{

    const TABLE_NAME = 'building_development_resource_value';

      /**
       * Получение экземпляра сущности.
       *
       * @param string $className
       * @return  building_DevelopmentResource
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
          return 'id_building_development_resource_value';
      }


}
