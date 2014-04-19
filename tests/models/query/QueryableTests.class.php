<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");

  use phrames\models\query\Query as Query;
  use phrames\models\manager\Manager as Manager;
  use phrames\models\Model as Model;

  class QueryableModel extends Model { }
  class QueryableManager extends Manager { }
  class QueryableObject extends Query { }
  
  class QueryableTests extends PHPUnit_Framework_TestCase {

    private static $queryable;

    public static function setUpBeforeClass() {
      $manager = new Manager("QueryableModel");
      self::$queryable = new QueryableObject($manager);
    }

    public function testQueryableFilter() {
      $this->assertInstanceOf('phrames\models\query\QueryFilter', self::$queryable->filter([]));
    }

    public function testQueryableExclude() {
      $this->assertInstanceOf('phrames\models\query\QueryExclude', self::$queryable->exclude([]));
    }

    public function testQueryableOrderByWithArray() {
      $this->assertInstanceOf('phrames\models\query\Queryable', self::$queryable->order_by(["field1", "field2"]));
    }

    public function testQueryableOrderByParameterized() {
      $obj = self::$queryable->order_by("field1", "field2");
      $this->assertInstanceOf('phrames\models\query\Queryable', $obj);
      $this->assertAttributeEquals(["field1", "field2"], "order", $obj);
    }

    public function testQueryableOrderByAddSeparately() {
      $obj = self::$queryable->order_by("field1")->order_by("field2");
      $this->assertAttributeEquals(["field1", "field2"], "order", $obj);
    }

    public function testQueryableOrderByReturnsClone() {
      $obj1 = self::$queryable;
      $obj2 = $obj1->order_by("some_field");
      $this->assertNotSame($obj1, $obj2);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testQueryableOrderByInvalidFields() {
      self::$queryable->order_by(1, 10.1, false);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testQueryableFilterAfterOrderBy() {
      self::$queryable->order_by("test")->filter([]);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testQueryableExcludeAfterOrderBy() {
      self::$queryable->order_by("test")->exclude([]);
    }

    public function testQueryableReverse() {
      $obj = self::$queryable->order_by('field1', '-field2', 'field3')->reverse();
      $this->assertAttributeEquals(['-field1', 'field2', '-field3'], "order", $obj);
    }

    public function testQueryableReverseReturnsClone() {
      $obj1 = self::$queryable;
      $obj2 = $obj1->reverse();
      $this->assertNotSame($obj1, $obj2);
    }
    
  }
