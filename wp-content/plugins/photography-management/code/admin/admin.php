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
  use \codeneric\phmm\base\includes\BackgroundProcess;
  use \codeneric\phmm\base\includes\ErrorSeverity;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Logger;
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
      register_post_type(
        $slug,
        array(
          "labels" => array(
            "name" => __("Clients", $pluginName),
            "singular_name" => __("Client", $pluginName),
            "add_new" => __("New client", $pluginName),
            "add_new_item" => __("Add new client", $pluginName),
            "edit_item" => __("Edit client", $pluginName),
            "view_item" => __("View client", $pluginName),
            "all_items" => __("Clients", $pluginName),
            "menu_name" => __("Photography Management", $pluginName)
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
      register_post_type(
        $slug,
        array(
          "labels" => array(
            "name" => __("Projects", $pluginName),
            "singular_name" => __("Project", $pluginName),
            "add_new" => __("New project", $pluginName),
            "add_new_item" => __("Add new project", $pluginName),
            "edit_item" => __("Edit project", $pluginName),
            "view_item" => __("View project", $pluginName),
            "all_items" => __("Projects", $pluginName),
            "menu_name" => __("Photography Management", $pluginName)
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
      add_meta_box(
        $pluginName."-client-information",
        __("Client Information", $pluginName),
        array(FrontendHandler::class, "render_client_information_meta_box"),
        $config[\hacklib_id("client_post_type")],
        "normal",
        "high"
      );
      add_meta_box(
        $pluginName."-client-project-access",
        __("Project access", $pluginName),
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
      remove_meta_box(
        "postimagediv",
        $config[\hacklib_id("project_post_type")],
        "side"
      );
      $pluginName = $config[\hacklib_id("plugin_name")];
      add_meta_box(
        $pluginName."-project-thumbnail",
        __("Cover Image", $pluginName),
        array(FrontendHandler::class, "render_project_thumbnail_meta_box"),
        $config[\hacklib_id("project_post_type")],
        "normal",
        "high"
      );
      add_meta_box(
        $pluginName."-project-configuration",
        __("Configuration", $pluginName),
        array(
          FrontendHandler::class,
          "render_project_configuration_meta_box"
        ),
        $config[\hacklib_id("project_post_type")],
        "normal",
        "high"
      );
      add_meta_box(
        $pluginName."-project-gallery",
        __("Gallery", $pluginName),
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
      if (\hacklib_cast_as_boolean(defined("DOING_AUTOSAVE")) &&
          \hacklib_cast_as_boolean(/* UNSAFE_EXPR */ DOING_AUTOSAVE)) {
        return false;
      }
      if ("trash" === get_post_status($post_id)) {
        return false;
      }
      $post_type = get_post_type($post_id);
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
        return true;
      }
      if ($post_type === $config[\hacklib_id("project_post_type")]) {
        if ((!\hacklib_cast_as_boolean(is_null($P[\hacklib_id("gallery")]))) &&
            \hacklib_cast_as_boolean(is_string($P[\hacklib_id("gallery")]))) {
          if ($P[\hacklib_id("gallery")] === "") {
            $P[\hacklib_id("gallery")] = array();
          } else {
            $P[\hacklib_id("gallery")] =
              explode(",", (string) $P[\hacklib_id("gallery")]);
          }
        }
        $data = \codeneric\phmm\validate\project_from_admin($P);
        Project::save_project($post_id, $data);
        return true;
      }
      return false;
    }
    public static function cleanup_before_deletion($projectID) {
      $projectID = (int) $projectID;
      $post_type = get_post_type($projectID);
      $config = Configuration::get();
      if ($post_type === $config[\hacklib_id("client_post_type")]) {
        $clientID = $projectID;
        $client = Client::get($clientID);
        if (!\hacklib_cast_as_boolean(is_null($client))) {
          $wp_user = $client[\hacklib_id("wp_user")];
          if (!\hacklib_cast_as_boolean(is_null($wp_user))) {
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
        do_action(
          "codeneric/phmm/delete_images_unique_to_project",
          $projectID
        );
      }
    }
    public static function add_custom_image_sizes() {
      $doit = true;
      if (\hacklib_cast_as_boolean($doit)) {
        add_image_size(
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
            array_key_exists("HTTP_REFERER", $_server)
          )) {
        $comps = parse_url($_server[\hacklib_id("HTTP_REFERER")]);
        if (\hacklib_cast_as_boolean(array_key_exists("query", $comps))) {
          parse_str($comps[\hacklib_id("query")], $query_params);
          return $query_params;
        }
      }
      return array();
    }
    private static function request_comes_from_project_edit_page() {
      $query_params = self::parse_query();
      if (\hacklib_cast_as_boolean(array_key_exists("page", $query_params)) &&
          ($query_params[\hacklib_id("page")] === "options")) {
      }
      $config = Configuration::get();
      $new_post =
        \hacklib_cast_as_boolean(array_key_exists("post", $query_params)) &&
        (get_post_type($query_params[\hacklib_id("post")]) ===
         $config[\hacklib_id("project_post_type")]);
      $edit_old_post =
        \hacklib_cast_as_boolean(
          array_key_exists("post_type", $query_params)
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
            is_null(self::$CACHE_resume_photo_upload_dir)
          )) {
        return self::$CACHE_resume_photo_upload_dir;
      }
      $config = Configuration::get();
      $query_params = array();
      $_server = Superglobals::Server();
      $_get = Superglobals::Get();
      if (\hacklib_cast_as_boolean(
            array_key_exists("HTTP_REFERER", $_server)
          )) {
        $comps = parse_url($_server[\hacklib_id("HTTP_REFERER")]);
        if (\hacklib_cast_as_boolean(array_key_exists("query", $comps))) {
          parse_str($comps[\hacklib_id("query")], $query_params);
          $new_post =
            \hacklib_cast_as_boolean(array_key_exists("post", $query_params)) &&
            (get_post_type($query_params[\hacklib_id("post")]) ===
             $config[\hacklib_id("project_post_type")]);
          if (\hacklib_cast_as_boolean(
                array_key_exists("page", $query_params)
              ) &&
              ($query_params[\hacklib_id("page")] === "options")) {
            self::$CACHE_resume_photo_upload_dir = $param;
            return $param;
          }
          $edit_old_post =
            \hacklib_cast_as_boolean(
              array_key_exists("post_type", $query_params)
            ) &&
            ($query_params[\hacklib_id("post_type")] ===
             $config[\hacklib_id("project_post_type")]);
          if (\hacklib_cast_as_boolean($new_post) ||
              \hacklib_cast_as_boolean($edit_old_post)) {
            if (\hacklib_cast_as_boolean(function_exists("add_image_size"))) {
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
        array_map(
          function($imageID) use ($clientID) {
            $image = Image::get_image($imageID);
            \HH\invariant(
              !\hacklib_cast_as_boolean(is_null($image)),
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
          array_key_exists("codeneric_load_csv", $get)
        ) ? $get[\hacklib_id("codeneric_load_csv")] : 0;
      if (\hacklib_equals(intval($codeneric_load_csv), 1)) {
        if (!\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
          wp_die(
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
      $_request = Superglobals::Request();
      $projects = Project::get_all_ids();
      $clients = Client::get_all_ids();
      $ids = array_merge($projects, $clients);
      $project_edit = self::request_comes_from_project_edit_page();
      if (\hacklib_cast_as_boolean($project_edit)) {
        $query[\hacklib_id("post_parent__in")] = $ids;
      } else {
        $query[\hacklib_id("post_parent__not_in")] = $ids;
      }
      return $query;
    }
    public static function update_htaccess($old_siteurl, $new_siteurl) {
      if (!\hacklib_cast_as_boolean(
            is_plugin_active("phmm-fast-images/phmm_fast_images.php")
          )) {
        $upload_dir = wp_upload_dir();
        $upload_dir =
          $upload_dir[\hacklib_id("basedir")]."/photography_management";
        Photography_Management_Base_Generate_Htaccess(
          $upload_dir."/.htaccess",
          $new_siteurl
        );
      }
    }
    public static function add_admin_notice_fast_images_available_for_free() {
      if (!\hacklib_cast_as_boolean(
            \is_plugin_active("phmm-fast-images/phmm_fast_images.php")
          )) {
        Utils::add_admin_notice(
          "<p><strong>Photography Management</strong>: use the coupon <i>freespeed</i> and get the <strong><a href=\"https://codeneric.com/shop/phmm-fast-images/\">PHMM Fast Images</a></strong> extension for free!</p>",
          "info",
          "fast_images_available_for_free",
          60 * 60 * 24 * 7,
          true
        );
      }
    }
    public static function add_admin_notice_rate_the_plugin() {
      $config = Configuration::get();
      $td = $config[\hacklib_id("text_domain")];
      $install_time =
        (int) get_option($config[\hacklib_id("option_install_time")], 0);
      if ((time() - $install_time) >
          $config[\hacklib_id("ask_for_rating_cooldown")]) {
        $rating_url =
          "https://wordpress.org/support/plugin/photography-management/reviews/?filter=5#postform";
        $support_url = menu_page_url(SupportPage::page_name, false);
        Utils::add_admin_notice(
          "<p><strong>Photography Management</strong>: ".
          __("How do you like Photography Managment?", $td).
          "<br> <a class=\"cc-phmm-dismiss\" href=\"".
          $rating_url.
          "\" >".
          __("LOVE IT, 5 STARS!", $td).
          "</a>  &nbsp;&nbsp;&nbsp;&nbsp;   <a class=\"cc-phmm-dismiss\" href=\"".
          $support_url.
          "\">".
          __("don't like it.", $td).
          "</a> </p>",
          "info",
          "rate_the_plugin",
          $config[\hacklib_id("ask_for_rating_cooldown")],
          true
        );
      }
    }
  }
}
