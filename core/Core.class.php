<?php
class Core{
    
    private static $config;
    private static $imports;
    private static $autoImports;
    private static $bashPath;

    public function setConfig($config){
        self::$config = $config;
    }

    public static function init(){
        if(empty(self::$config))
            die('$config is empty please config.php config');
        self::$imports = self::$config['import'];
        self::$bashPath = self::$config['basePath'];
        self::import();
    }

    public static function import(){
        $autoImports = array();
        if(!is_array(self::$imports)){
            die('$config is $import error');
        }
        foreach(self::$imports as $import){
            $autoImports[] = self::$bashPath . $import . DIRECTORY_SEPARATOR;
        }
        self::$autoImports = $autoImports;
    }

	/**
     * 系统自动加载类库
     * @param string $class 对象类名
     * @return void
     */
    public static function autoload($class){
        foreach(self::$autoImports as $autoImport){
            $classFile = $autoImport . ucfirst($class) . '.class.php';
            if(file_exists($classFile)){
                include $classFile;
            }
        }
    }
}
?>
