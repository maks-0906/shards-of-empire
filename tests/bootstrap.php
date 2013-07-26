<?php
define('ROOT', '/home/vetalrakitin/shards');
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REMOTE_ADDR'] = '125.126.325.125';
$_SERVER['HTTP_USER_AGENT'] = 'FF';
$_SERVER['SERVER_ADDR'] = '125.126.325.125';

define('TEST_BASE_URL', 'http://game/index-test.php');

date_default_timezone_set('Europe/Helsinki');
function e1() {
    $aParam = func_get_args();
    $aMsg = '['.date('d-M-Y H:i:s',time()).'] ';
    foreach ($aParam as $param) {
        if (is_string($param)) {$aMsg .=$param;} else {$aMsg .= var_export($param, true);};
    }
    file_put_contents('server.log',$aMsg.PHP_EOL, FILE_APPEND);
}

require_once(ROOT . '/tests/core/CDbTestCase.php');
//require_once(ROOT . '/tests/core/CWebTestCase.php');
//require_once(ROOT . '/tests/core/WebTestCase.php');
//
require_once ROOT . '/tests/core/index_bootstrap.php';
//require_once ROOT . '/Library/E1/Entity.php';
//require_once ROOT . '/Library/E1/SimpleEntity.php';
//require_once ROOT . '/tests/core/MapperTest.php';
//
//require_once ROOT . '/Library/E1/Router.php';
//require_once ROOT . '/Library/E1/Action.php';
//require_once ROOT . '/Library/E1/Auth.php';
//require_once ROOT . '/Library/E1/Filter.php';
//require_once ROOT . '/Library/E1/JSONAction.php';
//require_once ROOT . '/Library/E1/JSONViewer.php';
//require_once ROOT . '/Library/kcaptcha/kcaptcha.php';
