<?php

  namespace phrames\models\query;

  use phrames\models\manager\Manager as Manager;

  abstract class Query extends Queryable implements \ArrayAccess, \Iterator, \Countable {

    const LIMIT_LIMIT = 18446744073709551615;

    private $parent;
    private $query = [];

    private $limit_offset = [];

    public function __construct($parent, $query = []) {
      $this->query = $query;

      if ($parent instanceof Query || $parent instanceof Manager) {
        $this->parent = $parent;
      } else {
        throw new \InvalidArgumentException(sprintf(
          "Cannot initialize %s object. %s parent, on __construct(), must either be a Query or model Manager object",
          get_class($this),
          get_class($this)));
      }
    }

    public function filter($q) {
      $this->try_further_refinement("filter");
      return parent::filter($q);
    }

    public function exclude($q) {
      $this->try_further_refinement("exclude");
      return parent::exclude($q);
    }

    private function try_further_refinement($method) {
      if (sizeof($this->limit_offset) > 0) {
        throw new \BadMethodCallException(sprintf(
          "Cannot perform %s on query when it has been limited by a range",
          $method));
      }
    }

    /**
     * ARRAYACCESS
     */

    public function offsetExists($offset) {
    }

    public function offsetGet($offset) {
      if (is_int($offset)) {
        // [0] should become ... LIMIT 1 OFFSET 0
        // [10] should be LIMIT 1 OFFSET 10
        $this->limit_offset = [1, $offset];
      } elseif (is_string($offset) && strpos($offset, ":") !== false) {
        list($offset, $upto) = explode(":", $offset, 2);

        if ($upto !== "" && (int)$upto < (int)$offset) {
          throw new \InvalidArgumentException(sprintf(
            "Invalid limiting of %s. The 'up to' portion of the range must be greater than the offset",
            get_class($this)));
        } elseif ($upto === "") {
            // [12:] should be LIMIT [max_number_of_results] OFFSET 12
            $this->limit_offset = [self::LIMIT_LIMIT, $offset];
        } else {
          // [5:11] should become LIMIT 6 OFFSET 5
          // [:5] should become LIMIT 5 OFFSET 0
          $this->limit_offset = [(int)$upto - (int)$offset, (int)$offset];
        }
      } else {
        throw new \InvalidArgumentException(sprintf(
          "Limit of %s must either be an integer or a colon-separated range",
          get_class($this)));
      }

      return $this;
    }

    public function offsetSet($offset, $value) {
      throw new \BadMethodCallException(sprintf(
        "Cannot modify an offset result of a %s object. Query objects are readonly.",
        get_class($this)));
    }

    public function offsetUnset($offset) {
      throw new \BadMethodCallException(sprintf(
        "Cannot unset an offset result of a %s object. Query objects are readonly.",
        get_class($this)));
    }

    public function count_using_offset() {
      if (sizeof($this->limit_offset) != 2 || $this->limit_offset[0] == self::LIMIT_LIMIT) {
        // limit not yet defined or incalcuable
        return null;
      } else {
        return $this->limit_offset[0];
      }
    }

    /**
     * ITERATOR
     */

    public function current() {
    }

    public function key() {
    }

    public function next() {
    }
    
    public function rewind() {
    }

    public function valid() {
    }

    /**
     * COUNTABLE
     */

    public function count() {
    }

  }
