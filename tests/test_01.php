<?php

define('TEMPLR_DEBUG', true);

// include the templr library
require_once "templr.php";
//require_once "webpage.php";

// Create a templr object using templates in the directory "templates"
$tmplr = new templr\Templr("tests/templates");

$view = $tmplr->GetView("t1.tplr");
//$view->printMissingReqs();

//$wp = new templr\WebPage();

//echo $wp;

