<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

use Soil\Settings;

/**
 * Settings
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Settings implements \ArrayAccess
{
    private $locked = false;
    public $items = [];


    /**
     *
     * @param array $items
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->checkKey($key);
        }

        $this->items = $items;
    }


    /**
     * Retrieves all keys
     * @return array
     */
    public function keys()
    {
        return array_keys($this->items);
    }


    /**
     * Checks if a setting is set.
     *
     * @param string $key The setting key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }


    /**
     * Sets a setting.
     *
     * @param string $key The setting key
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        $this->checkLocked();

        $this->checkKey($key);
        $this->items[$key] = $value;
    }


    /**
     * Gets a setting.
     *
     * @param string $key The setting key
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->items[$key] : $default;
    }


    /**
     * Remove a setting
     *
     * @param string $key The setting key
     */
    public function remove($key)
    {
        $this->checkLocked();

        unset($this->items[$key]);
    }


    /**
     * Clears all settings.
     */
    public function clear()
    {
        $this->checkLocked();

        $this->items = [];
    }


    /**
     * Lists all setting items in the same group
     *
     * If we have [foo, foo.aaa, foo.bbb, foo.ccc],
     * We'll get [foo.aaa, foo.bbb, foo.ccc], exclude [foo].
     *
     * @param string $group Group name
     *
     * @return array Settings array found, order by their keys. Return [] if not found.
     *
     * @throws \InvalidArgumentException
     */
    public function getGroupItems($group)
    {
        $this->checkGroup($group);

        $find = $group . '.';
        $len = mb_strlen($find);

        // first, filters all keys include 'group.'
        $keys = array_keys($this->items, $find, true);

        // checks one by one, finds starts with 'group.'
        $return = [];
        foreach ($keys as $key) {
            if (strncmp($find, $key, $len) === 0) {
                $return[$key] = $this->items[$key];
            }
        }
        return $return;
    }


    /**
     * Set a group of settings.
     *
     * If we have $items ['aaa'=>foo, 'bbb'=>bar],
     * setGroup('mor', $items) will add ['mor.aaa'=>foo, 'mor.bbb'=>bar] to our settings.
     *
     * @param string $group The group name.
     * @param array $items
     *
     * @throws \InvalidArgumentException
     */
    public function setGroup($group, array $items)
    {
        $this->checkLocked();

        $this->checkGroup($group);

        foreach ($items as $key => $value) {
            $this->items[$group . '.' . $key] = $value;
        }
    }


    /**
     * Gets all items of the group
     *
     * @param string $group
     *
     * @return array
     */
    public function getGroup($group)
    {
        $return = [];
        $items = $this->getGroupItems($group);

        // remove "group." from the key name
        foreach ($items as $key => $value) {
            $newkey = mb_substr($key, mb_strlen($group) + 1);
            $return[$newkey] = $value;
        }

        return $return;
    }


    /**
     * Removes all items in a group
     *
     * @param string $group The group name.
     */
    public function removeGroup($group)
    {
        $this->checkLocked();

        $items = $this->getGroupItems($group);

        foreach ($items as $key => $value) {
            unset($this->items[$key]);
        }
    }


    /**
     * Batch set settings.
     *
     * @param array $user_settings 用户设置
     * @param array $default_settings 默认设置
     */
    public function batchSet(array $user_settings, array $default_settings = [])
    {
        $this->checkLocked();

        // process $default_settings
        foreach ($default_settings as $key => $value) {
            $this->checkKey($key);
        }

        // process $user_settings
        foreach ($user_settings as $key => $value) {
            $this->checkKey($key);
        }

        // merge $default_settings and $user_settings
        $items = array_merge($default_settings, $user_settings);

        // merge $items to $this->items
        $this->items = array_merge($this->items, $items);
    }


    /**
     * Sorts the array keys.
     *
     * $return void
     */
    public function sortKeys()
    {
        ksort($this->items);
    }


    /**
     * Loads a setting file.
     *
     * @param string $filepath
     * @param string $group
     *
     * @return bool success/fail
     */
    public function load($filepath, $group = null)
    {
        $this->checkLocked();

        $require = function () use ($filepath) {
            if (file_exists($filepath)) {
                return require($filepath);
            } else {
                return false;
            }
        };
        $items = $require();

        if (empty($items)) {
            return false;
        }

        if ($group === null) {
            $groupname = '';
        } else {
            $this->checkGroup($group);
            $groupname = $group . '.';
        }

        foreach ($items as $key => $value) {
            $this->items[$groupname . $key] = $value;
        }
        return true;
    }


    /**
     * Merges the src setting to the current settings.
     *
     * @param \Soil\Settings $src
     */
    public function merge(\Soil\Settings $src)
    {
        $this->checkLocked();

        $keys = $src->keys();
        foreach ($keys as $key) {
            $this->items[$key] = $src[$key];
        }
    }


    /**
     * Locks the setting values, makes all values readonly
     */
    public function lock()
    {
        $this->locked = true;
    }


    /**
     * Unlocks the setting values
     */
    public function unlock()
    {
        $this->locked = false;
    }


    private function checkLocked()
    {
        if ($this->locked) {
            throw new \Exception('This settings had been locked.');
        }
    }


    /**
     * Checks the $key type is string and not a null string.
     *
     * @param string $key
     * @throws \InvalidArgumentException
     */
    private function checkKey($key)
    {
        if (!is_string($key) || ($key === '')) {
            throw new \InvalidArgumentException('Invalid key name.');
        }
    }


    /**
     * Checks the $group type is string and not a null string.
     *
     * @param string $group
     * @throws \InvalidArgumentException
     */
    private function checkGroup($group)
    {
        if (!is_string($group) || ($group === '')) {
            throw new \InvalidArgumentException('Invalid group name.');
        }
    }


    /**
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }


    /**
     *
     * @param string $key
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }


    /**
     *
     * @param string $key
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }


    /**
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }
}
