<?php
//定义常量
if(!defined('SLIM_ROOT')){
	define('SLIM_ROOT', str_replace('\\', '/', __DIR__));
}
if(!defined('SLIM_CONFIG')){
	define('SLIM_CONFIG', SLIM_ROOT.'/config/');
}
if(!defined('SLIM_COMMON')){
	define('SLIM_COMMON', SLIM_ROOT.'/common/');
}
if(!defined('SLIM_ROUTE')){
	define('SLIM_ROUTE', SLIM_ROOT.'/routers/');
}
if(!defined('SLIM_CORE')){
	define('SLIM_CORE', SLIM_ROOT.'/core/');
}
define('SLIM_HTTP_HOST', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

//加载主文件
require 'Slim/Slim.php';
require 'Slim/Curl/Curl.php';
$config = require SLIM_CONFIG.'main.global.php';
require SLIM_COMMON.'Common.class.php';
require SLIM_CORE.'Core.class.php';
Common::autoSession();
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
if($config['db_off'] != 'OFF'){
    require 'php-activerecord/ActiveRecord.php';
    ActiveRecord\Config::initialize(function($cfg) use ($config)
    {
        $cfg->set_model_directory('models');
        $cfg->set_connections($config['db_connect']);
    });
}
$app->globalConfig = $config;
$url = parse_url(str_replace($config['url'], '', SLIM_HTTP_HOST));
$path = Common::slimParseUrl($url);
require SLIM_ROUTE.$path.".router.php";
$core = new Core();
$core->setConfig($config);
$core::init();
spl_autoload_register(array('Core', 'autoload'));
$app->run();

