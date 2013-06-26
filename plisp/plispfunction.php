<?php
/*
 *  plisp/plispfunction.php
 *
 *  
 *
 *
 */
 
 namespace templr\plisp;
 
 // Declare the interface 'iTemplate'
abstract class PlispFunction
{
    abstract public function exec($args);

    public function getHtml($template) {
      
    }
    
    static public function Create($name) {    
      $classname = __namespace__ . "\\commands\\$name";
      $res = null;

      if (class_exists($classname)) {
         $res = new $classname();
      } else {
        // check if it's stored in the math class
        $res = PlispFunction::LoadMathClass($name);
      }

      if ($res === null) {
          print "Error $class not found \n";
      }
      
      
      return $res;
    }
    
    static protected function LoadMathClass($name) {
      $math = new commands\Math();
        if ($math->SetCommand($name)) {
          return $math;
        }
    }
    
    
}
