<?php
$app->get('/',function() use ($app){
    $data = array();
    $data['url'] = \Slim\Slim::getInstance()->globalConfig['url'];
    $data['css'] = \Slim\Slim::getInstance()->globalConfig['css'];
    $data['js'] = \Slim\Slim::getInstance()->globalConfig['js'];
	$app->render('logintheme/index.html.php', $data); 
});

$app->post('/index/loginRed',function() use ($app){
    $indexController = new IndexController($app);
    $indexController->loginRed();
});

