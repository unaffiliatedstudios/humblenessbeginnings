<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  require_once (plugin_dir_path(__FILE__)."image.php");
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\base\admin\Settings;
  class Project {
    public static function is_assigned_to_at_least_one_client($projectID) {
      $clientIDs = Client::get_all_ids();
      foreach ($clientIDs as $id) {
        $projectIDs = Client::get_project_ids($id);
        if (\hacklib_cast_as_boolean(in_array($projectID, $projectIDs))) {
          return true;
        }
      }
      return false;
    }
    public static function save_project($id, $project) {
      update_post_meta(
        $id,
        "version",
        Configuration::get()[\hacklib_id("version")]
      );
      update_post_meta($id, "gallery", $project[\hacklib_id("gallery")]);
      update_post_meta(
        $id,
        "protection",
        $project[\hacklib_id("protection")]
      );
      update_post_meta(
        $id,
        "post_password",
        $project[\hacklib_id("protection")][\hacklib_id("password")]
      );
      if (!\hacklib_cast_as_boolean(
            is_null($project[\hacklib_id("thumbnail")])
          )) {
        update_post_meta(
          $id,
          "thumbnail",
          $project[\hacklib_id("thumbnail")]
        );
      }
      if (\hacklib_cast_as_boolean(
            is_null($project[\hacklib_id("thumbnail")])
          ) &&
          (!\hacklib_cast_as_boolean(
             is_null(self::get_meta_thumbnail($id))
           ))) {
        delete_post_meta($id, "thumbnail");
      }
      update_post_meta(
        $id,
        "configuration",
        $project[\hacklib_id("configuration")]
      );
    }
    public static function get_all_ids() {
      $projectIDs = get_posts(
        array(
          "post_type" => Configuration::get()[\hacklib_id(
            "project_post_type"
          )],
          "post_status" => "any",
          "numberposts" => -1,
          "fields" => "ids"
        )
      );
      \HH\invariant(
        is_array($projectIDs),
        "%s",
        new Error("Expected array getting project IDs")
      );
      return $projectIDs;
    }
    public static function get_content($id) {
      $post = get_post($id);
      \HH\invariant(
        !\hacklib_cast_as_boolean(is_null($post)),
        "%s",
        new Error("get_post did not return a post")
      );
      $content = $post->post_content;
      $content = apply_filters("the_content", $content);
      return str_replace("]]>", "]]&gt;", $content);
    }
    private static function get_gallery_for_dashboard($id) {
      $IDs = self::get_gallery_image_ids($id);
      $map = function($i) {
        return \codeneric\phmm\base\includes\Image::get_image($i, false);
      };
      $imgs = array_map($map, $IDs);
      $res = array();
      foreach ($imgs as $i) {
        if (!\hacklib_cast_as_boolean(is_null($i))) {
          $res[] = $i;
        }
      }
      return $res;
    }
    private static function get_gallery_for_frontend($id, $preloadCount = 10) {
      $IDs = self::get_gallery_image_ids($id);
      $order = $IDs;
      $preloaded = array();
      $query_args = array("project_id" => $id);
      foreach ($IDs as $index => $ID) {
        if ($index < $preloadCount) {
          $image = \codeneric\phmm\base\includes\Image::get_image(
            $ID,
            true,
            $query_args
          );
          if (!\hacklib_cast_as_boolean(is_null($image))) {
            $preloaded[] = $image;
          }
        }
      }
      return array("order" => $order, "preloaded" => $preloaded);
    }
    public static function get_thumbnail($id, $withMinithumb = true) {
      \HH\invariant(
        get_post_type($id) ===
        Configuration::get()[\hacklib_id("project_post_type")],
        "%s",
        new Error("Given ID must be of post project post type")
      );
      $raw =
        Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS($id, "thumbnail");
      $query_args = array("project_id" => $id);
      if (!\hacklib_cast_as_boolean(is_null($raw))) {
        $thumbnailID = (int) $raw;
        $image = Image::get_image($thumbnailID, $withMinithumb, $query_args);
        if ((!\hacklib_cast_as_boolean(is_null($image))) &&
            ($image[\hacklib_id("error")] !== true)) {
          return $image;
        }
      }
      $IDs = self::get_gallery_image_ids($id);
      if (count($IDs) > 0) {
        $firstImage = $IDs[0];
        $image =
          Image::get_image((int) $firstImage, $withMinithumb, $query_args);
        if ((!\hacklib_cast_as_boolean(is_null($image))) &&
            ($image[\hacklib_id("error")] !== true)) {
          return $image;
        }
      }
      $defaultIDString = get_option(
        Configuration::get()[\hacklib_id("default_thumbnail_id_option_key")],
        null
      );
      if (!\hacklib_cast_as_boolean(is_string($defaultIDString))) {
        return null;
      }
      $defaultID = (int) $defaultIDString;
      return Image::get_image($defaultID, $withMinithumb, $query_args);
    }
    public static function get_configuration($id) {
      \HH\invariant(
        get_post_type($id) ===
        Configuration::get()[\hacklib_id("project_post_type")],
        "%s",
        new Error("Given ID must be of post project post type")
      );
      $raw = Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS(
        $id,
        "configuration"
      );
      if (\hacklib_cast_as_boolean(is_array($raw))) {
        $configuration =
          array_merge(self::getDefaultProjectConfiguration(), $raw);
      } else {
        $configuration = self::getDefaultProjectConfiguration();
      }
      $data = \codeneric\phmm\validate\configuration($configuration);
      return $data;
    }
    public static function get_title($id) {
      $post = get_post($id);
      if (\hacklib_cast_as_boolean(is_null($post))) {
        return null;
      }
      return (string) $post->post_title;
    }
    public static function get_title_with_id_default($id) {
      $maybe = self::get_title($id);
      return \hacklib_cast_as_boolean(is_null($maybe)) ? ("#".$id) : $maybe;
    }
    private static function get_meta_thumbnail($id) {
      $raw =
        Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS($id, "thumbnail");
      if (!\hacklib_cast_as_boolean(is_null($raw))) {
        return (int) $raw;
      }
      return null;
    }
    public static function get_gallery_image_ids($id) {
      $gallery =
        Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS($id, "gallery");
      if (!\hacklib_cast_as_boolean(is_array($gallery))) {
        $gallery = array();
      }
      return array_map(
        function($ID) {
          if (\hacklib_cast_as_boolean(is_string($ID))) {
            return (int) $ID;
          } else {
            return $ID;
          }
        },
        $gallery
      );
    }
    public static function get_default_protection() {
      return array(
        "password_protection" => false,
        "private" => false,
        "password" => null
      );
    }
    public static function get_protection($id) {
      $raw =
        Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS($id, "protection");
      if (!\hacklib_cast_as_boolean(is_array($raw))) {
        return self::get_default_protection();
      }
      try {
        return \codeneric\phmm\validate\project_protection($raw);
      } catch (\Exception $e) {
        return self::get_default_protection();
      }
    }
    public static function get_project_gallery($id, $minithumbs = false) {
      $IDs = self::get_gallery_image_ids($id);
      $order = $IDs;
      $res = array();
      foreach ($IDs as $index => $ID) {
        $image =
          \codeneric\phmm\base\includes\Image::get_image($ID, $minithumbs);
        if (!\hacklib_cast_as_boolean(is_null($image))) {
          $res[] = $image;
        }
      }
      return $res;
    }
    public static function get_project_for_admin($id) {
      $gallery = self::get_gallery_image_ids($id);
      $thumbID = self::get_meta_thumbnail($id);
      $project = array(
        "gallery" =>
          \hacklib_cast_as_boolean(is_null($gallery)) ? array() : $gallery,
        "id" => $id,
        "thumbnail" =>
          \hacklib_cast_as_boolean(is_null($thumbID))
            ? null
            : Image::get_image($thumbID, true),
        "pwd" => null,
        "configuration" => self::get_configuration($id),
        "protection" => self::get_protection($id)
      );
      return $project;
    }
    public static function get_project_for_frontend($id, $clientID = null) {
      $gallery = self::get_gallery_for_frontend($id, 10);
      $clientConfig = null;
      if (!\hacklib_cast_as_boolean(is_null($clientID))) {
        $clientConfig = Client::get_project_configuration($clientID, $id);
      }
      $download_base_url = "";
      $wp_upload_dir = wp_upload_dir();
      if (\hacklib_cast_as_boolean(
            array_key_exists("baseurl", $wp_upload_dir)
          )) {
        $download_base_url =
          $wp_upload_dir[\hacklib_id("baseurl")]."/photography_management/";
      }
      $download_base_url =
        Utils::get_protocol_relative_url($download_base_url);
      $project = array(
        "labels" =>
          array(
            array(
              "id" => (string) InternalLabelID::Favorites,
              "images" =>
                Labels::get_set(
                  \hacklib_cast_as_boolean(is_null($clientID))
                    ? 0
                    : $clientID,
                  $id,
                  (string) InternalLabelID::Favorites
                )
            )
          ),
        "comment_counts" =>
          Utils::apply_filter_or(
            "codeneric/phmm/get_comment_counts",
            array("project_id" => $id, "client_id" => $clientID),
            array()
          ),
        "gallery" =>
          \hacklib_cast_as_boolean(is_null($gallery))
            ? array("order" => array(), "preloaded" => array())
            : $gallery,
        "id" =>
          $id,
        "configuration" =>
          \hacklib_cast_as_boolean(is_null($clientConfig))
            ? self::get_configuration($id)
            : $clientConfig,
        "download_base_url" =>
          $download_base_url,
        "thumbnail" =>
          null
      );
      return $project;
    }
    public static function getDefaultProjectConfiguration() {
      return \codeneric\phmm\validate\configuration(array());
    }
    public static function get_favorites($project_id, $client_id) {
      return Labels::get_set(
        $client_id,
        $project_id,
        (string) InternalLabelID::Favorites
      );
    }
    public static function get_number_of_zip_parts(
      $project_id,
      $mode,
      $client_id
    ) {
      $batches =
        self::partition_gallery_into_batches($project_id, $mode, $client_id);
      return count($batches);
    }
    public static function partition_gallery_into_batches(
      $project_id,
      $mode,
      $client_id
    ) {
      $gallery = self::get_gallery_image_ids($project_id);
      $files = array();
      if ($mode === "zip-favs") {
        \HH\invariant(
          !\hacklib_cast_as_boolean(is_null($client_id)),
          "%s",
          new Error("client_id has to be specified for favs mode!")
        );
        $gallery = self::get_favorites($project_id, $client_id);
      }
      foreach ($gallery as $attach_id) {
        $files[] = get_attached_file($attach_id);
      }
      return self::partition_files_into_batches($files);
    }
    private static function partition_files_into_batches($file_paths) {
      $settings = Settings::getCurrentSettings();
      $max_zip_part_size = $settings[\hacklib_id("max_zip_part_size")];
      $curr_part_size = 0;
      $max_part_size = $max_zip_part_size * 1000000;
      $patches = array(array());
      $patch_index = 0;
      foreach ($file_paths as $i => $path) {
        $file_size = filesize($path);
        if (($curr_part_size + $file_size) < $max_part_size) {
          $curr_part_size += $file_size;
        } else {
          $patch_index++;
          $patches[$patch_index] = array();
          $curr_part_size = $file_size;
        }
        $patches[$patch_index][] = $path;
      }
      return $patches;
    }
    public static function get_zip_batch(
      $project_id,
      $mode,
      $batch,
      $client_id
    ) {
      $batches =
        self::partition_gallery_into_batches($project_id, $mode, $client_id);
      \HH\invariant(
        count($batches) > $batch,
        "%s",
        new Error("batch is out of bound!")
      );
      return $batches[$batch];
    }
  }
}
