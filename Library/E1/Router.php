<?php

final class Router extends Singleton {

    private $aParams = array();
    private $sActionEvent;
    private $sActionClass;
    private $oConfig;

    /**
     * Split url to action/event
     */
    private function ParseURL() {
        $sReq = (isset($_SERVER["REQUEST_URI"])) ? parse_url($_SERVER["REQUEST_URI"]) : array('path'=>'');
        $this->aParams = array_filter(explode('/', $sReq['path']));
        $this->sActionClass = array_shift($this->aParams); //First param - action
        $this->sActionEvent = array_shift($this->aParams); //Second param - event
    }
    /**
     * get Url params
     * @return array 
     */
    public function GetParams() {
        return $this->aParams;
    }
    /**
     * 
     * @return string 
     */
    public function GetActionName() {
        //TODO:Как узнать оригинальное имя action если его нету
        return $this->sActionClass ? $this->sActionClass : $this->oConfig->default['action'];
    }
    
    /**
     *
     * @return string
     */
    public function GetEventName() {
        return $this->sActionEvent;
    }
    public static function Location($sLocation,$code=301) {  
        //TODO: время изменить на UTC 
        header("Expires: ".gmdate("D, d M Y H:i:s")." GMT");// дата в прошлом
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT"); 
        header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");// HTTP/1.0
        switch ($code) { 
            case 302 : header("HTTP/1.1 302 Moved Temporarily"); break;
            default  :
            case 301 : header("HTTP/1.1 301 Moved Permanently");   break;
        }      
        header('Location: '.$sLocation);
        die();
    }
    private function DoAction() {
        $sAction = $this->GetActionName().'_Action';
        if (!class_exists($sAction)) {
            $sAction = $this->oConfig->default['error_action'].'_Action';
            if (!class_exists($sAction)) {
                e1('raise config error ',$sAction,' not found');
                die();
            }
        }
        $oAction = new $sAction();
        //oAction instanse of system Action? do init,run, else error!
        if ($oAction instanceof Action) {
            $oAction->ExecEvent($this->sActionEvent);
            return true;
        }    
        e1('Error ',$oAction,' do not Action instance');
        die();    
    }
    /**
     * Internal action (without change url)
     * @param string $sActionSrc
     * @param string $sEvent
     */
    public function Action($sActionSrc, $sEvent=null) {
        $this->sActionClass = $sActionSrc;
        $this->sActionEvent = $sEvent;
        $this->DoAction();
    }

    /**
     * Routing excution
     */
    public function Exec() {
        $this->oConfig = Config::getInstance();
        $this->ParseURL();
        $this->DoAction();
    }       

}

