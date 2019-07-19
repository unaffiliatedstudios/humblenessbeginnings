<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class FileStream {
    public static function export_label_csv($data) {
      if (\count($data) === 0) {
        exit();
      }
      $fh = \fopen("php://output", "w");
      \ob_start();
      $header = array(
        "Label Name",
        "Client ID",
        "Client Name",
        "Original filename",
        "Wordpress File ID"
      );
      \fputcsv($fh, $header);
      foreach ($data as $k => $entry) {
        $line = array(
          $entry[\hacklib_id("label_name")],
          $entry[\hacklib_id("client_id")],
          $entry[\hacklib_id("client_name")],
          $entry[\hacklib_id("original_filename")],
          $entry[\hacklib_id("wordpress_file_id")]
        );
        \fputcsv($fh, $line);
      }
      $string = \ob_get_clean();
      $filename =
        $data[0][\hacklib_id("client_name")].
        "_".
        $data[0][\hacklib_id("label_name")].
        "_".
        \date("Y_m_d").
        "-".
        \date("H_i");
      \header("Pragma: public");
      \header("Expires: 0");
      \header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      \header("Cache-Control: private", false);
      \header("Content-Type: application/octet-stream");
      \header(
        "Content-Disposition: attachment; filename=\"".$filename.".csv\";"
      );
      \header("Content-Transfer-Encoding: binary");
      exit($string);
    }
  }
}
