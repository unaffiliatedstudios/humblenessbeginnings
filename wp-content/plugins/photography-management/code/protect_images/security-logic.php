<?php
namespace codeneric\phmm\base\protect_images {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\base\includes\Project;
  use \codeneric\phmm\base\includes\Client;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\base\includes\Permission;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\enums\UserState;
  use \codeneric\phmm\enums\ProjectState;
  require_once (dirname(__FILE__)."/ZipStream/ZipStream.plain.php");
  class Main {
    public static function provide_file(
      $f,
      $filename,
      $project_id,
      $part = 0
    ) {
      while (\ob_get_level() > 0) {
        \ob_end_clean();
      }
      if (($f === "zip-all") || ($f === "zip-favs")) {
        $current_user = \wp_get_current_user();
        $user_id = ($current_user !== false) ? $current_user->ID : 0;
        $dir = \dirname(__FILE__);
        \HH\invariant(
          is_int($project_id),
          "%s",
          new Error("Cannot download zip without a project_id!")
        );
        $total_parts = 0;
        $files = array();
        if ($f === "zip-favs") {
          $client = Client::get_current();
          $client_id =
            (!\hacklib_cast_as_boolean(\is_null($client)))
              ? $client[\hacklib_id("ID")]
              : 0;
          $files = Project::get_zip_batch($project_id, $f, $part, $client_id);
          $total_parts =
            Project::get_number_of_zip_parts($project_id, $f, $client_id);
        } else {
          if ($f === "zip-all") {
            $total_parts =
              Project::get_number_of_zip_parts($project_id, $f, null);
            $files = Project::get_zip_batch($project_id, $f, $part, null);
          }
        }
        $display_part = $part + 1;
        $title = \get_the_title($project_id);
        $zip = new \Photography_Management_Base_ZipStream(
          $title." (".$display_part." of ".$total_parts.")".".zip",
          array("large_file_size" => 1)
        );
        \http_response_code(200);
        foreach ($files as $file) {
          $zip->addFileFromPath(\basename($file), $file);
        }
        $zip->finish();
        die();
      }
      $apply_watermark = false;
      $access_config = null;
      $client = Client::get_current();
      if ((!\hacklib_cast_as_boolean(\is_null($client))) &&
          \hacklib_cast_as_boolean(is_int($project_id))) {
        $client_id = $client[\hacklib_id("ID")];
        $access_config =
          Client::get_project_configuration($client_id, $project_id);
      }
      if (\hacklib_cast_as_boolean(\is_null($access_config)) &&
          \hacklib_cast_as_boolean(is_int($project_id))) {
        $access_config = Project::get_configuration($project_id);
      }
      $apply_watermark =
        (!\hacklib_cast_as_boolean(\is_null($access_config)))
          ? $access_config[\hacklib_id("watermark")]
          : false;
      if (\hacklib_cast_as_boolean($apply_watermark) &&
          (\getimagesize($filename) !== false) &&
          \hacklib_cast_as_boolean(\has_action("codeneric/phmm/watermark"))) {
        $settings = \codeneric\phmm\base\admin\Settings::getCurrentSettings();
        $wms = $settings[\hacklib_id("watermark")];
        if ((!\hacklib_cast_as_boolean(
               \is_null($wms[\hacklib_id("image_id")])
             )) &&
            (!\hacklib_cast_as_boolean(
               \is_null($wms[\hacklib_id("position")])
             )) &&
            (!\hacklib_cast_as_boolean(
               \is_null($wms[\hacklib_id("scale")])
             ))) {
          $args = array("file" => $filename, "wms" => $wms);
          \do_action("codeneric/phmm/watermark", $args);
          die();
        }
      }
      $file = \fopen($filename, "rb");
      $buffer = 1024 * 8;
      $mime = "image/xyz";
      if (\hacklib_cast_as_boolean(\function_exists("mime_content_type"))) {
        $mime = \mime_content_type($filename);
      }
      \header("Content-Description: File Transfer");
      \header("Content-Type: ".$mime);
      \header(
        "Content-Disposition: inline; filename=\"".\basename($filename)."\""
      );
      \http_response_code(200);
      while (!\hacklib_cast_as_boolean(\feof($file))) {
        echo (\fread($file, $buffer));
        \flush();
      }
      \fclose($file);
      die();
    }
    public static function file_belongs_to_attachment($url, $attach_id) {
      $attach_url = \wp_get_attachment_url($attach_id);
      if ($attach_url === $url) {
        return true;
      }
      $sizes = Utils::get_intermediate_image_sizes();
      foreach ($sizes as $size) {
        $data = \wp_get_attachment_image_src($attach_id, $size);
        if (\hacklib_cast_as_boolean(is_array($data)) &&
            (Utils::get_protocol_relative_url($data[0]) ===
             Utils::get_protocol_relative_url($url))) {
          return true;
        }
      }
      return false;
    }
    private static function user_can_access_project($project_id) {
      $project_state = Permission::get_project_state($project_id);
      $client_state =
        Permission::get_client_state_wrt_project($project_state, $project_id);
      if ($project_state === ProjectState::Public_) {
        return true;
      }
      return
        \in_array($client_state, array(UserState::Client, UserState::Guest));
    }
    public static function user_can_access_file($f, $attach_id, $project_id) {
      if (\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
        return true;
      }
      \HH\invariant(
        is_int($attach_id),
        "%s",
        new Error(
          "Current user is not an admin and the attach_id is not defnied, but only admins can load files without setting the project parameter."
        )
      );
      \HH\invariant(
        is_int($project_id),
        "%s",
        new Error(
          "Current user is not an admin and the project_id is not defnied, but only admins can load files without setting the project parameter."
        )
      );
      $can_access = self::user_can_access_project($project_id);
      if (!\hacklib_cast_as_boolean($can_access)) {
        return false;
      }
      $config = null;
      $current_client = Client::get_current();
      if (!\hacklib_cast_as_boolean(\is_null($current_client))) {
        $config = Client::get_project_configuration(
          $current_client[\hacklib_id("ID")],
          $project_id
        );
      }
      if (\hacklib_cast_as_boolean(\is_null($config))) {
        $config = Project::get_configuration($project_id);
      }
      if (($f === "zip-all") || ($f === "zip-favs")) {
        $can_download_all =
          (!($f === "zip-all")) ||
          \hacklib_cast_as_boolean($config[\hacklib_id("downloadable")]);
        $can_download_favs =
          (!($f === "zip-favs")) ||
          \hacklib_cast_as_boolean($config[\hacklib_id("downloadable_favs")]);
        return
          \hacklib_cast_as_boolean($can_download_all) &&
          \hacklib_cast_as_boolean($can_download_favs);
      } else {
        $upload_url = \wp_upload_dir();
        $upload_url = $upload_url[\hacklib_id("baseurl")];
        $attach_url = $upload_url."/photography_management/".$f;
        $fbta = self::file_belongs_to_attachment($attach_url, $attach_id);
        if (!\hacklib_cast_as_boolean($fbta)) {
          return false;
        }
        $g = Project::get_gallery_image_ids($project_id);
        foreach ($g as $img) {
          if ($img === $attach_id) {
            return true;
          }
        }
        $thumbnail_id = Project::get_meta_thumbnail($project_id);
        if ($thumbnail_id === $attach_id) {
          return true;
        }
        return false;
      }
    }
    private function is_exposed_cover_image() {
      return true;
    }
    public static function user_is_permitted($f, $attach_id, $project_id) {
      $user_can_access_file =
        self::user_can_access_file($f, $attach_id, $project_id);
      $is_exposed_cover_image = false;
      return
        \hacklib_cast_as_boolean($user_can_access_file) ||
        \hacklib_cast_as_boolean($is_exposed_cover_image);
    }
  }
}
