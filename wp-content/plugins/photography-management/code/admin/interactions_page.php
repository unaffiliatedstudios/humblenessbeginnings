<?php
namespace codeneric\phmm\base\admin {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\base\includes\Error;
  class InteractionsPage {
    const page_name = "interactions";
    public static function init() {}
    public static function add_page() {
      \add_submenu_page(
        "edit.php?post_type=".
        Configuration::get()[\hacklib_id("client_post_type")],
        "PHMM ".\__("Interactions", "photography-management"),
        \__("Interactions", "photography-management"),
        "manage_options",
        self::page_name,
        array(self::class, "render_page")
      );
    }
    public static function render_page() {
      echo
        ("<div id='cc_phmm_interactions_page'>\n       <div style=\"background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:block;\"></div>\n    </div>")
      ;
    }
  }
}
