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

use \templr\plisp\PLISP;

class plisp_assign extends \templr\plisp\PlispFunction {
  public function exec($arg) {
//     print "\nsetting  $arg[0] to  $arg[1] \n";
      PLISP::BeginSub(__METHOD__);
        print "Running arg0 $arg\n";
    $A0 = $arg[0]();
    while (!is_a($A0, "templr\plisp\plispvariable") && is_callable($A0)) {
        $A0 = $A0();
    }
    print "--Done\n";
    print "--Running arg1\n";
    $A1 = $arg[1]();
    print "--Done\n";

    while (is_callable($A1)) {
        $A1 = $A1();
    }

    print "--Assigning $A1 to $A0\n";

    $this->plisp->set($A0, $A1);
          PLISP::EndSub();
    
    return $A1;
  }
}