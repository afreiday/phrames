<?php

  require_once("Negatable.class.php");
  require_once("Field.class.php");

  class Expression extends Negatable {

    /**
     * Defines what fields this expression is to be performed on
     *
     * @var Field
     */
    private $field;

    /**
     * Store the expression operator (greater than, exact, contains, etc)
     *
     * @var string
     */
    private $operator;

    /**
     * Store the comparison value for this expression. If null, then it is an
     * operation on the field/table column itself
     *
     * @var mixed
     */
    private $value;

    /**
     * Construct a new Expression object
     *
     * @param Field $field
     * @param string $operator
     * @param mixed $value;
     */
    public function __construct($field, $operator, $value) {
      if ($value === null) {
        throw new Exception("Cannot create Expression without test value.");
      } else {
        $this->field = $field;
        $this->operator = strtoupper($operator);
        $this->value = $value;
      }
    }

    /**
     * Get the stored field object
     *
     * @return Field
     */
    public function get_field() {
      return $this->field;
    }

    /**
     * Get the stored value
     *
     * @return mixed
     */
    public function get_value() {
      return $this->value;
    }

    /**
     * Get the stored operator
     *
     * @return string
     */
    public function get_operator() {
      return $this->operator;
    }

  }


  class ExpressionMath extends Expression { 

  }
