<?php

  namespace phrames\models\manager;

  class Manager extends Queryable {

    private $model = "";

    private $query = "";

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

    /*
    public function create($init_values = []) { }
    */

  }

