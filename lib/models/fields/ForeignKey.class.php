<?php

  namespace phrames\models\fields;

  class ForeignKey extends KeyField {

    public function __construct($model, $options = []) {
      $this->add_options([
        "to_field"				 => "",
        "on_delete"			   => 0, // TODO: SET() ??
      ]);
      parent::__construct($model, $options);
    }

    public function validate($value) {
      parent::validate($value);

      $value_type = get_class($value);
      if ($value_type != $this->model) {
        throw new \phrames\exceptions\ValueValidationException(
          sprintf("Cannot assign value of %s to ForeignKey, expecting type %s", $value_type, $this->model));
        return false;
      }

      return true;
    }

    protected function validate_options($options) {
      parent::validate_options($options);

      // ensure on_delete has a valid option
      $ref = new \ReflectionClass('phrames\models\fields\OnDelete');
      $on_delete_values = $ref->getConstants();
      if ($this->options["on_delete"] && !in_array($this->options["on_delete"], $on_delete_values, true)) {
        throw new \phrames\exceptions\InvalidOptionValueException(
          sprintf("Invalid value for on_delete option. Must be one of %s " .
            "from phrames\\models\\fields\\OnDelete class constants",
            implode(", ", array_keys($on_delete_values))));
      }
    }
      
  }

