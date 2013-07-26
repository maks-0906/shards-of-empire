<?php

final class Viewer extends Singleton {
    private $sLang = null;
    private $oConfig;
    private $oAuth;
    private $oTpl;
    private $oRouter;
    private $sSkinName = 'default';

    protected function __construct() {
        $this->oTpl = new RainTPL();
        $this->oConfig = Config::getInstance();
	$this->setSkinName($this->oConfig->view['skin']);

        $this->oAuth = Auth::getInstance();
        $this->oRouter = Router::getInstance();
	
    }
    
    public function setSkinName($sSkinName) {
    	$this->sSkinName = $sSkinName;
    }

    /**
     *
     * @param string $sVar
     * @param mixed $value 
     */
    public function Assign($sVar, $value) {
        $this->oTpl->assign($sVar, $value);
    }
    /**
     *
     * @param array $aMessages
     * @return boolean 
     */
    private function LoadLangFile($aMessages) {
        if (is_array($aMessages)) {
            $this->Assign('aLang', $aMessages);
            return true;
        } 
        $this->Assign('aLang', array());
        return false;
    }

    /**
     * Rendering template file to output or return his buffer
     * @param string $sDrawName [path/to/template/]TemplateName
     * @param boolean $bToBuffer true=return a string, false=echo the template
     * @return string or null 
     */
    public function Draw($sDrawName, $bRenderMode=false) {
        //Path params
        //$this->oTpl->configure('base_url', $this->oConfig->site['url']);
        $this->oTpl->configure('base_url', $_SERVER["SERVER_NAME"]);
        $this->oTpl->configure('path_replace', false); //Решем пути через тег html-head-base
        $sSkinDir = $this->oConfig->system['tpl_dir'] . '/'. 'skin/' . $this->sSkinName . '/';
        $this->oTpl->configure('tpl_dir', $sSkinDir);
        //General assign
        $this->Assign('BasePath', $this->oConfig->site['url'] . $sSkinDir);
        $this->Assign('sAction', $this->oRouter->GetActionName());
        $this->Assign('sEvent', $this->oRouter->GetEventName());
        //Cache
        //TODO:Узнать больше про управление кешем
        /*
             // if there's a valid cache the method will return it
            if( $cache = $tpl->cache( 'test', $expire_time = 600, $cache_id=null ) )
                 echo $cache;
            else{

                ... query, operation, assign ....

                //draw template...
                $tpl->draw( 'test' );
            }
         */
        $this->oTpl->configure('cache_dir', $this->oConfig->system['tpl_dir']. '/cache/');

        //Lang
       // $this->sLang = $this->oAuth->GetStoreLang() ? $this->oAuth->GetStoreLang() : $this->oConfig->default['language'];
        $this->sLang = Auth::GetStoreLang() ? Auth::GetStoreLang() : $this->oConfig->default['language'];
        $this->Assign('sLang', $this->sLang);
        $sLangFilePath = $this->oConfig->system['tpl_dir'] . '/language/' . basename($this->sLang) .'.php';
        if (file_exists($sLangFilePath)) {
            $this->LoadLangFile(include($sLangFilePath)); // aLang assign
        }

        // General Assign
        $this->Assign('aSite', $this->oConfig->site);
        //TODO:Обратить внимание на соответствие имени класса имени шаблона Index->index
        return $this->oTpl->draw(strtolower($sDrawName), $bRenderMode);
    }
}
