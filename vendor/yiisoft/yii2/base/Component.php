<?php

namespace yii\base;

use Yii;

class Component extends Object
{
    //事件
    private $_events = [];
    //行为
    private $_behaviors;


    public function __get($name){
        $getter = 'get'.$name;
        if(method_exists($this,$getter)){
            return $this->$getter();
        }else{
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if($behavior->canGetProperty($name)){
                    return $behavior->$name;
                }
            }

        }
        if (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }


    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }
        return false;
    }


    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            // set property
            $this->$setter($value);

            return;
        } elseif (strncmp($name, 'on ', 3) === 0) {
            // on event: attach event handler
            $this->on(trim(substr($name, 3)), $value);

            return;
        } elseif (strncmp($name, 'as ', 3) === 0) {
            // as behavior: attach behavior
            $name = trim(substr($name, 3));
            $this->attachBehavior($name, $value instanceof Behavior ? $value : Yii::createObject($value));

            return;
        } else {
            // behavior property
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name)) {
                    $behavior->$name = $value;

                    return;
                }
            }
        }
        if (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     *'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * @return array
     */
    public function behaviors()
    {
        return [];
    }


    public function ensureBehaviors(){
        if($this->_behaviors === null){
            $this->_behaviors=[];
            foreach($this->behaviors() as $name => $behavior){
                $this->attachBehaviorInternal($name,$behavior);
            }
        }
    }

    /**
     * @param $name string 行为名称
     * @param $behavior mixed
     * @throws InvalidConfigException
     */
    private function attachBehaviorInternal($name, $behavior)
    {
        if (!($behavior instanceof Behavior)) {
            $behavior = Yii::createObject($behavior);
        }
        if (is_int($name)) {
            $behavior->attach($this);
            $this->_behaviors[] = $behavior;
        } else {
            if (isset($this->_behaviors[$name])) {
                $this->_behaviors[$name]->detach();
            }
            $behavior->attach($this);
            $this->_behaviors[$name] = $behavior;
        }
        return $behavior;
    }

    public function detachBehavior($name){

        $this->ensureBehaviors();
        if(isset($this->_behaviors[$name])){

            $behavior = $this->_behaviors[$name];
            unset($this->_behaviors[$name]);
            $behavior->detach();
            return $behavior;

        }else{
            return null;
        }
    }




    public function detachBehaviors(){
        $this->ensureBehaviors();
        foreach($this->_behaviors as $name => $behavior){
            $this->detachBehavior($name);
        }
    }

    /**
     * 判断是否有该事件
     * @param $name
     */
    public function hasEventHandlers($name)
    {
        $this->ensureBehaviors();
        return !empty($this->_events[$name]) || Event::hasHandlers($this, $name);
    }


    /**
     * 绑定事件
     * @param $name string 事件名称
     * @param $handle callable 事件处理程序1.php全局函数名2,对象的方法3,类的静态方法4.匿名函数,具体是call_user_func该方法
     * @param null $data mixed 参数
     * @param bool $append 是否添加到尾部,一个事件可以绑定多个处理程序,所以有顺序,默认追加到末尾
     */
    public function on($name, $handle, $data = null, $append = true)
    {
        $this->ensureBehaviors();
        if ($append || empty($this->_events[$name])) {
            //如果追加到末尾,或者没有该事件名
            $this->_events[$name][] = [$handle, $data];
        } else {
            //追加到数组的首部
            array_unshift($this->_events[$name], [$handle, $data]);
        }
    }


    /**
     * 解除绑定
     * @param $name string 事件名称
     * @param null $handler callback 事件处理程序
     * @return bool
     */
    public function off($name, $handler = null)
    {
        $this->ensureBehaviors();
        if (empty($this->_events[$name])) {
            //没有该事件名称,返回false
            return false;
        }
        if ($handler === null) {
            //没有指定解除该事件名称的哪个程序,则全部解除
            unset($this->_events[$name]);
            return true;
        } else {
            $removed = false;
            foreach ($this->_events[$name] as $i => $event) {
                if ($event[0] == $handler) {
                    //解除具体的事件处理程序
                    unset($this->_events[$name][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                $this->_events[$name] = array_values($this->_events[$name]);
            }
            return $removed;
        }
    }


    /**
     * 触发事件(对象的触发)
     * @param $name
     */
    public function trigger($name, Event $event = null)
    {
        $this->ensureBehaviors();
        if (!empty($this->_events[$name])) {
            //存在该事件名称
            if ($event === null) {
                $event = new Event();
            }
            if ($event->sender === null) {
                $event->sender = $this;
            }
            $event->handled = false;
            $event->name = $name;
            foreach ($this->_events[$name] as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                if ($event->handled) {
                    return;
                }
            }

        }
        /**
         * 触发该对象的类的事件,如果该类的每个对象都需要绑定该事件,则把该事件绑定到该类上面,当触发该对象的事件时,则会自动触发该对象的类事件,避免每个类都需要进行绑定
         */
        Event::trigger($this, $name, $event);
    }

}
