<?php
/*
 *  plisp/plist.php
 *
 *
 */

namespace templr\plisp;

class Plist implements \ArrayAccess {
    protected $_data = [];
    
    function __construct($list = []) {
        if (is_array($list)) {
          $this->_data = $list;
        } else {
          $this->_data = [$list];
        }
    }
    
    function __tostring() {
      $str = implode(' ', \array_map('trim', $this->_data));
      return $str;
    }
    
    function PrintOut() {
      print "($this)\n";
    }
    
    
    static function GenerateFromString($str) {
        return new Plisp(array_filter(explode(' ', $str), 'strlen'));
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
