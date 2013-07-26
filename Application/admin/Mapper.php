<?php

/**
 * Description of Mapper
 *
 * @author al
 */
class admin_Mapper extends Mapper{

	/**
	 * Получение экземпляра сущности.
	 *
	 * @param string $className
	 * @return chat_Mapper
	 */
	public static function model($className=__CLASS__)
	{
		return new $className();
	}

	/**
	 * @return string
	 */
	public function tableName() { return 'admin';}

	/**
	 * @param $email
	 * @param $password
	 * @return bool
	 */
	public function CheckAdmin($email, $password) {
        $admin = $this->find('
            SELECT count(*) ct
            FROM admin
            WHERE email="%s" AND password="%s"
            ',$email, $password);

		if($admin == null)
			return false;
		else
			return $admin;
        //return $row['ct']>0;
    }

}

