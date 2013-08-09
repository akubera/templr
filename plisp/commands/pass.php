<?php
/*
 *  templr/plisp/commands/pass.php
 *
 *  Pass does nothing - just returns an empty Plist
 *  
 *
 *
 */

namespace templr\plisp\commands;

class Pass extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        return [];// new \templr\plisp\Plist();
    }
}
