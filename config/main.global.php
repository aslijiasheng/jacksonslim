<?php
//自定义参数
return array(
	'basePath'=>dirname(__DIR__).DIRECTORY_SEPARATOR,
    'url' => 'http://localhost/truecolorpro/slim/index.php/',
    'css' => 'http://localhost/truecolorpro/slim/theme/',
    'js' => 'http://localhost/truecolorpro/slim/theme/',
    'db_off' => 'OFF',
    'db_debug' => FALSE,//调试DBSQL
	'db_connect' => array('development' =>'mysql://root:871027@127.0.0.1/blog'),//数据库连接参数
	// autoloading model and component classes
	'import'=>array(
		'core/controller',
		'core/plugin',
		'core/module',
		'core/service',
	),
    'oaUrl' => 'http://192.168.66.88/',//OA接口前缀
    'configUrl' => array(
        'OA' => array(
           'LoginOA' => '',//登陆
           'Menu' => '',//拉取菜单
           'Transerfer' => '',//转交
           'Refuse' => '',//回退
           'FormData' => '',//表单数据
           'SaveDraft' => '',//保存草稿
           'FlowData' => '',//拉取流程数据
        )
    ),
    'logPath' => '/Library/WebServer/Documents/truecolorpro/slim/log/',//OA接口前缀
);
?>
