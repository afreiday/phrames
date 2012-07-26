<?php

  namespace phrames\query;

  abstract class Aggregate {

    private $field = null;

    private $return_key = null;

    protected $name = null;

    /**
     * @param $field Which DB column the aggregate is to work on
     * @param $return_key The custom key/column the value should be returned as
     */
    public function __construct($field, $return_key = null) {
      // we need to create a Field object that can be
      // properly parsed at SQL creation time to properly
      // create joins if necessary
      if (strpos($field, "__")) {
        list($through, $field) = explode("__", $field, 2);
        $field = new Field($field, $through);
      } else {
        $field = new Field($field);
      }
      $this->field = $field;
      $this->return_key = $return_key;
    }

    public function get_name() {
      return strtoupper($this->name);
    }

    public function get_field() {
      return $this->field;
    }

    public function get_return_key() {
      return ($this->return_key ? $this->return_key : 
        "{$this->field->get_field()}__" . strtolower($this->name));
    }

  }
 
  class Avg extends Aggregate {
    protected $name = "Avg";
  }

  class Count extends Aggregate {
    protected $name = "Count";
  }

  class Max extends Aggregate {
    protected $name = "Max";
  }

  class Min extends Aggregate {
    protected $name = "Min";
  }

  class Sum extends Aggregate {
    protected $name = "Sum";
  }
