<?php

  require_once(__DIR__ . "/../vendor/autoload.php");
  require_once(__DIR__ . "/../phrames.php");
  
  use phrames\models\fields as fields;
  
  class BaseFieldTests extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException Exception
     */
    public function testBaseFieldInvalidOption() {
      new fields\BaseField(["max_digits" => 123]);
    }

  }

