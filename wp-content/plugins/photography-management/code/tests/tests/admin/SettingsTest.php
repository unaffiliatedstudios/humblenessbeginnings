<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\admin\Settings;
final class SettingsTest extends Codeneric_UnitTest {
  public function setUp() {}
  public function testDefaultConfiguration() {
    $this->assertSame(
      array(
        "hide_admin_bar" => false,
        "accent_color" => "#0085ba",
        "cc_photo_image_box" => false,
        "cc_photo_enable_styling" => true,
        "cc_photo_lightbox_theme" => "dark",
        "page_template" => "",
        "custom_css" => "",
        "remove_images_on_project_deletion" => false,
        "canned_emails" => array(),
        "max_zip_part_size" => 10
      ),
      Settings::getDefaultSettings()
    );
  }
  public function testUntouchedConfiguration() {
    $this->assertSame(
      Settings::getDefaultSettings(),
      Settings::getCurrentSettings()
    );
  }
}
