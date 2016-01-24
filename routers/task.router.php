<?php

$app->get('/task',function() use ($app){
	$app->render('admin4/index.html.php'); 
});

$app->post('/task',function() use ($app){
	$app->render('admin4/index.html'); 
});
