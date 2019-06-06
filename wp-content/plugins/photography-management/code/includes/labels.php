<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  final class InternalLabelID {
    private function __construct() {}
    private static $hacklib_values = array("Favorites" => "1111111111111");
    use \HH\HACKLIB_ENUM_LIKE;
    const Favorites = "1111111111111";
  }
  class Labels {
    const allLabelsOptionName = "codeneric/phmm/labels/all";
    private static function generate_label_id() {
      return uniqid();
    }
    private static function get_option_name($clientID, $projectID, $labelID) {
      \HH\invariant(
        is_int($clientID),
        "%s",
        new Error("clientID must be int")
      );
      \HH\invariant(
        is_int($projectID),
        "%s",
        new Error("projectID must be int")
      );
      \HH\invariant(
        is_string($labelID),
        "%s",
        new Error("labelID must be string")
      );
      \HH\invariant(
        $labelID !== "",
        "%s",
        new Error("labelID cannot be empty string")
      );
      $hash = md5($clientID."/".$projectID."/".$labelID);
      return "codeneric/phmm/labels/".$hash;
    }
    public static function get_all_labels() {
      $labels = get_option(self::allLabelsOptionName, array());
      \HH\invariant(
        is_array($labels),
        "%s",
        new Error("Expected labels to be of type array.")
      );
      return $labels;
    }
    public static function label_exists($id) {
      $labels = self::get_all_labels();
      $filter = function($label) use ($id) {
        \HH\invariant(
          is_array($label),
          "%s",
          new Error("label expected to be of type array")
        );
        \HH\invariant(
          array_key_exists("id", $label),
          "%s",
          new Error("key id expected to exist in label array")
        );
        return $label[\hacklib_id("id")] === $id;
      };
      $matches = array_values(array_filter($labels, $filter));
      return count($matches) === 1;
    }
    private static function save_all_labels($labels) {
      return update_option(Labels::allLabelsOptionName, $labels);
    }
    public static function get_label_by_name($labelName) {
      $labels = self::get_all_labels();
      $filter = function($label) use ($labelName) {
        \HH\invariant(
          is_array($label),
          "%s",
          new Error("label expected to be of type array")
        );
        \HH\invariant(
          array_key_exists("name", $label),
          "%s",
          new Error("key name expected to exist in label array")
        );
        return $label[\hacklib_id("name")] === $labelName;
      };
      $matches = array_values(array_filter($labels, $filter));
      return $matches;
    }
    public static function get_label_id_by_name($labelName) {
      $labels = self::get_label_by_name($labelName);
      \HH\invariant(
        count($labels) <= 1,
        "%s",
        new Error("Cannot get ID when label name matches multiple entries")
      );
      if (count($labels) === 0) {
        return null;
      }
      $label = $labels[0];
      return $label[\hacklib_id("id")];
    }
    public static function update_label($name, $id) {
      \HH\invariant(
        $name !== "",
        "%s",
        new Error("Label name cannot be empty string")
      );
      \HH\invariant(
        $id !== "",
        "%s",
        new Error("Label id cannot be empty string")
      );
      $labels = self::get_all_labels();
      if (\hacklib_cast_as_boolean(is_null($id))) {
        $copy = $labels;
        $id = self::generate_label_id();
        array_push($copy, array("id" => $id, "name" => $name));
        return self::save_all_labels($copy);
      }
      $map = function($label) use ($name, $id) {
        if ($label[\hacklib_id("id")] !== $id) {
          return $label;
        }
        $label[\hacklib_id("name")] = $name;
        return $label;
      };
      if (count($labels) > 0) {
        $newLabels = array_map($map, $labels);
      } else {
        $newLabels = array(array("id" => $id, "name" => $name));
      }
      return self::save_all_labels($newLabels);
    }
    public static function get_set($clientID, $projectID, $labelID) {
      $optionName = self::get_option_name($clientID, $projectID, $labelID);
      $labels = get_option($optionName, array());
      \HH\invariant(
        is_array($labels),
        "%s",
        new Error("Expected labels to be of type array.")
      );
      return $labels;
    }
    public static function save_set(
      $clientID,
      $projectID,
      $labelID,
      $imageIDs
    ) {
      \HH\invariant(
        is_array($imageIDs),
        "%s",
        new Error("Expected labels to be of type array.")
      );
      $optionName = self::get_option_name($clientID, $projectID, $labelID);
      return update_option($optionName, $imageIDs);
    }
    public static function delete_set($clientID, $projectID, $labelID) {
      $optionName = self::get_option_name($clientID, $projectID, $labelID);
      return delete_option($optionName);
    }
    public static function delete_label($id) {
      $labels = self::get_all_labels();
      \HH\invariant(
        is_array($labels),
        "%s",
        new Error("get all labels does not return array!")
      );
      \HH\invariant(
        count($labels) !== 0,
        "%s",
        new Error("_labels is an empty array")
      );
      \HH\invariant(
        !\hacklib_cast_as_boolean(InternalLabelID::isValid($id)),
        "%s",
        new Error("internal labels cannot be deleted")
      );
      foreach ($labels as $index => $label) {
        \HH\invariant(
          array_key_exists("id", $label),
          "%s",
          new Error("_label does not have an id!")
        );
        if (\hacklib_equals($label[\hacklib_id("id")], $id)) {
          unset($labels[$index]);
          $client_ids = Client::get_all_ids();
          \HH\invariant(
            is_array($client_ids),
            "%s",
            new Error("client_ids is not an array")
          );
          $project_ids = Project::get_all_ids();
          \HH\invariant(
            is_array($project_ids),
            "%s",
            new Error("project_ids is not an array")
          );
          if ((count($client_ids) !== 0) && (count($project_ids) !== 0)) {
            foreach ($client_ids as $client_id) {
              foreach ($project_ids as $project_id) {
                self::delete_set($client_id, $project_id, $id);
              }
            }
          }
        }
      }
      return self::save_all_labels($labels);
    }
    public static function initLabelStore() {
      $res = self::get_all_labels();
      \HH\invariant(
        is_array($res),
        "%s",
        new Error("result of get all labels is not an array!")
      );
      if (\hacklib_not_equals(count($res), 0)) {
        foreach ($res as $label) {
          \HH\invariant(
            array_key_exists("id", $label),
            "%s",
            new Error("key does not exist in _label")
          );
          if (!\hacklib_cast_as_boolean(
                InternalLabelID::isValid($label[\hacklib_id("id")])
              )) {
            \HH\invariant(
              is_string($label[\hacklib_id("id")]),
              "%s",
              new Error("_label id is not a string")
            );
            \HH\invariant(
              is_string($label[\hacklib_id("name")]),
              "%s",
              new Error("_label name is not a string")
            );
            $bool = self::update_label(
              $label[\hacklib_id("name")],
              $label[\hacklib_id("id")]
            );
            if (\hacklib_cast_as_boolean($bool)) {
            } else {
            }
          }
        }
      } else {
        foreach (InternalLabelID::getValues() as $key => $val) {
          \HH\invariant(
            is_string($key),
            "%s",
            new Error("_key is not a string")
          );
          \HH\invariant(
            is_string($val),
            "%s",
            new Error("_val name is not a string")
          );
          $bool = self::update_label($key, $val);
          if (\hacklib_cast_as_boolean($bool)) {
          } else {
          }
        }
      }
    }
  }
}
