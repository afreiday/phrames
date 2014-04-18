<?php

  namespace phrames\models\fields;

  class ManyToManyField extends KeyField {
    
    public function __construct($model, $options = []) {
      $this->add_options([
        "symmetrical" => true,
        "through"			=> null,
        "db_table"		=> null,
      ]);
      parent::__construct($model);
    }

    // TODO: Support for ManyToManyField (returns a Manager?) add() method

  }
