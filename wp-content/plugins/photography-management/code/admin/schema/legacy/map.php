<?php
namespace codeneric\phmm\legacy {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\legacy\type\client_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\project_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\comment_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\plugin_settings_data_representation_3_6_5;
  use \codeneric\phmm\legacy\type\plugin_settings_representation_4_0_0;
  use \codeneric\phmm\base\includes\Error;
  function map_client_from_3_6_5($client_data_3_6_5, $project_ids) {
    $client_data_4_0_0 = array();
    $client_data_4_0_0[\hacklib_id("project_access")] = array();
    foreach ($project_ids as $pid) {
      $client_data_4_0_0[\hacklib_id("project_access")][] = array(
        "id" => $pid,
        "active" => true,
        "configuration" => null
      );
    }
    $login_name = $client_data_3_6_5[\hacklib_id("login_name")];
    $client_data_4_0_0[\hacklib_id("post_title")] =
      $client_data_3_6_5[\hacklib_id("full_name")];
    \HH\invariant(
      !\hacklib_cast_as_boolean(\is_null($login_name)),
      "%s",
      new Error("cannot map this client!")
    );
    $client_data_4_0_0[\hacklib_id("user_login")] = $login_name;
    $client_data_4_0_0[\hacklib_id("email")] =
      $client_data_3_6_5[\hacklib_id("email")];
    $client_data_4_0_0[\hacklib_id("plain_pwd")] =
      $client_data_3_6_5[\hacklib_id("pwd")];
    $client_data_4_0_0[\hacklib_id("internal_notes")] =
      "Phone: ".
      $client_data_3_6_5[\hacklib_id("phone")].
      "\n".
      "Address: ".
      $client_data_3_6_5[\hacklib_id("address")];
    return $client_data_4_0_0;
  }
  function map_project_from_3_6_5($data_3_6_5, $pwd, $watermark) {
    $data_4_0_0 = array();
    $data_4_0_0[\hacklib_id("gallery")] = $data_3_6_5[\hacklib_id("gallery")];
    $data_4_0_0[\hacklib_id("protection")] = array(
      "private" => !\hacklib_cast_as_boolean(\is_null($pwd)),
      "password_protection" => !\hacklib_cast_as_boolean(\is_null($pwd)),
      "password" => $pwd,
      "registration" => null
    );
    $data_4_0_0[\hacklib_id("pwd")] = $pwd;
    $data_4_0_0[\hacklib_id("thumbnail")] =
      \hacklib_equals($data_3_6_5[\hacklib_id("thumbnail")], 0)
        ? null
        : $data_3_6_5[\hacklib_id("thumbnail")];
    $data_4_0_0[\hacklib_id("configuration")] = array(
      "commentable" => $data_3_6_5[\hacklib_id("commentable")] === "true",
      "disableRightClick" =>
        $data_3_6_5[\hacklib_id("disableRightClick")] === "true",
      "downloadable" => $data_3_6_5[\hacklib_id("downloadable")] === "true",
      "downloadable_favs" =>
        $data_3_6_5[\hacklib_id("downloadable_favs")] === "true",
      "downloadable_single" => false,
      "favoritable" => $data_3_6_5[\hacklib_id("favoritable")] === "true",
      "showCaptions" => $data_3_6_5[\hacklib_id("showCaptions")] === "true",
      "showFilenames" =>
        $data_3_6_5[\hacklib_id("showFilenames")] === "true",
      "watermark" => $watermark,
      "favoritable_limit" => null
    );
    return $data_4_0_0;
  }
  function map_comment_from_3_6_5(
    $data_3_6_5,
    $project_id,
    $wp_user_id_of_client
  ) {
    $data_4_0_0 = array();
    $data_4_0_0[\hacklib_id("attachment_id")] =
      $data_3_6_5[\hacklib_id("attach_id")];
    $data_4_0_0[\hacklib_id("wp_user_id")] = $wp_user_id_of_client;
    $data_4_0_0[\hacklib_id("project_id")] = $project_id;
    $data_4_0_0[\hacklib_id("content")] = $data_3_6_5[\hacklib_id("content")];
    $data_4_0_0[\hacklib_id("client_id")] =
      $data_3_6_5[\hacklib_id("client_id")];
    $data_4_0_0[\hacklib_id("time")] =
      \date("Y-m-d H:i:s", $data_3_6_5[\hacklib_id("date")]);
    $data_4_0_0[\hacklib_id("wp_author_id")] =
      $data_3_6_5[\hacklib_id("user_id")];
    return $data_4_0_0;
  }
  function map_plugin_settings_from_3_6_5($data_3_6_5) {
    $data_4_0_0 = array();
    $data_4_0_0[\hacklib_id("enable_slider")] =
      $data_3_6_5[\hacklib_id("cc_photo_image_box")] === 1;
    $data_4_0_0[\hacklib_id("slider_theme")] =
      \hacklib_cast_as_boolean(
        \in_array(
          $data_3_6_5[\hacklib_id("cc_photo_lightbox_theme")],
          array("light", "dark")
        )
      ) ? $data_3_6_5[\hacklib_id("cc_photo_lightbox_theme")] : "dark";
    $data_4_0_0[\hacklib_id("page_template")] =
      $data_3_6_5[\hacklib_id("page_template")];
    $data_4_0_0[\hacklib_id("accent_color")] = "#0085ba";
    $data_4_0_0[\hacklib_id("hide_admin_bar")] =
      $data_3_6_5[\hacklib_id("hide_admin_bar")] === 1;
    $data_4_0_0[\hacklib_id("portal_page_id")] =
      $data_3_6_5[\hacklib_id("cc_photo_portal_page")];
    $er = \explode(",", $data_3_6_5[\hacklib_id("cc_email_recipient")]);
    $er = \array_filter(
      $er,
      function($e) {
        return $e !== "";
      }
    );
    $data_4_0_0[\hacklib_id("email_recipients")] = $er;
    $data_4_0_0[\hacklib_id("custom_css")] =
      $data_3_6_5[\hacklib_id("custom_css")];
    $data_4_0_0[\hacklib_id("max_zip_part_size")] =
      $data_3_6_5[\hacklib_id("max_zip_part_size")];
    $data_4_0_0[\hacklib_id("watermark")] = array(
      "image_id" => $data_3_6_5[\hacklib_id("watermark_image_id")],
      "scale" => $data_3_6_5[\hacklib_id("watermark_scale")],
      "position" => $data_3_6_5[\hacklib_id("watermark_position")]
    );
    $data_4_0_0[\hacklib_id("remove_images_on_project_deletion")] =
      $data_3_6_5[\hacklib_id("remove_images_on_project_deletion")] === 1;
    $data_4_0_0[\hacklib_id("canned_emails")] = array();
    $es = $data_3_6_5[\hacklib_id("canned_email_subject")];
    $ec = $data_3_6_5[\hacklib_id("canned_email")];
    if ((!\hacklib_cast_as_boolean(\is_null($es))) &&
        (!\hacklib_cast_as_boolean(\is_null($ec)))) {
      $data_4_0_0[\hacklib_id("canned_emails")][] = array(
        "id" => "generated_".\time(),
        "display_name" => "Migrated template",
        "subject" => $es,
        "content" => $ec
      );
    }
    return $data_4_0_0;
  }
}
