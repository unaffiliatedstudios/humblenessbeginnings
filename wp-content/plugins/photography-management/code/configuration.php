<?php
namespace codeneric\phmm {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class MODE {
    private function __construct() {}
    private static
      $hacklib_values = array(
        "DEVELOPMENT" => "development",
        "PRODUCTION" => "production"
      );
    use \HH\HACKLIB_ENUM_LIKE;
    const DEVELOPMENT = "development";
    const PRODUCTION = "production";
  }
  use \codeneric\phmm\base\includes\Error;
  require_once (ABSPATH."wp-admin/includes/plugin.php");
  class Configuration {
    static $target = "base";
    const
      premium_key = "photography-management-premium/photography_management-premium.php";
    private static $CACHE_plugins_url = null;
    private static $CACHE_get_plugin_data = null;
    private static $CACHE_get = null;
    private static $CACHE_FACTOR__filter__get = null;
    private static function _plugins_url_cached($p1, $p2) {
      $key = $p1." ".$p2;
      $cache = self::$CACHE_plugins_url;
      if (\hacklib_cast_as_boolean(is_null($cache))) {
        self::$CACHE_plugins_url = array();
        $cache = array();
      }
      if (\hacklib_cast_as_boolean(array_key_exists($key, $cache))) {
        return $cache[$key];
      }
      $res = plugins_url($p1, $p2);
      $cache[$key] = $res;
      self::$CACHE_plugins_url = $cache;
      return $res;
    }
    private static function _get_plugin_data_cached($p1) {
      $key = $p1;
      $cache = self::$CACHE_get_plugin_data;
      if (\hacklib_cast_as_boolean(is_null($cache))) {
        self::$CACHE_get_plugin_data = array();
        $cache = array();
      }
      if (\hacklib_cast_as_boolean(array_key_exists($key, $cache))) {
        return $cache[$key];
      }
      $res = get_plugin_data($p1);
      $cache[$key] = $res;
      self::$CACHE_get_plugin_data = $cache;
      return $res;
    }
    public static function get() {
      $has_manifest_filter = has_filter("codeneric/phmm/premium/manifest");
      if ((self::$CACHE_FACTOR__filter__get === $has_manifest_filter) &&
          (!\hacklib_cast_as_boolean(is_null(self::$CACHE_get)))) {
        return self::$CACHE_get;
      }
      self::$CACHE_FACTOR__filter__get = $has_manifest_filter;
      $env = "production";
      $manifestPath = "";
      if (\hacklib_cast_as_boolean($has_manifest_filter)) {
        self::$target = "premium";
        $manifestPath =
          apply_filters("codeneric/phmm/premium/manifest", null);
      } else {
        $manifestPath = plugin_dir_path(__FILE__)."assets/js/manifest.json";
      }
      $plugin_file_path =
        plugin_dir_path(__FILE__)."../photography_management.php";
      \HH\invariant(
        is_string($manifestPath),
        "%s",
        new Error("Path to manifest.json not a string.")
      );
      \HH\invariant(
        file_exists($manifestPath),
        "%s",
        new Error(
          "Given manifest.json does not exist",
          array(array("manifestPath", $manifestPath))
        )
      );
      \HH\invariant(
        file_exists($plugin_file_path),
        "%s",
        new Error(
          "Given plugin file does not exist",
          array(array("manifestPath", $manifestPath))
        )
      );
      $string = file_get_contents($manifestPath);
      $manifest = json_decode($string, true);
      \HH\invariant(
        is_array($manifest),
        "%s",
        new Error("Decoding of manifest failed.")
      );
      \HH\invariant(
        ($env === "development") || ($env === "production"),
        "%s",
        new Error("Setting plugin env failed.")
      );
      \HH\invariant(
        file_exists($plugin_file_path),
        "%s",
        new Error(
          "Given plugin file does not exist at path",
          array(array("plugin_file_path", $plugin_file_path))
        )
      );
      \HH\invariant(
        function_exists("get_plugins"),
        "%s",
        new Error("get_plugins function is not defined.")
      );
      $all_plugins = get_plugins();
      $plugin_data = self::_get_plugin_data_cached($plugin_file_path);
      $has_premium_ext =
        array_key_exists(Configuration::premium_key, $all_plugins);
      $a_p = get_option("active_plugins");
      \HH\invariant(
        is_array($a_p),
        "%s",
        new Error("active_plugins did not return an array")
      );
      $the_plugs = get_site_option("active_sitewide_plugins");
      $premium_ext_active =
        \hacklib_cast_as_boolean(in_array(Configuration::premium_key, $a_p)) ||
        (\hacklib_cast_as_boolean(is_array($the_plugs)) &&
         \hacklib_cast_as_boolean(
           array_key_exists(Configuration::premium_key, $the_plugs)
         ));
      $premium_version = "0.0.0";
      if (\hacklib_cast_as_boolean(
            file_exists(
              dirname(__FILE__)."/../".Configuration::premium_key
            )
          )) {
        $premium_data = self::_get_plugin_data_cached(
          dirname(__FILE__)."/../".Configuration::premium_key
        );
        $premium_version = $premium_data[\hacklib_id("Version")];
      }
      $project_post_type =
        \hacklib_cast_as_boolean(defined("CODENERIC_PHMM_PROJECT_SLUG"))
          ? /* UNSAFE_EXPR */ CODENERIC_PHMM_PROJECT_SLUG
          : "project";
      $getJsPath = function($asset) use ($env, $manifestPath) {
        if ($env !== "production") {
          return $asset;
        }
        return self::_plugins_url_cached($asset, $manifestPath);
      };
      $config = \HH\Map::hacklib_new(
        array("development", "production"),
        array(
          array(
            "support_email" => "support@codeneric.com",
            "manifest_path" => $manifestPath,
            "target" => self::$target,
            "revision" => "1.0.0",
            "env" => "development",
            "wpps_url" =>
              "https://headgame.draco.uberspace.de/sandbox.wpps",
            "landing_url" => "https://sandbox.phmm.codeneric.com",
            "client_post_type" => "client",
            "project_post_type" => $project_post_type,
            "plugin_name" => "photography-management",
            "premium_plugin_name" => "photography-management-premium",
            "plugin_slug_abbr" => "phmm",
            "version" => $plugin_data[\hacklib_id("Version")],
            "has_premium_ext" => $has_premium_ext,
            "premium_ext_active" => $premium_ext_active,
            "premium_plugin_key" => Configuration::premium_key,
            "update_check_cool_down" => 5,
            "assets" =>
              array(
                "css" =>
                  array(
                    "public" =>
                      array(
                        "projects" => self::_plugins_url_cached(
                          "/assets/css/public.projects.css",
                          __FILE__
                        )
                      ),
                    "admin" =>
                      array(
                        "post" => self::_plugins_url_cached(
                          "/assets/css/post.css",
                          __FILE__
                        ),
                        "custom" => self::_plugins_url_cached(
                          "/assets/css/custom.css",
                          __FILE__
                        ),
                        "fixes" => self::_plugins_url_cached(
                          "/assets/css/fixes.css",
                          __FILE__
                        )
                      )
                  ),
                "js" =>
                  array(
                    "admin" =>
                      array(
                        "common" => $getJsPath(
                          $manifest[\hacklib_id("admin.commons.js")]
                        ),
                        "client" => $getJsPath(
                          $manifest[\hacklib_id("admin.client.js")]
                        ),
                        "migration" => $getJsPath(
                          $manifest[\hacklib_id("admin.migration.js")]
                        ),
                        "project" => $getJsPath(
                          $manifest[\hacklib_id("admin.project.js")]
                        ),
                        "premium_page" => $getJsPath(
                          $manifest[\hacklib_id(
                            "admin.premiumpage.js"
                          )]
                        ),
                        "settings" => $getJsPath(
                          $manifest[\hacklib_id("admin.settings.js")]
                        ),
                        "support_page" => $getJsPath(
                          $manifest[\hacklib_id(
                            "admin.supportpage.js"
                          )]
                        ),
                        "interactions_page" => $getJsPath(
                          $manifest[\hacklib_id(
                            "admin.interactionspage.js"
                          )]
                        )
                      ),
                    "public" =>
                      array(
                        "common" => $getJsPath(
                          $manifest[\hacklib_id("public.commons.js")]
                        ),
                        "client" => $getJsPath(
                          $manifest[\hacklib_id("public.client.js")]
                        ),
                        "project" => $getJsPath(
                          $manifest[\hacklib_id("public.project.js")]
                        )
                      )
                  ),
                "crypto" =>
                  array(
                    "pub_key" =>
                      dirname(__FILE__).
                      "/assets/crypto/codeneric_support_rsa.pub"
                  )
              ),
            "max_zip_part_size" => 10,
            "plugin_base_url" => "/photography_management",
            "image_size_fullscreen" => "phmm-fullscreen",
            "phmm_posts_logout" => "codeneric_phmm_posts_logout",
            "cookie_wp_postpass" => "wp-postpass_",
            "client_user_role" => "phmm_client",
            "default_thumbnail_id_option_key" =>
              "cc_phmm_default_thumbnail_id",
            "ping_service_url" => "http://172.17.0.1:62449",
            "notification_cool_down" => 10,
            "ask_for_rating_cooldown" => 10,
            "text_domain" => $plugin_data[\hacklib_id("TextDomain")],
            "option_install_time" => "codeneric/phmm/install_time"
          ),
          array(
            "manifest_path" =>
              $manifestPath,
            "support_email" =>
              "support@codeneric.com",
            "target" =>
              self::$target,
            "revision" =>
              "1.0.0",
            "env" =>
              "production",
            "wpps_url" =>
              "https://headgame.draco.uberspace.de/wpps",
            "landing_url" =>
              "https://codeneric.com",
            "client_post_type" =>
              "client",
            "project_post_type" =>
              $project_post_type,
            "plugin_name" =>
              "photography-management",
            "premium_plugin_name" =>
              "photography-management-premium",
            "plugin_slug_abbr" =>
              "phmm",
            "version" =>
              $plugin_data[\hacklib_id("Version")],
            "has_premium_ext" =>
              $has_premium_ext,
            "premium_ext_active" =>
              $premium_ext_active,
            "premium_plugin_key" =>
              Configuration::premium_key,
            "update_check_cool_down" =>
              60 * 60,
            "assets" =>
              array(
                "css" =>
                  array(
                    "public" =>
                      array(
                        "projects" => self::_plugins_url_cached(
                          "/assets/css/public.projects.css",
                          __FILE__
                        )
                      ),
                    "admin" =>
                      array(
                        "post" => self::_plugins_url_cached(
                          "/assets/css/post.css",
                          __FILE__
                        ),
                        "custom" => self::_plugins_url_cached(
                          "/assets/css/custom.css",
                          __FILE__
                        ),
                        "fixes" => self::_plugins_url_cached(
                          "/assets/css/fixes.css",
                          __FILE__
                        )
                      )
                  ),
                "js" =>
                  array(
                    "admin" =>
                      array(
                        "common" => $getJsPath(
                          $manifest[\hacklib_id("admin.commons.js")]
                        ),
                        "client" => $getJsPath(
                          $manifest[\hacklib_id("admin.client.js")]
                        ),
                        "migration" => $getJsPath(
                          $manifest[\hacklib_id("admin.migration.js")]
                        ),
                        "project" => $getJsPath(
                          $manifest[\hacklib_id("admin.project.js")]
                        ),
                        "premium_page" => $getJsPath(
                          $manifest[\hacklib_id(
                            "admin.premiumpage.js"
                          )]
                        ),
                        "settings" => $getJsPath(
                          $manifest[\hacklib_id("admin.settings.js")]
                        ),
                        "support_page" => $getJsPath(
                          $manifest[\hacklib_id(
                            "admin.supportpage.js"
                          )]
                        ),
                        "interactions_page" => $getJsPath(
                          $manifest[\hacklib_id(
                            "admin.interactionspage.js"
                          )]
                        )
                      ),
                    "public" =>
                      array(
                        "common" => $getJsPath(
                          $manifest[\hacklib_id("public.commons.js")]
                        ),
                        "client" => $getJsPath(
                          $manifest[\hacklib_id("public.client.js")]
                        ),
                        "project" => $getJsPath(
                          $manifest[\hacklib_id("public.project.js")]
                        )
                      )
                  ),
                "crypto" =>
                  array(
                    "pub_key" =>
                      dirname(__FILE__).
                      "/assets/crypto/codeneric_support_rsa.pub"
                  )
              ),
            "max_zip_part_size" =>
              4000,
            "plugin_base_url" =>
              "/photography_management",
            "image_size_fullscreen" =>
              "phmm-fullscreen",
            "phmm_posts_logout" =>
              "codeneric_phmm_posts_logout",
            "cookie_wp_postpass" =>
              "wp-postpass_",
            "client_user_role" =>
              "phmm_client",
            "default_thumbnail_id_option_key" =>
              "cc_phmm_default_thumbnail_id",
            "ping_service_url" =>
              "http://ping.codeneric.com",
            "notification_cool_down" =>
              60 * 5,
            "ask_for_rating_cooldown" =>
              60 * 60 * 24 * 45,
            "text_domain" =>
              $plugin_data[\hacklib_id("TextDomain")],
            "option_install_time" =>
              "codeneric/phmm/install_time"
          )
        )
      );
      self::$CACHE_get = $config[$env];
      return self::$CACHE_get;
    }
  }
}
