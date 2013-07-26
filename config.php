<?php
//Attention!!! $config['var1']['somesubvar']= value overwrite value $config['var1']= value if need use $config['var1'][0] = value

//Non public System variables

//Local Database
/*$config['system']['db_host'] = '46.249.52.227';
$config['system']['db_user'] = 'root';
$config['system']['db_pass'] = '05emiT03pmatS';
$config['system']['db_name'] = 'shards';
 */
//Server Database

$config['system']['db_host'] = '127.0.0.1';
$config['system']['db_user'] = 'root';
$config['system']['db_pass'] = 'root';
$config['system']['db_name'] = 'shards';


// Settings for mailbox sender
$config['system']['mail_host'] = 'smtp.yandex.ru';
$config['system']['mail_username'] = 'test_email@kiberland.com';
$config['system']['mail_password'] = '123456';
$config['system']['smtp_port'] = '587';

define('SESSION_PERSONAGE_ID', 'current_id_personage');
define('SESSION_USER_ID', 'current_id_user');

$config['system']['site_name'] = 'Shards';

//Templates Skin & Storages
$config['system']['tpl_dir'] = 'Templates';
$config['system']['storage'] = 'Templates/storage';
$config['view']['skin'] = 'shards';

//Defaults
$config['default']['action'] = 'index';
$config['default']['forbiden'] = array ('admin','login');
$config['default']['error'] = array('error','404');
$config['default']['language'] = 'ru';

$config['system']['debug'] = true;

//Site_root http://example.com/site/  endslash required!
$config['site']['url'] = 'http://'.$_SERVER["SERVER_NAME"].'/';
$config['site']['root_dir'] = $_SERVER["DOCUMENT_ROOT"];
$config['site']['library_dir'] = $config['site']['root_dir'] . '/Library';
$config['site']['components_dir'] = $config['site']['root_dir'] . '/Library/components';

//Конфигурации связанные с Cron
$config['cron']['not_attend_game']['subject'] = "Хватит пялиться играть иди";
$config['cron']['not_attend_game']['user_last_online'] = 30; //Количество дней отсутствия пользователя

define('DEBUG_DB', true);
define('PROFILING_DB', true);
define('DEBUG', true);