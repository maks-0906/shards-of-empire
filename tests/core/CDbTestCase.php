<?php
/**
 * This file contains the CDbTestCase class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once 'PHPUnit/Autoload.php';

/**
 * CDbTestCase is the base class for test cases about DB-related features.
 *
 * CDbTestCase provides database fixture management with the help of {@link CDbFixtureManager}.
 * By declaring {@link fixtures} property, one can ensure the specified
 * tables have the expected fixture state when executing each test method.
 * In addition, CDbTestCase provides two ways to access the fixture data.
 *
 * For example, assume we declare {@link fixtures} to be:
 * <pre>
 * public $fixtures=array(
 *     'posts' => 'Post',
 *     'comments' => 'Comment',
 * );
 * </pre>
 *
 * We can access the original fixture data rows using <code>$this->posts</code>
 * <code>$this->posts['first post']</code>. We can also retrieve an ActiveRecord instance
 * corresponding to a fixture data row using <code>$this->posts('first post')</code>.
 * Note, here 'first post' refers to a key to a row in the original fixture data.
 *
 * @property CDbFixtureManager $fixtureManager The database fixture manager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDbTestCase.php 3426 2011-10-25 00:01:09Z alexander.makarow $
 * @package system.test
 * @since 1.1
 */
abstract class CDbTestCase extends PHPUnit_Framework_TestCase
{
	/**
	 * @var array a list of fixtures that should be loaded before each test method executes.
	 * The array keys are fixture names, and the array values are either AR class names
	 * or table names. If table names, they must begin with a colon character (e.g. 'Post'
	 * means an AR class, while ':post' means a table name).
	 * Defaults to false, meaning fixtures will not be used at all.
	 */
	protected $fixtures=false;

	/**
	 * @var null|Status
	 */
	protected $status = null;

	/**
	 * PHP magic method.
	 * This method is overridden so that named fixture data can be accessed like a normal property.
	 * @param string $name the property name
	 * @return mixed the property value
	 */
	/*public function __get($name)
	{
		if(is_array($this->fixtures) && ($rows=$this->getFixtureManager()->getRows($name))!==false)
			return $rows;
		else
			throw new Exception("Unknown property '$name' for class '".get_class($this)."'.");
	}*/

	/**
	 * PHP magic method.
	 * This method is overridden so that named fixture ActiveRecord instances can be accessed in terms of a method call.
	 * @param string $name method name
	 * @param string $params method parameters
	 * @return mixed the property value
	 */
	/*public function __call($name,$params)
	{
		if(is_array($this->fixtures) && isset($params[0]) && ($record=$this->getFixtureManager()->getRecord($name,$params[0]))!==false)
			return $record;
		else
			throw new Exception("Unknown method '$name' for class '".get_class($this)."'.");
	}*/

	/**
	 * @return CDbFixtureManager the database fixture manager
	 */
	/*public function getFixtureManager()
	{
		return Yii::app()->getComponent('fixture');
	}*/

	/**
	 * @param string $name the fixture name (the key value in {@link fixtures}).
	 * @return array the named fixture data
	 */
	/*public function getFixtureData($name)
	{
		return $this->getFixtureManager()->getRows($name);
	}*/

	/**
	 * @param string $name the fixture name (the key value in {@link fixtures}).
	 * @param string $alias the alias of the fixture data row
	 * @return CActiveRecord the ActiveRecord instance corresponding to the specified alias in the named fixture.
	 * False is returned if there is no such fixture or the record cannot be found.
	 */
	/*public function getFixtureRecord($name,$alias)
	{
		return $this->getFixtureManager()->getRecord($name,$alias);
	}*/

	/**
	 * Sets up the fixture before executing a test method.
	 * If you override this method, make sure the parent implementation is invoked.
	 * Otherwise, the database fixtures will not be managed properly.
	 */
	protected function setUp()
	{
		parent::setUp();
		if(is_array($this->fixtures)) $this->setFixtures($this->fixtures);
		$this->status = Status::getInstance();
	}

	/**
	 * @TODO Переделать в соответствии с моделью Mapper (->saveAdmin()). Трудность в TRUNCATE специфичных запросах.
	 *
	 * @param array $fixtures
	 * @throws Exception
	 */
	private function setFixtures($fixtures)
	{
		foreach($fixtures as $file => $modelName)
		{
			/* @var $model MapperTest */
			$model = new $modelName();

			$data = include(ROOT . '/tests/fixtures/' . $file . '.php');
			if(!is_array($data)) throw new Exception('File `' . $file . '.php` fixture not found!');

			$model->query('TRUNCATE TABLE  `' . $model->tableName() . '`');

			foreach($data as $fieldsAndValues)
			{
				$insert = "INSERT `" . $model->tableName() . "` SET ";
				foreach($fieldsAndValues as $field => $value)
				{
					if($value === null) continue;
					if(is_string($value))
						$insert .= '`' . $field . "`='" . $value . "', ";
					else
						$insert .= '`' . $field . "`=" . $value . ", ";
				}

				$length = strlen($insert);
				$insert = substr($insert, 0 , $length-2);
				$model->query($insert);
			}
		}
	}
}