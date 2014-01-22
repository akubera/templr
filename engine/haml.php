<?php

namespace templr\engine;

/**
 * Description of haml
 *
 * @author andrew
 */
class HamlEngine extends AbstractEngine  {

    public function Process($content) {
        return "<$content>";
    }

    public function Name() {
        return __CLASS__;
    }


}

