<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models\fields as fields;
  
  class FieldValueValidatorTests extends PHPUnit_Framework_TestCase {

    public function testValidateNotThrowable() {
      $v = new fields\BaseField(["null" => false, "validate" => "silent"]);
      $this->assertFalse($v->try_validation(null));
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testValidateNull() {
      $v = new fields\BaseField(["null" => false]);
      $v->try_validation(null);
    }

    public function testValidateNullTrue() {
      $v = new fields\BaseField(["null" => false]);
      $this->assertTrue($v->try_validation("test"));
    }

    /**
     * @expectedException Exception
     */
    public function testValidateBlank() {
      $v = new fields\BaseField(["blank" => false]);
      $v->try_validation("");
    }
    
    public function testValidateBlankTrue() {
      $v = new fields\BaseField(["blank" => false]);
      $this->assertTrue($v->try_validation("test"));
    }

    /**
     * @expectedException Exception
     */
    public function testValidateMaxLength() {
      $v = new fields\CharField(["max_length" => 5]);
      $v->try_validation("123456");
    }
    
    public function testValidateMaxLengthTrue() {
      $v = new fields\CharField(["max_length" => 5]);
      $this->assertTrue($v->try_validation("test"));
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testValidateMaxDigits() {
      $v = new fields\DecimalField(["max_digits" => 5, "decimal_places" => 2]);
      $v->try_validation(12345.67);
    }
    
    public function testValidateMaxDigitsTrue() {
      $v = new fields\DecimalField(["max_digits" => 5, "decimal_places" => 2]);
      $this->assertTrue($v->try_validation(123.45));
      $v = new fields\DecimalField(["max_digits" => 5, "decimal_places" => 2]);
      $this->assertTrue($v->try_validation(123));
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testValidateDecimalPlaces() {
      $v = new fields\DecimalField(["decimal_places" => 2, "max_digits" => 100]);
      $v->try_validation(12345.123);
    }
    
    public function testValidateDecimalPlacesTrue() {
      $v = new fields\DecimalField(["decimal_places" => 2, "max_digits" => 100]);
      $this->assertTrue($v->try_validation(123.45));
      $v = new fields\DecimalField(["decimal_places" => 2, "max_digits" => 100]);
      $this->assertTrue($v->try_validation(123));
    }

  }
