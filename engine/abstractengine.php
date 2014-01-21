<?php

namespace templr\engine;

/**
 * Description of abstract
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
abstract class AbstractEngine {

    abstract public function Process($content);
    public function Name() {
        return "AbstractEngine";
    }
}

