<?php

class error_Action extends Action {
    public function RegisterEvent() {
        $this->AddEvent('404', 'NotFound');
        $this->AddEvent('403', 'Forbiden');
    }
    public function NotFound() {
        die('not found');
    }
    public function Forbiden() {
        die('forbiden');
    }
}