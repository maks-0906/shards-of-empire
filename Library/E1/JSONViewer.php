<?php

final class JSONViewer extends Singleton{
    private $json = array();

    protected function __construct() {
        $data = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
        $is_xhr = strtolower($data) == 'xmlhttprequest';
        header('Content-type: application/' . ($is_xhr ? 'json' : 'x-javascript').'; charset=utf-8');
    }
    public function Assign($sVar, $value) {
        $this->json[$sVar] = $value;
    }
    public function ArrayAssign($aData) {
        $this->json = array_merge($this->json, $aData);
    }
    public function Draw($jsonp_callback) {
        //TODO: need answer to file?
        $json = json_encode($this->json);
        echo $jsonp_callback ? "$jsonp_callback($json)" : $json;
        //die;
    }
}
