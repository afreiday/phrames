<?php

  namespace phrames\model;
  
  class Manager {
    
    private $model = null;

    public function __construct($model) {
      $this->model = $model;
    }

    public function get_query_set() {
      return new \phrames\query\QuerySet($this->model);
    }

    public function all() {
      return $this->get_query_set();
    }

    public function count() {
      return $this->get_query_set()->count();
    }

    public function get() {
      return call_user_func_array(array($this->get_query_set(), "get"), func_get_args());
    }

    /**
     * Create a new object of type $this->model
     * using the passed set of arguments (fields and values)
     *
     * @param array
     * @return object
     */
    public function create($args) {
      $class = $this->model;
      $obj = new $class;
      foreach($args as $field => $value)
        $obj->$field = $value;
      return $obj;
    }

    /**
     * Retrieve a single object given a set of arguments or create a new object
     * using those same arguments if a result cannot be found
     *
     * @param array $args
     * @return Object
     */
    public function get_or_create($args) {
      $and = array();
      foreach($args as $field => $val)
        $and[] = forward_static_call(array("phrames\query\Field", "{$field}__exact"), $val);
      
      try {
        return $this->get(_AND_($and));
      } catch(\Exception $e) {
        $obj = $this->create($args);
        $obj->save();
        return $obj;
      }
    }

    public function filter() {
      return call_user_func_array(array($this->get_query_set(), "filter"), func_get_args());
    }

    public function exclude() {
      return call_user_func_array(array($this->get_query_set(), "exclude"), func_get_args());
    }

  }
