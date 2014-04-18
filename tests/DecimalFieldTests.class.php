<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models\fields as fields;

  class DecimalFieldTests extends PHPUnit_Framework_TestCase {

    protected static $field;

    public static function setUpBeforeClass() {
      self::$field = new fields\DecimalField(["max_digits" => 10, "decimal_places" => 2]);
    }

    /**
     * @expectedException Exception
     */
    public function testDecimalFieldRequired() {
      new fields\DecimalField();
    }

    public function testDecimalFieldFormatNull() {
      $this->assertNull(self::$field->get_prep_value(null));
    }

    public function testDecimalFieldFormatFloat() {
      $this->assertSame(self::$field->get_prep_value(123.45), 123.45);
    }

    public function testDecimalFieldFormatInteger() {
      $this->assertSame(self::$field->get_prep_value(123), 123.0);
    }

    public function testDecimalFieldFormatStringFloat() {
      $this->assertSame(self::$field->get_prep_value("123.45"), 123.45);
    }

    public function testDecimalFieldFormatStringChars() {
      $this->assertSame(self::$field->get_prep_value("abc"), 0.0);
    }

    public function testDecimalFieldFormatDecimalPlacesTrimming() {
      $this->assertSame(self::$field->get_prep_value(123.456), 123.46);		
    }

  }

