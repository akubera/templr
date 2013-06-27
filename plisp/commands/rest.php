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
        $result = ['all'];
        for ($i = 1; $i < count($args); $i++) {
          $result[] = $args[$i]();
        }
        return new \templr\plisp\plist($result);
    }
}
