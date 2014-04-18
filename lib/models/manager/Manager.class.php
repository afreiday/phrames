<?php

  namespace phrames\models\manager;

  require_once(__DIR__ . "/Queryable.class.php");

  class Manager extends Queryable {

    private $model = "";

    private $query = "";

    public function __construct($model) {
      if (!in_array("phrames\models\Model", class_parents($model))) {
        throw new \Exception(sprintf(
              "Cannot create Manager for model %s. Class %s is not a phrames Model.",
              $model, $model));
      }
      $this->model = $model;
    }

    public function create($init_values = []) {
    }

  }

