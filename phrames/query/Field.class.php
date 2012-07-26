<?php

  namespace phrames\query;

  class Field {

    /**
     * Store the field (of another table) that $field references to
     *
     * @var string
     */
		private $through;
    
    /**
     * Store the field/column to be referenced
     *
     * @var string
     */
    private $field;
		
    /**
     * Construct a new Field referencer
     *
     * @param string $field
     * @param string $through
     */
    public function __construct($field, $through = null) {
      $this->field = $field;
      if (substr_count($through, "__")) {
        $expression = explode("__", $through);
        $field = array_pop($expression);
        $this->through = new self($field, implode("__", $expression));
      } else {
        $this->through = $through;
      }
    }

    /**
     * Create a new Expression given a particular set of
     * field, operator and value, e.g.
     * Field::field__operator(value)
     *
     * @param string $func
     * @param mixed $value
     * @return Expression
     */
		public static function __callStatic($func, $value) {
			$split_count = substr_count($func, "__");

			if ($split_count == 0) {
        if (!sizeof($value)) {
          return new Field($func);
        } else {
          $operator = "exact";
          $obj = new Field($func);
        }
			} else {
				$expression = explode("__", $func);
        $operator = array_pop($expression);
        if (sizeof($expression) == 1) {
          $obj = new self($expression[0]);
        } else {
          $field = array_pop($expression);
          $obj = new self($field, implode("__", $expression));
        }
      }

      $value = $value[0];

      $db = new \phrames\db\Database();

      if (in_array(strtoupper($operator), $db->get_math_operators())) {
        return new ExpressionMath($obj, $operator, $value);
      } else {
        if (!in_array(strtoupper($operator), $db->get_operators()))
          throw new \Exception("Invalid query operator '{$operator}'.");
        else
          return new Expression($obj, $operator, $value);
      }
		}

    /**
     * Get the stored Field
     *
     * @return Field
     */
    public function get_field() {
      return $this->field;
    }

    /**
     * Get the stored through field/column
     *
     * @return string
     */
    public function get_through() {
      return $this->through;
    }

  }

