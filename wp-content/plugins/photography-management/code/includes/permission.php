<?php
namespace codeneric\phmm\base\includes {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Client;
  use \codeneric\phmm\base\includes\Project;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\enums\UserState as userState;
  use \codeneric\phmm\enums\ProjectState as projectState;
  use \codeneric\phmm\enums\ProjectDisplay as display;
  use \codeneric\phmm\enums\ClientDisplay as clientDisplay;
  use \codeneric\phmm\enums\PortalDisplay as portalDisplay;
  class Permission {
    public static function current_user_can_access_client($client_id) {
      $is_admin = Utils::is_current_user_admin();
      if (\hacklib_cast_as_boolean($is_admin)) {
        return true;
      }
      $client = Client::get($client_id);
      if (\hacklib_cast_as_boolean(\is_null($client))) {
        return false;
      }
      $wp_user = $client[\hacklib_id("wp_user")];
      if (\hacklib_cast_as_boolean(\is_null($wp_user))) {
        return false;
      }
      $current_user = \wp_get_current_user();
      if ($current_user === 0) {
        return false;
      }
      return $current_user->ID === $wp_user->ID;
    }
    public static function current_user_can_access_project($project_id) {
      $project_state = self::get_project_state($project_id);
      $client_state =
        self::get_client_state_wrt_project($project_state, $project_id);
      $allowed_states = array(
        userState::Admin,
        userState::Client,
        userState::Guest
      );
      return \in_array($client_state, $allowed_states);
    }
    public static function get_client_id_wrt_project($project_id) {
      $project_state = self::get_project_state($project_id);
      $client_state =
        self::get_client_state_wrt_project($project_state, $project_id);
      switch ($client_state) {
        case userState::Client:
          $client = Client::get_current();
          return
            \hacklib_cast_as_boolean(\is_null($client))
              ? null
              : $client[\hacklib_id("ID")];
          break;
        case userState::Admin:
        case userState::Guest:
          return 0;
          break;
        default:
          return null;
          break;
      }
    }
    public static function get_client_state_wrt_project(
      $projectState,
      $projectID
    ) {
      if (!\hacklib_cast_as_boolean(\is_user_logged_in())) {
        if (($projectState !== projectState::PrivateWithGuestLogin) &&
            ($projectState !==
             projectState::PrivateWithGuestLoginNoClientsAssigned)) {
          return
            ($projectState !== projectState::Public_)
              ? userState::NotLoggedIn
              : userState::Guest;
        }
        $pwdRequired = self::post_password_required($projectID);
        if (\hacklib_cast_as_boolean($pwdRequired)) {
          return userState::NotLoggedIn;
        } else {
          return userState::Guest;
        }
      } else {
        if (\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
          return userState::Admin;
        }
        $maybeClient = Client::get_current();
        if (\hacklib_cast_as_boolean(\is_null($maybeClient))) {
          return userState::LoggedInUserWithNoAccess;
        }
        $client = $maybeClient;
        $hasAccess = Client::has_access_to_project(
          $client[\hacklib_id("ID")],
          $projectID
        );
        return
          \hacklib_cast_as_boolean($hasAccess)
            ? userState::Client
            : userState::LoggedInUserWithNoAccess;
      }
    }
    public static function get_project_state($projectID) {
      $state = Project::get_protection($projectID);
      if ($state[\hacklib_id("private")] === false) {
        return projectState::Public_;
      }
      if (($state[\hacklib_id("password_protection")] === true) &&
          (!\hacklib_cast_as_boolean(
             \is_null($state[\hacklib_id("password")])
           )) &&
          ($state[\hacklib_id("password")] !== "")) {
        return
          \hacklib_cast_as_boolean(
            Project::is_assigned_to_at_least_one_client($projectID)
          )
            ? projectState::PrivateWithGuestLogin
            : projectState::PrivateWithGuestLoginNoClientsAssigned;
      }
      return projectState::Private_;
    }
    public static function display_project($projectID) {
      $projectState = self::get_project_state($projectID);
      $client = self::get_client_state_wrt_project($projectState, $projectID);
      switch ($client) {
        case userState::Client:
          return display::ProjectWithClientConfig;
        case userState::Admin:
          return
            ($projectState === projectState::Public_)
              ? display::ProjectWithProjectConfig
              : display::AdminNotice;
        case userState::Guest:
          return
            ($projectState === projectState::Private_)
              ? display::LoginForm
              : display::ProjectWithProjectConfig;
        case userState::NotLoggedIn:
          switch ($projectState) {
            case projectState::Private_:
              return display::LoginForm;
            case projectState::PrivateWithGuestLogin:
              return display::SplitLoginView;
            case projectState::PrivateWithGuestLoginNoClientsAssigned:
              return display::PasswordInput;
            case projectState::Public_:
              return display::ProjectWithProjectConfig;
          }
        case userState::LoggedInUserWithNoAccess:
          switch ($projectState) {
            case projectState::Private_:
              return display::NoAccess;
            case projectState::PrivateWithGuestLogin:
            case projectState::PrivateWithGuestLoginNoClientsAssigned:
              return display::PasswordInput;
            case projectState::Public_:
              return display::ProjectWithProjectConfig;
          }
      }
    }
    public static function display_portal() {
      if (\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
        return portalDisplay::AdminNotice;
      }
      $c = Client::get_current();
      if (\hacklib_cast_as_boolean(\is_null($c))) {
        return portalDisplay::LoginForm;
      }
      return portalDisplay::Redirect;
    }
    public static function display_client($clientID) {
      if (\hacklib_cast_as_boolean(Utils::is_current_user_admin())) {
        return clientDisplay::AdminNoticeWithClientView;
      }
      $c = Client::get_current();
      if (\hacklib_cast_as_boolean(\is_null($c))) {
        return clientDisplay::LoginForm;
      }
      if ($c[\hacklib_id("ID")] === $clientID) {
        return clientDisplay::ClientView;
      } else {
        return clientDisplay::NoAccess;
      }
    }
    public static function post_password_required($post_id) { // UNSAFE
      $post = get_post($post_id);
      if ((!isset($post->post_password)) ||
          \hacklib_equals($post->post_password, false)) {
        return apply_filters("post_password_required", false, $post);
      }
      if (!\hacklib_cast_as_boolean(
            isset($_COOKIE["wp-postpass_".COOKIEHASH])
          )) {
        return true;
      }
      $hasher = new \PasswordHash(8, true);
      $hash = wp_unslash($_COOKIE["wp-postpass_".COOKIEHASH]);
      $required = !\hacklib_cast_as_boolean(
        $hasher->CheckPassword($post->post_password, $hash)
      );
      return $required;
    }
  }
}
