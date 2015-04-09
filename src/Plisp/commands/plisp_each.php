<?php
/*
 *  templr/plisp/commands/plisp_each.php
 *
 *  'Each' executes the first element with each of the following 
 *    elements as a parameter
 */

namespace templr\plisp\commands;

class Each extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        return new \templr\plisp\Plist();
    }
}
