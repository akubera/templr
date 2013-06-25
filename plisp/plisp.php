<?php
/*
 * plisp/plisp.php
 */

namespace templr\plisp;

global $plisp_registry;
$plisp_registry = [];

require_once 'list.php';
require_once 'token.php';

class PLISP {

    const regex = "/^\(((?>[^()]+)|(?:R))*\) *$/"; // (function arg1 arg2)


    public function __construct() {

    }

    /**
     * 
     * @param string $str
     * @return array 
     */
    static public function tokenize($str) {
        $res = [];
        
        $str;
        return $res;
    }
}
