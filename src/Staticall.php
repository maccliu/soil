<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

use Soil\Container;

/**
 * Staticall
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Staticall
{
    public static $container = null;
    public static $cachepath = null;

    protected static $staticalls = [];      // maps Foo to service_id
    protected static $fqcns = [];           // maps Namespace\FooStaticall to Foo

    private static $registerd = false;
    private static $eval_enabled = false;


    /**
     * Initializes this Staticall
     *
     * @param Container $container
     */
    final public static function __construct(Container $container, $cachepath = null)
    {
        static::$staticalls = [];
        static::$fqcns = [];

        // sets the container
        static::$container = $container;

        // sets the cache root directory
        if (is_string($cachepath) && file_exists($cachepath) && is_dir($cachepath)) {
            static::$cachepath = $cachepath;
        } else {
            static::$cachepath = null;
        }

        // checks eval() enabled
        static::$eval_enabled = function_exists('eval');

        // register
        static::$registerd = spl_autoload_register([get_called_class(), 'autoload']);
    }


    /**
     * @param string $class
     */
    final public static function autoload($class)
    {
        if (array_key_exists($class, static::$staticalls)) {
            static::loadStaticallClass($class);
        }
    }


    /**
     * Loads the specified Staticall class
     *
     * If PHP evel() function works, we use eval() to generator a FooStaticall class,
     * If evel() has been disabled, we have to write a FooStaticall class file to a temporary file firstly,
     * then include it.
     *
     * @param string $class
     */
    private static function loadStaticallClass($class)
    {
        $fqcn = __NAMESPACE__ . "\\{$class}Staticall";

        $def = static::createStaticallClass($class);
        if (function_exists('eval')) {
            eval($def); // Hope eval() works, its usage is more simple.
        } else {
            $cachepath = self::$cachepath;
            if ($cachepath === null || !file_exists($cachepath) || !is_dir($cachepath)) {
                throw new \Exception("Fail to create a {$class}Staticall class.");
                return false;
            }

            $filename = realpath($cachepath) . '/' . $fqcn . '.php';

            if (!file_exists($filename)) {
                $result = file_put_contents($filename, $def);
                if ($result === false) {
                    throw new \Exception("Fail to create a {$class}Staticall file.");
                    return false;
                }
            }

            require $filename;
        }

        class_alias($fqcn, $class);
        self::$fqcns[$fqcn] = $class;
    }


    /**
     * Links the specified staticall to a container service
     *
     * @param string $staticall_name  The staticall name, like 'Foo'
     * @param string $service_id      The service id in the Container
     */
    final public static function link($staticall_name, $service_id)
    {
        static::$staticalls[$staticall_name] = $service_id;
    }


    /**
     * @throws \Exception
     */
    public static function __callStatic($method, $arguments)
    {
        $fqcn = get_called_class();
        $class = static::$fqcns[$fqcn];
        $id = static::$staticalls[$class];

        if (!static::$container->has($id)) {
            throw new \Exception("Staticall {$class}::{$method} fail. Not found the corresponded service.");
        }

        $instance = static::$container[$id];
        return call_user_func_array([$instance, $method], $arguments);
    }


    /**
     * Creates a FooStaticall Class file.
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
