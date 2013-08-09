<?php

namespace templr\engine;

/**
 * Rendering enging which processes html code
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class HtmlEngine extends AbstractEngine {

    public function Process($content) {
        return $content;
    }

}

