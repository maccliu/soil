<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Service;

/**
 * Statica
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Statica
{
    protected static $container = null;
    protected static $loader = null;
    protected static $instances = [];


    /**
     *
     * @param \Soil\Service\Container $container
     * @param \Soil\Loader\AliasLoader $loader
     */
    final public static function init(\Soil\Service\Container $container, \Soil\Loader\AliasLoader $loader)
    {
        static::$container = $container;
        static::$loader = $loader;

        foreach ($loader->getAliases() as $alias => $class) {
            static::$container->bind($class,
                                     $class::setClass());
        }
        var_dump(static::$container);
    }


    /**
     *
     * @return mixed 返回可为实际要指向的类名、闭包或者实例。可参见 Container::register()的 $class参数
     *
     * @throws \RuntimeException
     */
    protected static function setLinker()
    {
        throw new \RuntimeException("Statica子类必须实现setAccessor方法");
    }


    /**
     * @param string $method
     * @param array $args
     * @return type
     */
    public static function __callStatic($method, $args)
    {
        var_dump($method,
                 $args);
        $instance = static::$container[get_called_class()];

        return call_user_func_array([$instance, $method],
                                    $args);
    }


    protected static function getInstance()
    {
        var_dump(static::$container);
        echo __CLASS__;
        return static::$container[__CLASS__];
    }
}
