<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\admin\Main as Admin;
final class AdminTest extends WP_UnitTestCase {
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
  public function setUp() {
    parent::setUp();
    $this->config = Codeneric_UnitTest_Helper::makeConfig();
    $this->schema = new \codeneric\phmm\validate\Handler();
    $proph = $this->prophesize(\codeneric\phmm\base\includes\Client::class);
    $this->client = $proph->reveal();
    $this->clientMock = $proph;
    $proph = $this->prophesize(\codeneric\phmm\base\includes\Project::class);
    $this->project = $proph->reveal();
    $this->projectMock = $proph;
    $this->admin = new \codeneric\phmm\base\admin\Main();
  }
  public function testPostTypesExist() {
    $this->assertTrue(
      post_type_exists($this->config[\hacklib_id("client_post_type")])
    );
    $this->assertTrue(
      post_type_exists($this->config[\hacklib_id("project_post_type")])
    );
  }
  public function testPostTypesAreSetupCorrectly() {
    $clientConfiguration =
      get_post_type_object($this->config[\hacklib_id("client_post_type")]);
    $projectConfiguration =
      get_post_type_object($this->config[\hacklib_id("project_post_type")]);
    $this->assertFalse(is_null($clientConfiguration));
    $this->assertFalse(is_null($projectConfiguration));
  }
  public function testSaveMetaBoxData() { /* UNSAFE_EXPR */
    $this->assertFalse(Admin::save_meta_box_data(null, null, false));
  }
  public function testSaveMetaBoxData2() {
    $post = $this->factory->post->create_and_get();
    $this->assertFalse(Admin::save_meta_box_data($post->ID, $post, true));
  }
  public function testSaveMetaBoxData3() {
    $post = $this->factory->post->create_and_get();
    $this->assertFalse(Admin::save_meta_box_data($post->ID, $post, true));
  }
  public function testReferenceCleanupBeforeProjectDeletion() {
    $this->markTestIncomplete();
  }
}
