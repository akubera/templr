<?php

/*
 *  templr/plisp/extend.php
 *
 *  Extend is a templr command which finds the file(s) specified by the arguements
 *   and loads them for rendering *before* the current file is loaded.
 *
 */

namespace templr\plisp\commands;

use \templr\plisp\PLISP;

class Extend extends \templr\plisp\PlispFunction {

    static $loaded_files = [];

    public function exec($args) {
        PLISP::BeginSub(__METHOD__);
        // Loop through each element
        foreach ($args as $arg) {
            $filename = $arg();
            if (is_array($filename)) {
                foreach ($filename as $fname) {
                    $this->load_file($fname);
                }
            } else if (is_string($filename)) {
                $this->load_file($filename);
            } else {
                $this->load_file("$filename");
            }
        }
        PLISP::EndSub();
        return [true]; //new \templr\plisp\Plist();
    }

    public function load_file($filename) {
        $filename = trim($filename, " \t\n\"'");
        if (isset(Extend::$loaded_files[$filename])) {
            return;
        }
        if (\templr\plisp\PlispFunction::$DEBUG)
            print "Loading {$filename}\n";
        $wp = new \templr\Webpage($filename);
        Extend::$loaded_files[$filename] = $wp->Render();
    }

}
