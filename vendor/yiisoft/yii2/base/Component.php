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


    /**
     * 绑定事件
     * @param $name string 事件名称
     * @param $handle callable 事件处理程序1.php全局函数名2,对象的方法3,类的静态方法4.匿名函数,具体是call_user_func该方法
     * @param null $data mixed 参数
     * @param bool $append 是否添加到尾部,一个事件可以绑定多个处理程序,所以有顺序,默认追加到末尾
     */
    public function on($name,$handle,$data=null,$append=true){
        if($append || empty($this->_events[$name])){
            //如果追加到末尾,或者没有该事件名
            $this->_events[$name][] = [$handle,$data];
        }else{
            //追加到数组的首部
            array_unshift($this->_events[$name],[$handle,$data]);
        }
    }


    /**
     * 触发事件(对象的触发)
     * @param $name
     */
    public function trigger($name,Event $event=null){

        if(!empty($this->_events[$name])){
            //存在该事件名称
            if($event===null){
                $event=new Event();
            }
            if($event->sender===null){
                $event->sender=$this;
            }
            $event->handled=false;
            $event->name=$name;
            foreach ($this->_events[$name] as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                if ($event->handled) {
                    return;
                }
            }

        }
        /**
         * 触发该对象的类的事件
         */
        Event::trigger($this, $name, $event);
    }
    
}
