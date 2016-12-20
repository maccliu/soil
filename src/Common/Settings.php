<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil\Common;

/**
 * Settings
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Settings implements \ArrayAccess
{
    protected $items = [];


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
     * @throws InvalidArgumentException
     */
    public function set($key, $value)
    {
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
        unset($this->items[$key]);
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
     */
    public function getItemsByGroup($group)
    {
        $this->checkGroup($group);

        $find = $group . '.';
        $len = mb_strlen($find);

        // first, filters all keys include 'group.'
        $keys = array_keys($this->items, $find, true);

        // checks one by one, finds starts with 'group.'
        $return = [];
        foreach ($keys as $key) {
            if (strncasecmp($find, $key, $len) === 0) {
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
     * @throws InvalidArgumentException
     */
    public function setGroup($group, array $items)
    {
        $this->checkgroup($group);

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
        $this->checkGroup($group);

        $return = [];
        $items = $this->getItemsByGroup($group);

        // remove "group." from the key name
        foreach ($items as $key => $value) {
            $newkey = mb_substr($key, mb_strlen($group) + 1);
            $return[$newkey] = $value;
        }

        return $return;
    }


    /**
     * Removes all items in the group
     *
     * @param string $group The group name.
     */
    public function removeGroup($group)
    {
        $items = $this->getItemsByGroup($group);

        foreach ($items as $key => $value) {
            unset($this->items[$key]);
        }
    }


    /**
     * Batch set.
     *
     * @param array $userSettings 用户设置
     * @param array $defaultSettings 默认设置
     */
    public function batchSet(array $userSettings, array $defaultSettings = [])
    {
        // process $defaultSettings
        foreach ($defaultSettings as $key => $value) {
            if (!$this->has($key)) {
                $this->checkKey($key);
                $this->set($key, $value);
            }
        }

        // process $userSettings
        foreach ($userSettings as $key => $value) {
            $this->checkKey($key);
            $this->set($key, $value);
        }
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
     * Reads a setting file.
     *
     * @param string $filepath
     * @param string $group
     *
     * @return array Returns the settings array if OK.
     */
    public function readSettingFile($filepath, $group = '')
    {
        $require = function () use ($filepath) {
            if (file_exists($filepath)) {
                return require($filepath);
            } else {
                return [];
            }
        };
        $items = $require();

        if (empty($items)) {
            return [];
        }

        if (!is_string($group)) {
            return [];
        } elseif ($group === '') {
            $groupname = '';
        } else {
            $groupname = $group . '.';
        }

        $return = [];
        foreach ($items as $key => $value) {
            $return[$groupname . $key] = $value;
        }
        return $return;
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
