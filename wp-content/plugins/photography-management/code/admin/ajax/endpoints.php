<?php
namespace codeneric\phmm\base\admin\ajax {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Labels;
  use \codeneric\phmm\base\includes\Client;
  use \codeneric\phmm\base\includes\Email;
  use \codeneric\phmm\base\admin\Settings;
  use \codeneric\phmm\base\includes\Image;
  use \codeneric\phmm\base\includes\Project;
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\base\admin\CannedEmail\Handler as CannedEmail;
  use \codeneric\phmm\type\event;
  use \codeneric\phmm\base\includes\Permission;
  use \codeneric\phmm\base\admin\FrontendHandler;
  class Endpoints extends Request {
    public static function label_images() {
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\label_photo($request);
      $permitted_to_access_project =
        Permission::current_user_can_access_project(
          $request[\hacklib_id("project_id")]
        );
      if (!\hacklib_cast_as_boolean($permitted_to_access_project)) {
        self::rejectInvalidRequest(
          "Current user cannot access this project!"
        );
        return null;
      }
      $clientID = Permission::get_client_id_wrt_project(
        $request[\hacklib_id("project_id")]
      );
      \HH\invariant(
        !\hacklib_cast_as_boolean(\is_null($clientID)),
        "%s",
        new Error(
          "Something went wrong, your are permitted to access the project, but do not have a client_id!"
        )
      );
      if (!\hacklib_cast_as_boolean(
            \codeneric\phmm\base\includes\Labels::label_exists(
              $request[\hacklib_id("label_id")]
            )
          )) {
        self::rejectInvalidRequest("Given label does not exist");
        return null;
      }
      $projectConfig =
        Project::get_configuration($request[\hacklib_id("project_id")]);
      $limit = $projectConfig[\hacklib_id("favoritable_limit")];
      if (!\hacklib_cast_as_boolean(\is_null($limit))) {
        if (\count($request[\hacklib_id("photo_ids")]) > $limit) {
          self::rejectInvalidRequest("Limit reached", 424);
          return null;
        }
      }
      $successful = Labels::save_set(
        $clientID,
        $request[\hacklib_id("project_id")],
        $request[\hacklib_id("label_id")],
        $request[\hacklib_id("photo_ids")]
      );
      if (\hacklib_cast_as_boolean($successful)) {
        $make_event = function($event) {
          return $event;
        };
        $event = $make_event(
          array(
            "type" => "updated_labels",
            "client_id" => $clientID,
            "project_id" => $request[\hacklib_id("project_id")]
          )
        );
        \do_action("codeneric/phmm/label_images_notification", $event);
        return self::resolveValidRequest(true);
      }
      self::rejectInvalidRequest("Failed to save favorites", 500);
      return null;
    }
    public static function check_username($t) {
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\check_username($request);
      $username = \sanitize_user($request[\hacklib_id("username")]);
      $valid = \strlen($username) > 0;
      if (\hacklib_cast_as_boolean($valid)) {
        $valid = \validate_username($username);
        if (!\hacklib_cast_as_boolean($valid)) {
          $valid = Helper::validate_username_fallback($username);
        }
        $valid =
          \hacklib_cast_as_boolean($valid) &&
          \hacklib_cast_as_boolean(is_bool(\username_exists($username)));
      }
      self::resolveValidRequest($valid);
      return null;
    }
    public static function dismiss_admin_notice() {
      $request = self::getPayload();
      \HH\invariant(
        is_array($request),
        "%s",
        new Error("Type error in payload!")
      );
      \HH\invariant(
        \hacklib_cast_as_boolean(\array_key_exists("id", $request)) &&
        \hacklib_cast_as_boolean(
          \array_key_exists("cooldown_in_seconds", $request)
        ),
        "%s",
        new Error("Type error in payload!")
      );
      $notice_id = $request[\hacklib_id("id")];
      $cooldown_in_seconds =
        (int) $request[\hacklib_id("cooldown_in_seconds")];
      \HH\invariant(
        is_string($notice_id),
        "%s",
        new Error("Type error in payload!")
      );
      $transient = Utils::get_admin_notice_transient_key($notice_id);
      \set_transient($transient, true, $cooldown_in_seconds);
      self::resolveValidRequest(true);
    }
    public static function analytics_opt_in_allow() {
      $currentSettings = Settings::getCurrentSettings();
      $currentSettings[\hacklib_id("analytics_opt_in")] = true;
      Settings::updateSettings($currentSettings);
      self::resolveValidRequest(true);
    }
    public static function update_premium($t) {
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\update_premium($request);
      \update_option("cc_prem", $request[\hacklib_id("bool")]);
      \delete_option("__temp_site_transiant_54484886");
      self::resolveValidRequest(true);
    }
    public static function check_email() {
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\check_email($request);
      $email = \sanitize_email($request[\hacklib_id("email")]);
      if (!\hacklib_cast_as_boolean(\is_email($email))) {
        self::rejectInvalidRequest("Invalid email", 200);
        return false;
      }
      $user = \get_user_by("email", $email);
      if (\hacklib_cast_as_boolean(is_bool($user))) {
        return self::resolveValidRequest(true);
      } else {
        \HH\invariant(
          $user instanceof \WP_User,
          "%s",
          new Error("user should exist in this scope")
        );
        $id = Client::get_client_id_from_wp_user_id($user->ID);
        if (\hacklib_cast_as_boolean(\is_null($id))) {
          return self::resolveValidRequest(false);
        }
        if ($id === $request[\hacklib_id("client_id")]) {
          return self::resolveValidRequest(true);
        }
      }
      return self::resolveValidRequest(false);
    }
    public static function fetch_gallery_images() {
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\fetch_images($request);
      $map = function($ID) use ($request) {
        $pid = $request[\hacklib_id("project_id")];
        $query_args = array();
        if (!\hacklib_cast_as_boolean(\is_null($pid))) {
          $query_args = array("project_id" => $pid);
        }
        $image = \codeneric\phmm\base\includes\Image::get_image(
          $ID,
          $request[\hacklib_id("mini_thumbs")],
          $query_args
        );
        if (\hacklib_cast_as_boolean(is_array($image))) {
          return $image;
        }
        return array("id" => $ID, "error" => true);
      };
      $result = \array_map($map, $request[\hacklib_id("IDs")]);
      return self::resolveValidRequest($result);
    }
    public static function send_feedback() {
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\send_feedback($request);
      $config = Configuration::get();
      $to = $config[\hacklib_id("support_email")];
      $subject = \sanitize_text_field($request[\hacklib_id("subject")]);
      $headers = array(
        "From: \"".
        $request[\hacklib_id("name")].
        "\" <".
        \sanitize_email($request[\hacklib_id("email")]).
        ">"
      );
      $message = \sanitize_text_field($request[\hacklib_id("content")]);
      $meta_payload = array();
      $meta_payload[\hacklib_id("product")] =
        $config[\hacklib_id("plugin_name")];
      $meta_payload[\hacklib_id("product_version")] =
        $config[\hacklib_id("version")];
      $meta_payload[\hacklib_id("plugin_id")] = Utils::get_plugin_id();
      $meta_payload[\hacklib_id("topic")] = $request[\hacklib_id("topic")];
      $crypted = "";
      if (\hacklib_cast_as_boolean(
            \function_exists("openssl_public_encrypt")
          )) {
        $pub_key = \file_get_contents(
          $config[\hacklib_id("assets")][\hacklib_id("crypto")][\hacklib_id(
            "pub_key"
          )]
        );
        \openssl_public_encrypt(
          \json_encode($meta_payload),
          $crypted,
          $pub_key
        );
        $s = Utils::get_temp_file("support_medatada_");
        $resource = $s[\hacklib_id("resource")];
        $name = $s[\hacklib_id("name")];
        \fwrite($resource, $crypted);
        $mail_attachments = array($name);
        $success =
          \wp_mail($to, $subject, $message, $headers, $mail_attachments);
        Utils::close_and_delete_file($resource, $name);
      } else {
        $success = \wp_mail($to, $subject, $message, $headers);
      }
      self::resolveValidRequest($success);
    }
    public static function get_interactions($t) {
      if (!\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
        self::rejectInvalidRequest("This is an admin-only endpoint");
      }
      $request = self::getPayload();
      $request = \codeneric\phmm\validate\get_interactions($request);
      $projects = Client::get_project_ids($request[\hacklib_id("client_id")]);
      $populated = \array_map(
        function($projectID) use ($request) {
          $labels = Labels::get_all_labels();
          $interactionLabels = array();
          foreach ($labels as $label) {
            $set = Labels::get_set(
              $request[\hacklib_id("client_id")],
              $projectID,
              $label[\hacklib_id("id")]
            );
            $interactionLabels[] = array(
              "project_id" => $projectID,
              "label_id" => $label[\hacklib_id("id")],
              "label_name" => $label[\hacklib_id("name")],
              "set" => $set
            );
          }
          $comments = Utils::apply_filter_or(
            "codeneric/phmm/get_comment_counts",
            array(
              "client_id" => $request[\hacklib_id("client_id")],
              "project_id" => $projectID
            ),
            array()
          );
          $comments = \array_map(
            function($comment) use ($projectID, $request) {
              return \array_merge(
                $comment,
                array(
                  "project_id" => $projectID,
                  "client_id" => $request[\hacklib_id("client_id")],
                  "image" => Image::get_image(
                    $comment[\hacklib_id("image_id")],
                    true
                  )
                )
              );
            },
            $comments
          );
          return array(
            "labels" => $interactionLabels,
            "comments" => $comments
          );
        },
        $projects
      );
      $abc = \array_reduce(
        $populated,
        function($carry, $item) {
          $carry[\hacklib_id("comments")] = \array_merge(
            $carry[\hacklib_id("comments")],
            $item[\hacklib_id("comments")]
          );
          $carry[\hacklib_id("labels")] = \array_merge(
            $carry[\hacklib_id("labels")],
            $item[\hacklib_id("labels")]
          );
          return $carry;
        },
        array("comments" => array(), "labels" => array())
      );
      self::resolveValidRequest($abc);
    }
    public static function get_download_zip_parts() {
      $r = self::getPayload();
      $r = \codeneric\phmm\validate\get_download_zip_parts($r);
      self::resolveValidRequest(
        Project::get_number_of_zip_parts(
          $r[\hacklib_id("project_id")],
          $r[\hacklib_id("mode")],
          $r[\hacklib_id("client_id")]
        )
      );
    }
    public static function get_original_image_url() { // UNSAFE
      $r = self::getPayload();
      $r = \codeneric\phmm\validate\get_original_image_url_request($r);
      $id = $r[\hacklib_id("image_id")];
      $query_args = array("project_id" => $r[\hacklib_id("project_id")]);
      $img_url = Image::get_original_image_url($id, $query_args);
      self::resolveValidRequest($img_url);
    }
    public static function set_product_demo_finished() {
      FrontendHandler::set_product_tour_finished();
      self::resolveValidRequest(true);
    }
    public static function get_debug_info() {
      $tmp_wp_version = $GLOBALS[\hacklib_id("wp_version")];
      $clients = array();
      $projects = array();
      $client_ids = \codeneric\phmm\base\includes\Client::get_all_ids();
      $project_ids = \codeneric\phmm\base\includes\Project::get_all_ids();
      $settings = \codeneric\phmm\base\admin\Settings::getCurrentSettings();
      foreach ($client_ids as $id) {
        $c = \codeneric\phmm\base\includes\Client::get($id);
        if ($c === null) {
          continue;
        }
        $clients[] = array(
          "ID" => $c[\hacklib_id("ID")],
          "project_access" => $c[\hacklib_id("project_access")],
          "internal_notes" => $c[\hacklib_id("internal_notes")],
          "canned_email_history" => $c[\hacklib_id("canned_email_history")]
        );
      }
      foreach ($project_ids as $id) {
        $p = \codeneric\phmm\base\includes\Project::get_configuration($id);
        $t = \codeneric\phmm\base\includes\Project::get_thumbnail($id, false);
        $images_ids =
          \codeneric\phmm\base\includes\Project::get_gallery_image_ids($id);
        $title = \codeneric\phmm\base\includes\Project::get_title($id);
        $projects[] = array(
          "ID" => $id,
          "configuration" => $p,
          "thumbnail" => $t,
          "image_ids" => $images_ids,
          "title" => $title
        );
      }
      $phpversion = \phpversion();
      $active_plugins = \get_option("active_plugins");
      $plugins = \get_plugins();
      $plugin_id = \get_option("cc_photo_manage_id");
      $install_time = \get_option("codeneric/phmm/install_time");
      $premium_install_time =
        \get_option("codeneric/phmm/premium_install_time");
      $dfs = \ini_get("disable_functions");
      $df = null;
      $dt = null;
      if (!\hacklib_cast_as_boolean(\strpos($dfs, "disk_free_space"))) {
        $df = \disk_free_space(ABSPATH);
      }
      if (!\hacklib_cast_as_boolean(\strpos($dfs, "disk_total_space"))) {
        $dt = \disk_total_space(ABSPATH);
      }
      $locale = \get_locale();
      $theme = \wp_get_theme();
      $theme_data = array(
        "Name" => $theme->get("Name"),
        "Version" => $theme->get("Version"),
        "ThemeURI" => $theme->get("ThemeURI")
      );
      $res = array(
        "wp_verison" => $tmp_wp_version,
        "php_version" => $phpversion,
        "clients" => $clients,
        "projects" => $projects,
        "client_ids" => $client_ids,
        "projects_ids" => $project_ids,
        "plugins" => $plugins,
        "active_plugins" => $active_plugins,
        "phmm_settings" => $settings,
        "plugin_id" => $plugin_id,
        "base_install_time" => $install_time,
        "premium_install_time" => $premium_install_time,
        "disk_free_space" => $df,
        "disk_total_space" => $dt,
        "theme" => $theme_data,
        "locale" => $locale
      );
      $site_url = \get_site_url();
      $ajaxurl = \admin_url("admin-ajax.php");
      $res = array(
        "data" => \json_encode($res),
        "site_url" => $site_url,
        "ajaxurl" => $ajaxurl,
        "version" => 2
      );
      \wp_send_json($res);
    }
  }
}
