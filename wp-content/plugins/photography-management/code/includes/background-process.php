<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Logger;
  class BackgroundProcess extends \WP_Background_Process {
    protected $action = "example_process";
    protected function task($item) {
      Logger::debug("Background process item", $item);
      return false;
    }
    protected function complete() {
      parent::complete();
      Logger::debug("Background process complete");
    }
  }
}
