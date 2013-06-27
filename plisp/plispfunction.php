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
    protected static $func_registry = ["=" => "plisp_assign",
                              "set" => "plisp_assign",
                           "assign" => "plisp_assign",
                               "if" => "plisp_if",
                             "echo" => "plisp_echo"];

    abstract public function exec($args);
    protected $plisp = null;
    
    
    public function __construct($plisp) {
      $this->plisp = $plisp;
    }  

    public function getHtml($template) {
      
    }
    
    static public function Create($plisp, $name) {    
      $classname = __namespace__ . "\\commands\\$name";
      $res = null;

       if (isset(PlispFunction::$func_registry[$name])) {
          $cname =  __namespace__ . "\\". PlispFunction::$func_registry[$name];
          if (class_exists($cname)) {
             $res = new $cname($plisp);
          } else {
            die( "Error! Unimplemented PlispFunction class : $name. Please add a class with an 'exec' method to namespace " . __namespace__. "\n");
          }
      } else if (class_exists($classname)) {
         $res = new $classname($plisp);
      } else {
        // check if it's stored in the math class
        $res = commands\Math::CreateWithCommand($plisp, $name);
      }

      if ($res === null) {
          print "Error $class not found \n";
      }
      
      
      return $res;
    }

}

class plisp_assign extends \templr\plisp\PlispFunction {
  public function exec($arg) {
    print "\nsetting  $arg[0] to  $arg[1] \n";
    $this->plisp->set($arg[0], $arg[1]);
    return  $args[1];
  }
}

class plisp_if extends \templr\plisp\PlispFunction {

    public function exec($args) {
        return eval($args[0]. ";") ? $args[1] : $args[2];
    }

}

class plisp_echo extends \templr\plisp\PlispFunction {

    public function exec($args) {
        foreach ($args as $arg) {
          if ($arg[0] === '$' || $arg[0] === '&') {
              echo $this->plisp->get($arg);
          } else {
            echo $arg;
          }
            echo " ";
        }
//         echo implode(' ' , $arg);
        return "";
    }

}