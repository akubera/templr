<?php
/*
 *  templr/plisp/commands/plisp_echo.php
 *
 *  Print a string to standard output
 *
 */

namespace templr\plisp\commands;


class plisp_echo extends \templr\plisp\PlispFunction {

    public function exec($args) {
        foreach ($args as $arg) {
          if ($arg[0] === '$' || $arg[0] === '&') {
              echo $this->plisp->get($arg);
          } else {
            echo $arg;
          }
            echo " ";
        }
//         echo implode(' ' , $arg);
        return new \templr\plisp\Plist();
    }

}