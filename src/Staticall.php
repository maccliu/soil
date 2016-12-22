<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Staticall
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Staticall
{
    protected static $container = null;
    protected static $staticalls = [];      // maps Foo to service_id
    protected static $fqcns = [];            // maps Namespace\FooStaticall to Foo
    protected static $registerd = false;


    /**
     * Boots the Staticall
     *
     * @param Container $container
     */
    final public static function boot($container)
    {
        self::$container = $container;
        self::$staticalls = [];
        self::$fqcns = [];

        static::$registerd = spl_autoload_register([get_called_class(), 'autoload']);
    }


    /**
     * @param string $class
     */
    final public static function autoload($class)
    {
        if (array_key_exists($class,
                             static::$staticalls)) {
            static::loadStaticallClass($class);
        }
    }


    /**
     * Load the specified StaticallClass
     *
     * If eval() can work, use eval() to fake a FooStaticall Class.
     * If evel() be disabled, we have to create a Class file using the template.
     *
     * @param string $class
     */
    private static function loadStaticallClass($class)
    {
        $fqcn = __NAMESPACE__ . "\\{$class}Staticall";

        $def = self::createStaticallClass($class);
        eval($def); // wish eval() can work.

        class_alias($fqcn,
                    $class);
        self::$fqcns[$fqcn] = $class;
    }


    /**
     * Bindings the specified staticall to a container service
     *
     * @param string $staticall_name The staticall name
     * @param string $service_id The service id in the Container
     */
    public static function set($staticall_name, $service_id)
    {
        static::$staticalls[$staticall_name] = $service_id;
    }


    /**
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $arguments)
    {
        $fqcn = get_called_class();
        $class = self::$fqcns[$fqcn];
        $id = static::$staticalls[$class];

        if (static::$container->has($id)) {
            $instance = static::$container[$id];
            return call_user_func_array([$instance, $method],
                                        $arguments);
        } else {
            throw new \RuntimeException("Staticall {$class}::{$method} fail. Not found the corresponded service.");
        }
    }


    /**
     * Creates a FooStaticall Class file.
     *
     * For use with eval() or write to a file.
     *
     * @param string $class
     * @return string
     */
    private static function createStaticallClass($class)
    {
        $namespace = __NAMESPACE__;

        return <<<EOB
namespace {$namespace};
class {$class}Staticall extends Staticall {}
EOB;
    }
}
