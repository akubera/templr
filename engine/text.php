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

/**
 * class text
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class TextEngine extends Engine {

    public function Process($content) {
        // escape the html characters
        return htmlentities($content);
    }

    public function Name() {
        return __CLASS__;
    }

}

