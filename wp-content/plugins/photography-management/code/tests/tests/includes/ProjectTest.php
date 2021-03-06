<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\base\includes\Project;
final class ProjectTest extends Codeneric_UnitTest {
  public function setUp() {
    parent::setUp();
    self::makeAdministrator();
  }
  private function getExpectedDefaultConfiguration() {
    return array(
      "commentable" => false,
      "disableRightClick" => false,
      "downloadable" => true,
      "downloadable_favs" => false,
      "downloadable_single" => false,
      "favoritable" => true,
      "showCaptions" => false,
      "showFilenames" => false,
      "watermark" => null
    );
  }
  private function createAndGetTestProject() {
    return
      $this->factory
        ->post
        ->create_and_get(
          array(
            "post_type" => $this->config[\hacklib_id("project_post_type")]
          )
        );
  }
  public function testGetAllIds() {
    $this->assertEmpty(Project::get_all_ids());
  }
  public function testGetAllIds2() {
    $this->createAndGetTestProject();
    $this->assertCount(1, Project::get_all_ids());
  }
  public function testGetAllIds3() {
    $this->factory
      ->post
      ->create_many(
        20,
        array(
          "post_type" => $this->config[\hacklib_id("project_post_type")]
        )
      );
    $this->assertCount(20, Project::get_all_ids());
  }
  public function testGetDefaultConfiguration() {
    $default = Project::getDefaultProjectConfiguration();
    $this->assertSame(
      $this->getExpectedDefaultConfiguration(),
      $default,
      "the default project configuration should look as expected"
    );
  }
  public function testGetConfiguration() {
    $this->predictError();
    Project::get_configuration(42);
  }
  public function testGetConfiguration2() {
    $post = $this->factory->post->create_and_get();
    $this->predictError();
    Project::get_configuration($post->ID);
  }
  public function testGetConfiguration3() {
    $project = $this->createAndGetTestProject();
    $this->assertSame(
      $this->getExpectedDefaultConfiguration(),
      Project::get_configuration($project->ID),
      "new project should have default configuration"
    );
  }
  public function testSaveProject() {
    $project = $this->createAndGetTestProject();
    $this->markTestIncomplete(
      "issue with the json parser in gallery (string instead of empty array, and configuration (no errors even when not present)"
    );
  }
}
