<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Action
 * @property user_Mapper $mEntity
 * @author al
 */
class adminuser_Action extends AdminAction{

	/**
	 * @var user_Mapper
	 */
	private $mEntity;

    public function RegisterEvent() {
        $this->AddEvent('list', 'eList', 1);
        $this->AddEvent('edit', 'eEdit', 1);
        $this->SetDefaultEvent('list');
        $this->mEntity = new adminuser_Mapper();
    }
    public function eList() {
        if ($this->GetVar('action') == 'edit')  $this->eSave();
        if ($this->GetVar('action') == 'delete')  $this->eDelete();
        $pager = array('per_page' => 20, 'route' => '/adminuser/list');
        $pager['current'] = $this->GetVar('page') ? $this->GetVar('page') : 1;
        $pager['count'] = ceil($this->mEntity->GetCount()/$pager['per_page']);
        $this->Viewer_Assign('pager', $pager);

		$formatUsers = array();
		$users = $this->mEntity->GetList(($pager['current']-1)*$pager['per_page'], $pager['per_page']);
		foreach($users as $user)
			array_push($formatUsers, $user->getProperties());
        $this->Viewer_Assign('List', $formatUsers);
        $this->SetTemplateName('user/list');
    }
    public function eEdit() {
        $id = (int) $this->GetParam(0);
        $this->Viewer_Assign('aEntity', $this->mEntity->GetUserById($id)->getProperties());
        $this->SetTemplateName('user/edit');
        
    }
    protected function eSave() {
        $eUser = new adminuser_Mapper();
        $eUser->id = $this->GetVar('id',0); //we check in mapper
        $eUser->email = $this->GetVar('login');
        $eUser->password = $this->GetVar('password');
        $eUser->reg_ip = $this->GetVar('reg_ip');
        $eUser->last_online = $this->GetVar('last_online');
		$eUser->save();
        //$this->mEntity->saveAdmin($eUser);
    }
    protected function eDelete() {
        $this->mEntity->del($this->GetVar('idlist'));
    }
}


