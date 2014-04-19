<?php

  namespace phrames\models;

  require_once(__DIR__ . "/../objects/Object.class.php");
  require_once(__DIR__ . "/manager/Manager.class.php");

  use phrames\objects as objects;
  use phrames\models as models;
  use phrames\models\fields as fields;

  abstract class Model {

    private $object; 
    protected static $fields = [];
    protected $options = []; // TODO: https://docs.djangoproject.com/en/1.7/ref/models/options/

    public function __construct($init_values = []) {
      $this->init_object();

      foreach($this->get_fields() as $field => $type) {
        $options = $type->get_options();
        if (strlen($options["default"])) {
          $this->$field = $options["default"];
        }
      }

      foreach($init_values as $field => $value) {
        $this->$field = $value;
      }
    }
    
    public function __toString() {
      return "" . array_pop(explode("\\", get_called_class()));
    }

    public function __get($field) {
      $this->init_object();
      $value = $this->object->$field;
      return $this->get_field_object($field)->get_prep_value($value);
    }

    public function __set($field, $value) {
      $this->init_object();

      $model = get_class($this);
      if ($value instanceof fields\Field) {
        // trying to initiate field type
        $trace = debug_backtrace();
        $trace = sizeof($trace) > 1 ? $trace[1] : $trace[0];
        if ($trace["class"] == $model && $trace["function"] == "__construct") {
          // constructor field setup
          static::$fields[$model][$field] = $value;
        } else {
          throw new \phrames\exceptions\FieldTypeInitiationException(
            sprintf("Cannot initiate field %s with %s outside of __construct",
              $field, $model));
        }
      } else {
        $validator = $this->get_field_object($field);
        $valid = $validator->try_validation($value);

        if ($valid) {
          $this->object->$field = $value;
        }
      }
    }

    private function init_object() {
      if (is_null($this->object)) {
        $this->object = new objects\Object(get_class($this));
      }
    }

    public static function objects() {
      return new models\manager\Manager(get_called_class());
    }

    /**
     * GENERAL HELPERS
     */

    public function get_fields() {
      $model = get_class($this);
      if (array_key_exists($model, static::$fields)) {
        return static::$fields[$model];
      } else {
        return [];
      }
    }

    public function get_field_object($field) {
      $class = get_class($this);
      return isset(static::$fields[$class][$field]) ?
          static::$fields[$class][$field] : new fields\BaseField();
    }

    /**
     * DATABASE
     */

    /*
    public function clean_fields($exclude = null) { }

    public function clean() { }

    public function validate_unique($exclude = null) { }

    public function full_clean($exclude = null) { }

    public function save($force_insert = false, $force_update = false, $update_fields = null) { }

    public function delete() { }
    */

    // TODO: Meta options ?

  }
