<?php

namespace templr;

define('_TEMPLR_INITIALIZED', true);

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// autoloader
function autoloader($class) {
    // split around the namespace separater
    $names = explode('\\', $class);

    // Get the top element of the namespace
    $top_namespace = array_unshift($names);

    // Global namespace - get next one
    if ($top_namespace === "") {
        $top_namespace = array_unshift($names);
    }

    // if in templr namespace, implode the namespace in check for the file
    if ($top_namespace === "templr") {
        $filename = __DIR__ . implode(DIRECTORY_SEPARATOR, $names) . '.php';
        if (file_exists($filename)) {
            require_once $filename;
        }
    }
}

spl_autoload_register(__NAMESPACE__ .'\\autoloader');

