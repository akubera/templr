<?php
/*
 *  templr/plisp/commands/all.php
 *
 *  All evaluates and returns all arguments - used for the 'top' 
 *    calling function which executes each argument 
 */

namespace templr\plisp\commands;

class All extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        $result = [];
        foreach ($args as $next) {
          $result[] = $next->Eval();
        }
        return new \templr\plisp\Plist($result);
    }
}
