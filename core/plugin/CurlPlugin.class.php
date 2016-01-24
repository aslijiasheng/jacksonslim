<?php
class CurlPlugin{

    public static function get($path, $data){
        $curl = new Curl();
        $curl->get(\Slim\Slim::getInstance()->globalConfig['oaUrl'].$path, $data);
        return json_decode($curl->response, TRUE);
    }

    public static function post($path, $data){
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        $curl->post(\Slim\Slim::getInstance()->globalConfig['oaUrl'].$path, $data);
        return json_decode($curl->response->json, TRUE);
    }

    public static function put($path, $data){
        $curl = new Curl();
        $curl->put(\Slim\Slim::getInstance()->globalConfig['oaUrl'].$path, $data);
        return json_decode($curl->response->form, TRUE);
    }
    
}
