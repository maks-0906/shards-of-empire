<?php

/**
 * Description of Mapper
 *
 * @author al
 */
class lang_Mapper extends Mapper {

	/**
	 * Получение экземпляра сущности.
	 * @param string $className
	 * @return lang_Mapper
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	public function tableName()
	{
		return 'langlist';
	}

    public function GetLangList() {
        return $this->findAll('SELECT * FROM `%s`', $this->tableName());
    }
}

