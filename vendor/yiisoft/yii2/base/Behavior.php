<?php

namespace yii\base;

class Behavior extends Object{
    /**
     * @var Component
     */
    public $owner;

    public function events(){
        return [];
    }

    public function attach($owner){
        $this->owner=$owner;
        foreach($this->events() as $event => $handler){
            $owner->on($event,is_string($handler) ? [$this,$handler] : $handler);
        }
    }

    public function detach(){
        if ($this->owner) {
            foreach ($this->events() as $event => $handler) {
                $this->owner->off($event, is_string($handler) ? [$this, $handler] : $handler);
            }
            $this->owner = null;
        }
    }
}