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

require_once 'enginefactory.php';

class Block {

    public static $id_matcher = "/^(\[[^:]*:[^\]:]*(?::[^\]:]*)*\])/m"; // [html:body:etc]
    public static $id_splitter = "/^\[([^:]*):([^\]:]*)(?::([^\]:]*))*\]/m"; // [html:body:etc]


    public $id = '';

    /**
     * Create a templr block from an identification string and block of code
     *
     * @param string $name The id in the form [engine:indentifier]
     * @param string $contents The contents of the block
     */
    public function __construct($contents) {
        if (TEMPLR_DEBUG) {
            print "Building Block out of string '$contents'\n";
        }
        $b = preg_split(Block::$id_splitter, $contents);
        print_r($b);
        $engine_name = $b[1];
        $this->id = $b[2];
        $this->engine = \templr\EngineFactory::Create($engine_name);

        if (TEMPLR_DEBUG) {
            print "Produces : '" . $this->engine->Process($contents) . "'\n";
        }

    }


    static public function Factory($contents) {
        return new Block($contents);
    }
}
