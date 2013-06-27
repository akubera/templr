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
      $str = "";
        foreach ($args as $arg) {
          $str .= $arg();
        }
        echo $str . "\n";
//         echo implode(' ' , $arg);
        return [];
    }

}