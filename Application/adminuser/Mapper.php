<?php


/**
 * Description of Mapper
 *
 * @author al
 */
class adminuser_Mapper extends Mapper {
	public function GetCount()
	{
		$row = $this->query('SELECT COUNT(*) ct FROM `users`')->valid();
		return $row->ct;
	}

	/**
	 * Получение экземпляра сущности.
	 * @param string $className
	 * @return chat_Mapper
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	public function tableName()
	{
		return 'users';
	}

	public function GetList($from = 0, $count = 50)
	{
		return $this->findAll('SELECT * FROM `users` LIMIT %d, %d', $from, $count);
	}

	public function GetUserById($id)
	{
		return $this->query('SELECT * FROM `users` WHERE id=%d LIMIT 1', $id)->valid();
	}

	public function saveAdmin(Entity $eUser)
	{
		if($eUser->getid() > 0)
		{
			$this->update('users', $eUser, 'id');
		}
		else
		{
			$eUser->setid(null);
			$this->insert('users', $eUser);
		}
	}

	public function del(array $aId)
	{
		$where = implode(', ', array_map('intval', $aId));
		$this->query('DELETE FROM `users` WHERE id in (%s)', $where);
	}
}


