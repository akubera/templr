<?php

/*
 *  templr/plisp/commands/plisp_if.php
 *
 *  If conditional test for plisp
 *    returns first argument if true
 *    second arument if false
 *
 */

namespace templr\plisp\commands;

use templr\plisp\PLISP;

class plisp_if extends \templr\plisp\PlispFunction {

    public function exec($args) {
        PLISP::BeginSub(__METHOD__);
        print "plisp_if : executing first argument \n";
        $arg0 = $args[0];
//      ob_start(function ($buffer) { return "   ". preg_replace('/\n/', "\n   ", trim($buffer)) . "\n";});
        $ev = $arg0();
        ob_end_flush();
        if (is_null($ev)) {
            PLISP::EndSub();
            return $args[2]();
        }

        $to_str = is_array($ev) ? '[' . implode(',', $ev) . ']' : "$ev";
        $eval_str = "return (" . $to_str . " or 0);";

        $res = (eval($eval_str) ? $args[1]() : $args[2]());
        $rstr = $res;
        print "-- plisp_if returning with ";
        print (is_callable($res, false, $rstr) ? $rstr : (is_null($res)) ? "null" : $res) . "\n";
        PLISP::EndSub();
        return $res;

//       $cmd = "return (". $args[0] . " ? true : false);";
//         echo "\n\nTesting '$cmd' => '{$args[1]}' '{$args[2]}'\n\n";
//         echo "::". eval($cmd) . "\n |" . (eval($cmd) ? $args[1] : $args[2]) . "\n\n";
//         return (eval($cmd)) ? $args[1] : $args[2]; // eval($args[0]. ";") ? $args[1] : $args[2];
    }

}
