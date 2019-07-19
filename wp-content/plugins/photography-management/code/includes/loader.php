<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  class Loader {
    protected $actions;
    protected $filters;
    public function __construct() {
      $this->actions = new \HH\Vector(array());
      $this->filters = new \HH\Vector(array());
    }
    public function add_action(
      $hook,
      $component,
      $callback,
      $priority = 10,
      $accepted_args = 1
    ) {
      $this->actions = $this->add(
        $this->actions,
        $hook,
        $component,
        $callback,
        $priority,
        $accepted_args
      );
    }
    public function add_filter(
      $hook,
      $component,
      $callback,
      $priority = 10,
      $accepted_args = 1
    ) {
      $this->filters = $this->add(
        $this->filters,
        $hook,
        $component,
        $callback,
        $priority,
        $accepted_args
      );
    }
    private function add(
      $hooks,
      $hook,
      $component,
      $callback,
      $priority,
      $accepted_args
    ) {
      $hooks[] = array(
        "hook" => $hook,
        "component" => $component,
        "callback" => $callback,
        "priority" => $priority,
        "accepted_args" => $accepted_args
      );
      return $hooks;
    }
    public function run() {
      foreach ($this->filters as $hook) {
        \add_filter(
          $hook[\hacklib_id("hook")],
          array(
            $hook[\hacklib_id("component")],
            $hook[\hacklib_id("callback")]
          ),
          $hook[\hacklib_id("priority")],
          $hook[\hacklib_id("accepted_args")]
        );
      }
      foreach ($this->actions as $hook) {
        if ($hook[\hacklib_id("component")] instanceof Labels) {
        }
        \add_action(
          $hook[\hacklib_id("hook")],
          array(
            $hook[\hacklib_id("component")],
            $hook[\hacklib_id("callback")]
          ),
          $hook[\hacklib_id("priority")],
          $hook[\hacklib_id("accepted_args")]
        );
      }
    }
  }
}