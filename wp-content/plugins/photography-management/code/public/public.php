<?php
namespace codeneric\phmm\base\frontend {
  require_once ($GLOBALS["HACKLIB_ROOT"]);
  use \codeneric\phmm\base\includes\Project;
  use \codeneric\phmm\base\includes\Client;
  use \codeneric\phmm\base\includes\Image;
  use \codeneric\phmm\base\includes\Error;
  use \codeneric\phmm\base\includes\Permission;
  use \codeneric\phmm\base\admin\Settings;
  use \codeneric\phmm\base\globals\Superglobals;
  use \codeneric\phmm\base\admin\FrontendHandler;
  use \codeneric\phmm\Utils;
  use \codeneric\phmm\Configuration;
  use \codeneric\phmm\enums\ProjectDisplay;
  use \codeneric\phmm\enums\PortalDisplay;
  use \codeneric\phmm\enums\ClientDisplay;
  use \codeneric\phmm\enums\AdvancedBoolSettings;
  final class Shortcodes {
    private function __construct() {}
    private static
      $hacklib_values = array(
        "PORTAL" => "cc_phmm_portal",
        "GALLERY" => "phmm-project",
        "CLIENT" => "phmm-client"
      );
    use \HH\HACKLIB_ENUM_LIKE;
    const PORTAL = "cc_phmm_portal";
    const GALLERY = "phmm-project";
    const CLIENT = "phmm-client";
  }
  class Main {
    public static function enqueue_styles() {
      if (\hacklib_cast_as_boolean(self::is_our_business())) {
        \do_action("codeneric/phmm/custom_css");
      }
      if (\hacklib_cast_as_boolean(self::is_project_page())) {
        \wp_enqueue_style(
          "codeneric-phmm-css-public",
          Configuration::get()[\hacklib_id("assets")][\hacklib_id("css")][\hacklib_id(
            "public"
          )][\hacklib_id("projects")],
          array(),
          null,
          "all"
        );
      }
    }
    private static function enqueue_filelist(
      $list,
      $globals,
      $dependencies = array()
    ) {
      $configuration = Configuration::get();
      foreach ($list as $index => $file) {
        $handle = $file;
        $isLast = \count($list) === ($index + 1);
        \wp_register_script($handle, $file, $dependencies, null, true);
        if (\hacklib_cast_as_boolean($isLast)) {
          $url =
            \plugins_url("/", $configuration[\hacklib_id("manifest_path")]);
          \wp_localize_script($handle, "codeneric_phmm_plugins_dir", $url);
          \wp_localize_script(
            $handle,
            "codeneric_phmm_public_general_globals",
            \json_encode(self::get_general_public_frontend_globals())
          );
          foreach ($globals as $name => $data) {
            \wp_localize_script($handle, $name, $data);
          }
        }
        \wp_enqueue_script($handle);
        \array_push($dependencies, $handle);
      }
    }
    public static function enqueue_scripts() {
      if (!\hacklib_cast_as_boolean(self::is_our_business())) {
        return;
      }
      $configuration = Configuration::get();
      if (\hacklib_cast_as_boolean(self::is_project_page())) {
        $id = self::get_relevant_id(Shortcodes::GALLERY);
        if (\get_post_type($id) !==
            $configuration[\hacklib_id("project_post_type")]) {
          return;
        }
      }
      if (\hacklib_cast_as_boolean(self::is_client_page())) {
        $id = self::get_relevant_id(Shortcodes::CLIENT);
        if (\get_post_type($id) !==
            $configuration[\hacklib_id("client_post_type")]) {
          return;
        }
      }
      $scripthandle = "";
      if (\hacklib_cast_as_boolean(self::is_project_page())) {
        $id = self::get_relevant_id(Shortcodes::GALLERY);
        $display = Permission::display_project($id);
        if (($display !== ProjectDisplay::ProjectWithClientConfig) &&
            ($display !== ProjectDisplay::ProjectWithProjectConfig) &&
            ($display !== ProjectDisplay::AdminNotice)) {
          return;
        }
      }
      if (\hacklib_cast_as_boolean(self::is_project_page())) {
        $id = self::get_relevant_id(Shortcodes::GALLERY);
        $display = Permission::display_project($id);
        $scriptsrc =
          $configuration[\hacklib_id("assets")][\hacklib_id("js")][\hacklib_id(
            "public"
          )][\hacklib_id("project")];
        $scripthandle =
          $configuration[\hacklib_id("plugin_name")]."-public-project";
        $projectGlobals = null;
        if ($display === ProjectDisplay::ProjectWithClientConfig) {
          $client = Client::get_current();
          $clientID = null;
          if (\hacklib_cast_as_boolean($client)) {
            $clientID = $client[\hacklib_id("ID")];
          }
          $projectGlobals = Project::get_project_for_frontend($id, $clientID);
        }
        if ($display === ProjectDisplay::ProjectWithProjectConfig) {
          $projectGlobals = Project::get_project_for_frontend($id, null);
        }
        if ($display === ProjectDisplay::AdminNotice) {
          $projectGlobals = Project::get_project_for_frontend($id, null);
        }
        if (!\hacklib_cast_as_boolean(\is_null($projectGlobals))) {
          self::enqueue_filelist(
            $configuration[\hacklib_id("assets")][\hacklib_id("js")][\hacklib_id(
              "public"
            )][\hacklib_id("project")],
            array(
              "codeneric_phmm_public_project_globals" => \json_encode(
                $projectGlobals
              )
            )
          );
        }
      }
      if (\hacklib_cast_as_boolean(self::is_client_page())) {
        self::enqueue_filelist(
          $configuration[\hacklib_id("assets")][\hacklib_id("js")][\hacklib_id(
            "public"
          )][\hacklib_id("client")],
          array(
            "codeneric_phmm_public_client_globals" => \json_encode(
              self::get_client_public_frontend_globals()
            )
          )
        );
      }
    }
    public static function get_client_public_frontend_globals() {
      $clientID = self::get_relevant_id(Shortcodes::CLIENT);
      $projects = Client::get_project_wp_posts($clientID, true);
      $transformed = \array_map(
        function($project) {
          $permalink = \get_permalink($project->ID);
          \HH\invariant(
            is_string($permalink),
            "%s",
            new Error("Failed to get permalink from existing project post")
          );
          return array(
            "id" => $project->ID,
            "permalink" => $permalink,
            "title" => Project::get_title_with_id_default($project->ID),
            "thumbnail" => Project::get_thumbnail($project->ID)
          );
        },
        $projects
      );
      return array("projects" => $transformed);
    }
    private static function get_public_global_options() {
      $settings = Settings::getCurrentSettings();
      return array(
        "accent_color" => $settings[\hacklib_id("accent_color")],
        "enable_slider" => $settings[\hacklib_id("enable_slider")],
        "slider_theme" => $settings[\hacklib_id("slider_theme")]
      );
    }
    public static function get_general_public_frontend_globals() {
      $backUrl = null;
      $logoutUrl = null;
      if (\hacklib_cast_as_boolean(self::is_project_page()) &&
          (Permission::display_project(
             self::get_relevant_id(Shortcodes::GALLERY)
           ) ===
           ProjectDisplay::ProjectWithClientConfig)) {
        $clientID =
          Client::get_client_id_from_wp_user_id(Utils::get_current_user_id());
        if (\hacklib_cast_as_boolean(is_int($clientID))) {
          $clientUrl = \get_permalink($clientID);
          if (\hacklib_cast_as_boolean(is_string($clientUrl))) {
            $backUrl = $clientUrl;
          }
          $logoutUrl = self::posts_logout_url();
        }
      }
      return array(
        "author_id" => Utils::get_current_user_id(),
        "ajax_url" => \admin_url("admin-ajax.php"),
        "base_url" => \get_site_url(),
        "locale" => \get_locale(),
        "logout_url" => $logoutUrl,
        "back_url" => $backUrl,
        "options" => self::get_public_global_options()
      );
    }
    private static function get_current_post_type() {
      $post = /* UNSAFE_EXPR */ $GLOBALS[\hacklib_id("post")];
      $type = \get_post_type($post);
      if (\hacklib_cast_as_boolean(is_bool($type))) {
        return null;
      } else {
        return (string) $type;
      }
    }
    public static function is_project_page() {
      $post = \get_post();
      if (!\hacklib_cast_as_boolean(\is_null($post))) {
        if (\hacklib_cast_as_boolean(
              \has_shortcode($post->post_content, Shortcodes::GALLERY)
            )) {
          return true;
        }
      }
      return
        \hacklib_cast_as_boolean(\is_single()) &&
        (self::get_current_post_type() ===
         Configuration::get()[\hacklib_id("project_post_type")]);
    }
    public static function is_client_page() {
      $post = \get_post();
      if (!\hacklib_cast_as_boolean(\is_null($post))) {
        if (\hacklib_cast_as_boolean(
              \has_shortcode($post->post_content, Shortcodes::CLIENT)
            )) {
          return true;
        }
      }
      return
        \hacklib_cast_as_boolean(\is_single()) &&
        (self::get_current_post_type() ===
         Configuration::get()[\hacklib_id("client_post_type")]);
    }
    public static function is_portal_page() {
      $post = \get_post();
      if (\hacklib_cast_as_boolean(\is_null($post))) {
        return false;
      }
      if (\hacklib_cast_as_boolean(
            \has_shortcode($post->post_content, Shortcodes::PORTAL)
          )) {
        return true;
      }
      $currentPortalPage =
        Settings::getCurrentSettings()[\hacklib_id("portal_page_id")];
      if (\hacklib_cast_as_boolean(\is_null($currentPortalPage))) {
        return false;
      }
      return $currentPortalPage === $post->ID;
    }
    private static function is_our_business() {
      return
        \hacklib_cast_as_boolean(self::is_client_page()) ||
        \hacklib_cast_as_boolean(self::is_project_page());
    }
    private static function has_current_post_shortcode($shortcode) {
      $post = \get_post();
      if (\hacklib_cast_as_boolean(\is_null($post))) {
        return false;
      }
      return \has_shortcode($post->post_content, $shortcode);
    }
    private static function attach_shortcode($content, $shortcode) {
      if (\hacklib_cast_as_boolean(\has_shortcode($content, $shortcode))) {
        return $content;
      }
      $content .= "[".$shortcode."]";
      return $content;
    }
    private static function replace_shortcode_or_append(
      $content,
      $replacement,
      $shortcode
    ) {
      if (\hacklib_cast_as_boolean(\has_shortcode($content, $shortcode))) {
        $d = \preg_replace(
          "/\\[\\s*".$shortcode."\\s*(?:id=[\"'](.*)[\"'])?\\s*\\]/",
          $replacement,
          $content
        );
        return $d;
      }
      return $content.$replacement;
    }
    private static function get_relevant_id($shortcode) {
      $post = \get_post();
      \HH\invariant(
        !\hacklib_cast_as_boolean(\is_null($post)),
        "%s",
        new Error("Post is not set")
      );
      if (!\hacklib_cast_as_boolean(
            \has_shortcode($post->post_content, $shortcode)
          )) {
        return $post->ID;
      }
      $content = $post->post_content;
      $matches = array();
      \preg_match(
        "/\\[".$shortcode."\\s*id=\"(.*)\"\\s*\\]/",
        $content,
        $matches
      );
      if (\count($matches) === 2) {
        return (int) $matches[1];
      }
      return -1;
    }
    private static function get_the_id() {
      $id = \get_the_ID();
      \HH\invariant(is_int($id), "%s", new Error("Post is not set"));
      return $id;
    }
    public static function the_content_hook($content) {
      if (!\hacklib_cast_as_boolean(is_int(\get_the_ID()))) {
        return $content;
      }
      $noAccessHTML =
        "<h1>".\__("No Access", "photography-management")."<h1>";
      $adminNoticeHTML =
        "<style>@keyframes slideInFromBottom {\n  0% {\n    transform: translateY(100%);\n  }\n  100% {\n    transform: translateX(0);\n  }\n      }</style>".
        "<div id='cc-phmm-pfe-admin-notice' style=' transform: translateY(100%); animation:slideInFromBottom 1s ease-in-out forwards;  animation-delay: 2s; background:#ecf0f1; padding: 10px 0; text-align:center;border:1px solid rgba(0,0,0,0.1); left:0; position:fixed; bottom:0; width:100%; z-index:100000;'><h3 style='margin:0;margin-bottom:10px;'>".
        "<strong>PHMM: </strong>".
        \__(
          "You view the project as an admin. To verify the project password protection, open this page in an incognito window.",
          "photography-management"
        ).
        "</h3><span style='background:#7f8c8d; margin: 5px; padding:5px; cursor:pointer; color: #ecf0f1;' id='cc-phmm-pfe-admin-notice-close'>Ok, close</span></div>\n      <script>\n        document.getElementById('cc-phmm-pfe-admin-notice-close').onclick = function() {\n          document.getElementById('cc-phmm-pfe-admin-notice').remove();\n        }\n      </script>";
      $loginForm = \wp_login_form(array("echo" => false));
      $pwdForm = \get_the_password_form();
      if (\hacklib_cast_as_boolean(self::is_client_page())) {
        $postID = self::get_relevant_id(Shortcodes::CLIENT);
        $cv = Permission::display_client($postID);
        switch ($cv) {
          case ClientDisplay::ClientView:
            return self::attach_shortcode($content, Shortcodes::CLIENT);
            break;
          case ClientDisplay::NoAccess:
            return self::replace_shortcode_or_append(
              $content,
              $noAccessHTML,
              Shortcodes::CLIENT
            );
            break;
          case ClientDisplay::AdminNoticeWithClientView:
            return self::attach_shortcode($content, Shortcodes::CLIENT);
            break;
          case ClientDisplay::LoginForm:
            return self::replace_shortcode_or_append(
              $content,
              $loginForm,
              Shortcodes::CLIENT
            );
            break;
        }
      }
      if (\hacklib_cast_as_boolean(self::is_project_page())) {
        $postID = self::get_relevant_id(Shortcodes::GALLERY);
        switch (Permission::display_project($postID)) {
          case ProjectDisplay::LoginForm:
            return self::replace_shortcode_or_append(
              $content,
              $loginForm,
              Shortcodes::GALLERY
            );
          case ProjectDisplay::NoAccess:
            return self::replace_shortcode_or_append(
              $content,
              $noAccessHTML,
              Shortcodes::GALLERY
            );
          case ProjectDisplay::PasswordInput:
            return self::replace_shortcode_or_append(
              $content,
              $pwdForm,
              Shortcodes::GALLERY
            );
          case ProjectDisplay::SplitLoginView:
            $wrapStyle =
              "border:1px solid rgba(0,0,0,0.15); padding: 1em;margin: 0.5em;";
            $html =
              "<div style='".
              $wrapStyle.
              "'>".
              "<h3>".
              \__("Guest login", "photography-management").
              "</h3>".
              $pwdForm.
              "</div>".
              "<div style='".
              $wrapStyle.
              "'>".
              "<h3>".
              \__("Client login", "photography-management").
              "</h3>".
              $loginForm.
              "</div>";
            return self::replace_shortcode_or_append(
              $content,
              $html,
              Shortcodes::GALLERY
            );
          case ProjectDisplay::AdminNotice:
            return self::attach_shortcode(
              $adminNoticeHTML.$content,
              Shortcodes::GALLERY
            );
          case ProjectDisplay::ProjectWithClientConfig:
          case ProjectDisplay::ProjectWithProjectConfig:
            return self::attach_shortcode($content, Shortcodes::GALLERY);
        }
      }
      if (\hacklib_cast_as_boolean(self::is_portal_page())) {
        switch (Permission::display_portal()) {
          case PortalDisplay::LoginForm:
            return self::attach_shortcode($content, Shortcodes::PORTAL);
          case PortalDisplay::AdminNotice:
            $replacement =
              "<h1>".
              \__(
                "You are logged in as admin. Logout to see the client login form and login as client to see the redirection",
                "photography-management"
              ).
              "</h1>";
            return self::replace_shortcode_or_append(
              $content,
              $replacement,
              Shortcodes::PORTAL
            );
          case PortalDisplay::Redirect:
            return $content;
        }
      }
      return $content;
    }
    private static function _redirect_client_to_single_project_if_necessary(
      $client_id
    ) {
      if (\hacklib_cast_as_boolean(
            Utils::get_advanced_bool_setting(
              AdvancedBoolSettings::PHMM_REDIRECT_CLIENT_TO_SINGLE_PROJECT
            )
          )) {
        $project_ids = Client::get_project_ids($client_id);
        if (\count($project_ids) === 1) {
          $content = (string) \get_post_field("post_content", $client_id);
          if (\strlen($content) === 0) {
            $link = \get_permalink($project_ids[0]);
            if (!\hacklib_cast_as_boolean(is_string($link))) {
              return;
            }
            \wp_redirect($link);
            exit();
          }
        }
      }
    }
    public static function redirect_from_portal_page() {
      if (!\hacklib_cast_as_boolean(self::is_portal_page())) {
        return;
      }
      $client_id = Client::get_current_id();
      if (\hacklib_cast_as_boolean(\is_null($client_id))) {
        return;
      }
      self::_redirect_client_to_single_project_if_necessary($client_id);
      $link = \get_permalink($client_id);
      if (!\hacklib_cast_as_boolean(is_string($link))) {
        return;
      }
      \wp_redirect($link);
      exit();
    }
    public static function redirect_from_client_page() {
      if (!\hacklib_cast_as_boolean(self::is_client_page())) {
        return;
      }
      $client_id = Client::get_current_id();
      if (\hacklib_cast_as_boolean(\is_null($client_id))) {
        return;
      }
      self::_redirect_client_to_single_project_if_necessary($client_id);
    }
    public static function remove_protected_string($default) {
      if (\hacklib_cast_as_boolean(self::is_client_page()) ||
          \hacklib_cast_as_boolean(self::is_project_page())) {
        return "%s";
      }
      return $default;
    }
    public static function apply_template($default) {
      $should_apply_template = self::is_project_page();
      if (\hacklib_cast_as_boolean(
            Utils::get_advanced_bool_setting(
              AdvancedBoolSettings::PHMM_APPLY_TEMPLATE_TO_CLIENT_PAGE
            )
          )) {
        $should_apply_template =
          \hacklib_cast_as_boolean(self::is_client_page()) ||
          \hacklib_cast_as_boolean(self::is_project_page());
      } else {
        $should_apply_template = self::is_project_page();
      }
      if (\hacklib_cast_as_boolean($should_apply_template)) {
        $settings = Settings::getCurrentSettings();
        $template = $settings[\hacklib_id("page_template")];
        if (\hacklib_equals($template, "phmm-theme-default") ||
            ($template === "")) {
          return $default;
        }
        if (\hacklib_equals($template, "phmm-legacy")) {
          $legacy = \dirname(__FILE__)."/single-client.plain.php";
          if (\hacklib_cast_as_boolean(\file_exists($legacy))) {
            return $legacy;
          } else {
            return $default;
          }
        }
        $theme = \get_template_directory();
        $template_file = $theme."/".$template;
        if (\hacklib_cast_as_boolean(\file_exists($template_file))) {
          return $template_file;
        }
      }
      return $default;
    }
    public static function client_shortcode() {
      $scripthandle =
        Configuration::get()[\hacklib_id("plugin_name")]."-public-client";
      return
        "<div id=\"cc_phmm_public_client\" style=\"position:relative\" ></div>";
    }
    public static function gallery_shortcode($args) {
      $scripthandle =
        Configuration::get()[\hacklib_id("plugin_name")]."-public-project";
      return
        "<div  id=\"cc_phmm_public_project\" style=\"position:relative\" ></div>";
    }
    public static function portal_shortcode() {
      return \wp_login_form(array("echo" => false));
    }
    public static function posts_logout_url() {
      return \wp_nonce_url(
        \add_query_arg(
          array("action" => "codeneric_phmm_posts_logout"),
          \site_url("wp-login.php", "login")
        ),
        "codeneric_phmm_posts_logout"
      );
    }
    public static function posts_logout() {
      $request = \codeneric\phmm\base\globals\Superglobals::Request();
      \HH\invariant(
        is_array($request),
        "%s",
        new Error("_request is not an array.")
      );
      if (\hacklib_cast_as_boolean(\array_key_exists("action", $request)) &&
          \hacklib_equals(
            $request[\hacklib_id("action")],
            Configuration::get()[\hacklib_id("phmm_posts_logout")]
          )) {
        $cookiehash = /* UNSAFE_EXPR */ COOKIEHASH;
        $cookiepath = /* UNSAFE_EXPR */ COOKIEPATH;
        \setcookie(
          Configuration::get()[\hacklib_id("cookie_wp_postpass")].
          $cookiehash,
          " ",
          \time() - 31536000,
          $cookiepath
        );
        \wp_logout();
        \wp_redirect(\wp_get_referer());
        die();
      }
    }
    public static function filter_post_password_required($actual, $post) {
      if (!\hacklib_cast_as_boolean(self::is_project_page())) {
        return $actual;
      }
      return false;
    }
    public static function the_password_form_hook($output) {
      $postID = self::get_the_id();
      if (\hacklib_cast_as_boolean(self::is_project_page())) {
        return "";
      }
      return $output;
    }
    public static function photon_exceptions($val, $src, $tag) {
      if (\strpos($src, "uploads/photography_management") !== false) {
        return true;
      }
      return $val;
    }
    public static function photon_exceptions_2($skip, $b) { // UNSAFE
      if (\hacklib_cast_as_boolean(isset($b)) &&
          \hacklib_cast_as_boolean(isset($b[\hacklib_id("attachment_id")]))) {
        $fullsize_path = get_attached_file($b[\hacklib_id("attachment_id")]);
        return strpos($fullsize_path, "photography_management") !== false;
      }
      return $skip;
    }
    public static function provide_secured_image($args) {
      $get = Superglobals::Get();
      $codeneric_load_image =
        \hacklib_cast_as_boolean(
          \array_key_exists("codeneric_load_image", $get)
        ) ? $get[\hacklib_id("codeneric_load_image")] : 0;
      if (\hacklib_equals(\intval($codeneric_load_image), 1)) {
        \codeneric_send_image_if_allowed();
      }
    }
    public static function login_failed() {
      $referrer = \wp_get_referer();
      if (\hacklib_cast_as_boolean($referrer) &&
          (!\hacklib_cast_as_boolean(\strstr($referrer, "wp-login"))) &&
          (!\hacklib_cast_as_boolean(\strstr($referrer, "wp-admin")))) {
        \wp_redirect($referrer);
        exit();
      }
    }
  }
}
