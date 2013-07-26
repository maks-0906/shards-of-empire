<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Action
 *
 * @author al
 */
class adminworld_Action extends AdminAction{
    const PATTERN_SIZE = 10;
    const PATTERN_COUNT = 100;
    private static $STRATEGY = array(
                   //X0                  Y0                    DX  DY
          1 => array(0,                  0,                   +1,  +1)
        , 2 => array(0,                  self::PATTERN_SIZE,  +1,  -1)
        , 3 => array(self::PATTERN_SIZE, 0,                   -1,  +1)
        , 4 => array(self::PATTERN_SIZE, self::PATTERN_SIZE,  -1,  -1)
    );
    private $mEntity;
    public function RegisterEvent() {
        $this->AddEvent('list', 'eList', 1);
        $this->AddEvent('edit', 'eEdit', 1);
        $this->SetDefaultEvent('list');

        $this->mEntity = new adminworld_Mapper();
    }
    public function eList() {
        if ($this->GetVar('action') == 'edit')  $this->eSave();
        if ($this->GetVar('action') == 'delete')  $this->eDelete();
        $pager = array('per_page' => 20, 'route' => '/adminworld/list');
        $pager['current'] = $this->GetVar('page') ? $this->GetVar('page') : 1;
        $pager['count'] = ceil($this->mEntity->GetCount()/$pager['per_page']);
        $this->Viewer_Assign('pager', $pager);

		$formatWorlds = array();
		$worlds = $this->mEntity->GetList(($pager['current']-1)*$pager['per_page'], $pager['per_page']);
		foreach($worlds as $world)
			array_push($formatWorlds, $world->getProperties());
        $this->Viewer_Assign('List', $formatWorlds);
        $this->SetTemplateName('world/list');
    }
    protected function eEdit() {
        $id = (int) $this->GetParam(0);
        $this->Viewer_Assign('aEntity', $this->mEntity->GetEntityById($id)->getProperties());
        $this->Viewer_Assign('PATTERN_SIZE', self::PATTERN_SIZE);

		$formatLangList = array();
        $languages = lang_Mapper::model()->GetLangList();
		foreach($languages as $lang)
			array_push($formatLangList, $lang->getProperties());

        $this->Viewer_Assign('langlist', $formatLangList);

		$formatPatterns = array();
		$patterns = pattern_Mapper::model()->findPatternList();
		foreach($patterns as $pattern)
			array_push($formatPatterns, $pattern->getProperties());
        $this->Viewer_Assign('patternlist', $formatPatterns);
        $this->SetTemplateName('world/edit');
        
    }

	/**
	 *
	 */
	protected function eSave() {
        $map = new adminworld_Mapper();

        $map->id = $this->GetVar('id',0); //we check in mapper
        $map->name = $this->GetVar('name');
        $map->lang_id = $this->GetVar('lang_id');
        $map->max_users = $this->GetVar('max_users');
        $map->server = $this->GetVar('server');
        $map->status = (int) $this->GetVar('status');
        $map->map_template = $this->GetVar('map_template');
        //$this->mEntity->saveAdmin($eData);
		$map->save();
        //generate?
        /*for ($y0 = 0; $y0 < self::PATTERN_COUNT * self::PATTERN_SIZE; $y0 = $y0 + self::PATTERN_SIZE) {
            for ($x0 = 0; $x0 < self::PATTERN_COUNT * self::PATTERN_SIZE; $x0 = $x0 + self::PATTERN_SIZE) {
                //Gogo(X0,Y0)
            }
        }*/

        //self::PATTERN_COUNT
    }

    protected function eDelete()
	{
        $this->mEntity->del($this->GetVar('idlist'));
    }
}

/**
 * Класс генератор,
 */
class PatternGenerator
{
	/**
	 * @var
	 */
	private $pattern;

	/**
	 * @param $pattern
	 */
	public function __construct($pattern) {
        $this->$pattern = $pattern;
        //rnd Start POINT XY or YX
        //rnd Strategy [1-4]
        mt_srand(); //seed
        $order = mt_rand(0, 1);
        $strategy = mt_rand(1, 4);
    }

	/**
	 *
	 */
	private function seedXY() {

    }

	/**
	 *
	 */
	private function seedYX() {

    }
}


