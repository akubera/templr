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

class plisp_if extends \templr\plisp\PlispFunction {

    public function exec($args) {
      $ev = $args[0]();
      if (is_null($ev)) {
        return $args[2]();
      }
      
      $to_str = is_array($ev) ? '['.implode(',', $ev).']' :  "$ev";
      $eval_str = "return (" .$to_str . " or 0);";

      $res = (eval($eval_str) ? $args[1]() : $args[2]());
      $rstr = $res;
      print "-- plisp_if returning with ";
      print (is_callable($res, false, $rstr) ? $rstr : $res) . "\n";
      return $res;

//       $cmd = "return (". $args[0] . " ? true : false);";
//         echo "\n\nTesting '$cmd' => '{$args[1]}' '{$args[2]}'\n\n";
//         echo "::". eval($cmd) . "\n |" . (eval($cmd) ? $args[1] : $args[2]) . "\n\n";
        
//         return (eval($cmd)) ? $args[1] : $args[2]; // eval($args[0]. ";") ? $args[1] : $args[2];
    }

}
