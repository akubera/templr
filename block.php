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

require_once 'engine/Engine.php';

class Block {

    public static $id_matcher = "/^(\[[^:]*:[^\]:]*(?::[^\]:]*)*\])/m"; // [html:body:etc]
//    public static $id_splitter = "/^\[([^:]*):([^\]:]*)(?::([^\]:]*))*\]/m"; // [html:body:etc]
    public static $id_splitter = "/^\[([^:]*):([^\]:]*)\] *\n(.*) *$/ms"; // [html:body:etc]

    public $id = '';

    /**
     * Create a templr block from an identification string and block of code
     *
     * @param string $name The id in the form [engine:indentifier]
     * @param string $contents The contents of the block
     */
    public function __construct($contents) {
        if (TEMPLR_DEBUG) {
            print "[".__METHOD__."] DEBUG Building Block out of string '$contents'\n";
        }
        // match
        $b = [];
        preg_match(Block::$id_splitter, $contents, $b);
        if (TEMPLR_DEBUG) {
            print "[".__METHOD__."] DEBUG ****\n";
            print_r($b);
            print "***\n";
        }

        // remove the first element (the complete match)
        array_shift($b);

        // pop off the last element and store in content
        $this->content = array_pop($b);

        // the first and second elements are the engine and identifier
        $engine_name = array_shift($b);
        $block_id = array_shift($b);

        // TODO : Handle remaining elements in $b (the template options)

        print "[".__METHOD__."] DEBUG Engine : '{$engine_name}'\n";
        print "[".__METHOD__."] DEBUG Id : '{$block_id}'\n";
        print "[".__METHOD__."] DEBUG Content : '{$this->content}'\n";

        // set block stuff
        $this->id = $block_id;
        $this->engine = engine\Engine::Create($engine_name);

        if (TEMPLR_DEBUG) {
            print "[".__METHOD__."] DEBUG Produces : '" . $this->engine->Process($contents) . "'\n";
            print "[".__METHOD__."] DEBUG **** Done\n";
        }

    }

    /** Static call to create a new block */
    static public function Factory($contents) {
        return new Block($contents);
    }

    /** An alias for the Factory function */
    static public function Generate($contents) {
        return static::Factory($contents);
    }
}
