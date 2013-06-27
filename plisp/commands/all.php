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
          if (is_callable($next)) {
            $result[] = $next();
          } else {
            $result[] = $this->plisp->magic($next);
          }
        }
        return count($result) === 1 ? $result[0] : $result;
    }
}
