<?php
namespace codeneric\phmm\base\admin\ajax {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\globals\Superglobals;
  class Request {
    public static function getPayload() {
      $P = Superglobals::Post();
      \HH\invariant(
        array_key_exists("payload", $P),
        "%s",
        new Error("Payload not set")
      );
      \HH\invariant(
        is_string($P[\hacklib_id("payload")]),
        "%s",
        new Error("Payload not string")
      );
      try {
        return /* UNSAFE_EXPR */ (array) \hacklib_cast_as_array(
          json_decode(stripslashes($P[\hacklib_id("payload")]))
        );
      } catch (\Exception $e) {
        return null;
      }
    }
    public static function rejectInvalidRequest(
      $error = null,
      $statusCode = 422
    ) {
      $e =
        \hacklib_cast_as_boolean(is_string($error))
          ? self::makeError($error)
          : $error;
      wp_send_json_error(array("error" => $e), $statusCode);
    }
    public static function resolveValidRequest($response) {
      wp_send_json_success(array("data" => $response));
      return $response;
    }
    public static function makeError($msg) {
      return array((object) array("message" => $msg));
    }
  }
}
