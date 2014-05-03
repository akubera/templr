<?php
/*
 * templr/engine/enginefactory.php
 *
 * A Factory which creates a rendering engine specified by a string
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 *
 */

namespace templr\engine;

/**
 * Static class used for hiding implementation of rendering
 *  engine creation.
 */
abstract class Engine {

    static private $engine_map = ["html" => ["file" => "engine/html.php", "class" => "engine\HtmlEngine"],
                                  "text" => ["file" => "engine/text.php", "class" => "engine\TextEngine"],
                                  "haml" => ["file" => "engine/haml.php", "class" => "engine\HamlEngine"]];

    /**
     * Create the engine from a string
     *
     * @param string $string name or identifier of the engine to be used
     * @return Concrete rendering engine
     */
    static public function Create($string) {

        // see if we have the engine
        $found = in_array($string, array_keys(self::$engine_map));

        if (TEMPLR_DEBUG) {
            print ("[" . __METHOD__ . "] DEBUG : Searching for engine identified by '$string' : " .
                    ($found ? "found!" : "NOT FOUND") . "\n");
        }

        if ($found) {
            // get engine data from the map
            $engine = static::$engine_map[$string];

            if (TEMPLR_DEBUG) {
                print "Engine Details : ";
                print_r($engine);
            }
            // include the file and return an instance of the
            include_once $engine['file'];
            $classname = "\\templr\\" . $engine['class'];
            return new $classname;
        } else {
            throw \Exception;
        }
    }

    public function Name() {
        return "AbstractEngine";
    }

    /**
     * Function a concrete engine MUST implement which reads in the content of
     * the block - and retruns an object with parsed output and references to
     * variables.
     */
    abstract public function Process($content);


}
