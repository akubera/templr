<?php
/*
 *  templr/plisp/commands/ifset.php
 *
 *  Ifset returns true if a variable identified by the first argument exists, false if it doesn't  
 *
 *
 */

namespace templr\plisp\commands;

class Ifset extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        return new \templr\plisp\Plist(false);
    }
}
