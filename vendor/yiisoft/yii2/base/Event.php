<?php

namespace yii\base;

/**
 * 事件类
 * Class Event
 * @package yii\base
 */
class Event extends Object
{

    /**
     * @var string 事件名称
     */
    public $name;

    /**
     * @var object 事件对象
     */
    public $sender;

    /**
     * @var bool 是否终止该事件后续的处理程序
     */
    public $handled = false;

    /**
     * @var mixed 事件参数
     */
    public $data;

    private static $_events = [];

    /**
     * 类的触发
     * @param $class
     * @param $name
     * @param null $event
     */
    public static function trigger($class, $name, $event = null)
    {
        if (empty(self::$_events[$name])) {
            return;
        }
        if ($event === null) {
            $event = new static();
        }
        $event->handled = false;
        $event->name = $name;
        if (is_object($class)) {
            if ($event->sender === null) {
                $event->sender = $class;
            }
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }
        /**
         * 该类的所有父类如果绑定了该事件,则都会被触发
         */
        do {
            if (!empty(self::$_events[$name][$class])) {
                foreach (self::$_events[$name][$class] as $handler) {
                    $event->data = $handler[1];
                    call_user_func($handler[0], $event);
                    if ($event->handled) {
                        return;
                    }
                }
            }
        } while (($class = get_parent_class($class)) !== false);
    }


}