<?php
/*
 *  templr/plisp/extend.php
 *
 *  Extend is a templr command which finds the file(s) specified by the arguements
 *   and loads them for rendering *before* the current file is loaded.
 *
 */

namespace templr\plisp\commands;

class Extend extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        $files = [];
        foreach ($args as $filename) {
          if (!isset($files[$filename])) {
            print "Loading {$filename}\n";
            $files[$filename] = new \templr\Webpage($filename);
          }
        }
        
        return new \templr\plisp\Plist([]);
    }
}
