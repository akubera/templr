<?php
/*
 *  templr/plisp/commands/all.php
 *
 *  All evaluates and returns all arguments - used for the 'top' 
 *    calling function which executes each argument 
 */

namespace templr\plisp\commands;

use \templr\plisp\PLISP;


class All extends \templr\plisp\PlispFunction
{
    public function exec($args) {
                      PLISP::BeginSub(__METHOD__);
        $spacer = "       ";
        print count($args) . " Arguments\n";
        $result = [];
        $i = 0;
        foreach ($args as $next) {
            $cname = '';
            // loop through the element while it's still some kind of
            //  function until it returns a data value 
            while (is_callable($next, false, $cname)) {
//              ob_start(function ($buffer) use ($spacer) { return "\n{$spacer}" . preg_replace('/\n/', "\n{$spacer}",  trim($buffer)) . "\n";});

              $next = $next();

            }
            if ( is_array($next) ) {
                if (count($next) === 0) {
                    $result[] = null;
                } else
                if (count($next) === 1) {
                    $result[] = $next[0];
                } else {
                    $result[] = $next;
                }
            } else {
                $result[] = $this->plisp->get($next);
            }
        }
        $res = count($result) === 1 ? $result[0] : $result;
        var_dump($res);
        PLISP::EndSub();
        return $res;
    }
}
