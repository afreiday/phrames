<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");

  use phrames\models\Model as Model;
  use phrames\models\manager\Manager as Manager;
  use phrames\models\query\EmptyQuery as EmptyQuery;

  class EmptyQueryModel extends Model { }
  
  class EmptyQueryTests extends PHPUnit_Framework_TestCase {

    private static $query;

    public static function setUpBeforeClass() {
      self::$query = new EmptyQuery(new Manager("EmptyQueryModel"));
    }

    public function testEmptyQueryValuesReturnsEmptyArray() {
      $this->assertEmpty(self::$query->values());
    }

    public function testEmptyQueryValuesListReturnsEmptyArray() {
      $this->assertEmpty(self::$query->values_list());
    }

    public function testEmptyQueryFilterReturnsEmptyQuery() {
      $this->assertInstanceOf('phrames\models\query\EmptyQuery', self::$query->filter([]));
    }

    public function testEmptyQueryExcludeReturnsEmptyQuery() {
      $this->assertInstanceOf('phrames\models\query\EmptyQuery', self::$query->exclude([]));
    }

    public function testEmptyQueryAnyOffsetExistsReturnsFalse() {
      $this->assertFalse(isset(self::$query[123]));
    }

    public function testEmptyQueryAnyOffsetReturnsEmptyQuery() {
      $this->assertInstanceOf('phrames\models\query\EmptyQuery', self::$query[123]);
    }

    public function testEmptyQueryCountReturnsZero() {
      $this->assertEquals(0, sizeof(self::$query));
    }

  }

