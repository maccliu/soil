<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

/**
 * Tests a subject whether it matches a specified rule.
 *
 * @author Macc Liu <mail@maccliu.com>
 */
abstract class Match
{
    const TOKEN_TEXT = 'TEXT';
    const TOKEN_VAR = 'VAR';


    /**
     * Checks if the $subject starts with $find.
     *
     * @param string $subject
     * @param string $find
     * @param bool $ignore_case
     *
     * @return bool
     */
    public static function testStartWith($subject, $find, $ignore_case = false)
    {
        if (!is_string($find) || !is_string($subject)) {
            return false;
        }
        $len = strlen($find);
        if ($ignore_case) {
            return (strncasecmp($find, $subject, $len) === 0);
        } else {
            return (strncmp($find, $subject, $len) === 0);
        }
    }


    /**
     * Checks if the $subject ends with $find.
     *
     * @param string $subject
     * @param string $find
     * @param bool $ignore_case
     *
     * @return bool
     */
    public static function testEndWith($subject, $find, $ignore_case = false)
    {
        if (!is_string($find) || !is_string($subject)) {
            return false;
        }
        $len = strlen($find);
        $_find = strrev($find);
        $_subject = strrev($subject);
        if ($ignore_case) {
            return (strncasecmp($_find, $_subject, $len) === 0);
        } else {
            return (strncmp($_find, $_subject, $len) === 0);
        }
    }


    /**
     * Splits a $rule into tokens array.
     *
     * @param string    $rule       a rule to split
     * @param array     $rule_vars  ['var1'=>'pattern1', 'var1'=>'pattern2', ...]
     * @param null      $rule_vars  Using a default setting like {var}
     *
     * @return null     If $rule_vars invalid.
     * @return array    [
     *                      [type, text or var],
     *                      [type, text or var],
     *                      ...
     *                   ]
     */
    public static function tokenizeRule($rule, $rule_vars = null)
    {
        $preg_delimter = '/';

        $return = [];

        // Gets all variable names
        if ($rule_vars === null) {
            // Using a default setting like {var}
            $pattern_vars = $preg_delimter . '\{([^\{\}]+?)\}' . $preg_delimter;
        } elseif (is_array($rule_vars)) {
            if (empty($rule_vars)) {
                // If not defined any variable.
                $return[] = [self::TOKEN_TEXT, $rule];
                return $return;
            } else {
                // Gets the variable names from $rule_vars.
                $var_names = [];
                foreach ($rule_vars as $var_name => $var_pattern) {
                    // Transforms the var_name to preg qualified..
                    $var_names[] = preg_quote($var_name, $preg_delimter);
                }
                $var_names = implode('|', $var_names);
                $pattern_vars = $preg_delimter . $var_names . $preg_delimter;
            }
        } else {
            // Invalid $rule_vars
            return null;
        }

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
                $return[] = [ self::TOKEN_TEXT, $s];
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
     * Matches $subject with the specified named parameters $rule
     *
     * @param string        $subject
     * @param array         $matches        Returns the matches array if matched.
     * @param string        $rule
     * @param null          $rule           Uses the default style {foo}.
     * @param array         $rule_vars      ['var1'=>'pattern1', 'var1'=>'pattern2', ...]
     * @param bool          $ignore_case
     * @param string        $terminate_chars
     *
     * @return int
     */
    public static function namedParameters($subject, &$matches, $rule, $rule_vars = null, $ignore_case = false,
                                           $terminate_chars = '/\\?#')
    {
        $preg_delimter = '/';

        $rule_parts = self::tokenizeRule($rule, $rule_vars);
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
