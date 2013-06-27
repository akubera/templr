<?php
/*
 *  templr/plisp/commands/plisp_not.php
 *
 *  returns logical 'not' of the first argument
 */

namespace templr\plisp\commands;

class plisp_not extends \templr\plisp\PlispFunction
{
    public function exec($arg) {
        return new \templr\plisp\Plist(!$arg[0] ? 1 : 0);
    }
}
