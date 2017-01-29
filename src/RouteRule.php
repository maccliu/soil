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


    /**
     * Matches a subject.
     *
     * @param string  $subject
     *
     * @return bool
     */
    public function match($subject, &$matches)
    {
        $preg_delimter = '/';

        $rule_parts = $this->tokenize($this->rule);
        if ($rule_parts === null) {
            return null;
        }

        $vartable = []; // $vartable['{foo}'] => $var1

        $patterns = [];
        $patterns[] = '^';

        foreach ($rule_parts as $id => $part) {
            list($type, $token) = $part;

            switch ($type) {
                case self::TOKEN_TEXT:
                    $patterns[] = preg_quote($token, $preg_delimter);
                    break;

                case self::TOKEN_VAR:
                    /*
                     * If $vartable[$text] defined, just add a backward-reference,
                     * Or creates a new var entry.
                     */
                    if (array_key_exists($token, $vartable)) {
                        $pattern[] = '(\\k<' . $vartable[$token] . '>)';
                    } else {
                        $vartable[$token] = 'var' . $id;
                        if ($rule_vars === null || !isset($rule_vars[$token])) {
                            $p = '[^' . preg_quote($terminate_chars, $preg_delimter) . ']+';
                        } else {
                            $p = $rule_vars[$token];
                        }
                        $patterns[] = '(?<' . $vartable[$token] . '>' . $p . ')';
                    }
                    break;
            }
        }

        $patterns = $preg_delimter . implode('', $patterns) . $preg_delimter;
        if ($ignore_case) {
            $patterns = $patterns . 'i';  // Case-insensitive
        }

        // Executes the preg_match_all, returns the result.
        $submatches = [];
        $result = preg_match($patterns, $subject, $submatches);

        // If matched, puts the variables value into $matches
        if ($result) {
            $matches[0] = $submatches[0];
            $matches[-1] = substr($subject, strlen($matches[0]));
            foreach ($vartable as $token => $var) {
                $matches[$token] = $submatches[$var];
            }
        }

        // Done!
        return $result;
    }
}
