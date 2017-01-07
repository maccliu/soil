<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Application
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Application extends \Soil\Container
{
    public $default_module = 'Index';
    public $default_controller = 'Index';
    public $default_action = 'index';
    private $loaded_controllers = [];


    /**
     * Import settings
     *
     * @param \Soil\Settings $conf
     */
    public function config(\Soil\Settings $conf)
    {
        $keys = $conf->keys();
        foreach ($keys as $key) {
            $this->set($key, $conf[$key]);
        }
    }


    /**
     *
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    public function execute($module = null, $controller = null, $action = null, $arguments = null)
    {
        if (!$this->has('path.app')) {
            throw new \Exception('path.app MUST be defined');
        }

        $_module = ($module === null) ? $this->default_module : $module;
        $_controller = ($controller === null) ? $this->default_controller : $controller;
        $_action = ($action === null) ? $this->default_action : $action;

        $app_path = $this->get('path.app');

        $module_path = $app_path . $_module . DIRECTORY_SEPARATOR;
        if (!file_exists($module_path) || !is_dir($module_path)) {
            throw new \Exception("Module '$_module' does not exist.");
        }

        $controller_path = $module_path . 'controllers' . DIRECTORY_SEPARATOR . $_controller . 'Controller.php';
        if (!file_exists($controller_path) || !is_file($controller_path)) {
            throw new \Exception("Controller '$_controller' does not exist in module '$_module'");
        }

        if (!array_key_exists($controller_path, $this->loaded_controllers)) {
            $this->loaded_controllers[$controller_path] = true;
            require($controller_path);
        }

        $controller_name = '\\App\\' . $_controller . 'Controller';
        $action_name = $_action . 'Action';

        $this_controller = new $controller_name;
        $this_controller->setApp($this);
        try {
            $this_controller->$action_name();
        } catch (Exception $ex) {
        }
    }
}
