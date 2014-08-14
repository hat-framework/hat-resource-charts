<?php

require_once __DIR__.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."charts.php";
class chartsResource extends \classes\Interfaces\resource{
        
   /**
    * @uses Contém a instância do banco de dados
    */
    private static $instance = NULL;
    public static function getInstanceOf(){
        $class_name = __CLASS__;
        $obj = new $class_name;
        return $obj->LoadApi();
    }
    
    //private $api = "gcharts";
    private $api = "angularcharts";
    public function LoadApi() {
        $this->dir = dirname(__FILE__);
        $file = "/api/$this->api/$this->api.php";
        $this->LoadResourceFile($file);
        return new $this->api();
    }
    
    
    
    
}
