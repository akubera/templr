<?php
/*
 * engine/text.php
 *
 * The object which processes [text:*] blocks
 *
 * text is raw text, so when outputing as html, special characters
 *  such as < and & get escaped using htmlentities
 *
 */

namespace templr\engine;

require_once 'abstractengine.php';

/**
 * class text
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class TextEngine extends AbstractEngine {

    public function Process($content) {
        // nothing to process! Just text (right?)
        return $content;
    }

    public function Name() {
        return __CLASS__;
    }

}

