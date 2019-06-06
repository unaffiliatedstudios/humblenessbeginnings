<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\includes\ErrorSeverity;
require_once (__DIR__."/helper.php");
require_once (__DIR__."/../admin/admin.php");
require_once (__DIR__."/../admin/settings.php");
require_once (__DIR__."/../admin/frontendHandler.php");
require_once (__DIR__."/../admin/ajax/endpoints.php");
require_once (__DIR__."/../includes/project.php");
require_once (__DIR__."/../includes/client.php");
require_once (__DIR__."/../includes/error.php");
require_once (__DIR__."/../includes/labels.php");
require_once (__DIR__."/../types.php");
require_once (__DIR__."/../includes/superglobals.php");
require_once (__DIR__."/../protect_images/security-logic.php");
class Codeneric_UnitTest extends WP_UnitTestCase {
  use \Eris\TestTrait;
  protected $config;
  protected $schema;
  protected $settings;
  protected $frontendHandler;
  protected $project;
  protected $admin;
  protected $client;
  protected $ajaxEndpoints;
  protected $labels;
  protected $phmm;
  public static function hacklib_initialize_statics_TestTrait() {}
  public function setUp() {
    parent::setUp();
    $this->config = Codeneric_UnitTest_Helper::makeConfig();
    $this->schema = new \codeneric\phmm\validate\Handler();
    $this->project = new \codeneric\phmm\base\includes\Project();
    $this->client = new \codeneric\phmm\base\includes\Client();
    $this->labels = new \codeneric\phmm\base\includes\Labels();
    $this->admin = new \codeneric\phmm\base\admin\Main();
    $this->ajaxEndpoints = new \codeneric\phmm\base\admin\ajax\Endpoints();
  }
  public function tearDown() { /* UNSAFE_EXPR */
    unset($this->config); /* UNSAFE_EXPR */
    unset($this->schema); /* UNSAFE_EXPR */
    unset($this->client); /* UNSAFE_EXPR */
    unset($this->proect); /* UNSAFE_EXPR */
    unset($this->labels);
    parent::tearDown();
  }
  public function predictError($msgContains = null) {
    $this->setExpectedException("\\WPDieException", $msgContains);
  }
  public static function _setRole($role) {
    $post = /* UNSAFE_EXPR */ $_POST;
    $user_id = self::factory()->user->create(array("role" => $role));
    wp_set_current_user($user_id);
    $_POST = array_merge(/* UNSAFE_EXPR */ $_POST, $post);
  }
  public static function makeAdministrator() {
    self::_setRole("administrator");
  }
  public function getValidProjectConfiguration() {
    return array(
      "commentable" => false,
      "disableRightClick" => false,
      "downloadable" => true,
      "downloadable_favs" => false,
      "downloadable_single" => false,
      "favoritable" => true,
      "showCaptions" => false,
      "showFilenames" => false,
      "watermark" => false
    );
  }
  public function getInvalidProjectConfiguration() {
    return array(
      "commentable" => false,
      "disableRightClick" => false,
      "downloadable" => true,
      "downloadable_favs" => "not a bool",
      "favoritable" => true,
      "showCaptions" => false,
      "showFilenames" => false
    );
  }
  public function getValidProjectConfigurationAllTrue() {
    return array(
      "commentable" => true,
      "disableRightClick" => true,
      "downloadable" => true,
      "downloadable_favs" => true,
      "favoritable" => true,
      "showCaptions" => true,
      "showFilenames" => true,
      "downloadable_single" => false,
      "watermark" => false
    );
  }
  public function getValidProjectConfigurationAllFalse() {
    return array(
      "commentable" => false,
      "disableRightClick" => false,
      "downloadable" => false,
      "downloadable_favs" => false,
      "downloadable_single" => false,
      "watermark" => false,
      "favoritable" => false,
      "showCaptions" => false,
      "showFilenames" => false
    );
  }
  public static function hacklib_initialize_statics() {
    self::hacklib_initialize_statics_TestTrait();
  }
}
Codeneric_UnitTest::hacklib_initialize_statics();
