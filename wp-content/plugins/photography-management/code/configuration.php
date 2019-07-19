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
      if (\hacklib_cast_as_boolean(\is_null($cache))) {
        self::$CACHE_plugins_url = array();
        $cache = array();
      }
      if (\hacklib_cast_as_boolean(\array_key_exists($key, $cache))) {
        return $cache[$key];
      }
      $res = \plugins_url($p1, $p2);
      $cache[$key] = $res;
      self::$CACHE_plugins_url = $cache;
      return $res;
    }
    private static function _get_plugin_data_cached($p1) {
      $key = $p1;
      $cache = self::$CACHE_get_plugin_data;
      if (\hacklib_cast_as_boolean(\is_null($cache))) {
        self::$CACHE_get_plugin_data = array();
        $cache = array();
      }
      if (\hacklib_cast_as_boolean(\array_key_exists($key, $cache))) {
        return $cache[$key];
      }
      $res = \get_plugin_data($p1);
      $cache[$key] = $res;
      self::$CACHE_get_plugin_data = $cache;
      return $res;
    }
    private static function premium_is_installed() {
      $a_p = \get_option("active_plugins");
      $the_plugs = \get_site_option("active_sitewide_plugins");
      $premium_ext_active =
        (\hacklib_cast_as_boolean(is_array($a_p)) &&
         \hacklib_cast_as_boolean(\in_array(self::premium_key, $a_p))) ||
        (\hacklib_cast_as_boolean(is_array($the_plugs)) &&
         \hacklib_cast_as_boolean(
           \array_key_exists(self::premium_key, $the_plugs)
         ));
      return $premium_ext_active;
    }
    private static function premium_is_active() {
      $all_plugins = \get_plugins();
      $has_premium_ext = \array_key_exists(self::premium_key, $all_plugins);
      return $has_premium_ext;
    }
    private static function premium_is_incompatible() {
      $plugin_file_path =
        \plugin_dir_path(__FILE__)."../photography_management.php";
      $plugin_data = self::_get_plugin_data_cached($plugin_file_path);
      $base_version = $plugin_data[\hacklib_id("Version")];
      $premium_version = "0.0.0";
      $premium_path = \dirname(__FILE__)."/../../".self::premium_key;
      if (\hacklib_cast_as_boolean(\file_exists($premium_path))) {
        $premium_data = self::_get_plugin_data_cached($premium_path);
        $premium_version = $premium_data[\hacklib_id("Version")];
        return \version_compare($premium_version, "4.3.3", "<");
      }
      return false;
    }
    public static function dispaly_admin_notice_update_premium() {
      $class = "notice notice-error";
      $plugins_page = \admin_url("plugins.php");
      $plugin_file_path =
        \plugin_dir_path(__FILE__)."../photography_management.php";
      $plugin_data = self::_get_plugin_data_cached($plugin_file_path);
      $base_version = $plugin_data[\hacklib_id("Version")]; /* UNSAFE_EXPR */
      $message =
        \sprintf(
          \__(
            "Please <a href=\"%s\">update</a> Photography Management Premium to version %s.",
            "photography-management"
          ),
          $plugins_page,
          $base_version
        ); /* UNSAFE_EXPR */
      \printf("<div class=\"notice notice-error\"><p>%s</p></div>", $message);
    }
    private static function add_admin_notice_update_premium() {
      \add_action(
        "admin_notices",
        array(self::class, "dispaly_admin_notice_update_premium")
      );
    }
    public static function get() {
      $plugin_file_path =
        \plugin_dir_path(__FILE__)."../photography_management.php";
      $plugin_data = self::_get_plugin_data_cached($plugin_file_path);
      $base_version = $plugin_data[\hacklib_id("Version")];
      $has_manifest_filter = \has_filter("codeneric/phmm/premium/manifest");
      if ((self::$CACHE_FACTOR__filter__get === $has_manifest_filter) &&
          (!\hacklib_cast_as_boolean(\is_null(self::$CACHE_get)))) {
        return self::$CACHE_get;
      }
      self::$CACHE_FACTOR__filter__get = $has_manifest_filter;
      $env = "production";
      $manifestPath = "";
      $incompatible = self::premium_is_incompatible();
      if (\hacklib_cast_as_boolean($has_manifest_filter) &&
          (!\hacklib_cast_as_boolean($incompatible))) {
        self::$target = "premium";
        $manifestPath =
          \apply_filters("codeneric/phmm/premium/manifest", null);
      } else {
        $manifestPath = \plugin_dir_path(__FILE__)."assets/js/manifest.json";
      }
      if (\hacklib_cast_as_boolean(self::premium_is_active()) &&
          \hacklib_cast_as_boolean($incompatible)) {
        self::add_admin_notice_update_premium();
      }
      \HH\invariant(
        is_string($manifestPath),
        "%s",
        new Error("Path to manifest.json not a string.")
      );
      \HH\invariant(
        \file_exists($manifestPath),
        "%s",
        new Error(
          "Given manifest.json does not exist",
          array(array("manifestPath", $manifestPath))
        )
      );
      \HH\invariant(
        \file_exists($plugin_file_path),
        "%s",
        new Error(
          "Given plugin file does not exist",
          array(array("manifestPath", $manifestPath))
        )
      );
      $string = \file_get_contents($manifestPath);
      $manifest = \json_decode($string, true);
      $getEntryPoints = function($manifest) {
        return $manifest[\hacklib_id("entrypoints")];
      };
      $manifest = $getEntryPoints($manifest);
      \HH\invariant(
        ($env === "development") || ($env === "production"),
        "%s",
        new Error("Setting plugin env failed.")
      );
      \HH\invariant(
        \file_exists($plugin_file_path),
        "%s",
        new Error(
          "Given plugin file does not exist at path",
          array(array("plugin_file_path", $plugin_file_path))
        )
      );
      \HH\invariant(
        \function_exists("get_plugins"),
        "%s",
        new Error("get_plugins function is not defined.")
      );
      $has_premium_ext = self::premium_is_installed();
      $premium_ext_active = self::premium_is_active();
      $project_post_type =
        \hacklib_cast_as_boolean(\defined("CODENERIC_PHMM_PROJECT_SLUG"))
          ? /* UNSAFE_EXPR */ CODENERIC_PHMM_PROJECT_SLUG
          : "project";
      $getJsPath = function($list) use ($env, $manifestPath) {
        $res = array();
        foreach ($list as $asset) {
          if ($env !== "production") {
            \array_push($res, $asset);
          } else {
            \array_push(
              $res,
              self::_plugins_url_cached($asset, $manifestPath)
            );
          }
        }
        return $res;
      };
      $js = array(
        "admin" => array(
          "client" => $getJsPath(
            $manifest[\hacklib_id("admin.client")][\hacklib_id("js")]
          ),
          "migration" => $getJsPath(
            $manifest[\hacklib_id("admin.migration")][\hacklib_id("js")]
          ),
          "project" => $getJsPath(
            $manifest[\hacklib_id("admin.project")][\hacklib_id("js")]
          ),
          "premium_page" => $getJsPath(
            $manifest[\hacklib_id("admin.premiumpage")][\hacklib_id("js")]
          ),
          "settings" => $getJsPath(
            $manifest[\hacklib_id("admin.settings")][\hacklib_id("js")]
          ),
          "support_page" => $getJsPath(
            $manifest[\hacklib_id("admin.supportpage")][\hacklib_id("js")]
          ),
          "interactions_page" => $getJsPath(
            $manifest[\hacklib_id("admin.interactionspage")][\hacklib_id(
              "js"
            )]
          ),
          "product_tour" => $getJsPath(
            $manifest[\hacklib_id("admin.producttour")][\hacklib_id("js")]
          ),
          "data" => $getJsPath(
            $manifest[\hacklib_id("data.c")][\hacklib_id("js")]
          ),
          "support_chat" => $getJsPath(
            $manifest[\hacklib_id("support.chat")][\hacklib_id("js")]
          )
        ),
        "public" => array(
          "client" => $getJsPath(
            $manifest[\hacklib_id("public.client")][\hacklib_id("js")]
          ),
          "project" => $getJsPath(
            $manifest[\hacklib_id("public.project")][\hacklib_id("js")]
          )
        )
      );
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
                "css" => array(
                  "public" => array(
                    "projects" => self::_plugins_url_cached(
                      "/assets/css/public.projects.css",
                      __FILE__
                    )
                  ),
                  "admin" => array(
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
                "js" => $js,
                "crypto" =>
                  array(
                    "pub_key" =>
                      \dirname(__FILE__).
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
            "ask_for_analytics_opt_in_cooldown" => 10,
            "text_domain" => $plugin_data[\hacklib_id("TextDomain")],
            "option_install_time" => "codeneric/phmm/install_time",
            "option_premium_install_time" =>
              "codeneric/phmm/premium_install_time",
            "option_product_tour_started" =>
              "codeneric/phmm/product_tour_started",
            "ga_tracking_id" => "UA-37826633-21"
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
                    "public" => array(
                      "projects" => self::_plugins_url_cached(
                        "/assets/css/public.projects.css",
                        __FILE__
                      )
                    ),
                    "admin" => array(
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
                  $js,
                "crypto" =>
                  array(
                    "pub_key" =>
                      \dirname(__FILE__).
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
            "ask_for_analytics_opt_in_cooldown" =>
              60 * 60 * 24 * 7 * 3,
            "text_domain" =>
              $plugin_data[\hacklib_id("TextDomain")],
            "option_install_time" =>
              "codeneric/phmm/install_time",
            "option_premium_install_time" =>
              "codeneric/phmm/premium_install_time",
            "option_product_tour_started" =>
              "codeneric/phmm/product_tour_started",
            "ga_tracking_id" =>
              "UA-37826633-20"
          )
        )
      );
      self::$CACHE_get = $config[$env];
      return self::$CACHE_get;
    }
  }
}
