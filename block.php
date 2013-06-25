<?php
/*
 * templr/block.php
 *
 * A Templr block - piece of code which can be inserted into other
 *  blocks to form a page
 *
 * @author andrewkubera
 *
 */
 
namespace templr;

class Block {

    public static $id_matcher = "/^(\[[^:]*:[^\]:]*(?::[^\]:]*)*\])/m"; // [html:body:etc]
    public static $id_splitter = "/^\[([^:]*):([^\]:]*)(?::([^\]:]*))*\]/m"; // [html:body:etc]


    /**
     * Create a templr block from an identification string and block of code
     * 
     * @param string $name The id in the form [engine:indentifier]
     * @param string $contents The contents of the block 
     */
    public function __construct($name, $contents) {
        $b = [];
        preg_match(Block::$id_splitter, $name, $b);
        $engine = $b[1];
        $id = $b[2];
        $this->engine = \templr\EngineFactory::Create($engine);
    }
  
}
