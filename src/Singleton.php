<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Singleton
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Singleton
{
    /**
     * Saves the unique instance
     */
    private static $_instance = null;


    /**
     * new is not permitted.
     */
    private function __construct()
    {
    }


    /**
     * clone() is not permitted.
     */
    private function __clone()
    {
    }


    /**
     * Gets the instance. If not exists, creates one.
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
}
