<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Route Rule
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class RouteRule
{
    private $rule = null;
    private $parsed = false;
    private $vars = [];

    const TOKEN_TEXT = 'TEXT';
    const TOKEN_VAR = 'VAR';


    public function __construct($rule)
    {
        $this->parse($rule);
    }


    private function parse($rule)
    {
        $pattern = '\{([^\}]+)\}';
    }


    /**
     * Defines a variable's pattern.
     *
     * @param string  $var
     * @param string  $pattern
     *
     * @return this
     */
    public function def($var, $pattern)
    {
        if (!is_string($var) || !is_string($pattern)) {
            throw new \Exception('Invalid argument type.');
        }

        $std_var = trim($var);
        $std_pattern = trim($pattern);

        if (trim($std_var) === '' || trim($std_pattern) === '') {
            throw new \Exception('Invalid argument value.');
        }

        $this->vars[$std_var] = $std_pattern;
        return $this;
    }


    /**
     * Matches a subject.
     *
     * @param string  $subject
     *
     * @return bool
     */
    public function match($subject)
    {
        return false;
    }


    /**
     * Splits a rule into a token array.
     *
     * @param string  $rule rule to split
     *
     * @return array  [
     *                     [type, text or var],
     *                     [type, text or var],
     *                     ...
     *                 ]
     */
    private function tokenize($rule)
    {
        $preg_delimter = '/';

        $return = [];

        $pattern_vars = $preg_delimter . '\{([^\{\}]+?)\}' . $preg_delimter;

        // Gets all variables in the $rule.
        $matches = [];
        $result = preg_match_all($pattern_vars, $rule, $matches, PREG_OFFSET_CAPTURE);

        // Returns if not matched.
        if (!$result) {
            $return[] = [self::TOKEN_TEXT, $rule];
            return $return;
        }

        $lastpos = 0;

        foreach ($matches[0] as $part) {
            list($var, $pos) = $part;

            // Adds the text
            if ($pos > $lastpos) {
                $s = substr($rule, $lastpos, $pos - $lastpos);
                $return[] = [self::TOKEN_TEXT, $s];
            }

            // Adds this var
            $return[] = [self::TOKEN_VAR, $var];

            // Moves the $lastpos
            $lastpos = $pos + strlen($var);
        }

        // Adds the tail.
        $s = substr($rule, $lastpos);
        if ($s !== '') {
            $return[] = [self::TOKEN_TEXT, $s];
        }

        return $return;
    }
}
