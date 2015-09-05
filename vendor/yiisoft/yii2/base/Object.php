<?php

namespace yii\base;

use Yii;

class Object implements Configurable
{
    /**
     * 获取静态方法调用的类名。
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    public function __construct($config = [])
    {
        if (!empty($config)) {
            //构造方法传参
            Yii::configure($this, $config);
        }
        $this->init();
    }

    public function init()
    {

    }

    /**
     * return $this->label,不存在的时候,会调用getLabel()
     * @param $name
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * $this->label=5;会调用setLabel(5)
     * @param $name
     * @param $value
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     *不存在的属性时,false
     * boolean whether the named property is set (not null).
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __call($name, $params)
    {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }

    /**
     * 该属性是\是否可写,默认$checkVars为true,只要property_exists,属性存在即可
     * 如果$checkVars为false,那么则必须要有SetXX函数,则返回true,否则false
     * @param $name
     * @param bool $checkVars
     * @return bool
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * 该属性是\是否可读,默认$checkVars为true,只要property_exists,属性存在即可
     * 如果$checkVars为false,那么则必须要有GetXX函数,则返回true,否则false
     * @param $name
     * @param bool $checkVars
     * @return bool
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }
}