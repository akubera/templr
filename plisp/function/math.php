<?php   

namespace \templr\plisp;

global $plisp_registry;

function plisp_add($list) {
    $sum = 0;
    foreach ($list as $el) {
        $sum += $el->eval();
    }
    return $sum;
}

$plisp_registry["+"] = "plisp_add";
$plisp_registry["sum"] = "plisp_add";
$plisp_registry["add"] = "plisp_add";

function plisp_minus($list) {
    $diff = \array_shift($list)->eval();
    foreach ($list as $el) {
        $diff -= $el->eval();
    }
    return $diff;
}

$plisp_registry["-"] = "plisp_minus";
$plisp_registry["diff"] = "plisp_minus";

function plisp_mult($list) {
    $product = 1;
    foreach ($list as $el) {
        $product *= $el->eval();
    }
    return $product;
}


$plisp_registry["*"] = "plisp_mult";
$plisp_registry["multiply"] = "plisp_mult";

