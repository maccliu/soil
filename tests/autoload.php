<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu
 * WITHOUT WARRANTY OF ANY KIND
 */
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'soil\\settings' => 'Settings.php',
                'soil\\container' => 'Container.php',
            );
        }
        $classname = strtolower($class);
        if (isset($classes[$classname])) {
            require __DIR__ . '\\..\\src\\' . $classes[$classname];
        }
    }, true, false
);
