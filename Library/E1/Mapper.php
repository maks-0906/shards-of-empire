<?php
/**
 * Mapper prototype  Now only mysqli mapper
 */

/**
 * @property mysqli $__oDbLink
 */
abstract class Mapper extends Object
{

    /**
     * Объект соединения с БД.
     * @var mysqli
     */
    static private $__oDbLink = null;

    //TODO: Проверить опасность блокировок при использовании транзакций с одним коннектом.
    /**
     * Результат запроса к БД.
     * @var array
     */
    protected $__DBResult = array();

    /**
     * Текущие свойства (поля) модели, как перед так и после запроса к БД.
     * @var array
     */
    protected $__aData = array();

    /**
     * Ошибки, произошедшие во время запросов к БД.
     * @var array
     */
    public $errors = array();

    /**
     * Объект содержащий информацию о текущих статусах в системе для передачи клиенту.
     * @var Status
     */
    protected $oStatus;

    /**
     * return string table name for insert/update
     */
    abstract protected function tableName();

    /**
     * Получение экземпляра сущности.
     * Factory метод.
     *
     * @param string $className
     * @return Mapper
     */
    public static function model($className = __CLASS__)
    {
    }

    /**
     * Правила валидации свойств (полей) таблицы.
     *
     * @return array
     */
    protected function rules()
    {
        return array();
    }

    /**
     * Первичный ключ. По умолчанию является `id`.
     * @return string
     */
    public function pk()
    {
        return 'id';
    }

    /**
     * Инициализация нового соединения с БД.
     */
    protected static final function __NewConnect()
    {
        //new connection
        $oConfig = Config::getInstance();
        $sDbHost = $oConfig->system['db_host'];
        $sDbUser = $oConfig->system['db_user'];
        $sDbPass = $oConfig->system['db_pass'];
        $sDbName = $oConfig->system['db_name'];
        self::$__oDbLink = new mysqli($sDbHost, $sDbUser, $sDbPass, $sDbName); //, 4040);
        if (self::$__oDbLink->connect_error) {
            e1('Connect Error (', self::$__oDbLink->connect_errno, ') ', self::$__oDbLink->connect_error);
            die();
        }
        if (!self::$__oDbLink->set_charset("utf8")) {
            e1("Error loading character set utf8");
            die();
        }
    }

    /**
     * Инициализация модели к БД.
     */
    public function __construct()
    {
        if (self::$__oDbLink === null) {
            self::__NewConnect();
        }
        $this->oStatus = Status::getInstance();
        $this->init();
    }

    public function init()
    {
    }

    /**
     * Получение значений свойств (полей) модели.
     * @param string $name
     * @return null|mixed
     */
    public final function __get($name)
    {
        if (isset($this->__aData[$name]))
            return $this->__aData[$name];
        elseif ($name == 'properties')
            return $this->__aData; elseif ($name == 'result')
            return $this->__DBResult; else
            return null;
    }

    /**
     * API для заполнения модели свойствами.
     * :WARNING: Свойства участвуют в формировании запроса к БД, поэтому являются реальными.
     * При добавлении не существующего, абстрактного свойства (поля) запрос будет исполнен с обшибкой.
     * @param string $name
     * @param string $value
     * @throws DBException
     */
    public final function __set($name, $value)
    {
        // Пакетное наполнение свойствами
        if ($name == 'properties') {
            if (!is_array($value)) throw new DBException('During batch filling parameter must be an array');
            $this->__aData = $value;
        } else
            $this->__aData[$name] = $value;
    }

    /**
     * Включение транзакции.
     */
    protected static final function begin()
    {
        self::$__oDbLink->autocommit(false);
    }

    /**
     * Совершение транзакции и установка режима работы транзакции в положение по умолчанию (AUTOCOMMIT=1)
     * @return bool true on success or false on failure.
     */
    protected static final function commit()
    {
        $resultTransaction = self::$__oDbLink->commit();
        self::$__oDbLink->autocommit(true);

        return $resultTransaction;
    }

    /**
     * Откат текущей транзакции и установка режима работы транзакции в положение по умолчанию (AUTOCOMMIT=1)
     * @return bool true on success or false on failure.
     */
    protected static final function rollback()
    {
        self::$__oDbLink->rollback();
        return self::$__oDbLink->autocommit(true);
    }

    /**
     * Получение последнего ключа после вставки записи.
     * @return mixed
     */
    protected static final function get_insert_id()
    {
        return self::$__oDbLink->insert_id;
    }

    /**
     * Исполнение мультизапроса.
     * @TODO :WARNING: Недоработано.
     * @param string $sql
     */
    private static final function m_exec($sql)
    {
        $result = self::$__oDbLink->multi_query($sql);
        if ($result !== FALSE) {
            while (self::$__oDbLink->more_results()) {
                self::$__oDbLink->use_result();
                self::$__oDbLink->next_result();
            }
            //last result return
            $result = self::$__oDbLink->store_result();
        }
    }

    /**
     * Получение количество последних строк.
     *
     * @param $result
     * @return mixed
     */
    public function getAffectedRows($result)
    {
        return $result->__DBResult["affected_rows"];
    }

    /**
     * Непосредственное исполнение запроса к БД.
     *
     * @param string $sql
     * @param Mapper $obj Модель получающая ответы на свои запросы.
     * @param bool $returnFullObject Флаг, указывающий о не изменении свойств текущего объекта, например при UPDATE/REPLACE
     * @return Mapper
     * @throws DBException
     */
    private static final function exec($sql, $obj, $returnFullObject = false)
    {
        try {
            //fb($sql, 'sql', FirePHP::ERROR);
            /* @var $__oDbLink mysqli */
            $result = self::$__oDbLink->query($sql, MYSQLI_STORE_RESULT);
            if ($result === FALSE) { //error
                throw new DBException(
                    'Query Error on SQL: ' . $sql . '   number error: ' . self::$__oDbLink->errno
                );
            } elseif ($result === TRUE) {
                //unbuffered transaction
                if ($returnFullObject === false)
                    if (isset(self::$__oDbLink->insert_id) && !isset($obj->__aData['id']))
                        $obj->__aData = array('id' => self::$__oDbLink->insert_id);

                $obj->__DBResult = array('affected_rows' => self::$__oDbLink->affected_rows);
            } else {
                //buffered transaction
                if (method_exists('mysqli_result', 'fetch_all')) # Compatibility layer with PHP < 5.3
                    $obj->__DBResult = $result->fetch_all(MYSQLI_ASSOC);
                else
                    for ($res = array(); $tmp = $result->fetch_assoc();) $obj->__DBResult[] = $tmp;
                /*$obj->__DBResult = $result->fetch_all(MYSQLI_ASSOC);
                $obj->__DBResult = $result->fetch_assoc();*/
            }
        } catch (DBException $e) {
            e1('Query Error on ', $sql, self::$__oDbLink->errno, self::$__oDbLink->error);
            // Обработка ошибок
            self::formatErrors(self::$__oDbLink, $obj);
        }

        return $obj;
    }

    /**
     * Форматирование ошибок.
     *
     * @param mysqli $db
     * @param Mapper $self
     */
    protected static function formatErrors(mysqli $db, Mapper $self)
    {
        switch ($db->errno) {
            // TODO: WARNING: Возможна ошибка, так как коды в разных версиях меняются!!!
            case 1062:
                $self->errors['unique'] = $db->error;
                break;

            default:
                $self->errors['main'] = $db->error;
                break;
        }
    }

    /**
     * Получение ошибок запроса.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Проверка существования типа ошибки, если таковая произощла во время запроса к БД.
     * @param string $typeError
     * @return bool
     */
    public function hasError($typeError)
    {
        return isset($this->errors[$typeError]);
    }

    /**
     * Утверждение наличия ошибок  после запроса.
     * @return bool
     */
    public function isError()
    {
        return count($this->errors) > 0;
    }

    /**
     * @return Mapper
     */
    protected final function valid()
    {
        $aData = array_shift($this->__DBResult);
        if ($aData)
            foreach ($aData as $sKey => $val) {
                $this->__aData[strtolower($sKey)] = $val; //! lowercase
            }

        return $this;
    }

    /**
     * Подготовка и исполнение запроса.
     * @return Mapper
     */
    public final function query( /*$sql[,$Param1[,$Param2,...]]*/)
    {
        $aParams = func_get_args();
        $sql = array_shift($aParams);
        if (!empty ($aParams)) {
            ///escape
            foreach ($aParams as $key => $sParam)
                $aParams[$key] = self::$__oDbLink->real_escape_string($sParam); // If $sParam array???
        }

        $sql = vsprintf($sql, $aParams);

        return self::exec($sql, $this);
    }

    /**
     * Обновление данных модели.
     *
     * @param Mapper $self
     * @return Mapper
     * @throws DBException
     */
    protected static final function __update($self)
    {
        $aData = $self->__aData;
        $pk = $self->pk();
        if ($self->$pk == null) throw new DBException('PK for update not exists', $self);

        /*
         * if (!array_key_exists('id', $aData) && !array_key_exists('pk', $aData)) {
            e1('error update ',$self->tableName(),' id not found in ',$aData);
            die();
        }*/

        $sUpdate = 'UPDATE ' . $self->tableName() . ' SET ';
        $sWhere = ' WHERE ' . $pk . ' = ' . self::$__oDbLink->real_escape_string($aData[$pk]);
        unset($aData[$pk]);

        $aSet = array();
        foreach ($aData as $sSetField => $sSetValue) {
            array_push(
                $aSet,
                '`' . self::$__oDbLink->real_escape_string($sSetField) . '`="'
                    . self::$__oDbLink->real_escape_string($sSetValue) . '"'
            );
        }
        $sql = $sUpdate . implode(',', $aSet) . $sWhere;
        return self::exec($sql, $self, true);
    }

    /**
     * @param Mapper $self
     * @return Mapper
     */
    protected static final function __insert($self)
    {
        $sInsert = 'INSERT INTO ' . $self->tableName();
        $aFields = array();
        $aValues = array();
        foreach ($self->__aData as $field => $value) {
            array_push($aFields, '`' . self::$__oDbLink->real_escape_string($field) . '`');
            array_push($aValues, '"' . self::$__oDbLink->real_escape_string($value) . '"');
        }
        $sql = $sInsert . ' (' . implode(',', $aFields) . ') values (' . implode(',', $aValues) . ')';
        return self::exec($sql, $self);
    }

    /**
     * @param Mapper $self
     * @return Mapper
     */
    protected static final function __delete($self)
    {
        $aData = $self->__aData;
        if (!array_key_exists($sFilterField, $aData)) {
            e1('Error delete from  ', $self->tableName(), ' with filter ', $sFilterField, ' in ', $aData);
            die();
        }
        $sUpdate = 'DELETE FROM ' . $self->tableName();
        $sWhere = ' WHERE ' . $sFilterField . '=' . self::$__oDbLink->real_escape_string($aData[$sFilterField]);
        return self::exec($sUpdate . $sWhere, $self);
    }

    /**
     * Метод пост обработки данных после поиска данных.
     * @return bool
     */
    public function afterFind()
    {
        return true;
    }

    /**
     *
     */
    public final function GetById()
    { /* return self::query() */
    }

    /**
     * Очистка таблицы.
     *
     * @return Mapper
     */
    public function clearTable()
    {
        $sql = "TRUNCATE TABLE `%s`";
        return $this->query($sql, $this->tableName());
    }

    /**
     * Удаление модели (записи).
     * @return Mapper
     */
    public final function delete()
    {
        return self::__delete($this);
    }

    /**
     * Сохранение изменений.
     *
     * @return Mapper
     */
    public final function save()
    {
        $pk = $this->pk();
        if ($this->$pk != null) {
            $this->__update($this);
        } else {
            $this->__insert($this);
        }

        return $this;
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isEmptyResult()
    {
        return count($this->__DBResult) == 0;
    }

    /**
     * Зпись лога профилирования.
     */
    public function show_profiles()
    {
        $resource = $this->query("show profiles;");
        foreach ($this->__DBResult as $result) {
            $msg = '';
            foreach ($result as $key => $val)
                $msg = $key . ': ' . $val . ' ';

            e1($msg . '\t');
        }
    }

    /**
     * Поиск единичной записи
     * @return Mapper|null
     */
    public function find()
    {
        $aParams = func_get_args();
        $sql = array_shift($aParams);
        if (!empty ($aParams)) {
            ///escape
            foreach ($aParams as $key => $sParam)
                $aParams[$key] = self::$__oDbLink->real_escape_string($sParam); // If $sParam array???
        }

        $sql = vsprintf($sql, $aParams);
        $self = self::exec($sql, $this);
        if (is_array($self->__DBResult) && count($self->__DBResult) > 0) {
            $this->__aData = $self->__DBResult[0];
            $this->afterFind();
            return $this;
        } else
            return null;
    }

    /**
     * Поиск всех записей по sql переданному запросу.
     * @return Mapper[]|array
     */
    public function findAll()
    {
        /*$this->query("set profiling=1;");
        register_shutdown_function(array($this, 'show_profiles'));*/

        $aParams = func_get_args();
        $sql = array_shift($aParams);
        if (!empty ($aParams)) {
            ///escape
            foreach ($aParams as $key => $sParam) {
                $aParams[$key] = self::$__oDbLink->real_escape_string($sParam); // If $sParam array???
            }
        }
        $sql = vsprintf($sql, $aParams);

        $self = self::exec($sql, $this);
        if (is_array($self->__DBResult) && count($self->__DBResult) > 0) {
            $currentClassName = get_class($this);
            $collection = array();
            foreach ($self->__DBResult as $row) {
                /* @var $rowObject Mapper */
                $rowObject = new $currentClassName();
                $rowObject->properties = $row;
                $rowObject->afterFind();
                array_push($collection, $rowObject);
            }

            return $collection;
        } else
            return array();
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->__aData;
    }
}