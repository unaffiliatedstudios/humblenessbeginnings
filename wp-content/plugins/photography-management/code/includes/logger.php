<?php
namespace codeneric\phmm {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \studio24\Rotate\Rotate;
  use \Analog as AnalogLogger;
  class Logger {
    static $jsonOptions = 0;
    public static function init() {
      if (("false" !== "true") ||
          ("production" === "production")) {
        $customHandler = function() {
          $upload_dir = wp_upload_dir();
          $upload_dir =
            $upload_dir[\hacklib_id("basedir")]."/photography_management";
          if (!\hacklib_cast_as_boolean(file_exists($upload_dir))) {
            mkdir($upload_dir, 0777, true);
          }
          $log_file = $upload_dir."/phmm.log";
          $maxLogSize = "2MB";
          $rotate = new Rotate($log_file);
          $rotate->keep(5);
          $rotate->size($maxLogSize);
          $rotate->run();
          return AnalogLogger\Handler\File::init($log_file);
        };
        AnalogLogger::$format = "%s - %s - %s - %s\n";
        AnalogLogger::handler(
          AnalogLogger\Handler\LevelName::init($customHandler())
        );
      } else {
        self::$jsonOptions = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
        AnalogLogger::handler(AnalogLogger\Handler\ChromeLogger::init());
      }
    }
    private static function parseAdditionalInfo($additional = null) {
      if (\hacklib_cast_as_boolean(is_null($additional))) {
        return "";
      }
      $additionalInfoString = json_encode($additional, self::$jsonOptions);
      if ($additionalInfoString === false) {
        return "";
      }
      return " \n ".$additionalInfoString;
    }
    private static function formatString($msg, $additional = null) {
      return $msg.self::parseAdditionalInfo($additional);
    }
    public static function info($message, $additional = null) {
      try {
        AnalogLogger::info(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
    public static function error($message, $additional = null) {
      try {
        AnalogLogger::error(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
    public static function urgent($message, $additional = null) {
      try {
        AnalogLogger::urgent(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
    public static function alert($message, $additional = null) {
      try {
        AnalogLogger::alert(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
    public static function notice($message, $additional = null) {
      try {
        AnalogLogger::notice(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
    public static function debug($message, $additional = null) {
      try {
        AnalogLogger::debug(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
    public static function warning($message, $additional = null) {
      try {
        AnalogLogger::warning(self::formatString($message, $additional));
      } catch (\Exception $e) {
      }
    }
  }
  Logger::init();
}
