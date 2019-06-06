<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
require_once (__DIR__."/../../../../admin/dbupdater.php");
final class DBUpdaterTest extends Codeneric_UnitTest {
  public static function setUpBeforeClass() {
    parent::setUpBeforeClass();
    $path = dirname(__FILE__);
    exec(
      "cd ".
      $path.
      " && wp plugin install wordpress-importer --activate --allow-root"
    );
    echo
      (exec(
         "cd ".
         $path.
         " && wp import ".
         $path.
         "/clients-3.6.5.xml --authors=skip --allow-root"
       ))
    ;
  }
  public function testDecoupleProjects() {
    $fc = new \codeneric\phmm\FunctionContainer();
    $fc->update_to_4_0_0();
    $this->assertSame(get_option("decoupled_projects"), "done");
  }
}
