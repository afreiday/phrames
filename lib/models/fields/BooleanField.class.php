<?php

  namespace phrames\models\fields;

  class BooleanField extends BaseField {

    public function __construct($options = []) {
      parent::__construct($options);
      if ($this->options["null"] == true) {
        throw new \phrames\exceptions\InvalidOptionValueException(
          "Option 'null' for BooleanField cannot be true.");
      }
    }

    public function get_prep_value($value) {
      return (boolean) $value;
    }
  }

