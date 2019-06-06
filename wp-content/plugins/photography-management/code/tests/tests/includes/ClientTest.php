<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\includes\Client;
use \codeneric\phmm\base\includes\Error;
use \codeneric\phmm\base\includes\ErrorSeverity;
use \Eris\Generator;
final class ClientTest extends Codeneric_UnitTest {
  protected $post;
  public function setUp() {
    parent::setUp();
    self::makeAdministrator();
    $this->post =
      $this->factory
        ->post
        ->create_and_get(
          array(
            "post_type" => $this->config[\hacklib_id("client_post_type")]
          )
        );
  }
  public function tearDown() {
    parent::tearDown();
    unset($this->post);
  }
  private function setUpClient($data) {
    $default = array(
      "project_access" => array(),
      "post_title" => $this->post->post_title,
      "user_login" => $this->post->post_title."_login"
    );
    $default = \codeneric\phmm\validate\client_from_client($default);
    if (\hacklib_cast_as_boolean(is_null($data))) {
      $data = $default;
    } else {
      $data = array_merge($default, $data);
    }
    return Client::save($this->post->ID, $data);
  }
  public function testGetWpUserFromClientIdFuzz() {
    $this->forAll(Generator\int())->then(
      function($number) {
        $this->assertNull(Client::get_wp_user_from_client_id($number));
      }
    );
  }
  public function testGetWpUserFromClientId() {
    $this->assertNull(Client::get_wp_user_from_client_id(-1));
  }
  public function testGetWpUserFromClientId2() {
    $this->assertNull(Client::get_wp_user_from_client_id($this->post->ID));
  }
  public function testGetWpUserFromClientId3() {
    $this->setUpClient(null);
    $user = Client::get_wp_user_from_client_id($this->post->ID);
    $this->assertInstanceOf("WP_User", $user);
  }
  public function testGet() {
    $this->assertSame(
      Client::get($this->post->ID),
      array(
        "ID" => $this->post->ID,
        "wp_user" => null,
        "project_access" => array(),
        "internal_notes" => null,
        "canned_email_history" => array(),
        "plain_pwd" => null
      )
    );
  }
  public function testGet2() {
    $this->forAll(
      Generator\filter(
        function($n) {
          return $n !== $this->post->ID;
        },
        Generator\int()
      )
    )->then(
      function($number) {
        $this->assertNull(Client::get($number));
      }
    );
  }
  public function testGet3() {
    $this->setUpClient(null);
    $user = Client::get_wp_user_from_client_id($this->post->ID);
    $get = Client::get($this->post->ID);
    $this->assertArrayHasKey("ID", $get);
    $this->assertArrayHasKey("wp_user", $get);
    $this->assertArrayHasKey("project_access", $get);
    \HH\invariant(
      !\hacklib_cast_as_boolean(is_null($get)),
      "%s",
      new Error("")
    );
    $this->assertInstanceOf("WP_User", $get[\hacklib_id("wp_user")]);
  }
  public function testSave() {
    $this->assertSame(
      "",
      get_post_meta($this->post->ID, "project_access", true)
    );
    $this->assertSame(get_post_meta($this->post->ID, "wp_user", true), "");
    $this->setUpClient(
      array("plain_pwd" => "qwertz1234", "user_login" => "dietmar")
    );
    $this->assertSame(
      array(),
      get_post_meta($this->post->ID, "project_access", true)
    );
    $user = get_user_by("login", "dietmar");
    \HH\invariant(
      $user instanceof \WP_User,
      "%s",
      new Error("Should be a WP_User object")
    );
    $this->assertSame(
      (int) get_post_meta($this->post->ID, "wp_user", true),
      $user->ID
    );
    $this->assertTrue(
      wp_check_password("qwertz1234", $user->get("user_pass"), $user->ID)
    );
  }
  public function testSave2() {
    $this->setUpClient(array("user_login" => "billgates"));
    $this->setUpClient(array("post_title" => "HANS WURST"));
    $user = Client::get_wp_user_from_client_id($this->post->ID);
    $this->assertSame(
      \hacklib_nullsafe($user)->get("display_name"),
      "HANS WURST"
    );
    $this->assertSame(
      \hacklib_nullsafe($user)->get("user_login"),
      "billgates"
    );
  }
  public function testSave21() {
    $this->setUpClient(array("plain_pwd" => "qwertz1234"));
    $this->setUpClient(array("plain_pwd" => "alex"));
    $user = Client::get_wp_user_from_client_id($this->post->ID);
    $this->assertTrue(
      wp_check_password(
        "alex",
        \hacklib_nullsafe($user)->get("user_pass"),
        \hacklib_nullsafe($user)->ID
      )
    );
  }
  public function testSave22() {
    $this->setUpClient(array("plain_pwd" => "qwertz1234"));
    $this->setUpClient(array("plain_pwd" => "alex"));
    $this->setUpClient(null);
    $user = Client::get_wp_user_from_client_id($this->post->ID);
    $this->assertTrue(
      wp_check_password(
        "alex",
        \hacklib_nullsafe($user)->get("user_pass"),
        \hacklib_nullsafe($user)->ID
      )
    );
  }
  public function testSave3() {
    $this->setUpClient(
      array(
        "project_access" => array(
          array("id" => 9, "active" => true),
          array("id" => 8, "active" => true),
          array("id" => 7, "active" => true)
        )
      )
    );
    $client = Client::get($this->post->ID);
    \HH\invariant(
      !\hacklib_cast_as_boolean(is_null($client)),
      "%s",
      new Error("")
    );
    $this->assertSame(
      array(
        array("id" => 9, "active" => true),
        array("id" => 8, "active" => true),
        array("id" => 7, "active" => true)
      ),
      $client[\hacklib_id("project_access")]
    );
  }
  public function testSave4() {
    $this->setUpClient(
      array(
        "project_access" => array(
          array("id" => 9, "active" => true),
          array(
            "id" => 8,
            "configuration" => $this->getValidProjectConfiguration(),
            "active" => true
          ),
          array("id" => 7, "active" => true)
        )
      )
    );
    $client = Client::get($this->post->ID);
    \HH\invariant(
      !\hacklib_cast_as_boolean(is_null($client)),
      "%s",
      new Error("")
    );
    $this->assertSame(
      array(
        array("id" => 9, "active" => true),
        array(
          "id" => 8,
          "configuration" => $this->getValidProjectConfiguration(),
          "active" => true
        ),
        array("id" => 7, "active" => true)
      ),
      $client[\hacklib_id("project_access")]
    );
  }
  public function testSave5() {
    $this->setUpClient(
      array(
        "project_access" => array(
          array("id" => 9, "active" => true),
          array(
            "id" => 8,
            "active" => true,
            "configuration" => $this->getInvalidProjectConfiguration()
          ),
          array("id" => 7, "active" => true)
        )
      )
    );
  }
  public function testSave6() {
    $this->setUpClient(
      array(
        "project_access" =>
          array(
            array("id" => 9, "active" => true),
            array(
              "id" => 8,
              "configuration" =>
                $this->getValidProjectConfigurationAllFalse(),
              "active" => true
            ),
            array("id" => 7, "active" => true)
          )
      )
    );
    $client = Client::get($this->post->ID);
    \HH\invariant(
      !\hacklib_cast_as_boolean(is_null($client)),
      "%s",
      new Error("")
    );
    $this->assertSame(
      array(
        array("id" => 9, "active" => true),
        array(
          "id" => 8,
          "configuration" => $this->getValidProjectConfigurationAllFalse(),
          "active" => true
        ),
        array("id" => 7, "active" => true)
      ),
      $client[\hacklib_id("project_access")]
    );
    $this->setUpClient(
      array(
        "project_access" =>
          array(
            array("id" => 9, "active" => false),
            array(
              "id" => 8,
              "active" => true,
              "configuration" =>
                $this->getValidProjectConfigurationAllTrue()
            ),
            array("id" => 7, "active" => true)
          )
      )
    );
    $client = Client::get($this->post->ID);
    \HH\invariant(
      !\hacklib_cast_as_boolean(is_null($client)),
      "%s",
      new Error("")
    );
    $this->assertSame(
      array(
        array("id" => 9, "active" => false),
        array(
          "id" => 8,
          "active" => true,
          "configuration" => $this->getValidProjectConfigurationAllTrue()
        ),
        array("id" => 7, "active" => true)
      ),
      $client[\hacklib_id("project_access")]
    );
  }
  public function testGetProjects() {
    $projectIDs =
      $this->factory
        ->post
        ->create_many(
          5,
          array(
            "post_type" => $this->config[\hacklib_id("project_post_type")]
          )
        );
    $project_access = array_map(
      function($id) {
        return array("id" => $id);
      },
      $projectIDs
    );
    $this->setUpClient(array("project_access" => $project_access));
    $projects = Client::get_project_wp_posts($this->post->ID);
    $this->assertCount(5, $projects);
    foreach ($projects as $project) {
      $this->assertInstanceOf("WP_Post", $project);
      $this->assertTrue(in_array($project->ID, $projectIDs));
    }
  }
  public function testGetProjects2() {
    $this->setUpClient(null);
    $projects = Client::get_project_wp_posts($this->post->ID);
    $this->assertSame(array(), $projects);
  }
  public function testGetProjects3() {
    $this->assertSame(array(), Client::get_project_wp_posts(-1));
  }
  public function testGetAllIds() {
    wp_delete_post($this->post->ID, true);
    $this->assertSame(array(), Client::get_all_ids());
  }
  public function testGetAllIds2() {
    $this->assertSame(array($this->post->ID), Client::get_all_ids());
  }
  public function testHasAccessToProject() {
    $this->assertFalse(Client::has_access_to_project($this->post->ID, 42));
    $this->assertFalse(Client::has_access_to_project($this->post->ID, -1));
  }
  public function testHasAccessToProject2() {
    $this->setUpClient(
      array(
        "project_access" => array(
          array("id" => 9),
          array("id" => 8),
          array("id" => 7)
        )
      )
    );
    $this->assertFalse(Client::has_access_to_project($this->post->ID, 42));
    $this->assertTrue(Client::has_access_to_project($this->post->ID, 7));
    $this->assertTrue(Client::has_access_to_project($this->post->ID, 8));
    $this->assertTrue(Client::has_access_to_project($this->post->ID, 9));
  }
  public function testDereferenceProject() {
    $this->assertSame(array(), Client::get_project_ids($this->post->ID));
    Client::dereference_project(42, null);
    $this->assertSame(array(), Client::get_project_ids($this->post->ID));
  }
  public function testDereferenceProject2() {
    $this->setUpClient(
      array(
        "project_access" => array(
          array("id" => 9),
          array("id" => 8),
          array("id" => 7)
        )
      )
    );
    Client::dereference_project(8, null);
    $this->assertSame(array(9, 7), Client::get_project_ids($this->post->ID));
    Client::dereference_project(7, null);
    $this->assertSame(array(9), Client::get_project_ids($this->post->ID));
    Client::dereference_project(9, null);
    $this->assertSame(array(), Client::get_project_ids($this->post->ID));
  }
  public function testGetProjectIds() {
    $this->assertSame(array(), Client::get_project_ids($this->post->ID));
    $this->setUpClient(
      array(
        "project_access" => array(
          array("id" => 9),
          array("id" => 8),
          array("id" => 7)
        )
      )
    );
    $this->assertSame(
      array(9, 8, 7),
      Client::get_project_ids($this->post->ID)
    );
  }
}
