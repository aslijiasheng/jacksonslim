<?php
class CController{

    protected $app;
    
    function __construct($app){
        $this->app = $app;
    }

    public function redirect($path, $data = ''){
        $this->app->redirect($path, $data); 
    }

    public function render($path, $data = ''){
        $this->app->render($path, $data); 
    }

    public function sender($data){
        echo json_encode($data);
        die;
    }
}
