<?php

abstract class AdminAction extends Action{

    public /*final*/ function __construct() {
	$this->oRouter = Router::getInstance();
        $this->oConfig = Config::getInstance();
        $this->oViewer = Viewer::getInstance();
	$this->oViewer->setSkinName('admin');
        $this->oAuth   = Auth::getInstance();
        $this->aParams = $this->oRouter->GetParams();
        $this->sTemplateName = $this->oRouter->GetActionName();
        $this->RegisterEvent();
	
    }
}
