<?php

  namespace phrames\query;

  use phrames\db\Database as Database;

  require_once("QuerySet.class.php");

  class QueryResult implements \Countable, \Iterator, \ArrayAccess {

    /**
     * Store the QuerySet that this result references
     *
     * @var QuerySet
     */
    private $query = null;

    /**
     * An array of keys (object database IDs) that are the
     * result of the query
     *
     * @var array
     */
    private $keys = array();

    /**
     * State of whether or not this result has been loaded
     *
     * @var bool
     */
    private $loaded = false;

    /**
     * The cursor position of the iterator throughout the
     * result (keys)
     *
     * @var int
     */
    private $pos = 0;

    /**
     * Construct a new QueryResult object
     *
     * @param QuerySet $query
     */
    public function __construct(QuerySet $query) {
      $this->query = $query;
    }

    /**
     * Load the results (keys) into this QueryResult
     *
     * @return QueryResult
     */
    public function load() {
      $class = $this->query->get_class();
      $db = $class::get_db();
      $this->keys = $db->get_keys($this->query);
      $this->loaded = true;
      return $this;
    }

    /**
     * Get an item at a particular position
     *
     * @param int $key
     * @return Object
     */
    public function get_item($key) {
      if (!$this->loaded) $this->load();
      $class = $this->query->get_class();
      return new $class((int) $this->keys[$key]);
    }

    /**
     * COUNTABLE
     */

    /**
     * Count the number of results for this query
     *
     * @return int
     */
    public function count() {
      if ($this->loaded) {
        return sizeof($this->keys);
      } else {
        // get straight from DB
        $class = $this->query->get_class();
        $db = $class::get_db();
        return $db->get_query_count($this->query);
      }
    }

    /**
     * ITERATOR
     */

    public function rewind() {
      $this->pos = 0;
    }

    public function valid() {
      return $this->pos < sizeof($this);
    }

    public function key() {
      return $this->pos;
    }

    public function current() {
      return $this->get_item($this->pos);
    }

    public function next() {
      $this->pos++;
    }

    /**
     * ARRAYACCESS
     */

    public function offsetSet($offset, $value) {
      throw new Exception("Cannot add an item to a QueryResult.");
    }

    public function offsetExists($offset) {
      return ($offset < sizeof($this) && $offset >= 0 ? true : false);
    }

    public function offsetUnset($offset) {
      $this->get_item($offset)->delete();
      unset($this->keys[$offset]);
    }

    public function offsetGet($offset) {
      return $this->get_item($offset);
    }

  }
