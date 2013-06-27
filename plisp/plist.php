<?php
/*
 *  plisp/plist.php
 *
 *
 */

namespace templr\plisp;

class Plist implements \ArrayAccess {
    // a static reference to a plisp environment - there should only be one, 
    //  but we optionally leave this here to allow more.
    static protected $recent_plisp = null;

    // the held data held
    protected $_args = [];
    
    function __construct($list = [], $plisp = null) {
        // we have a new recent plisp
        if (is_a($plisp, "\templr\plisp\plisp")) {
          Plist::$recent_plisp = $plisp;
        }
        print "building from : $list\n";

        // set the plisp member to the most recent plisp, perhaps just set a few lines before
        $this->plisp = Plist::$recent_plisp;

        if (is_array($list)) {
          $this->_build_from_array($list);
        } else if (is_string($list)) {
          $this->_build_from_array(explode(' ', $list));        
        } else {
          die ("Unkown plist initiating object : " . get_class($list) . "\n");
        }
        $this->plisp_reference_id = $this->plisp->RegisterId($this);
    }
    
    private function _build_from_array($list) {
        assert(count($list) !== 0, "Attempting to build PLIST from empty list!");

        // remove first item from list
        $head = array_shift($list);
        $this->_data[] = $head;
        foreach ($list as $token) {
            if (is_string($token)) {
            
                // this is a reference of some kind
                if ($token[0] === '&' && @$token[1] !== '&') {
                }
            } else if(is_strong()) {
            
            }
            
        }
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
        
    static function GenerateFromString($str, $plisp) {
      // remove all extraneous whitespace
      $str = preg_replace(['/\([\s]+/', '/[\s]+\)/', '/[\s]+/'], ['(', ')', ' '], trim($str));
      
      assert($str[0] === '(', "PLisp command does not start with '(' character!");

      // set it and forget it!
      PList::SetPlisp($plisp);
      PList::RecursiveCreation($str);

        return new Plist(array_filter(explode(' ', $str), 'strlen'));
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
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->_data[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
}
