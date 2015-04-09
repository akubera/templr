<?php
/*
 *  templr/plisp/commands/append.php
 *
 *  Append creates a new list by joining all arguments into a new list
 */

namespace templr\plisp\commands;

class Append extends \templr\plisp\PlispFunction
{
    public function exec($args) {
        $result = [];
        foreach ($args as $next) {
          if (is_a($next, "\\templr\\plisp\\plist")) {
            foreach ($next->GetData() as $x) {
              $result[] = $x;
            }
          } else {
            $result[] = $next;
          }
        }
        print "Appended array : [".implode(',', $result) ."]\n";
        return new \templr\plisp\plist($result);
    }
}
