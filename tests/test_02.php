<?php

//define('TEMPLR_DEBUG', true);

require_once 'enginefactory.php';

$x = \templr\EngineFactory::Create("text");

print $x->Name() . "\n";
