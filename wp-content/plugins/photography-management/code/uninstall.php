<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
function run() {
  if (!\hacklib_cast_as_boolean(defined("WP_UNINSTALL_PLUGIN"))) {
    exit(0);
  }
}
