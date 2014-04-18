<?php

  namespace phrames\models\fields;

  interface FieldValidator {

    public function needs_validation();

    public function is_silent();

    public function try_validation($value);

  }

