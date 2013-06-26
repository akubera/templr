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

    public function __construct($obj) {
      $this->obj = $obj;
    }
    
    public function Evaluate($string) {
      // remove front and end whitespace, and take out "string literals"
      $string = $this->RemoveStringLiterals(trim($string));
      if (!$string) {
        throw new \Exception("Trying to Evaluate empty command");
      }
      print "Evaluating $string : \n";
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
//       $escaped = str_replace("\\\\", "\\_", str_replace("&", "&&", str_replace("_", "__", $str)));

      $escaped = $str;
      $replace = ["_" => "__", "&" => "&&", "\\\\" => "\\_"];

      foreach ($replace as $k => $v) {
        if (strpos($str, $k) !== false) {
          $escaped = str_replace($k, $v, $escaped);
        }
      }
      

      // Now only single underscores are preceded by a backslash - replace escaped quotes (assume they're in the right place)
      $escaped = str_replace('\\"', " &ESCAPEDQUOTE&", $escaped);

      //replace slashes and underscores
//       $replace = ["_" => "__", "&" => "&&", ];
      $escaped = str_replace(["__", "\\_"], ["_", "\\\\"], $escaped);

      // Begin finding strings
      $matches = [];

      // regex matches all characters between double quotes which are NOT preceded by a '\'
      $regex = "{[^\\\\]\"[^\"]*\"}m"; // '{[^\\]"((?:[^"])*)"}'; //"/[^\\\]\"((?:[^\"]|\\\")*)\"/"; //  "/[^\\\]\"(.*)\"/"; //  '/[^\\]"(.*)[^\\]"/'; // 
      while (preg_match($regex, $escaped, $matches, PREG_OFFSET_CAPTURE)) {
          // create a new string literal
          $id = uniqid(" &STRL");
          $literal_strings[$id] = str_replace(" &ESCAPEDQUOTE&", '"', substr($matches[1][0],1,-1));
          $escaped = substr_replace($escaped, $id, $matches[0][1], strlen($matches[0][0]));
      }

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
        
        // the position we found the instruction
        $offset = $match[0][1];
        
        // if it was the beginning - there are no sub ()
        if ($offset === 0) {
          $no_parens = substr($subcommand, 1, -1);
          $parens = $subcommand;
          return $this->EvalSingleLine($no_parens);
        }
        $res = $this->RecursiveEval($subcommand);
        
        $line = substr_replace($line, $res, $offset, strlen($subcommand));

      } while ($offset !== 0);
    }

    protected function EvalSingleLine($line) {
        print " Single Line : '$line' ";
        // escape quotes
        $underscores = 0;
        $d_under = str_replace("_", "__", $line, $underscores);

        $args = explode(' ', $line);// Plist::GenerateFromString($line);
        
        $command = array_shift($args);
        $f = PlispFunction::Create($command);
        $res = $f->exec($args);
        print " => $res\n";
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
    
}
