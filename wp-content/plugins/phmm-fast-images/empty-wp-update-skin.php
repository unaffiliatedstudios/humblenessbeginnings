<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
include_once (ABSPATH."wp-admin/includes/class-wp-upgrader.php");
class PHMM_Fast_Images_Installer_Upgrader_Skins
  extends WP_Upgrader_Skin {
  public function __construct($args = array()) {
    parent::__construct();
    $defaults = array(
      "url" => "",
      "nonce" => "",
      "title" => "",
      "context" => false
    );
    $this->options = wp_parse_args($args, $defaults);
  }
  public function header() {}
  public function footer() {}
  public function error($error) { /* UNSAFE_EXPR */
    $this->installer_error = $error;
  }
  public function add_strings() {}
  public function feedback($string) {}
  public function before() {}
  public function after() {}
}
