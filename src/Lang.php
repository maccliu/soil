<?php
/**
 * Soil: An underlying PHP framework, under the MIT license.
 *
 * Copyright (c) 2016-2017, Macc Liu <mail@maccliu.com>
 * WITHOUT WARRANTY OF ANY KIND
 */

namespace Soil;

use Soil\Lang;

/**
 * Lang
 *
 * @author Macc Liu <mail@maccliu.com>
 */
class Lang implements \ArrayAccess
{
    /* for read */
    private $root = null;
    private $rel = null;
    private $source_lang = null;
    private $target_lang = null;
    private $source = [];
    private $target = [];
    private $loaded = false;

    /* for save */
    private $autosave = false;
    private $source_changed = false;
    private $target_changed = false;


    public function __construct($root = null, $rel = null, $source_lang = null, $target_lang = null)
    {
        $this->setRoot($root);
        $this->setRel($rel);
        $this->setSourceLang($source_lang);
        $this->setTargetLang($target_lang);
    }


    public function setRoot($root)
    {
        if (is_string($root) && file_exists($root) && is_dir($root)) {
            $this->root = realpath($root);
        } else {
            $this->root = null;
        }
        return $this;
    }


    public function setRel($rel)
    {
        if (is_string($rel)) {
            $this->rel = $rel;
        } else {
            $this->rel = null;
        }
        return $this;
    }


    public function setSourceLang($lang)
    {
        if (is_string($lang)) {
            $this->source_lang = $lang;
        } else {
            $this->source_lang = null;
        }
        $this->loaded = false;
        return $this;
    }


    public function setTargetLang($lang)
    {
        if (is_string($lang)) {
            $this->target_lang = $lang;
        } else {
            $this->target_lang = null;
        }
        $this->loaded = false;
        return $this;
    }


    public function setAutosave($bool)
    {
        $this->autosave = $bool;
        return $this;
    }


    public function get($text)
    {
        if (!$this->loaded) {
            $this->load();
        }

        if ($this->autosave) {
            if (!array_key_exists($text, $this->source)) {
                $this->source[$text] = true;
                $this->source_changed = true;
            }
        }

        if (array_key_exists($text, $this->target)) {
            return $this->target[$text];
        } else {
            return $text;
        }
    }


    public function load()
    {
        $this->loadLang($this->source_lang, $this->source);
        $this->loadLang($this->target_lang, $this->target);
        $this->loaded = true;
    }


    public function __destruct()
    {
        if ($this->autosave) {
            $this->save();
            return;
        }
    }


    public function save()
    {
        if (is_null($this->root) || is_null($this->rel)) {
            return;
        }

        if ($this->source_changed) {
            ksort($this->source);
            $this->saveLang($this->source_lang, $this->source);
        }

        if ($this->target_changed) {
            ksort($this->target);
            $this->saveLang($this->target_lang, $this->target);
        }
    }


    /**
     * Loads a lang file to the specified array.
     *
     * @param string $lang
     * @param array  $array
     *
     * @return bool
     */
    private function loadLang($lang, &$array)
    {
        $array = [];

        $root = $this->root;
        $rel = $this->rel;

        if (!is_string($root) || !is_string($rel) || !is_string($lang)) {
            return false;
        }

        $path = "{$root}/{$rel}/{$lang}.php";
        if (!file_exists($path) || !is_file($path)) {
            return false;
        }

        $result = require($path);
        if (is_array($result)) {
            $array = $result;
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param string  $lang
     * @param array   $array
     *
     * @return bool
     */
    private function saveLang($lang, &$array)
    {
        $root = $this->root;
        $rel = $this->rel;

        if (!is_string($root) || !is_string($rel) || !is_string($lang)) {
            return false;
        }

        $dir = "{$root}/{$rel}";
        if (!file_exists($dir)) {
            if (!@mkdir($dir, 0777)) {
                return false;
            }
        } elseif (!is_dir($dir)) {
            return false;
        }

        $path = "$dir/{$lang}.php";

        $var = var_export($array, true);
        $content = <<<EOF
<?php
return $var;

EOF;
        return @file_put_contents($path, $content);
    }


    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->source);
    }


    public function offsetGet($offset)
    {
        return $this->get($offset);
    }


    public function offsetSet($offset, $value)
    {
    }


    public function offsetUnset($offset)
    {
    }
}
