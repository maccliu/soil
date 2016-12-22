<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

use \ArrayAccess;
use \Interop\Container\ContainerInterface;

use Soil\Container\ContainerException;
use Soil\Container\NotFoundException;

/**
 * Container
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Container implements ArrayAccess, ContainerInterface
{
    /*
     * 条目的类型常量
     */
    const PARAMETER_TYPE = 'parameter';     // 参数
    const CLASSNAME_TYPE = 'classname';     // 类名字符串
    const CLOSURE_TYPE = 'closure';         // 闭包
    const INSTANCE_TYPE = 'instance';       // 服务实例

    /*
     * 所有登记过keys
     */
    private $_keys = [];

    /*
     * 各种类型
     */
    private $_parameters = [];  // 参数
    private $_classnames = [];  // 类名
    private $_closures = [];    // 闭包
    private $_instances = [];   // 已生成的实例


    public function __construct()
    {
    }


    /**
     * 实现ArrayAccess。检查键值是否存在。
     *
     * @param string $id
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }


    /**
     * 实现ArrayAccess。获取一个条目。
     *
     * @param string $id
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }


    /**
     * 实现ArrayAccess。设置一个参数条目。
     *
     * @param string $id
     * @param mixed $value
     */
    public function offsetSet($id, $value)
    {
        return $this->set($id,
                          $value);
    }


    /**
     * 实现ArrayAccess。删除一个条目。
     *
     * @param string $id
     */
    public function offsetUnset($id)
    {
        return $this->remove($id);
    }


    /**
     * 返回所有的keys
     *
     * @return array
     */
    public function keys()
    {
        return $this->_keys;
    }


    /**
     * 是否已经注册此id。
     *
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id,
                                $this->_keys);
    }


    /**
     * 返回一个共享的服务实例或者一个参数配置条目。
     *
     * 如果需要返回新的服务实例，需要用make方法来完成。
     *
     * @param string $id
     *
     * @return mixed
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException("Not found the specified id '{$id}' in the container");
        }

        $obj = null;

        switch ($this->_keys[$id]) {
            case self::PARAMETER_TYPE:
                return $this->_parameters[$id];

            case self::INSTANCE_TYPE:
                return $this->_instances[$id];

            case self::CLOSURE_TYPE:
                //如果服务实例以前已经创建，直接返回创建好的服务实例
                if (isset($this->_instances[$id])) {
                    return $this->_instances[$id];
                }
                // 如果是第一次运行，则创建新服务实例，并保存备用
                $serviceInstance = call_user_func_array($this->_closures[$id],
                                                        $params);
                $this->_instances[$id] = $serviceInstance;
                return $serviceInstance;

            case self::CLASSNAME_TYPE:
                //如果服务实例以前已经创建，直接返回创建好的服务实例
                if (isset($this->_instances[$id])) {
                    return $this->_instances[$id];
                }
                // 如果是第一次运行，则创建新服务实例，并保存备用
                $class = new \ReflectionClass($this->_classnames[$id]);
                if (!$class->isInstantiable()) {
                    return null;
                }
                $serviceInstance = new $this->_classnames[$id];
                $this->_instances[$id] = $serviceInstance;
                return $serviceInstance;
        } // switch
    }


    /**
     * 设置一个参数型的条目。
     *
     * @param string $id
     * @param mixed $value
     *
     * @throws ContainerException key必须为非空字符串
     */
    public function set($id, $value)
    {
        $this->checkID($id);

        if ($this->has($id)) {
            $this->remove($id);
        }

        $this->_keys[$id] = self::PARAMETER_TYPE;
        $this->_parameters[$id] = $value;

        return true;
    }


    /**
     * 删除指定的条目
     *
     * @param string $id
     */
    public function remove($id)
    {
        unset($this->_keys[$id],
              $this->_parameters[$id],
              $this->_classnames[$id],
              $this->_closures,
              $this->_instances[$id]);
    }


    /**
     * 删除某个由服务类名（或者闭包函数）生成的服务实例
     *
     * 执行本操作后，在下次get时，就会有重新有个新的服务实例生成。
     *
     * @param string $id
     */
    public function removeServiceInstance($id)
    {
        if (!$this->has($id) || !isset($this->_instances[$id])) {
            return;
        }

        // 仅针对CLASSNAME_TYPE或者CLOSURE_TYPE生效
        switch ($this->_keys[$id]) {
            case self::CLASSNAME_TYPE:
            case self::CLOSURE_TYPE:
                unset($this->_instances[$id]);
        }
    }


    /**
     * 返回一个新的服务实例
     *
     * @param string $id
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function getNew($id)
    {
        if (!$this->exists($id)) {
            throw new NotFoundException('未找到指定id');
        }

        $obj = null;

        switch ($this->_keys[$id]) {
            case self::PARAMETER_TYPE:
                return $this->_parameters[$id];

            case self::INSTANCE_TYPE:
                return $this->_instances[$id];

            case self::CLOSURE_TYPE:
                $serviceInstance = call_user_func_array($this->_closures[$id],
                                                        $params);
                return $serviceInstance;

            case self::CLASSNAME_TYPE:
                $class = new \ReflectionClass($this->_classnames[$id]);
                if (!$class->isInstantiable()) {
                    return null;
                }
                $serviceInstance = new $this->_classnames[$id];
                return $serviceInstance;
        } // switch
    }


    /**
     * 注册一个服务
     *
     * @param string $id
     * @param string|closure|object $service
     *
     * @return bool 成功返回true，失败返回false
     *
     * @throws ContainerException
     */
    public function bind($id, $service)
    {
        $this->checkID($id);

        if ($this->has($id)) {
            $this->remove($id);
        }

        if (is_string($service)) {
            if (!$this->checkClassname($service)) {
                throw new ContainerException('id必须为非空字符串');
            }
            $this->_keys[$id] = self::CLASSNAME_TYPE;
            $this->_classnames[$id] = $service;
        } elseif (is_object($service)) {
            if ($service instanceof \Closure) {
                $this->_keys[$id] = self::CLOSURE_TYPE;
                $this->_closures[$id] = $service;
            } else {
                $this->_keys[$id] = self::INSTANCE_TYPE;
                $this->_instances[$id] = $service;
            }
        } else {
            throw new ContainerException('传入的service类型不合法');
        }

        // 服务注册成功
        return true;
    }


    /**
     * 检查id是否合法。
     *
     * 按照container-interop要求，id必须为非空字符串，否则抛异常。
     *
     * @param string $id
     *
     * @throws ContainerException
     */
    private function checkID($id)
    {
        if (!is_string($id) || $id === '') {
            throw new ContainerException('id必须为非空字符串');
        }
    }


    /**
     * 检查给出的类名是否是一个有效的类名字符串
     *
     * 用正则表达式检查只包含：字母，数字，_，\
     *
     * @param string $classname
     *
     * @return bool
     */
    private function checkClassname($classname)
    {
        $matches = '';

        // 检查开始字符是数字或者结尾字符是\
        $result = preg_match('/^\\d/',
                             $classname,
                             $matches);
        if ($result > 0) {
            return false;
        }

        // 检查存在非单词字符
        $result = preg_match('/[^\w\\\]/',
                             $classname,
                             $matches);
        if ($result > 0) {
            return false;
        }

        // 检查 \数字 这种形式的错误
        $result = preg_match('/(\\\\\d)/',
                             $classname,
                             $matches);
        if ($result > 0) {
            return false;
        }

        // 检查无错，返回true
        return true;
    }
}
