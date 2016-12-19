<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Service;

/**
 * Facade
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Facade
{
    public static $resolvedInstance = [];
    protected static $app;


    public static function setFacadeApplication($app)
    {
        static::$app = $app;
    }


    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        return call_user_func_array([$instance, $method], $args);
    }


    public static function getFacadeRoot()
    {
        return self::resolveFacadeInstance(static::getFacadeAccessor());
    }


    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        return static::$resolvedInstance[$name] = static::$app[$name];
    }


    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException("Facade子类必须实现getFacadeAccessor方法");
    }
}
