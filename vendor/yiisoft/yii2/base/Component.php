<?php

namespace yii\base;

use Yii;

class Component extends Object{
    //事件
    private $_events = [];
    //行为
    private $_behaviors;


    public function behaviors()
    {
        return [];
    }

    
}
