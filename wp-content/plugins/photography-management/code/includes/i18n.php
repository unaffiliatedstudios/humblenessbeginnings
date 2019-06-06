<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class i18n {
    public function load_plugin_textdomain() {
      load_plugin_textdomain(
        "phmm",
        false,
        dirname(dirname(plugin_basename(__FILE__)))."/languages/"
      );
    }
  }
}
