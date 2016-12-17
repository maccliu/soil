<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */


namespace Soil;


/**
 * Container容器对象类
 *
 */
class Container implements \ArrayAccess
{
    /*
     * 条目的类型常量
     */
    const PARAMETER_TYPE = 'parameter';
    const CLASSNAME_TYPE = 'classname';
    const CLOSURE_TYPE = 'closure';
    const INSTANCE_TYPE = 'instance';
    const SHARED_CLASSNAME_TYPE = 'shared_classname';
    const SHARED_CLOSURE_TYPE = 'shared_closure';

    /*
     * 所有登记过keys
     */
    protected $_keys = [];

    /*
     * 各种类型
     */
    private $_parameters = [];  // 参数
    private $_classnames = [];  // 类名
    private $_closures = [];    // 闭包
    private $_instances = [];   // 已生成的实例

    /*
     * 严格模式。如果为true，在检查时，遇到错误会抛出一个异常
     */
    public $strict_mode = true;


    /**
     * 数组方式访问。检查键值是否存在。
     *
     * @param string $key
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
    }


    /**
     * 数组方式访问。获取一个条目。
     *
     * @param string $key
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }


    /**
     * 数组方式访问。设置一个条目。
     *
     * @param string $key
     * @param any $value
     */
    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }


    /**
     * 数组方式访问。删除一个条目。
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        return $this->remove($key);
    }


    /**
     * 检查key是否已经注册过
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->_keys);
    }


    /**
     * 删除指定的条目
     *
     * @param string $key
     */
    public function remove($key)
    {
        unset($this->_keys[$key],
              $this->_parameters[$key],
              $this->_classnames[$key],
              $this->_closures,
              $this->_instances[$key]);
    }


    /**
     * 设置一个参数型的条目
     *
     * @param string $key
     * @param any $value
     *
     * @throws \InvalidArgumentException key必须为字符串且不为空串
     */
    public function set($key, $value)
    {
        if (!$this->checkKey($key)) {
            return false;
        }

        if ($this->exists($key)) {
            $this->remove($key);
        }

        $this->_keys[$key] = self::PARAMETER_TYPE;
        $this->_parameters[$key] = $value;

        return true;
    }


    /**
     * 注册一个对象条目
     *
     * @param string $key
     * @param mixed $class
     *
     * @return bool 成功返回true，失败返回false
     *
     * @throws \InvalidArgumentException
     */
    public function register($key, $class)
    {
        if (!$this->checkKey($key)) {
            return false;
        }

        if ($this->exists($key)) {
            $this->remove($key);
        }

        if (is_string($class)) {
            if (!$this->checkClassname($class)) {
                if ($this->strict_mode) {
                    throw new \InvalidArgumentException('$class不是一个有效的类名字符串');
                    return false;
                } else {
                    return false;
                }
            }
            $this->_keys[$key] = self::CLASSNAME_TYPE;
            $this->_classnames[$key] = $class;
        } elseif (is_object($class)) {
            if ($class instanceof \Closure) {
                $this->_keys[$key] = self::CLOSURE_TYPE;
                $this->_closures[$key] = $class;
            } else {
                $this->_keys[$key] = self::INSTANCE_TYPE;
                $this->_instances[$key] = $class;
            }
        } else {
            if ($this->strict_mode) {
                throw new \InvalidArgumentException('不是一个有效的$class');
                return false;
            } else {
                return false;
            }
        }

        // 注册成功
        return true;
    }


    /**
     * 注册一个共享对象条目
     *
     * @param string $key
     * @param mixed $class
     *
     * @return bool 成功返回true，失败返回false
     *
     * @throws \InvalidArgumentException
     */
    public function registerShared($key, $class)
    {
        if (!$this->checkKey($key)) {
            return false;
        }

        if ($this->exists($key)) {
            $this->remove($key);
        }

        if (is_string($class)) {
            if (!$this->checkClassname($class)) {
                if ($this->strict_mode) {
                    throw new \InvalidArgumentException('$class不是一个有效的类名字符串');
                    return false;
                } else {
                    return false;
                }
            }
            $this->_keys[$key] = self::SHARED_CLASSNAME_TYPE;
            $this->_classnames[$key] = $class;
        } elseif (is_object($class)) {
            if ($class instanceof \Closure) {
                $this->_keys[$key] = self::SHARED_CLOSURE_TYPE;
                $this->_closures[$key] = $class;
            } else {
                $this->_keys[$key] = self::INSTANCE;
                $this->_instances[$key] = $class;
            }
        } else {
            if ($this->strict_mode) {
                throw new \InvalidArgumentException('不是一个有效的$class');
                return false;
            } else {
                return false;
            }
        }

        // 注册成功
        return true;
    }


    /**
     * 获取一个已注册的条目
     *
     * @param string $key
     * @param array $params 仅对以类名方式注册的对象有效
     *
     * @return
     */
    public function get($key, array $params = [])
    {
        if (!$this->exists($key)) {
            return null;
        }

        $obj = null;

        switch ($this->_keys[$key]) {
            case self::PARAMETER_TYPE:
                return $this->_parameters[$key];
                break;

            case self::INSTANCE_TYPE:
                return $this->_instances[$key];
                break;

            case self::CLOSURE_TYPE:
                $obj = call_user_func_array($this->_closures[$key], $params);
                return $obj;
                break;

            case self::SHARED_CLOSURE_TYPE:
                if (isset($this->_instances[$key])) {
                    return $this->_instances[$key];
                }
                $obj = call_user_func_array($this->_closures[$key], $params);
                $this->_instances[$key] = $obj;
                return $obj;
                break;

            case self::CLASSNAME_TYPE:
                $class = new \ReflectionClass($this->_classnames[$key]);
                if (!$class->isInstantiable()) {
                    return null;
                }
                if (empty($params)) {
                    $obj = new $this->_classnames[$key];
                } else {
                    $obj = $class->newInstanceArgs($params);
                }
                return $obj;
                break;

            case self::SHARED_CLASSNAME_TYPE:
                if (isset($this->_instances[$key])) {
                    return $this->_instances[$key];
                }
                $class = new \ReflectionClass($this->_classnames[$key]);
                if (!$class->isInstantiable()) {
                    return null;
                }
                if (empty($params)) {
                    $obj = new $this->_classnames[$key];
                } else {
                    $obj = $class->newInstanceArgs($params);
                }
                $this->_instances[$key] = $obj;
                return $obj;
                break;
        } // switch
    }


    /**
     * 检查key是否合法
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException
     */
    private function checkKey($key)
    {
        if (!is_string($key) || $key === '') {
            if ($this->strict_mode) {
                throw new \InvalidArgumentException('key必须为字符串类型且不得为空串');
            } else {
                return false;
            }
        }
        return true;
    }


    /**
     * 检查给出的类名是否是一个有效的类名字符串
     *
     * 用正则表达式检查只包含：字母，数字，_，\
     *
     * @param string $class
     *
     * @return bool
     */
    private function checkClassname($class)
    {
        $matches = '';

        // 检查开始字符是数字或者结尾字符是\
        $result = preg_match('/^\\d/', $class, $matches);
        if ($result > 0) {
            print_r('1 ');
            print_r($matches);
            return false;
        }

        // 检查存在非单词字符
        $result = preg_match('/[^\w\\\]/', $class, $matches);
        if ($result > 0) {
            print_r('2 ');
            print_r($matches);
            return false;
        }

        // 检查 \数字 这种形式的错误
        $result = preg_match('/(\\\\\d)/', $class, $matches);
        if ($result > 0) {
            print_r('3 ');
            print_r($matches);
            return false;
        }

        // 检查无错，返回true
        return true;
    }
}
