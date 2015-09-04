<?php

include('./web/index.php');


class Test extends \yii\base\Object{

    private $_label;

    public function getLabel(){
        return $this->_label;
    }

    public function setLabel($value){
        $this->_label = $value;
    }
}


$test = new Test();
$test->label = 5;
echo $test->label;
var_dump(Test::className());