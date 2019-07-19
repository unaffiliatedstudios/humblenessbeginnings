<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\admin\Settings;
  class Activator {
    public static function activate() {
      \codeneric\phmm\base\admin\Main::register_client_post_type();
      \codeneric\phmm\base\admin\Main::register_project_post_type();
      \flush_rewrite_rules();
      $upload_dir = \wp_upload_dir();
      $upload_dir =
        $upload_dir[\hacklib_id("basedir")]."/photography_management";
      if (!\hacklib_cast_as_boolean(\file_exists($upload_dir))) {
        \mkdir($upload_dir);
      }
      $settings = Settings::getCurrentSettings();
      if (\hacklib_cast_as_boolean(\function_exists("is_plugin_active")) &&
          (!\hacklib_cast_as_boolean(
             \is_plugin_active("phmm-fast-images/phmm_fast_images.php")
           )) &&
          (!\hacklib_cast_as_boolean(
             $settings[\hacklib_id("fast_image_load")]
           ))) {
        $htaccess_suc = \Photography_Management_Base_Generate_Htaccess(
          $upload_dir."/.htaccess"
        );
      }
      Utils::get_plugin_id();
    }
  }
}
