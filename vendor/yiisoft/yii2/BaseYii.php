<?php

namespace yii;

defined('YII2_PATH') or define('YII2_PATH', __DIR__);

class BaseYii{
    //YII的命名空间和文件路径的对应关系,因为yii的命名空间和文件路径不一样,所以要有个对应,来通过命名空间,进行找到文件路径,达到自动加载的目的
    public static $classMap = [];
    public static $container;

    public static function autoload($className){
        if(isset(static::$classMap[$className])){
            $classFile = static::$classMap[$className];
        }else{
            return;
        }

        include($classFile);
    }

    public static function configure($object,$properties){
        foreach($properties as $name => $value){
            $object->$name = $value;
        }
        return $object;
    }
}