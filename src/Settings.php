<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu
 * WITHOUT WARRANTY OF ANY KIND
 */


namespace Soil;


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
            if (!is_string($key)) {

                throw new \InvalidArgumentException(
                'Invalid argument type in ' . __NAMESPACE__ . '\\' . __CLASS__ . '->' . __METHOD__ . '():' .
                'key MUST be string.'
                );
            }
        }

        $this->items = $items;
    }


    /**
     * Retrieves all keys
     * @return array
     */
    public function keys()
    {
        $this->sortKeys();
        return array_keys($this->items);
    }


    /**
     * Checks if a setting is set.
     *
     * @param string $key The setting key
     *
     * @return bool
     */
    public function exists($key)
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
        if (!is_string($group) || ($group === '')) {
            throw new \InvalidArgumentException(
            'Invalid argument type in ' . __NAMESPACE__ . '\\' . __CLASS__ . '->' . __METHOD__ . '():' .
            'key MUST be string.'
            );
        }

        $this->items[$key] = $value;
    }


    /**
     * Gets a setting.
     *
     * @param string $key   The setting key
     *
     * @return mixed Return the setting if found.
     * @return null Return null if not exist.
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        } else {
            return null;
        }
    }


    /**
     * Remove a setting
     *
     * @param string $key The setting key
     */
    public function remove($key)
    {
        if ($this->exists($key)) {
            unset($this->items[$key]);
        }
    }


    /**
     * Lists all setting items in the same group
     *
     * If we have [foo, foo.aaa, foo.bbb, foo.ccc],
     * We'll get [foo.aaa, foo.bbb, foo.ccc], exclude [foo].
     *
     * @param string $group Group name
     *
     * @return array Settings array found, order by their keys.
     * @return array [] if not found any one.
     */
    public function getGroupItems($group)
    {
        if (!is_string($group) || ($group === '')) {
            throw new \InvalidArgumentException(
            'Invalid argument type in ' . __NAMESPACE__ . '\\' . __CLASS__ . '->' . __METHOD__ . '():' .
            'group MUST be string.'
            );
        }

        /*
         * Sort the keys first
         */
        ksort($this->items);

        /*
         * searching
         */
        $find = $group . '.';
        $len = mb_strlen($find);
        $return = [];
        $found = false;

        foreach ($this->items as $key => $value) {
            if (strncasecmp($find, $key, $len) === 0) {
                /*
                 * Found one
                 */
                $return[$key] = $value;
                $found = true;
            } elseif ($found == true) {
                /*
                 * Because we had sorted the array keys.
                 */
                break;
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
        if (!is_string($group) || ($group === '')) {
            throw new \InvalidArgumentException(
            'Invalid argument type in ' . __NAMESPACE__ . '\\' . __CLASS__ . '->' . __METHOD__ . '():' .
            'group MUST be string and NOT a null string.'
            );
        }

        foreach ($items as $key => $value) {
            $this->items[$group . '.' . $key] = $value;
        }

        ksort($this->items);
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
        if (!is_string($group) || ($group === '')) {
            throw new \InvalidArgumentException(
            'Invalid argument type in ' . __NAMESPACE__ . '\\' . __CLASS__ . '->' . __METHOD__ . '():' .
            'group MUST be string.'
            );
        }

        $return = [];

        $items = $this->getGroupItems($group);

        foreach ($items as $key => $value) {
            /*
             * remove "groupname." from the key name
             */
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
        $items = $this->getGroupItems($group);

        foreach ($items as $key => $value) {
            unset($this->items[$key]);
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
     *
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
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
