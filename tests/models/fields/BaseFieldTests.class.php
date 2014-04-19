<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");
  
  use phrames\models\fields as fields;
  
  class BaseFieldTests extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException Exception
     */
    public function testBaseFieldInvalidOption() {
      new fields\BaseField(["max_digits" => 123]);
    }

    public function testBaseFieldValidateNotThrowable() {
      $v = new fields\BaseField(["null" => false, "validate" => "silent"]);
      $this->assertFalse($v->try_validation(null));
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testBaseFieldValidateNullNotAllowed() {
      $v = new fields\BaseField(["null" => false]);
      $v->try_validation(null);
    }

    public function testBaseFieldValidateNullAllowed() {
      $v = new fields\BaseField(["null" => false]);
      $this->assertTrue($v->try_validation("test"));
    }

    /**
     * @expectedException Exception
     */
    public function testBaseFieldValidateBlankNotAllowed() {
      $v = new fields\BaseField(["blank" => false]);
      $v->try_validation("");
    }
    
    public function testBaseFieldValidateBlankTrueAllowed() {
      $v = new fields\BaseField(["blank" => false]);
      $this->assertTrue($v->try_validation("test"));
    }

  }

