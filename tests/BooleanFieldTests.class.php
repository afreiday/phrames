<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models as models;
  use phrames\models\fields as fields;

  class BooleanFieldTests extends PHPUnit_Framework_TestCase {

    protected static $field;

    public static function setUpBeforeClass() {
      self::$field = new fields\BooleanField();
    }

    public function testBooleanFieldFormatIntegerOne() {
      $this->assertSame(self::$field->get_prep_value(1), true);
    }

    public function testBooleanFieldFormatIntegerZero() {
      $this->assertSame(self::$field->get_prep_value(0), false);
    }

    public function testBooleanFieldFormatString() {
      $this->assertSame(self::$field->get_prep_value("test"), true);
    }

    public function testBooleanFieldConstruct() {
      $this->assertTrue(new fields\BooleanField() instanceof fields\BooleanField);
    }

    /**
     * @expectedException phrames\exceptions\InvalidOptionValueException
     */
    public function testBooleanFieldNullOption() {
      new fields\BooleanField(["null" => true]);
    }

  }

