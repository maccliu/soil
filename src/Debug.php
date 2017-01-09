<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Debug
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Debug
{
    const VAR_DUMP_MODE = 'var_dump';
    const PRINT_R_MODE = 'print_r';


    /**
     * 停止执行后面的程序。
     * 还可以顺带输出一个需要跟踪的变量值。
     *
     * @param mixed  $var  需要跟踪的变量
     */
    public static function halt($var = null)
    {
        if ($var !== null) {
            echo self::dumpVar($var);
        }
        die();
    }


    /**
     * 导出一个变量的定义。
     *
     * @param mixed  $var
     * @param string $varname
     *
     * @return string
     */
    public static function dumpVar($var, $varname = null)
    {
        $begin = '';
        $end = '';
        if (is_string($varname) && $varname !== '') {
            $begin = $varname . ' = ';
            $end = ';' . PHP_EOL;
        }

        $s = self::format($var, 0);

        return "{$begin}{$s}{$end}";
    }


    protected static function format($var, $leading = 0)
    {
        if (is_array($var)) {
            if (empty($var)) {
                return '[]';
            }
            return self::formatArray($var, $leading);
        } elseif (is_null($var)) {
            return 'null';
        } else {
            return var_export($var, true);
        }
    }


    protected static function formatArray($array, $leading = 0)
    {
        $glue = PHP_EOL;
        $spaces = str_repeat(' ', $leading);    // 前导空格

        // 找出最大字符长度的key
        $maxlen = 0;
        $keys = array_keys($array);
        foreach ($keys as $key) {
            $len = mb_strwidth($key);
            if ($len > $maxlen) {
                $maxlen = $len;
            }
        }

        // 生成数组定义个每一行
        $s = [];
        $s[] = '['; // 第一行无需前导空格
        foreach ($array as $key => $value) {
            $key = var_export($key, true);
            $value = self::format($value, $leading + $maxlen + 10);
            $s[] = $spaces . str_pad('', 4) . str_pad($key, $maxlen + 2) . ' => ' . $value . ',';
        }
        $s[] = $spaces . ']';

        return implode($glue, $s);
    }
}
