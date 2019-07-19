<?php
namespace codeneric\phmm\base {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class Watermarker {
    public static function watermark_image($args) {
      $filename = $args[\hacklib_id("file")];
      $options = $args[\hacklib_id("wms")];
      list($w_img, $h_img, $image_type) = \getimagesize($filename);
      $default_watermark_path =
        \dirname(__FILE__)."/../assets/img/placeholder.png";
      $watermark_image_id =
        \hacklib_cast_as_boolean(\is_null($options[\hacklib_id("image_id")]))
          ? (-1)
          : $options[\hacklib_id("image_id")];
      $watermark_position =
        \hacklib_cast_as_boolean(\is_null($options[\hacklib_id("position")]))
          ? "center"
          : $options[\hacklib_id("position")];
      $watermark_scale =
        \hacklib_cast_as_boolean(\is_null($options[\hacklib_id("scale")]))
          ? 20
          : $options[\hacklib_id("scale")];
      \HH\invariant(
        !\hacklib_cast_as_boolean(\is_null($watermark_scale)),
        "%s",
        "watermark_scale is null!"
      );
      $position_map = array(
        "center" => array("h" => 0.5, "v" => 0.5),
        "left_bottom" => array("h" => 0.1, "v" => 0.9),
        "right_bottom" => array("h" => 0.9, "v" => 0.9),
        "left_top" => array("h" => 0.1, "v" => 0.1),
        "right_top" => array("h" => 0.9, "v" => 0.1)
      );
      $watermark_path = \get_attached_file($watermark_image_id, true);
      if ($watermark_path === "") {
        $watermark_path = $default_watermark_path;
      }
      $input_image_is_watermark_itself = $watermark_path === $filename;
      list($w_watermark, $h_watermark) = \getimagesize($watermark_path);
      $pos_h = $position_map[$watermark_position][\hacklib_id("h")];
      $pos_v = $position_map[$watermark_position][\hacklib_id("v")];
      $scale = $watermark_scale / 100;
      $ratio = \min($w_img / $w_watermark, $h_img / $h_watermark) * $scale;
      $w_watermark_new = \ceil($w_watermark * $ratio);
      $h_watermark_new = \ceil($h_watermark * $ratio);
      if (\hacklib_cast_as_boolean(\function_exists("ini_set"))) {
        \ini_set("memory_limit", "-1");
      }
      switch ($image_type) {
        case 1:
          $dest = \imagecreatefromgif($filename);
          break;
        case 2:
          $dest = \imagecreatefromjpeg($filename);
          break;
        case 3:
          $dest = \imagecreatefrompng($filename);
          break;
        default:
          return;
      }
      $watermark_src = \imagecreatefrompng($watermark_path);
      $abs_pos_h = \ceil(($pos_h * $w_img) - ($w_watermark_new / 2));
      $abs_pos_v = \ceil(($pos_v * $h_img) - ($h_watermark_new / 2));
      $abs_pos_h =
        (($abs_pos_h + $w_watermark_new) <= $w_img)
          ? $abs_pos_h
          : ($abs_pos_h - (($abs_pos_h + $w_watermark_new) - $w_img));
      $abs_pos_v =
        (($abs_pos_v + $h_watermark_new) <= $h_img)
          ? $abs_pos_v
          : ($abs_pos_v - (($abs_pos_v + $h_watermark_new) - $h_img));
      $abs_pos_h = ($abs_pos_h >= 0) ? $abs_pos_h : 0;
      $abs_pos_v = ($abs_pos_v >= 0) ? $abs_pos_v : 0;
      $watermark = \imagecreatetruecolor($w_watermark_new, $h_watermark_new);
      \imagealphablending($watermark, false);
      \imagesavealpha($watermark, true);
      \imagecopyresampled(
        $watermark,
        $watermark_src,
        0,
        0,
        0,
        0,
        $w_watermark_new,
        $h_watermark_new,
        $w_watermark,
        $h_watermark
      );
      if (!\hacklib_cast_as_boolean($input_image_is_watermark_itself)) {
        \imagecopy(
          $dest,
          $watermark,
          $abs_pos_h,
          $abs_pos_v,
          0,
          0,
          $w_watermark_new,
          $h_watermark_new
        );
      }
      switch ($image_type) {
        case 1:
          \imagegif($dest);
          break;
        case 2:
          \imagejpeg($dest);
          break;
        case 3:
          \imagepng($dest);
          break;
        default:
          echo ("");
          break;
      }
      \imagedestroy($dest);
      \imagedestroy($watermark_src);
      \imagedestroy($watermark);
    }
  }
}
