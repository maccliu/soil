<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * 单例化
 *
 * 只能用 $bar=Foo::getInstance() 取得实例，不能用 new 或 clone
 * @see https://github.com/maccliu/handbook/tree/master/php
 */
class Singleton
{
    /**
     * 该static成员变量用来保存实例
     */
    private static $_instance = null;


    /**
     * 把__construct()私有化，防止创建对象
     */
    private function __construct()
    {

    }


    /**
     * 把__clone()私有化，防止创建对象
     */
    private function __clone()
    {

    }


    /**
     * 获取实例化对象
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
}
