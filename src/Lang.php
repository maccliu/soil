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
    public $lang_root = null;
    public $lang_file = null;
    public $source_lang = null;
    public $target_lang = null;
    public $source = [];
    public $target = [];
    private $autosave = false;
    private $source_changed = false;
    private $target_changed = false;
    private $loaded  =false;

    const DEFAULT_LANG = 'en_US';


    public function __construct($lang_root = null, $source_lang = null, $lang_file = null)
    {
        $this->setLangRoot($lang_root);
        $this->setLangFile($lang_file);
        $this->setSourceLang($source_lang);
    }


    public function setLangRoot($lang_root)
    {
        if (is_string($lang_root) && file_exists($lang_root) && is_dir($lang_root)) {
            $this->lang_root = realpath($lang_root);
        } else {
            $this->lang_root = null;
        }
        return $this;
    }


    public function setLangFile($lang_file)
    {
        if (is_string($lang_file) && $lang_file !== '') {
            $this->lang_file = $lang_file;
        } else {
            $this->lang_file = null;
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
        return $this;
    }


    public function setTargetLang($lang)
    {
        if (is_string($lang)) {
            $this->target_lang = $lang;
        } else {
            $this->target_lang = null;
        }
        return $this;
    }


    public function get($text)
    {
        if (!$this->loaded) {
            $this->load();
        }

        if (!array_key_exists($text, $this->source)) {
            $this->source[$text] = true;
            $this->source_changed = true;
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


    public function autosave($bool)
    {
        $this->autosave = $bool;
        return $this;
    }


    public function __destruct()
    {
        if (!$this->autosave) {
            return;
        }

        if (is_null($this->lang_root) || is_null($this->lang_file)) {
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


    private function loadLang($lang, &$array)
    {
        $array = [];

        $root = $this->lang_root;
        if (is_null($root) || !file_exists($root) || !is_dir($root)) {
            return;
        }

        if (!is_string($lang) || ($lang === '') || !file_exists("$root/$lang") || !is_dir("$root/$lang")) {
            return;
        }

        $file = $this->lang_file;
        if (!is_string($file) || $file === '') {
            return;
        }

        $path = "$root/$lang/$file";
        if (!file_exists($path) || !is_file($path)) {
            return;
        }

        $array = require($path);
    }


    /**
     * @param string  $lang
     * @param array   $array
     *
     * @return boolean
     */
    private function saveLang($lang, &$array)
    {
        $lang_root = $this->lang_root;
        if ($lang_root === null) {
            return false;
        }

        $lang_file = $this->lang_file;
        if ($lang_file === null) {
            return false;
        }

        $path = "{$lang_root}/{$lang}/{$lang_file}";
        $dir = dirname($path);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777);
        }
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }

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
