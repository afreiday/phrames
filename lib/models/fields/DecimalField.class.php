<?php

  namespace phrames\models\fields;

  class DecimalField extends BaseField {

    public function __construct($options = []) {
      $this->add_options([
        "max_digits"		 => null,
        "decimal_places" => null,
      ]);
      parent::__construct($options);
    }

    public function validate($value) {
      parent::validate($value);
      $options = $this->options;

      // MAX_DIGITS
      if (isset($options["max_digits"])) {
          $digits = str_replace(".", "", $value);
          if (strlen($digits) > (int) $options["max_digits"]) {
              throw new \phrames\exceptions\ValueValidationException(
                  sprintf("Field must have max %d digits",
                      (int) $options["max_digits"]));
              return false;
          }
      }
      // DECIMAL_PLACES
      if (isset($options["decimal_places"])) {
          $fval = (float) $value;
          $decimals = substr($fval, strpos($fval, ".") + 1);
          if (strlen($decimals) > (int) $options["decimal_places"]) {
              throw new \phrames\exceptions\ValueValidationException(
                  sprintf("Field must have max %d decimal places",
                      (int) $options["decimal_places"]));
              return false;
          }
      }
      
      return true;
    }

    public function get_prep_value($value) {
      if ($value == null) {
        return null;
      } else {
        return (float) number_format((float) $value, $this->options["decimal_places"]);
      }
    }

  }

