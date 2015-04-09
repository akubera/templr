<?php

/*
 * engine/neph/parser.php
 *
 */

namespace templr\engine\neph;

/**
 * The neph language lexer
 *
 * @author Andrew Kubera <andrewkubera@gmail.com>
 */
class Parser {

    protected $parsing_tree = array(
        "T_IDENTIFIER" =>
        ["T_WHITESPACE" =>
            ["T_IDENTIFIER"]
        ],
        "T_SQUARE_LBRACE" =>
        ["T_IDENTIFIER" =>
            ["T_COLON_SEPARATOR" =>
                ["T_IDENTIFIER" =>
                    ["T_SQUARE_RBRACE" => "L_BLOCKSTART"] // [xxx:xxx]
                ],
                "T_DOUBLESEPARATOR" =>
                ["T_IDENTIFIER" =>
                    ["T_SQUARE_RBRACE" => "L_DOUBLE_BLOCKSTART"] // [xxx::xxx]
                ]
            ]
        ],
        "T_LPARENS" =>
        ["T_IDENTIFIER" =>
            ["T_STS"]
        ]


//        "T_FRONT_WHITESPACE" => 
    );
    protected $bnf_reverse = ["T_IDENTIFIER" => ["T_WHITESPACE" => ["T_IDENTIFIER"]]
    ];

    public static function run($token_list) {

        foreach ($token_list as $token) {
            switch ($token) {
                case 1:
                    break;
                default:
                    break;
            }
        }
        $x = [
            "T_WHITESPACE",
            "T_URL",
            "T_BLOCKSTART",
            "T_COLON_SEPARATOR",
            "T_DOUBLESEPARATOR",
            "T_IDENTIFIER",
            "T_VARIABLE",
            "",
            "T_SQUARE_RBRACE",
            "T_LPARENS",
            "T_RPARENS"
        ];
    }

}
