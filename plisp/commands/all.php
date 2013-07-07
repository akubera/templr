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
              $cname = '';
            // loop through the element while it's still some kind of
            //  function until it returns a data value 
            while (is_callable($next, false, $cname)) {
//                print "Calling $cname();\n";
              $next = $next();
            }
          
            $result[] = $this->plisp->get($next);
        }
        return count($result) === 1 ? $result[0] : $result;
    }
}
