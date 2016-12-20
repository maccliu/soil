<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Loader;

/**
 * Description of AliasLoader
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class AliasLoader implements AliasLoaderInterface
{
    private $_aliases = [];
    private $_registered = false;


    /**
     * 新增一个类别名
     *
     * @param string $alias 别名
     * @param string $original 想要的完整类名（含Namespace）
     *
     * @throws \InvalidArgumentException 不可重复设置类的别名
     */
    public function addAlias($alias, $original)
    {
        if (isset($this->_aliases[$alias])) {
            throw new \InvalidArgumentException("不可重复设置类别名:{$alias}");
        }

        $this->_aliases[$alias] = $original;

        return $this; // 可链式调用
    }


    /**
     * 执行class_alias()
     *
     * @return bool 成功返回true，失败返回false
     */
    public function load($alias)
    {
        if (!isset($this->_aliases[$alias])) {
            throw new \InvalidArgumentException("未找到此别名:{$alias}");
        }

        $original = $this__aliases[$alias];
        return class_alias($original, $alias);
    }


    /**
     * 把本类的login()注册到spl_autoload
     *
     * @return bool
     */
    public function register()
    {
        if ($this->isRegistered()) {
            return true;
        }

        $this->_registered = spl_autoload_register([$this, 'load']);
        return $this->_registered;
    }


    /**
     * 是否已经成功执行过register()，确保不会重复注册。
     *
     * @return bool
     */
    public function isRegistered()
    {
        return $this->_registered;
    }
}
