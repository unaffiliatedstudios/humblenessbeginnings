<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
final class ConditionalHooksTest extends Codeneric_UnitTest {
  public static function setUpBeforeClass() {
    parent::setUpBeforeClass();
    $GLOBALS[\hacklib_id("wp_version")] = "1.0.0";
  }
  public function setUp() {
    parent::setUp();
    $GLOBALS[\hacklib_id("wp_version")] = "1.0.0";
  }
  private function expectHookToBeRegistered(
    $tag,
    $target,
    $expectation = true
  ) {
    $class = $target[0];
    $class =
      \hacklib_cast_as_boolean(is_string($class))
        ? $class
        : get_class($class);
    $this->assertSame(
      $expectation,
      is_int(has_action($tag, $target)),
      $class."::".$target[1]." should be registered to ".$tag
    );
  }
  public function testConditionalHookForWpVersion1_0_0() {
    $this->markTestIncomplete();
    $public = \codeneric\phmm\base\frontend\Main::class;
    $this->expectHookToBeRegistered(
      "the_password_form",
      array($public, "the_password_form_hook")
    );
  }
}
