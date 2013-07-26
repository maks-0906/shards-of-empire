<?php
class Db
{
    private $_dbLink;
    protected static $_instance;

    private function __construct(){}
    private function __clone(){}

    public static function getInstance() {
        if (null === self::$_instance) {

            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getConnect($sDbHost,$sDbUser,$sDbPass,$sDbName)
    {
        if ($this->_dbLink == NULL) {
            $this->_dbLink = new mysqli($sDbHost,$sDbUser,$sDbPass,$sDbName);
        }
    }

    public function getDbLink()
    {
        return $this->_dbLink;
    }
}