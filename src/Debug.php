<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Description of Debug
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Debug
{
    public static function halt($var)
    {
        var_dump($var);
        die();
    }
}
