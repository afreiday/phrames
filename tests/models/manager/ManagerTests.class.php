<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");
  
  use phrames\models\Model as Model;
  use phrames\models\manager\Manager as Manager;

  class AnotherModel extends Model { }

  class AnotherNonModelClass { }
  
  class ManagerTests extends PHPUnit_Framework_TestCase {

    public function testManagerConstruct() {
      $this->assertInstanceof('phrames\models\manager\Manager', new Manager("AnotherModel"));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testManagerConstructNonExistantClass() {
      new Manager("AnotherNonExistantClass");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testManagerConstructNonModelClass() {
      new Manager("AnotherNonModelClass");
    }
    
  }
