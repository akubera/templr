<?php

namespace templr\engine;

/**
 * Rendering enging which processes html code
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class HtmlEngine extends Engine {

    // nothing to change - just output the content
    public function Process($content) {
        return $content;
    }

    public function Name() {
        return __CLASS__;
    }


}

