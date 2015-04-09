<?php

/**
 * mfile.php
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */

namespace templr;

class mFile implements \ArrayAccess {

    const require_regex = "/^ *#require +([a-zA-Z\.]+)/m"; // #require filename.txt
    const section_regex = "/^(\[[^:]{0,10}(?:[^\]]+)+\])/m"; // [html:body]
    const tag_regex = "/\{([^:]{0,10}:[^\}]+)\}/"; // {text:name}
    const lisp_regex = "/^\(.*\) *$/m"; // (lisp_command things)

    private $_data = [];
    private $_view_root_path = '';

    public function __construct($view, $opts = []) {
        header("content-type: text/plain");
        $ext = ".php";
        $filename = '';
        foreach (Templr::ViewPath() as $dir) {
            $f = $dir . $view . $ext;
            if (file_exists($f)) {
                $filename = $f;
                $this->_view_root_path = $dir;
                break;
            }
        }
        $this->LoadFile($view);
    }

    public function LoadFile($filename) {

        // read in the file
        if (is_file($filename)) {
            ob_start();
            include $filename;
            $str = trim(ob_get_clean());
        }

        $blocks = \preg_split(self::section_regex, $str, null, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);

        // check if first block was definition, or file-scoped instructions
        if (preg_match(self::section_regex, $blocks[0]) === 1) {
            $file_info = array_shift($blocks);
            $this->_data['_file_header'] = $this->parse_block(NULL, $file_info);
        }

        // make sure we have an even number of 
        if (count($blocks) % 2 === 1) {
            print "ERROR : Misconfigured view file : $filename.";
            return "";
        }

        // store block on file-level
        while (count($blocks)) {
            $h = array_shift($blocks);
            $c = array_shift($blocks);
            $this->_data[$h] = $this->parse_block($h, $c);
        }
        
        $this->_str = $str;

        return "";
    }

    public function parse_block(\string $header, \string $content) {
        
    }

    public function Render($data) {
        print $this->_str;
    }

    public function offsetExists($offset) {
        
    }

    public function offsetGet($offset) {
        
    }

    public function offsetSet($offset, $value) {
        
    }

    public function offsetUnset($offset) {
        
    }

}
