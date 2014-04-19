<?php

  namespace phrames\models\manager;

  use phrames\models\query as query;

  class Manager extends query\Queryable implements \IteratorAggregate {

    private $model = "";

    public function __construct($model) {

      if (!class_exists($model)) {
        throw new \InvalidArgumentException(sprintf(
          "Cannot create Manager for model %s. Class %s does not exist",
          $model, $model));
      } elseif (!in_array("phrames\models\Model", class_parents($model))) {
        throw new \InvalidArgumentException(sprintf(
          "Cannot create Manager for model %s. Class %s is not a phrames Model.",
          $model, $model));
      }

      $this->model = $model;

    }

    public function get_model() {
    }

    /*
    public function create($init_values = []) { }
    */

    public function all() {
      return new query\QueryAll($this);
    }

    public function delete() {
    }

    /**
     * ITERATORAGGREGATE
     */

    public function getIterator() {
      return $this->all();
    }

    /**
     * COUNTABLE
     */
    public function count() {
      return sizeof($this->all());
    }

  }

