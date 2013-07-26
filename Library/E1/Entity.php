<?php

abstract class Entity extends Object{
    protected $_aData=array();
    /**
     *
     * @param array $aData
     * @return type 
     */
    public function __construct($aData = null) {
            if (!$aData) {
                return true; 
            }
            foreach ($aData as $sKey => $val) {
                $this->_aData[strtolower($sKey)] = $val;
            }
    }
    
    public function fetchdata() {
        return $this->_aData;
    }
    public function clean() {
        $this->_aData =  array_filter($this->_aData);
        return $this;
    }
    public final function __call($sName,$aArgs) {
        $sRequest = strtolower($sName);
        $sType=substr($sRequest,0,3);
        $sVar = strtolower(substr($sRequest,3));
        switch ($sType) {
            case 'get': 
                if (isset($this->_aData[$sVar])) {
                    return $this->_aData[$sVar];
                }
                return null;
                break;

            case 'set': 
                if (isset($aArgs[0])) {
                    $this->_aData[$sVar]=$aArgs[0];
                }
                break;
            default :
                e1('Error call Entity ',$sType,' ',  get_called_class());
                die();
        }

    }
} //class


