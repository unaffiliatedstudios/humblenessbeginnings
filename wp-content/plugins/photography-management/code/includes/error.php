<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class ErrorSeverity {
    private function __construct() {}
    private static
      $hacklib_values = array(
        "WARNING" => "WARNING",
        "CRITICAL" => "CRITICAL",
        "CONSTRUCTOR" => "CONSTRUCTOR"
      );
    use \HH\HACKLIB_ENUM_LIKE;
    const WARNING = "WARNING";
    const CRITICAL = "CRITICAL";
    const CONSTRUCTOR = "CONSTRUCTOR";
  }
  final class RecoverEnum {
    private function __construct() {}
    private static
      $hacklib_values = array("recoverOption" => "recoverOption");
    use \HH\HACKLIB_ENUM_LIKE;
    const recoverOption = "recoverOption";
  }
  class RecoverFunctions {
    public static function recoverOption($args) {
      return false;
    }
  }
  class Error {
    const MAX_ERROR_COUNT = 100;
    public $message;
    public $severity;
    public $recoverFn;
    public $recoverFnParams;
    public $failedVariables;
    public function __construct(
      $message,
      $failedVariables = array(),
      $severity = ErrorSeverity::CRITICAL,
      $recoverFn = null,
      $recoverFnParams = null
    ) {
      $this->message = $message;
      $this->severity = $severity;
      $this->recoverFn = $recoverFn;
      $this->failedVariables = $failedVariables;
      $this->recoverFnParams =
        \hacklib_cast_as_boolean(is_null($recoverFnParams))
          ? array()
          : $recoverFnParams;
    }
    public function recover() {
      $fnRef = $this->recoverFn;
      if (\hacklib_cast_as_boolean(is_null($fnRef)) ||
          (!\hacklib_cast_as_boolean(RecoverEnum::isValid($fnRef)))) {
        return false;
      }
      return call_user_func(
        array(RecoverFunctions::class, $fnRef),
        $this->recoverFnParams
      );
    }
    public function __toString() {
      return serialize($this);
    }
    public static function unseralize($serialized) {
      $us = unserialize($serialized);
      if ($us instanceof Error) {
        return $us;
      }
      return null;
    }
    public static function handle_error_case($procentS, $encodedError = null) {
      if (!\hacklib_cast_as_boolean(is_object($encodedError))) {
        return self::kernel_panic("Encoded error not expected shape");
      }
      $error = self::unseralize($encodedError);
      if (\hacklib_cast_as_boolean(is_null($error))) {
        return self::kernel_panic("Encoded error null");
      }
      $error->recover();
      $id = self::getTransientErrorID();
      if (\hacklib_cast_as_boolean(is_null($id))) {
        return self::kernel_panic("Transient ID null. ".$error->message);
      }
      $transient = get_transient($id);
      if ("production" === "development") {
        $vars = json_encode($error->failedVariables);
        if (\hacklib_cast_as_boolean(class_exists("WPDieException"))) {
          throw new \WPDieException(
            $error->message." ***** Failed variables: ".$vars
          );
        } else {
          throw new \PhmmFatalInvariantException(
            $error->message." ***** Failed variables: ".$vars
          );
        }
        if (\hacklib_cast_as_boolean(class_exists("\\WPDieException"))) {
          throw new \WPDieException(
            $error->message." ***** Failed variables: ".$vars
          );
        } else {
          throw new \PhmmFatalInvariantException(
            $error->message." ***** Failed variables: ".$vars
          );
        }
      }
      $count = ($transient === false) ? 0 : ((int) $transient);
      switch ($error->severity) {
        case ErrorSeverity::CRITICAL:
          if ($count >= self::MAX_ERROR_COUNT) {
            self::deleteTransient($id);
            self::kernel_panic($error->message);
          } else {
            self::updateTransientCount($id, $count + 1);
            self::print_error($error->message, $error->failedVariables);
          }
          break;
        case ErrorSeverity::CONSTRUCTOR:
          throw new \PhmmFatalInvariantException($error->message);
          break;
        case ErrorSeverity::WARNING:
          self::updateTransientCount($id, $count + 1);
          self::print_error($error->message, $error->failedVariables);
          break;
      }
    }
    public static function deleteTransient($id) {
      return delete_transient($id);
    }
    public static function updateTransientCount($id, $count) {
      return set_transient($id, $count, 60 * 60);
    }
    public static function getTransientErrorID() {
      $bt = debug_backtrace();
      if (!\hacklib_cast_as_boolean(is_array($bt))) {
        return null;
      }
      $needle = "HH\\invariant";
      foreach ($bt as $entry) {
        if (\hacklib_cast_as_boolean(array_key_exists("function", $entry)) &&
            ($entry[\hacklib_id("function")] === $needle) &&
            \hacklib_cast_as_boolean(array_key_exists("file", $entry)) &&
            \hacklib_cast_as_boolean(array_key_exists("line", $entry))) {
          return
            "codeneric/phmm/error/".
            md5($entry[\hacklib_id("file")].$entry[\hacklib_id("line")]);
        }
      }
      return null;
    }
    private static function deactivate_plugin() {
      $name = "photography-management/photography_management.php";
      deactivate_plugins(
        array("photography-management/photography_management.php"),
        true
      );
    }
    private static function print_error(
      $error = null,
      $failedVariables = null
    ) {
      if (!\hacklib_cast_as_boolean(current_user_can("administrator"))) {
        return;
      }
      $title = "<h1>Photography Management</h1>";
      try {
        if (\hacklib_cast_as_boolean(
              class_exists("\\codeneric\\phmm\\Logger", false)
            )) {
          \codeneric\phmm\Logger::urgent(
            \hacklib_cast_as_boolean(is_null($error))
              ? "Fatal Error with no name"
              : $error,
            $failedVariables
          );
        }
      } catch (\Exception $e) {
      }
      try {
        $backtrace = debug_backtrace();
      } catch (\Exception $e) {
        $backtrace = null;
      }
      if (\hacklib_cast_as_boolean(is_null($backtrace))) {
        $backtrace = array();
      }
      try {
        ob_start();
        phpinfo(-1);
        $phpinfo = ob_get_contents();
        ob_get_clean();
      } catch (\Exception $e) {
        $phpinfo = "";
      }
      $data = array("backtrace" => $backtrace, "phpinfo" => $phpinfo);
      try {
        if (\hacklib_cast_as_boolean(
              class_exists("\\codeneric\\phmm\\Logger", false)
            )) {
          \codeneric\phmm\Logger::urgent(
            \hacklib_cast_as_boolean(is_null($error))
              ? "Fatal Error with no name"
              : $error,
            array(
              "backtrace" => $backtrace,
              "phpinfo" => $phpinfo,
              "failedVariables" => $failedVariables
            )
          );
        }
      } catch (\Exception $e) {
      }
      $backtraceJSON =
        htmlspecialchars(json_encode($backtrace), ENT_QUOTES, "UTF-8");
      $phpinfoEscaped = htmlspecialchars($phpinfo, ENT_QUOTES, "UTF-8");
      $body =
        "<div id='cc_phmm_fatal_error' data-phpinfo='".
        $phpinfoEscaped.
        "'  data-backtrace='".
        $backtraceJSON.
        "' >\n            <div style=\"background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:block;\"></div>\n      </div>";
      if ("production" === "development") {
        $path = "http://192.168.0.43:4242/base/phmm.fatal.error.js";
      } else {
        $filename = "phmm.fatal.error.js";
        $path = plugins_url("../assets/js/".$filename, __FILE__);
      }
      $rand = function() {
        if (\hacklib_cast_as_boolean(
              function_exists("openssl_random_pseudo_bytes")
            )) {
          return bin2hex(openssl_random_pseudo_bytes(20));
        } else {
          return strval(mt_rand());
        }
      };
      $nonce = $rand();
      if (\hacklib_cast_as_boolean(function_exists("current_user_can"))) {
        if (\hacklib_cast_as_boolean(current_user_can("administrator"))) {
          set_transient("codeneric_phmm_deactivate", $nonce);
        } else {
          $nonce = "";
        }
      } else {
        set_transient("codeneric_phmm_deactivate", $nonce);
      }
      $failedVariablesStringified = json_encode($failedVariables);
      if ($failedVariablesStringified === false) {
        $failedVariablesStringified = "";
      }
      $errorName = self::get_error_name($error, $backtrace);
      $pluginsDir = plugins_url("assets/js/", dirname(__FILE__));
      $baseUrl = get_site_url();
      $localize =
        "<script type='text/javascript' >\n        codeneric_phmm_plugins_dir = '".
        $pluginsDir.
        "';\n        codeneric_phmm_nonce = '".
        $nonce.
        "';\n        codeneric_error_name = '".
        $errorName.
        "';\n        codeneric_failed_variables = ".
        $failedVariablesStringified.
        ";\n        codeneric_base_url = '".
        $baseUrl.
        "';\n    </script>";
      $script = "<script type='text/javascript' src='".$path."'></script>";
      wp_die($body.$localize.$script);
    }
    private static function kernel_panic($error = null) {
      self::deactivate_plugin();
      $title = "<h1>Photography Management Kernel Panic</h1>";
      wp_die(
        $title.((!\hacklib_cast_as_boolean(is_null($error))) ? $error : "")
      );
    }
    public static function get_error_name($error, $backtrace) {
      $unknownErrorName = "Unknown error";
      if (!\hacklib_cast_as_boolean(is_array($backtrace))) {
        return
          \hacklib_cast_as_boolean(is_null($error))
            ? $unknownErrorName
            : $error;
      }
      $e = \hacklib_cast_as_boolean(is_null($error)) ? "" : ($error." ");
      foreach ($backtrace as $trace) {
        if (\hacklib_cast_as_boolean(is_array($trace)) &&
            \hacklib_cast_as_boolean(array_key_exists("function", $trace)) &&
            \hacklib_cast_as_boolean(array_key_exists("file", $trace)) &&
            \hacklib_cast_as_boolean(array_key_exists("line", $trace)) &&
            ($trace[\hacklib_id("function")] === "HH\\invariant")) {
          $path = $trace[\hacklib_id("file")].":".$trace[\hacklib_id("line")];
          $result = array();
          preg_match("/.*(photography\\-management.*)/", $path, $result);
          if (count($result) >= 2) {
            return $e.$result[1];
          } else {
            return $e.$path;
          }
        }
      }
      return
        \hacklib_cast_as_boolean(is_null($error))
          ? $unknownErrorName
          : $error;
    }
  }
}
