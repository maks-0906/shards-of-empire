<?php
//Attention!!! $config['var1']['somesubvar']= value overwrite value $config['var1']= value if need use $config['var1'][0] = value

//Non public System variables

//Local Database
/*
$config['system']['db_host'] = 'al0.mysql.ukraine.com.ua';
$config['system']['db_user'] = 'al0_webkpdp7';
$config['system']['db_pass'] = 'flajymuu';
$config['system']['db_name'] = 'al0_webkpdp7';
*/
//Server Database
$config['system']['db_host'] = 'localhost';
$config['system']['db_user'] = 'root';
$config['system']['db_pass'] = '';
$config['system']['db_name'] = 'game_test';

//Templates Skin & Storages
$config['system']['tpl_dir'] = 'Templates';
$config['system']['storage'] = 'Templates/storage';
$config['view']['skin'] = 'default';

//Defaults
$config['default']['action'] = 'index';
$config['default']['forbiden'] = array ('admin','login');
$config['default']['error'] = array('error','404');
$config['default']['language'] = 'ru';

$config['system']['debug'] = true;

//Site_root http://example.com/site/  endslash required!
//$config['site']['url'] = 'http://'.$_SERVER["SERVER_NAME"].'/';

define('SESSION_PERSONAGE_ID', 'current_id_personage');
