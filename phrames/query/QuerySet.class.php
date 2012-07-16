<?php

  namespace phrames\query;

  class QuerySet implements \IteratorAggregate, \ArrayAccess, \Countable {

    /**
     * Arguments of the query
		 *
		 * @var array
     */
    protected $args = array();

    /**
     * Parent query that is being filtered/excluded
     * or store the class name of the Model which this
     * QuerySet references
		 *
		 * @var string
     */
    protected $parent;

    /**
     * Query orders, such as -make, id, -name, etc
		 *
		 * @var array
     */ 
    protected $order_by = array();

		/**
		 * How this query's results should be limited
		 *
		 * @var string
		 */
    protected $limit;

    /**
     * Construct a new QuerySet (Filter/Exclude) in the form of a linked-list
     * using the $parent argument. The top-level QuerySet's $parent will be
     * the name of the class for which it is a QuerySet on
     *
     * @param string|QuerySet $parent
     * @param array $args
     */
		public function __construct($parent, $args = null) {
			if (!($parent instanceof QuerySet) && !is_subclass_of($parent, "\phrames\model\Model")) 
				throw new \Exception("Invalid QuerySet parent. Must either be valid class name " .
						"or QuerySet object.");

			$this->parent = $parent;

      // perfecly valid to have a query that returns all items
      if (sizeof($args)) {
        foreach($args as $arg) {
          // just an integer, assume searching by id
          if (is_int($arg))
            $arg = \phrames\model\Field::id__exact($arg);

          if ($arg instanceof Expression || $arg instanceof ExpressionNode)
            $this->args[] = $arg;
          else
            throw new \Exception("Invalid QuerySet argument " . var_export($arg, true));
        }
      }

		}

    /**
     * A nicer wrapper for the values_list() method when obtaining an array
     * of values for a particular field for every object in a QuerySEt
     *
     * @param string $field
     * @return array
     */
    public function __get($field) {
      return $this->values_list($field);
    }

		/**
		 * Return this QuerySet as a string, and print out each object
		 * returned by the query
		 *
		 * @return string
		 */
    public function __toString() {
      $str = "[QuerySet:{$this->get_class()}] ";
      foreach($this as $obj)
        $str .= "<{$obj->id()}: {$obj}> ";
      return trim($str);
    }

    /**
     * Return a single result (Object) based on a query, will fault if
     * more than one result is returned
     *
     * @param array $args
     * @return Object
     */
		public function get($args) {
      $clone = $this->filter($args);
      $num = sizeof($clone);

      if ($num == 1)
        return $clone[0];

      if (!$num)
        throw new \Exception("{$this->get_class()} matching query does not exist.");

      throw new \Exception("get() returned more than one {$this->get_class()} -- " .
          "it returned {$num}");
		}

    /**
     * Filter a given query using an AND operator and a set of conditions
     *
     * @return QueryFilter
     */
		public function filter() {
      if ($this->limit || $this->order_by)
        throw new \Exception("Cannot further filter query after limiting or ordering.");
      return new QueryFilter($this, func_get_args()); 
		}

    /**
     * Filter a given query using an AND NOT operator and a set of conditions
     *
     * @return QueryExclude
     */
		public function exclude() {
      if ($this->limit || $this->order_by)
        throw new \Exception("Cannot further exclude query after limiting or ordering.");
      return new QueryExclude($this, func_get_args());
		}

    /**
     * Reorder the query, ASC or DESC, by a given field(s)
     *
     * @return QuerySet
     */
    public function order_by($fields) {
      $this->result = null;
      $this->order_by = func_get_args();
      return $this;
    }

    /**
     * Return the private order_by property for use in building
     * a proper query
     *
     * @return array
     */
    public function get_order_by() {
      return $this->order_by;
    }

    /**
     * Reverse the order of this QuerySet, as defined
     * from the order() method
     *
     * @return QuerySet
     */
    function reverse() {
      $this->result = null;
      $new = array();
      foreach($this->order_by as $order)
        if (substr($order, 0, 1) == "-")
          $new[] = substr($order, 1);
        else
          $new[] = "-{$order}";
      $this->order_by = $new;
      return $this;
    }

    /**
     * Return the arguments that define this query
     *
     * @return array
     */
    public function get_args() {
      return $this->args;
    }

    /**
     * Return the parent QuerySet/class name for this QuerySet
     *
     * @return array|string
     */
    public function get_parent() {
      return $this->parent;
    }

    /**
     * Return the root class name for which this QuerySet pertains
     *
     * @return string
     */
    public function get_class() {
      if ($this->parent instanceof QuerySet)
        return $this->parent->get_class();
      else
        return $this->parent;
    }

    /**
     * Limit/splice this QuerySet to obtain a subset of results
     *
     * @return QuerySet
     */
    public function limit($offset = null, $limit = null) {
      $offset = (int) $offset;
      $limit = (int) $limit;

      if ($offset) $offset--;

      if (!$limit) $limit = "18446744073709551610";

      if ($offset && $limit > 0 && $limit <= $offset)
        throw new \Exception("Limit must be greater than offset");

      $this->limit = "{$offset}:{$limit}";
      return $this;
    }

    /**
     * Use the limit definition to get a theoretical count
     * of this Query's result size
     * 
     * @return int
     */
    public function get_limit_count() {
      if (strpos($this->limit, ":") !== false) {
        list($offset, $limit) = explode(":", $this->limit);
        if ($offset)
          return (int) $limit - $offset;
        else
          return (int) $limit;
      }
    }

    /**
     * Return the currently set limit of this Query for building
     *
     * @return array
     */
    public function get_limit() {
      if (strpos($this->limit, ":") !== false)
        return explode(":", $this->limit);
      else
        return array();
    }

    /**
     * Returns an array of values of a particular field from each
     * member/result of this QuerySet (one dimensionally)
     *
     * e.g. $query->value_list("somefield"); might return something like
     * array(1, 2, 3, 4, 5)
     *
     * @return array
     */
    public function value_list($field) {
      $values = array();
      foreach($this as $obj) {
        if ($obj->$field instanceof Model)
          $values[] = $obj->$field->id;
        else
          $values[] = $obj->$field;
      }
      return $values;
    }

    /**
     * Returns an array of values for a series of fields from each
     * member/result of this QuerySet (two dimensionally)
     *
     * e.g. $query->values_list("somefield"); might return
     * array(array("somefield" => 1), array("somefield" => 2), ...)
     * and $query->values_list(array("field1", "field2")) would return
     * array(array("field1" => 1, "field2" => "A"), array("field1" => 2, "field2" => "B"), ...)
     *
     * @return array
     */
    public function values_list($fields) {
      if (!is_array($fields))
        $fields = array($fields);

      $values = array();
      foreach($this as $obj) {
        $cur = array();
        foreach($fields as $field)
          $cur[$field] = $obj->$field;
        $values[] = $cur;
      }
      return $values;
    }

    /**
     * Perform a series of field updates to each item in this
     * query's result set
     *
     * @return QuerySet
     */
    public function update($fields) {
      if (sizeof($this))
        foreach($this as $obj)
          $obj->set_fields($fields)->save();
      return $this;
    }

    /**
     * Delete every item returned by this QuerySet
     *
     * @return QuerySet
     */
    public function delete() {
      if (sizeof($this))
        foreach($this as $obj)
          $obj->delete();
      return $this;
    }

    /**
     * ITERATOR AGGREGATE
     */

    public function getIterator() {
      return new QueryResult($this);
    }

    /**
     * ARRAYACCESS
     */

    public function offsetSet($offset, $value) {
      throw new \Exception("Cannot arbitrarily set hte object at a current " .
          "position within a QuerySet");
    }

    public function offsetExists($offset) {
      return ($offset < sizeof($this) && $offset >= 0 ? true : false);
    }

    public function offsetUnset($offset) {
      return $this->result[$offset]->delete();
    }

    public function offsetGet($limit) {
      if (strpos($limit, ":") !== false) {
        list($offset, $limit) = explode(":", $limit);
        $this->limit($offset, $limit);
        return $this;
      } else {
        $limit = (int) $limit;
        if ($limit < 0)
          $limit = sizeof($this) - 1 - abs($limit + 1);
        return $this->getIterator()->offsetGet($limit);
      }
    }

    /**
     * COUNTABLE
     */

    public function count() {
      return sizeof($this->getIterator());
    }
    
  }

  class QueryFilter extends QuerySet { }

  class QueryExclude extends QuerySet { }

  // TODO: this
  class EmptyQuerySet extends QuerySet {

    public function count() {
      return 0;
    }

  }
