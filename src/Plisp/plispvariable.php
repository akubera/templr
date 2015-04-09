<?php
/*
 *  plisp/plispvariable.php
 *
 */
 
namespace templr\plisp;

class PlispVariable {
    
    public $name = '';
    public $value = null;
    public $is_set = false;
    
//    protected $plisp;
    
    public function __invoke() {
        if ($this->is_set) {
            return $value;
        } else {
            throw new \Exception("Error : invoking unset Plisp Variable");
        }
    }
    
    public function __toString() {
        return "$name";
    }
}