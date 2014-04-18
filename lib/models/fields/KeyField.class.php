<?php

  namespace phrames\models\fields;

  abstract class KeyField extends BaseField {

    protected $model = "";
    const REQUIRED_PARENT = "phrames\\models\\Model";

    public function __construct($model, $options = []) {
      unset($this->options["blank"]);

      $keyfield_type = get_class($this);

      if (!class_exists($model)) {
        throw new \InvalidArgumentException(
          sprintf("Cannot create %s relationship to '%s'. Class '%s' does not exist", $keyfield_type, $model, $model));
      } elseif (get_parent_class($model) != self::REQUIRED_PARENT) {
        throw new \InvalidArgumentException(
          sprintf("Cannot create %s relationship to '%s'. Class '%s' must be child of %s", $keyfield_type, $model, $model, self::REQUIRED_PARENT)); 
      } else {
        $this->model = $model;
        $this->add_options([
          "limit_choices_to" => [],
        ]);
        parent::__construct($options);
      }
    }

  }

