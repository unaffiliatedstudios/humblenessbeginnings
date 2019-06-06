<?php
namespace codeneric\phmm\base\admin {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\base\frontend\Shortcodes;
  use \codeneric\phmm\base\includes\Error;
  class Settings {
    const option_group = "codeneric_phmm";
    const option_name = "codeneric_phmm_plugin_settings";
    const option_section = "cc_photo_settings_section";
    const page_name = "options";
    public static function init() {
      register_setting(
        self::option_group,
        self::option_name,
        array(self::class, "sanitize_option")
      );
      add_settings_section(
        self::option_section,
        "",
        array(self::class, "settings_section_callback"),
        self::option_group
      );
    }
    public static function sanitize_option($options) {
      if (\hacklib_cast_as_boolean(is_null($options))) {
        return array();
      }
      $booleanOptions = array(
        "hide_admin_bar",
        "enable_slider",
        "remove_images_on_project_deletion"
      );
      foreach ($booleanOptions as $key) {
        if (!\hacklib_cast_as_boolean(array_key_exists($key, $options))) {
          $options[$key] = null;
        }
      }
      $return = array();
      foreach ($options as $key => $value) {
        $state = null;
        switch ($key) {
          case "enable_slider":
          case "remove_images_on_project_deletion":
          case "hide_admin_bar":
            $state =
              \hacklib_cast_as_boolean(is_bool($value))
                ? $value
                : (!\hacklib_cast_as_boolean(is_null($value)));
            break;
          case "canned_emails":
            if (\hacklib_cast_as_boolean(is_array($value))) {
              $state = array_values($value);
            } else {
              $state = $value;
            }
            break;
          case "max_zip_part_size":
            $state = (int) $value;
            break;
          case "portal_page_id":
            if ($value === "") {
              $newPage = null;
            } else {
              $newPage = (int) $value;
            }
            $state = $newPage;
            break;
          case "watermark":
            $sanitized = \codeneric\phmm\validate\watermark($value);
            $state = $sanitized;
            break;
          default:
            $state = $value;
            break;
        }
        $return[$key] = $state;
      }
      return $return;
    }
    public static function settings_section_callback() {}
    public static function add_settings_page() {
      add_submenu_page(
        "edit.php?post_type=".
        Configuration::get()[\hacklib_id("client_post_type")],
        "PHMM ".__("Settings"),
        __("Settings"),
        "manage_options",
        self::page_name,
        array(self::class, "render_add_submenu_page")
      );
    }
    public static function render_add_submenu_page() {
      $title = "<h2>".__("Settings")."</h2>";
      $fbJoin =
        "<strong>".
        __(
          "Join our <a style='color: coral' target='_blank' href='https:\\/\\/www.facebook.com/groups/1529247670736165/'>facebook group</a> to get immediate help or get in contact with other photographers using WordPress!",
          Configuration::get()[\hacklib_id("plugin_name")]
        ).
        "</strong>";
      echo
        ("<form action='options.php' method='post'>\n            ".
         $title.
         "\n      <div class='postbox'>\n                <div class='inside'>")
      ;
      wp_nonce_field();
      settings_fields(self::option_group);
      do_settings_sections(self::option_group);
      echo
        ("<div id='cc_phmm_settings'  >\n       <div style=\"background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:block;\"></div>\n    </div>")
      ; /* UNSAFE_EXPR */
      submit_button();
      echo ("<hr />".$fbJoin."</div></div></form>");
    }
    public static function getCurrentSettings() {
      $settings = get_option(self::option_name, array());
      \HH\invariant(
        is_array($settings),
        "%s",
        new Error("Getting options; expected array")
      );
      $defaultSettings = self::getDefaultSettings();
      $merged = array_merge($defaultSettings, $settings);
      return $merged;
    }
    public static function getDefaultSettings() {
      $s = \codeneric\phmm\validate\plugin_settings(
        array(
          "max_zip_part_size" => Configuration::get()[\hacklib_id(
            "max_zip_part_size"
          )],
          "watermark" => array()
        )
      );
      return $s;
    }
  }
}
