<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models as models;
  use phrames\models\fields as fields;

  class SomeModel extends models\Model { }

  class SomeNonModelClass { }

  class ModelForeignKeyField extends models\Model {
    public function __construct($init_values = []) {
      $this->some_fk_field = new fields\ForeignKey("SomeModel");
      parent::__construct($init_values);
    }
  }
  
  class FieldTests extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException Exception
     */
    public function testInvalidOption() {
      new fields\CharField(["max_digits" => 123]);
    }

    /**
     * @expectedException Exception
     */
    public function testCharFieldRequired() {
      new fields\CharField();
    }
    
    public function testCharFieldFormat() {
      $obj = new fields\CharField(["max_length" => 100]);
      $this->assertSame($obj->get_prep_value(123), "123");
    }

    public function testTextFieldFormat() {
      $obj = new fields\TextField();
      $this->assertSame($obj->get_prep_value(123), "123");
    }

    public function testIntegerFieldFormat() {
      $obj = new fields\IntegerField();
      $this->assertSame($obj->get_prep_value("123"), 123);
      $this->assertSame($obj->get_prep_value(123.45), 123);
      $this->assertSame($obj->get_prep_value("abc"), 0);
    }

    public function testBooleanFieldFormat() {
      $obj = new fields\BooleanField();
      $this->assertSame($obj->get_prep_value(1), true);
      $this->assertSame($obj->get_prep_value(0), false);
      $this->assertSame($obj->get_prep_value("test"), true);
    }

    /**
     * @expectedException Exception
     */
    public function testBooleanFieldFormatNull() {
      $obj = new fields\BooleanField(["null" => true]);
    }

    public function testNullBooleanFieldFormat() {
      $obj = new fields\NullBooleanField();
      $this->assertSame($obj->get_prep_value(null), null);
    }

    /**
     * @expectedException Exception
     */
    public function testDeicmalFieldRequired() {
      new fields\DecimalField();
    }

    public function testDecimalFieldFormat() {
      $obj = new fields\DecimalField(["max_digits" => 10, "decimal_places" => 2]);
      $this->assertSame($obj->get_prep_value(123), 123.0);
      $this->assertSame($obj->get_prep_value(123.45), 123.45);
      $this->assertSame($obj->get_prep_value("123.45"), 123.45);
      $this->assertSame($obj->get_prep_value("abc"), 0.0);
      $this->assertSame($obj->get_prep_value(123.456), 123.46);		
    }

    /**
     * @expectedException phrames\exceptions\InvalidOptionValueException
     */
    public function testForeignKeyFieldBadOnDeleteOption() {
      $obj = new fields\ForeignKey("SomeModel", ["on_delete" => "123abc"]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testForeignKeyFieldNonExistentModelRelationship() {
      $obj = new fields\ForeignKey("SomeNonExistantModel");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testForeignKeyFieldNonModelClassRelationship() {
      $obj = new fields\ForeignKey("SomeNonModelClass");
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testForeignKeyAssignmentOfNonModelClass() {
      $obj = new ModelForeignKeyField();
      $obj->some_fk_field = new SomeNonModelClass();
    }
      
  }
