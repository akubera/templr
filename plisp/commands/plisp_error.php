<?php
/*
 *  templr/plisp/commands/plisp_error.php
 *
 *  Prints an error message.
 *
 */

namespace templr\plisp\commands;

class plisp_error extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        echo "Error : ". implode(':',$args) ."\n";
        return new \templr\plisp\Plist();
    }
}
