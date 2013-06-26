<?php
/*
 *  templr/plisp/commands/rest.php
 *
 *  Rest returns all but the first element of the list provided
 *  
 *
 *
 */

namespace templr\plisp\commands;

class Rest extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        return new \templr\plisp\Plist(array_slice($args, 1));
    }
}
