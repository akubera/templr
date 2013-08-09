<?php

namespace templr;

/**
 * 
 */
class Templr {

    static private $configure = [];

    /**
     * 
     * @param array $opts
     */
    public static function config(Array $opts) {
        static::$configure = $opts;
    }

    /**
     * 
     * @param String $view_name
     * @return mFile The view
     */
    public static function GetView($view_name) {

        foreach (self::$configure['VIEW_PATH'] as $dir) {
            $name = $dir . $view_name; 
            $b = file_exists($name);
            if (!$b) {
                $name .= ".php";
            }
            $b = file_exists($name);
            if ($b) {
                return new mFile($name); //WebPage($name);
            }
        }
        return NULL;
    }

    public static function ViewPath() {
        return self::$configure['VIEW_PATH'] ?: [];
    }
    
    public static function TemplatePath() {
        return self::$configure['TEMPLATE_PATH'] ?: [];
    }
}
