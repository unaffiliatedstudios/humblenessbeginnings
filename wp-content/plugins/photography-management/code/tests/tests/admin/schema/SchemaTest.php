<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
use \codeneric\phmm\validate\Handler as Schema;
final class SchemaTest extends Codeneric_UnitTest {
  private function checkEmail($data) {
    return \codeneric\phmm\validate\check_email($data);
  }
  private function checkUsername($data) {
    return \codeneric\phmm\validate\check_username($data);
  }
  private function iterateCheck($datasets, $schema) {
    foreach ($datasets as $entry) {
      list($data, $v) =
        Schema::validate($entry[\hacklib_id("data")], $schema);
      $this->assertTrue(
        $entry[\hacklib_id("assertion")] === $v->isValid(),
        \hacklib_cast_as_boolean(array_key_exists("info", $entry))
          ? $entry[\hacklib_id("info")]
          : json_encode($v->getErrors())
      );
    }
  }
  public function testValidateCheckEmail() {
    $set = array(
      array("assertion" => false, "data" => array()),
      array("assertion" => false, "data" => array("bull" => "shit")),
      array("assertion" => false, "data" => array("email" => 123)),
      array("assertion" => false, "data" => array("client_id" => 123)),
      array(
        "assertion" => false,
        "data" => array(
          "additional" => "prop",
          "email" => "hans",
          "client_id" => "123"
        )
      ),
      array(
        "assertion" => true,
        "data" => array("email" => "hans", "client_id" => "123")
      ),
      array(
        "assertion" => true,
        "data" => array("email" => "hans", "client_id" => 123)
      ),
      array(
        "assertion" => false,
        "data" => array("email" => 13456789, "client_id" => 123)
      ),
      array(
        "assertion" => false,
        "data" => array("email" => "", "client_id" => 123)
      )
    );
    $this->iterateCheck(
      $set,
      codeneric\phmm\base\admin\schema\Schemas::check_email
    );
  }
  public function testValidateCheckEmail2() {
    $data = array("email" => "hans", "client_id" => "123");
    list($data, $v) = $this->checkEmail($data);
    $this->assertSame($data, array("email" => "hans", "client_id" => 123));
  }
  public function testValidateCheckEmail3() {
    $data = array("email" => "hans", "client_id" => 123);
    list($data, $v) = $this->checkEmail($data);
    $this->assertSame($data, array("email" => "hans", "client_id" => 123));
  }
  public function testValidateCheckEmail4() {
    $data = array("email" => "hans");
    list($data, $v) = $this->checkEmail($data);
    $this->assertSame($data, array("email" => "hans", "client_id" => -1));
  }
  public function testValidateCheckUsername() {
    $set = array(
      array("assertion" => false, "data" => array()),
      array("assertion" => false, "data" => array("bull" => "shit")),
      array("assertion" => false, "data" => array("username" => 123)),
      array(
        "assertion" => false,
        "data" => array("additional" => "prop", "username" => "valid")
      ),
      array("assertion" => false, "data" => array("username" => "")),
      array("assertion" => true, "data" => array("username" => "h")),
      array(
        "assertion" => true,
        "data" => array("username" => "Bill Gates")
      )
    );
    $this->iterateCheck(
      $set,
      codeneric\phmm\base\admin\schema\Schemas::check_username
    );
  }
  public function testValidateFetchImages() {
    $set = array(
      array("assertion" => false, "data" => array()),
      array("assertion" => false, "data" => array("bull" => "shit")),
      array("assertion" => false, "data" => array("IDs" => 123)),
      array("assertion" => false, "data" => array("IDs" => "123")),
      array(
        "assertion" => false,
        "data" => array("IDs" => "123", "project_id" => "string")
      ),
      array("assertion" => false, "data" => array("IDs" => array())),
      array("assertion" => false, "data" => array("IDs" => array(1, 2, 3))),
      array(
        "assertion" => true,
        "data" => array("IDs" => array(), "project_id" => 42)
      ),
      array(
        "assertion" => true,
        "data" => array("IDs" => array(1, 2, 3), "project_id" => 42)
      ),
      array(
        "assertion" => false,
        "data" => array(
          "additional" => "prop",
          "IDs" => array(1, 2, 3),
          "project_id" => 42
        )
      ),
      array(
        "assertion" => true,
        "data" => array(
          "IDs" => array("1", "2", "3"),
          "project_id" => "42"
        )
      ),
      array(
        "assertion" => false,
        "data" => array("IDs" => array("1", "", "3"))
      )
    );
    $this->iterateCheck(
      $set,
      codeneric\phmm\base\admin\schema\Schemas::fetchImages
    );
  }
  public function testValidateFetchImages2() {
    $input = array("IDs" => array("1", "2", "3"), "project_id" => "321");
    list($data, $v) = Schema::validate(
      $input,
      codeneric\phmm\base\admin\schema\Schemas::fetchImages
    );
    $this->assertSame(
      $data,
      array("IDs" => array(1, 2, 3), "project_id" => 321)
    );
  }
  public function testValidateConfiguration() {
    $input = array();
    list($data, $v) = Schema::validate(
      $input,
      codeneric\phmm\base\admin\schema\Schemas::configuration
    );
    $this->assertSame(
      $data,
      array(
        "commentable" => false,
        "disableRightClick" => false,
        "downloadable" => true,
        "downloadable_favs" => false,
        "downloadable_single" => false,
        "favoritable" => true,
        "showCaptions" => false,
        "showFilenames" => false,
        "watermark" => null
      )
    );
  }
  public function testValidateConfiguration2() {
    $set = array(
      array("assertion" => true, "data" => array()),
      array("assertion" => false, "data" => array("bull" => "shit")),
      array(
        "assertion" => true,
        "data" => array(
          "commentable" => false,
          "disableRightClick" => false,
          "downloadable" => true,
          "downloadable_favs" => false,
          "downloadable_single" => false,
          "favoritable" => true,
          "showCaptions" => false,
          "showFilenames" => false,
          "watermark" => null
        )
      ),
      array(
        "assertion" => true,
        "data" => array(
          "commentable" => "false",
          "disableRightClick" => "false",
          "downloadable" => "true",
          "downloadable_favs" => "false",
          "downloadable_single" => false,
          "favoritable" => "true",
          "showCaptions" => "false",
          "showFilenames" => "false",
          "watermark" => null
        )
      ),
      array(
        "assertion" => false,
        "data" => array(
          "commentable" => 0,
          "disableRightClick" => 0,
          "downloadable" => 1,
          "downloadable_favs" => 0,
          "downloadable_single" => 0,
          "favoritable" => 1,
          "showCaptions" => 0,
          "showFilenames" => 0
        )
      ),
      array(
        "assertion" => false,
        "data" => array(
          "additional" => "prop",
          "commentable" => false,
          "disableRightClick" => false,
          "downloadable" => true,
          "downloadable_favs" => false,
          "downloadable_single" => false,
          "favoritable" => true,
          "showCaptions" => false,
          "showFilenames" => false,
          "watermark" => null
        )
      )
    );
    $this->iterateCheck(
      $set,
      codeneric\phmm\base\admin\schema\Schemas::configuration
    );
  }
  public function testWatermark() {
    $set = array(
      array("assertion" => false, "data" => array()),
      array("assertion" => false, "data" => array("non" => "sense")),
      array(
        "assertion" => false,
        "data" => array(
          "scale" => 15,
          "position" => "abc",
          "image_id" => 10
        )
      ),
      array(
        "assertion" => true,
        "data" => array(
          "scale" => 10,
          "position" => "center",
          "image_id" => 10
        )
      )
    );
    $this->iterateCheck(
      $set,
      codeneric\phmm\base\admin\schema\Schemas::watermark
    );
  }
  public function testValidateProjectFromAdmin() {
    $set = array(
      array("assertion" => false, "data" => array()),
      array("assertion" => false, "data" => array("bull" => "shit")),
      array(
        "assertion" => false,
        "data" => array(
          "gallery" => array(1, 2),
          "protection" => 1,
          "configuration" => $this->getValidProjectConfiguration()
        )
      ),
      array(
        "assertion" => true,
        "data" => array(
          "gallery" => "",
          "protection" => array(
            "password_protection" => false,
            "private" => false
          ),
          "configuration" => $this->getValidProjectConfiguration()
        ),
        "info" => "Empty gallery should be possible"
      ),
      array(
        "assertion" => true,
        "data" => array(
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false"
          ),
          "configuration" => $this->getValidProjectConfiguration()
        )
      ),
      array(
        "assertion" => true,
        "data" => array(
          "additional" => "props",
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false",
            "password" => "qwert1234"
          ),
          "configuration" => $this->getValidProjectConfiguration()
        ),
        "info" => "Additional props are okay"
      ),
      array(
        "assertion" => false,
        "data" => array(
          "pwd" => 2,
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false",
            "password" => "qwert1234"
          ),
          "configuration" => $this->getValidProjectConfiguration()
        ),
        "info" => "pwd needs to be string"
      ),
      array(
        "assertion" => true,
        "data" => array(
          "pwd" => "qwertzz1234",
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false",
            "password" => "qwert1234"
          ),
          "configuration" => $this->getValidProjectConfiguration()
        ),
        "info" => "Everything set"
      ),
      array(
        "assertion" => false,
        "data" => array(
          "pwd" => "qwertzz1234",
          "thumbnail" => "qwertzz1234",
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false",
            "password" => "qwert1234"
          ),
          "configuration" => $this->getValidProjectConfiguration()
        ),
        "info" => "thumbnail should be int"
      ),
      array(
        "assertion" => true,
        "data" => array(
          "pwd" => "qwertzz1234",
          "thumbnail" => 42,
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false",
            "password" => "qwert1234"
          ),
          "configuration" => $this->getValidProjectConfiguration()
        ),
        "info" => "thumbnail should be int"
      ),
      array(
        "assertion" => false,
        "data" => array(
          "pwd" => "qwertzz1234",
          "gallery" => "1,2,3,4,5",
          "protection" => array(
            "password_protection" => "false",
            "private" => "false",
            "password" => "qwert1234"
          ),
          "configuration" => $this->getInvalidProjectConfiguration()
        ),
        "info" => "Invalid config not ok"
      )
    );
    $this->iterateCheck(
      $set,
      codeneric\phmm\base\admin\schema\Schemas::projectFromAdmin
    );
  }
}
