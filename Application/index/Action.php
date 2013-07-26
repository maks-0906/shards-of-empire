<?php

class index_Action extends Action {

	/**
	 *
	 */
    public function RegisterEvent() {
        $this->AddEvent('main', 'main');
        $this->SetDefaultEvent('main');
    }

	/**
	 *
	 */
    public function main() {
        //$this->Viewer_Assign('JSON_Server', $this->oConfig->site['url']);

		/* @var $view Viewer */
		$view = Viewer::getInstance();
		$view->setSkinName($this->oConfig->view['skin']);
		$view->Assign(
			'BasePath',
			$this->oConfig->site['url'] . $this->oConfig->system['tpl_dir']
				. '/skin/' . $this->oConfig->view['skin'] . '/'
		);
    }
}