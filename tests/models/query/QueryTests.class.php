<?php

  require_once(__DIR__ . "/../../../vendor/autoload.php");
  require_once(__DIR__ . "/../../../phrames.php");

  use phrames\models\Model as Model;
  use phrames\models\manager\Manager as Manager;
  use phrames\models\query\Query as Query;

  class QueryModel extends Model { }
  class BaseQuery extends Query { }
  class AnotherQuery extends Query { }
  
  class QueryTests extends PHPUnit_Framework_TestCase {

    private static $query;

    public static function setUpBeforeClass() {
      self::$query = new BaseQuery(new Manager("QueryModel"));
    }

    public function testQueryConstructWithManagerObject() {
      $manager = new Manager("QueryModel");
      $this->assertInstanceOf('phrames\models\query\Query', new BaseQuery($manager));
    }

    /**
     * @depends testQueryConstructWithManagerObject
     */
    public function testQueryConstructWithQueryObject() {
      $query = new AnotherQuery(new Manager("QueryModel"));
      $this->assertInstanceOf('phrames\models\query\Query', new BaseQuery($query));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testQueryConstructWithInvalidParent() {
      new BaseQuery("test");
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testQueryArrayAccessOffsetSet() {
      self::$query[0] = "something else";
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testQueryArrayAccessOffsetUnset() {
      unset(self::$query[0]);
    }

    public function testQueryArrayAccessOffsetGetInteger() {
      $this->assertInstanceOf('BaseQuery', self::$query[1]);
    }

    public function testQueryArrayAccessOffsetGetRange() {
      $this->assertInstanceOf('BaseQuery', self::$query["5:10"]);
    }

    public function testQueryArrayAccessOffsetGetNoUpto() {
      $this->assertInstanceOf('BaseQuery', self::$query["5:"]);
    }

    public function testQueryArrayAccessOffsetGetNoFrom() {
      $this->assertInstanceOf('BaseQuery', self::$query[":5"]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testQueryArrayAccessOffsetGetReversedRange() {
      self::$query["10:5"];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testQueryArrayAccessOffsetGetInvalidRange() {
      self::$query["test"];
    }

    public function testQueryArrayAccessCountUsingOffsetRange() {
      $this->assertEquals(5, self::$query["5:10"]->count_using_offset());
    }

    public function testQueryArrayAccessCountUsingOffsetNoUpTo() {
      $this->assertNull(self::$query["5:"]->count_using_offset());
    }

    public function testQueryArrayAccessCountUsingOffsetNoFrom() {
      $this->assertEquals(10, self::$query[":10"]->count_using_offset());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testQueryableFilterAfterLimit() {
      self::$query["0:10"]->filter([]);;
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testQueryableExcludeAfterLimit() {
      self::$query["0:10"]->exclude([]);
    }
    
  }
