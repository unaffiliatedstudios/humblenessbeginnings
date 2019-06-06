<?php
namespace codeneric\phmm {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\admin\FrontendHandler;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\Logger;
  use \codeneric\phmm\enums\SemaphoreExecutorReturn;
  use \codeneric\phmm\Semaphore;
  require_once (dirname(__FILE__)."/admin.php");
  require_once
    (dirname(__FILE__)."/../protect_images/generate_htaccess.php")
  ;
  require_once (ABSPATH."wp-admin/includes/upgrade.php");
  require_once (dirname(__FILE__)."/schema/legacy/read_3_6_5.php");
  require_once (dirname(__FILE__)."/schema/legacy/map.php");
  require_once (dirname(__FILE__)."/schema/legacy/write_4_0_0.php");
  require_once (dirname(__FILE__)."/schema/legacy/legacy_validators.php");
  class FunctionContainer {
    public function update_to_1_1_0() {
      $options = get_option("cc_photo_settings", array());
      \HH\invariant(is_array($options), "%s", new Error("Expected array."));
      $options[\hacklib_id("cc_photo_image_box")] = 1;
      $options[\hacklib_id("cc_photo_download_text")] = "Download all";
      update_option("cc_photo_settings", $options);
      $posts_array = get_posts("post_type=client");
      foreach ($posts_array as $client) {
        $projects = get_post_meta($client->ID, "projects", true);
        $projects =
          \hacklib_cast_as_boolean(is_array($projects)) ? $projects : array();
        foreach ($projects as $k => $project) {
          $projects[$k][\hacklib_id("downloadable")] = true;
        }
        update_post_meta($client->ID, "projects", $projects);
      }
    }
    public function update_to_2_2_2() {
      if (get_option("codeneric_phmm_error_log") === false) {
        update_option("codeneric_phmm_error_log", array());
      }
    }
    public function update_to_2_3_0() {
      add_role(
        "phmm_client",
        __("PhMm Client"),
        array(
          "read" => true,
          "edit_posts" => false,
          "delete_posts" => false
        )
      );
    }
    public function update_to_2_7_0() {
      $upload_dir = wp_upload_dir();
      $upload_dir =
        $upload_dir[\hacklib_id("basedir")]."/photography_management";
      if (\hacklib_cast_as_boolean(is_link($upload_dir."/protect.php"))) {
        unlink($upload_dir."/protect.php");
      }
      if (\hacklib_cast_as_boolean(is_link($upload_dir."/.htaccess"))) {
        unlink($upload_dir."/.htaccess");
      }
      if (\hacklib_cast_as_boolean(file_exists($upload_dir."/.htaccess"))) {
        unlink($upload_dir."/.htaccess");
      }
      Photography_Management_Base_Generate_Htaccess($upload_dir."/.htaccess");
    }
    public function update_to_3_2_6() {
      $upload_dir = wp_upload_dir();
      $upload_dir =
        $upload_dir[\hacklib_id("basedir")]."/photography_management";
      if (\hacklib_cast_as_boolean(file_exists($upload_dir."/.htaccess"))) {
        unlink($upload_dir."/.htaccess");
      }
      Photography_Management_Base_Generate_Htaccess($upload_dir."/.htaccess");
    }
    public function update_to_3_5_0() {
      $query_args = array(
        "posts_per_page" => -1,
        "offset" => 0,
        "post_type" => "client"
      );
      $posts_array = get_posts($query_args);
      foreach ($posts_array as $client) {
        $projects = get_post_meta($client->ID, "projects", true);
        $projects =
          \hacklib_cast_as_boolean(is_array($projects)) ? $projects : array();
        foreach ($projects as $k => $project) {
          $uniqid = uniqid("", true);
          $uniqid = str_replace(".", "", $uniqid);
          if (\hacklib_cast_as_boolean(/* UNSAFE_EXPR */
                (!isset($projects[$k][\hacklib_id("id")])) ||
                \hacklib_equals($projects[$k][\hacklib_id("id")], false)
              )) {
            $projects[$k][\hacklib_id("id")] = $uniqid;
          }
        }
        update_post_meta($client->ID, "projects", $projects);
      }
    }
    public function update_to_4_0_0() {
      Logger::info("Starting update to 4.0.0");
      Logger::info("Memory allocated in beginning: ".memory_get_usage());
      try {
        $memory_limit = ini_get("memory_limit");
      } catch (\Exception $e) {
        $memory_limit = "not retrievable";
      }
      Logger::info("Memory limit: ".$memory_limit);
      $startTime = microtime(true);
      $wpdb = Superglobals::Globals("wpdb");
      \HH\invariant(
        $wpdb instanceof \wpdb,
        "%s",
        new Error("wpdb is not available!")
      );
      $charset_collate = $wpdb->get_charset_collate();
      $table_name = "codeneric_phmm_comments";
      $sql =
        "CREATE TABLE ".
        $table_name.
        " (\n      id   bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,\n      content   text DEFAULT '' NOT NULL,\n      project_id   bigint(20) UNSIGNED NOT NULL,\n      attachment_id   bigint(20) UNSIGNED NOT NULL,\n      wp_user_id   bigint(20) UNSIGNED NOT NULL,\n      client_id   bigint(20) UNSIGNED NOT NULL,\n      wp_author_id   bigint(20) UNSIGNED NOT NULL,\n      UNIQUE KEY id (id)\n    ) ".
        $charset_collate.
        ";";
      dbDelta(/* UNSAFE_EXPR */ $sql);
      Logger::info("Comments Table created");
      try {
        $plugin_settings =
          \codeneric\phmm\legacy\v3_6_5\read_plugin_settings();
        Logger::info("Old Plugin Settings");
        Logger::info(json_encode($plugin_settings));
        $plugin_settings_4_0_0 =
          \codeneric\phmm\legacy\map_plugin_settings_from_3_6_5(
            $plugin_settings
          );
        Logger::info("New Plugin Settings");
        Logger::info(json_encode($plugin_settings_4_0_0));
        update_option(
          "codeneric_phmm_plugin_settings",
          $plugin_settings_4_0_0
        );
        Logger::info("Plugin settings successfully migrated");
      } catch (\Exception $e) {
        Logger::error(
          "Migrating plugin settings failed! ".$e->__toString(),
          array(
            "memory" => memory_get_usage(),
            "seconds_passed" => microtime(true) - $startTime
          )
        );
      }
      $mutex_name = "update_to_4_0_0";
      $all_client_ids = function() {
        $query_args = array(
          "posts_per_page" => -1,
          "offset" => 0,
          "post_type" => "client"
        );
        $posts_array = get_posts($query_args);
        $ids = array();
        foreach ($posts_array as $p) {
          $ids[] = (int) $p->ID;
        }
        return $ids;
      };
      $semaphore_state = Semaphore::get_state($mutex_name, $all_client_ids);
      Logger::info("update_to_4_0_0 get_state:", $semaphore_state);
      $caller =
        function($client_id) {
          if ("production" === "development") {
            sleep(11);
          }
          $client = get_post_meta($client_id, "client", true);
          try {
            Logger::info(
              "Migrate client data representation for ID ".$client_id,
              $client
            );
            $client =
              \codeneric\phmm\legacy\validate\client_data_representation_3_6_5(
                $client
              );
            Logger::info(
              "Got v3.6.5 client for ID ".$client_id." was successfull!",
              $client
            );
            Logger::info("Getting projects for client ID ".$client_id);
            $projects =
              \codeneric\phmm\legacy\v3_6_5\read_projects($client_id);
            Logger::info("Success! Got ".count($projects)." projects");
            $project_ids = array();
            foreach ($projects as $key => $project) {
              Logger::info(
                "Migrating project with ID ".$project[\hacklib_id("id")]
              );
              $project_4_0_0 = \codeneric\phmm\legacy\map_project_from_3_6_5(
                $project,
                $client[\hacklib_id("pwd")],
                false
              );
              Logger::info(
                "Success migrating project with ID ".
                $project[\hacklib_id("id")],
                $project_4_0_0
              );
              $pid = wp_insert_post(
                array(
                  "post_title" => $project[\hacklib_id("title")],
                  "post_type" => "project",
                  "post_status" => get_post_status($client_id),
                  "post_password" => $client[\hacklib_id("pwd")],
                  "post_content" => $project[\hacklib_id("description")]
                )
              );
              if (\hacklib_cast_as_boolean(is_int($pid)) && ($pid !== 0)) {
                Logger::info(
                  "Inserted WP Post for project with ID ".
                  $project[\hacklib_id("id")]
                );
                \codeneric\phmm\legacy\v4_0_0\save_project(
                  $pid,
                  $project_4_0_0
                );
                Logger::info("Saved metadata");
                $project_ids[] = $pid;
                if (!\hacklib_cast_as_boolean(
                      is_null($client[\hacklib_id("wp_user_id")])
                    )) {
                  Logger::info(
                    "Mapping comments for project with ID ".
                    $project[\hacklib_id("id")]
                  );
                  foreach ($project[\hacklib_id("gallery")] as $i) {
                    $comments =
                      \codeneric\phmm\legacy\v3_6_5\read_comments($i);
                    foreach ($comments as $c) {
                      $wp_user_id = $client[\hacklib_id("wp_user_id")];
                      $wp_user_id_of_client =
                        (!\hacklib_cast_as_boolean(is_null($wp_user_id)))
                          ? $wp_user_id
                          : 0;
                      Logger::info("Got v3.6.5 comment", $c);
                      $comment_4_0_0 =
                        \codeneric\phmm\legacy\map_comment_from_3_6_5(
                          $c,
                          $pid,
                          $wp_user_id_of_client
                        );
                      Logger::info(
                        "Mapped to v4.0.0 comment",
                        $comment_4_0_0
                      );
                      \codeneric\phmm\legacy\v4_0_0\save_comment(
                        $comment_4_0_0
                      );
                      Logger::info("Saved comment");
                    }
                  }
                  Logger::info("Saving favorites");
                  \codeneric\phmm\legacy\v4_0_0\save_lable_set(
                    $client_id,
                    $pid,
                    $project[\hacklib_id("starred")]
                  );
                  Logger::info("Saving favorites successfull");
                }
              }
            }
            if (!\hacklib_cast_as_boolean(
                  is_null($client[\hacklib_id("wp_user_id")])
                )) {
              update_post_meta(
                $client_id,
                "wp_user",
                $client[\hacklib_id("wp_user_id")]
              );
              $client_4_0_0 = \codeneric\phmm\legacy\map_client_from_3_6_5(
                $client,
                $project_ids
              );
              Logger::info(
                "Migrated client to v4.0.0. for ID ".$client_id,
                $client
              );
              \codeneric\phmm\legacy\v4_0_0\save_client(
                $client_id,
                $client_4_0_0
              );
              Logger::info("Client saved", $client);
            }
          } catch (\Exception $e) {
            Logger::error(
              "Migrating client with id ".
              $client_id.
              "failed! ".
              $e->__toString(),
              array("memory" => memory_get_usage())
            );
            return SemaphoreExecutorReturn::Failed;
          }
          return SemaphoreExecutorReturn::Finished;
        };
      Logger::info("Starting migration of clients...", $semaphore_state);
      $semaphore_state =
        Semaphore::run($mutex_name, $semaphore_state, $caller);
      if (!\hacklib_cast_as_boolean(is_null($semaphore_state))) {
        Logger::info("new clients migration state:", $semaphore_state);
        Semaphore::set_state($mutex_name, $semaphore_state);
      }
      $semaphore_state = Semaphore::get_state($mutex_name, $all_client_ids);
      if (count($semaphore_state[\hacklib_id("outstanding")]) === 0) {
        Logger::info("Clients migration finished:", $semaphore_state);
        Semaphore::delete_state($mutex_name);
      }
      return $semaphore_state;
    }
    public function update_to_4_1_5() {
      $install_time = get_option("codeneric/phmm/install_time");
      if ($install_time === false) {
        update_option("codeneric/phmm/install_time", time());
      }
      $wpdb = Superglobals::Globals("wpdb");
      \HH\invariant(
        $wpdb instanceof \wpdb,
        "%s",
        new Error("wpdb is not available!")
      );
      $charset_collate = $wpdb->get_charset_collate();
      $table_name = "codeneric_phmm_comments";
      $sql =
        "CREATE TABLE ".
        $table_name.
        " (\n      id   bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,\n      time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,\n      content   text DEFAULT '' NOT NULL,\n      project_id   bigint(20) UNSIGNED NOT NULL,\n      attachment_id   bigint(20) UNSIGNED NOT NULL,\n      wp_user_id   bigint(20) UNSIGNED NOT NULL,\n      client_id   bigint(20) UNSIGNED NOT NULL,\n      wp_author_id   bigint(20) UNSIGNED NOT NULL,\n      UNIQUE KEY id (id)\n    ) ".
        $charset_collate.
        ";";
      dbDelta(/* UNSAFE_EXPR */ $sql);
    }
    public function legacy($cc_phmm_config) {
      $p = get_option("cc_prem");
      if (\hacklib_cast_as_boolean($p) &&
          (!\hacklib_cast_as_boolean(
             $cc_phmm_config[\hacklib_id("has_premium_ext")]
           ))) {
        $cc_phmm_base_admin_notice_update_to_premium =
          function() {
            $class = "notice notice-error";
            $prem_url = admin_url("edit.php");
            $prem_url = add_query_arg(
              array("post_type" => "client", "page" => "premium"),
              $prem_url
            );
            $p_url = admin_url("plugins.php");
            $message =
              "Please <a id=\"cc_phmm_install_notice\" href=\"".
              $prem_url.
              "\" data-plugins-url=\"".
              $p_url.
              "\" >install</a> the Photography Management Premium extension!";
            wp_enqueue_script(
              "cc_phmm_admin_notice",
              plugin_dir_url(__FILE__)."/partials/admin_notice.js"
            );
            $spinner =
              "<div id=\"cc_phmm_notice_spinner\" style=\"background:url('images/spinner.gif') no-repeat;background-size: 20px 20px;vertical-align: middle;margin: 0 auto;height: 20px;width: 20px;display:none;\"></div>";
            echo
              ("<div id=\"cc_phmm_notice_wrap\" class=\"".
               $class.
               "\"> <p>".
               $spinner.
               " ".
               $message.
               "</p></div>")
            ;
          };
        add_action(
          "admin_notices",
          $cc_phmm_base_admin_notice_update_to_premium
        );
      } else {
        if (\hacklib_cast_as_boolean(
              $cc_phmm_config[\hacklib_id("has_premium_ext")]
            ) &&
            (!\hacklib_cast_as_boolean(
               $cc_phmm_config[\hacklib_id("premium_ext_active")]
             ))) {
          $cc_phmm_base_admin_notice_update_to_premium =
            function() {
              $class = "notice notice-error";
              $d_url = admin_url("plugins.php");
              $message =
                "Please <a href=\"".
                $d_url.
                "\" >activate</a> the Photography Management Premium extension!";
              $script = "";
              echo
                ("<div id=\"cc_phmm_notice_wrap\" class=\"".
                 $class.
                 "\"> <p>".
                 $message.
                 "</p></div>".
                 $script)
              ;
            };
          add_action(
            "admin_notices",
            $cc_phmm_base_admin_notice_update_to_premium
          );
        }
      }
    }
  }
}
