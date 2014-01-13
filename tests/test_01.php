<?php

// include the templr library
require_once "../templr.php";
//require_once "webpage.php";

// Create a templr object using templates in the directory "templates"
$tmplr = new templr\Templr("./templates");

$wp = new templr\WebPage();

echo $wp;

