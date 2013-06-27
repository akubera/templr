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

    // the raw init array;
    protected $_data = [];

    // the command
    public $head = '';

    // the evaluated objects
    protected $_args = [];
    
    public $plisp = null;
    
    protected $_index = 0;
    protected $_is_executable = true;
    
    function __construct($list = [], $plisp = null) {
        // we have a new recent plisp
        if (is_a($plisp, "\templr\plisp\plisp")) {
          Plist::$recent_plisp = $plisp;
        }

        // set the plisp member to the most recent plisp, perhaps just set a few lines before
        $this->plisp = Plist::$recent_plisp;

        if (is_numeric($list) or is_null($list)) {
//           die ("Initiating plist with number : $list \n");
          $this->_build_from_array([$list]);        
        } else if (is_array($list)) {
          print "building from list : [". implode(",", $list)."]\n";
          $this->_build_from_array($list);
        } else if (is_string($list)) {
          print "building from string : '$list'\n";
          $this->_build_from_array(explode(' ', $list));        
        } else {
          die ("Unkown plist initiating object of class '" . get_class($list) . "'\n");
        }
        $this->plisp_reference_id = $this->plisp->RegisterId($this);
    }
    
    private function _build_from_array($list) {
//         assert(!is_numeric($list[0]), "building plist with number head");
//         assert(count($list) !== 0, "Attempting to build PLIST from empty list!");
        $this->_data = $list;

        // remove first item from list and store as 'head'
        $this->head = array_shift($list);

        // Number is first element - this is not an executable list
        if (is_numeric($this->head)) {
            $this->_is_executable = false;
        }

        // run head if necessary
        while ($this->head[0] === '&') {
          $x = $this->plisp->Get($this->head);
          $this->head = $x();
        }
        // don't bother evaluating yet - only do what you have to!
        $this->_args = $list;
        
        print "created plist : ($this)\n";
    }
    
    public function __invoke() {
        // evaluate this list
//         $f = PlispFunction::Create($this->plisp, $this->head);
//         return $f->Exec($this);
        if ($this->_is_executable) {
          $res = PlispFunction::CreateAndRunList($this);
        } else {
          $dat = $this->_data;
          $res = function () use ($dat) {return $this->_data();};
        }
        $res = $this->_data;
        return $res;
    }
    
    function __tostring() {
      $str = implode(' ', \array_map('trim', $this->_data));
      return $str;
    }
    
    function PrintOut() {
      print "($this)\n";
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

    static protected function RecursiveCreation($str, $plisp = null) {
      print "RecursiveCreation: $str\n";

      $match = [];

      // Get first matching 'simple' command
      preg_match("/\([^\)\(]+\)/", $str, $match, PREG_OFFSET_CAPTURE);

      // entire simple sublist (including '(' & ')')
      $sublist_str = $match[0][0];
      $offset = $match[0][1];

      // this should never happen but if it evaluates to false, die
      if (!$sublist_str) {
          die ("plist error with line $str");
      }
      
      // explode the string and create a new list
      $sublist = explode(' ', substr($sublist_str, 1, -1));

      $new_plist = new Plist($sublist, $plisp);
      
      // we have reached the end! return our $new_plist
      if ($offset === 0) {
        return $new_plist;
      }

      // we are still somewhere in the middle - replace this sublist with a reference      
      $new_str = substr_replace($str, $new_plist->plisp_reference_id, $offset, strlen($sublist_str));
      
      return PList::RecursiveCreation($new_str, $plisp);
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
        $res = isset($this[$offset]) ? $this->_args[$offset] : null;
        if (is_numeric($res)) {
            $res = floatval($res);
        } else 
        if ( $x = $this->plisp->Get($res) ) {
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
