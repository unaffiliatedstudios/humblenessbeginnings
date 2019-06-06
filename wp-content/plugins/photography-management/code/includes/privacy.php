<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class Privacy {
    public static function data_remover() {}
    public static function get_privacy_content() {
      if (!\hacklib_cast_as_boolean(
            function_exists("wp_add_privacy_policy_content")
          )) {
        return;
      }
      $content = "";
      $content .= "<h3>What Photography Management collects</h3>";
      $content .=
        "The plugin Photography Management does not collect personal data from visitors, it only collects personal data from logged in users. The personal data is limited to data gathered by the user's interaction with the plugin, which are the following: 'proofing' (liking) images and commenting images.";
      wp_add_privacy_policy_content(
        "Photography Management",
        wp_kses_post(wpautop($content, false))
      );
    }
    public static function export_item($email_address, $page = 1) {
      $emptyReturn = array("done" => true, "data" => array());
      $user = get_user_by("email", $email_address);
      if ($user instanceof \WP_User) {
        $clientID = Client::get_client_id_from_wp_user_id($user->ID);
        if (\hacklib_cast_as_boolean(is_null($clientID))) {
          return $emptyReturn;
        }
        $config = \codeneric\phmm\Configuration::get();
        $data = array();
        $projects = Client::get_project_ids($clientID);
        $projectTitles = array_map(
          function($projectID) {
            return Project::get_title_with_id_default($projectID);
          },
          $projects
        );
        $data[] = array(
          "name" => "Assigned Projects",
          "value" =>
            (count($projectTitles) > 0)
              ? implode(",", $projectTitles)
              : "none"
        );
        $item = array(
          "group_id" => $config[\hacklib_id("plugin_slug_abbr")],
          "group_label" => "Photography Management",
          "item_id" =>
            $config[\hacklib_id("client_post_type")]."-".$clientID,
          "data" => $data
        );
        return array("done" => true, "data" => array($item));
      } else {
        return $emptyReturn;
      }
    }
    public static function register_data_exporter($exporters) {
      $exporters[\codeneric\phmm\Configuration::get()[\hacklib_id(
        "plugin_name"
      )]] = array(
        "exporter_friendly_name" => __("Photography Management Plugin"),
        "callback" => array(Privacy::class, "export_item")
      );
      return $exporters;
    }
  }
}
