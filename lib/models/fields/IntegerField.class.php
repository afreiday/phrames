<?php

  namespace phrames\models\fields;

  class IntegerField extends BaseField {
      
    public function get_prep_value($value) {
      if ($value == null) {
        return null;
      } else {
        return (int) $value;
      }
    }

  }

  /*
   TODO: Do these and move them

    class PositiveIntegerField extends IntegerField {
    }

    class BigIntegerField extends IntegerField {
    }

    class SmallIntegerField extends IntegerField {
    }
  */

