<?php
use yii\base\Event;
use yii\base\Component;
include('./web/index.php');

class Test2 extends Component{
    public function test(){
        echo 'test<br />';
    }

    public static function test_two(){
        echo 'test2<br />';
    }
}
function hello(){
    echo 'hello world!<br />';
}

function name($event){
    echo 'hi '.$event->data.'<br />';
}
function event(){
    echo 'hello event!<br />';
}
function event2(){
    echo 'hello event2!<br />';
}

$test = new Test2();
$test->on('say','hello');
$test->on('say','name','shuru');
$test->on('say',[$test,'test']);
$test->on('say',['Test2','test_two']);
$test->trigger('say');  //会触发实例的事件
echo '<hr />';
$test->off('say','hello');
$test->trigger('say');
echo '<hr />';
Event::on(Test2::className(),'say','event');
$test->trigger('say');  //因为该对象的类,也绑定了该事件,因此会触发类事件,类事件是在Event.php里面
echo '<hr />';
Event::on(Component::className(),'say','event2');   //父类也绑定了该事件,因此,也会被触发
$test->trigger('say');
