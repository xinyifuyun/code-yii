<?php

include('./web/index.php');

class Test extends \yii\base\Component{
    public function test(){
        echo 'test';
    }

    public static function test2(){
        echo 'test2';
    }
}
function hello(){
    echo 'hello world!';
}

function name($event){
    echo 'hi'.$event->data;
}

$test = new Test();
$test->on('say','hello');
$test->on('say','name','shuru');
$test->on('say',[$test,'test']);
$test->on('say',['Test','test2']);
$test->trigger('say');
