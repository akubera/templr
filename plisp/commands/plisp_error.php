<?php
/*
 *  templr/plisp/commands/plisp_error.php
 *
 *  Prints an error message.
 *
 */

namespace templr\plisp\commands;

class plisp_error extends \templr\plisp\PlispFunction
{
    public function exec($args) {
      $errmsg = "Error : ";
      foreach ($args as $msg) {
        $errmsg .= " " .$msg();
      }
        echo $errmsg .   "\n";
        return null;
    }
}
