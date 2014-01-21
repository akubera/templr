<?php

namespace templr;

define('TEMPLR_ROOT', '.');
define('TEMPLR_EXTENSION', 'tplr');

if (!_TEMPLR_INITIALIZED) {
    require_once "init.php";
}

require_once 'webpage.php';

/**
 * The main class which the user interacts with to use the library
 */
class Templr {

    /**
     * Configuration of entire Templr library
     * @var array
     */
    static private $configure = [];

    /**
     * List of paths to search for files with
     * @var array
     */
    private $file_path = [];

    /**
     * Create a new Templr object.
     * If $path is a string, the Templr object will use this as the default path
     * to locate templr files. If $path is an array, it assumes this is a list
     * of path names and will search each of them in order for any templr files.
     *
     * @param mixed $path A path specifing where to find the templr files
     */
    function __construct($path = "") {

        // if only a string, append to file_path
        if (is_string($path)) {
            if (TEMPLR_DEBUG) {
                print ("[Templr::Templr::__construct] DEBUG : Adding '$path' to file path \n");
            }
            $this->file_path[] = $path;
        }
        // ensure path is an array full of strings - else throw exception
        else if (is_array($path)) {
            foreach ($path as $p) {
                if (!is_string($p)) {
                    throw \Exception;
                }
                if (TEMPLR_DEBUG) {
                    print ("[Templr::Templr::__construct] DEBUG : Adding '$p' to file path \n");
                }
                $this->file_path[] = $p;
            }
        }
        // throw exception
        else {
            throw \Exception;
        }

        // set the relevant configuration
        self::$configure['VIEW_PATH'] = $this->file_path;
        self::$configure['TEMPLATE_PATH'] = $this->file_path;
    }

    /**
     * Set the templr configuration
     *
     * @param array $opts
     */
    public static function config(Array $opts) {
        static::$configure = $opts;
    }

    /**
     * Gets the view identified by $view_name
     *
     * @param String $view_name
     * @return mFile The view
     */
    public function GetView(string $view_name) {
        // loop through each directory in file_path
        foreach ($this->file_path as $dir) {
            $name = $dir . $view_name;
            if (file_exists($name) || (file_exists(($name .= ".php")))) {
                return new mFile($name);
            }
        }
        return NULL;
    }

    /**
     * Adds a pathname to search path
     *
     * @param string $pathname The pathname or a list of pathnames
     * @param int $position Position in the list to insert (-1 is the end of the list)
     */
    public function AddToPath($pathname, $position = -1) {
        if (!is_int($position)) {
            // error - wrong type for position
            throw \Exception;
        }
        // wrap the number around so -1 is after the last element
        if ($position < 0) {
            $position += (count($this->file_path) + 1);
        }
        // insert at the correct position
        array_splice($this->file_path, $position, 0, $pathname);
    }

    public static function ViewPath() {
        return self::$configure['VIEW_PATH'] ? : [];
    }

    public static function TemplatePath() {
        return self::$configure['TEMPLATE_PATH'] ? : [];
    }

}
