<?php

  namespace phrames\models\fields;

  class BaseField implements Field, FieldValidator {

    protected $options = [
      // an option with a null value indicates it is required
      "validate"			=> true,
      "db_column"			=> "",
      "db_index"			=> false,
      "null"					=> false,
      "blank"					=> false,
      "default"				=> "",
      "primary_key"		=> false,
      "unique"				=> false,
    ];

    public function __construct($options = []) {
      $this->validate_options($options);
    }

    protected function add_options($options) {
      $this->options = array_merge($this->options, $options);
    }

    public function get_options() {
      return $this->options;
    }
    
    protected function validate_options($options) {
      // max sure the passed options are all valid to this class
      foreach($options as $option => $value) {
        if (!key_exists($option, $this->options)) {
          throw new \phrames\exceptions\InvalidOptionException(
            sprintf("Invalid %s option %s", get_called_class(), $option));
        } else {
          $this->options[$option] = $value;
        }
      }

      // make sure each required option (defaulted as null) is defined
      foreach($this->options as $option => $value) {
        if ($value === null) {
          throw new \phrames\exceptions\InvalidOptionValueException(
            sprintf("%s option %s is a required option", get_called_class(), $option));
        }
      }
    }

    public function needs_validation() {
      return $this->options["validate"] == true;
    }

    public function is_silent() {
      return $this->options["validate"] === "silent";
    }

    public function try_validation($value) {
      if (!$this->needs_validation())
        return true;

      try {
        return $this->validate($value);
      } catch (\Exception $e) {
        if (!$this->is_silent())
          throw $e;
        return false;
      }
    }

    protected function validate($value) {
      $options = $this->options;

      // NULL
      if (isset($options["null"]) && !$options["null"] && $value === null) {
          throw new \phrames\exceptions\ValueValidationException(
            "Field cannot be null");
          return false;
      }
      // BLANK
      if (isset($options["blank"]) && !$options["blank"] && strlen($value) == 0) {
          throw new \phrames\exceptions\ValueValidationException(
            "Field cannot be blank");
          return false;
      }

      return true;
    }

    public function get_prep_value($value) {
      return $value;
    }
  }

