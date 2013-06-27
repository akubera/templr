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

    static protected $string_id_prefix = "&STR";
    static protected $list_id_prefix = "&LST";

    protected $double_ampersands = true;
    

    public function __construct($obj) {
      $this->obj = $obj;
    }
    
    public function Evaluate($string) {
      // remove front and end whitespace, and take out "string literals"
      $string = $this->RemoveStringLiterals(trim($string));

      if (!$string) {
        throw new \Exception("Trying to Evaluate empty command");
      }
      
      if ($string[0] != '(') {
        $word = substr($string, 0, strpos($string, ' '));
         echo "Error : unknown command : '{$word}'. Did you mean ({$word} ...) \n";
         return null;
      }
      
      // ensure number of ( matches number of )
      $l_count = substr_count($string, '(');
      $r_count = substr_count($string, ')');
      
      if ($l_count !== $r_count) {
        throw new \Exception("Error : Parens mismatch. Unequal number of '(' and ')' characters (" . $l_count . " != " .$r_count . ") in string:\n\t$string\n");
      }

      // Build the master list - which is a list that runs each command given 
      //  in the initial plisp init string, using plisp command 'all'
      $master_list = $this->BuildLists("(all $string)");

      // run the master list
      $string = $master_list(); // $this->RecursiveEval($master_list);

      // print the end result
      print "End : ";
      var_dump($string);
    }

    //
    protected function BuildLists($str) {
        $res = plist::GenerateFromString($str, $this);
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

    private function RecursiveEval($list) {
      return $list->run();

      print "found id : " . $this->FindId($list) . "\n";

      $match = [];
      $offset = 0;
      $line = $str;

      do {
        print "\n\nLINE: $line\n\n";

        // Get inner contents of the command
        preg_match("/\([^\)\(]+\)/", $line, $match, PREG_OFFSET_CAPTURE);
        
        // entire subcommand (including '(' & ')')
        $subcommand = $match[0][0];
        if (!$subcommand) {
            echo "NOT $line\n";
            return '';
        }
        // the position we found the instruction
        $offset = $match[0][1];
        
        // if it was the beginning - there are no sub ()
        if ($offset === 0) {
          $no_parens = substr($subcommand, 1, -1);
          $parens = $subcommand;
          return $this->EvalSingleLine($no_parens);
        }
       print "RecursiveEval: $subcommand\n";
        $res = $this->RecursiveEval($subcommand);
        print "\n\nRES: $res\n\n";

        $line = substr_replace($line, $res, $offset, strlen($subcommand));

      } while ($offset !== 0);
              print "\n\nEND WHILE LINE: $line\n\n";

    }

    protected function EvalSingleLine($line) {
        print " Eval Single Line : '$line' ";
        // escape quotes
        $underscores = 0;
        $d_under = str_replace("_", "__", $line, $underscores);

        $args = array_filter(explode(' ', $line), 'strlen'); // Plist::GenerateFromString($line);

        $command = array_shift($args);
        ob_start();
        $f = PlispFunction::Create($this, $command);
        $res = $f->exec($args);
        $txt = ob_get_clean();
        print " => '$res'\n$txt";
        return "$res";
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
      if ($this->get($val) === null) {
        $this->variables[$name] = $val;
      } else {
        $this->variables[$name] = $this->get($val);
      }
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
      if (is_null($name)) {
        return null; // function(){return null;};
      }
      
      if (is_callable($name)) {
        $name = $name();
      }

       print "Looking for '$name'... ";

        $res = null;

        if (null !== ($res = $this->GetReference($name))) {
           print "found '$res'!\n";
           
           if (is_string($res)) {
           
              $res = function () use ($res) { return $res; };
           }
           return $res;
        }
        if (isset($this->variables[$name])) {
          $res = $this->variables[$name] ;
        } else if ($name[0] == "\$") {
          $res = null; // function(){return null;};;
        } else {
          $res = $this->Clean($name);
        }
           print "found '$res'\n";

        return $res;
    }

    public function GetReference($id) {
      // we have a string
      if (strpos($id, plisp::$string_id_prefix) === 0) {
          return $this->literal_strings[$id];
      } else 
      
      // we have a list
      if (strpos($id, plisp::$list_id_prefix) === 0) {
          return $this->stored_lists[$id];
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
    
}
