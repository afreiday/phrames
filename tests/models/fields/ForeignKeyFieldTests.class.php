<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");
  
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
  
  class ForeignKeyFieldTests extends PHPUnit_Framework_TestCase {

    public function testForeignKeyValidModelRelationship() {
      $this->assertInstanceOf('phrames\models\fields\ForeignKey', new fields\ForeignKey("SomeModel"));
    }

    /**
     * @expectedException phrames\exceptions\InvalidOptionValueException
     */
    public function testForeignKeyFieldBadOnDeleteOption() {
      new fields\ForeignKey("SomeModel", ["on_delete" => "123abc"]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testForeignKeyFieldNonExistentModelRelationship() {
      new fields\ForeignKey("SomeNonExistantModel");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testForeignKeyFieldNonModelClassRelationship() {
      new fields\ForeignKey("SomeNonModelClass");
    }

    public function testForeignKeyAssignment() {
      $field = new fields\ForeignKey("SomeModel");
      $this->assertTrue($field->try_validation(new SomeModel()));
    }

    /**
     * @expectedException phrames\exceptions\ValueValidationException
     */
    public function testForeignKeyAssignmentOfNonModelClass() {
      $obj = new ModelForeignKeyField();
      $obj->some_fk_field = new SomeNonModelClass();
    }
      
  }

