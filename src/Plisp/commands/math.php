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

use templr\plisp\PLISP;

class Math extends \templr\plisp\PlispFunction {

    protected $c_command = "";
    static public $DEBUG = true;
    protected $math_functions = [
        "+" => "plisp_add", "sum" => "plisp_add", "add" => "plisp_add",
        "-" => "plisp_minus", "diff" => "plisp_minus",
        "*" => "plisp_mult", "multiply" => "plisp_mult",
        "/" => "plisp_divide", "divide" => "plisp_divide"
    ];

    // Looks for a registered math function named '$function' and 
    //  set the current function $c_command to that string
    //
    public function SetCommand($function) {
        PLISP::BeginSub(__METHOD__);
        print "Math::SetCommand : $function ... ";
        if (isset($this->math_functions[$function])) {
            $this->c_command = $this->math_functions[$function];
            print "found :  {$this->c_command}\n";
            PLISP::EndSub();
            return true;
        }
        print "not found\n";
        PLISP::EndSub();
        return false;
    }

    public static function CreateWithCommand($plisp, $func) {
        PLISP::BeginSub(__METHOD__);
        print "create with command : $func\n";
        $math = new Math($plisp);
        $res = $math->SetCommand($func) ? $math : null;
        PLISP::EndSub();
        return $res;
    }

    public function exec($args) {
        PLISP::BeginSub(__METHOD__);
        $c = __namespace__ . '\\' . $this->c_command;
        if (Math::$DEBUG)
            print "Math::exec $c\n";
        // only try to run $c if the function exists
        $res = function_exists($c) ? $c($args) : null;
        if (Math::$DEBUG)
            print "returning from exec\n";
        PLISP::ReturnSub($res);
        PLISP::EndSub();
        return $res;
    }

}

function plisp_add($list) {
    PLISP::BeginSub(__METHOD__);
    if (Math::$DEBUG)
        print "Math::plisp_add\n";

    $sum = 0;
    foreach ($list as $x) {
        $X = $x();
        if (Math::$DEBUG)
            print " + $X\n";

        // evaluate 'x' and add result to sum
        $sum += $X; // $el->eval();
    }
    if (Math::$DEBUG)
        print " = $sum\n";
    if (Math::$DEBUG)
        print "Math::plisp_add DONE\n";

    PLISP::EndSub();
    return function() use ($sum) {
                return $sum;
            };
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
