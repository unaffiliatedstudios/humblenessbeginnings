<?php
namespace codeneric\phmm\base\admin {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Labels;
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\base\includes\Client;
  use \codeneric\phmm\base\includes\Image;
  use \codeneric\phmm\base\includes\Project;
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\base\includes\CommentService;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\includes\ErrorSeverity;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Logger;
  use \codeneric\phmm\enums\AdvancedBoolSettings;
  class Main {
    private static $CACHE_resume_photo_upload_dir = null;
    public static function update_database() {
      $config = Configuration::get();
      \codeneric\phmm\DBUpdater::update($config);
    }
    public static function register_client_post_type() {
      $config = Configuration::get();
      $slug = $config[\hacklib_id("client_post_type")];
      $pluginName = $config[\hacklib_id("plugin_name")];
      \register_post_type(
        $slug,
        array(
          "labels" => array(
            "name" => \__("Clients", "photography-management"),
            "singular_name" => \__("Client", "photography-management"),
            "add_new" => \__("New client", "photography-management"),
            "add_new_item" => \__(
              "Add new client",
              "photography-management"
            ),
            "edit_item" => \__("Edit client", "photography-management"),
            "view_item" => \__("View client", "photography-management"),
            "all_items" => \__("Clients", "photography-management"),
            "menu_name" => "Photography Management"
          ),
          "public" => true,
          "publicly_queryable" => true,
          "show_ui" => true,
          "query_var" => true,
          "can_export" => true,
          "has_archive" => false,
          "menu_icon" => "dashicons-camera",
          "supports" => array("title", "editor"),
          "rewrite" => array("slug" => $slug, "with_front" => false),
          "taxonomies" => array()
        )
      );
    }
    public static function register_project_post_type() {
      $config = Configuration::get();
      $slug = $config[\hacklib_id("project_post_type")];
      $pluginName = $config[\hacklib_id("plugin_name")];
      \register_post_type(
        $slug,
        array(
          "labels" => array(
            "name" => \__("Projects", "photography-management"),
            "singular_name" => \__("Project", "photography-management"),
            "add_new" => \__("New project", "photography-management"),
            "add_new_item" => \__(
              "Add new project",
              "photography-management"
            ),
            "edit_item" => \__("Edit project", "photography-management"),
            "view_item" => \__("View project", "photography-management"),
            "all_items" => \__("Projects", "photography-management"),
            "menu_name" => "Photography Management"
          ),
          "public" => true,
          "publicly_queryable" => true,
          "show_ui" => true,
          "query_var" => true,
          "can_export" => true,
          "show_in_admin_bar" => true,
          "show_in_menu" =>
            "edit.php?post_type=".$config[\hacklib_id("client_post_type")],
          "has_archive" => false,
          "hierarchical" => true,
          "supports" => array("title", "editor", "thumbnail"),
          "rewrite" => array(
            "slug" => $config[\hacklib_id("project_post_type")],
            "with_front" => false
          ),
          "taxonomies" => array("category")
        )
      );
    }
    public static function add_client_meta_box() {
      $config = Configuration::get();
      $pluginName = $config[\hacklib_id("plugin_name")];
      \add_meta_box(
        $pluginName."-client-information",
        \__("Client Information", "photography-management"),
        array(FrontendHandler::class, "render_client_information_meta_box"),
        $config[\hacklib_id("client_post_type")],
        "normal",
        "high"
      );
      \add_meta_box(
        $pluginName."-client-project-access",
        \__("Project access", "photography-management"),
        array(
          FrontendHandler::class,
          "render_client_project_access_meta_box"
        ),
        $config[\hacklib_id("client_post_type")],
        "normal",
        "high"
      );
    }
    public static function add_project_meta_box() {
      $config = Configuration::get();
      \remove_meta_box(
        "postimagediv",
        $config[\hacklib_id("project_post_type")],
        "side"
      );
      $pluginName = $config[\hacklib_id("plugin_name")];
      \add_meta_box(
        $pluginName."-project-thumbnail",
        \__("Cover Image", "photography-management"),
        array(FrontendHandler::class, "render_project_thumbnail_meta_box"),
        $config[\hacklib_id("project_post_type")],
        "normal",
        "high"
      );
      \add_meta_box(
        $pluginName."-project-configuration",
        \__("Configuration", "photography-management"),
        array(
          FrontendHandler::class,
          "render_project_configuration_meta_box"
        ),
        $config[\hacklib_id("project_post_type")],
        "normal",
        "high"
      );
      \add_meta_box(
        $pluginName."-project-gallery",
        \__("Gallery", "photography-management"),
        array(FrontendHandler::class, "render_project_gallery_meta_box"),
        $config[\hacklib_id("project_post_type")],
        "normal",
        "high"
      );
    }
    public static function save_meta_box_data($post_id, $post, $is_update) {
      if (!\hacklib_cast_as_boolean($is_update)) {
        return false;
      }
      if (\hacklib_cast_as_boolean(\defined("DOING_AUTOSAVE")) &&
          \hacklib_cast_as_boolean(/* UNSAFE_EXPR */ DOING_AUTOSAVE)) {
        return false;
      }
      if ("trash" === \get_post_status($post_id)) {
        return false;
      }
      $post_type = \get_post_type($post_id);
      \HH\invariant(
        is_string($post_type),
        "%s",
        new Error(
          "Post type not string",
          array(array("post_type", $post_type))
        )
      );
      $config = Configuration::get();
      if (($post_type !== $config[\hacklib_id("client_post_type")]) &&
          ($post_type !== $config[\hacklib_id("project_post_type")])) {
        return false;
      }
      $P = Superglobals::Post();
      if ($post_type === $config[\hacklib_id("client_post_type")]) {
        $data = \codeneric\phmm\validate\client_from_client($P);
        Client::save($post_id, $data);
        \set_transient("codeneric/phmm/data_changed", true, 60);
        return true;
      }
      if ($post_type === $config[\hacklib_id("project_post_type")]) {
        if (\hacklib_cast_as_boolean(\array_key_exists("gallery", $P)) &&
            (!\hacklib_cast_as_boolean(
               \is_null($P[\hacklib_id("gallery")])
             )) &&
            \hacklib_cast_as_boolean(is_string($P[\hacklib_id("gallery")]))) {
          if ($P[\hacklib_id("gallery")] === "") {
            $P[\hacklib_id("gallery")] = array();
          } else {
            $P[\hacklib_id("gallery")] =
              \explode(",", (string) $P[\hacklib_id("gallery")]);
          }
        }
        $data = \codeneric\phmm\validate\project_from_admin($P);
        Project::save_project($post_id, $data);
        \set_transient("codeneric/phmm/data_changed", true, 60);
        return true;
      }
      return false;
    }
    public static function cleanup_before_deletion($projectID) {
      $projectID = (int) $projectID;
      $post_type = \get_post_type($projectID);
      $config = Configuration::get();
      if ($post_type === $config[\hacklib_id("client_post_type")]) {
        $clientID = $projectID;
        $client = Client::get($clientID);
        if (!\hacklib_cast_as_boolean(\is_null($client))) {
          $wp_user = $client[\hacklib_id("wp_user")];
          if (!\hacklib_cast_as_boolean(\is_null($wp_user))) {
            \wp_delete_user($wp_user->ID);
          }
        }
        return;
      }
      if ($post_type !== $config[\hacklib_id("project_post_type")]) {
        return;
      }
      $clientIDs = Client::get_all_ids();
      Client::dereference_project($projectID, $clientIDs);
      foreach ($clientIDs as $clientID) {
        Labels::delete_set(
          $clientID,
          $projectID,
          (string) \codeneric\phmm\base\includes\InternalLabelID::Favorites
        );
      }
      $settings = Settings::getCurrentSettings();
      if (\hacklib_cast_as_boolean(
            $settings[\hacklib_id("remove_images_on_project_deletion")]
          )) {
        \do_action(
          "codeneric/phmm/delete_images_unique_to_project",
          $projectID
        );
      }
    }
    public static function add_custom_image_sizes() {
      $doit = true;
      if (\hacklib_cast_as_boolean($doit)) {
        \add_image_size(
          Configuration::get()[\hacklib_id("image_size_fullscreen")],
          2000,
          2000
        );
      }
    }
    private static function parse_query() {
      $query_params = array();
      $_server = Superglobals::Server();
      $_get = Superglobals::Get();
      if (\hacklib_cast_as_boolean(
            \array_key_exists("HTTP_REFERER", $_server)
          )) {
        $comps = \parse_url($_server[\hacklib_id("HTTP_REFERER")]);
        if (\hacklib_cast_as_boolean(\array_key_exists("query", $comps))) {
          \parse_str($comps[\hacklib_id("query")], $query_params);
          return $query_params;
        }
      }
      return array();
    }
    private static function request_comes_from_project_edit_page() {
      $query_params = self::parse_query();
      if (\hacklib_cast_as_boolean(
            \array_key_exists("page", $query_params)
          ) &&
          ($query_params[\hacklib_id("page")] === "options")) {
      }
      $config = Configuration::get();
      $new_post =
        \hacklib_cast_as_boolean(\array_key_exists("post", $query_params)) &&
        (\get_post_type($query_params[\hacklib_id("post")]) ===
         $config[\hacklib_id("project_post_type")]);
      $edit_old_post =
        \hacklib_cast_as_boolean(
          \array_key_exists("post_type", $query_params)
        ) &&
        ($query_params[\hacklib_id("post_type")] ===
         $config[\hacklib_id("project_post_type")]);
      if (\hacklib_cast_as_boolean($new_post) ||
          \hacklib_cast_as_boolean($edit_old_post)) {
        return true;
      }
      return false;
    }
    public static function resume_photo_upload_dir($param) {
      if (!\hacklib_cast_as_boolean(
            \is_null(self::$CACHE_resume_photo_upload_dir)
          )) {
        return self::$CACHE_resume_photo_upload_dir;
      }
      $config = Configuration::get();
      $query_params = array();
      $_server = Superglobals::Server();
      $_get = Superglobals::Get();
      if (\hacklib_cast_as_boolean(
            \array_key_exists("HTTP_REFERER", $_server)
          )) {
        $comps = \parse_url($_server[\hacklib_id("HTTP_REFERER")]);
        if (\hacklib_cast_as_boolean(\array_key_exists("query", $comps))) {
          \parse_str($comps[\hacklib_id("query")], $query_params);
          $new_post =
            \hacklib_cast_as_boolean(
              \array_key_exists("post", $query_params)
            ) &&
            (\get_post_type($query_params[\hacklib_id("post")]) ===
             $config[\hacklib_id("project_post_type")]);
          if (\hacklib_cast_as_boolean(
                \array_key_exists("page", $query_params)
              ) &&
              ($query_params[\hacklib_id("page")] === "options")) {
            self::$CACHE_resume_photo_upload_dir = $param;
            return $param;
          }
          $edit_old_post =
            \hacklib_cast_as_boolean(
              \array_key_exists("post_type", $query_params)
            ) &&
            ($query_params[\hacklib_id("post_type")] ===
             $config[\hacklib_id("project_post_type")]);
          if (\hacklib_cast_as_boolean($new_post) ||
              \hacklib_cast_as_boolean($edit_old_post)) {
            if (\hacklib_cast_as_boolean(
                  \function_exists("add_image_size")
                )) {
            }
            $mydir =
              $config[\hacklib_id("plugin_base_url")].
              $param[\hacklib_id("subdir")];
            $param[\hacklib_id("path")] =
              $param[\hacklib_id("basedir")].$mydir;
            $param[\hacklib_id("url")] =
              $param[\hacklib_id("baseurl")].$mydir;
          }
        }
      }
      self::$CACHE_resume_photo_upload_dir = $param;
      return $param;
    }
    private static function download_proofing_csv($request) {
      $clientID = $request[\hacklib_id("client_id")];
      $projectID = $request[\hacklib_id("project_id")];
      $proofs = Labels::get_set(
        $clientID,
        $projectID,
        (string) \codeneric\phmm\base\includes\InternalLabelID::Favorites
      );
      $data =
        \array_map(
          function($imageID) use ($clientID) {
            $image = Image::get_image($imageID);
            \HH\invariant(
              !\hacklib_cast_as_boolean(\is_null($image)),
              "%s",
              new Error("Unexpected Image get failure")
            );
            return array(
              "label_name" => "Proofs",
              "label_id" =>
                (string) \codeneric\phmm\base\includes\InternalLabelID::Favorites,
              "original_filename" => $image[\hacklib_id("filename")],
              "wordpress_file_id" => $imageID,
              "client_name" => Client::get_name($clientID),
              "client_id" => $clientID
            );
          },
          $proofs
        );
      \codeneric\phmm\base\includes\FileStream::export_label_csv($data);
    }
    public static function provide_csv() {
      $get = Superglobals::Get();
      $codeneric_load_csv =
        \hacklib_cast_as_boolean(
          \array_key_exists("codeneric_load_csv", $get)
        ) ? $get[\hacklib_id("codeneric_load_csv")] : 0;
      if (\hacklib_equals(\intval($codeneric_load_csv), 1)) {
        if (!\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
          \wp_die(
            "Access not permitted",
            "Access not permitted",
            array("response" => 401)
          );
        }
        $request = \codeneric\phmm\validate\get_proofing_csv($get);
        self::download_proofing_csv($request);
      }
    }
    public static function mutate_media_modal_query($sql) {
      $x = "hello";
    }
    public static function mutate_media_library_query($query) {
      $enable_media_separation = Utils::get_advanced_bool_setting(
        AdvancedBoolSettings::PHMM_ENABLE_MEDIA_SEPARATION
      );
      if (!\hacklib_cast_as_boolean($enable_media_separation)) {
        return $query;
      }
      $_request = Superglobals::Request();
      $projects = Project::get_all_ids();
      $clients = Client::get_all_ids();
      $ids = \array_merge($projects, $clients);
      $project_edit = self::request_comes_from_project_edit_page();
      if (\hacklib_cast_as_boolean($project_edit)) {
        $query[\hacklib_id("post_parent__in")] = $ids;
      } else {
        $query[\hacklib_id("post_parent__not_in")] = $ids;
      }
      return $query;
    }
    public static function update_htaccess($old_siteurl, $new_siteurl) {
      $settings = Settings::getCurrentSettings();
      if ((!\hacklib_cast_as_boolean(
             \is_plugin_active("phmm-fast-images/phmm_fast_images.php")
           )) &&
          (!\hacklib_cast_as_boolean(
             $settings[\hacklib_id("fast_image_load")]
           ))) {
        $upload_dir = \wp_upload_dir();
        $upload_dir =
          $upload_dir[\hacklib_id("basedir")]."/photography_management";
        \Photography_Management_Base_Generate_Htaccess(
          $upload_dir."/.htaccess",
          $new_siteurl
        );
      }
    }
    public static function add_admin_notice_analytics_opt_in() {
      $config = Configuration::get();
      $settings = Settings::getCurrentSettings();
      $id = "phmm_analytics_opt_in_notice";
      if (!\hacklib_cast_as_boolean(
            $settings[\hacklib_id("analytics_opt_in")]
          )) {
        Utils::add_admin_notice(
          "<p><strong>Photography Management</strong>: ".
          \__(
            "Want to help us improve the plugin?",
            "photography-management"
          ).
          "</p>".
          "<p>".
          \__(
            "Please allow Photography Management to send anonymous usage statistics and crash reports.",
            "photography-management"
          ).
          "<p><button type=\"button\" id=\"cc-phmm-analytics-opt-in-deny\" class=\"button cc-phmm-dismiss\">".
          \__("No, thanks", "photography-management").
          "</button> <button type=\"button\" class=\"button button-primary cc-phmm-analytics-opt-in-allow cc-phmm-dismiss\">".
          \__("Yes, sure!", "photography-management").
          "</button></p>".
          "<script>\n        jQuery(\"#cc-phmm-analytics-opt-in-deny\").on(\"click\", function() {\n           jQuery(\"#".
          $id.
          "\").fadeOut();\n        });\n          jQuery(\".cc-phmm-analytics-opt-in-allow\").on(\"click\", function() {\n               jQuery.post(ajaxurl, { action: \"analytics_opt_in_allow\", payload: undefined });\n               jQuery(\"#".
          $id.
          "\").fadeOut();\n\n          })\n        </script>",
          "info",
          $id,
          0,
          true
        );
      }
    }
    public static function add_admin_notice_fast_images_available_for_free() {
      $phmm_fi_id = "phmm-fast-images/phmm_fast_images.php";
      if (!\hacklib_cast_as_boolean(\is_plugin_active($phmm_fi_id))) {
        $pdp = \plugin_dir_path(__FILE__);
        $fi_file = /* UNSAFE_EXPR */ WP_PLUGIN_DIR."/".$phmm_fi_id;
        $is_installed = \file_exists($fi_file);
        if (!\hacklib_cast_as_boolean($is_installed)) {
          Utils::add_admin_notice(
            \__(
              "<p><strong>Photography Management</strong>: use the coupon <i>freespeed</i> and get the <strong><a href=\"https://codeneric.com/shop/phmm-fast-images/\">PHMM Fast Images</a></strong> extension for free!</p>",
              "photography-management"
            ),
            "info",
            "fast_images_available_for_free",
            60 * 60 * 24 * 7,
            true
          );
        } else {
          $dl_link = "https://codeneric.com/account/downloads/";
          $plugin_data = \get_plugin_data($fi_file, false, false);
          $version = $plugin_data[\hacklib_id("Version")];
          if (\hacklib_cast_as_boolean(
                \version_compare($version, "5.1", "<=")
              )) {
            Utils::add_admin_notice(
              \sprintf(
                \__(
                  "<p><strong>PHMM Fast Images</strong>: the plugin was deactivated, because it is outdated. Please download the <strong><a href=\"%s\">new version</a></strong>, delete the old version and upload the new one!</p>",
                  "photography-management"
                ),
                $dl_link
              ),
              "info",
              "fast_images_should_be_updated",
              60 * 60 * 24 * 7,
              true
            );
          }
        }
      }
    }
    public static function add_admin_notice_rate_the_plugin() {
      $config = Configuration::get();
      $install_time =
        (int) \get_option($config[\hacklib_id("option_install_time")], 0);
      if ((\time() - $install_time) >
          $config[\hacklib_id("ask_for_rating_cooldown")]) {
        $rating_url =
          "https://wordpress.org/support/plugin/photography-management/reviews/?filter=5#postform";
        $support_url = \menu_page_url(SupportPage::page_name, false);
        Utils::add_admin_notice(
          "<p><strong>Photography Management</strong>: ".
          \__(
            "How do you like Photography Managment?",
            "photography-management"
          ).
          "<br> <a class=\"cc-phmm-dismiss\" href=\"".
          $rating_url.
          "\" >".
          \__("LOVE IT, 5 STARS!", "photography-management").
          "</a>  &nbsp;&nbsp;&nbsp;&nbsp;   <a class=\"cc-phmm-dismiss\" href=\"".
          $support_url.
          "\">".
          \__("don't like it.", "photography-management").
          "</a> </p>",
          "info",
          "rate_the_plugin",
          $config[\hacklib_id("ask_for_rating_cooldown")],
          true
        );
      }
    }
    public static function add_admin_notice_update_phmm_fast_images() { // UNSAFE
      $phmm_fi_version =
        \hacklib_cast_as_boolean(defined("PHMM_FI_VERSION"))
          ? PHMM_FI_VERSION
          : "4.1.1";
      $config = Configuration::get();
      $phmm_fi_base_name = "phmm-fast-images/phmm_fast_images.php";
      $dl_link = "https://codeneric.com/account/downloads/";
      if (\hacklib_cast_as_boolean(is_plugin_active($phmm_fi_base_name)) &&
          \hacklib_cast_as_boolean(
            version_compare($phmm_fi_version, "5.1.1", "<")
          )) {
        deactivate_plugins($phmm_fi_base_name);
        Utils::add_admin_notice(
          "<p><strong>PHMM Fast Images</strong>: ".
          __(
            "The plugin was deactivated, because it is outdated. Please replace it with the <a href=\"$dl_link\">latest version</a>!",
            "photography-management"
          ).
          "</p>",
          "error",
          "update_phmm_fast_images"
        );
      }
    }
    public static function remove_update_cache() {
      $pagenow = Superglobals::Globals("pagenow");
      $on_plugins_page = $pagenow === "plugins.php";
      if (\hacklib_cast_as_boolean($on_plugins_page)) {
        \update_option("cc_phmm_last_time_checked_for_update", 0);
      }
    }
    public static function plugin_settings_changed($old_value, $new_value) {
      $settings = Settings::getCurrentSettings();
      $upload_dir = \wp_upload_dir();
      $upload_dir =
        $upload_dir[\hacklib_id("basedir")]."/photography_management";
      if (\hacklib_cast_as_boolean(
            $settings[\hacklib_id("fast_image_load")]
          )) {
        if (\hacklib_cast_as_boolean(
              \file_exists($upload_dir."/.htaccess")
            )) {
          \unlink($upload_dir."/.htaccess");
        }
      } else {
        if (!\hacklib_cast_as_boolean(
              \is_plugin_active("phmm-fast-images/phmm_fast_images.php")
            )) {
          \Photography_Management_Base_Generate_Htaccess(
            $upload_dir."/.htaccess"
          );
        } else {
          \do_action("codeneric/phmm/refresh-htaccess");
        }
      }
    }
    public static function plugin_deactivation_survey() {
      $config = Configuration::get();
      $pagenow = Superglobals::Globals("pagenow");
      $R = Superglobals::Request();
      $P = Superglobals::Post();
      $S = Superglobals::Server();
      $plugin = "photography-management/photography_management.php";
      if ($pagenow !== "plugins.php") {
        return;
      }
      if (!\hacklib_cast_as_boolean(\array_key_exists("action", $R))) {
        return;
      }
      if (\hacklib_cast_as_boolean(\array_key_exists("action", $R)) &&
          ("deactivate" !== $R[\hacklib_id("action")])) {
        return;
      }
      if (!\hacklib_cast_as_boolean(\array_key_exists("plugin", $R))) {
        return;
      }
      if (\hacklib_cast_as_boolean(\array_key_exists("plugin", $R)) &&
          ($plugin !== $R[\hacklib_id("plugin")])) {
        return;
      }
      if (\hacklib_cast_as_boolean(\array_key_exists("ignore_sv", $R))) {
        return;
      }
      $ignore_sv_url = (string) /* UNSAFE_EXPR */ \add_query_arg(
        array("ignore_sv" => 1),
        $S[\hacklib_id("REQUEST_URI")]
      );
      if (\hacklib_cast_as_boolean(\array_key_exists("submit_sv", $P)) &&
          ($P[\hacklib_id("submit_sv")] === "1")) {
        $ds_post = \codeneric\phmm\validate\deactivation_survey_POST($P);
        $email = $ds_post[\hacklib_id("email")];
        $email =
          ((!\hacklib_cast_as_boolean(\is_null($email))) &&
           \hacklib_cast_as_boolean($ds_post[\hacklib_id("allow_contact")]))
            ? $email
            : "UNKNOWN";
        $data = array(
          "version" => $config[\hacklib_id("version")],
          "reason" => \sanitize_text_field($ds_post[\hacklib_id("reason")]),
          "explanation" => \sanitize_text_field(
            $ds_post[\hacklib_id("explanation")]
          ),
          "email" => $email
        );
        $body = array(
          "site_url" => "UNKNOWN",
          "type" => "deactivation_survey",
          "context" => "phmm",
          "version" => 1,
          "data" => $data
        );
        $args = array(
          "body" => $body,
          "blocking" => false,
          "sslverify" => false
        );
        \wp_remote_post(
          $config[\hacklib_id("wpps_url")]."/event/2.0/",
          $args
        );
        if (($ds_post[\hacklib_id("allow_contact")] === true) &&
            \hacklib_cast_as_boolean(
              \is_email($ds_post[\hacklib_id("email")])
            )) {
          $res = \wp_mail(
            "support@codeneric.com",
            "Deactivation Survey",
            \sprintf(
              "%s: %s",
              \sanitize_text_field($ds_post[\hacklib_id("reason")]),
              \sanitize_text_field($ds_post[\hacklib_id("explanation")])
            ),
            array(
              "From: ".
              \get_bloginfo("name").
              " <".
              \sanitize_text_field($email).
              ">"
            )
          );
        }
        \wp_safe_redirect($ignore_sv_url);
        exit();
      } else {
        $deactivation_survey =
          \file_get_contents(__DIR__."/../assets/deactivation-survey.html");
        $deactivation_survey = \str_replace(
          "%%ADMIN_PLUGINS_URL%%",
          \admin_url("plugins.php"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%ADMIN_EMAIL%%",
          \get_bloginfo("admin_email", "display"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%JUST_DEACTIVATE_URL%%",
          $ignore_sv_url,
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_Cancel%%",
          \__("Cancel", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey =
          \str_replace(
            "%%_I18_title%%",
            \__(
              "If you have a moment, please let me know why you are deactivating Photography Management:",
              "photography-management"
            ),
            $deactivation_survey
          );
        $deactivation_survey = \str_replace(
          "%%_I18_couldnt_understand%%",
          \__(
            "couldn't understand how to make it work",
            "photography-management"
          ),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_happy_to_help%%",
          \__(
            "we are happy to help you out! Just drop us a mail:",
            "photography-management"
          ),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_found_better_plugin%%",
          \__("Found a better plugin", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_missing_feature%%",
          \__(
            "Photography Management is missing a feature",
            "photography-management"
          ),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_not_looking_for%%",
          \__("It's not what I was looking for", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_didnt_work_as_expected%%",
          \__(
            "The plugin didn't work as expected",
            "photography-management"
          ),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_deactivate_temporarily%%",
          \__(
            "Deactivating temporarily to test something",
            "photography-management"
          ),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_other%%",
          \__("Other", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_reach_me_at%%",
          \__("You can reach me at", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_to_discuss%%",
          \__("to discuss in more detail", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey = \str_replace(
          "%%_I18_just_deactivate%%",
          \__("I just want to deactivate", "photography-management"),
          $deactivation_survey
        );
        $deactivation_survey =
          \str_replace(
            "%%_I18_no_additional_data%%",
            \__(
              "The only information stored is your response to this survey. No additional data is collected about you or your website. You will not be added to any email list.",
              "photography-management"
            ),
            $deactivation_survey
          );
        \wp_die(
          $deactivation_survey,
          "Deactivating Photography Management..."
        );
      }
    }
  }
}
