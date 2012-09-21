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
    var $attributes = [];

    var $parent = null;
    var $text = '';

    public function __construct($type, $attributes = [], $text = "", $selfclose = false) {
        $this->type = $type;
        $this->selfclosing = $selfclose;
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        $this->addText($text);
    }

    public function addAttribute($attribute, $value = '') {
        $this->setAttribute($attribute, $value);
        return $this;
    }

    public function setAttribute($attribute, $value = '') {
        if (is_array($attribute)) {
            $this->attributes = array_merge($this->attributes, $attribute);
        } else {
            $this->attributes[$attribute] = $value;
        }
    }

    public function removeAttribute($attribute) {
        if (isset($this->attributes[$attribute])) {
            unset($this->attributes[$attribute]);
        }
    }

    public function addChild($child) {
        if (is_string($child)) {
            $child = HtmlElement::Text($child);
        }
        assert(get_class($child) === __class__);
        assert(!$this->selfclosing);
        assert($this->parent != $child);
        assert($child != $this);
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
        if ($this->type == '') {
            return $this->text;
        }
        $result = "<{$this->type}";
        foreach ($this->attributes as $key => $value) {
            $result .= " {$key}='{$value}'";
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
