<?php
/*
 *  templr/plisp/first.php
 *
 *  First returns the 
 *
 *
 *
 */

namespace templr\plisp\commands;

class First extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        return new \templr\plisp\Plist($args[0]);
    }
}
