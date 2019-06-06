<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\includes\Client;
use \codeneric\phmm\base\includes\Error;
use \codeneric\phmm\base\includes\ErrorSeverity;
use \Eris\Generator;
final class ConfigurationTest extends Codeneric_UnitTest {
  public function testConfiguration() {
    \codeneric\phmm\Configuration::get();
    $this->assertTrue(true);
  }
  public function testConfiguration2() {
    $conifg = \codeneric\phmm\Configuration::get();
    $this->assertSame($conifg[\hacklib_id("target")], "base");
  }
}
