<?php

  namespace phrames\query;

  /**
   * The QueryBuilder class is essentially a helper class
   * for building complex queries. When a query is sent to
   * the driver to be compiled and executed, a QueryBuilder
   * object can assist by storing a list of required
   * joins and hashed parameters (for PDO prepared statements)
   * between method calls that compile the actual SQL statement
   */
  class QueryBuilder { 

    /**
     * Stores the parameters that need to be bound to a PDO
     * object at time of execution
     *
     * @var array
     */
    private $params = array();

    /**
     * Stores a list of required table joins required for
     * when this statement is executed
     *
     * @var array
     */
    private $joins = array();

    /**
     * Stores a list of values that _should not_ be hashed
     *
     * @var array
     */
    private $dont_hash = array();

    /**
     * Add a parameter (its key and value) to the list
     *
     * @param string $k
     * @param string $v
     * @return QueryBuilder
     */
    public function add_param($k, $v) {
      $this->params[$k] = $v;
      return $this;
    }

    public function get_params() {
      return $this->params;
    }

    /**
     * Add to the list of keys to not hash
     *
     * @param string $v
     * @return QueryBuilder
     */
    public function dont_hash($v) {
      $this->dont_hash[] = $v;
      return $this;
    }

    /**
     * Returns whether or not a particular value should
     * be hashed or not
     *
     * @param string $v
     * @return bool
     */
    public function should_hash($v) {
      return !in_array($v, $this->dont_hash);
    }

    /**
     * Hash a particular value and add it to the list
     * or parameters to be bound to PDO object if
     * required
     *
     * @param string $v
     * @return string
     */
    public function hash($v) {
      if ($v instanceof Field) {
        // comparing to another field
        return $v->get_field();
      } elseif ($this->should_hash($v)) {
        // field needs to be hashed and parameritized for PDO
        $hash = md5($v);
        $this->add_param($hash, $v);
        return ":{$hash}";
      } else {
        // just return the field as it is
        return $v;
      }
    }

    public function add_join($table) {
      if (!in_array($table, $this->joins))
        $this->joins[] = $table;
    }

    public function get_joins() {
      return $this->joins;
    }

  }
