<?php

/*
 * engine/neph/lexer.php
 *
 */

namespace templr\engine\neph;

/**
 * The neph language lexer
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class Lexer {

    protected static $_terminals = array(
        "/^(root)/" => "T_ROOT",
        "/^(map)/" => "T_MAP",
        "/^(\s+)/" => "T_WHITESPACE",
        "/^(\/[A-Za-z0-9\/:]+[^\s])/" => "T_URL",
        "/^(->)/" => "T_BLOCKSTART",
        "/^(:)[^:]/" => "T_COLON_SEPARATOR",
        "/^(::)/" => "T_DOUBLESEPARATOR",
        "/^(\w+)/" => "T_IDENTIFIER",
        "/^($\w+)/" => "T_VARIABLE",
        "/^(\[)/" => "T_SQUARE_LBRACE",
        "/^(\])/" => "T_SQUARE_RBRACE",
        "/^(\()/" => "T_LPARENS",
        "/^(\))/" => "T_RPARENS",
    );
    
    // "/^([\s]+)/" => T_FRONT_WHITESPACE

    public static function run($source) {
        $tokens = [];

        foreach ($source as $number => $line) {
//            if ($number == 0) continue;
            // first whitespace check
            $matches = [];
            if (preg_match("/^([\s]+)/", $line, $matches)) {
                array_push($tokens, ['match' => $matches[1], 'token' => "T_FRONT_WHITESPACE", 'line' => $number + 1]);
            }

            // result match
            $offset = isset($matches[1]) ? strlen($matches[1]) : 0;

            // loop through extracting tokens
            while ($offset < strlen($line)) {
                $result = static::_match($line, $number, $offset);
                if ($result === false) {
                    throw new \Exception("Unable to parse line " . ($number + 1) . "  '". $line . "'.");
                }
                $tokens[] = $result;
                $offset += strlen($result['match']);
            }
        }

        return $tokens;
    }

    protected static function _match($line, $number, $offset) {
        $string = substr($line, $offset);
        $matches = [];
        foreach (static::$_terminals as $pattern => $name) {
            if (preg_match($pattern, $string, $matches)) {
                var_dump($matches);
                return array(
                    'match' => $matches[1],
                    'token' => $name,
                    'line' => $number + 1
                );
            }
        }
        return false;
    }

}

//
// Some tests here
//
if (php_sapi_name() === 'cli') :

    $input = array('root -> (var)');
    $result = Lexer::run($input);
//    var_dump($result);

    $input = ["[html::xxx]","  html","    head","      title Unit Test","    body","      p test"];
    $result = Lexer::run($input);
    var_dump($result);

endif;


