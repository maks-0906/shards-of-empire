<?php


/**
 * Description of Mapper
 *
 * @author al
 */
class adminworld_Mapper extends Mapper
{
	const TABLE_NAME = 'worldlist';

	const MAX_X = 100;
	const MAX_Y = 100;

	/**
	 * Получение экземпляра сущности.
	 * @param string $className
	 * @return adminworld_Mapper
	 */
	public static function model($className = __CLASS__)
	{
		return new $className();
	}

	public function tableName()
	{
		return self::TABLE_NAME;
	}

	public function GetCount()
	{
		$row = $this->query('SELECT COUNT(*) ct FROM `worldlist`')->valid();
		return $row->ct;
	}

	public function GetList($from = 0, $count = 50)
	{
		return $this->findAll('
            SELECT
                wl.*, ll.name lang
            FROM
                `worldlist` wl LEFT JOIN langlist ll ON (wl.lang_id = ll.id)
            LIMIT %d, %d', $from, $count);
	}

	public function GetEntityById($id)
	{
		return $this->query('SELECT * FROM `worldlist` WHERE id=%d LIMIT 1', $id)->valid();
	}

	/**
	 * @param $id
	 * @return Mapper|null
	 */
	public function findWorldById($id)
	{
		return $this->find('SELECT * FROM `%s` WHERE id=%d LIMIT 1', $this->tableName(), $id);
	}

	public function saveAdmin(Entity $eUser)
	{
		if($eUser->getid() > 0)
		{
			$this->update('worldlist', $eUser, 'id');
		}
		else
		{
			$eUser->setid(null);
			$this->insert('worldlist', $eUser);
		}
	}

	public function del(array $aId)
	{
		$where = implode(', ', array_map('intval', $aId));
		$this->query('DELETE FROM `worldlist` WHERE id in (%s)', $where);
	}

	/**
	 * Определение загруженности карты.
	 *
	 * @param int $idWorld
	 * @return adminworld_Mapper
	 * @throws StatusErrorException
	 */
	public function findMapForCalculationLoaded($idWorld)
	{
		$sql = "SELECT
					(SELECT count(*) FROM %1\$s as per WHERE `per`.world_id=%2\$d) as current_count_users,
					`map`.max_users,
					`map`.`map_template`,
					`map`.`id` as id
				FROM %3\$s as map
				WHERE`map`.`id`=%2\$d";

		$mapTemplate = $this->find($sql, personage_Mapper::model()->tableName(), $idWorld, $this->tableName());

		if($mapTemplate == null)
			throw new StatusErrorException('Such a world is not found in the system');

		return $mapTemplate;
	}
}


