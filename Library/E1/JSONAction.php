<?php

/**
 * Description of JSONAction
 * @property $oViewer JSONViewer
 * @property Auth $oAuth
 * @property Config $oConfig
 * @author al
 */
abstract class JSONAction extends Action {

	/**
	 *
	 */
	public final function __construct() {
        $this->oRouter = Router::getInstance();
        $this->oConfig = Config::getInstance();
        $this->oViewer = JSONViewer::getInstance();
        $this->oAuth   = Auth::getInstance();
		$this->status = Status::getInstance();
        $this->aParams = $this->oRouter->GetParams();
        $this->sTemplateName = $this->GetVar('callback');
		$this->RegisterEvent();
    }
    /**
     * Assign JSON-Array or JSON Key-Value
     * @param array|string $aDataOrKey
     * @param mixed $mValue
     */
    public final function Viewer_Assign($aDataOrKey, $mValue = null) {
        if (is_array($aDataOrKey)) return $this->oViewer->ArrayAssign($aDataOrKey);
        return $this->oViewer->Assign($aDataOrKey,$mValue);
    }

    protected function EventNotFound() {
       $this->Viewer_Assign('error', 'event no found');
       $this->Viewer_Assign('code', '404');
       $this->oViewer->Draw($this->GetTemplateName());
       die();
    }
    
    protected function EventForbiden() {
       $this->Viewer_Assign('error', 'forbiden');
       $this->Viewer_Assign('code', '403');
       $this->oViewer->Draw($this->GetTemplateName());
       die();
    }

    /**
     * @param array $data
     * @return array
     */
    protected function formatJSONResponse(array $data)
    {
        $response = array();
        /* @var $value Mapper */
        foreach($data as $value)
            array_push($response, $value->getProperties());

        return $response;
    }

	/**
	 * Определение существования и получение персонажа для текущего пользователя и требуемого мира.
	 *
	 * @param string $nameParameterIdWorld
	 * @throws StatusErrorException
	 * @return personage_Mapper
	 */
	protected function detectExistsAndGetPersonage($nameParameterIdWorld = 'world_id')
	{
		$personage = personage_Mapper::detectExistsAndGetPersonageForCurrentUser($this->GetVar($nameParameterIdWorld));
		return $personage;
	}
}
