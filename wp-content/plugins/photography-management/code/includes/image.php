<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Logger;
  class Image {
    public static function get_image(
      $id,
      $use_minithumb = false,
      $query_args = array()
    ) {
      if (get_post_type($id) !== "attachment") {
        return null;
      }
      $query_args[\hacklib_id("attach_id")] = (string) $id;
      $imagedata = wp_get_attachment_metadata($id);
      if (!\hacklib_cast_as_boolean(is_array($imagedata))) {
        return null;
      }
      $image = get_post($id);
      $meta = array(
        "caption" =>
          \hacklib_cast_as_boolean(is_null($image))
            ? null
            : $image->post_excerpt
      );
      $uncropped_sizes = Utils::get_uncropped_image_sizes();
      $uncropped_sizes_names = array_keys($uncropped_sizes);
      $sizes =
        \hacklib_cast_as_boolean(array_key_exists("sizes", $imagedata))
          ? $imagedata[\hacklib_id("sizes")]
          : array();
      $mapped_sizes = array();
      $available_uncropped_sizes =
        array_intersect($uncropped_sizes_names, array_keys($sizes));
      $no_uncropped_sizes_availalbe = count($available_uncropped_sizes) === 0;
      foreach ($sizes as $size_name => $size) {
        $s = wp_get_attachment_image_src($id, $size_name);
        if (\hacklib_cast_as_boolean(is_array($s)) &&
            (\hacklib_cast_as_boolean($no_uncropped_sizes_availalbe) ||
             \hacklib_cast_as_boolean(
               in_array($size_name, $uncropped_sizes_names)
             ))) {
          $url = (string) add_query_arg($query_args, $s[0]);
          $url = Utils::get_protocol_relative_url($url);
          $mapped_sizes[] = array(
            "url" => $url,
            "width" => (int) $size[\hacklib_id("width")],
            "height" => (int) $size[\hacklib_id("height")],
            "name" => (string) $size_name
          );
        }
      }
      $t2 = Utils::time();
      $filename = basename(get_attached_file($id, true));
      $mini_thumb_b64 =
        \hacklib_cast_as_boolean($use_minithumb)
          ? self::get_minithumb($id)
          : null;
      $image = array(
        "sizes" => $mapped_sizes,
        "filename" => $filename,
        "id" => (int) $id,
        "meta" => $meta,
        "mini_thumb" => (string) $mini_thumb_b64,
        "error" => false
      );
      return $image;
    }
    static function get_minithumb($id) {
      $imagedata = wp_get_attachment_metadata($id);
      if (!\hacklib_cast_as_boolean(is_array($imagedata))) {
        return null;
      }
      $filename = false;
      $medium_exists = \codeneric\phmm\Utils::array_nested_key_exist(
        "sizes.medium.file",
        $imagedata
      );
      $thumb_exists = \codeneric\phmm\Utils::array_nested_key_exist(
        "sizes.thumbnail.file",
        $imagedata
      );
      if (\hacklib_cast_as_boolean($medium_exists) ||
          \hacklib_cast_as_boolean($thumb_exists)) {
        $size_name =
          \hacklib_cast_as_boolean($medium_exists) ? "medium" : "thumbnail";
        $filename =
          $imagedata[\hacklib_id("sizes")][$size_name][\hacklib_id("file")];
        $o_path = get_attached_file($id, true);
        $filename = dirname($o_path)."/".$filename;
        if (!\hacklib_cast_as_boolean(file_exists($filename))) {
          return null;
        }
      }
      if ($filename === false) {
        return null;
      }
      list($width, $height, $image_type) = getimagesize($filename);
      $b64_path = dirname($filename)."/".$id.".b64";
      if (!\hacklib_cast_as_boolean(file_exists($b64_path))) {
        $newwidth = min(20, intval(20 * ($width / $height)));
        $newheight = min(20, intval(20 * ($height / $width)));
        $minithumb = imagecreatetruecolor($newwidth, $newheight);
        $image_type_str = null;
        $source = null;
        switch ($image_type) {
          case IMAGETYPE_GIF:
            $source = imagecreatefromgif($filename);
            break;
          case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($filename);
            $image_type_str = "jpeg";
            break;
          case IMAGETYPE_PNG:
            $source = imagecreatefrompng($filename);
            $image_type_str = "png";
            break;
          default:
            break;
        }
        if (($source === null) || ($image_type_str === null)) {
          return null;
        }
        imagecopyresized(
          $minithumb,
          $source,
          0,
          0,
          0,
          0,
          $newwidth,
          $newheight,
          $width,
          $height
        );
        ob_start();
        imagejpeg($minithumb);
        $img = ob_get_clean();
        $b64 = "data:image/".$image_type_str.";base64,".base64_encode($img);
        imagedestroy($minithumb);
        imagedestroy($source);
        $fp = fopen($b64_path, "w");
        fwrite($fp, $b64);
        fclose($fp);
        return $b64;
      } else {
        $fp = fopen($b64_path, "r");
        $res = fread($fp, filesize($b64_path));
        fclose($fp);
        return $res;
      }
    }
    static function delete($image_id) {
      wp_delete_attachment($image_id, true);
    }
    static function get_original_image_url($id, $query_args = array()) {
      $query_args[\hacklib_id("attach_id")] = $id;
      $img_arr = \wp_get_attachment_image_src($id, "full");
      if ($img_arr === false) {
        return null;
      }
      \HH\invariant(
        \hacklib_cast_as_boolean(\is_array($img_arr)) &&
        (\count($img_arr) >= 4) &&
        \hacklib_cast_as_boolean(\is_string($img_arr[0])),
        "%s",
        new Error("Bad format of wp_get_attachment_image_src return.")
      );
      $url = (string) \add_query_arg($query_args, $img_arr[0]);
      $url = Utils::get_protocol_relative_url($url);
      return array("url" => $url);
    }
  }
}
