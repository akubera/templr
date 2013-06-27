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
      $cmd = "return (". $args[0] . " ? true : false);";
//         echo "\n\nTesting '$cmd' => '{$args[1]}' '{$args[2]}'\n\n";
//         echo "::". eval($cmd) . "\n |" . (eval($cmd) ? $args[1] : $args[2]) . "\n\n";
        
        return (eval($cmd)) ? $args[1] : $args[2]; // eval($args[0]. ";") ? $args[1] : $args[2];
    }

}
