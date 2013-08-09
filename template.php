<?php

namespace templr;

require_once 'init.php';
require_once 'plisp/plisp.php';

class Template implements \ArrayAccess {

    const require_regex = "/^ *#require +([a-zA-Z\.]+)/m"; // #require filename.txt
    const section_regex = "/\[([^:]{0,10}):([^\]]+)\]/"; // [html:body]
    const tag_regex = "/\{([^:]{0,10}:[^\}]+)\}/"; // {text:name}

    public $container;
    protected $filename;
    protected $file;
    public $contents;
    protected $labels = [];
    protected $requires = [];
    protected $is_root_template;
    /**
     * Template Constructor
     * 
     * @param string $filename The filename of template to load
     */
    public function __construct($filename, $params = [], $is_root = false) {
        $this->filename = $filename;
        $this->is_root_template = $is_root;

        // load the contents of $filename into $this->contents
        ob_start();
        $bytes = readfile($filename);
        if ($bytes === false || $bytes === 0) {
            $this->contents = null;
            ob_clean();
        } else {
            $this->contents = ob_get_clean();
        }
        print $this->contents . "\n";
        
        $matches = [];
        $filenames = [];
        
        // search for plisp expressions
        if (preg_match(plisp\PLISP::regex, $this->contents, $matches)) {
            var_dump($matches);
        }

        // search for any require statements - if so replace them
        while (preg_match(self::require_regex, $this->contents, $matches)) {
            $fname = (string)$matches[1];
            if ($fname[0] != "/") { // Not absolute file path, append path
                $fname = TEMPLATE_PATH.DS.$fname;
            }

            if (isset($filenames[$fname])) {
                $this->contents = preg_replace(self::require_regex, "", $this->contents, 1);
            } else {
                $file_str = @file_get_contents($fname);
                if (!$file_str) {
                    $this->contents = preg_replace(self::require_regex, "", $this->contents, 1);
                    continue;
                }
                $filenames[$fname]= true;
                $this->contents = preg_replace(self::require_regex, $file_str, $this->contents, 1);
            }
        }
        
        // begin procesing imediately
        $this->process();
    }

    private function process() {
        // splits the contents of template into array of [0] => engine [1] => name [2] => contents
        $split = \preg_split(self::section_regex, $this->contents, null, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);

        // if nothing was found - return
        if (count($split) === 0) {
            return;
        }
        
        // if there was not a multiple of three results, format must be wrong
        if (count($split) % 3) {
            $nl = (PHP_SAPI === 'cli') ? "\n" : "<br />";
            print_r($split);
            die("{$nl}Problem with parsing template file {$this->filename}$nl");
        }

        // trim all whitespace from elements of split
        $split = array_map('trim', $split);
        
        // list of requires for this template
        $requirements = [];

        // store these into the '$this->labels' tree
        for ($i = 0; $i < count($split); $i += 3) {
            list($class, $name, $data) = array_slice($split, $i, 3);
            $this->labels[$class][$name]['data'] = $data;
        }

        // complete the tree by filling in missing data
        foreach ($this->labels as $Class => &$class) {
            foreach ($class as $Name => &$lbl) {
                //set required tags list as an array, (will be in tree form)

                $tags = [];
                // from the label's data extract all necessary tags (i.e. {text:name})
                preg_match_all(self::tag_regex, $lbl['data'], $tags);

                // $tags[1] is the text inside of the curly braces
                foreach ($tags[1] as $re) {
                    // paris[0] is class, pairs[1] is name
                    $pairs = explode(':', $re);

                    // add class to required if not there
                    if (!isset($lbl['req'][$pairs[0]])) {
                        $lbl['req'][$pairs[0]] = array();
                    }

                    // add name to the class if not there
                    if (!isset($lbl['req'][$pairs[0]][$pairs[1]])) {
                        $lbl['req'][$pairs[0]][$pairs[1]] = array("name" => $pairs[1], "matched" => false);
                    }

                    array_push($requirements, array("source" => array("class" => $Class, "name" => $Name),
                        "needs" => array("class" => $pairs[0], "name" => $pairs[1]),
                        "link" => null,
                        "satisfied" => false));
                }
            }
        }

        $this->requires = array_unique($requirements, SORT_REGULAR);
        $this->matchRequirements();
    }

    private function matchRequirements() {
        foreach ($this->requires as &$next) {
            if ($next['satisfied']) {
                continue;
            }

            $source_class = $next['source']['class'];
            $source_name = $next['source']['name'];

            $needs_class = $next['needs']['class'];
            $needs_name = $next['needs']['name'];

            // if the required class the source needs is already in lables, link it.
            if (isset($this->labels[$needs_class][$needs_name])) {
                $next['satisfied'] = true;
                $this->labels[$source_class][$source_name]['req'][$needs_class][$needs_name]['matched'] = true;
                $this->labels[$source_class][$source_name]['req'][$needs_class][$needs_name]['link'] = & $this->labels[$needs_class][$needs_name]['data'];
            }
        }
    }

    public function getReqs() {
        return $this->requires;
    }
    
    public function IsRoot() {
      return $this->is_root_template;
    }

    public function getLabels($class = null) {
        $result = null;
        if (isset($class)) {
            return $this->labels[$class];
        } else {
            $result = $this->labels;
        }
        return $result;
    }

    public function getAllTags() {
        $result = array();
        return $result;
    }

    public function processThing($object, $classname = "") {

        $classname = $classname ? strtolower($classname) : strtolower(get_class($object));
        //        echo "Processing ".$classname ."\n";
        //risky, we have access to all properties
        $objarray = (array) $object;
        echo "<hr/>";
        print_r($objarray);
        foreach ($this->requires as &$next) {
            if ($next['satisfied'])
                continue;
            if ($next['needs']['class'] == $classname && isset($objarray[$next['needs']['name']])) {

                //                print "found match with $classname:{$next['needs']['name']} ({$objarray[$next['needs']['name']]})<br>";

                $next['satisfied'] = true;
                $this->labels[$next['source']['class']][$next['source']['name']]['req'][$next['needs']['class']][$next['needs']['name']]['matched'] = true;
                $this->labels[$next['source']['class']][$next['source']['name']]['req'][$next['needs']['class']][$next['needs']['name']]['link'] = & $objarray[$next['needs']['name']];
            }
        }
    }

    public function printMissingReqs() {
        $str = '';
        foreach ($this->requires as $next) {
            if (!$next['satisfied']) {
                $str1 = $next['source']['class'] . ':' . $next['source']['name'];
                $str2 = '{' . $next['needs']['class'] . ':' . $next['needs']['name'] . '}';
                $str .= "[$str1] requires $str2\n";
            }
        }
        return $str;
    }

    public function ParseWithObject($class, $name, $object, $classname = "") {
        $this->processThing($object, $classname);
        $this->Parse($class, $name, true);
    }

    public function Parse($class, $name, $data = array(), $debug = false) {

        // prefix to help format output during recursive calls
        static $prefix = " ";
        static $one = 1;
        if ($debug)
            print $prefix . "parsing $class:$name\n";
        if (!isset($this->labels[$class][$name]['data'])) {
            return null;
        }

        // Create usable variables from data array 
        //  Ex $data = {"a"=>[1,2,3], "b"=>[5,6,7]},
        //   will create $a = [1,2,3], and $b = [1,2,3];
        //  !!!!!!!!! MUST NOT HAVE !!!!!!!!!!!!!
        //    $data['class'] or $data['name'] or $data['data']
        //
        if ($data != NULL):
            foreach ($data as $_name => $_) {
                //var_dump($_);
                global $$_name;
                $$_name = $_;
            }
        endif;
        // $str is the text contained within [$class][$name]
        $str = $this->labels[$class][$name]['data'];

        // if there is runnable code in the string, run it
        if (strpos($str, "<?php") != -1):

            // Regex to select runabble code
            $php_code_regex = "/<\?php((?:\?[^>]|[^\?])*)(?:\?>)?/s";

            // Each runnable is the eval-able text inside <?php tags
            $runables = array();

            // New_strs are the replacement text for each runnable
            $new_strs = array();

            // Fill $runables
            $num = preg_match_all($php_code_regex, $str, $runables);

            // Run each, store result in $new_strs
            foreach ($runables[1] as $run) {
                ob_start();
                eval($run);
                $new_strs[] = ob_get_clean();
            }

            //replace code with output
            for ($c = 0; $c < $num; $c++) {
                $old = $runables[0][$c];
                $new = $new_strs[$c];
                $str = str_replace($old, $new, $str, $one);
            }
        endif;

        if (isset($this->labels[$class][$name]['req'])) {
            $requirements = & $this->labels[$class][$name]['req'];
            foreach ($requirements as $Class => &$requirement) {
                foreach ($requirement as $Name => &$array) {
                    $prefix .= "\t";
                    $string = $this->Parse($Class, $Name, $data, $debug);
                    $prefix = substr($prefix, 0, -1);
                    if ($string) {
                        $search = '{' . $Class . ':' . $Name . '}';
                        if ($debug)
                            print $prefix . "replacing '$search' with '$string'\n";
                        if ($Class == "php") {
                            try {
                                $string = eval("return " . $string);
                            } catch (Exception $e) {
                                $string = $e . ' ' . $search;
                            }
                        }
                        $str = str_replace($search, $string, $str);
                    } else if ($array['matched'] && isset($array['link'])) {
                        $search = '{' . $Class . ':' . $Name . '}';
                        $str = str_replace($search, $array['link'], $str);
                    }
                }
            }
        }

        if ($data != null)
        // Unset the new names
            foreach ($data as $name => $d) {
                unset($$name);
            }
        if ($debug)
            print $prefix . "returning $str\n";
        return $str;
    }

    protected function runphps() {
        if (!isset($this->labels['php']))
            return;

        foreach ($this->labels['php'] as $Name => &$phps) {
            foreach ($phps['req'] as $requirements) {
                
            }
        }
    }

    /*
     * Stateless processing functions
     */
    
    static function StripComments(&$string) {
        
    }
    
    /*
     * ArrayAccess functions
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

}

require_once 'init.php';

