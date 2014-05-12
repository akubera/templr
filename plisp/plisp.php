<?php
/*
 * plisp/plisp.php
 */

namespace templr\plisp;

class PLISP {

    const regex = "/^\(((?>[^()]+)|(?:R))*\) *$/"; // (function arg1 arg2)

    protected $prefix = "";
    protected $literal_strings = [];
    protected $stored_lists = [];
    protected $variables = [];
    protected $extended_files = [];
    protected $included_files = [];

    static protected $string_id_prefix = "&STR";
    static protected $list_id_prefix = "&LST";

    static public $DEBUG = true;

    protected $double_ampersands = true;

    static public function BeginSub($method = '') {
        $s = '| ';
//        print $method $method."\n";
        if ($method) {
            ob_start(function ($buffer) use ($s, $method) {
             return "\n$s".preg_replace('/\n/', "\n$s", trim( "{{".$method . "}}\n" . $buffer)) . "\n";
            });
        } else {
            ob_start(function ($buffer) use ($s) { return "\n$s".preg_replace('/\n/', "\n$s", trim($buffer)) . "\n";});
        }
    }

    static public function ReturnSub($val) {
        $cname = '';
        $ret_tag  =  ">> ";
        if (is_callable($val, false, $cname)) {
            print $ret_tag . $cname;
        } else {
            print $ret_tag . $val;
        }
    }

    static public function EndSub() {
        if (PLISP::$DEBUG) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    /**
     * What was this supposed to DO?
     *
     * @param type $obj
     */
    public function __construct($obj) {
      $this->obj = $obj;
    }

    public function Evaluate($string) {

      // No commands given
      if ($string === "") {
        return [];
      }

      // remove front and end whitespace, and take out "string literals"
      $string = $this->RemoveStringLiterals(trim($string));

      if (!$string) {
        throw new \Exception("Trying to Evaluate empty command");
      }

      // Remove comments
      $string = preg_replace('/^#.*/m', "", $string);
      if ($string[0] === '#') {
          //
          print "Comment\n";
          print "$string\n";
          return [];
      }
      // check if NOT beginning of plisp command
      else if ($string[0] != '(') {
        $word = substr($string, 0, strpos($string, ' '));
         echo "[".__METHOD__."] Error : unknown command : '{$word}'. Did you mean ({$word} ...) \n";
         return null;
      }

      // ensure number of ( matches number of )
      $l_count = substr_count($string, '(');
      $r_count = substr_count($string, ')');
      if ($l_count !== $r_count) {
        throw new \Exception("Error : Parens mismatch. Unequal number of '(' and ')' characters (" . $l_count . " != " .$r_count . ") in string:\n\t$string\n");
      } else if ($l_count === 0) {
          return [];
      }

      if (PLISP::$DEBUG) { print "\n=== Building plisp header ==="; }
      // Build the master list - which is a list that runs each command given
      //  in the initial plisp init string, using plisp command "all'
      $master_list = $this->BuildLists("(all $string)");
      if (PLISP::$DEBUG) { print "=== Done ===\n"; }

      if (PLISP::$DEBUG) { print "=== Running plisp header ==="; }
//      ob_start(function ($buffer) { return preg_replace('/\n/', "\n  ", $buffer . "\n") . "\n";});

      // run the master list
      $string = $master_list(); // $this->RecursiveEval($master_list);
      if (PLISP::$DEBUG) { print "=== Done ===\n"; }

      if (PLISP::$DEBUG) {
        // print the end result
        print " = Result = \n";
        var_dump($string);
        print " ==== ";
      }
//      var_dump($this->Reduce());
      return $this->jsonize();
    }

    //
    protected function BuildLists($str) {
        PLISP::BeginSub(__METHOD__);
        $res = plist::GenerateFromString($str, $this);
        PLISP::EndSub();
        return $res;
    }

    protected function RemoveStringLiterals($str) {

      $begin = strpos($str, '"');
      $str = str_replace("&", "&&", $str);

      // cool - no strings to worry about
      if ($begin === false) {
        return $str;
      }

      // Remove pesky escaped backslashes by doulbing underscores and ampersands - then re-writing \\ as \_
      $escaped = str_replace("\\\\", "\\_", str_replace("_", "__", $str));

//       $escaped = $str;
//       $replace = ["_" => "__", "&" => "&&", "\\\\" => "\\_"];
//
//       foreach ($replace as $k => $v) {
//         if (strpos($str, $k) !== false) {
//           $escaped = str_replace($k, $v, $escaped);
//         }
//       }

      // Now only single underscores are preceded by a backslash - replace escaped quotes (assume they're in the right place)
      $escaped = str_replace('\\"', " &ESCAPEDQUOTE&", $escaped);

      //replace slashes and underscores
//       $escaped = str_replace("\\_", "\\\\", str_replace("__", '_', $escaped));
//       $replace = ["_" => "__", "\\_" => "\\\\", "&" => "&&", ];
      $escaped = str_replace(["__", "\\_"], ["_", "\\\\"], $escaped);

      // Begin finding strings
      $matches = [];

      // regex matches all characters between double quotes which are NOT preceded by a '\'
      $regex = "{[^\\\](\"[^\"]*\")}m"; // '{[^\\]"((?:[^"])*)"}'; //"/[^\\\]\"((?:[^\"]|\\\")*)\"/"; //  "/[^\\\]\"(.*)\"/"; //  '/[^\\]"(.*)[^\\]"/'; //
      while (preg_match($regex, $escaped, $matches, PREG_OFFSET_CAPTURE)) {
          $quote = $matches[1];

          // set the literal string to whatever is inside the quotes - replace all escaped things
          //  we set any \" in the original string to " here!
          $unescaped = str_replace('&&', '&', str_replace(" &ESCAPEDQUOTE&", '"', $quote[0]));

          // create a new string literal id
          $id = $this->RegisterId($unescaped);
          $escaped = substr_replace($escaped, " $id", $quote[1], strlen($quote[0]));
      }

      // At this point the only thing escaped is still & (as &&)
      $escaped = str_replace(" &ESCAPEDQUOTE&", '\\"', $escaped);

      return $escaped;
    }


    //
    // When given some identifier in a plisp - ensure
    //  all escaped characters are back to normal
    // Because we use a single '&' to identify a reference, they were doubled
    //  upon initial reading of the string, and now must be halved
    //
    public function Clean($item) {
        if ($item === "null") {
          return null;
        }
        if ($this->double_ampersands) {
          $item = str_replace("&&", "&", $item);
        }
        return $item;
    }


    /**
     *
     * @param string $str
     * @return array
     */
    static public function tokenize($str) {
        $res = [];

        $str;
        return $res;
    }

    public function set($name, $val) {
        PLISP::BeginSub(__METHOD__);
        if (plisp::$DEBUG) {
            print '['.__METHOD__."] DEBUG name='{$name}' val='{$val}'\n";
        }

        if (is_a($name, "templr\plisp\plispvariable")) {
            $name = $name->name;
        }
        else if (!is_string($name)) {
            throw new \Exception("Error : Attempting to identify plisp variable by something not a string! not implemented yet");
        }
        else if ($name === '') {
            throw new \Exception("Error : Attempting to identify plisp variable by empty string!");
        }

        // a string we must ensure is not a reference
        if ( is_string($val) ) {
            $val = $this->get($val);
        }

        // Append a $ so that we identify it as a variable
        if ($name[0] !== '$') {
            $name = '$' . $name;
        }

        $v = new PlispVariable();

        $v->name = $name;
        $v->value = $val;
        $v->is_set = true;

        $this->variables[$name] = $v;

        if (plisp::$DEBUG) {
            var_dump($this->variables);
            print "-- plisp.set Done\n";
        }
        PLISP::EndSub();
    }

    public function RegisterId($obj) {
      $id = null;

      // create and store a literal string
      if (is_string($obj)) {
          $id = uniqid(plisp::$string_id_prefix);
          $this->literal_strings[$id] = &$obj;
      } else

      // create and store a plisp plist
      if (is_a($obj, '\templr\plisp\plist') or is_subclass_of($obj, '\templr\plisp\plist')) {
          $id = uniqid(plisp::$list_id_prefix);
          $this->stored_lists[$id] = &$obj;
      }
      return $id;
    }


    public function get($name) {
      PLISP::BeginSub(__METHOD__);
      if (is_null($name)) {
        PLISP::ReturnSub(null);
        PLISP::EndSub();
        return null; // function(){return null;};
      }

//        if (is_array($name)) {
//          $backtrace = debug_backtrace();
//          foreach ($backtrace as $frame) {
//              print "{$frame['class']}{$frame['type']}{$frame['function']}\n";
//          }
//          exit(0);
//        }

      $cname = '';
      if (is_callable($name, false, $cname)) {
          print "Calling $cname()\n";
            $name = $name();
      }

      if (plisp::$DEBUG) print "Plisp.Get() : Looking for '$name'... ";

      // get whatever $name is referring to
      $res = $this->GetReference($name);

      if (null !== $res and '' !== $res) {
        if (plisp::$DEBUG) print "found '$res'!\n";

//        if (is_string($res)) {
//            $res = function () use ($res) { return $res; };
//        }
        PLISP::ReturnSub($res);
        PLISP::EndSub();
        return $res;
      }
      if (isset($this->variables[$name])) {
          $res = $this->variables[$name] ;
      } else if ($name[0] == "\$") {
          $res = null; // function(){return null;};;
      } else {
          $res = $this->Clean($name);
      }

      if (plisp::$DEBUG) { print "found '$res'\n"; }

      PLISP::ReturnSub($res);
      PLISP::EndSub();
      return $res;
    }

    public function GetReference($id) {
      if (is_array($id)) {
//          var_dump($id);
           $res = [];
          foreach ($id as $r) {
              $res[] = $this->GetReference($r);
          }
          return $res;
      }
      // we have a string
      if (strpos($id, plisp::$string_id_prefix) === 0) {
          return $this->literal_strings[$id];
      } else

      // we have a list
      if (strpos($id, plisp::$list_id_prefix) === 0) {
          return $this->stored_lists[$id];
      } else

      // we have a variable
      if ($id[0] === '$') {
          if (!isset($this->variables[$id])) {
              $this->variables[$id] = new PlispVariable();
              $this->variables[$id]->name = $id;
              return $id;
          }
          return $this->variables[$id];
//              throw new \Exception("Error : No such plisp variable {$id} in plisp env.");
      }
      return null;
    }

    public function FindId($obj) {

      // create and store a literal string
      if (is_string($obj)) {
          $id = array_search($obj, $this->literal_strings, true);
      } else

      if (is_a($obj, '\templr\plisp\plist') or is_subclass_of($obj, '\templr\plisp\plist')) {
          $id = array_search($obj, $this->stored_lists, true);
      }

      return $id;
    }

    public function Reduce() {
        return ['lists' => $this->stored_lists,'variables' => $this->variables, "strings" => $this->literal_strings];
    }

    public function jsonize() {
        $res = $this->Reduce();
        foreach ($res as $k => &$v) {
            foreach ($v as $id => &$thing) {
                if (is_a($thing, "templr\plisp\Plist")) {
                    $res[$k][$id] = $thing->jsonize();
                } else {
                    $res[$k][$id] = json_encode($thing);
                }
            }
        }
//        var_dump($res);
//        return json_encode($res);
        return $res;
    }

    /**
     * Returns a string with comments and duplicate spaces removed from the
     *
     * @param type $str
     * @return type
     */
    static public function HeaderClean($str) {
        return trim(preg_replace('/  +/m',' ', preg_replace('/^#.*/m', "", $str)));
    }
}
