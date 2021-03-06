<?php
namespace codeneric\phmm {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\enums\AdvancedBoolSettings;
  class Utils {
    public static function admin_user_ids() {
      $wpdb = Superglobals::Globals("wpdb");
      \HH\invariant(
        $wpdb instanceof \wpdb,
        "%s",
        new Error("Can not get global wpdb object!")
      );
      $wp_user_search = $wpdb->get_results(
        "SELECT ID, display_name FROM ".$wpdb->users." ORDER BY ID"
      );
      $adminArray = array();
      foreach ($wp_user_search as $userid) {
        $curID = $userid->ID;
        $curuser = \get_userdata($curID);
        if ($curuser instanceof \WP_User) {
          $user_level = $curuser->user_level;
          if ($user_level >= 8) {
            $adminArray[] = $curID;
          }
        }
      }
      return $adminArray;
    }
    public static function is_current_user_admin() {
      $allow_editors = self::get_advanced_bool_setting(
        AdvancedBoolSettings::PHMM_ALLOW_EDITORS
      );
      $result = false;
      if (\hacklib_cast_as_boolean($allow_editors)) {
        $result =
          \hacklib_cast_as_boolean(\current_user_can("administrator")) ||
          \hacklib_cast_as_boolean(\current_user_can("editor"));
      } else {
        $result = \current_user_can("administrator");
      }
      $result = \apply_filters("codeneric/phmm/user_can_administer", $result);
      return $result;
    }
    public static function string_contains($haystack, $needle) {
      $strpos = \strpos($haystack, $needle);
      return $strpos !== false;
    }
    private static function generate_unique_id() {
      $uniqid = \uniqid("", true);
      $uniqid = \str_replace(".", "", $uniqid);
      return $uniqid;
    }
    public static function get_plugin_id() {
      $id = \get_option("cc_photo_manage_id", null);
      if (!\hacklib_cast_as_boolean(is_string($id))) {
        $id = self::generate_unique_id();
        \update_option("cc_photo_manage_id", $id);
      }
      return $id;
    }
    public static function apply_filter_or($filterHandle, $arg, $or) {
      if (\hacklib_cast_as_boolean(\has_filter($filterHandle))) {
        return \apply_filters($filterHandle, $arg);
      } else {
        return $or;
      }
    }
    public static function wp_version_is_at_least($version) {
      $actual = \get_bloginfo("version");
      return \version_compare($actual, $version, ">=");
    }
    public static function php_version_is_at_least($version) {
      $actual = \phpversion();
      return \version_compare($actual, $version, ">=");
    }
    public static function wp_version_is_lower_than($version) {
      $actual = \get_bloginfo("version");
      return \version_compare($actual, $version, "<");
    }
    public static function array_nested_key_exist(
      $path,
      $array,
      $separator = "."
    ) {
      if (!\hacklib_cast_as_boolean(is_array($array))) {
        return false;
      }
      $paths = \explode($separator, $path);
      $p = \array_shift($paths);
      if (\hacklib_cast_as_boolean(\array_key_exists($p, $array))) {
        $arr = $array[$p];
        $path = \implode($separator, $paths);
        if ($path === "") {
          return true;
        }
        return self::array_nested_key_exist($path, $arr, $separator);
      }
      return false;
    }
    public static function get_temp_file($prefix) {
      $tmp_dir = \sys_get_temp_dir();
      $temp_file = \tempnam($tmp_dir, $prefix);
      \HH\invariant(
        is_string($temp_file),
        "%s",
        new Error("Unable to get a filename for a temporary file.")
      );
      $handle = \fopen($temp_file, "w");
      \HH\invariant(
        is_resource($handle),
        "%s",
        new Error("Could not create temporary file.")
      );
      return array("resource" => $handle, "name" => $temp_file);
    }
    public static function close_and_delete_file($handle, $name) {
      return
        \hacklib_cast_as_boolean(\fclose($handle)) &&
        \hacklib_cast_as_boolean(\unlink($name));
    }
    public static function get_current_user_id() {
      return \get_current_user_id();
    }
    public static function get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS(
      $postID,
      $key
    ) {
      $meta = \get_post_meta($postID, (string) $key, true);
      if ($meta === "") {
        return null;
      } else {
        return $meta;
      }
    }
    public static function array_reduce($array, $f, $init) {
      $temp = $init;
      $res = array();
      foreach ($array as $i => $item) {
        $temp = $f($temp, $item);
      }
      return $temp;
    }
    public static function array_merge($a, $b) {
      $res = $a;
      foreach ($b as $item) {
        $res[] = $item;
      }
      return $res;
    }
    public static function get_intermediate_image_sizes() {
      $phmmsize = Configuration::get()[\hacklib_id("image_size_fullscreen")];
      $allowed_sizes = array(
        "thumbnail",
        "medium",
        "medium_large",
        "large",
        $phmmsize
      );
      $res = array();
      foreach (\get_intermediate_image_sizes() as $_size) {
        if (\hacklib_cast_as_boolean(\in_array($_size, $allowed_sizes))) {
          $res[] = $_size;
        }
      }
      return $res;
    }
    public static function get_uncropped_image_sizes() {
      $_wp_additional_image_sizes = \wp_get_additional_image_sizes();
      $sizes = array();
      foreach (self::get_intermediate_image_sizes() as $_size) {
        if (\hacklib_cast_as_boolean(
              \in_array(
                $_size,
                array("thumbnail", "medium", "medium_large", "large")
              )
            )) {
          $sizes[$_size] = array("width" => 0, "height" => 0, "crop" => true);
          $sizes[$_size][\hacklib_id("width")] =
            (int) \get_option($_size."_size_w");
          $sizes[$_size][\hacklib_id("height")] =
            (int) \get_option($_size."_size_h");
          $sizes[$_size][\hacklib_id("crop")] =
            (bool) \hacklib_cast_as_boolean(\get_option($_size."_crop"));
        } else {
          if (\hacklib_cast_as_boolean(
                is_array($_wp_additional_image_sizes)
              ) &&
              \hacklib_cast_as_boolean(
                \array_key_exists($_size, $_wp_additional_image_sizes)
              )) {
            $sizes[$_size] = array(
              "width" =>
                (int) $_wp_additional_image_sizes[$_size][\hacklib_id(
                  "width"
                )],
              "height" =>
                (int) $_wp_additional_image_sizes[$_size][\hacklib_id(
                  "height"
                )],
              "crop" => (bool) \hacklib_cast_as_boolean(
                $_wp_additional_image_sizes[$_size][\hacklib_id("crop")]
              )
            );
          }
        }
      }
      $sizes = \array_filter(
        $sizes,
        function($s) {
          return !\hacklib_cast_as_boolean($s[\hacklib_id("crop")]);
        }
      );
      return $sizes;
    }
    public static function time() {
      return (float) \microtime(true);
    }
    public static function get_admin_notice_transient_key($id) {
      return \md5("codeneric/phmm/add_admin_notice/".$id);
    }
    public static function add_admin_notice(
      $html,
      $type,
      $id,
      $cooldown_in_seconds = 0,
      $dismissable = false
    ) {
      $transient = self::get_admin_notice_transient_key($id);
      $currently_cooling_down =
        (bool) \hacklib_cast_as_boolean(\get_transient($transient));
      if (\hacklib_cast_as_boolean($currently_cooling_down)) {
        return;
      }
      $f =
        function(
        ) use ($html, $dismissable, $type, $id, $cooldown_in_seconds) {
          $dis =
            \hacklib_cast_as_boolean($dismissable) ? "is-dismissible" : "";
          echo
            ("<div class=\"notice notice-".
             $type.
             " ".
             $dis.
             "\" id=\"".
             $id.
             "\" data-cooldown_in_seconds=\"".
             $cooldown_in_seconds.
             "\" >".
             $html.
             "</div>")
          ;
        };
      $cooldown_in_seconds =
        ($cooldown_in_seconds <= 0) ? 1 : $cooldown_in_seconds;
      \add_action("admin_notices", $f);
      $code_injection =
        function() {
          echo
            ("<script type=\"text/javascript\">\n          if (!window.codeneric_phmm_admin_notice_logic_applied) {\n    window.codeneric_phmm_admin_notice_logic_applied = true;\n\n    function waitForEl(selector, callback) {\n\n        var poller = setInterval(function() {\n\n          var el = jQuery(selector).find('button.notice-dismiss');\n          if(el.length < 1)\n            return;\n          clearInterval(poller);\n          callback(el);\n        },100);\n    };\n      jQuery('.notice.is-dismissible').each(function (i, e) {\n          var id = jQuery(e).attr('id');\n          var cooldown_in_seconds = jQuery(e).attr('data-cooldown_in_seconds');\n          var payload = JSON.stringify({ id: id, cooldown_in_seconds: cooldown_in_seconds });\n\n          waitForEl(e, function() {\n            jQuery(e).find('button.notice-dismiss, .cc-phmm-dismiss').on('click', function (e) {\n\n              jQuery.post(ajaxurl, { action: 'phmm_dismiss_notice', payload: payload });\n            });\n\n          });\n        });\n\n\n          }\n          </script>")
          ;
        };
      \add_action("admin_footer", $code_injection);
    }
    public static function get_protocol_relative_url($url) {
      $disallowed = array("http://", "https://");
      foreach ($disallowed as $d) {
        if (\strpos($url, $d) === 0) {
          return \str_replace($d, "//", $url);
        }
      }
      return $url;
    }
    private static function _get_bool_constant($setting, $default) {
      if (!\hacklib_cast_as_boolean(\defined((string) $setting))) {
        return $default;
      } else {
        return (bool) \hacklib_cast_as_boolean(\constant((string) $setting));
      }
    }
    public static function get_advanced_bool_setting($setting) {
      switch ($setting) {
        case AdvancedBoolSettings::PHMM_ALLOW_EDITORS:
          return self::_get_bool_constant($setting, true);
        case AdvancedBoolSettings::PHMM_REDIRECT_CLIENT_TO_SINGLE_PROJECT:
          return self::_get_bool_constant($setting, true);
        case AdvancedBoolSettings::PHMM_APPLY_TEMPLATE_TO_CLIENT_PAGE:
          return self::_get_bool_constant($setting, true);
        case AdvancedBoolSettings::PHMM_ENABLE_MEDIA_SEPARATION:
          return self::_get_bool_constant($setting, false);
      }
    }
  }
}
