<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Description of String
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class String
{
    /**
     * Checks the $subject is start with $find.
     *
     * @param string $str
     * @param string $subject
     * @param bool $ignore_case
     *
     * @return boolean
     */
    public static function isStartWith($find, $subject, $ignore_case = false)
    {
        if (!is_string($find) || !is_string($subject)) {
            return false;
        }
        $len = mb_strlen($find);
        if ($ignore_case) {
            return (strncasecmp($find,
                                $subject,
                                $len) === 0);
        } else {
            return (strncmp($find,
                            $subject,
                            $len) === 0);
        }
    }


    /**
     * Checks the $subject is end with $find.
     *
     * @param string $str
     * @param string $subject
     * @param bool $ignore_case
     *
     * @return boolean
     */
    public static function isEndWith($find, $subject, $ignore_case = false)
    {
        if (!is_string($find) || !is_string($subject)) {
            return false;
        }
        $len = mb_strlen($find);
        $_find = strrev($find);
        $_subject = strrev($subject);
        if ($ignore_case) {
            return (strncasecmp($_find,
                                $_subject,
                                $len) === 0);
        } else {
            return (strncmp($_find,
                            $_subject,
                            $len) === 0);
        }
    }
}
