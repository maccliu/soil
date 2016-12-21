<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Loader;

/**
 * AliasLoaderInterface
 *
 * @author Macc Liu <mail@maccliu.com>
 */
interface AliasLoaderInterface
{


    /**
     * 新增一个类别名
     *
     * @param string $alias 别名
     * @param string $linkto 要指向的实际类名（完整类名，包含Namespace）
     *
     * @throws \InvalidArgumentException 不可重复设置类的别名
     */
    public function addAlias($alias, $linkto);


    /**
     * 执行class_alias()
     *
     * @return bool 成功返回true，失败返回false
     */
    public function load($alias);


    /**
     * 把load()注册到spl_autoload
     *
     * @return bool 成功返回true，失败返回false
     */
    public function register();


    /**
     * 确保只执行一次注册
     *
     * @return bool
     */
    public function isRegistered();


    /**
     * 返回所有已登记的别名
     */
    public function getAliases();
}
