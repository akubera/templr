<?php   
/*
 *  templr/plisp/commands/math.php
 *
 *  Math class handles all mathematical functions in plsip.
 *  If command is not found, this class will be checked to  
 *   see if it handles the name.
 *
 */
 
namespace templr\plisp\commands;

class Math extends \templr\plisp\PlispFunction {
    protected $c_command = "";

  
    protected $math_functions = [
                                  "+" => "plisp_add","sum" => "plisp_add","add" => "plisp_add",
                                  "-" => "plisp_minus","diff" => "plisp_minus",
                                  "*" => "plisp_mult","multiply" => "plisp_mult",
                                  "/" => "plisp_divide", "divide" => "plisp_divide"
                                ];

    // Looks for a registered math function named '$function' and 
    //  set the current function $c_command to that string
    //
    public function SetCommand($function) {
        if(isset($this->math_functions[$function])) {
          $this->c_command = $this->math_functions[$function];
          return true;
        }
        return false;
    }

    public static function CreateWithCommand($plisp, $func) {
      $math = new Math($plisp);
      return $math->SetCommand($func) ? $math : null;
    }

    public function exec($args) {
        $c = __namespace__.'\\'.$this->c_command;

        // only try to run $c if the function exists
        return function_exists($c) ? $c($args) : null;
    }
}


function plisp_add($list) {
    $sum = 0;
    foreach ($list as $el) {
        $sum += $el;// $el->eval();
    }
    return $sum;
}

function plisp_minus($list) {
    $diff = \array_shift($list)->eval();
    foreach ($list as $el) {
        $diff -= $el->eval();
    }
    return $diff;
}
function plisp_mult($list) {
    $product = 1;
    foreach ($list as $el) {
        $product *= $el->eval();
    }
    return $product;
}
