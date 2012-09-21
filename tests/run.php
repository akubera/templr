<?php

define("TEMPLR_ROOT", '.');

require_once "webpage.php";

foreach (glob("test_*.php") as $file) {

	include $file;
}

