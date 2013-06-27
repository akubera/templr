<?php
/*
 *  templr/plisp/commands/plisp_assign.php
 *
 *  Assigns a value $arg[1] to a variable $arg[0]
 *  
 *
 *
 */

namespace templr\plisp\commands;

class plisp_assign extends \templr\plisp\PlispFunction {
  public function exec($arg) {
//     print "\nsetting  $arg[0] to  $arg[1] \n";
    $A0 = $arg[0]();
    $A1 = $arg[1]();
    $this->plisp->set($A0, $A1);
    return $A1;
  }
}