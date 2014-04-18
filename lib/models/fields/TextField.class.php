<?php

  namespace phrames\models\fields;

  class TextField extends BaseField {
    public function get_prep_value($value) {
      if ($value == null) {
        return null;
      } else {
        return "{$value}";
      }
    }
  }

