<?php

define('TEMPLR_DEBUG', true);
require_once "../src/Templr/templr.php";

class BlockTest extends PHPUnit_Framework_TestCase
{
  public function testBlockCreation()
  {
    $str = "[new_block:A:B]\nhello friend";
    $block = new \templr\Block($str);
    $this->assertEquals('hello friend', $block->content);
  }
}
