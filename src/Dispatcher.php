<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Dispatcher
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Dispatcher
{
    protected $jobs = [];
    protected $hooks = [];


    public function init()
    {
        $this->jobs = [];
        $this->hooks = [];
    }


    public function set($job, $callback)
    {
        $this->jobs[$job] = $callback;
    }


    public function get($job)
    {
        return isset($this->jobs[$job]) ? $this->jobs[$job] : null;
    }


    public function has($job)
    {
        return isset($this->jobs[$job]);
    }


    public function remove($job)
    {
        unset($this->jobs[$job]);
        unset($this->hooks[$job]);
    }


    /**
     * 在指定的job前或job后挂载一个回调函数
     *
     * @param string $job        job名称
     * @param string $position   位置（可为before或者after）
     * @param callback $callback 要加载的回调函数
     */
    public function hook($job, $position, $callback)
    {
        $this->hooks[$job][$position][] = $callback;
    }


    /**
     * 处理指定的任务
     *
     * @param string $job   任务名称
     * @param array $params 参数
     */
    public function execute($job, array $params = [])
    {
        $output = '';

        // 处理任务前置队列
        if (!isset($this->hooks[$job]['before'])) {
            $this->processHooks($this->hooks[$job]['before'], $params, $output);
        }

        // 处理任务
        $output = $this->process($this->get($job), $params);

        // 处理任务后置队列
        if (!isset($this->hooks[$job]['after'])) {
            $this->processHooks($this->hooks[$job]['after'], $params, $output);
        }

        // 输出结果
        return $output;
    }


    /**
     * 具体执行一个任务
     *
     * @param string|array $callback
     * @param array $params
     * @throws \Exception
     */
    public static function process($callback, array &$params = [])
    {
        if (is_callable($callback)) {
            if (is_array($callback)) {
                list($class, $method) = $callback;
                if (is_object($class)) {
                    return self::invokeMethod($callback, $params);
                } else {
                    return self::invokeStaticMethod($callback, $params);
                }
            } else {
                return self::callFunction($callback, $params);
            }
        } else {
            throw new \Exception('指定了不合法的回调函数');
        }
    }


    /**
     * 处理钩子任务
     *
     * @param array $hooks
     * @param array $params
     * @param string $output
     */
    public static function processHooks(array $hooks, array &$params, &$output)
    {
        $args = array(&$params, &$output);
        foreach ($hooks as $callback) {
            $continue = $this->process($callback, $args);
            if ($continue === false) {
                break;
            }
        }
    }


    /**
     * 调用指定函数
     *
     * @param string $callback
     * @param array $params
     */
    public static function callFunction($callback, array &$params = [])
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            return call_user_func_array($callback, $params);
        }

        switch (count($params)) {
            case 0:
                return $callback();
            case 1:
                return $callback($params[0]);
            case 2:
                return $callback($params[0], $params[1]);
            case 3:
                return $callback($params[0], $params[1], $params[2]);
            case 4:
                return $callback($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return $callback($params[0], $params[1], $params[2], $params[3], $params[4]);
            case 6:
                return $callback($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
            default:
                return call_user_func_array($callback, $params);
        }
    }


    /**
     * 调用指定对象的指定方法
     *
     * @param array $callback
     * @param array $params
     * @throws \Exception
     */
    public static function invokeMethod(array $callback, array &$params = [])
    {
        list($class, $method) = $callback;

        switch (count($params)) {
            case 0:
                return $class->$method();
            case 1:
                return $class->$method($params[0]);
            case 2:
                return $class->$method($params[0], $params[1]);
            case 3:
                return $class->$method($params[0], $params[1], $params[2]);
            case 4:
                return $class->$method($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return $class->$method($params[0], $params[1], $params[2], $params[3], $params[4]);
            case 6:
                return $class->$method($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
            default:
                return call_user_func_array($callback, $params);
        }
    }


    /**
     * 调用指定类的静态方法
     *
     * @param array $callback
     * @param array $params
     * @throws \Exception
     */
    public static function invokeStaticMethod(array $callback, array &$params = [])
    {
        list($class, $method) = $callback;

        switch (count($params)) {
            case 0:
                return $class::$method();
            case 1:
                return $class::$method($params[0]);
            case 2:
                return $class::$method($params[0], $params[1]);
            case 3:
                return $class::$method($params[0], $params[1], $params[2]);
            case 4:
                return $class::$method($params[0], $params[1], $params[2], $params[3]);
            case 5:
                return $class::$method($params[0], $params[1], $params[2], $params[3], $params[4]);
            case 6:
                return $class::$method($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
            default:
                return call_user_func_array($callback, $params);
        }
    }
}
