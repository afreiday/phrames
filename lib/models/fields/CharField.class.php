<?php

  namespace phrames\models\fields;

  require_once(__DIR__ . "/BaseField.class.php");
  
  class CharField extends BaseField {
    public function __construct($options = []) {
      $this->add_options([
        "max_length" => null,
      ]);
      parent::__construct($options);
    }

    public function validate($value) {
      parent::validate($value);
      $options = $this->options;

      // MAX_LENGTH
      if (isset($options["max_length"]) && strlen($value) > (int) $options["max_length"]) {
          throw new \phrames\exceptions\ValueValidationException(
              sprintf("CharField must have length less than %d",
                  (int) $options["max_length"]));
          return false;
      }

      return true;
    }

    public function get_prep_value($value) {
      if ($value == null) {
        return null;
      } else {
        return "{$value}";
      }
    }
  }

