<?php
namespace codeneric\phmm {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\admin\FrontendHandler;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\Semaphore;
  use \codeneric\phmm\enums\SemaphoreExecutorReturn;
  use \NinjaMutex\Lock\FlockLock;
  use \NinjaMutex\Mutex;
  use \codeneric\phmm\Logger;
  require_once (dirname(__FILE__)."/update_functions.php");
  class DBUpdater {
    static $currVersion = "";
    static $mutex_name = "codeneric_phmm_migration";
    static $highlevel_mutex_name = "codeneric_phmm_migration_highlevel";
    private static function get_lock_dir() {
      $upload_dir = \wp_upload_dir();
      return $upload_dir[\hacklib_id("basedir")];
    }
    private static function progress($semaphore_state) {
      $sum_done =
        \count($semaphore_state[\hacklib_id("finished")]) +
        \count($semaphore_state[\hacklib_id("failed")]);
      $sum_outstanding = \count($semaphore_state[\hacklib_id("outstanding")]);
      return ($sum_done > 0) ? (1 - ($sum_outstanding / $sum_done)) : 0;
    }
    private static function get_outstanding_functions($oldVersion) {
      $functions = \get_class_methods(FunctionContainer::class);
      $funcName = DBUpdater::version_to_func_name($oldVersion);
      \HH\invariant(
        is_array($functions),
        "%s",
        new Error("Fatal error, no update methods found!")
      );
      $filterFunctions = function($v) {
        return \strpos($v, "update_to_") === 0;
      };
      $update_funcs = \array_filter($functions, $filterFunctions);
      $update_funcs[] = $funcName;
      $update_funcs = \array_unique($update_funcs);
      \natsort($update_funcs);
      $update_funcs = \array_values($update_funcs);
      $oldVersionIndex = \array_search($funcName, $update_funcs);
      $outstanding_funcs = \array_slice($update_funcs, $oldVersionIndex + 1);
      return $outstanding_funcs;
    }
    public static function version_to_func_name($newVersion) {
      $tempNew = \str_replace(".", "_", $newVersion);
      return "update_to_".$tempNew;
    }
    public static function update($config) {
      $oldVersion =
        (string) \get_option("cc_photo_manage_curr_version", "1.0.1");
      $outstanding_functions = self::get_outstanding_functions($oldVersion);
      $all_relevant_update_function =
        function() use ($outstanding_functions) {
          return $outstanding_functions;
        };
      $lock = new FlockLock(self::get_lock_dir());
      $mutex = new Mutex(self::$highlevel_mutex_name, $lock);
      if (\hacklib_cast_as_boolean($mutex->acquireLock(0))) {
        DBUpdater::$currVersion = $config[\hacklib_id("version")];
        \HH\invariant(
          is_string($oldVersion),
          "%s",
          new Error(
            "String expected",
            array(array("oldVersion", $oldVersion))
          )
        );
        if (\get_option("cc_photo_manage_id") === false) {
          Logger::info("No UUID exists. Creating one.");
          $uniqid = \uniqid("", true);
          $uniqid = \str_replace(".", "", $uniqid);
          \update_option("cc_photo_manage_id", $uniqid);
        }
        $funcContainer = new FunctionContainer();
        $funcContainer->legacy($config);
        if ($oldVersion === DBUpdater::$currVersion) {
          $mutex->releaseLock();
          return 1;
        }
        Logger::info(
          "Starting migration from ".
          $oldVersion.
          " to ".
          DBUpdater::$currVersion
        );
        \flush_rewrite_rules();
        Logger::debug("About to get_state...");
        $semaphore_state = Semaphore::get_state(
          self::$mutex_name,
          $all_relevant_update_function
        );
        Logger::debug("get_state:", $semaphore_state);
        $items = array();
        $caller = function($funcName) use ($config) {
          return DBUpdater::execute_update_function($funcName);
        };
        Logger::debug("About to run...");
        $semaphore_state =
          Semaphore::run(self::$mutex_name, $semaphore_state, $caller);
        Logger::debug("new state:", $semaphore_state);
        Logger::debug("new state is null:", \is_null($semaphore_state));
        if (!\hacklib_cast_as_boolean(\is_null($semaphore_state))) {
          if (\count($semaphore_state[\hacklib_id("outstanding")]) === 0) {
            Logger::debug("No outstanding!");
            \update_option(
              "cc_photo_manage_curr_version",
              DBUpdater::$currVersion
            );
            Semaphore::delete_state(self::$mutex_name);
            \do_action("codeneric/phmm/base-plugin-updated");
            $mutex->releaseLock();
            return 1;
          } else {
            Logger::debug("Some outstanding, not done yet!");
            Semaphore::set_state(self::$mutex_name, $semaphore_state);
            Logger::debug("Have set new state!");
            $progress = self::progress($semaphore_state);
            Logger::debug("progress:", $progress);
            $mutex->releaseLock();
            return $progress;
          }
        } else {
          $semaphore_state = Semaphore::get_state(
            self::$mutex_name,
            $all_relevant_update_function
          );
          Logger::debug(
            "new state is null, get current state:",
            $semaphore_state
          );
          $progress = self::progress($semaphore_state);
          Logger::debug("progress:", $progress);
          $mutex->releaseLock();
          return $progress;
        }
      } else {
        Logger::debug("We dont have the lock...");
        $semaphore_state = Semaphore::get_state(
          self::$mutex_name,
          $all_relevant_update_function
        );
        $progress = self::progress($semaphore_state);
        return $progress;
      }
    }
    private static function execute_update_function($funcName) {
      $funcContainer = new FunctionContainer();
      $res = null;
      switch ($funcName) {
        case "update_to_4_3_1":
          $funcContainer->update_to_4_3_1();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_4_1_5":
          $funcContainer->update_to_4_1_5();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_4_0_0":
          $semaphore_state = $funcContainer->update_to_4_0_0();
          return
            (\count($semaphore_state[\hacklib_id("outstanding")]) > 0)
              ? SemaphoreExecutorReturn::Outstanding
              : SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_3_5_0":
          $funcContainer->update_to_3_5_0();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_3_2_6":
          $funcContainer->update_to_3_2_6();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_2_7_0":
          $funcContainer->update_to_2_7_0();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_2_3_0":
          $funcContainer->update_to_2_3_0();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_2_2_2":
          $funcContainer->update_to_2_2_2();
          return SemaphoreExecutorReturn::Finished;
          break;
        case "update_to_1_1_0":
          $funcContainer->update_to_1_1_0();
          return SemaphoreExecutorReturn::Finished;
          break;
        default:
          \HH\invariant(
            false,
            "%s",
            new Error(
              "Migration error: the update_function '".
              $funcName.
              "' is not mapped!"
            )
          );
          break;
      }
      return SemaphoreExecutorReturn::Finished;
    }
  }
}
