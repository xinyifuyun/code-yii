<?php
use yii\base\Component;

include('./web/index.php');

class Test3 extends Component
{

    public function behaviors()
    {
        return [
            'behaviorName' => [
                'class' => 'BehaviorClass',
                'property1' => 'value1',
                'property2' => 'value2',
            ]
        ];
    }
}

class BehaviorClass extends \yii\base\Behavior{
    public $property1;
    public $property2;
}

$test = new Test3();
$test->ensureBehaviors();
echo '<pre>';
print_r($test);
var_dump($test->hello);

