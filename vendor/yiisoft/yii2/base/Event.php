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
     * 类的绑定事件,比component多了一个参数,class,代表是哪个类
     * @param $class
     * @param $name
     * @param $handler
     * @param null $data
     * @param bool $append
     */
    public static function on($class, $name, $handler, $data = null, $append = true)
    {

        $class = ltrim($class, '\\');
        if ($append || empty(self::$_events[$name][$class])) {
            self::$_events[$name][$class][] = [$handler, $data];
        } else {
            array_unshift(self::$_events[$name][$class], [$handler, $data]);
        }
    }

    /**
     * 移除事件
     * @param $class
     * @param $name
     * @param null $handler
     * @return bool
     */
    public static function off($class, $name, $handler = null)
    {
        $class = ltrim($class, '\\');

        if (empty(self::$_events[$name][$class])) {
            return false;
        }
        if ($handler === null) {
            unset(self::$_events[$name][$class]);
            return true;
        } else {
            $removed = false;
            foreach (self::$_events[$name][$class] as $i => $event) {
                if ($event[0] === $handler) {
                    unset(self::$_events[$name][$class][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                self::$_events[$name][$class] = array_values(self::$_events[$name][$class]);
            }
            return $removed;
        }
    }

    /**
     * 是否有该事件
     * @param $class
     * @param $name
     * @return bool
     */
    public static function hasHandlers($class, $name)
    {
        if (empty(self::$_events[$name])) {
            return false;
        }
        if (is_object($class)) {
            $class = get_class($class);
        } else {
            $class = ltrim($class, '\\');
        }
        do {
            if (!empty(self::$_events[$name][$class])) {
                return true;
            }
        } while (($class = get_parent_class($class)) !== false);

        return false;
    }


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