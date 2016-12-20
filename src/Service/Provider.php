<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Service;

/**
 * Provider
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Provider
{
    public $defer = false;  // 是否延迟加载
    protected $app = null;  // 指向app实例


    abstract public function register();


    public function __construct($app)
    {
        $this->app = $app;
    }


    public function __call($method, $args)
    {
        switch ($method) {
            case 'boot':
                // 在boot()中可以执行一些配置初始化的工作（bootstrap）
                return;
        }

        throw new \BadMethodCallException("{$method}方法不存在");
    }
}
