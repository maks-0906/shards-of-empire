<?php

/**
 * @properties Status $status
 */
abstract class Action extends Object{
    protected $aRegisterEvents = array();
    protected $aAuthLevelEvents = array();
    protected $sDefaultEvent = null;
    protected $aParams = array();
    protected $sTemplateName;
    protected $oRouter;
    protected $oConfig;
    protected $oViewer;
    protected $oAuth;
	protected $status;

    public /*final*/ function __construct() {
        $this->oRouter = Router::getInstance();
        $this->oConfig = Config::getInstance();
        $this->oViewer = Viewer::getInstance();
        $this->oAuth   = Auth::getInstance();
		$this->status = Status::getInstance();
        $this->aParams = $this->oRouter->GetParams();
        $this->sTemplateName = $this->oRouter->GetActionName();
        $this->RegisterEvent();
    }

    abstract protected function RegisterEvent(); //AutoCall method body use AddEvent/AddEventPreg
    /**
     * AddEvent to Routing call
     * @param string $sEvent
     * @param string $sCallback
     * @param integer $iAuthLevel 
     */
    protected final function AddEvent($sEvent, $sCallback, $iAuthLevel=0) {
        //For easy purpose
        $this->AddEventPreg("/^{$sEvent}$/i", $sCallback, $iAuthLevel);
    }
    
    protected final function AddEventPreg($sEventRE, $sCallback, $iAuthLevel=0) {
        // $iAuthLevel 0 - Public content, 1 - Autorized content,..., 255 - Admin content
        if (!method_exists($this, $sCallback)) {			
            e1('Error method of the event not found: ',  get_called_class(),'->',$sCallback);
            die();
	}
        $this->aRegisterEvents[$sEventRE] = $sCallback;
        $this->aAuthLevelEvents[$sEventRE] = $iAuthLevel;

    }

    protected final function SetDefaultEvent($sEvent) {
        $sReEvent = "/^{$sEvent}$/i";
        $this->SetDefaultEventPreg($sReEvent);
    }
    /**
     * Unless we take this event    
     * @param string $sEvent 
     */
    protected final function SetDefaultEventPreg($sReEvent) {
        if (array_key_exists ($sReEvent,$this->aRegisterEvents)) {
            $this->sDefaultEvent = $sReEvent;
            return true;
        }
        e1('Error default Event not Register in routine: ',  get_called_class(),'->',$sReEvent);
        die();
    }
    /**
     * Exec Event routine
     * @param string $sEvent
     */
    public final function ExecEvent($sEvent) {      
        $sCmd ='';
        //Empty event = default event
        if ($sEvent=='' && $this->sDefaultEvent) {
            $this->AuthCheck($this->sDefaultEvent);    
            $sCmd = $this->aRegisterEvents[$this->sDefaultEvent];
            return $this->CallAndDraw($sCmd);
        } 
        foreach ($this->aRegisterEvents as $sEventPattern=>$sCallback) {
            if (preg_match($sEventPattern, $sEvent)) {
                $this->AuthCheck($sEventPattern);
                $sCmd = $sCallback;
                break;
            }
        }
        if (''==$sCmd) {
            return $this->EventNotFound();
            die();
        }
        return $this->CallAndDraw($sCmd);
    }
    /**
     *
     * @param string $sAuthPattern
     * @return semaphor 
     */
    protected function AuthCheck($sAuthPattern) {
        if (!$this->oAuth->CanRun($this->aAuthLevelEvents[$sAuthPattern])) {
                return $this->EventForbiden();
                die();
        }
    }

    /**
     * Rendering template file to output or return his buffer
     * @param string $sDrawName [path/to/template/]TemplateName
     */
    protected final function CallAndDraw($sCmd) {
        $sCmd = '$this->'.$sCmd.'();';
        eval($sCmd); //Warring! See AddEventPreg method_exist check
        //After call Action:Event Draw template
        $this->oViewer->Draw($this->GetTemplateName());
    }

    /**
     * Redeclare for use another NotFound Action
     */
    protected function EventNotFound() {
       list($sAction, $sEvent) = $this->oConfig->default['error'];
       $this->oRouter->Action($sAction, $sEvent);
       die();
    }
    /**
     * Redeclare for use another Forbiden Action
     */
    protected function EventForbiden() {
       list($sAction, $sEvent) = $this->oConfig->default['forbiden'];
       $this->oRouter->Action($sAction, $sEvent);
       die();
    }
     /**
     *
     * @param string $Param
     * @return mixed 
     */
     protected final function GetParam($Param, $default = null) {
        if (array_key_exists($Param, $this->aParams)) {
            return $this->aParams[$Param];
        }
        return $default;
    }

    /**
     *
     * @param string $VarName
     * @return mixed 
     */
    protected final function GetVar($VarName, $default = null) {
        if (isset($_REQUEST[$VarName])) {
            return $_REQUEST[$VarName];
        }
        return $default;
    }
    /**
     * Assign sVar to Viewer template
     * @param string $sVar
     * @param mixed $value 
     */
    protected /*final*/ function Viewer_Assign($sVar, $value) {
       $this->oViewer->Assign($sVar, $value);
    }
    protected final function Viewer_Render() {
        return $this->oViewer->Draw($this->GetTemplateName(), true);
    }
    /**
     * Redeclare for use another template name
     * @param string $sTemplateName [path/to/template/]TemplateName
     */
    protected final function SetTemplateName($sTemplateName) {
        $this->sTemplateName = $sTemplateName;
    }

    /**
     * Return current template name
     * @return string Template Filename
     */
    protected final function GetTemplateName() {
       return $this->sTemplateName;
    }
}