<?php

/**
 * Class with HtmlElement tree structure which automatically
 *  renders an html document
 *
 * @author andrewkubera
 */

namespace templr;

if (!_TEMPLR_INITIALIZED) {
    require_once "init.php";
}

class WebPage implements \ArrayAccess {

    static $default_template_name = "index";
    static $template_path = [TEMPLR_ROOT];
    private $_path = [TEMPLR_ROOT];
    private $_data = [];
    private $renderlock = false;
    private $render_file_cache = [];
    private $_template_root_path = '';
    private $_root_name = '';

    private $_path_info = [];

    /**
     * Create a templr webpage from the specified template filename
     *
     * @param string $template Filename of template to load
     * @param array $opts Extra otions for the webpage
     */
    public function __construct($template = NULL, $opts = []) {

        // the name of the template we are looking for
        $template = $template ? : WebPage::$default_template_name;

        // the extensions of the templates
        $ext = @$opts['extension'] ? : TEMPLR_EXTENSION;

        // the found filename
        $filename = "";

        // set some default data references
        $this->_data['session'] = &$_SESSION;

        // check first character for directory separator - absolute path
        if ($template[0] === DIRECTORY_SEPARATOR) {

            // template is the filename
            $filename = $template;

            // _template_root_path
            $this->_template_root_path = dirname($filename);

            // store the path info
            $this->_path_info = pathinfo($filename);

        } else {
            // search through the template path for files that match the name
            self::$template_path = \array_merge(Templr::ViewPath(), Templr::TemplatePath());
            foreach (self::$template_path as $dir) {
                $f = $dir . $template . $ext;
                if (file_exists($f)) {
                    // store the path info
                    $this->_path_info = pathinfo($f);
                    $filename = $f;
                    $this->_template_root_path = $dir;
                    break;
                }
            }
        }

        // We could not find the file - print an error
        if (!$filename) {
            // TODO : Throw an exception object containing info on how to render an error page
            var_dump(WebPage::$template_path);
            echo "<br/>";
            print "error : Could not find file " . $template . " in WebPage template path ({$f}).";
            exit(1);
        }

        // the root of this file
        $this->_root_name = $filename;

        // we should remove these soon - too specific
        $this->_data['styles'] = [];
        $this->_data['scripts'] = [];
    }

    /**
     * Look through root file for any required files - loading, building
     *   and caching each one until done.
     */
    protected function _BuildTemplateTree() {

    }


    /**
     * Prints and returns the entire rendered webpage as a string by
     *  rendering the root file
     *
     * @param bool $print Determines whether to automatically print the webpage to stdout or not
     *
     * @return string
     */
    public function Render($print = true) {
        // It could be possible for a script to call render on the page, while page
        //  is already rendering, which is bad. This lock will prevent such infinite loops.
        if ($this->renderlock) {
            return;
        }
        $this->renderlock = true;

        // something really bad happened - file deleted while parsing?
        if (!file_exists($this->_root_name)) {
            die("Could not open root templr file '{$this->_root_name}'. Aborting!");
        }

        print "[". __METHOD__.  "] rendering " . $this->_root_name . "\n";

        // get the contents of the file
        $str = $this->load_file($this->_root_name);

        // Replace all %% with TEMPLR_WEB_ROOT
        $str = str_replace("{%%}", "%%", $str);
        $str = str_replace("%%", TEMPLR_WEB_ROOT, $str);

        // Replace all escaped %%
        $str = str_replace("%\%", "%%", $str);

        // Print the entire page here
        if ($print) {
            print $str;
        }

        // unlock the thing
        $this->renderlock = false;
        return $str;
    }

    protected function load_file($filename) {
        // $string contains the rendered page
        $string = $this->read_file($filename);

        // Find all blocks using regex
        $block_regex = "/^(\[[^:]{0,10}(?:[^\]]+)+\])/";

        $blocks = array_map(function ($str) {return trim($str, "\n\r");},
                      preg_split(Block::$id_matcher, $string, -1, PREG_SPLIT_DELIM_CAPTURE));

        // TODO : Handle malformed files
        //   $Blocks should have odd number of entries - header and block-title pairs

        $header = $this->Process_Header(array_shift($blocks));

        $block_list = [];

        while (count($blocks)) {
          // shift the first and second items in blocks to
          $block_list[] = new Block(array_shift($blocks), array_shift($blocks));
        }
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

        // TODO : Check for cached copy of the file

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
            foreach (array_reverse($this->_path) as $path) {
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
        $page = $this->_data;
        if (is_file($filename)) {
            ob_start();
            include $filename;
            return ob_get_clean();
        }
        print "no file\n";
        return "";
    }

    protected function Process_Header($header) {
        $plisp_env = new plisp\Plisp($this);

        $plisp_env->Evaluate($header);
        return;

        // Look for plisp
        foreach(explode("\n", $header) as $line) {
            $backup_line = $line;
            // double all underscores:
            $underscores = 0;
            $line = str_replace("_", "__", $line, $underscores);

            print $line . " ($underscores)\n\n";

            // number of subcommands
            $subcommand_count = 0;
            $subcommands = [];

            $match = [];
            $offset = -1;

            // begin looping through finding strings lisp commands
            do {
                // match a simple command (...) - no ( between
                $regex = "/(\([^\)\(]+\))/";
                preg_match($regex, $line, $match, PREG_OFFSET_CAPTURE);
//                var_dump(preg_split($regex, $line, -1, PREG_OFFSET_CAPTURE));
                $subcommand = $match[0][0];
                $offset = $match[0][1];
                if ($subcommand === NULL) {
                    throw new \Exception("Malformed LISP! Error around $backup_line", 500);
                }

                $sub_id = "_" . ($subcommand_count++);
                $line = substr_replace($line, $sub_id, $offset, strlen($subcommand));
                $subcommands[$sub_id] = $subcommand;
//              print "=======\n$line\n";
//              var_dump($subcommands);
            } while ($offset !== 0);

            // safety limit to avoid infinite loops
            $safe = 1024;
            // No errors! good
            $done = [];
            while (count($subcommands) && $safe--) {
                $var = [];

                // Get first key and value
                reset($subcommands);
                $key = key($subcommands);

                // Start goose chase for next 'simple' LISP command
                do {
                    $next = $subcommands[$key];
                    print "\n($key) Searching : '$next'\n";
                    preg_match("/_[\d]+/", $next, $var);

                    // Great! Simple lisp
                    if (count($var) === 0) {
                        // if there are no underscores - skip this step
                        if ($underscores !== 0) {
                            // reset all double underscores
                            $tmp_u = 0;
                            $next = str_replace("__", "_", $next, $tmp_u);
                            $underscores -= $tmp_u;
                        }

                        // process simple lisp
                        $done[$key] = $plisp_environment->Evaluate($next);

                        // remove from $subcommands
                        unset ($subcommands[$key]);

                        // we are done
                        break;

                    } else {
                        // Find the offending variables
                        print "($key) Need to load : " . $var[0] . "\n";

                        // We have already processed and stored the missing function
                        if (key_exists($var[0], $done)) {
                            print " Found : {$var[0]} => {$done[$var[0]]}\n";
                            $subcommands[$key] = str_replace($var[0], $done[$var[0]], $next);
                            break;
                        }
                        // Need to search for it
                        else {
                            $key = $var[0];
                        }
                    }

                } while ($key !== 0);

            }

        }
    }

    public function AddStyle($style) {
        array_push($this->_data['styles'], (string) $style);
    }

    public function AddStyles($styles) {
        foreach ($styles as $style) {
            $this->AddStyle($style);
        }
    }

    public function AddScript($script) {
        array_push($this->_data['scripts'], (string) $script);
    }

    public function AddToPath($path) {
        array_push($this->_path, (string) $path);
    }

    public function GetPath() {
        return $this->_path;
    }

    static public function AddPath($dir) {
        // ensure that $dir is a string
        array_unshift(WebPage::$template_path, (string) $dir);
    }

    // Array Functions - serve as a wrapper around $this->data
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
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

if (defined('TEMPLR_DEFAULT_NAME') ) {
    WebPage::$default_template_name = TEMPLR_DEFAULT_NAME;
}

require_once 'init.php';
