<?php

  namespace phrames\models\fields;

  class NullBooleanField extends BaseField {

    public function get_prep_value($value) {
      if ($value == null) {
        return null;
      } else {
        return (boolean) $value;
      }
    }

  }

