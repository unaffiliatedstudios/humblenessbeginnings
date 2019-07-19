<?php
namespace codeneric\phmm\legacy\v4_0_0 {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\legacy\type;
  use \codeneric\phmm\base\includes\Error;
  function save_project($id, $data) {
    \update_post_meta($id, "gallery", $data[\hacklib_id("gallery")]);
    \update_post_meta($id, "protection", $data[\hacklib_id("protection")]);
    \update_post_meta(
      $id,
      "post_password",
      $data[\hacklib_id("protection")][\hacklib_id("password")]
    );
    if (!\hacklib_cast_as_boolean(
          \is_null($data[\hacklib_id("thumbnail")])
        )) {
      \update_post_meta($id, "thumbnail", $data[\hacklib_id("thumbnail")]);
    }
    if (\hacklib_cast_as_boolean(\is_null($data[\hacklib_id("thumbnail")])) &&
        (!\hacklib_cast_as_boolean(\is_null(get_thumbnail_meta($id))))) {
      \delete_post_meta($id, "thumbnail");
    }
    \update_post_meta(
      $id,
      "configuration",
      $data[\hacklib_id("configuration")]
    );
  }
  function get_thumbnail_meta($id) {
    $raw = \get_post_meta($id, "thumbnail", true);
    if (\hacklib_cast_as_boolean(is_string($raw)) && ($raw !== "")) {
      return (int) $raw;
    }
    return null;
  }
  function save_client($post_id, $data) {
    \update_post_meta(
      $post_id,
      "project_access",
      $data[\hacklib_id("project_access")]
    );
    \update_post_meta(
      $post_id,
      "internal_notes",
      $data[\hacklib_id("internal_notes")]
    );
    if (\hacklib_cast_as_boolean(\is_null($data[\hacklib_id("plain_pwd")])) &&
        (\get_post_meta($post_id, "plain_pwd", true) === "")) {
      $data[\hacklib_id("plain_pwd")] = \wp_generate_password(10);
    }
    if (!\hacklib_cast_as_boolean(
          \is_null($data[\hacklib_id("plain_pwd")])
        )) {
      \update_post_meta(
        $post_id,
        "plain_pwd",
        $data[\hacklib_id("plain_pwd")]
      );
    }
    $wp_user = get_client_wp_user_id($post_id);
    if (\hacklib_cast_as_boolean(is_int($wp_user))) {
      update_wp_user($wp_user, $data);
    } else {
      create_and_save_wp_user($post_id, $data);
    }
  }
  function get_client_wp_user_id($clientID) {
    $id = \get_post_meta($clientID, "wp_user", true);
    if ($id === "") {
      return null;
    }
    return (int) $id;
  }
  function update_wp_user($wpUserID, $data) {
    $plain_pwd = $data[\hacklib_id("plain_pwd")];
    $userdata = array(
      "display_name" => $data[\hacklib_id("post_title")],
      "user_email" => $data[\hacklib_id("email")],
      "user_login" => $data[\hacklib_id("user_login")],
      "ID" => $wpUserID,
      "user_pass" =>
        \hacklib_cast_as_boolean(\is_null($plain_pwd))
          ? null
          : \wp_hash_password($plain_pwd)
    );
    \wp_insert_user($userdata);
  }
  function create_and_save_wp_user($post_id, $data) {
    $userdata = array(
      "user_login" => $data[\hacklib_id("user_login")],
      "user_email" => $data[\hacklib_id("email")],
      "display_name" => $data[\hacklib_id("post_title")],
      "role" => "phmm_client",
      "show_admin_bar_front" => false,
      "user_pass" => $data[\hacklib_id("plain_pwd")]
    );
    $userID = \wp_insert_user($userdata);
    \HH\invariant(
      is_int($userID),
      "%s",
      new Error(
        "Failed to create a user.",
        array(array("data", \json_encode($data)))
      )
    );
    $updated = \update_post_meta($post_id, "wp_user", $userID);
    \HH\invariant(
      is_int($updated),
      "%s",
      new Error("Failed to save wp_user meta to client post")
    );
    return $userID;
  }
  function save_comment($comment) {
    $res = 0;
    $current_date = \date("Y-m-d H:i:s");
    $wpdb = \codeneric\phmm\base\globals\Superglobals::Globals("wpdb");
    \HH\invariant(
      $wpdb instanceof \wpdb,
      "%s",
      new Error("Can not get global wpdb object!")
    );
    $res = $wpdb->insert(
      "codeneric_phmm_comments",
      array(
        "time" => $current_date,
        "content" => $comment[\hacklib_id("content")],
        "project_id" => $comment[\hacklib_id("project_id")],
        "attachment_id" => $comment[\hacklib_id("attachment_id")],
        "wp_user_id" => $comment[\hacklib_id("wp_user_id")],
        "client_id" => $comment[\hacklib_id("client_id")],
        "wp_author_id" => $comment[\hacklib_id("wp_author_id")]
      )
    );
    if (\hacklib_cast_as_boolean(is_int($res)) && ($res === 1)) {
      return true;
    } else {
      return false;
    }
  }
  function save_lable_set(
    $clientID,
    $projectID,
    $imageIDs,
    $labelID = "1111111111111"
  ) {
    \HH\invariant(
      is_array($imageIDs),
      "%s",
      new Error("Expected labels to be of type array.")
    );
    $optionName = get_option_name($clientID, $projectID, $labelID);
    return \update_option($optionName, $imageIDs);
  }
  function get_option_name($clientID, $projectID, $labelID) {
    \HH\invariant(is_int($clientID), "%s", new Error("clientID must be int"));
    \HH\invariant(
      is_int($projectID),
      "%s",
      new Error("projectID must be int")
    );
    \HH\invariant(
      is_string($labelID),
      "%s",
      new Error("labelID must be string")
    );
    \HH\invariant(
      $labelID !== "",
      "%s",
      new Error("labelID cannot be empty string")
    );
    $hash = \md5($clientID."/".$projectID."/".$labelID);
    return "codeneric/phmm/labels/".$hash;
  }
}
