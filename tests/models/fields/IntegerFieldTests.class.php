<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");
  
  use phrames\models\fields as fields;

  class IntegerFieldTests extends PHPUnit_Framework_TestCase {

    protected static $field;

    public static function setUpBeforeClass() {
      self::$field = new fields\IntegerField();
    }

    public function testIntegerFieldFormatNull() {
      $this->assertNull(self::$field->get_prep_value(null));
    }

    public function testIntegerFieldFormatInteger() {
      $this->assertSame(self::$field->get_prep_value(123), 123);
    }

    public function testIntegerFieldFormatStringNumber() {
      $this->assertSame(self::$field->get_prep_value("123"), 123);
    }

    public function testIntegerFieldFormatFloat() {
      $this->assertSame(self::$field->get_prep_value(123.45), 123);
    }

    public function testIntegerFieldFormatStringChars() {
      $this->assertSame(self::$field->get_prep_value("abc"), 0);
    }

  }
