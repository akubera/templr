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
          //  
          while (is_callable($next)) {
            print "calling : $next\n";
            $next = $next();
            print "returned : $next\n";
          }
          
           $result[] = $this->plisp->get($next);
        }
        return count($result) === 1 ? $result[0] : $result;
    }
}
