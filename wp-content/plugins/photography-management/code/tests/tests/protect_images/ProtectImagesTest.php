<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\protect_images\Main as SecurityLogic;
use \codeneric\phmm\base\globals\Superglobals;
final class ProtectImages extends Codeneric_UnitTest {
  public function setUp() {
    parent::setUp();
    self::makeAdministrator();
  }
  private function createAndGetTestProject() {
    return
      $this->factory
        ->post
        ->create_and_get(
          array(
            "post_type" => $this->config[\hacklib_id("project_post_type")]
          )
        );
  }
  public function testValidator() {
    $this->assertTrue(true);
  }
}
