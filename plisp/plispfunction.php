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
    protected static $func_aliases = [  "=" => "plisp_assign",
                                      "set" => "plisp_assign",
                                   "assign" => "plisp_assign",
                                       "if" => "plisp_if",
                                     "echo" => "plisp_echo",
                                      "not" => "plisp_not",
                                    "error" => "plisp_error",
                                  "foreach" => "plisp_each"];

    abstract public function Exec($args);
    protected $plisp = null;
    
    
    public function __construct($plisp) {
      $this->plisp = $plisp;
    }  
    
    public function __invoke($args) {
        return $this->Exec($args);
    }
    
    static public function Create($plisp, $name) {    
        print "creating plispfunction $name\n";
        $res = null;

        // check if $name provided is an alias to a function - replace with that name
        if (isset(PlispFunction::$func_aliases[$name])) {
            $name = PlispFunction::$func_aliases[$name];
        }

        // $classname is the proposed name of the command - findable in the "commands" subdirectory
        $classname = __namespace__ . "\\commands\\$name";

        // check if we have our class
        if (class_exists($classname)) {
            $res = new $classname($plisp);
        } 

        // we did not find the class - check in the math 'registry'
        if ($res === null) {
            $res = commands\Math::CreateWithCommand($plisp, $name);
        }
        
        // we don't know waht to do with $name - throw error
        if ($res === null) {
            throw new \Exception("Error! Unimplemented PlispFunction class '$name'. Please add a class with an 'exec' method to namespace " . __namespace__. "\\commands.\n");
        }

        return $res;
    }
    
    static public function CreateAndRunList($plist) {
        $f = PlispFunction::Create($plist->plisp, $plist->head);
        return $f($plist);
    }

}
