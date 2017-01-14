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
 * Loads a FQCN (Fully-Qualified Class Name), like 'your\\class\\name'.
 * Loads a Class Alias.
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Loader
{
    private $_namespaces = [];      // namespace dictionary
    private $_aliases = [];         // alias dictionary
    private $_queue = [];

    const NAMESPACE_TYPE = 'namespace';
    const ALIAS_TYPE = 'alias';


    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
    }


    /**
     *
     * @param string $class  The class name to load
     *
     * @return bool
     */
    protected function autoload($class)
    {
        foreach ($this->_queue as $item) {
            switch ($item['type']) {
                case self::NAMESPACE_TYPE:
                    $result = $this->loadNamespace($class, $item['namespace'], $item['directory']);
                    if ($result) {
                        return true;
                    }
                    break;

                case self::ALIAS_TYPE:
                    $result = $this->loadAlias($class, $item['alias'], $item['real']);
                    if ($result) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }


    /**
     * Try to load a class under the specified namespace.
     *
     * @param string $class     FQCN
     * @param string $namespace
     * @param string $directory
     *
     * @return bool
     */
    private function loadNamespace($class, $namespace, $directory)
    {
        // under the namespace?
        $len = strlen($namespace);
        if (strncmp($class, $namespace, $len) !== 0) {
            return false;
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        }

        // remove the namepsace part
        $cls = substr($class, $len+1);

        // check the class file exists
        $dir = realpath($directory);
        $target = "{$dir}/{$cls}.php";
        if (!file_exists($target) || !is_file($target)) {
            return false;
        }

        // require the target class file.
        require($target);

        // check the result
        if (class_exists($class)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Try to load a class alias.
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
     * Add a namespace.
     *
     * @param string $namespace  'your\\namespace'
     * @param string $directory  '/your/namespace/root/directory'
     *
     * @return ClassLoader $this
     */
    public function addNamespace($namespace, $directory)
    {
        // normalize
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
     * Add a class alias.
     *
     * @param string $alias  The class alias
     * @param string $real   Real FQCN
     */
    public function addAlias($alias, $real)
    {
        if (array_key_exists($alias, $this->_aliases)) {
            throw new \Exception('Duplicated class alias.');
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
