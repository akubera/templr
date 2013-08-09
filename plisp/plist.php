<?php
/*
 *  plisp/plist.php
 *
 *
 */

namespace templr\plisp;

class Plist implements \ArrayAccess, \Iterator, \Countable {
    // a static reference to a plisp environment - there should only be one, 
    //  but we optionally leave this here to allow more.
    static protected $recent_plisp = null;
    
    static public $DEBUG = true;

    // the raw init array;
    protected $_data = [];

    // the command
    public $head = '';

    // the evaluated objects
    protected $_args = [];

    // associated plisp environment
    public $plisp = null;

    // array_access index
    protected $_index = 0;

    // if _is_execuable we must invoke the plisp command with the first element of the array
    // if not, return the array as a bunch of data
    protected $_is_executable = true;
    
    /*
     * Build a plist out of a list of things, associate with a plisp object if one hasn't been
     * set to the static memember recent_plisp, not expected to have more than one plisp environment
     * but this parameter allows for non-singleton use. 
     * 
     */
    function __construct($list = [], $plisp = null) {
        // we have a new recent plisp
        if (is_a($plisp, "\templr\plisp\plisp")) {
          Plist::$recent_plisp = $plisp;
        }

        // set the plisp member to the most recent plisp, perhaps just set a few lines before
        $this->plisp = Plist::$recent_plisp;

        // We are trying to create a list from a single number or null, we are not executable
        // proceed to build from a single-element array
        if (is_numeric($list) or is_null($list)) {
            $this->_build_from_array([$list]);        
        } else if (is_array($list)) {

            if (PLIST::$DEBUG) print "building plist from list : [". implode(",", $list)."]\n";

            // empty list is equivalent to null list
            if (count($list) === 0 || (count($list) === 1 && $list[0] === "")) {
                $list = [null];
            }

            $this->_build_from_array($list);
        } else if (is_string($list)) {
          if (PLIST::$DEBUG) print "building plist from string : '$list'\n";
          $list = preg_split("/[\s]+/", $list, -1, PREG_SPLIT_NO_EMPTY); //explode(' ', $list);

          if (count($list) === 0) {
              $list = [null];
          }
          $this->_build_from_array($list);
        } else {
          die ("Unkown plist initiating object of class '" . get_class($list) . "'\n");
        }
        $this->plisp_reference_id = $this->plisp->RegisterId($this);
        if (PLIST::$DEBUG) print "Created $this->plisp_reference_id = ($this)\n";
    }

    /*
     * Called from constructor - accepts an array of data
     */
    private function _build_from_array($list) {
//         assert(!is_numeric($list[0]), "building plist with number head");
        assert(count($list) !== 0, "Attempting to build PLIST from empty list - should have been set to null!");
        
        // No function may be identified by a number or 'null'
        if (is_numeric($list[0]) or is_null($list[0])) {
            $this->_is_executable = false;
        }
        
        // _data is a copy of initilization list
        $this->_data = $list;

        // remove first item from list and store as 'head'
        $this->head = array_shift($list);

        // if head is a reference - 'get' from plisp and execute
        while ($this->head[0] === '&') {
            if (PLIST::$DEBUG) print "building from array loop:\n";
            $x = $this->plisp->Get($this->head);
            $this->head = $x();
        }

        // don't bother evaluating yet - only do what you have to!
        $this->_args = $list;
    }

    // 
    public function __invoke($exec_all = true) {
        if (!$exec_all) {
          return $this->_data;
        }
        // evaluate this list
//         $f = PlispFunction::Create($this->plisp, $this->head);
//         return $f->Exec($this);
        if ($this->_is_executable) {
          ob_start(function ($buffer) { return preg_replace('/\n/', "\n|    ", $buffer);});
          $res = PlispFunction::CreateAndRunList($this);
          ob_end_flush();
        } else {
          $dat = $this->_data;
          $res = function () use ($dat) {return $dat;};
        }
        return $res;
    }
    
    function __tostring() {
        if (count($this->_data) === 0) {
            die("PLIST Error : Completely empty plist ({$this->plisp_reference_id})");
        }

        $str = $this->_data[0] === null ? "null" : "'{$this->_data[0]}'";
            
        if (count($this->_data) === 1) {
            return $str;
        }
        
        foreach (array_slice($this->_data, 1) as $s) {
            if (is_null($s)) {// === null) {
                $str .= " null";
            } else {
                $str .= " '" . $s . "'";
            }
        }
//      $str = implode(' ', \array_map('trim', $this->_data));
      return $str;
    }
    
    function PrintOut() {
      print "($this)p\n";
    }

    static public function SetPlisp(plisp $plisp) {
        plist::$recent_plisp = &$plisp;
    }
    
    function GetData() {
      return $this->_data;
    }
        
    static function GenerateFromString($str, $plisp) {
      // remove all extraneous whitespace
      $str = preg_replace(['/\([\s]+/', '/[\s]+\)/', '/[\s]+/'], ['(', ')', ' '], trim($str));
      
      assert($str[0] === '(', "PLisp command does not start with '(' character!");

      // set it and forget it!
      PList::SetPlisp($plisp);
      $res = PList::RecursiveCreation($str);

        return $res;
    }

    
    /**
     * Recursively create all plists in the string '$str'
     * 
     * @param string $str string with which to build the plist
     * @param type $plisp if plisp has not been set - set it here
     * 
     * @return \templr\plisp\Plist
     */
    static protected function RecursiveCreation($str, $plisp = null) {
      if (PLIST::$DEBUG) print "RecursiveCreation: $str\n";

      $match = [];

      // Get first matching 'simple' command
      preg_match("/\([^\)\(]*\)/", $str, $match, PREG_OFFSET_CAPTURE);

      // entire simple sublist (including '(' & ')')
      $sublist_str = $match[0][0];
      $offset = $match[0][1];

      // this should never happen but if it evaluates to false, die
      if (!$sublist_str) {
          die ("plist error with line $str");
      }
      
      // explode the string and create a new list
      $sublist = preg_split("/[\s]+/", substr($sublist_str, 1, -1), -1, PREG_SPLIT_NO_EMPTY); //explode(' ', substr($sublist_str, 1, -1));
        ob_start(function ($buffer) { return preg_replace('/\n/', "\n   ", $buffer);});
      
      $new_plist = new Plist($sublist, $plisp);
      // we have reached the end! return our $new_plist
      if ($offset === 0) {
        return $new_plist;
      }

      // we are still somewhere in the middle - replace this sublist with a reference      
      $new_str = substr_replace($str, $new_plist->plisp_reference_id, $offset, strlen($sublist_str));
        ob_end_flush();
      $res = PList::RecursiveCreation($new_str, $plisp);

      return $res;
    }
 
    public function Slice(int $offset, int $length = NULL) {
      return new Plist(array_slice($this->_data, $offset, $length));
    }

    // Array Functions

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_args[] = $value;
        } else {
            $this->_args[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->_args[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_args[$offset]);
    }

    // get item at '$offset'
    public function offsetGet($offset) {

        $res = isset($this->_args[$offset]) ? $this->_args[$offset] : null;
        if (is_numeric($res)) {
            $res = floatval($res);
        } else 
        if ( $x = $this->plisp->Get($res) ) {
           if (PLIST::$DEBUG) print "[$this->plisp_reference_id] getting item at offset '$offset'\n";

          if (is_callable($x)) { 
            return $x;
          }
          $res = $x;
        }
        return function() use ($res) {return $res;};
    }
    
    
    // iterator functions
    public function rewind() {
      $this->_index = 0;
    }
    
    public function current() {
      return $this[$this->_index];
    }
    public function key(){
      return $this->_index;
    }

    public function next(){
        return isset($this[++$this->_index]) ?  $this[$this->_index] : false;
    }
    
    public function valid() {
      return isset($this[$this->_index]);// ($this->_index < count($this->_args));// ;
    }
    
    public function count() {
       return count($this->_args);
    }

}
