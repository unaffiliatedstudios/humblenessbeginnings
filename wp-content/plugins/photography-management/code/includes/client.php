<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Logger;
  class Client {
    const phmm_user_role = "phmm_client";
    public static function get($clientID) {
      if (get_post_status($clientID) === false) {
        return null;
      }
      $projectAccess = self::get_meta_project_access($clientID);
      $internalNotes = self::get_meta_internal_notes($clientID);
      $pwd = self::get_meta_plain_pwd($clientID);
      $user = null;
      if (\hacklib_cast_as_boolean(self::has_client_wp_user($clientID))) {
        $user = self::get_wp_user_from_client_id($clientID);
      }
      $client = array(
        "ID" => $clientID,
        "wp_user" => $user,
        "project_access" => $projectAccess,
        "internal_notes" => $internalNotes,
        "canned_email_history" => Utils::apply_filter_or(
          "codeneric/phmm/get_canned_email_history",
          $clientID,
          array()
        ),
        "plain_pwd" => $pwd
      );
      return $client;
    }
    public static function get_current() {
      $current_user = wp_get_current_user();
      if ($current_user === false) {
        return null;
      }
      $c = self::get_client_id_from_wp_user_id($current_user->ID);
      if (\hacklib_cast_as_boolean(is_null($c))) {
        return null;
      }
      return self::get($c);
    }
    private static function update_wp_user($wpUserID, $data) {
      $userdata = array(
        "display_name" => $data[\hacklib_id("post_title")],
        "user_email" => $data[\hacklib_id("email")],
        "user_login" => $data[\hacklib_id("user_login")],
        "ID" => $wpUserID,
        "user_pass" =>
          \hacklib_cast_as_boolean(is_null($data[\hacklib_id("plain_pwd")]))
            ? null
            : wp_hash_password($data[\hacklib_id("plain_pwd")])
      );
      wp_insert_user($userdata);
    }
    private static function create_and_get_wp_user($post_id, $data) {
      $userdata = array(
        "user_login" => $data[\hacklib_id("user_login")],
        "user_email" => $data[\hacklib_id("email")],
        "display_name" => $data[\hacklib_id("post_title")],
        "role" => Configuration::get()[\hacklib_id("client_user_role")],
        "show_admin_bar_front" => false,
        "user_pass" => $data[\hacklib_id("plain_pwd")]
      );
      $userID = wp_insert_user($userdata);
      \HH\invariant(
        is_int($userID),
        "%s",
        new Error(
          "Failed to create a user.",
          array(array("data", json_encode($data)))
        )
      );
      return $userID;
    }
    public static function typesafe_save($ID, $data) {
      $data = \codeneric\phmm\validate\client_to_db($data);
      update_post_meta(
        $ID,
        "project_access",
        $data[\hacklib_id("project_access")]
      );
      update_post_meta(
        $ID,
        "internal_notes",
        $data[\hacklib_id("internal_notes")]
      );
      update_post_meta($ID, "plain_pwd", $data[\hacklib_id("plain_pwd")]);
      update_post_meta($ID, "wp_user", $data[\hacklib_id("wp_user")]);
    }
    public static function get_meta_plain_pwd($post_id) {
      $mix = Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS(
        $post_id,
        "plain_pwd"
      );
      if (\hacklib_cast_as_boolean(is_string($mix))) {
        return $mix;
      } else {
        return null;
      }
    }
    public static function get_meta_wp_user($post_id) {
      $mix = Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS(
        $post_id,
        "wp_user"
      );
      if (!\hacklib_cast_as_boolean(is_null($mix))) {
        return (int) $mix;
      } else {
        return null;
      }
    }
    public static function get_meta_project_access($post_id) {
      $mix = Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS(
        $post_id,
        "project_access"
      );
      if (\hacklib_cast_as_boolean(is_array($mix))) {
        return array_map(
          function($e) {
            return \codeneric\phmm\validate\client_project_access($e);
          },
          $mix
        );
      } else {
        return array();
      }
    }
    public static function get_meta_internal_notes($post_id) {
      $mix = Utils::get_post_meta_ONLY_USE_IN_HELPER_FUNCTIONS(
        $post_id,
        "internal_notes"
      );
      if (\hacklib_cast_as_boolean(is_string($mix))) {
        return $mix;
      } else {
        return null;
      }
    }
    public static function save($post_id, $data) {
      $pwd = null;
      $plain_pwd = $data[\hacklib_id("plain_pwd")];
      $oldPwd = self::get_meta_plain_pwd($post_id);
      if (\hacklib_cast_as_boolean(is_null($plain_pwd))) {
        if (\hacklib_cast_as_boolean(is_null($oldPwd))) {
          $pwd = wp_generate_password(10);
        } else {
          $pwd = $oldPwd;
        }
      } else {
        $pwd = $plain_pwd;
      }
      $data[\hacklib_id("plain_pwd")] = $pwd;
      $wp_user_id = self::get_client_wp_user_id($post_id);
      if (\hacklib_cast_as_boolean(is_int($wp_user_id))) {
        self::update_wp_user($wp_user_id, $data);
      } else {
        $wp_user_id = self::create_and_get_wp_user($post_id, $data);
      }
      self::typesafe_save(
        $post_id,
        array(
          "project_access" => $data[\hacklib_id("project_access")],
          "wp_user" => $wp_user_id,
          "internal_notes" => $data[\hacklib_id("internal_notes")],
          "plain_pwd" => $data[\hacklib_id("plain_pwd")]
        )
      );
    }
    public static function has_client_wp_user($clientID) {
      $id = self::get_meta_wp_user($clientID);
      return !\hacklib_cast_as_boolean(is_null($id));
    }
    public static function get_client_wp_user_id($clientID) {
      return self::get_meta_wp_user($clientID);
    }
    public static function get_client_id_from_wp_user_id($userID) {
      $clients = self::get_all_clients();
      $clientID = null;
      foreach ($clients as $client) {
        $uid = self::get_client_wp_user_id($client->ID);
        if ($uid === $userID) {
          $clientID = $client->ID;
        }
      }
      return $clientID;
    }
    public static function get_wp_user_from_client_id($clientID) {
      $id = self::get_client_wp_user_id($clientID);
      if (!\hacklib_cast_as_boolean(is_int($id))) {
        return null;
      }
      $user = get_user_by("ID", $id);
      if ($user instanceof \WP_User) {
        return $user;
      }
      return null;
    }
    public static function get_project_ids($clientID) {
      if ($clientID === 0) {
        $project_ids_with_guest_access = array();
        $project_ids = Project::get_all_ids();
        foreach ($project_ids as $id) {
          $protec = Project::get_protection($id);
          if ((!\hacklib_cast_as_boolean(
                 is_null($protec[\hacklib_id("password")])
               )) ||
              (!\hacklib_cast_as_boolean($protec[\hacklib_id("private")]))) {
            $project_ids_with_guest_access[] = $id;
          }
        }
        return $project_ids_with_guest_access;
      }
      $projects = self::get_meta_project_access($clientID);
      if (\hacklib_cast_as_boolean(is_array($projects))) {
        $map = function($project) {
          \HH\invariant(
            array_key_exists("id", $project),
            "%s",
            new Error("Project access shape different than expected")
          );
          return $project[\hacklib_id("id")];
        };
        return array_values(array_map($map, $projects));
      }
      return array();
    }
    public static function get_project_wp_posts(
      $clientID,
      $filterActive = false
    ) {
      $projects = self::get_meta_project_access($clientID);
      \HH\invariant(
        is_array($projects),
        "%s",
        new Error("get project_access meta expected to be array")
      );
      if (\hacklib_cast_as_boolean($filterActive)) {
        $projects = array_values(
          array_filter(
            $projects,
            function($project) {
              return $project[\hacklib_id("active")] === true;
            }
          )
        );
      }
      $map = function($project) {
        $post = get_post($project[\hacklib_id("id")]);
        \HH\invariant(
          $post instanceof \WP_Post,
          "%s",
          new Error("Could not get project post by id")
        );
        return $post;
      };
      $posts = array_map($map, $projects);
      return $posts;
    }
    public static function get_project_configuration($clientID, $projectID) {
      $accesses = self::get_meta_project_access($clientID);
      foreach ($accesses as $i => $a) {
        if ($a[\hacklib_id("id")] === $projectID) {
          if (!\hacklib_cast_as_boolean(
                is_null($a[\hacklib_id("configuration")])
              )) {
            return $a[\hacklib_id("configuration")];
          } else {
            return Project::get_configuration($projectID);
          }
        }
      }
      return null;
    }
    public static function get_all_ids() {
      $clientIDs = get_posts(
        array(
          "post_type" => Configuration::get()[\hacklib_id(
            "client_post_type"
          )],
          "post_status" => "any",
          "numberposts" => -1,
          "fields" => "ids"
        )
      );
      \HH\invariant(
        is_array($clientIDs),
        "%s",
        new Error("Expected array getting client IDs")
      );
      return $clientIDs;
    }
    public static function get_all_clients() {
      $clients = get_posts(
        array(
          "post_type" => Configuration::get()[\hacklib_id(
            "client_post_type"
          )],
          "post_status" => "any",
          "numberposts" => -1,
          "post_parent" => null
        )
      );
      \HH\invariant(
        is_array($clients),
        "%s",
        new Error("Expected array getting clients")
      );
      return $clients;
    }
    public static function get_all_labels_from_client($clientID) {
      $projectIDs = Client::get_project_ids($clientID);
      $labels = array();
      foreach ($projectIDs as $projectID) {
        $labels[] = array(
          "client_id" => $clientID,
          "project_id" => $projectID,
          "label_id" => (string) InternalLabelID::Favorites,
          "labels" => Labels::get_set(
            $clientID,
            $projectID,
            (string) InternalLabelID::Favorites
          )
        );
      }
      return $labels;
    }
    public static function has_access_to_project($clientID, $projectID) {
      $protection = Project::get_protection($projectID);
      if ($protection[\hacklib_id("private")] === false) {
        return true;
      }
      $project_access = self::get_meta_project_access($clientID);
      foreach ($project_access as $access) {
        if ($access[\hacklib_id("id")] === $projectID) {
          return $access[\hacklib_id("active")];
        }
      }
      return false;
    }
    public static function dereference_project($projectID, $clientIDs) {
      if (\hacklib_equals($clientIDs, null)) {
        $clientIDs = self::get_all_ids();
      }
      foreach ($clientIDs as $clientID) {
        $access = self::get_meta_project_access($clientID);
        \HH\invariant(
          is_array($access),
          "%s",
          new Error("project_access shape not as expected")
        );
        $filter = function($acc) use ($projectID) {
          \HH\invariant(
            is_array($acc),
            "%s",
            new Error("project_access shape not as expected")
          );
          if ($acc[\hacklib_id("id")] === $projectID) {
            return false;
          }
          return true;
        };
        $cleanAccess = array_filter($access, $filter);
        update_post_meta($clientID, "project_access", $cleanAccess);
      }
    }
    public static function get_name($clientID) {
      return ($clientID !== 0) ? get_the_title($clientID) : "Guest";
    }
    public static function delete_client($userID) {
      $postID = self::get_client_id_from_wp_user_id($userID);
      if (\hacklib_cast_as_boolean(is_null($postID))) {
        return;
      }
      wp_delete_post($postID, true);
    }
  }
}
