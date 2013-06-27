<?php
/*
 * plisp/plisp.php
 */

namespace templr\plisp;

global $plisp_registry;
$plisp_registry = [];

class PLISP {

    const regex = "/^\(((?>[^()]+)|(?:R))*\) *$/"; // (function arg1 arg2)

    protected $prefix = "";
    protected $literal_strings = [];
    protected $variables = [];

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

//       print "Evaluating $string : \n";

      $line = $this->RecursiveEval($string);
      print "End : $line";
    }
    
    protected function RemoveStringLiterals($str) {

      $begin = strpos($str, '"');

      // cool - no strings to worry about
      if ($begin === false) {
        return $str;
      }

      // Remove pesky escaped backslashes by doulbing underscores and ampersands - then re-writing \\ as \_
      $escaped = str_replace("\\\\", "\\_", str_replace("&", "&&", str_replace("_", "__", $str)));

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

          // create a new string literal id
          $id = uniqid("&STRL");
          
          // set the literal string to whatever is inside the quotes - replace all escaped things
          //  we set any \" in the original string to " here!
          $this->literal_strings[$id] = str_replace('&&', '&', str_replace(" &ESCAPEDQUOTE&", '"', substr($quote[0],1,-1)));
          $escaped = substr_replace($escaped, " $id", $quote[1], strlen($quote[0]));
      }

      // At this point the only thing escaped is still & (as &&)
      $escaped = str_replace(" &ESCAPEDQUOTE&", '\\"', $escaped);

      return $escaped;
    }

    private function RecursiveEval($str) {
      $match = [];
      $offset = 0;
      $line = $str;

      do {
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
        $line = substr_replace($line, $res, $offset, strlen($subcommand));

      } while ($offset !== 0);
      
    }

    protected function EvalSingleLine($line) {
        print " Eval Single Line : '$line' ";
        // escape quotes
        $underscores = 0;
        $d_under = str_replace("_", "__", $line, $underscores);

        $args = explode(' ', $line);  // Plist::GenerateFromString($line);

        $command = array_shift($args);
        ob_start();
        $f = PlispFunction::Create($this, $command);
        $res = $f->exec($args);
        $txt = ob_get_clean();
        print " => '$res'\n$txt";
        return "$res";
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
    
    public function get($name) {
         if (strpos($name, '&STRL') === 0) {
          echo  "FOUND LITERAL STRING : '{$this->literal_strings[$name]}'\n";
            return $this->literal_strings[$name];
        } 
      return isset($this->variables[$name]) ? $this->variables[$name] : null;
    }
    
}
