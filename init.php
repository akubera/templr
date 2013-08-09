<?php

namespace templr;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (defined('TEMPLR_DEFAULT_NAME') ) {
    WebPage::$default_template_name = TEMPLR_DEFAULT_NAME;
}
