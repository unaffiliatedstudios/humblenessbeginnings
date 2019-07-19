<?php
namespace codeneric\phmm {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \NinjaMutex\Lock\FlockLock;
  use \NinjaMutex\Mutex;
  use \codeneric\phmm\enums\SemaphoreExecutorReturn;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\Logger;
  class Semaphore {
    static $migration_flag_key = "cc_phmm_migration_flag";
    static $failed_migration_flag_key = "cc_phmm_failed_migration_flag";
    static $safety_padding_factor = 0.33;
    private static function get_execution_time($start) {
      return \microtime(true) - $start;
    }
    private static function get_lock_dir() {
      $upload_dir = \wp_upload_dir();
      return $upload_dir[\hacklib_id("basedir")];
    }
    public static function progress($mutex_name) {
      $semaphore_state = self::get_state(
        $mutex_name,
        function() {
          return array();
        }
      );
      $sum =
        \count($semaphore_state[\hacklib_id("outstanding")]) +
        \count($semaphore_state[\hacklib_id("finished")]) +
        \count($semaphore_state[\hacklib_id("failed")]);
      return
        ($sum > 0)
          ? (1 -
             (\count($semaphore_state[\hacklib_id("outstanding")]) / $sum))
          : 1;
    }
    private static function get_max_execution_time() {
      $max = null;
      if (\hacklib_cast_as_boolean(\function_exists("ini_get"))) {
        $max = \ini_get("max_execution_time");
      }
      $actual = 30;
      if (\hacklib_cast_as_boolean(is_numeric($max))) {
        $num = (int) $max;
        $actual = ($num === 0) ? \INF : $num;
      }
      Logger::debug("get_max_execution_time", $actual);
      return $actual;
    }
    private static function time_exceeded($start) {
      $res =
        self::get_execution_time($start) >=
        (self::get_max_execution_time() * self::$safety_padding_factor);
      Logger::debug("time_exceeded:", $res);
      return $res;
    }
    private static function memory_exceeded() {
      $memory_limit = self::get_memory_limit() * 0.95;
      $current_memory = \memory_get_usage(false);
      $res = $current_memory >= $memory_limit;
      Logger::debug("current_memory:", $current_memory);
      Logger::debug("memory_limit:", $memory_limit);
      Logger::debug("memory_exceeded:", $res);
      return $res;
    }
    private static function get_memory_limit() {
      if (\hacklib_cast_as_boolean(\function_exists("ini_get"))) {
        $memory_limit = \ini_get("memory_limit");
      } else {
        $memory_limit = "128M";
      }
      if ((!\hacklib_cast_as_boolean($memory_limit)) ||
          ((-1) === \intval($memory_limit))) {
        $memory_limit = "32000M";
      }
      return \intval($memory_limit) * 1024 * 1024;
    }
    public static function is_running($mutex_name) {
      $lock = new FlockLock(self::get_lock_dir());
      $mutex = new Mutex($mutex_name, $lock);
      return $mutex->isLocked();
    }
    public static function get_state($state_name, $get_all_items) {
      $state = \get_transient($state_name);
      if ($state !== false) {
        return /* UNSAFE_EXPR */ $state;
      } else {
        if (!\hacklib_cast_as_boolean(\is_null($get_all_items))) {
          return array(
            "failed" => array(),
            "finished" => array(),
            "outstanding" => $get_all_items()
          );
        } else {
          \HH\invariant(
            false,
            "%s",
            new Error(
              "There is no state with statename '".
              $state_name.
              "' stored and no function 'get_all_items' specified!"
            )
          );
        }
      }
    }
    public static function set_state($state_name, $state) {
      \set_transient($state_name, $state, 60 * 60 * 24);
    }
    public static function delete_state($state_name) {
      \delete_transient($state_name);
    }
    public static function run($mutex_name, $state, $fn) {
      $lock = new FlockLock(self::get_lock_dir());
      $mutex = new Mutex($mutex_name, $lock);
      $max_wait =
        self::get_max_execution_time() *
        (1 - self::$safety_padding_factor) *
        1000;
      if (\hacklib_cast_as_boolean($mutex->acquireLock(0))) {
        try {
          $arr = $state[\hacklib_id("outstanding")];
          $server = Superglobals::Server();
          $start = (float) $server[\hacklib_id("REQUEST_TIME_FLOAT")];
          Logger::debug("REQUEST_TIME_FLOAT:", $start);
          $res = array(
            "failed" => array(),
            "finished" => array(),
            "outstanding" => array()
          );
          while ((\count($arr) > 0) &&
                 (!\hacklib_cast_as_boolean(self::time_exceeded($start))) &&
                 (!\hacklib_cast_as_boolean(self::memory_exceeded()))) {
            $item = \array_shift($arr);
            $r = $fn($item);
            switch ($r) {
              case SemaphoreExecutorReturn::Finished:
                $res[\hacklib_id("finished")][] = $item;
                break;
              case SemaphoreExecutorReturn::Failed:
                $res[\hacklib_id("failed")][] = $item;
                break;
              case SemaphoreExecutorReturn::Outstanding:
                $res[\hacklib_id("outstanding")][] = $item;
                break;
            }
          }
          $mutex->releaseLock();
          Logger::debug("temp res:", $res);
          Logger::debug("old state:", $state);
          $state[\hacklib_id("finished")] = \array_merge(
            $state[\hacklib_id("finished")],
            $res[\hacklib_id("finished")]
          );
          $state[\hacklib_id("failed")] = \array_merge(
            $state[\hacklib_id("failed")],
            $res[\hacklib_id("failed")]
          );
          $state[\hacklib_id("outstanding")] =
            \array_merge($res[\hacklib_id("outstanding")], $arr);
          Logger::debug("new state:", $state);
          return $state;
        } catch (\Exception $e) {
          $mutex->releaseLock();
          throw $e;
        }
      } else {
        return null;
      }
    }
  }
}
