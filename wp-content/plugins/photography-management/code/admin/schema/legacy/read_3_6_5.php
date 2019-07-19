<?php
namespace codeneric\phmm\legacy\v3_6_5 {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\legacy\type\client_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\project_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\comment_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\plugin_settings_data_representation_3_6_5;
  use \codeneric\phmm\base\includes\Error;
  function read_client($id) {
    $client = \get_post_meta($id, "client", true);
    return \codeneric\phmm\legacy\validate\client_data_representation_3_6_5(
      $client
    );
  }
  function read_projects($client_id) {
    $projects = \get_post_meta($client_id, "projects", true);
    if (!\hacklib_cast_as_boolean(is_array($projects))) {
      return array();
    }
    $res = array();
    foreach ($projects as $p) {
      if (\hacklib_cast_as_boolean(\array_key_exists("thumbnail", $p)) &&
          (!\hacklib_cast_as_boolean(
             is_numeric($p[\hacklib_id("thumbnail")])
           ))) {
        $p[\hacklib_id("thumbnail")] = 0;
      }
      if (\hacklib_cast_as_boolean(\array_key_exists("gallery", $p))) {
        if (\hacklib_cast_as_boolean(is_array($p[\hacklib_id("gallery")]))) {
          $p[\hacklib_id("gallery")] =
            \array_values($p[\hacklib_id("gallery")]);
        } else {
          if (\hacklib_cast_as_boolean(
                is_string($p[\hacklib_id("gallery")])
              ) &&
              ($p[\hacklib_id("gallery")] !== "")) {
            $p[\hacklib_id("gallery")] =
              \explode(",", $p[\hacklib_id("gallery")]);
          } else {
            $p[\hacklib_id("gallery")] = array();
          }
        }
      }
      $res[] =
        \codeneric\phmm\legacy\validate\project_data_representation_3_6_5($p);
    }
    return $res;
  }
  function read_comments($image_id) {
    $comments = \get_post_meta($image_id, "codeneric/phmm/comments", false);
    $res = array();
    if (\hacklib_cast_as_boolean(is_array($comments))) {
      foreach ($comments as $c) {
        $res[] =
          \codeneric\phmm\legacy\validate\comment_data_representation_3_6_5(
            $c
          );
      }
    }
    return $res;
  }
  function read_plugin_settings() {
    $options = \get_option("cc_photo_settings", array());
    if (\hacklib_cast_as_boolean(is_array($options)) &&
        \hacklib_cast_as_boolean(
          \array_key_exists("cc_photo_portal_page", $options)
        ) &&
        (!\hacklib_cast_as_boolean(
           is_numeric($options[\hacklib_id("cc_photo_portal_page")])
         ))) {
      $options[\hacklib_id("cc_photo_portal_page")] = null;
    }
    $res =
      \codeneric\phmm\legacy\validate\plugin_settings_data_representation_3_6_5(
        $options
      );
    return $res;
  }
}
