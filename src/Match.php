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
abstract class Match
{


    /**
     * Checks if the $subject is start with $find.
     *
     * @param string $subject
     * @param string $find
     * @param bool $ignore_case
     *
     * @return bool
     */
    public static function isStartWith($subject, $find, $ignore_case = false)
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
     * Checks if the $subject is end with $find.
     *
     * @param string $subject
     * @param string $find
     * @param bool $ignore_case
     *
     * @return bool
     */
    public static function isEndWith($subject, $find, $ignore_case = false)
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
     * Matches $subject with the specified named parameters $rule
     *
     * @param string $subject
     * @param string $rule
     * @param null|array $rule_vars ['str1'=>'pattern1', 'str2'=>'pattern2', ...]
     * @param array $matches Returns the matches if matched.
     *
     * @return int
     */
    function namedParameters($subject, $rule, &$matches, $rule_vars = null, $ignore_case = false)
    {
        $preg_delimter = '/';

        // Gets all variable names
        if ($rule_vars === null) {
            // Default, uses the default declare style, such as {foo},{bar}
            $pattern_vars = $preg_delimter . '\{([^\{\}]+?)\}' . $preg_delimter;
        } elseif (is_array($rule_vars)) {
            if (empty($rule_vars)) {
                $pattern_vars = null;
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
            return false;
        }

        // Gets all variables in the $rule.
        if ($pattern_vars === null) {
            $result = false;
        } else {
            $vars = [];
            $result = preg_match_all($pattern_vars, $rule, $vars, PREG_OFFSET_CAPTURE);
        }

        // If not found any variables in $rule
        if (!$result) {
            if ($ignore_case) {
                return (strncasecmp($subject, $rule, strlen($rule)) === 0);
            } else {
                return (strncmp($subject, $rule, strlen($rule)) === 0);
            }
        }

        // Maps the user variables to the inner Variables
        // {foo} => var1
        $var_user_inner = [];

        // Maps the inner variables to the user Variables
        // var1 => {foo}
        $var_inner_user = [];

        $pattern = [];      // Splits $rule into many pattern parts
        $lastpos = 0;       // last pos
        $var_id = 1;        // inner variable increment id
        // Processes the begining part.
        $pattern[] = '^';

        // Loops to process each parts
        foreach ($vars[0] as $part) {
            list($var, $pos) = $part;

            // The normal text part
            if ($pos > $lastpos) {
                $s = substr($rule, $lastpos, $pos - $lastpos);
                $s = preg_quote($s, $preg_delimter);
                $pattern[] = $s;
            }

            // The variable part
            if (!array_key_exists($var, $var_user_inner)) {
                // Creates an item in the $var_user_inner and $var_inner_user
                // if $var not exists in them.
                $inner_var = 'var' . $var_id;
                $var_user_inner[$var] = $inner_var;         // $var_user_inner['{foo}'] = 'var1'
                $var_inner_user[$inner_var] = $var;         // $var_inner_user['var1'] = '{foo}'
                $var_id++;

                // Buiilds the pattern for this variable
                if (is_array($rule_vars) && is_string($rule_vars[$var])) {
                    // If $rules_vars defines a valid pattern, use it directly.
                    $p = $rule_vars[$var];
                } else {
                    // Find the $breakchar
                    $breakchar = substr($rule, $pos + strlen($var), 1);
                    if ($breakchar === '') {
                        $p = '.*';
                    } else {
                        $p = '[^' . preg_quote($breakchar, $preg_delimter) . ']+?';
                    }
                }

                // Builds a pattern with the sub-group name
                $pattern[] = '(?<' . $var_user_inner[$var] . '>' . $p . ')';
            } else {
                // Puts a backward-reference here, if the variable was defined already.
                $pattern[] = '(\\k<' . $var_user_inner[$var] . '>)';
            }

            // forwords the $lastpos to the next char
            $lastpos = $pos + strlen($var);
        }

        // Processes the tail part.
        $s = substr($rule, $lastpos);
        $s = preg_quote($s, $preg_delimter);
        $pattern[] = $s;

        // Implodes the whole pattern!
        $pattern = implode('', $pattern);
        $pattern = $preg_delimter . $pattern . $preg_delimter;
        if ($ignore_case) {
            $pattern = $pattern . 'i';  // Case-insensitive
        }

        // Executes the preg_match_all, returns the result.
        $submatches = [];
        $result = preg_match_all($pattern, $subject, $submatches);

        // If matched, puts the variables value into $matches
        if ($result) {
            $matches = [];
            foreach ($var_user_inner as $user => $inner) {
                $matches[$user] = $submatches[$inner][0];
            }
        }

        // Done!
        return $result;
    }
}
