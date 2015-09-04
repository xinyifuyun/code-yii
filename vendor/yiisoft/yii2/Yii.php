<?php

require(__DIR__.'/BaseYii.php');

class Yii extends \yii\BaseYii{

}
//自动加载,BaseYii的autoload函数
spl_autoload_register(['Yii','autoload'],true,true);

Yii::$classMap = require(__DIR__.'/classes.php');