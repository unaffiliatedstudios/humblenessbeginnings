<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Error;
  class Email {
    public static function send($data) {
      return \wp_mail(
        $data[\hacklib_id("to")],
        $data[\hacklib_id("subject")],
        $data[\hacklib_id("message")],
        $data[\hacklib_id("headers")],
        $data[\hacklib_id("attachments")]
      );
    }
  }
}
