<?php

  namespace phrames\models\fields;

  require_once(__DIR__ . "/BaseField.class.php");

  class DateField extends BaseField {

    public function __construct($options = []) {
      $this->add_options([
        "auto_now"			=> false,
        "auto_now_add"  => false,
      ]);
      parent::__construct($options);
    }

  }

