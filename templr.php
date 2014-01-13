<?php

namespace templr;

define('TEMPLR_ROOT', '.');
define('TEMPLR_EXT', 'tplr');

require_once 'init.php';
require_once 'webpage.php';

/**
 * The main class which the user interacts with to use the library
 */
class Templr {

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
        if (is_string($path)) {
            $this->file_path[] = $path;
        } else if (is_array($path)) {
            foreach ($path as $p) {
                if (!is_string($p)) {
                    throw \Exception;
                }
                $this->file_path[] = $p;
            }
        }
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
     * Gets a view pointed to by $view_name
     *
     * @param String $view_name
     * @return mFile The view
     */
    public function GetView(string $view_name) {

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
     * @param int $position Position in the list to insert
     */
    public function AddToPath($pathname, $position = -1) {
        if (!is_int($position)) {
            // error - wrong type for position
        }
        if ($position < 0) {
            $position += (count($this->file_path) + 1);
        }
        array_splice($this->file_path, $position, 0, $pathname);
    }

    public static function ViewPath() {
        return self::$configure['VIEW_PATH'] ?: [];
    }

    public static function TemplatePath() {
        return self::$configure['TEMPLATE_PATH'] ?: [];
    }
}
