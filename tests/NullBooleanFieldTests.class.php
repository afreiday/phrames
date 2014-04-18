<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models\fields as fields;

  class NullBooleanFieldTests extends PHPUnit_Framework_TestCase {

    protected static $field;

    public static function setUpBeforeClass() {
      self::$field = new fields\NullBooleanField();
    }

    public function testNullBooleanFieldFormatNull() {
      $this->assertNull(self::$field->get_prep_value(null));
    }

    public function testNullBooleanFieldFormatString() {
      $this->assertSame(self::$field->get_prep_value("test"), true);
    }

  }

