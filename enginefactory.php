<?php
/*
 * templr/engine/enginefactory.php
 *
 * A Factory which creates a rendering engine specified by a string
 *
 * @author andrewkubera
 *
 */
 
namespace templr;


/**
 * Static class used for hiding implementation of rendering
 *  engine creation. 
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class EngineFactory {
    
    static public function Create($string) {
        switch ($string) {
         case "html":
             include_once 'engine/html.php';
             return new \templr\engine\HtmlEngine();
         case "text":
             include_once 'engine/text.php';
             return new \templr\engine\TextEngine();    
         case "haml":
             include_once 'engine/haml.php';
             return new \templr\engine\HamlEngine();
             
        }
    }
    
    
}

?>
