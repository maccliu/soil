<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Service;

/**
 * Application
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Application extends Container
{


    /**
     * 注册一个Facade，可以直接调用类
     *
     * @param string $facade Facade的名字
     * @param string $class Facade的完整类名
     * @param string $accesspath 对应Facade的类文件路径。如为空，则从默认路径获取。
     */
    public function registerFacade($facade, $facadeClass, $accesspath = null)
    {
        if ($accesspath && (file_exists($accesspath)) && (is_file($accesspath))) {
            require $accesspath;
        }
        $this->registerShared($facade, $facadeClass);
    }


    /**
     * 注册一个服务提供者
     *
     * @class string 服务提供者的类名
     * @param string $accesspath 对应ServiceProvider的类文件所在路径。如为空，则从默认路径获取。
     */
    public function registerServiceProvider($class, $accesspath = null)
    {
    }
}
