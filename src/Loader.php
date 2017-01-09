<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Loader
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Loader
{
    private $_registered = false;   // 只可spl_autoload_register一次
    private $_namespaces = [];      // 名称空间列表
    private $_aliases = [];         // 别名列表
    private $_queue = [];           // 所有登记的队列

    const NAMESPACE_TYPE = 'namespace';
    const ALIAS_TYPE = 'alias';


    /**
     * 把$this->login()注册到spl_autoload
     *
     * @return bool
     */
    public function register()
    {
        if ($this->_registered) {
            return true;
        }

        $this->_registered = spl_autoload_register([$this, 'autoload']);
        return $this->_registered;
    }


    /**
     * 尝试load一个类
     *
     * @param string $class  FQCN的类名（'your\\class\\name'）
     *
     * @return bool
     */
    protected function autoload($class)
    {
        foreach ($this->_queue as $record) {
            switch ($record['type']) {
                case self::NAMESPACE_TYPE:
                    $result = $this->loadNamespace($class, $record['namespace'], $record['directory']);
                    if ($result) {
                        return true;
                    }
                    break;

                case self::ALIAS_TYPE:
                    $result = $this->loadAlias($class, $record['alias'], $record['real']);
                    if ($result) {
                        return true;
                    }
                    break;
            }
        }
    }


    /**
     * 在某个namespace下能否找到对应的class
     *
     * @param string $class     FQCN的类名
     * @param string $namespace
     * @param string $directory
     *
     * @return bool
     */
    private function loadNamespace($class, $namespace, $directory)
    {
        // 检查class是否包含指定的namespace
        $len = strlen($namespace);
        if (strncmp($class, $namespace, $len) !== 0) {
            return false;
        }

        // 检查对应的目录是否存在
        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        }

        // 去除class中的namespace部分
        $cls = substr($class, $len);

        // 检查目标php文件是否存在
        $dir = realpath($directory);
        $target = "{$dir}{$cls}.php";
        if (!file_exists($target) || !is_file($target)) {
            return false;
        }

        // 引入目标文件
        require($target);

        // 引入后，检查class是否有了
        if (class_exists($class)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 尝试class是不是一个已设置的alias
     *
     * @param string $class A FQCN classname
     * @param string $alias A FQCN classname
     * @param string $real  A FQCN classname
     *
     * @return bool
     */
    private function loadAlias($class, $alias, $real)
    {
        if ($class === $alias) {
            return class_alias($real, $alias);
        }
    }


    /**
     * 映射一个名称空间到其对应的目录
     *
     * @param string $namespace  'your\\namespace'
     * @param string $directory  '/your/namespace/root/directory'
     *
     * @return ClassLoader $this
     */
    public function addNamespace($namespace, $directory)
    {
        // 先去除$namespace前后的'\'以及空白字符
        $namespace = trim($namespace, "\\ \t\n\r\0\x0B");

        $this->_namespaces[$namespace] = $directory;

        $this->_queue[] = [
            'type'      => self::NAMESPACE_TYPE,
            'namespace' => $namespace,
            'directory' => $directory,
        ];

        return $this;
    }


    /**
     * 把一个类的别名指向到其真实类名
     *
     * @param string $alias  类的别名
     * @param string $real   包含名称空间的真实类名
     */
    public function addAlias($alias, $real)
    {
        if (array_key_exists($alias, $this->_aliases)) {
            throw new Exception('已经声明过的类别名，不可再次重复声明');
        }

        $this->_aliases[$alias] = $real;

        $this->_queue[] = [
            'type'  => self::ALIAS_TYPE,
            'alias' => $alias,
            'real'  => $real,
        ];

        return $this;
    }
}
