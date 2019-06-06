<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\admin\ajax\Helper;
final class EndpointHelperTest extends Codeneric_UnitTest {
  public function testUsernameFallback() {
    $this->assertFalse(Helper::validate_username_fallback(""));
    $this->assertFalse(
      Helper::validate_username_fallback("!\"\302\247$%&/\"")
    );
    $this->assertTrue(Helper::validate_username_fallback("abc"));
    $this->assertFalse(Helper::validate_username_fallback("abc!?"));
  }
}
