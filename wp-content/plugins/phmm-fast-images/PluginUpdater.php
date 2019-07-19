<?php
namespace codeneric\fastimages {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Configuration;
  class CommercialClient {
    private $APIurl = "";
    private $update_url = "";
    private $changelogURL = "";
    private $slug = "phmm-fast-images";
    private $config = null;
    private $plugin_key = 'phmm-fast-images/phmm_fast_images.php'; 
    static $instance = null;
    static function init() {
      if (\hacklib_cast_as_boolean(is_null(CommercialClient::$instance))) {
        CommercialClient::$instance = new CommercialClient();
      }
      return CommercialClient::$instance;
    }
    public function __construct() {
      $this->config = Configuration::get();
      $this->APIurl = $this->config[\hacklib_id("wpps_url")]."/";
      $this->update_url =
        $this->config[\hacklib_id("wpps_url")]."/premium/phmm-fast-images";
      $this->update_url =
        str_replace("https://", "http://", $this->update_url);
      $this->changelogURL = "http://phmm.codeneric.com/roadmap.html";
      add_filter(
        "site_transient_update_plugins",
        array($this, "checkForUpdates"),
        10,
        1
      );
      add_action(
        "install_plugins_pre_plugin-information",
        array($this, "overrideUpdateInformation"),
        1
      );
      add_action(
        "codeneric/phmm/base-plugin-updated",
        function() {
          update_option("cc_fastimages_last_time_checked_for_update", 0);
        }
      );
    }
    public function checkForUpdates($value) {
      $plugin_file =
        dirname(__FILE__)."/phmm_fast_images.php"; 
      $is_premium_plugin = true;
      include (ABSPATH.WPINC."/version.php");
      $plugin_id = Utils::get_plugin_id();
      $plugin_data = get_plugin_data($plugin_file);
      $last_time_checked =
        (int) get_option("cc_fastimages_last_time_checked_for_update", 0);
      $now = time();
      \HH\invariant(
        !\hacklib_cast_as_boolean(is_null($this->config)),
        "%s",
        new Error("Configuration is null!")
      );
      if (($now - $last_time_checked) <
          $this->config[\hacklib_id("update_check_cool_down")]) {
        $upo = get_option("cc_fast_images_update_plugin_object");
        if ($upo !== false) { // UNSAFE
          if (version_compare($plugin_data[\hacklib_id("Version")], $upo->new_version, '<' ) ) {  
            $value->response[$this->plugin_key] =
              $upo;
          }
        }
        return $value;
      }
      update_option("cc_fastimages_last_time_checked_for_update", time());
      $options = array(
        "timeout" =>
          (\hacklib_cast_as_boolean(defined("DOING_CRON")) && DOING_CRON)
            ? 30
            : 3,
        "user-agent" =>
          "WordPress/".get_bloginfo("version")."; ".get_bloginfo("url"),
        "body" => array(
          "version" => $plugin_data[\hacklib_id("Version")],
          "plugin_id" => $plugin_id
        )
      );
      $raw_response = wp_remote_post($this->update_url, $options);
      if (\hacklib_cast_as_boolean(is_wp_error($raw_response))) {
        delete_option("cc_fast_images_update_plugin_object");
        return $value;
      }
      $status_code = wp_remote_retrieve_response_code($raw_response);
      if (402 === $status_code) {
        delete_option("cc_fast_images_update_plugin_object");
        if (\hacklib_cast_as_boolean($is_premium_plugin)) {
          $cc_phmm_admin_notice_expired = function() {
            $class = "update-nag";
            $message =
              "Your PHMM Fast Images status is expired, prolong your licence to update the plugin!";
            echo ("<div class=\"".$class."\"> <p>".$message."</p></div>");
          };
          add_action("admin_notices", $cc_phmm_admin_notice_expired);
        }
      }
      if (\hacklib_not_equals(200, $status_code)) {
        delete_option("cc_fast_images_update_plugin_object");
        return $value;
      }
      try {
        $response = json_decode(wp_remote_retrieve_body($raw_response));
        if ((!isset($response)) || \hacklib_equals($response, false)) {
          return $value;
        }
      } catch (Exception $e) {
        print_r($e);
        return $value;
      }
      \HH\invariant(
        !\hacklib_cast_as_boolean(is_null($this->config)),
        "%s",
        new Error("Configuration is null!")
      );
      if (($plugin_data[\hacklib_id("Version")] === $response->new_version)) {
        delete_option("cc_fast_images_update_plugin_object");
        return $value;
      }
      $temp_test = new \stdClass();
      $temp_test->id = $response->id;
      $temp_test->slug = $response->slug;
      $temp_test->new_version = $response->new_version;
      $temp_test->url = $response->url;
      $temp_test->package = $response->package;
      \HH\invariant(
        !\hacklib_cast_as_boolean(is_null($this->config)),
        "%s",
        new Error("Configuration is null!")
      );
      $temp_test->plugin = $this->plugin_key;
      update_option("cc_fast_images_update_plugin_object", $temp_test);
      \HH\invariant(
        !\hacklib_cast_as_boolean(is_null($this->config)),
        "%s",
        new Error("Configuration is null!")
      );
      $value->response[$this->plugin_key] =
        $temp_test;
      return $value;
    }
    public function overrideUpdateInformation() {
      if (wp_unslash($_REQUEST[\hacklib_id("plugin")]) !== $this->slug) {
        return;
      }
      wp_redirect($this->changelogURL);
      exit;
    }
    public function updatePremium() {
      if (\hacklib_cast_as_boolean(
            \codeneric\phmm\Utils::wp_version_is_at_least("4.6.0")
          )) {
        require_once (ABSPATH."wp-admin/includes/class-wp-upgrader.php");
      } else {
        require_once (ABSPATH."wp-admin/includes/plugin-install.php");
      }
      $upgrader = new \Plugin_Upgrader(
        new \PHMM_Fast_Images_Installer_Upgrader_Skins()
      );
      update_option("cc_fastimages_last_time_checked_for_update", 0);
      \HH\invariant(
        !\hacklib_cast_as_boolean(is_null($this->config)),
        "%s",
        new Error("Configuration is null!")
      );
      $suc =
        $upgrader->upgrade($this->plugin_key); 
    }
  }
  add_action("plugins_loaded", array(CommercialClient::class, "init"));
}
