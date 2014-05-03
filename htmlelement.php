<?php

/**
 *  templr/HtmlElement.php
 *  Andrew Kubera (andrewkubera@gmail.com)
 *
 *  Basic class to programmatically create and print html elements
 */

namespace templr;

class HtmlElement {

    public $type;
    var $children = [];
    var $parent = null;
    var $text = '';
    static $default_tag_name = "div";
    protected $attributes = [];

    public function __construct($type, $attributes = [], $text = "", $selfclose = false) {
        if (type === "") {
            throw new Exception("Type must NOT be empty string.");
        }
        // Removes an id from the tag name (i.e. "span#my_id")
        $id = $this->extractID($type);

        // Removes all classes and stores into a list
        $classes = $this->extractClasses($type);

        if ($id !== null) {
            $this->setAttribute('id', $id);
        }

        if ($classes !== []) {
            $this->AddClasses($classes);
        }

        $this->type = $type;
        $this->selfclosing = $selfclose;
        if ($attributes) {
            $this->setAttribute($attributes);
        }
        if ($text) {
            $this->addText($text);
        }
    }

    private function extractID(&$str) {
        $id = NULL;

        // Break type string around the '#'
        $a = explode("#", $str);

        // If we lead with #idname use default type (auto div)
        if ($a[0] === "" && $a[1]) {
            $a[0] = self::$default_tag_name;
        }

        // a[1] now has everything after '#' char
        if ($a[1]) {
            // $break is now index of delimeter
            $break = strpos($a[1], '.');

            //  no delimeter found, copy everything to id
            if ($break === false) {
                $id = $a[1];
                $str = $a[0];
            } else {
                $id = substr($a[1], 0, $break);
                $str = $a[0] . substr($a[1], $break);
            }
        }
        return $id;
    }

    private function extractClasses(&$str) {
        $class_list = explode('.', $str);
        $str = array_shift($class_list);
        return $class_list;
    }

    public function AddClass($class_name) {
        if (!$this->attributes['class']) {
            $this->attributes['class'] = [];
        }
        $this->attributes['class'][] = $class_name;
        return $this;
    }

    public function AddClasses($classes) {
        if (!$this->attributes['class']) {
            $this->attributes['class'] = [];
        }
        $this->attributes['class'] += $classes;
        return $this;
    }

    /**
     *
     * @param String $class_name
     */
    public function RemoveClass($class_name) {
        $this->attributes['class'] = array_diff($this->attributes['class'], [$class_name]);
        return $this;
    }

    /**
     *
     * @param Array $classes
     */
    public function RemoveClasses($classes) {
        $this->attributes['class'] = array_diff($this->attributes['class'], $classes);
        return $this;
    }

    public function addAttribute($attribute, $value = '') {
        if ($attribute === 'class') {
            $this->AddClass($value);
        }
        $this->setAttribute($attribute, $value);
        return $this;
    }

    public function setAttribute($attribute, $value = '') {
        if (is_array($attribute)) {
            foreach ($attribute as $key => $value) {
                $this->setAttribute($key, $value);
            }
        } else if ($attribute === 'class') {
            $this->AddClass($value);
        } else {
            $this->attributes[$attribute] = $value;
        }
        return $this;
    }

    public function removeAttribute($attribute) {
        if (isset($this->attributes[$attribute])) {
            unset($this->attributes[$attribute]);
        }
        return $this;
    }

    public function addChild($child) {
        if (is_string($child)) {
            $child = HtmlElement::Text($child);
        }
        assert(get_class($child) === __class__);
        assert(!$this->selfclosing);
        assert($this->parent != $child);
        assert($child !== $this);
        assert($child !== null);
        $child->parent = $this;
        array_push($this->children, $child);
        return $child;
    }

    public function addToParent($parent) {
        $parent->addchild($this);
    }

    public function setText($text) {
        $this->text = (string) $text;
    }

    public function addText($text) {
        $this->text .= (string) $text;
    }

    public function __toString() {
        if ($this->type === '') {
            return $this->text;
        }
        $result = "<{$this->type}";

        // compress the array of classes to a string
        if (isset($this->attributes['class']) && $this->attributes['class']) {
            $tmp_classlist = $this->attributes['class'];
            $this->attributes['class'] = implode(' ', $tmp_classlist);
        }
        foreach ($this->attributes as $key => $value) {
            $result .= " {$key}='{$value}'";
        }
        // return classes to the stored array
        if (isset($this->attributes['class'])) {
            $this->attributes['class'] = $tmp_classlist;
        }

        if ($this->selfclosing) {
            $result .= " />";
        } else {
            $result .= ">";
            $result .= $this->text;
            foreach ($this->children as $child) {
                assert($child->parent === $this);
                $result .= $child;
            }
            $result .= "</{$this->type}>\n";
        }

        return $result;
    }

    static function BR() {
        return new HtmlElement("br", 0, 0, true);
    }

    static function HR() {
        return new HtmlElement("hr", 0, 0, true);
    }

    static function Link($location = null, $txt = '') {
        $a = new HtmlElement("a", 0, $txt);
        if ($location != null) {
            $a->setAttribute("href", $location);
        }
        return $a;
    }

    static function LocalLink($location = null, $txt = '') {
        $a = new HtmlElement("a", 0, $txt);
        if ($location != null) {
            $a->setAttribute("href", "%%/{$location}");
        }
        return $a;
    }

    static function Text($txt) {
        $a = new HtmlElement("", 0, $txt);
        return $a;
    }

}

require_once 'init.php';
