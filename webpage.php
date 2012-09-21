<?php

/**
 * Class with HtmlElement tree structure which automatically 
 *  renders an html document
 *
 * @author andrewkubera
 */

namespace templr;

class WebPage implements \ArrayAccess {

    static $default_template_name = "index";
    
    private $path = [TEMPLR_ROOT];
    private $data = [];
    private $renderlock = false;
    private $render_file_cache = [];

    public function __construct($template = NULL) {

        $template = $template ? : WebPage::$default_template_name;
        $this->template_root_path = TEMPLR_ROOT;

        $this->root_name = $this->template_root_path . DS . $template . TEMPLR_EXT;

        $this->data['styles'] = [];
        $this->data['scripts'] = [];
    }

    /**
     * Prints and returns the entired rendered webpage as a string by 
     *  rendering the root file
     * 
     * @param bool $print Determines whether to automatically print the webpage to stdout or not
     * 
     * @return string
     */
    public function render($print = true) {
        // It could be possible for a script to call render on the page, while
        //  page is already rendering, this will prevent such infinite loops
        if ($this->renderlock) {
            return;
        }
        $this->renderlock = true;
        $str = $this->render_file($this->root_name);

        // Replace all %% with WEB_ROOT
        $str = str_replace("%%", TEMPLR_WEB_ROOT, $str);
        // Replace all escaped %% 
        $str = str_replace("%\%", "%%", $str);

        // Print the entire page here
        if ($print) {
            print $str;
        }
        $this->renderlock = false;
        return $str;
    }

    /**
     * Renders the file with filename specified - returns resulting string
     * 
     * @staticvar int $one
     * @param type $file
     * @return string
     */
    protected function render_file($file) {
        // required variable holding value '1' for preg_match
        static $one = 1;

        // $string contains the rendered page
        $string = $this->read_file($file);

        $matches = [];

        // match anything between two % % characters (no spaces allowed)
        preg_match_all("/%([^% ]+)%/", $string, $matches);

        // store each match as $varname
        foreach ($matches[1] as $varname) {

            if (array_key_exists($varname, $this->render_file_cache)) {
                $str = $this->render_file($this->render_file_cache[$varname]);
                $string = str_replace("%$varname%", $str, $string, $one);
                continue;
            }

            // find a file matching that filename in the path
            foreach (array_reverse($this->path) as $path) {
                // exact name
                $name = $path . '/' . $varname . ".php";
                
                // lowercase name
                $lname = $path . '/' . strtolower($varname) . ".php";
                
                // the filename if it exists
                $filename = is_file($name) ? $name : is_file($lname) ? $lname : false;
                
                if ($filename) {
                    // save the name of the file corresponding to the label
                    $this->render_file_cache[$name] = $filename;
                    
                    // render that file as we are rendering this one
                    $str = $this->render_file($filename);
                    
                    // the resulting string replaces the %label%
                    $string = str_replace("%$varname%", $str, $string, $one);

                    // stop searching through the path
                    break;
                }
            }
        // TODO : Unknown Variable Match
        }
        return $string;
    }

    /**
     * Includes the file given and returns the resulting string
     * 
     * @param string $filename
     * @return string
     */
    private function read_file($filename) {
        // $page is used to allow the file to access given data (var names fall through)
        $page = $this->data;
        if (is_file($filename)) {
            ob_start();
            include $filename;
            return ob_get_clean();
        }
        return "";
    }

    public function AddStyle($style) {
        array_push($this->data['styles'], (string) $style);
    }

    public function AddStyles($styles) {
        foreach ($styles as $style) {
            $this->AddStyle($style);
        }
    }

    public function AddScript($script) {
        array_push($this->data['scripts'], (string) $script);
    }

    public function AddToPath($path) {
        array_push($this->path, (string) $path);
    }

    public function GetPath() {
        return $this->path;
    }

    // Array Functions - serve as a wrapper around $this->data
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * When turning into string - just render the whole structure (can do `echo $wp;`)
     * 
     * @return string
     */
    public function __tostring() {
        return $this->render(false);
    }

}

