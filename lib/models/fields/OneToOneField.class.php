<?php

  namespace phrames\models\fields;

  class OneToOneField extends KeyField {
    public function __construct($model, $options = []) {
      $this->add_options([
        "parent_link"		=> null,
      ]);
      parent::__construct($model);
    }
  }

