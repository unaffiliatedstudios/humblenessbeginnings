<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\globals\Superglobals;
use \codeneric\phmm\base\includes\Error;
function codeneric_send_image_if_allowed() {
  $upload_dir = wp_upload_dir();
  $upload_dir = $upload_dir[\hacklib_id("basedir")];
  $G = Superglobals::Get();
  $data = \codeneric\phmm\validate\protected_file_request($G);
  $f = $data[\hacklib_id("f")];
  $attach_id = $data[\hacklib_id("attach_id")];
  $project_id = $data[\hacklib_id("project_id")];
  $part = $data[\hacklib_id("part")];
  if (($f !== "zip-favs") &&
      ($f !== "zip-all") &&
      (!\hacklib_cast_as_boolean(
         file_exists($upload_dir."/photography_management/".$f)
       ))) {
    if (\hacklib_cast_as_boolean(function_exists("http_response_code"))) {
      http_response_code(404);
    }
    exit;
  }
  if (\codeneric\phmm\base\protect_images\Main::user_is_permitted(
        $f,
        $attach_id,
        $project_id
      ) ===
      false) {
    if (\hacklib_cast_as_boolean(function_exists("http_response_code"))) {
      http_response_code(401);
    }
    exit;
  }
  $file_path = $upload_dir."/photography_management/".$f;
  \codeneric\phmm\base\protect_images\Main::provide_file(
    $f,
    $file_path,
    $project_id,
    $part
  );
}
