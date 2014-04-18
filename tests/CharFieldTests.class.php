<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models as models;
  use phrames\models\fields as fields;
  
  class CharFieldTests extends PHPUnit_Framework_TestCase {

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
      
  }
