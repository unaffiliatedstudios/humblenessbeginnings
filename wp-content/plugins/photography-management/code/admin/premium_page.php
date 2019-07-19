<?php
namespace codeneric\phmm\base\admin {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\base\includes\Error;
  class PremiumPage {
    const page_name = "premium";
    public static function init() {}
    public static function add_page() {
      \add_submenu_page(
        "edit.php?post_type=".
        Configuration::get()[\hacklib_id("client_post_type")],
        "PHMM ".\__("Premium", "photography-management"),
        \__("Premium", "photography-management"),
        "manage_options",
        self::page_name,
        array(self::class, "render_page")
      );
    }
    public static function render_page() {
      $title = "<h2>".\__("Premium Page", "photography-management")."</h2>";
      $fbJoin = "<strong>".FrontendHandler::fb_join_message()."</strong>";
      echo
        ("<form action='options.php' method='post'>\n            ".
         $title.
         "\n      <div class='postbox'>\n                <div class='inside'>")
      ;
      echo
        ("<div id='cc_phmm_premium_page'>\n       <div style=\"background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:block;\"></div>\n    </div>")
      ;
      echo ("<hr />".$fbJoin."</div></div></form>");
    }
  }
}
