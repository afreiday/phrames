<?php

  require_once(__DIR__ . "/../../phrames.php");
  require_once(__DIR__ . "/../../vendor/autoload.php");
  
  use phrames\models as models;
  use phrames\models\fields as fields;
  
  class TestModelEmpty extends models\Model {

  }

  class TestModelBadFieldInitiation extends models\Model {
    public function someMethod() {
      $this->field = new fields\IntegerField();
    }
  }

  class TestModel extends models\Model {

    public function __toString() {
      return $this->field;
    }

    public function __construct() {
      $this->blank_field = new fields\CharField([
        "blank" => false,
        "max_length" => 100,
      ]);
      $this->null_field = new fields\CharField([
        "null" => false,
        "max_length" => 100,
      ]);
      $this->empty_field = new fields\CharField([
        "blank" => true,
        "null" => true,
        "max_length" => 100,
      ]);
      $this->default_field = new fields\CharField([
        "default" => "some default value",
        "max_length" => 100,
      ]);
      $this->validate_field = new fields\CharField([
        "max_length" => 2,
      ]);
      $this->dont_validate_field = new fields\CharField([
        "max_length" => 2,
        "validate" => false,
      ]);
      $this->silently_validate_field = new fields\CharField([
        "max_length" => 2,
        "validate" => "silent",
      ]);
      parent::__construct();
    }

  }

  class ModelTests extends PHPUnit_Framework_TestCase {
    
    public function testModelConstruct() {
      $this->assertInstanceOf('phrames\models\Model', new TestModelEmpty());
    }

    public function testEmptyModelToString() {
      $obj = new TestModelEmpty();
      $this->assertEquals("{$obj}", "TestModelEmpty");
    }

    public function testModelAssignToUndefinedField() {
      $obj = new TestModelEmpty();
      $obj->field = "123";
      $this->assertEquals($obj->field, "123");
    }

    /**
     * @expectedException phrames\exceptions\FieldTypeInitiationException
     */
    public function testModelDefineFieldTypesOutsideOfConstruct() {
      $obj = new TestModelBadFieldInitiation();
      $obj->someMethod();
    }

    public function testModelToString() {
      $obj = new TestModel();
      $obj->field = "123";
      $this->assertEquals("{$obj}", "123");
    }

    public function testModelConstructInit() {
      $obj = new TestModelEmpty(["f1" => "foo", "f2" => "bar"]);
      $this->assertEquals($obj->f1, "foo");
      $this->assertEquals($obj->f2, "bar");
    }
    
    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testModelBlankField() {
      $obj = new TestModel();
      $obj->blank_field = "";
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testModelNullField() {
      $obj = new TestModel();
      $obj->null_field = null;
    }

    public function testModelEmptyField() {
      $obj = new TestModel();
      $obj->empty_field = null;
      $this->assertEquals($obj->empty_field, null);
      $obj->empty_field = "";
      $this->assertEquals($obj->empty_field, "");
    }

    public function testModelDefaultField() {
      $obj = new TestModel();
      $this->assertEquals("some default value", $obj->default_field);
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testModelValidateField() {
      $obj = new TestModel();
      $obj->validate_field = "abc";
    }

    public function testModelDontValidateField() {
      $obj = new TestModel();
      $obj->dont_validate_field = "abc";
      $this->assertEquals("abc", $obj->dont_validate_field);
    }

    public function testModelSilentlyValidateField() {
      $obj = new TestModel();
      $obj->silently_validate_field = "abc";
      $this->assertEquals("", $obj->silently_validate_field);
    }

    public function testModelManager() {
      $this->assertInstanceOf("phrames\models\manager\Manager", TestModel::objects());
    }
      
  }
